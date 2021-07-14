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
		if ( ! wp_next_scheduled( 'noobtask_cron_hook_hourly' ) ) {
			wp_schedule_event( time(), 'hourly', 'noobtask_cron_hook_hourly' );
		}
	}

	public function create_db_table() {
		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	
		//* Create the noobtasks table
		$task_table_name = $wpdb->prefix . 'noobtasks';
		$task_sql = "CREATE TABLE  $task_table_name (
			task_id INTEGER NOT NULL AUTO_INCREMENT,
			task_name TEXT NOT NULL,
			task_desc LONGTEXT NULL,
			task_list TEXT NULL,
			task_link TEXT NULL,
			task_selector TEXT NULL,
			task_completed boolean NULL,
			task_tag TEXT NULL,
			task_is_default TEXT NULL,
			completed_at datetime NULL,
			PRIMARY KEY (task_id)
		) $charset_collate;";
		dbDelta( $task_sql );
	
	}

	function preload_data() {
		global $wpdb;
		$tableName = $wpdb->prefix . 'noobtasks';
		
		$default_tasks = Default_Tasks::auto_tasks();

		$result = $wpdb->get_results($wpdb->prepare( 
            "SELECT * FROM $tableName WHERE task_is_default = %d",
            true
        ));
		if(count($result) > 0){return;}

		foreach($default_tasks as $task){
			$wpdb->insert($tableName, $task);
		}
		
	}

}
