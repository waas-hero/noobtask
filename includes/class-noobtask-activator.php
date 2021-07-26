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
	 * Add a cron schdule hourly to wp cron.
	 *
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
		$task_sql = "CREATE TABLE $task_table_name (
			task_id INTEGER NOT NULL AUTO_INCREMENT,
			task_name TEXT NOT NULL,
			task_desc LONGTEXT NULL,
			task_list TEXT NULL,
			task_link TEXT NULL,
			task_selector TEXT NULL,
			task_completed BOOLEAN NULL,
			task_tag TEXT NULL,
			site_type TEXT NULL,
			visible BOOLEAN NULL,
			task_is_default TEXT NULL,
			completed_at datetime NULL,
			PRIMARY KEY (task_id)
		) $charset_collate;";
		dbDelta( $task_sql );

		//* Create the noobtask option table
		$taskoptions_table_name = $wpdb->prefix . 'noobtask_options';
		$taskoptions_sql = "CREATE TABLE $taskoptions_table_name (
			id INTEGER NOT NULL AUTO_INCREMENT,
			name TEXT NOT NULL,
			value TEXT NULL,
			PRIMARY KEY (id)
		) $charset_collate;";
		dbDelta( $taskoptions_sql );
	
	}

	function preload_task_data() {
		global $wpdb;
		
		//load default tasks
		$default_tasks = Default_Tasks::auto_tasks();
		//check to see if we already added these
		$tableName = $wpdb->prefix . 'noobtasks';
		$result = $wpdb->get_results($wpdb->prepare( 
            "SELECT * FROM $tableName WHERE task_is_default = %d",
            true
        ));
		//if we already added tasks then leave
		if(count($result) > 0){return;}
		//add default tasks to table
		foreach($default_tasks as $task){
			$wpdb->insert($tableName, $task);
		}
		
	}

	function preload_option_data() {
		global $wpdb;

		//load default options
		$tableName2 = $wpdb->prefix . 'noobtask_options';
		$result = $wpdb->get_results( "SELECT * FROM $tableName2" );
		//if we already added options then leave
		if(count($result) > 0){return;}
		$wpdb->insert($tableName2, [
			'name' => 'site_type',
			'value' => 'seller'
		]);
		
	}

}
