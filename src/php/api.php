<?php

/**
 * Return information using the Cloudbeds API via GET.
 *
 * @link https://hotels.cloudbeds.com/api/docs/
 * 
 * @param string $path The endpoint path name.
 * @param array $args Additional arguments to be added to the request.
 * @return void
 */
function cloudbeds_api_get($path = '', $args = []) {
    $endpoint = "https://hotels.cloudbeds.com/api/v1.2/$path";
    $token = get_option('cloudbeds_access_token');

    if ($token) { 
        if ($args) {
            $endpoint .= "?" . http_build_query($args);
        }

        $res = wp_remote_get($endpoint, [
            'headers' => "Authorization: " . $token,
        ]);  
        
        if (is_wp_error($res)) {
            return false;
        }

        // Check the cache for the response.
        if (cloudbeds_cache_table_exists()) {
            $cached_data = cloudbeds_cache_check($endpoint);
            if ($cached_data) {
                return json_decode($cached_data, true);
            }
        }

        // The response is not cached or has expired, so we need to make a request.
        $res_body = json_decode(wp_remote_retrieve_body($res), true);
        if ($res_body['success']) {
            cloudbeds_cache_update_row($endpoint, $res_body['data']);
            return $res_body['data'];
        }
    }

    return false;
}

/**
 * Return information using the Cloudbeds API via POST.
 *
 * @link https://hotels.cloudbeds.com/api/docs/
 * 
 * @param string $path The endpoint path name.
 * @param array $args Additional arguments to be added to the request.
 * @return void
 */
function cloudbeds_api_post($path = '', $args = []) {
    $endpoint = "https://hotels.cloudbeds.com/api/v1.2/$path";
    $token = get_option('cloudbeds_access_token');

    if (!isset($args['headers'])) {
        $args['headers'] = [];
    }

    if ($token) {
        $args['headers']['Authorization'] = "Bearer " . $token;
    }

    $res = wp_remote_post($endpoint, $args);  
    
    if ( defined( 'WP_CLI' ) && WP_CLI ) {
        WP_CLI::log(wp_json_encode($res));
    }

    if (CLOUDBEDS_DEBUG) {
        cloudbeds_log("\n\ncloudbeds_api_post() - " . wp_date('Y-m-d H:i:s'));
        cloudbeds_log("ENDPOINT: $endpoint");
        cloudbeds_log("ARGUMENTS: " . wp_json_encode($args));
        cloudbeds_log("RESPONSE: " . wp_json_encode($res));
    }

    if (is_wp_error($res)) {
        return false;
    }
    
    $res_body = json_decode(wp_remote_retrieve_body($res), true);

    if (CLOUDBEDS_DEBUG) {
        cloudbeds_log("RESPONSE BODY: " . wp_remote_retrieve_body($res));
    }

    return $res_body;
}