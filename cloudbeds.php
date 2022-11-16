<?php
/*
Plugin Name: Cloudbeds
Plugin URI:  https://marcopelloni.com/cloudbeds/
Description: WordPress integration utilizing the Cloudbeds API.
Version:     1.0.0
Author:      Marco Pelloni
Author URI:  https://marcopelloni.com/
License:     GPL3
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Version number is automatically adjusted by semantic-release-bot on release, do not adjust manually:
Version: 1.0.0
*/

if ( ! defined( 'ABSPATH' ) ) {
    die();
}

// Definitions
define('CLOUDBEDS_PLUGIN_PATH', plugin_dir_path( __FILE__ ));
define('CLOUDBEDS_PLUGIN_URL', plugin_dir_url( __FILE__ ));
define('CLOUDBEDS_ADMIN_URL', get_admin_url(null, '/options-general.php?page=cloudbeds'));
define('CLOUDBEDS_ADMIN_SYNC_URL', get_admin_url(null, '/options-general.php?page=cloudbeds-sync'));
define('CLOUDBEDS_DATA_KEYS', [
    'cloudbeds_client_id', 
    'cloudbeds_client_secret', 
    'cloudbeds_authorization_code', 
    'cloudbeds_access_token', 
    'cloudbeds_access_token_timestamp', 
    'cloudbeds_refresh_token',
    'cloudbeds_data_key',
    'cloudbeds_status'
]);

// Functions
require_once(CLOUDBEDS_PLUGIN_PATH . 'src/php/admin.php');
require_once(CLOUDBEDS_PLUGIN_PATH . 'src/php/api.php');
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

// Updates
require_once(CLOUDBEDS_PLUGIN_PATH . 'lib/plugin-update-checker-5.0/plugin-update-checker.php');
use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

$myUpdateChecker = PucFactory::buildUpdateChecker(
	'https://marcopelloni.com/releases/cloudbeds.json',
	__FILE__,
	'cloudbeds'
);