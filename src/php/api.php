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
            $endpoint .= '?' . http_build_query($args);
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $endpoint);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Authorization: " . $token
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $res = json_decode(curl_exec($ch), true);
        curl_close($ch);
        
        if ($res['success']) {
            return $res['data'];
        }
    }

    return false;
}