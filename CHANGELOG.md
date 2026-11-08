# Changelog

## 1.0.1.32
- Stack delivery and pickup time-window settings vertically while restoring the table layout for each service.

## 1.0.1.31
- Display special time-window rows as vertical setting cards to keep all controls visible.

## 1.0.1.30
- Added an active checkbox to each special time-window row so rules can be disabled without deleting them.

## 1.0.1.29
- Use a 24-hour time picker for special delivery and pickup time windows.

## 1.0.1.28
- Widened special time-window columns so complete times remain visible.

## 1.0.1.27
- Prevent all delivery or pickup card actions, including row deletion, while that service is off.

## 1.0.1.26
- Visually disable the full delivery or pickup settings card when its service is turned off.

## 1.0.1.25
- Merged preparation-time and time-interval overrides into one table per service; blank value cells leave that setting at its standard value.

## 1.0.1.24
- Moved popup settings into Bestellannahme; grouped standard values and weekday-specific time windows; disabled delivery and pickup settings when the service is off; arranged time settings and closing times side by side with compact tables and German “bis” labels.

## 1.0.1.23
- Simplified the delivery and pickup settings tab with grouped, collapsible sections, separate service cards, compact time-window editors, and optional help text.

## 1.0.1.22
- Added multiple daily custom time-interval windows for delivery and pickup, with fixed intervals used outside the windows and preparation boundaries preserved in frontend selections.

## 1.0.1.21
- Apply preparation times to available frontend time selections for delivery and pickup, using custom time windows and the fixed default elsewhere. Keep the selected order time unchanged and validate availability before checkout.

## 1.0.1.20
- Show close_shop_text when no enabled pickup or delivery service is currently open, including breaks and times before opening or after closing.
- Render the closing bar on product-category menu URLs as well as menu Pages.
- Apply weekly breaks within date-specific opening hours in Premium as well.

## 1.0.1.19
- Fixed invisible checkout field labels in the white theme while preserving dark-theme label colors.

## 1.0.1.18
- Added multiple daily preparation-time windows for delivery and pickup in both settings screens, overriding the fixed time during matching windows, including overnight periods.

## 1.0.1.17
- Keep coupon error notifications visible for at least eight seconds and prevent older popup timers from hiding newer notifications.

## 1.0.1.16
- Show the German minimum-order requirement when a coupon is applied below its configured order minimum.

## 1.0.1.15
- Made the shop header background follow the dark or white theme instead of the saved custom header color, including while sticky.

## 1.0.1.14
- Added an An/Aus radio setting to display category descriptions beneath category headings; disabled by default.

## 1.0.1.13
- Enabled announcement bar animation on desktop and mobile.
- Kept shop headers visible while scrolling on desktop and mobile.
- Added a mobile scroll-to-top button positioned above floating cart controls.

## 1.0.1.12
- Fixed duplicate worksheet-scoped filter definitions that could cause Excel to reject or repair multi-sheet exports.

## 1.0.1.11
- Improved Excel report overview, table readability and column widths; made linked charts compact with fixed dimensions.

## 1.0.1.10
- Restricted statistics and report export to administrators; client Shop Besitzer accounts no longer see or access reports.
- Added circle, column and line diagrams, with editable Excel charts linked to exported worksheet data.

## 1.0.1.9
- Added product statistics, date filters, sorting, time and day analysis, and formatted Excel export from shop settings.
