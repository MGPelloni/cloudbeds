<?php
/**
 * Checks the Cloudbeds cache.
 * 
 * @param string $url The URL to check.
 * @return array|bool Returns the row if it exists, otherwise returns false.
 */
function cloudbeds_cache_check($url) {
    // Check if the table exists, and if not - create it.
    if ( !cloudbeds_cache_table_exists() ) {
        cloudbeds_cache_create_table();
    }

    // Check if the row exists, and if so - return it.
    $row = cloudbeds_cache_get_row($url);

    if ($row) {
        // If too much time has passed, return false so the row will be regenerated.
        if (time() - $row->timestamp > 86400) {
            return false;
        }
        
        return $row->response;
    }

    return false;
}

/**
 * Creates a mySQL table for caching Cloudbeds data.
 * The table saves the cURL response, and the time it was saved.
 */
function cloudbeds_cache_create_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'cloudbeds_cache';
    $charset_collate = $wpdb->get_charset_collate();
    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        url text NOT NULL,
        response longtext NOT NULL,
        timestamp varchar(55) NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );
}

/**
 * Inserts a row in the mySQL table. 
 * If the row already exists, update it.
 *
 * @param string $endpoint The URL of the row.
 * @param string $response The response from Cloudbeds.
 * @return void
 */
function cloudbeds_cache_update_row($url, $response) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'cloudbeds_cache';

    $row = cloudbeds_cache_get_row($url);

    if ($row) {
        $wpdb->update(
            $table_name,
            array(
                'response' => json_encode($response),
                'url' => $url,
                'timestamp' => time(),
            ),
            array( 'id' => $row->id ),
        );
    } else {
        $wpdb->insert(
            $table_name,
            array(
                'url' => $url,
                'response' => json_encode($response),
                'timestamp' => time(),
            ),
        );
    }
}

/**
 * Returns a row from the mySQL table.
 *
 * @param string $url The URL of the row.
 * @return void
 */
function cloudbeds_cache_get_row($url) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'cloudbeds_cache';
    return $wpdb->get_row( "SELECT * FROM $table_name WHERE url = '$url'" );
}

/**
 * Checks if the mySQL table exists.
 *
 * @return boolean
 */
function cloudbeds_cache_table_exists() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'cloudbeds_cache';
    if ( $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) == $table_name ) {
        return true;
    } 

    return false;
}

/**
 * Retrieves all Cloudbeds table data to be used in the admin panel.
 */
function cloudbeds_cache_retrieve_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'cloudbeds_cache';
    return $wpdb->get_results( "SELECT * FROM $table_name" );
}