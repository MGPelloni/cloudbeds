<?php
/**
 * Registers an REST route to submit the Client ID and Client Secret from WordPress admin.
 * 
 * @return void
 */
function cloudbeds_route_connect(){
    register_rest_route( 'cloudbeds', '/connect/', array(
        'methods' => 'POST',
        'callback' => 'cloudbeds_connect',
        'permission_callback' => '__return_true'
    ));
}

/**
 * Registers an REST route to receive the authorization code from Cloudbeds.
 *
 * @link https://hotels.cloudbeds.com/api/docs/#api-Authentication-oauth
 * @return void
 */
function cloudbeds_route_auth(){
    register_rest_route( 'cloudbeds', '/auth/', array(
        'methods' => 'GET',
        'callback' => 'cloudbeds_auth',
        'permission_callback' => '__return_true'
    ));
}

/**
 * Registers an REST route to access Cloudbeds data via API key.
 *
 * @return void
 */
function cloudbeds_route_data(){
    register_rest_route( 'cloudbeds', '/data/', array(
        'methods' => 'GET',
        'callback' => 'cloudbeds_data',
        'permission_callback' => '__return_true'
    ));
}

/**
 * Registers an REST route to submit misc data from WordPress admin.
 *
 * @return void
 */
function cloudbeds_route_settings(){
    register_rest_route( 'cloudbeds', '/settings/', array(
        'methods' => 'POST',
        'callback' => 'cloudbeds_settings',
        'permission_callback' => '__return_true'
    ));
}

/**
 * Callback function for /wp-json/cloudbeds/connect/ route.
 * 
 * Registers the Client ID and Secret to the WordPress database. Once logged, the user is forwarded
 * to Cloudbeds to log in and retrieve the authorization code.
 *
 * @return void
 */
function cloudbeds_connect() {
    if (empty($_POST['_wpnonce'])) {
        wp_send_json_error(new WP_Error('500', 'Missing nonce.'));
    }

    if (empty($_POST['cloudbeds_client_id'])) {
        wp_send_json_error(new WP_Error('500', 'Missing client ID.'));
    } 

    if (empty($_POST['cloudbeds_client_secret'])) {
        wp_send_json_error(new WP_Error('500', 'Missing client secret.'));
    }

    $client_id = filter_var($_POST['cloudbeds_client_id'], FILTER_SANITIZE_STRING);
    $client_secret = filter_var($_POST['cloudbeds_client_secret'], FILTER_SANITIZE_STRING);
    $nonce = filter_var($_POST['_wpnonce'], FILTER_SANITIZE_STRING);
   
    if (!wp_verify_nonce($nonce, 'wp_rest')) {
        wp_send_json_error(new WP_Error('500', 'Invalid nonce.'));
    }

    cloudbeds_set_option('cloudbeds_client_id', $client_id);
    cloudbeds_set_option('cloudbeds_client_secret', $client_secret);
    cloudbeds_set_option('cloudbeds_state', $nonce);
    cloudbeds_get_authorization_code($client_id, $nonce);
    exit;
}

/**
 * Callback function for /wp-json/cloudbeds/auth/ route.
 * Saves the authorizaton code sent after Cloudbeds login authentication.
 *
 * @return void
 */
function cloudbeds_auth() {
    if (empty($_GET['state'])) {
        wp_send_json_error(new WP_Error('500', 'Missing nonce.'));
    }

    if (empty($_GET['code'])) {
        wp_send_json_error(new WP_Error('500', 'Missing authorization code.'));
    }

    $state = filter_var($_GET['state'], FILTER_SANITIZE_STRING); 
    $code = filter_var($_GET['code'], FILTER_SANITIZE_STRING);

    if ($state !== get_option('cloudbeds_state')) {
        wp_send_json_error(new WP_Error('500', 'Invalid nonce.'));
    }

    if (CLOUDBEDS_DEBUG) {
        cloudbeds_log("Retrieved authorization code from Cloudbeds: $code");
    }

    cloudbeds_set_option('cloudbeds_authorization_code', $code);
    cloudbeds_set_option('cloudbeds_status', 'Connected');
    wp_redirect(CLOUDBEDS_ADMIN_URL);
    exit;
}

/**
 * Callback function for /wp-json/cloudbeds/data/ route.
 * Returns all Cloudbeds data.
 *
 * @return void
 */
function cloudbeds_data() {
    if (empty($_GET['key'])) {
        wp_send_json_error(new WP_Error('500', 'Missing key.'));
    }

    $key = filter_var($_GET['key'], FILTER_SANITIZE_STRING);
    $data = cloudbeds_option_data();

    if ($key !== $data['cloudbeds_data_key']) {
        wp_send_json_error(new WP_Error('500', 'Incorrect key value.'));
    }

    echo wp_json_encode($data); 
    exit;
}


/**
 * Callback function for /wp-json/cloudbeds/settings/ route.
 * 
 * Registers settings to the WordPress database.
 *
 * @return void
 */
function cloudbeds_settings() {
    if (empty($_POST['_wpnonce'])) {
        wp_send_json_error(new WP_Error('500', 'Missing nonce.'));
    }

    $admin_email = filter_var($_POST['cloudbeds_admin_email'], FILTER_SANITIZE_STRING);
    $nonce = filter_var($_POST['_wpnonce'], FILTER_SANITIZE_STRING);
   
    if (!wp_verify_nonce($nonce, 'wp_rest')) {
        wp_send_json_error(new WP_Error('500', 'Invalid nonce.'));
    }

    cloudbeds_set_option('cloudbeds_admin_email', $admin_email);
    wp_redirect(CLOUDBEDS_ADMIN_SETTINGS_URL);
    exit;
}