<?php

/**
 * Fired during plugin deactivation
 *
 * @link       https://waashero.com/
 * @since      1.0.0
 *
 * @package    Noobtask
 * @subpackage Noobtask/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    Noobtask
 * @subpackage Noobtask/includes
 * @author     J Hanlon <j@waashero.com>
 */
class Noobtask_Deactivator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function deactivate() {
		$timestamp = wp_next_scheduled( 'noobtask_cron_hook' );
		wp_unschedule_event( $timestamp, 'noobtask_cron_hook' );

		if(get_site_option( 'delete_noobtask_on_deactivate')){
			global $wpdb;
			$task_table_name = $wpdb->prefix . 'noobtasks';
			$wpdb->query( "DROP TABLE IF EXISTS {$task_table_name}" );	
			$option_table_name = $wpdb->prefix . 'noobtask_options';
			$wpdb->query( "DROP TABLE IF EXISTS {$option_table_name}" );	
		}
	}

}
