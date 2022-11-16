<?php
/**
 * Checks if the expiration time has been reached to retrieve access and refresh tokens.
 * Action hook is attached to WordPress 'init' and a WordPress cron event (#cloudbeds_cron).
 *
 * @return void
 */
function cloudbeds_check_access_token() {
    $data = cloudbeds_option_data();

    if ($data['cloudbeds_status'] == "Connected") {
        // Check if a new access token is needed
        if ($data['cloudbeds_client_id'] && $data['cloudbeds_client_secret'] && $data['cloudbeds_authorization_code']) {
            if (!$data['cloudbeds_access_token'] || time() > intval($data['cloudbeds_access_token_timestamp']) + 1800) {
                cloudbeds_get_access_token($data['cloudbeds_client_id'], $data['cloudbeds_client_secret'], $data['cloudbeds_authorization_code']);
            }
        }
    } else if ($data['cloudbeds_status'] == "Syncing to Production") {
        // Non-production env sync
        if (time() > intval($data['cloudbeds_access_token_timestamp']) + 1800) {
            cloudbeds_import_data();
        }
    }
}

/**
 * Retrieves an authorization code from Cloudbeds using a client_id value.
 *
 * @link https://hotels.cloudbeds.com/api/docs/#api-Authentication-oauth
 * 
 * @param string $client_id The client ID.
 * @param string $nonce Nonce authentication.
 * @return void
 */
function cloudbeds_get_authorization_code($client_id, $nonce) {
    $endpoint = 'https://hotels.cloudbeds.com/api/v1.1/oauth';
    $query = http_build_query([
        'client_id' => $client_id,
        'redirect_uri' => rest_url('/cloudbeds/auth'),
        'response_type' => 'code',
        'state' => $nonce
    ]);

    wp_redirect($endpoint . "?" . $query);
    exit;
}

/**
 * Retrieves an access token from Cloudbeds.
 *
 * @link https://hotels.cloudbeds.com/api/docs/#api-Authentication-token
 * @return string|array Error message or response from server.
 */
function cloudbeds_get_access_token() {
    if ( defined( 'WP_CLI' ) && WP_CLI ) {
        WP_CLI::log("\nRetrieving a new access token for Cloudbeds API..");
    }

    $data = cloudbeds_option_data();

    if (!$data['cloudbeds_client_id']) {
        return "Missing client ID.";
    }

    if (!$data['cloudbeds_client_secret']) {
        return "Missing client secret.";
    }

    if (!$data['cloudbeds_authorization_code'] && !$data['cloudbeds_refresh_token']) {
        return "Missing a code to use for retrieving access token. (authorization_code || refresh_token)";
    }

    if ($data['cloudbeds_refresh_token']) {
        $token = [
            'grant_type' => 'refresh_token',
            'code' => $data['cloudbeds_refresh_token']
        ];
    } else {
        $token = [
            'grant_type' => 'authorization_code',
            'code' => $data['cloudbeds_authorization_code']
        ];        
    }

    if ($token['grant_type'] == 'refresh_token') {
        $args = [
            'client_id' => $data['cloudbeds_client_id'],
            'client_secret' => $data['cloudbeds_client_secret'],
            'refresh_token' => $token['code'],
            'grant_type' => $token['grant_type'],
            'redirect_uri' => rest_url('/cloudbeds/auth'),
        ];
    } else {
        $args = [
            'client_id' => $data['cloudbeds_client_id'],
            'client_secret' => $data['cloudbeds_client_secret'],
            'code' => $token['code'],
            'grant_type' => $token['grant_type'],
            'redirect_uri' => rest_url('/cloudbeds/auth'),
        ];
    }

    $res = cloudbeds_api_post('access_token', $args);

    if ( defined( 'WP_CLI' ) && WP_CLI ) {
        WP_CLI::log(wp_json_encode($res));
    }

    if ($res['error_description']) {
        cloudbeds_set_option('cloudbeds_status', $res['error_description']);
    }

    if ($res['access_token']) {
        cloudbeds_set_option('cloudbeds_access_token', $res['access_token']);
        cloudbeds_set_option('cloudbeds_access_token_timestamp', time());
        
        if ( defined( 'WP_CLI' ) && WP_CLI ) {
            WP_CLI::log("New access token: {$res['access_token']}");
        }
    }

    if ($res['refresh_token']) {
        cloudbeds_set_option('cloudbeds_refresh_token', $res['refresh_token']);

        if ( defined( 'WP_CLI' ) && WP_CLI ) {
            WP_CLI::log("New refresh token: {$res['refresh_token']}");
        }
    }

    return $res;
}