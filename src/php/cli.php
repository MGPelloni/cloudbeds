<?php
/**
 * Executes commands using WP-CLI.
 * Syntax: wp cloudbeds [command]
 */
class Cloudbeds_CLI {
	/**
	 * Deletes the Cloudbeds option data.
	 *
	 * @subcommand reset
	 */
	public function reset( $args ) {
        WP_CLI::log( sprintf( 'Deleting Cloudbeds data..' ) );
        cloudbeds_reset();

		foreach (cloudbeds_option_data() as $key => $data) {
            WP_CLI::log("{$key}: {$data}");
        }

		WP_CLI::success( sprintf( 'Deleted Cloudbeds data.' ) );
	}

    /**
	 * Shows the current Cloudbeds option data.
	 *
	 * @subcommand data
	 */
	public function data( $args ) {
        foreach (cloudbeds_option_data() as $key => $data) {
            WP_CLI::log("{$key}: {$data}");
        }
	}

    /**
	 * Retrieves a new access token using the refresh token.
	 *
	 * @subcommand new-token
	 */
	public function new_token( $args ) {
        $data = cloudbeds_option_data();
        cloudbeds_get_access_token($data['cloudbeds_client_id'], $data['cloudbeds_client_secret'], $data['cloudbeds_authorization_code']);
	}
}
