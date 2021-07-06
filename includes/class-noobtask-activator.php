<?php

/**
 * Fired during plugin activation
 *
 * @link       https://waashero.com/
 * @since      1.0.0
 *
 * @package    Noobtask
 * @subpackage Noobtask/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Noobtask
 * @subpackage Noobtask/includes
 * @author     J Hanlon <j@waashero.com>
 */
class Noobtask_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		
	}

	public function create_db_table() {
		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	
		//* Create the noobtasks table
		$task_table_name = $wpdb->prefix . 'noobtasks';
		$task_sql = "CREATE TABLE $task_table_name (
			task_id INTEGER NOT NULL AUTO_INCREMENT,
			task_name TEXT NOT NULL,
			task_type TEXT NULL,
			task_link TEXT NULL,
			task_selector TEXT NULL,
			task_completed boolean NULL,
			task_tag TEXT NULL,
			completed_at datetime NULL,
			PRIMARY KEY (task_id)
		) $charset_collate;";
		dbDelta( $task_sql );

		//* Create the noobtags tags table to use with Kartra
		$tag_table_name = $wpdb->prefix . 'noobtask_tags';
		$tag_sql = "CREATE TABLE $tag_table_name (
			id INTEGER NOT NULL AUTO_INCREMENT,
			tag_name TEXT NOT NULL,
			tag_description TEXT NULL,
			PRIMARY KEY (id)
		) $charset_collate;";
		dbDelta( $tag_sql );
	
	}

	function preload_data() {
		global $wpdb;
		
		$table_name = $wpdb->prefix . 'noobtasks';
		
		$wpdb->insert( 
			$table_name, 
			array( 
				'name' => 'Set your theme', 
				'type' => 'level_1', 
			) 
		);
	}

}
