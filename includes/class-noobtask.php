<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://waashero.com/
 * @since      1.0.0
 *
 * @package    Noobtask
 * @subpackage Noobtask/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Noobtask
 * @subpackage Noobtask/includes
 * @author     J Hanlon <j@waashero.com>
 */

namespace WaasHero;

class Noobtask {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Noobtask_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {

		$this->version = NOOBTASK_VERSION;
		$this->plugin_name = 'noobtask';

		$this->load_dependencies();
		$this->define_kartra_api_hooks();
		$this->define_default_tasks();
		$this->define_cron_hooks();
		$this->define_admin_hooks();
		$this->define_public_hooks();
		$this->set_locale();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Noobtask_Loader. Orchestrates the hooks of the plugin.
	 * - Noobtask_i18n. Defines internationalization functionality.
	 * - Noobtask_Admin. Defines all hooks for the admin area.
	 * - Noobtask_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-noobtask-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-noobtask-i18n.php';

		/**
		 * The class responsible for defining actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-noobtask-admin.php';

		/**
		 * The class responsible for defining actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-noobtask-public.php';

		/**
		 * The class responsible for defining all kartra api methods and related ajax actions.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-kartra-api.php';

		/**
		 * The class responsible for defining noobtask option api methods.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-noobtask-options-api.php';

		/**
		 * The class responsible for defining noobtask api methods.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-noobtask-api.php';

		/**
		 * The class responsible for defining all default tasks and actions.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-default-tasks.php';

		/**
		 * The class responsible for defining all the cron jobs and methods.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-cron-jobs.php';

		/**
		 * The class responsible for defining a custom task admin list. We extend the default wordpress list.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-task-list.php';

		/**
		 * The class responsible for defining the noobtask index/add/edit page and related ajax methods.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-task-add-edit-page.php';

		/**
		 * The class responsible for defining the noobtask network options page and related methods.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-task-options-page.php';


		$this->loader = new Noobtask_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Noobtask_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Noobtask_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Noobtask_Admin( $this->get_plugin_name(), $this->get_version() );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		$this->loader->add_action( 'wp_dashboard_setup', $plugin_admin,'add_dashboard_widgets' );

		$task_page = new Task_Add_Edit_Page( $this->get_plugin_name(), $this->get_version() );
		$this->loader->add_action( 'admin_init', $task_page, 'init' );
		$this->loader->add_action( 'admin_menu', $task_page, 'add_menu');
        $this->loader->add_action( 'wp_ajax_save_noobtask_ajax', $task_page, 'save_noobtask_ajax' );
		$this->loader->add_action( 'wp_ajax_complete_noobtask_ajax', $task_page, 'complete_noobtask_ajax' );
		$this->loader->add_action( 'admin_notices', $task_page, 'admin_notices');
		$this->loader->add_action( 'wp_ajax_save_noobtask_options_ajax', $task_page, 'save_noobtask_options_ajax' );

		$options_page = new Task_Network_Options_Page( $this->get_plugin_name(), $this->get_version() );
		$this->loader->add_action( 'network_admin_menu', $options_page, 'add_menu');
		$this->loader->add_action( 'network_admin_edit_noobtaskaction', $options_page, 'save_settings' );

		$setup_wizard = new Noobtask_Setup_Wizard( $this->get_plugin_name(), $this->get_version() );
		$this->loader->add_action( 'admin_menu', $setup_wizard, 'admin_menus', 99 );		

	}

	/**
	 * Register all of the hooks related to the cron job functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_cron_hooks() {

		$cron_jobs = new CronJobs();
		$this->loader->add_action( 'noobtask_cron_hook_hourly',  $cron_jobs, 'update_kartra' );

	}

	/**
	 * Register all of the hooks related to the kartra api
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_kartra_api_hooks() {

		$kartra_api = new Kartra_Api( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_init', $kartra_api, 'kartra_settings_init' );
        $this->loader->add_action( 'wp_ajax_save_tag_ajax', $kartra_api, 'save_tag_ajax' );
    
	}

	/**
	 * Register all of the hooks related to the cron job functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_default_tasks() {

		$default_tasks = new \Default_Tasks( $this->get_plugin_name(), $this->get_version() );
		$this->loader->add_action('wpmu_new_blog', $default_tasks, 'add_site_owner_to_options', 10, 2);
		$this->loader->add_action('user_register', $default_tasks, 'noobtask_register_add_meta');
        $this->loader->add_action('wp_login', $default_tasks, 'noobtask_first_user_login', 10, 2);
		$this->loader->add_action('wp_login', $default_tasks, 'track_user_login', 10, 2);
		$this->loader->add_action('admin_init', $default_tasks, 'check_for_custom_logo');

	}
	
	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Noobtask_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles', 11);
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Noobtask_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
