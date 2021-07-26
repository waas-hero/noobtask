<?php

/**
 *
 * @link              https://waashero.com/
 * @since             1.0.0
 * @package           Noobtask
 *
 * @wordpress-plugin
 * Plugin Name:       Starter Tasks - Kartra Edition
 * Plugin URI:        https://waashero.com/noobtask
 * Description:       Allows the network owner to create tasks for the user, and adds a tag to the user in Kartra, or adds that user to a Kartra List on completion of the task.
 * Version:           1.0.0
 * Author:            Waas Hero - J Hanlon
 * Author URI:        https://waashero.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       noobtask
 * Domain Path:       /languages
 */

 // Exit if accessed directly
defined('ABSPATH') || exit;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

//Set version here so it's easier :D
define('NOOBTASK_VERSION', '1.0.0');

/**
 * Require core file dependencies
 */
require_once __DIR__ . '/constants.php';

require_once plugin_dir_path( __FILE__ ) . 'includes/class-default-tasks.php';

require_once plugin_dir_path( __FILE__ ) . 'includes/class-noobtask-setup-wizard.php';

require_once plugin_dir_path( __FILE__ ) . 'includes/class-noobtask-activator.php';

require_once plugin_dir_path( __FILE__ ) . 'includes/class-noobtask-deactivator.php';

require plugin_dir_path( __FILE__ ) . 'includes/class-noobtask.php';

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-noobtask-activator.php
 */
function activate_noobtask() {
	
	Noobtask_Activator::activate();
	$activate = new Noobtask_Activator;
	$activate->create_db_table();
	$activate->preload_task_data();
	$activate->preload_option_data();

}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-noobtask-deactivator.php
 */
function deactivate_noobtask() {
	Noobtask_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_noobtask' );
register_deactivation_hook( __FILE__, 'deactivate_noobtask' );

/**
 * Begins execution of the plugin.
 *
 * Everything within the plugin is registered via hooks,
 * so kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_noobtask() {

	$plugin = new \WaasHero\Noobtask();
	$plugin->run();

}
run_noobtask();