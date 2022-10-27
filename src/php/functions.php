<?php
/**
 * Retrieves all option data using CLOUDBEDS_DATA_KEYS.
 *
 * @return array Cloudbeds saved data.
 */
function cloudbeds_option_data() {
    $res = [];

    foreach (CLOUDBEDS_DATA_KEYS as $key) {
        $res[$key] = get_option($key);
    }

    return $res;
}

/**
 * Sets option data.
 *
 * @param string The name of the option.
 * @param string The value of the option.
 * @return void
 */
function cloudbeds_set_option($name, $value) {
    if (get_option($name) !== false) {
        update_option($name, $value);
    } else {
        add_option($name, $value, null, 'no');
    }
}

/**
 * Deletes all Cloudbeds option data.
 *
 * @return void
 */
function cloudbeds_reset() {
    foreach (CLOUDBEDS_DATA_KEYS as $key) {
        delete_option($key);
    }
}

/**
 * Activation callback for the Cloudbeds plugin.
 *
 * @return void
 */
function cloudbeds_activate() {
    if ( ! wp_next_scheduled('cloudbeds_cron') ) {
        wp_schedule_event(time(), 'thirty_minutes', 'cloudbeds_cron');
    }

    if (get_option('cloudbeds_data_key') !== false) {
        cloudbeds_set_option('cloudbeds_data_key', wp_generate_password(30, false));
    }
}

/**
 * Deactivation callback for the Cloudbeds plugin.
 *
 * @return void
 */
function cloudbeds_deactivate() {
    wp_unschedule_event(wp_next_scheduled('cloudbeds_cron'), 'cloudbeds_cron');
}

/**
 * WordPress cron event for Cloudbeds.
 *
 * @return void
 */
function cloudbeds_cron() {
    cloudbeds_check_access_token();
}

/**
 * Filter function for adding a 30 minute cron time interval.
 *
 * @param array $schedules Current WordPress cron schedules.
 * @return array Updated WordPress cron schedules.
 */
function cloudbeds_cron_schedules( $schedules ) { 
    $schedules['thirty_minutes'] = array(
        'interval' => 1800,
        'display'  => esc_html__( 'Every Thirty Minutes' ), );
    return $schedules;
}

/**
 * Filter function to add a link to the options page.
 *
 * @param array $actions Current actions set by WordPress.
 * @return array Updated actions with WordPress option page added.
 */
function cloudbeds_action_links($actions) {
    return array_merge([
        '<a href="' . CLOUDBEDS_ADMIN_URL . '">Settings</a>',
    ], $actions);
}

/**
 * Import data from a website that has the Cloudbeds plugin installed.
 * Primarily utilized for local development.
 *
 * @param string $target_site The target website.
 * @param string $key The Cloudbeds access token located on the target website's Cloudbeds dashboard.
 * @return void
 */
function cloudbeds_import_data($target_site, $key) {
    $endpoint = "$target_site/wp-json/cloudbeds/data";
    $query = http_build_query([
        'key' => $key
    ]);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $endpoint . '?' .  $query);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $res = json_decode(curl_exec($ch), true);
    curl_close($ch);

    return $res;
}