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
    } else if (get_option('cloudbeds_sync_key')) {
        $key = get_option('cloudbeds_sync_key');
    }


    $endpoint = "$target_site/wp-json/cloudbeds/data";
    $query = http_build_query([
        'key' => $key
    ]);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $endpoint . '?' .  $query);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $res = json_decode(curl_exec($ch), true);
    curl_close($ch);

    if (!$res['cloudbeds_client_id']) {
        return false;
    } else {
        cloudbeds_set_option('cloudbeds_client_id', $res['cloudbeds_client_id']);
        cloudbeds_set_option('cloudbeds_client_secret', $res['cloudbeds_client_secret']);
        cloudbeds_set_option('cloudbeds_authorization_code', $res['cloudbeds_authorization_code']);
        cloudbeds_set_option('cloudbeds_access_token', $res['cloudbeds_access_token']);
        cloudbeds_set_option('cloudbeds_access_token_timestamp', $res['cloudbeds_access_token_timestamp']);
        cloudbeds_set_option('cloudbeds_refresh_token', $res['cloudbeds_refresh_token']);
        cloudbeds_set_option('cloudbeds_status', 'Syncing to Production');    
    }

    return $res;
}

function cloudbeds_sync_connect() {
    if (empty($_POST['target_website']) || empty($_POST['data_key']) ) {
        return false;
    }

    $website = filter_var($_POST['target_website'], FILTER_SANITIZE_STRING); 
    $key = filter_var($_POST['data_key'], FILTER_SANITIZE_STRING);

    if (filter_var($website, FILTER_VALIDATE_URL) == false) {
        return 'Website URL is incorrectly formed. Enter in the Site Address (URL) located under the target website general WordPress settings.';
    }
    
    $website = rtrim($website, "/");

    // Both website and key are present, attempt to connect
    $data = cloudbeds_import_data($website, $key);

    if ($data['cloudbeds_client_id']) {
        cloudbeds_set_option('cloudbeds_sync_website', $website);
        cloudbeds_set_option('cloudbeds_sync_key', $key);
        cloudbeds_set_option('cloudbeds_status', 'Syncing to Production');
        wp_redirect(CLOUDBEDS_ADMIN_URL);
    } else {
        cloudbeds_set_option('cloudbeds_status', 'Not Connected'); 
        return "Sync failed, double check the data key and website URL.";
    }
}