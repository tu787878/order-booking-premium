<?php

/**
 * 256-bit symmetric key shared with the calling site.
 * The secret string MUST be byte-for-byte identical on both sites.
 */
function myplugin_auth_key()
{
    $secret = '8764ecfe65173f70cd341552ab5d0bae6d5a0f09d2e532723e9f13c4a645ba42';
    return hash('sha256', $secret, true);
}

/**
 * Decrypt an auth code produced by the calling site.
 * Returns array('u' => username, 'p' => password) on success, or false on
 * failure / tampering.
 */
function myplugin_decrypt_code($code)
{
    $blob = base64_decode(strtr($code, '-_', '+/'));
    if ($blob === false || strlen($blob) < 28) {
        return false;
    }

    $iv         = substr($blob, 0, 12);
    $tag        = substr($blob, 12, 16);
    $ciphertext = substr($blob, 28);
    $key        = myplugin_auth_key();

    $plaintext = openssl_decrypt($ciphertext, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $iv, $tag);
    if ($plaintext === false) {
        return false;
    }

    $data = json_decode($plaintext, true);
    if (!is_array($data) || !isset($data['u'], $data['p'])) {
        return false;
    }

    return $data;
}

/**
 * Save a base64 image payload to the WordPress media library.
 */
function save_image2($base64, $name = '')
{
    if (empty($base64) || strpos($base64, 'data:') !== 0) {
        return 0;
    }

    $matches = array();
    if (!preg_match('/^data:(image\/[a-zA-Z0-9.+-]+);base64,(.+)$/', $base64, $matches)) {
        return 0;
    }

    $mime = $matches[1];
    $data = base64_decode($matches[2], true);
    if ($data === false || $data === '') {
        return 0;
    }

    $upload_dir = wp_upload_dir();
    if ($upload_dir['error'] !== false) {
        return 0;
    }

    $extension = 'bin';
    $mime_map = array(
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/gif'  => 'gif',
        'image/webp' => 'webp',
    );
    if (isset($mime_map[$mime])) {
        $extension = $mime_map[$mime];
    }

    $file_name = sanitize_file_name((!empty($name) ? $name : 'upload') . '.' . $extension);
    $file_path = $upload_dir['path'] . '/' . wp_unique_filename($upload_dir['path'], $file_name);

    if (file_put_contents($file_path, $data) === false) {
        return 0;
    }

    $file_type = wp_check_filetype($file_path, null);
    $attachment = array(
        'post_mime_type' => $file_type['type'] ?: $mime,
        'post_title'     => sanitize_file_name(pathinfo($file_path, PATHINFO_FILENAME)),
        'post_content'   => '',
        'post_status'    => 'inherit',
    );

    $attach_id = wp_insert_attachment($attachment, $file_path);
    if (is_wp_error($attach_id) || intval($attach_id) <= 0) {
        return 0;
    }

    require_once ABSPATH . 'wp-admin/includes/image.php';
    $attach_data = wp_generate_attachment_metadata($attach_id, $file_path);
    wp_update_attachment_metadata($attach_id, $attach_data);

    return $attach_id;
}

// Test
add_action('rest_api_init', function () {

    register_rest_route('ordertcg/v1', '/manage/change_option', array(
        'methods' => 'POST',
        'callback' => 'manage_change_option'
    ));
});
function manage_change_option()
{

    // authentication 
    $data = file_get_contents('php://input');

    parse_str($data, $data);
    $code = isset($data['code']) ? $data['code'] : '';
    $auth = myplugin_decrypt_code($code);

    if ($auth === false || empty($auth['u']) || empty($auth['p'])) {
        $result = array('status' => 'fail', 'code'=>1);
        return $result;
    }

    $check = wp_authenticate_username_password( NULL, $auth['u'], $auth['p'] );

    if(!is_wp_error( $check )){
        $result = array('status' => 'success', 'code'=>0, 'data'=>$data["data"]);
        update_option($data["option"], $data["data"]);

        return $result;
    }
    $result = array('status' => 'fail', 'code'=>1);
    return $result;
} 

add_action('rest_api_init', function () {

    register_rest_route('ordertcg/v1', '/manage/save_popup', array(
        'methods' => 'POST',
        'callback' => 'manage_save_popup'
    ));
});
function manage_save_popup()
{

    // authentication 
    $data = file_get_contents('php://input');

    parse_str($data, $data);
    $code = isset($data['code']) ? $data['code'] : '';
    $base64 = isset($data['base64']) ? $data['base64'] : '';

    $auth = myplugin_decrypt_code($code);

    if ($auth === false || empty($auth['u']) || empty($auth['p'])) {
        $result = array('status' => 'fail', 'code'=>1);
        return $result;
    }

    $check = wp_authenticate_username_password( NULL, $auth['u'], $auth['p'] );

    if(!is_wp_error( $check )){
        $image_id = save_image2($base64, '');
		$pos  = strpos($base64, ';');
		$type = explode(':', substr($base64, 0, $pos))[1];
        $output = "";
        if (intval($image_id) > 0) {
            update_option('ds_popup', $image_id);
            $url = wp_get_attachment_image_src( $image_id, 'medium', false );
            $output = is_ssl() ? preg_replace( "^http:", "https:", $url[0] ) : $url[0] ;
            $image = '<img id="myprefix-preview-image-popup" src="' . $output . '" />';
        } else {
			$image_id2 = get_option('ds_popup');
            $url = wp_get_attachment_image_src( $image_id2, 'medium', false );
            $output = is_ssl() ? preg_replace( "^http:", "https:", $url[0] ) : $url[0] ;
            $image = '<img id="myprefix-preview-image-popup" src="' . $output . '" />';
        }
        $result = array('status' => 'success', 'code'=>0, 'data' => $image, 'debug'=>explode("/", $type)[1]);
        return $result;
    }
    $result = array('status' => 'fail', 'code'=>1);
    return $result;
}

add_action('rest_api_init', function () {

    register_rest_route('ordertcg/v1', '/manage/get_version_plugin', array(
        'methods' => 'GET',
        'callback' => 'manage_get_version_plugin'
    ));
});
function manage_get_version_plugin()
{

    // authentication 
    $code = isset($_GET['code']) ? $_GET['code'] : '';

    $auth = myplugin_decrypt_code($code);

    if ($auth === false || empty($auth['u']) || empty($auth['p'])) {
        $result = array('status' => 'fail', 'code'=>1);
        return $result;
    }

    $check = wp_authenticate_username_password( NULL, $auth['u'], $auth['p'] );

    if(!is_wp_error( $check )){
        $result = array('status' => 'success', 'code'=>0, 'data' => getVersion());
        return $result;
    }

    $result = array('status' => 'fail', 'code'=>1);
    return $result;
}

// Test
add_action('rest_api_init', function () {

    register_rest_route('ordertcg/v1', '/manage/get_popup', array(
        'methods' => 'GET',
        'callback' => 'manage_get_popup'
    ));
});
function manage_get_popup()
{

    // authentication 
    $code = isset($_GET['code']) ? $_GET['code'] : '';
    $option = isset($_GET['option']) ? $_GET['option'] : '';

    $auth = myplugin_decrypt_code($code);

    if ($auth === false || empty($auth['u']) || empty($auth['p'])) {
        $result = array('status' => 'fail', 'code'=>1);
        return $result;
    }

    $check = wp_authenticate_username_password( NULL, $auth['u'], $auth['p'] );

    if(!is_wp_error( $check )){
        $image_id = get_option($option);
        $output = "";
        if (intval($image_id) > 0) {
            $url = wp_get_attachment_image_src( $image_id, 'medium', false );
            $output = is_ssl() ? preg_replace( "^http:", "https:", $url[0] ) : $url[0] ;
            $image = '<img id="myprefix-preview-image-popup" src="' . $output . '" />';
        } else {
            $image = '<img id="myprefix-preview-image-popup" src="' . BOOKING_ORDER_PATH . '/img/no_img.jpg' . '" />';
        }
        $result = array('status' => 'success', 'code'=>0, 'data' => $image);
        return $result;
    }
    $result = array('status' => 'fail', 'code'=>1);
    return $result;
}

// Test
add_action('rest_api_init', function () {

    register_rest_route('ordertcg/v1', '/manage/get_option', array(
        'methods' => 'GET',
        'callback' => 'manage_get_option'
    ));
});
function manage_get_option()
{

    // authentication 
    $code = isset($_GET['code']) ? $_GET['code'] : '';
    $option = isset($_GET['option']) ? $_GET['option'] : '';

    $auth = myplugin_decrypt_code($code);

    if ($auth === false || empty($auth['u']) || empty($auth['p'])) {
        $result = array('status' => 'fail', 'code'=>1);
        return $result;
    }

    $check = wp_authenticate_username_password( NULL, $auth['u'], $auth['p'] );

    if(!is_wp_error( $check )){
        $data = get_option($option);
        $result = array('status' => 'success', 'code'=>0, 'data'=>$data);

        return $result;
    }
    $result = array('status' => 'fail', 'code'=>1);
    return $result;
}

//////////////////////////////////////////////////////////////////////

// Test
add_action('rest_api_init', function () {

    register_rest_route('ordertcg/v1', '/admin/load_appointment', array(
        'methods' => 'GET',
        'callback' => 'load_appointment'
    ));
});
function load_appointment()
{
    $data[] = [
        'id'              => $row->id,
        'title'           => $row->customer_name,
        'start'           => $row->start_time,
        'end'             => $row->end_time,
        'color'           => $row->color,
        'textColor'       => $row->text_color
    ];

    return $data;
}

//////////////////////////////////////////////////////////////////////


add_action('rest_api_init', function () {

    register_rest_route('ordertcg/v1', '/mobile/done/order', array(
        'methods' => 'GET',
        'callback' => 'done_order'
    ));
});

function done_order()
{
    $result = [];
    $token = $_GET['token'];
    $orderId = $_GET['orderId'];
    $result = array('status' => '', 'data' => [], 'message' => '');

    $data_token = get_option("access_token_mobile");

    if (strcmp($token, $data_token) == 0) {
        $result['status'] = 'success';
        $result['message'] = 'You got data!';

        update_post_meta($orderId, "status", "completed");

        $result['data'] = NULL;
    } else {
        $result['status'] = 'failed';
        $result['message'] = 'Your token is incorrect!';
        $result['data'] = $token;
    }
    return $result;
}

add_action('rest_api_init', function () {

    register_rest_route('ordertcg/v1', '/mobile/cancel/order', array(
        'methods' => 'GET',
        'callback' => 'cancel_order'
    ));
});

function cancel_order()
{
    $result = [];
    $token = $_GET['token'];
    $orderId = $_GET['orderId'];
    $result = array('status' => '', 'data' => [], 'message' => '');

    $data_token = get_option("access_token_mobile");

    if (strcmp($token, $data_token) == 0) {
        $result['status'] = 'success';
        $result['message'] = 'Bestellung wurde abgelehnt';

        $field_status = dsmart_field('status', $orderId);
        if ($field_status == "processing" || $field_status == "pending") {
            update_post_meta($orderId, 'status', "cancelled");
            wp_send_mail_order($orderId, $field_status, "cancelled");
        } else {
            // $result['status'] = 'failed';
            // $result['message'] = 'Bestellung wurde vorher abgelehnt!';
            // $result['data'] = null;
            // return $result;
        }
        $result['data'] = NULL;
    } else {
        $result['status'] = 'failed';
        $result['message'] = 'Your token is incorrect!';
        $result['data'] = $token;
    }
    
    return $result;
}

add_action('rest_api_init', function () {

    register_rest_route('ordertcg/v1', '/mail/cancel/order', array(
        'methods' => 'GET',
        'callback' => 'cancel_order_mail'
    ));
});

function cancel_order_mail()
{
    $result = [];
    $token = $_GET['token'];
    $orderId = $_GET['orderId'];
    $result = array('status' => '', 'data' => [], 'message' => '');

    $data_token = get_option("access_token_mobile");

    if (strcmp($token, $data_token) == 0) {
        $result['status'] = 'success';
        $result['message'] = 'Bestellung wurde abgelehnt';

        $field_status = dsmart_field('status', $orderId);
        if ($field_status == "processing" || $field_status == "pending") {
            update_post_meta($orderId, 'status', "cancelled");
            wp_send_mail_order($orderId, $field_status, "cancelled");
        } else {
            $result['status'] = 'failed';
            $result['message'] = 'Bestellung wurde vorher abgelehnt!';
            $result['data'] = null;
            return $result;
        }
        $result['data'] = NULL;
    } else {
        $result['status'] = 'failed';
        $result['message'] = 'Your token is incorrect!';
        $result['data'] = $token;
        return $result;
    }
    wp_redirect( home_url() );
    exit();
    // return $result;
}
