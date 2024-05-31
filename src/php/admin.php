<?php
/**
 * Initialization callback for the Cloudbeds plugin.
 */
function cloudbeds_admin_init() {
    if (!wp_next_scheduled('cloudbeds_cron')) {
        wp_schedule_event(time(), 'thirty_minutes', 'cloudbeds_cron');
    }

    if (!get_option('cloudbeds_data_key')) {
        cloudbeds_set_option('cloudbeds_data_key', wp_generate_password(30, false));
    }
    
    if ( !cloudbeds_cache_table_exists() ) {
        cloudbeds_cache_create_table();
    }
}

/**
 * Enqueues styles for Cloudbeds admin pages.
 * 
 * @link https://developer.wordpress.org/themes/basics/including-css-javascript/
 * @return void
 */
function cloudbeds_admin_styles() {
    wp_register_style( 'cloudbeds', CLOUDBEDS_PLUGIN_URL . 'dist/cloudbeds.min.css', [], filemtime(CLOUDBEDS_PLUGIN_PATH . 'dist/cloudbeds.min.css') );
    wp_enqueue_style( 'cloudbeds' );
}

/**
 * Creates an option page.
 * 
 * @link https://codex.wordpress.org/Creating_Options_Pages
 * @return void
 */
function cloudbeds_admin_custom_menu() {
    add_submenu_page('options-general.php', 'Cloudbeds', 'Cloudbeds', 'manage_options', 'cloudbeds', 'cloudbeds_admin_options_page', 58);
    add_submenu_page('options.php', 'Cloudbeds Sync', 'Cloudbeds Sync', 'manage_options', 'cloudbeds-sync', 'cloudbeds_admin_sync_page', 58);
    add_submenu_page('options.php', 'Cloudbeds Cache', 'Cloudbeds Cache', 'manage_options', 'cloudbeds-cache', 'cloudbeds_admin_cache_page', 58);
    add_submenu_page('options.php', 'Cloudbeds Settings', 'Cloudbeds Settings', 'manage_options', 'cloudbeds-settings', 'cloudbeds_admin_settings_page', 58);
}

/**
 * Callback function for the options page.
 * 
 * @link https://codex.wordpress.org/Creating_Options_Pages
 * @return void
 */
function cloudbeds_admin_options_page() {
    require_once(CLOUDBEDS_PLUGIN_PATH . 'src/templates/admin.php');
}

/**
 * Callback function for the sync page.
 * 
 * @link https://codex.wordpress.org/Creating_Options_Pages
 * @return void
 */
function cloudbeds_admin_sync_page() {
    require_once(CLOUDBEDS_PLUGIN_PATH . 'src/templates/sync.php');
}

/**
 * Callback function for the cache page.
 * 
 * @link https://codex.wordpress.org/Creating_Options_Pages
 * @return void
 */
function cloudbeds_admin_cache_page() {
    require_once(CLOUDBEDS_PLUGIN_PATH . 'src/templates/cache.php');
}

/**
 * Callback function for the settings page.
 * 
 * @link https://codex.wordpress.org/Creating_Options_Pages
 * @return void
 */
function cloudbeds_admin_settings_page() {
    require_once(CLOUDBEDS_PLUGIN_PATH . 'src/templates/settings.php');
}


/**
 * Registers WordPress option settings.
 * 
 * @link https://codex.wordpress.org/Creating_Options_Pages#Register_Settings
 * @return void
 */
function cloudbeds_admin_register_settings() {
    foreach (CLOUDBEDS_DATA_KEYS as $key) {
        register_setting('cloudbeds', $key);
    }
}

/**
 * Logs data to the logs folder.
 *
 * @param string $data The data to be logged.
 * @return void
 */
function cloudbeds_log($data) {
    if (CLOUDBEDS_DEBUG !== true || !is_dir(CLOUDBEDS_LOGS)) {
        return;
    }

    $content = $data;
    $date = wp_date('mdy');
    $path = CLOUDBEDS_LOGS . '/' . $date . '.txt';

    if (!file_exists($path)) {
        touch($path);
    }

    $file = fopen($path, "a") or die("Unable to open file!");
    fwrite($file, $content . PHP_EOL);
    fclose($file);
}
