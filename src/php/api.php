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
    $endpoint = "https://hotels.cloudbeds.com/api/v1.1/$path";
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
        
        $res_body = json_decode(wp_remote_retrieve_body($res), true);

        if ($res_body['success']) {
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
    $endpoint = "https://hotels.cloudbeds.com/api/v1.1/$path";
    $token = get_option('cloudbeds_access_token');

    if ($token) {
        $args['headers'] = "Authorization: " . $token;
        $res = wp_remote_post($endpoint, $args);  
        
        if (is_wp_error($res)) {
            return false;
        }
        
        $res_body = json_decode(wp_remote_retrieve_body($res), true);

        if ($res_body['success']) {
            return $res_body['data'];
        }
    }

    return false;
}