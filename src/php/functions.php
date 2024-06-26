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
 * Deactivation callback for the Cloudbeds plugin.
 *
 * @return void
 */
function cloudbeds_deactivate() {
    wp_unschedule_event(wp_next_scheduled('cloudbeds_cron'), 'cloudbeds_cron');

    // Delete table
    global $wpdb;
    $table_name = $wpdb->prefix . 'cloudbeds_cache';
    $wpdb->query("DROP TABLE IF EXISTS $table_name");
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
 * Primarily utilized for local and staging environments.
 *
 * @param string $target_site The target website.
 * @param string $key The Cloudbeds access token located on the target website's Cloudbeds dashboard.
 * @return void
 */
function cloudbeds_import_data($target_site = null, $key = null) {
    if (!$target_site && !get_option('cloudbeds_sync_website')) {
        return 'Missing target site.';
    } else if (get_option('cloudbeds_sync_website')) {
        $target_site = get_option('cloudbeds_sync_website');
    }

    if (!$key && !get_option('cloudbeds_sync_key')) {
        return 'Missing key.';
    } else if (!$key && get_option('cloudbeds_sync_key')) {
        $key = get_option('cloudbeds_sync_key');
    }

    $query = http_build_query([
        'key' => $key
    ]);

    $endpoint = "$target_site/wp-json/cloudbeds/data?$query";

    $res = wp_remote_get($endpoint);

    if (is_wp_error($res)) {
        return false;
    } else if ($res) {
        $res = json_decode(wp_remote_retrieve_body($res), true);
    }

    if (empty($res['cloudbeds_client_id'])) {
        return false;
    } else {
        $data = cloudbeds_option_data();

        // Check to ensure the production site has retreived a new access token before syncing or that the token has yet to exist locally
        if ($data['cloudbeds_access_token'] !== $res['cloudbeds_access_token'] || !$data['cloudbeds_access_token']) {
            // A new access token is available, sync all data
            cloudbeds_set_option('cloudbeds_client_id', $res['cloudbeds_client_id']);
            cloudbeds_set_option('cloudbeds_client_secret', $res['cloudbeds_client_secret']);
            cloudbeds_set_option('cloudbeds_authorization_code', $res['cloudbeds_authorization_code']);
            cloudbeds_set_option('cloudbeds_access_token', $res['cloudbeds_access_token']);
            cloudbeds_set_option('cloudbeds_access_token_timestamp', $res['cloudbeds_access_token_timestamp']);
            cloudbeds_set_option('cloudbeds_refresh_token', $res['cloudbeds_refresh_token']);
            cloudbeds_set_option('cloudbeds_status', 'Syncing to Production');    
        } else {
            // The target site still has the old access token, push the timestamp out and sync later
            cloudbeds_set_option('cloudbeds_access_token_timestamp', intval($data['cloudbeds_access_token_timestamp']) + 1800);
        }
    }

    return $res;
}

/**
 * Connects the form data set in the sync administration panel to the target website.
 *
 * @return void
 */
function cloudbeds_sync_connect() {
    if (empty($_POST['target_website']) || empty($_POST['data_key'] || empty($_POST['_wpnonce'])) ) {
        return false;
    }

    $website = sanitize_text_field($_POST['target_website']); 
    $key = sanitize_text_field($_POST['data_key']);
    $nonce = sanitize_text_field($_POST['_wpnonce']);

    if (!wp_verify_nonce($nonce, 'cloudbeds_sync')) {
        return 'Invalid nonce.';
    }

    if (filter_var($website, FILTER_VALIDATE_URL) == false) {
        return 'Website URL is incorrectly formed. Enter in the Site Address (URL) located under the target website general WordPress settings.';
    }
    
    $website = rtrim($website, "/");

    // Both website and key are present, attempt to connect
    $data = cloudbeds_import_data($website, $key);

    if (!$data) {
        return "Sync failed, double check the data key and website URL.";
    } else if ($data['cloudbeds_client_id']) {
        cloudbeds_set_option('cloudbeds_sync_website', $website);
        cloudbeds_set_option('cloudbeds_sync_key', $key);
        cloudbeds_set_option('cloudbeds_status', 'Syncing to Production');
        return "Sync successful!";
    }
}

/**
 * Sends an error email to the site administrator.
 */
function cloudbeds_admin_email($subject, $message) {
    if (get_option('cloudbeds_admin_email')) {
        $to = get_option('cloudbeds_admin_email');
        $headers = array('Content-Type: text/html; charset=UTF-8');
        wp_mail($to, $subject, $message, $headers);
    }
}