<?php global $wp_query;
$dsmart_thumbnail = get_option('dsmart_thumbnail');
$dsmart_horizontal_menu = get_option('dsmart_horizontal_menu');
$dsmart_stock = get_option('dsmart_stock');
$current_term = get_queried_object();

$tax_query = array();
$meta_query = array();
if (isset($_GET['price']) && $_GET['price'] != "") {
    $price_input = $_GET['price'];
    $price_arr = explode(';', $price_input);
    $dsmart_currency = get_option('dsmart_currency');
    if ($dsmart_currency == "2") {
        $currency = "€";
    } else {
        $currency = "$";
    }
    $dsmart_currency_rate = get_option('dsmart_currency_rate');
    if ($dsmart_currency_rate != "") {
        $currency_rate = floatval($dsmart_currency_rate);
    } else {
        $currency_rate = 1;
    }
    if ($currency == "$") {
    } else {
        $price_arr[0] = $price_arr[0] / $currency_rate;
        $price_arr[1] = $price_arr[1] / $currency_rate;
    }
    $meta_query[] = array('key'  => 'price', 'value' => $price_arr, 'compare'   => 'BETWEEN', 'type' => 'NUMERIC');
} else {
    $price_input = "";
}
if (isset($_GET['rating']) && $_GET['rating'] != "") {
    $input_rating = $_GET['rating'];
    $meta_query[] = array(
        'key'  => 'avg_rating',
        'value'     => $input_rating,
        'compare' => '>=',
        'type' => 'NUMERIC'
    );
} else {
    $status = "";
}

if (count($meta_query) > 0) {
    $meta_query['relation'] = 'AND';
} else {
    $meta_query = null;
}
$order = get_option('dsmart_order');
if ($order == "") {
    $order = "DESC";
}
$orderby = get_option('dsmart_orderby');
if ($orderby == "") {
    $orderby = "date";
}
$dsmart_taxonomy_text = get_option('dsmart_taxonomy_text');
$show_notify = get_option('show_notify');
$notify_text = get_option('notify_text');

$button_color = get_option('button_color', '#50aecc');
$sidebar_color = get_option('sidebar_color', '#ff8000');
$price_color = get_option('price_color', '#b28e2d');

$check_time_open1 = true;
$check_time_open2 = true;
$time1 = get_close_time_shop_nodelay();
$time2 = get_close_time_shop2_nodelay();
$now1 = new DateTime(get_current_time());
$now2 = new DateTime(get_current_time2());
if (count($time1) > 0) {
    foreach ($time1 as $value) {
        $begin = new DateTime($value[0]);
        $end = new DateTime($value[1]);
        if($now1 > $begin && $now1 < $end){
            $check_time_open1 = false;
        }
    }
    foreach ($time2 as $value) {
        $begin = new DateTime($value[0]);
        $end = new DateTime($value[1]);
        if ($now2 > $begin && $now2 < $end) {
            $check_time_open2 = false;
        }
    }
}
?>
<style>
body.dark-style .category_single{
    border: solid 1px rgb(255, 82, 82);
    background-color: black;
    color: white !important;
}
.category_single{
    border-bottom: solid 1px #dfdfdf;
    padding: 10px;
    font-weight: 500;
    color: black !important;
    width: 100%;
    height: 100px;
}

.category_single.active{
    background-color: rgb(255, 82, 82) !important;
    color: white !important;
}

body.dark-style .category_single:hover{
    background-color: rgb(255, 82, 82) !important;
    color: white !important;
}
.category_single:hover{
    background-color: <?php echo $button_color ?> !important;
    color:white !important;
}

.menu-categories li.active a,.menu-categories li a:hover{
    border-bottom: solid 2px #fff;
    display: inline-block;
    transition: 0.5s;
}
</style>
<?php
get_header(); 

if (get_option('homepage_popup') === "2"){
    $image_id = get_option('ds_popup_homepage');
    $output = "";
    if (intval($image_id) > 0) {
        $url = wp_get_attachment_image_src($image_id, 'full', false);
        $output = $url[0];
    } else {
        $output = plugins_url('img/default-closed-popup.jpeg', __FILE__);
    }
?>
    <div id="booking_modal2" class="booking_modal">

    <!-- The Close Button -->
    <span class="close">&times;</span>

    <!-- Modal Content (The Image) -->
    <img style="margin-top: 10%;" class="modal-content" id="img01" src="<?php echo $output; ?>">
    <!-- Modal Caption (Image Text) -->
    <div id="caption"></div>
    </div>
<?php
}

?>

<?php if (strcmp(get_option('dsmart_close_shop'), "on") == 0) {
    
    $image_id = get_option('ds_popup');
    $output = "";
    if (intval($image_id) > 0) {
        $url = wp_get_attachment_image_src($image_id, 'full', false);
        $output = $url[0];
    } else {
        $output = plugins_url('img/default-closed-popup.jpeg', __FILE__);
    }
?>
    <div id="booking_modal" class="booking_modal">

        <!-- The Close Button -->
        <span class="close">&times;</span>

        <!-- Modal Content (The Image) -->
        <img style="margin-top: 10%;" class="modal-content" id="img01" src="<?php echo $output; ?>">
        <!-- Modal Caption (Image Text) -->
        <div id="caption"></div>
    </div>

<?php
}
?>


<div class="tcg-container">
<?php if ($show_notify == "on" && $notify_text != "") : ?>
        <div class="hihi">
            <div class="shop-notify">
                <!-- <marquee onmouseover="this.stop();" onmouseout="this.start();"><?php echo $notify_text; ?></marquee> -->
                <div class="marquee-parent">
                    <div class="marquee-child">
                        <?php echo $notify_text; ?>
                    </div>
                </div>
            </div>
        </div>

    <?php endif; ?>

        <div class="main-container">
            <div class="mySideBar" id="mySideBar">
            <a href="javascript:void(0)" style="color: <?php echo $button_color?> !important;" class="closebtn" onclick="closeNav()">&times;</a>
                <div class="menu-categories">
                    <?php $terms = get_terms(array(
                        'taxonomy' => 'product-cat',
                        'hide_empty' => false,
                        'parent' => 0
                    ));
                    if ($terms) : ?>
                        <ul class="menu-category-list">
                            <?php foreach ($terms as $term) {
                                if (check_category_open_or_not($term->term_id)) {
                                    if ($current_term->term_id == $term->term_id) {
                                        $class = "active";
                                    } else {
                                        $class = "";
                                    } ?>
                                    <li id="big_menu_side_<?php echo $term->term_id ?>" class="big_menu_side <?php echo $class; ?>"><a href="#" onclick="roll_to_cat(<?php echo $term->term_id ?>)" class=" menu-category <?php echo $class; ?>"><?php echo $term->name; ?></a></li>
                            <?php }
                            } ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>
            <div class="main-body" id="main-body">
                <?php
                    $header_image_id = get_option('ds_header_image');
                    $header_image_url = "";
                    if (intval($header_image_id) > 0) {
                        $url = wp_get_attachment_image_src($header_image_id, 'full', false);
                        $header_image_url = $url[0];
                    } 

                    $header_color = get_option('header_color', "");

                    $style = "";
                    if($header_image_url != "")
                    {
                        $style = "background-image: url('".$header_image_url."') !important;";
                    }
                    else if($header_color != "")
                    {
                        $style = "background-color: " . $header_color ." !important";
                    }
                ?>
                <div class="header" style="<?php echo $style;?>">
                    <div class="row">
                        <span id="openNavBtn" style="font-size:30px;cursor:pointer;margin-left: 7%;color: <?php echo $sidebar_color?> !important;" onclick="openNav()">&#9776;</span>
                        <?php $image_id = get_option('ds_logo');
                                if ($image_id != null) {
                                    $image = wp_get_attachment_image_src($image_id, 'full'); 
                                }?>
                        <a class="logo-header" href="<?php echo get_option('logo_link')?>"><img src="<?php echo $image[0]; ?>" alt="<?php _e("Logo", 'dsmart') ?>" /></a>    
                        <?php
                            $cart_id = get_page_id_by_template('templates/cart-page.php');
                            $quantity_circle_color = get_option('quantity_circle_color', 'black');
                        ?>
                        <div class="cart-info">
                            <i style="font-size:30px;cursor:pointer;color: <?php echo $sidebar_color?> !important;" onclick="window.location='<?php echo get_permalink($cart_id);?>'" class="fa fa-shopping-cart" aria-hidden="true"></i>
                            <span style="background-color: <?php echo  $quantity_circle_color;?> !important;"><?php $total_quantity = ds_get_real_cart_total_quantity(); echo $total_quantity; ?></span>
                        </div>
                    </div>
                    <?php if($dsmart_horizontal_menu == '1') {?>
                    <div class="horizontal-category">
                        <ul class="owl-carousel">
                            <?php $terms = get_terms(array(
                                    'taxonomy' => 'product-cat',
                                    'hide_empty' => false,
                                    'parent' => 0
                                ));
                                
                                if ($terms) : ?>
                                    <?php foreach ($terms as $term) {
                                        if (check_category_open_or_not($term->term_id)) {
                                            if ($current_term->term_id == $term->term_id) {
                                                $class = "active";
                                            } else {
                                                $class = "";
                                            } ?>
                                            <li onclick="roll_to_cat(<?php echo $term->term_id ?>)" class="item horizontal-menu-item <?php echo $class?>" id="horizontal-menu-item-<?php echo $term->term_id;?>"><?php echo $term->name;?></li>
                                    <?php }
                                    } ?>
                                <?php endif; ?>
                        </ul>
                    </div>
                    <?php } ?>
                </div>

                <div class="menu-list">
                    <div class="menu-meals">
                    <?php foreach ($terms as $term) {
                        if (check_category_open_or_not($term->term_id)) {
                            $tax_query = [];
                            $tax_query[] = array(
                                'taxonomy'  => 'product-cat',
                                'field'     => 'term_id',
                                'terms'     => $term->term_id,
                            );
                            // $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
                            $wp_query = new WP_Query(array(
                                'post_type' => 'product',
                                'orderby' => $orderby,
                                'order' => $order,
                                'post_status'    => 'publish',
                                'posts_per_page' => -1,
                                'paged' => $paged,
                                'tax_query' => $tax_query,
                                'meta_query' => $meta_query
                            ));
                            if ($wp_query->have_posts()) : $theme =get_option('dsmart_theme_style');?>
                                <div>
                                    <p style="color:rgba(255, 255, 255, 0);">-</p>
                                </div>
                                <div class="catcat" id="link_term_<?php echo $term->term_id ?>">
                                    <input type="hidden" name="id" value="<?php echo $term->term_id ?>">
                                    <div>
                                        <h2 class="change_when_scroll"><?php echo $term->name ?></h2>
                                    </div>
                                </div>
                                <!-- ?ao_noptirocket=1 -->
                                <div class="list-product">
                                    <?php while ($wp_query->have_posts()) : the_post();
                                        if (has_post_thumbnail() && $dsmart_thumbnail != "1") :
                                            $url_img =  wp_get_attachment_url(get_post_thumbnail_id(get_the_ID()));
                                            $class_img = ($url_img == "") ? "no-img" : "";
                                        else :
                                            $url_img = "";
                                            $class_img = "no-img";
                                        endif;
                                        $array_name = array();
                                        $post_terms = wp_get_post_terms(get_the_ID(), 'product-cat');
                                        if ($post_terms) {
                                            foreach ($post_terms as $item) {
                                                $array_name[] = $item->name;
                                            }
                                        }
                                        $ds_status_product = dsmart_field('status');
                                        $sku = dsmart_field('sku');
                                        $pro_status = dsmart_field('status');
                                        $desc = dsmart_field('desc', get_the_ID());
                                        $sidedish_text = dsmart_field('sidedish_text', get_the_ID());
                                        if($sidedish_text == null || $sidedish_text == '')
                                        {
                                            $sidedish_text = "Beilage";
                                        }
                                        $sharp = intval(dsmart_field('sharp', get_the_ID()));
                                        $price = dsmart_field('price');
                                        $vegetarian = dsmart_field('vegetarian');
                                        $type_promotion = dsmart_field('type_promotion');

                                        $meta['quantity'] = dsmart_field('quantity');
                                        $meta['varialbe_price'] = dsmart_field('varialbe_price');

                                        $meta['extra_name'] = dsmart_field('extra_name');
                                        $meta['extra_type'] = dsmart_field('extra_type');
                                        $meta['extra_price'] = dsmart_field('extra_price');

                                        $meta['sidedish_name'] = dsmart_field('sidedish_name');
                                        $meta['sidedish_price'] = dsmart_field('sidedish_price');
                                        if ($meta['varialbe_price'] != null && !empty(array_filter($meta['varialbe_price']))) :
                                            $price = $meta['varialbe_price'][0];
                                        endif;
                                        if ($meta['sidedish_price'] != null && !empty(array_filter($meta['sidedish_price']))) :
                                            $price = floatval($price) + floatval($meta['sidedish_price'][0]);
                                        endif;

                                        $image_size = get_option('image_size', "220");
                                        $style = "width: " . $image_size . "px !important";
                                    ?>
                                        <div class="item <?php echo $class_img; ?>" style="<?php echo $style;?>">
                                            <?php
                                            $isExtra = $meta['extra_name'] != null && !empty(array_filter($meta['extra_name'])) && $meta['extra_price'] != null && !empty(array_filter($meta['extra_price']));
                                            $isSidedish = $meta['sidedish_name'] != null && !empty(array_filter($meta['sidedish_name']));
                                            $isVariable = $meta['quantity'] != null &&  !empty(array_filter($meta['quantity'])) && $meta['varialbe_price'] != null && !empty(array_filter($meta['varialbe_price']));
                                            if ($url_img != "" && $dsmart_thumbnail != "1") : ?><span class="thumb"><img loading="lazy" style="loading: lazy;" src="<?php echo $url_img; ?>" alt="<?php the_title(); ?>"></span><?php endif; ?>
                                            <div class="desc">
                                                <div class="content-wrap rowct">
                                                    <div class="left-item">
                                                        <h3 class="title">
                                                            <?php the_title(); ?>
                                                            <?php if ($sharp != 0) : ?><span class="sharp"><?php
                                                                    for ($i = 1; $i <= $sharp; $i++) { ?>
                                                                        <img src="<?php echo BOOKING_ORDER_PATH . 'img/chili.png'; ?>" alt="" />
                                                                    <?php } ?></span><?php endif; ?>
                                                            <?php if ($vegetarian == '1') : ?>
                                                                <span class="leaf"><img src="<?php echo BOOKING_ORDER_PATH . 'img/leaf.png'; ?>" alt="" /></span>
                                                            <?php endif; ?>
                                                        </h3>
                                                        <?php if ($desc != '') : ?>
                                                                <sup style="color: white !important;font-size:13px">(<?php echo $desc; ?>)</sup>
                                                            <?php endif; ?>
                                                        <div class="excerpt"><?php echo get_the_excerpt(); ?></div>
                                                    </div>
                                                    <div class="right-item">
                                                        <?php if (($dsmart_stock == "1" || ($dsmart_stock != "1" && $ds_status_product == "instock")) && $price != "") :
                                                            if ($isExtra || $isVariable || $isSidedish ) : 
                                                                if(!$isVariable){ ?>
                                                                    <div class="price"><span style="color:<?php echo $price_color ?> !important"><?php echo ds_price_format_text($price); ?></span></div>
                                                                <?php }?>
                                                                <button type="button" class="add-to-cart-copy option_selection_btn" style="color: <?php echo $price_color?> !important;border-color: <?php echo $button_color?> !important;" data-id="<?php the_ID(); ?>">OPTIONEN AUSWÄHLEN</button>
                                                            <?php else : ?>
                                                                <!-- <div class="price"><span style="color:<?php echo $price_color ?> !important"><?php echo ds_price_format_text($price); ?></span></div> -->
                                                                <button type="button" class="add-to-cart" style="color: <?php echo $price_color?> !important;border-color: <?php echo $button_color?> !important;" data-id="<?php the_ID(); ?>"><?php echo ds_price_format_text($price); ?></button>
                                                            <?php endif; ?>
                                                        <?php else : ?> 
                                                            <button type="button" class="add-to-cart" style="color: <?php echo $price_color?> !important;border-color: <?php echo $button_color?> !important;pointer-events: none;" data-id="<?php the_ID(); ?>">Ausverkauft</button>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                                
                                                <?php 

                                                if (($dsmart_stock == "1" || ($dsmart_stock != "1" && $ds_status_product == "instock")) && $price != "" && ($isExtra || $isVariable || $isSidedish)) : ?>
                                                    <div class="variable-product" id="variable_<?php echo the_ID();?>" style="display:none;">
                                                        <div class="inner">
                                                            <div class="modal-box">
                                                                <div style="display: flex;gap:10px">
                                                                    <p class="title"><?php the_title(); ?></p>
                                                                    <?php if ($sharp != 0) : ?><span class="sharp"><?php
                                                                            for ($i = 1; $i <= $sharp; $i++) { ?>
                                                                                <img src="<?php echo BOOKING_ORDER_PATH . 'img/chili.png'; ?>" alt="" />
                                                                            <?php } ?></span><?php endif; ?>
                                                                    <?php if ($vegetarian == '1') : ?>
                                                                        <span class="leaf"><img src="<?php echo BOOKING_ORDER_PATH . 'img/leaf.png'; ?>" alt="" /></span>
                                                                    <?php endif; ?>
                                                                    <?php if (!(($dsmart_stock == "1" || ($dsmart_stock != "1" && $ds_status_product == "instock")) && $price != "")) {
                                                                        echo '<span class="no-item">Ausverkauft</span>';
                                                                    } ?>
                                                                </div>
                                                                <?php if ($desc != '') : ?>
                                                                    <sup style="font-size: 12px;">(<?php echo $desc; ?>)</sup>
                                                                <?php endif; ?>
                                                            </div>
                                                            <div class="excerpt"><?php echo get_the_excerpt(); ?></div>
                                                            <div class="close close-variable" >&times;</div>
                                                            <div class="content">
                                                                <div class="choose-variable">
                                                                    <form class="Variable-form">
                                                                    <input type="hidden" name="origin_price" value="<?php echo dsmart_field('price');?>">
                                                                    <?php
                                                                        if ($isSidedish) :
                                                                            echo '<div class="sidedish-product">';
                                                                            echo '<h5 class="sidedish-title">'.$sidedish_text.':</h5>';
                                                                            foreach ($meta['sidedish_name'] as $key => $sidedish) {
                                                                                if ($meta['sidedish_name'][$key] != null) :
                                                                        ?>      
                                                                                    <label class="custom-radio-checkbox rowct">
                                                                                        <input type="radio" name="sidedish_product" <?php echo (($key === 0) ? "checked" : "");?> data-id="<?php echo get_the_ID() . '_' . ($key + 1) . '_sidedish'; ?>" data-quantity="1" data-price="<?php echo $meta['sidedish_price'][$key]; ?>">
                                                                                        <span class="text"><?php echo $meta['sidedish_name'][$key] . (isset($meta['sidedish_price'][$key]) && $meta['sidedish_price'][$key] !== "" ? " (+".ds_price_format_text($meta['sidedish_price'][$key]).")" : ""); ?></span>
                                                                                    </label>
                                                                        <?php endif;
                                                                            }
                                                                            echo '<input type="hidden" name="sidedish_info" value=""/>';
                                                                            echo '</div>';
                                                                        endif;
                                                                        if ($isExtra) :
                                                                            echo '<div class="extra-product">';
                                                                            echo '<h5 class="extra-title">Ihre Extras:</h5>';
                                                                            foreach ($meta['extra_name'] as $key => $extra) {
                                                                                $extra_type = dsmart_field('extra_type' . ($key + 1));
                                                                                if ($meta['extra_name'][$key] != null && $meta['extra_price'][$key] != null) :
                                                                        ?>
                                                                                    <label class="custom-radio-checkbox rowct">
                                                                                        <input class="ccenter" type="checkbox" name="extra_product" data-id="<?php echo get_the_ID() . '_' . ($key + 1) . '_extra'; ?>" data-quantity="<?php echo ($extra_type == 'tick') ? 1 : ''; ?>" data-price="<?php echo $meta['extra_price'][$key]; ?>">
                                                                                        <span class="text"><?php echo $meta['extra_name'][$key] . ' (+' . ds_price_format_text($meta['extra_price'][$key]) . ')'; ?></span>
                                                                                        <?php if ($extra_type != 'tick') : ?>
                                                                                            <div class="extra-quantity quantity-wrap flex-list">
                                                                                                <button type="button" class="minus"><i class="fa fa-minus" aria-hidden="true"></i></button>
                                                                                                <input type="number" name="quantity" class="form-control input-quantity" value="1" min="1">
                                                                                                <button type="button" class="plus"><i class="fa fa-plus" aria-hidden="true"></i></button>
                                                                                            </div>
                                                                                        <?php endif; ?>
                                                                                    </label>
                                                                        <?php endif;
                                                                            }
                                                                            echo '<input type="hidden" name="extra_info" value="" />';
                                                                            echo '</div>';
                                                                        endif; 
                                                                        if ($isVariable) : 
                                                                        ?>
                                                                        <div class="select-wrap">
                                                                            <select class="variable-select nice-select-custom">
                                                                                <?php foreach ($meta['quantity'] as $index => $value) {
                                                                                    if ($meta['quantity'][$index] != null && $meta['varialbe_price'][$index] != null) : ?>
                                                                                        <option value="<?php echo get_the_ID() . '_' . ($index + 1) . '_variable'; ?>" data-price="<?php echo $meta['varialbe_price'][$index]; ?>" data-id="<?php echo get_the_ID() . '_' . ($index + 1) . '_variable'; ?>">
                                                                                            <?php echo $meta['quantity'][$index] . ': ' . ds_price_format_text($meta['varialbe_price'][$index]); ?>
                                                                                        </option>
                                                                                <?php endif;
                                                                                } ?>
                                                                            </select>
                                                                        </div>
                                                                        <?php endif; ?>
                                                                        <div class="quantity-submit flex-list">
                                                                            <div class="total-quantity quantity-wrap flex-list">
                                                                                <button type="button" class="minus"><i class="fa fa-minus" aria-hidden="true"></i></button>
                                                                                <input type="number" name="quantity" class="form-control input-quantity" value="1" min="1">
                                                                                <button type="button" class="plus"><i class="fa fa-plus" aria-hidden="true"></i></button>
                                                                            </div>
                                                                            <?php 
                                                                            if ($isVariable){
                                                                                $price2 = $meta['varialbe_price'][0];
                                                                            }
                                                                            else
                                                                            {
                                                                                $price2 = dsmart_field('price');
                                                                            }
                                                                            if ($meta['sidedish_price'] != null && !empty(array_filter($meta['sidedish_price']))) :
                                                                                $price2 = floatval($price2) + floatval($meta['sidedish_price'][0]);
                                                                            endif;
                                                                            ?>
                                                                            <button type="submit" class="add-to-cart" style="color: <?php echo $button_color?> !important;border-color: <?php echo $button_color?> !important;"  data-id="<?php the_ID(); ?>"><?php  echo ds_price_format_text($price2) ?></button>
                                                                        </div>
                                                                    </form>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endwhile;
                                    echo get_the_posts_pagination(array('mid_size' => 2, 'prev_text' => __('Bisherige', "dsmart"), 'next_text' => __('Nächste', "dsmart")));
                                    wp_reset_query(); ?>

                                </div>
                            <?php else : ?>

                            <?php endif; ?>
                    <?php
                        }
                    }
                    ?>
                    <?php if ($dsmart_taxonomy_text != "") : ?><div class="more-text">
                            <?php echo $dsmart_taxonomy_text; ?>
                        </div><?php endif; ?>
                </div>
                </div>
            </div>
        </div>
</div>
<div class="book-loading"><img src="<?php echo plugin_dir_url(__FILE__) . 'img/loading.gif'; ?>"></div>
<?php get_footer(); ?>