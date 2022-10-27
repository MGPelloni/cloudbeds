<?php
/*
Plugin Name: Cloudbeds
Plugin URI:  https://cacheinteractive.com/cloudbeds/
Description: WordPress integration utilizing the Cloudbeds API.
Version:     1.0.0
Author:      Cache Interactive
Author URI:  https://cacheinteractive.com/
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Domain Path: /languages
Text Domain: my-toolset
*/

if ( ! defined( 'ABSPATH' ) ) {
    die();
}

// Definitions
define('CLOUDBEDS_PLUGIN_PATH', plugin_dir_path( __FILE__ ));
define('CLOUDBEDS_PLUGIN_URL', plugin_dir_url( __FILE__ ));
define('CLOUDBEDS_ADMIN_URL', get_admin_url(null, '/options-general.php?page=cloudbeds'));
define('CLOUDBEDS_DATA_KEYS', [
    'cloudbeds_client_id', 
    'cloudbeds_client_secret', 
    'cloudbeds_authorization_code', 
    'cloudbeds_access_token', 
    'cloudbeds_access_token_timestamp', 
    'cloudbeds_refresh_token',
    'cloudbeds_status',
    'cloudbeds_data_key'
]);

// Functions
require_once(CLOUDBEDS_PLUGIN_PATH . 'src/php/admin.php');
require_once(CLOUDBEDS_PLUGIN_PATH . 'src/php/cloudbeds.php');
require_once(CLOUDBEDS_PLUGIN_PATH . 'src/php/functions.php');
require_once(CLOUDBEDS_PLUGIN_PATH . 'src/php/routes.php');

// WP-CLI
if ( defined( 'WP_CLI' ) && WP_CLI ) {
	require_once(CLOUDBEDS_PLUGIN_PATH . 'src/php/cli.php');
	WP_CLI::add_command( 'cloudbeds', 'Cloudbeds_CLI' );
}

// Hooks [Initialization]
add_action('init', 'cloudbeds_check_access_token');

// Hooks [Administration]
add_action('admin_enqueue_scripts', 'cloudbeds_admin_styles');
add_action('admin_menu', 'cloudbeds_admin_custom_menu');
add_action('admin_init', 'cloudbeds_admin_register_settings');

// Hooks [WordPress REST API]
add_action('rest_api_init', 'cloudbeds_route_connect');
add_action('rest_api_init', 'cloudbeds_route_auth');
add_action('rest_api_init', 'cloudbeds_route_data');

// Hooks [Activation, Deactivation]
register_activation_hook(__FILE__, 'cloudbeds_activate');
register_deactivation_hook(__FILE__, 'cloudbeds_deactivate'); 

// Filters
add_filter('cron_schedules', 'cloudbeds_cron_schedules');
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'cloudbeds_action_links' );

