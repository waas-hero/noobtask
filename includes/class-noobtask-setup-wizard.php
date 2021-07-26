<?php
/**
 * Noobtask Setup Wizard Class
 *
 * Takes new users through some basic steps.
 *
 * @author      jhanlon
 * @author      dtbaker
 * @author      vburlak
 * @package     noobtask_wizard
 * @version     1.0.0
 *
 *
 * Based off the WooThemes installer.
 *
 *
 */

namespace WaasHero;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

	/**
	 * Noobtask_Setup_Wizard class
	 */
	class Noobtask_Setup_Wizard {

		/**
		 * The class version number.
		 *
		 * @since 1.0.0
		 * @access private
		 *
		 * @var string
		 */
		protected $version = NOOBTASK_VERSION;

		/** @var string Current Step */
		protected $step = '';

		/** @var array Steps for the setup wizard */
		protected $steps = array();
        
		public $page_hook;

		/**
		 * The slug name for the parent menu
		 *
		 * @since 1.0.0
		 *
		 * @var string
		 */
		protected $parent_slug = 'noobtask';

		/**
		 * Complete URL to Setup Wizard
		 *
		 * @since 1.0.0
		 *
		 * @var string
		 */
		protected $plugin_url;

		/**
		 * @since 1.0.0
		 *
		 */
		public $site_styles = array();
		
		/**
		 * @since 1.0.0
		 *
		 */
		public $default_theme_style;

		/**
		 * Holds the current instance of the plugin manager
		 *
		 * @since 1.0.0
		 * @var Noobtask_Setup_Wizard
		 */
		private static $instance = null;

        public $app_title;

		/**
		 * @since 1.0.0
		 *
		 * @return Noobtask_Setup_Wizard
		 */
		public static function get_instance() {
			if ( ! self::$instance ) {
				self::$instance = new self;
			}
			return self::$instance;
		}

		/**
		 * A dummy constructor to prevent this class from being loaded more than once.
		 *
		 * @see Noobtask_Setup_Wizard::instance()
		 *
		 * @since 1.0.0
		 * @access private
		 */
		public function __construct() {
			$this->init_globals();
            $this->app_title = get_site_option( 'noobtask_app_title', 'Starter Tasks' );
		}

		/**
		 * Get the default style. Can be overriden by plugin init scripts.
		 *
		 * @see Noobtask_Setup_Wizard::instance()
		 *
		 * @since 1.1.7
		 * @access public
		 */
		public function get_default_theme_style() {
			if( $this->default_theme_style ){
				return $this->default_theme_style;
			}elseif( $this->site_styles && count($this->site_styles) > 0 ){
				foreach ( $this->site_styles as $style_name => $style_data ) {
					return $style_name;
				}
			}else{
				return false;
			}
		}

		/**
		 * Get the default style. Can be overriden by plugin init scripts.
		 *
		 * @see Noobtask_Setup_Wizard::instance()
		 *
		 * @since 1.1.9
		 * @access public
		 */
		public function get_header_logo_width() {
			return '200px';
		}


		/**
		 * Get the default style. Can be overriden by plugin init scripts.
		 *
		 * @see Noobtask_Setup_Wizard::instance()
		 *
		 * @since 1.1.9
		 * @access public
		 */
		public function get_logo_image() {
			
            $image_url = plugin_dir_url( __DIR__ ) . 'admin/images/reigrow_logo_light.png';

			return apply_filters( 'noobtask_setup_logo_image', $image_url );
		}

		/**
		 * Setup the class globals.
		 *
		 * @since 1.1.1
		 * @access public
		 */
		public function init_globals() {

			$this->default_theme_style = apply_filters( $this->parent_slug . '_setup_wizard_default_theme_style', ''  );

			// create an images/styleX/ folder for each style here.
			$this->site_styles = apply_filters( $this->parent_slug . '_setup_wizard_site_styles', array() );

            $this->page_slug = $this->parent_slug . '-setup';

			$this->page_url = 'admin.php?page=' . $this->page_slug;

		}


		/**
		 * Add admin menus/screens.
		 */
		public function admin_menus() {

			$this->page_hook = add_submenu_page( 'index.php', esc_html__( 'Setup Wizard' ), esc_html__( 'Setup Wizard' ), 'manage_options', $this->page_slug, array( $this, 'setup_wizard', ) );

			$this->init_actions();
		}

		/**
		 * Setup the hooks, actions and filters.
		 *
		 * @uses add_action() To add actions.
		 * @uses add_filter() To add filters.
		 *
		 * @since 1.1.1
		 * @access public
		 */
		public function init_actions() {
   
			if ($this->page_hook === 'dashboard_page_noobtask-setup') {

				add_action( "load-$this->page_hook", array( $this, 'enqueue_scripts' ) );
				add_action( "load-$this->page_hook", array( $this, 'admin_redirects' ), 30 );
				add_action( "load-$this->page_hook", array( $this, 'init_wizard_steps' ), 30 );
				add_action( "load-$this->page_hook", array( $this, 'setup_wizard' ), 30 );

				add_action( 'wp_ajax_noobtask_setup_plugins', array( $this, 'ajax_plugins' ) );
				add_action( 'wp_ajax_noobtask_setup_content', array( $this, 'ajax_content' ) );

				add_action( 'after_switch_plugin', array( $this, 'switch_plugin' ) );
				add_action( 'upgrader_post_install', array( $this, 'upgrader_post_install' ), 10, 2 );
			}
		}

		/**
		 * After a plugin update we clear the setup_complete option. This prompts the user to visit the update page again.
		 *
		 * @since 1.1.8
		 * @access public
		 */
		public function upgrader_post_install( $return, $plugin ) {
			if ( is_wp_error( $return ) ) {
				return $return;
			}
			if ( $plugin != get_stylesheet() ) {
				return $return;
			}
			update_option( 'noobtask_setup_complete', false );

			return $return;
		}

		/**
		 * We determine if the user already has plugin content installed. This can happen if swapping from a previous plugin or updated the current plugin. We change the UI a bit when updating / swapping to a new plugin.
		 *
		 * @since 1.1.8
		 * @access public
		 */
		public function is_possible_upgrade() {
			return false;
		}


		public function enqueue_scripts() {


			wp_enqueue_style( 'noobtask-setup',  plugin_dir_url( __DIR__ ) . 'admin/css/noobtask-setup-wizard.css', array(
				'wp-admin',
				'dashicons',
				'install',
			), $this->version );

			//enqueue style for admin notices
			wp_enqueue_style( 'wp-admin' );

			wp_register_script( 'jquery-blockui', plugin_dir_url( __DIR__ ) . 'admin/js/jquery.blockUI.js', array( 'jquery' ), '2.70', true );

			wp_register_script( 'noobtask-setup',  plugin_dir_url( __DIR__ ) . 'admin/js/noobtask-setup.js', array(
				'jquery',
				'jquery-blockui',
			), $this->version );

			wp_localize_script( 'noobtask-setup', 'noobtask_setup_params', array(
				'ajaxurl'          => admin_url( 'admin-ajax.php' ),
				'wpnonce'          => wp_create_nonce( 'noobtask_setup_nonce' ),
				'verify_text'      => esc_html__( '...verifying' ),
			) );

			wp_enqueue_media();
			wp_enqueue_script( 'media' );
			
		}

		public function switch_plugin() {
			set_transient( '_' . $this->parent_slug . '_activation_redirect', 1 );
		}

		public function admin_redirects() {
			if ( ! get_transient( '_' . $this->parent_slug . '_activation_redirect' ) || get_option( 'noobtask_setup_complete', false ) ) {
				return;
			}
			delete_transient( '_' . $this->parent_slug . '_activation_redirect' );
			wp_safe_redirect( admin_url( $this->page_url ) );
			exit;
		}


		/**
		 * Setup steps.
		 *
		 * @since 1.1.1
		 * @access public
		 * @return array
		 */
		public function init_wizard_steps() {

			$this->steps = array(
				'introduction' => array(
					'name'    => esc_html__( 'Introduction' ),
					'view'    => array( $this, 'noobtask_setup_introduction' ),
					'handler' => array( $this, 'noobtask_setup_introduction_save' ),
				),
			);

			$this->steps['style'] = array(
				'name'    => esc_html__( 'Style' ),
				'view'    => array( $this, 'noobtask_setup_color_style' ),
				'handler' => array( $this, 'noobtask_setup_color_style_save' ),
			);
		
			$this->steps['default_content'] = array(
				'name'    => esc_html__( 'Content' ),
				'view'    => array( $this, 'noobtask_setup_default_content' ),
				'handler' => '',
			);

			$this->steps['design']          = array(
				'name'    => esc_html__( 'Logo' ),
				'view'    => array( $this, 'noobtask_setup_logo_design' ),
				'handler' => array( $this, 'noobtask_setup_logo_design_save' ),
			);

			$this->steps['customize']       = array(
				'name'    => esc_html__( 'Customize' ),
				'view'    => array( $this, 'noobtask_setup_customize' ),
				'handler' => '',
			);

			$this->steps['help_support']    = array(
				'name'    => esc_html__( 'Support' ),
				'view'    => array( $this, 'noobtask_setup_help_support' ),
				'handler' => '',
			);

			$this->steps['next_steps']      = array(
				'name'    => esc_html__( 'Ready!' ),
				'view'    => array( $this, 'noobtask_setup_ready' ),
				'handler' => '',
			);

			$this->steps = apply_filters( $this->parent_slug . '_setup_wizard_steps', $this->steps );

		}

		/**
		 * Show the setup wizard
		 */
		public function setup_wizard() {

			if ( empty( $_GET['page'] ) || $this->page_slug !== $_GET['page'] ) {
				return;
			}

			ob_end_clean();
			
			$this->step = isset( $_GET['step'] ) ? sanitize_key( $_GET['step'] ) : current( array_keys( $this->steps ) );

			ob_start();

			$this->setup_wizard_header();
			
			$show_content = true;

			echo '<div class="noobtask-setup-content">';

            $this->setup_wizard_steps();

			if ( ! empty( $_REQUEST['save_step'] ) && isset( $this->steps[ $this->step ]['handler'] ) ) {
				$show_content = call_user_func( $this->steps[ $this->step ]['handler'] );
			}

			if ( $show_content ) {
				$this->setup_wizard_content();
			}

			echo '</div>';

			$this->setup_wizard_footer();

			exit;
		}

		public function get_step_link( $step ) {
			return add_query_arg( 'step', $step, admin_url( 'admin.php?page=' . $this->page_slug ) );
		}

		public function get_next_step_link() {
			$keys = array_keys( $this->steps );

			return add_query_arg( 'step', $keys[ array_search( $this->step, array_keys( $this->steps ) ) + 1 ], remove_query_arg( 'translation_updated' ) );
		}

        public function get_previous_step_link() {
			$keys = array_keys( $this->steps );

			return add_query_arg( 'step', $keys[ array_search( $this->step, array_keys( $this->steps ) ) - 1 ], remove_query_arg( 'translation_updated' ) );
		}

		/**
		 * Setup Wizard Header
		 */
	public function setup_wizard_header() { ?>

		<!DOCTYPE html>
		<html xmlns="http://www.w3.org/1999/xhtml" <?php language_attributes(); ?>>
		<head>
			<meta name="viewport" content="width=device-width"/>
			<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
			<?php

			// avoid plugin check issues.
			echo '<title>' . esc_html__( 'Setup Wizard' ) . '</title>'; ?>
			<?php acf_form_head(); ?>
			<?php wp_print_scripts( 'noobtask-setup' ); ?>
			<?php do_action( 'admin_print_styles' ); ?>
			<?php do_action( 'admin_print_scripts' ); ?>
			<?php do_action( 'admin_head' ); ?>
			
		</head>
		<body class="noobtask-setup-body">
            <div class="noobtask-setup">
                <h1 id="wc-logo">
                    <a href="/" target="_blank"><?php
                        $image_url = $this->get_logo_image();
                        if ( $image_url ) {
                            $image = '<img class="site-logo" src="%s" alt="%s" style="width:%s; height:auto" />';
                            printf(
                                $image,
                                $image_url,
                                get_bloginfo( 'name' ),
                                $this->get_header_logo_width()
                            );
                        } else { ?>
                                <img src="<?php echo esc_url( $this->plugin_url . 'images/logo.png' ); ?>" alt="Noobtask install wizard" /><?php
                        } ?></a>
                </h1>
		<?php
		}

		/**
		 * Setup Wizard Footer
		 */
		public function setup_wizard_footer() {
		?>
		<?php if ( 'next_steps' === $this->step ) : ?>
			<a class="wc-return-to-dashboard"
			   href="<?php echo esc_url( admin_url() ); ?>"><?php esc_html_e( 'Return to the Site Dashboard' ); ?></a>
		<?php endif; ?>
        </div> <!-- End inner div noobtask-setup -->
		</body>
		<?php
			do_action( 'admin_footer' );
			do_action( 'admin_print_footer_scripts' );
		?>
		</html>
		<?php
	}

		/**
		 * Output the steps
		 */
		public function setup_wizard_steps() {
			$ouput_steps = $this->steps;
			array_shift( $ouput_steps );
			?>
			<ol class="noobtask-setup-steps">
				<?php foreach ( $ouput_steps as $step_key => $step ) : ?>
					<li class="<?php
					$show_link = false;
					if ( $step_key === $this->step ) {
						echo 'active';
					} elseif ( array_search( $this->step, array_keys( $this->steps ) ) > array_search( $step_key, array_keys( $this->steps ) ) ) {
						echo 'done';
						$show_link = true;
					}
					?>"><?php
						if ( $show_link ) {
							?>
							<a href="<?php echo esc_url( $this->get_step_link( $step_key ) ); ?>"><?php echo esc_html( $step['name'] ); ?></a>
							<?php
						} else {
							echo esc_html( $step['name'] );
						}
						?></li>
				<?php endforeach; ?>
			</ol>
			<?php
		}

		/**
		 * Output the content for the current step
		 */
		public function setup_wizard_content() {
			isset( $this->steps[ $this->step ] ) ? call_user_func( $this->steps[ $this->step ]['view'] ) : false;
		}

		/**
		 * Introduction step
		 */
		public function noobtask_setup_introduction() {

			if ( $this->is_possible_upgrade() ) {
				?>

				<h1><?php printf( esc_html__( 'Welcome to the setup wizard for %s.' ), $this->app_title ); ?></h1>

				<p><?php esc_html_e( 'It looks like you may have recently upgraded to this plugin. Great! This setup wizard will help ensure all the default settings are correct. It will also show some information about your new website and support options.' ); ?></p>

				<p class="noobtask-setup-actions step">
					<a href="<?php echo esc_url( $this->get_next_step_link() ); ?>"
					   class="button-primary button button-large button-next"><?php esc_html_e( 'Let\'s Go!' ); ?></a>
					<a href="<?php echo admin_url( ); ?>"
					   class="button button-large"><?php esc_html_e( 'Not right now' ); ?></a>
				</p>

				<?php
			} else if ( get_option( 'noobtask_setup_complete', false ) ) {
				?>
				<h1><?php printf( esc_html__( 'Welcome to the setup wizard for %s.' ), $this->app_title ); ?></h1>
				<p><?php esc_html_e( 'It looks like you have already run the setup wizard. Below are some options: ' ); ?></p>
				<ul>
					<li>
						<a href="<?php echo esc_url( $this->get_next_step_link() ); ?>"
						   class="button-primary button button-next button-large"><?php esc_html_e( 'Run Setup Wizard Again' ); ?></a>
					</li>
					<li>
						<form method="post">
							<input type="hidden" name="reset-font-defaults" value="yes">
							<input type="submit" class="button-primary button button-large button-next"
							       value="<?php esc_attr_e( 'Reset font style and colors' ); ?>" name="save_step"/>
							<?php wp_nonce_field( 'noobtask-setup' ); ?>
						</form>
					</li>
				</ul>
				<p class="noobtask-setup-actions step">
					<a href="<?php echo esc_url( wp_get_referer() && ! strpos( wp_get_referer(), 'update.php' ) ? wp_get_referer() : admin_url( '' ) ); ?>"
					   class="button button-large"><?php esc_html_e( 'Cancel' ); ?></a>
				</p>
				<?php
			} else {
				?>
				<h1><?php printf( esc_html__( 'Welcome to the easy setup wizard for your new website.' ), $this->app_title ); ?></h1>

				<p><?php printf( esc_html__( 'Thank you for choosing %s. This quick setup wizard will help you configure your new website. We will automatically install your required default content, your logo, set your brand colors,c and tell you a little about our amazing Help &amp; Support options. It should take less than 5 minutes.' ), $this->app_title ); ?></p>

				<p><?php esc_html_e( 'No time right now? If you don\'t want to go through the wizard, you can select Not Now and return to your account dashboard. Come back anytime and complete your setup!' ); ?></p>

				<p class="noobtask-setup-actions step">

					<a href="<?php echo esc_url( $this->get_next_step_link() ); ?>"
					   class="button-primary button button-large button-next"><?php esc_html_e( 'Let\'s Get Started!' ); ?></a>
					
				</p>

                <a href="<?php echo admin_url( '' ); ?>"
					   class=""><?php esc_html_e( 'Not now' ); ?></a>
				<?php
			}
		}

		public function filter_options( $options ) {
			return $options;
		}

		/**
		 *
		 * Handles save button from welcome page. This is to perform tasks when the setup wizard has already been run. E.g. reset defaults
		 *
		 * @since 1.2.5
		 */
		public function noobtask_setup_introduction_save() {

			check_admin_referer( 'noobtask-setup' );

			if ( ! empty( $_POST['reset-font-defaults'] ) && $_POST['reset-font-defaults'] == 'yes' ) {

				// clear font options
				update_option( 'tt_font_plugin_options', array() );

				// do other reset options here.

				// reset site color
				remove_theme_mod( 'noobtask_site_color' );

				// if ( class_exists( 'noobtask_customize_save_hook' ) ) {
				// 	$site_color_defaults = new noobtask_customize_save_hook();
				// 	$site_color_defaults->save_color_options();
				// }

				$file_name = get_template_directory() . '/style.custom.css';
				if ( file_exists( $file_name ) ) {
					require_once( ABSPATH . 'wp-admin/includes/file.php' );
					WP_Filesystem();
					global $wp_filesystem;
					$wp_filesystem->put_contents( $file_name, '' );
				}
				?>
				<p>
					<strong><?php esc_html_e( 'Options have been reset. Please go to Appearance > Customize in the Site backend.' ); ?></strong>
				</p>
				<?php
				return true;
			}

			return false;
		}




		private function _content_default_get() {

			$content = array();

			// find out what content is in our default json file.
			//$available_content = $this->_get_json( 'default.json' );
			// foreach ( $available_content as $post_type => $post_data ) {
			// 	if ( count( $post_data ) ) {
			// 		$first           = current( $post_data );
			// 		$post_type_title = ! empty( $first['type_title'] ) ? $first['type_title'] : ucwords( $post_type ) . 's';
			// 		if ( $post_type_title == 'Navigation Menu Items' ) {
			// 			$post_type_title = 'Navigation';
			// 		}
			// 		$content[ $post_type ] = array(
			// 			'title'            => $post_type_title,
			// 			'description'      => sprintf( esc_html__( 'This will create default %s as seen in the demo.' ), $post_type_title ),
			// 			'pending'          => esc_html__( 'Pending.' ),
			// 			'installing'       => esc_html__( 'Installing.' ),
			// 			'success'          => esc_html__( 'Success.' ),
			// 			'install_callback' => array( $this, '_content_install_type' ),
			// 			'checked'          => $this->is_possible_upgrade() ? 0 : 1,
			// 			// dont check if already have content installed.
			// 		);
			// 	}
			// }

			$content['widgets'] = array(
				'title'            => esc_html__( 'Widgets' ),
				'description'      => esc_html__( 'Insert default sidebar widgets as seen in the demo.' ),
				'pending'          => esc_html__( 'Pending.' ),
				'installing'       => esc_html__( 'Installing Default Widgets.' ),
				'success'          => esc_html__( 'Success.' ),
				'install_callback' => array( $this, '_content_install_widgets' ),
				'checked'          => $this->is_possible_upgrade() ? 0 : 1,
				// dont check if already have content installed.
			);

			$content['settings'] = array(
				'title'            => esc_html__( 'Settings' ),
				'description'      => esc_html__( 'Configure default settings.' ),
				'pending'          => esc_html__( 'Pending.' ),
				'installing'       => esc_html__( 'Installing Default Settings.' ),
				'success'          => esc_html__( 'Success.' ),
				'install_callback' => array( $this, '_content_install_settings' ),
				'checked'          => $this->is_possible_upgrade() ? 0 : 1,
				// dont check if already have content installed.
			);

			$content = apply_filters( $this->parent_slug . '_setup_wizard_content', $content );

			return $content;

		}

		/**
		 * Page setup
		 */
		public function noobtask_setup_default_content() {
			?>

			<h1><?php esc_html_e( 'Default Content' ); ?></h1>

			<form method="post">

				<?php if ( $this->is_possible_upgrade() ) { ?>
					<p><?php esc_html_e( 'It looks like you already have content installed on this website. If you would like to install the default demo content as well you can select it below. Otherwise just choose the upgrade option to ensure everything is up to date.' ); ?></p>
				<?php } else { ?>
					<p><?php printf( esc_html__( 'It\'s time to insert some default content for your new Site website. Choose what you would like inserted below and click Continue. It is recommended to leave everything selected. Once inserted, this content can be managed from the Site admin dashboard. ' ), '<a href="' . esc_url( admin_url( 'edit.php?post_type=page' ) ) . '" target="_blank">', '</a>' ); ?></p>
				<?php } ?>

				<table class="noobtask-setup-pages" cellspacing="0">
					<thead>
					<tr>
						<td class="check"></td>
						<th class="item"><?php esc_html_e( 'Item' ); ?></th>
						<th class="description"><?php esc_html_e( 'Description' ); ?></th>
						<th class="status"><?php esc_html_e( 'Status' ); ?></th>
					</tr>
					</thead>
					<tbody>
					<?php foreach ( $this->_content_default_get() as $slug => $default ) { ?>
						<tr class="noobtask_default_content" data-content="<?php echo esc_attr( $slug ); ?>">
							<td>
								<input type="checkbox" name="default_content[<?php echo esc_attr( $slug ); ?>]"
								       class="noobtask_default_content"
								       id="default_content_<?php echo esc_attr( $slug ); ?>"
								       value="1" <?php echo ( ! isset( $default['checked'] ) || $default['checked'] ) ? ' checked' : ''; ?>>
							</td>
							<td><label
									for="default_content_<?php echo esc_attr( $slug ); ?>"><?php echo esc_html( $default['title'] ); ?></label>
							</td>
							<td class="description"><?php echo esc_html( $default['description'] ); ?></td>
							<td class="status"><span><?php echo esc_html( $default['pending'] ); ?></span>
								<div class="spinner"></div>
							</td>
						</tr>
					<?php } ?>
					</tbody>
				</table>

				<p class="noobtask-setup-actions step">
					
                    <a href="<?php echo esc_url( $this->get_previous_step_link() ); ?>"
					   class="button button-large button-previous"><?php esc_html_e( 'Go back a step' ); ?></a>
					<?php wp_nonce_field( 'noobtask-setup' ); ?>

                    <a href="<?php echo esc_url( $this->get_next_step_link() ); ?>"
					   class="button button-large button-next"><?php esc_html_e( 'Skip this step' ); ?></a>
					<?php wp_nonce_field( 'noobtask-setup' ); ?>

                    <a href="<?php echo esc_url( $this->get_next_step_link() ); ?>"
					   class="button-primary button button-large button-next"
					   data-callback="install_content"><?php esc_html_e( 'Continue' ); ?></a>

				</p>
			</form>
			<?php
		}


		public function ajax_content() {

			$content = $this->_content_default_get();
			if ( ! check_ajax_referer( 'noobtask_setup_nonce', 'wpnonce' ) || empty( $_POST['content'] ) && isset( $content[ $_POST['content'] ] ) ) {
				wp_send_json_error( array( 'error' => 1, 'message' => esc_html__( 'No content Found' ) ) );
			}

			$json         = false;
			$this_content = $content[ $_POST['content'] ];

			if ( isset( $_POST['proceed'] ) ) {
				// install the content!

				$this->log( ' -!! STARTING SECTION for ' . $_POST['content'] );

				// init delayed posts from transient.
				$this->delay_posts = get_transient( 'delayed_posts' );
				if ( ! is_array( $this->delay_posts ) ) {
					$this->delay_posts = array();
				}

				if ( ! empty( $this_content['install_callback'] ) ) {
					if ( $result = call_user_func( $this_content['install_callback'] ) ) {

						$this->log( ' -- FINISH. Writing ' . count( $this->delay_posts, COUNT_RECURSIVE ) . ' delayed posts to transient ' );
						set_transient( 'delayed_posts', $this->delay_posts, 60 * 60 * 24 );

						if ( is_array( $result ) && isset( $result['retry'] ) ) {
							// we split the stuff up again.
							$json = array(
								'url'         => admin_url( 'admin-ajax.php' ),
								'action'      => 'noobtask_setup_content',
								'proceed'     => 'true',
								'retry'       => time(),
								'retry_count' => $result['retry_count'],
								'content'     => $_POST['content'],
								'_wpnonce'    => wp_create_nonce( 'noobtask_setup_nonce' ),
								'message'     => $this_content['installing'],
								'logs'        => $this->logs,
								'errors'      => $this->errors,
							);
						} else {
							$json = array(
								'done'    => 1,
								'message' => $this_content['success'],
								'debug'   => $result,
								'logs'    => $this->logs,
								'errors'  => $this->errors,
							);
						}
					}
				}
			} else {

				$json = array(
					'url'      => admin_url( 'admin-ajax.php' ),
					'action'   => 'noobtask_setup_content',
					'proceed'  => 'true',
					'content'  => $_POST['content'],
					'_wpnonce' => wp_create_nonce( 'noobtask_setup_nonce' ),
					'message'  => $this_content['installing'],
					'logs'     => $this->logs,
					'errors'   => $this->errors,
				);
			}

			if ( $json ) {
				$json['hash'] = md5( serialize( $json ) ); // used for checking if duplicates happen, move to next plugin
				wp_send_json( $json );
			} else {
				wp_send_json( array(
					'error'   => 1,
					'message' => esc_html__( 'Error' ),
					'logs'    => $this->logs,
					'errors'  => $this->errors,
				) );
			}

			exit;

		}



		private function _content_install_widgets() {

			// todo: pump these out into the 'content/' folder along with the XML so it's a little nicer to play with
			$import_widget_positions = $this->_get_json( 'widget_positions.json' );
			$import_widget_options   = $this->_get_json( 'widget_options.json' );

			// importing.
			$widget_positions = get_option( 'sidebars_widgets' );
			if ( ! is_array( $widget_positions ) ) {
				$widget_positions = array();
			}

			foreach ( $import_widget_options as $widget_name => $widget_options ) {
				// replace certain elements with updated imported entries.
				foreach ( $widget_options as $widget_option_id => $widget_option ) {

					// replace TERM ids in widget settings.
					foreach ( array( 'nav_menu' ) as $key_to_replace ) {
						if ( ! empty( $widget_option[ $key_to_replace ] ) ) {
							// check if this one has been imported yet.
							$new_id = $this->_imported_term_id( $widget_option[ $key_to_replace ] );
							if ( ! $new_id ) {
								// do we really clear this out? nah. well. maybe.. hmm.
							} else {
								$widget_options[ $widget_option_id ][ $key_to_replace ] = $new_id;
							}
						}
					}
					// replace POST ids in widget settings.
					foreach ( array( 'image_id', 'post_id' ) as $key_to_replace ) {
						if ( ! empty( $widget_option[ $key_to_replace ] ) ) {
							// check if this one has been imported yet.
							$new_id = $this->_imported_post_id( $widget_option[ $key_to_replace ] );
							if ( ! $new_id ) {
								// do we really clear this out? nah. well. maybe.. hmm.
							} else {
								$widget_options[ $widget_option_id ][ $key_to_replace ] = $new_id;
							}
						}
					}
				}
				$existing_options = get_option( 'widget_' . $widget_name, array() );
				if ( ! is_array( $existing_options ) ) {
					$existing_options = array();
				}
				$new_options = $existing_options + $widget_options;
				update_option( 'widget_' . $widget_name, $new_options );
			}
			update_option( 'sidebars_widgets', array_merge( $widget_positions, $import_widget_positions ) );

			return true;

		}

		public function _content_install_settings() {

			$this->_handle_delayed_posts( true ); // final wrap up of delayed posts.
			$this->vc_post(); // final wrap of vc posts.

			$custom_options = $this->_get_json( 'options.json' );

			// we also want to update the widget area manager options.
			foreach ( $custom_options as $option => $value ) {
				// we have to update widget page numbers with imported page numbers.
				if (
					preg_match( '#(wam__position_)(\d+)_#', $option, $matches ) ||
					preg_match( '#(wam__area_)(\d+)_#', $option, $matches )
				) {
					$new_page_id = $this->_imported_post_id( $matches[2] );
					if ( $new_page_id ) {
						// we have a new page id for this one. import the new setting value.
						$option = str_replace( $matches[1] . $matches[2] . '_', $matches[1] . $new_page_id . '_', $option );
					}
				}
				if ( $value && ! empty( $value['custom_logo'] ) ) {
					$new_logo_id = $this->_imported_post_id( $value['custom_logo'] );
					if ( $new_logo_id ) {
						$value['custom_logo'] = $new_logo_id;
					}
				}
				if ( $option == 'dtbaker_featured_images' ) {
					$value      = maybe_unserialize( $value );
					$new_values = array();
					if ( is_array( $value ) ) {
						foreach ( $value as $cat_id => $image_id ) {
							$new_cat_id   = $this->_imported_term_id( $cat_id );
							$new_image_id = $this->_imported_post_id( $image_id );
							if ( $new_cat_id && $new_image_id ) {
								$new_values[ $new_cat_id ] = $new_image_id;
							}
						}
					}
					$value = $new_values;
				}
				update_option( $option, $value );
			}

			$menu_ids = $this->_get_json( 'menu.json' );
			$save     = array();
			foreach ( $menu_ids as $menu_id => $term_id ) {
				$new_term_id = $this->_imported_term_id( $term_id );
				if ( $new_term_id ) {
					$save[ $menu_id ] = $new_term_id;
				}
			}
			if ( $save ) {
				set_theme_mod( 'nav_menu_locations', array_map( 'absint', $save ) );
			}


			$homepage = get_page_by_title( 'Home' );
			if ( $homepage ) {
				update_option( 'page_on_front', $homepage->ID );
				update_option( 'show_on_front', 'page' );
			}

			$blogpage = get_page_by_title( 'Blog' );
			if ( $blogpage ) {
				update_option( 'page_for_posts', $blogpage->ID );
				update_option( 'show_on_front', 'page' );
			}

			global $wp_rewrite;
			$wp_rewrite->set_permalink_structure( '/%year%/%monthnum%/%day%/%postname%/' );
			update_option( 'rewrite_rules', false );
			$wp_rewrite->flush_rules( true );

			return true;
		}

		public function _get_json( $file ) {

			$plugin_style = __DIR__ . '/content/' . basename(get_theme_mod('noobtask_site_style',$this->get_default_theme_style())) .'/';
			if ( is_file( $plugin_style . basename( $file ) ) ) {
				WP_Filesystem();
				global $wp_filesystem;
				$file_name = $plugin_style . basename( $file );
				if ( file_exists( $file_name ) ) {
					return json_decode( $wp_filesystem->get_contents( $file_name ), true );
				}
			}
            // backwards compat:
			if ( is_file( __DIR__ . '/content/' . basename( $file ) ) ) {
				WP_Filesystem();
				global $wp_filesystem;
				$file_name = __DIR__ . '/content/' . basename( $file );
				if ( file_exists( $file_name ) ) {
					return json_decode( $wp_filesystem->get_contents( $file_name ), true );
				}
			}

			return array();
		}

		private function _get_sql( $file ) {
			if ( is_file( __DIR__ . '/content/' . basename( $file ) ) ) {
				WP_Filesystem();
				global $wp_filesystem;
				$file_name = __DIR__ . '/content/' . basename( $file );
				if ( file_exists( $file_name ) ) {
					return $wp_filesystem->get_contents( $file_name );
				}
			}

			return false;
		}


		public $logs = array();

		public function log( $message ) {
			$this->logs[] = $message;
		}

		public $errors = array();

		public function error( $message ) {
			$this->logs[] = 'ERROR!!!! ' . $message;
		}

		public function noobtask_setup_color_style() { 

            echo '<h1>'. esc_html_e( 'Site Style' ) .'</h1>';
		
			acf_register_form( array(
				'id' => 'reisites-style',
				'post_id'            => 'options', // Get the post ID
				'fields'       		 => array('field_5cddd9c12b14d',), // Create post field group ID(s)
				'form'               => true,
				'html_before_fields' => '',
				'html_after_fields'  => '',
				'submit_value'       => 'Save Changes',
				'html_submit_button'  => '<input type="submit" class="acf-button button button-primary button-large noonbtask-btn" value="%s" />',
				'return' => esc_url( $this->get_next_step_link() ),
			));

			acf_form( 'reisites-style' );

            	
		}

		/**
		 * Save logo & design options
		 */
		public function noobtask_setup_color_style_save() {
			check_admin_referer( 'noobtask-setup' );

			$new_style = isset( $_POST['new_style'] ) ? $_POST['new_style'] : false;
			if ( $new_style ) {
				set_theme_mod( 'noobtask_site_style', $new_style );
			}

			wp_redirect( esc_url_raw( $this->get_next_step_link() ) );
			exit;
		}


		/**
		 * Logo & Design
		 */
		public function noobtask_setup_logo_design() { ?>

			<h1><?php esc_html_e( 'Logo' ); ?></h1>

			<form method="post">

				<p><?php echo esc_html__( 'Please add your logo below. For best results, the logo should be a transparent PNG ( 466 by 277 pixels). The logo can be changed at any time.' ); ?></p>

                <div class="noobtask-logobox">
                    <div id="current-logo">
                        <img class="noobtask-site-logo" src="<?php echo plugin_dir_url( __DIR__ ); ?>admin/images/your-logo-here.png" alt="" style="width:250px; height:auto" />
                    </div>
					
				    <a href="#" class="button button-upload"><?php esc_html_e( 'Upload New Logo' ); ?></a>
                </div>

				<input type="hidden" name="new_logo_id" id="new_logo_id" value="">

				<p class="noobtask-setup-actions step">

                    <a href="<?php echo esc_url( $this->get_next_step_link() ); ?>"
					   class="button button-large button-next"><?php esc_html_e( 'Skip this step' ); ?></a>

					<input type="submit" class="button-primary button button-large button-next"
					       value="<?php esc_attr_e( 'Continue' ); ?>" name="save_step"/>
					
					<?php wp_nonce_field( 'noobtask-setup' ); ?>
				</p>
			</form>
			<?php
		}

		/**
		 * Save logo & design options
		 */
		public function noobtask_setup_logo_design_save() {
			
			check_admin_referer( 'noobtask-setup' );

			$new_logo_id = (int) $_POST['new_logo_id'];
			// save this new logo url into the database and calculate the desired height based off the logo width.
			// copied from dtbaker.plugin_options.php
			if ( $new_logo_id ) {
				$attr = wp_get_attachment_image_src( $new_logo_id, 'full' );
				if ( $attr && ! empty( $attr[1] ) && ! empty( $attr[2] ) ) {

					set_theme_mod( 'custom_logo', $new_logo_id );
					set_theme_mod( 'header_textcolor', 'blank' );
					set_theme_mod( 'logo_header_image', $attr[0] );
					// we have a width and height for this image. awesome.
					$logo_width  = (int) get_theme_mod( 'logo_header_image_width', '467' );
					$scale       = $logo_width / $attr[1];
					$logo_height = intval( $attr[2] * $scale );
					if ( $logo_height > 0 ) {
						set_theme_mod( 'logo_header_image_height', $logo_height );
					}
				}
			}

			$new_style = isset( $_POST['new_site_color'] ) ? $_POST['new_site_color'] : false;
			if ( $new_style ) {
				$demo_styles = apply_filters( 'noobtask_default_styles', array() );
				if ( isset( $demo_styles[ $new_style ] ) ) {
					set_theme_mod( 'noobtask_site_color', $new_style );
					if ( class_exists( 'noobtask_customize_save_hook' ) ) {
						$site_color_defaults = new noobtask_customize_save_hook();
						$site_color_defaults->save_color_options( $new_style );
					}
				}
			}

			wp_redirect( esc_url_raw( $this->get_next_step_link() ) );
			exit;
		}

		/**
		 * Payments Step
		 */
		public function noobtask_setup_updates() {
			?>
			<h1><?php esc_html_e( 'Theme Updates' ); ?></h1>
			<?php if ( function_exists( 'noobtask_market' ) ) { ?>
				<form method="post">
					<?php
					$option = noobtask_market()->get_options();

					$my_items = array();
					if ( $option && ! empty( $option['items'] ) ) {
						foreach ( $option['items'] as $item ) {
							if ( ! empty( $item['oauth'] ) && ! empty( $item['token_data']['expires'] ) && $item['oauth'] == $this->plugin_slug && $item['token_data']['expires'] >= time() ) {
								// token exists and is active
								$my_items[] = $item;
							}
						}
					}
					if ( count( $my_items ) ) {
						?>
						<p>Thanks! Theme updates have been enabled for the following items: </p>
						<ul>
							<?php foreach ( $my_items as $item ) { ?>
								<li><?php echo esc_html( $item['name'] ); ?></li>
							<?php } ?>
						</ul>
						<p>When an update becomes available it will show in the Dashboard with an option to install.</p>
						<p>Change settings from the 'Noobtask Market' menu in the Site Dashboard.</p>

						<p class="noobtask-setup-actions step">
							<a href="<?php echo esc_url( $this->get_next_step_link() ); ?>"
							   class="button button-large button-next button-primary"><?php esc_html_e( 'Continue' ); ?></a>
						</p>
						<?php
					} else {
						?>
						<p><?php esc_html_e( 'Please login using your ThemeForest account to enable Theme Updates. We update plugins when a new feature is added or a bug is fixed. It is highly recommended to enable Theme Updates.' ); ?></p>
						<p>When an update becomes available it will show in the Dashboard with an option to install.</p>
						<p>
							<em>On the next page you will be asked to Login with your ThemeForest account and grant
								permissions to enable Automatic Updates. If you have any questions please <a
									href="http://dtbaker.net/noobtask/" target="_blank">contact us</a>.</em>
						</p>
						<p class="noobtask-setup-actions step">
							<input type="submit" class="button-primary button button-large button-next"
							       value="<?php esc_attr_e( 'Login with Noobtask' ); ?>" name="save_step"/>
							<a href="<?php echo esc_url( $this->get_next_step_link() ); ?>"
							   class="button button-large button-next"><?php esc_html_e( 'Skip this step' ); ?></a>
							<?php wp_nonce_field( 'noobtask-setup' ); ?>
						</p>
					<?php } ?>
				</form>
			<?php } else { ?>
				Please ensure the Noobtask Market plugin has been installed correctly. <a
					href="<?php echo esc_url( $this->get_step_link( 'default_plugins' ) ); ?>">Return to Required
					Plugins installer</a>.
			<?php } ?>
			<?php
		}

		/**
		 * Payments Step save
		 */
		public function noobtask_setup_updates_save() {
			check_admin_referer( 'noobtask-setup' );

			// redirect to our custom login URL to get a copy of this token.
			$url = $this->get_oauth_login_url( $this->get_step_link( 'updates' ) );

			wp_redirect( esc_url_raw( $url ) );
			exit;
		}


		public function noobtask_setup_customize() {
			?>

			<h1>Theme Customization</h1>
			<p>
				Most changes to the website can be made through the Appearance > Customize menu from the Site
				dashboard. These include:
			</p>
			<ul>
				<li>Typography: Font Sizes, Style, Colors (over 200 fonts to choose from) for various page elements.
				</li>
				<li>Logo: Upload a new logo and adjust its size.</li>
				<li>Background: Upload a new background image.</li>
				<li>Layout: Enable/Disable responsive layout, page and sidebar width.</li>
			</ul>
			<p>To change the Sidebars go to Appearance > Widgets. Here widgets can be "drag &amp; droped" into sidebars.
				To control which "widget areas" appear, go to an individual page and look for the "Left/Right Column"
				menu. Here widgets can be chosen for display on the left or right of a page. More details in
				documentation.</p>
			<p>
				<em>Advanced Users: If you are going to make changes to the plugin source code please use a <a
						href="https://codex.wordpress.org/Child_Themes" target="_blank">Child Theme</a> rather than
					modifying the main plugin HTML/CSS/PHP code. This allows the parent plugin to receive updates without
					overwriting your source code changes. <br/> See <code>child-plugin.zip</code> in the main folder for
					a sample.</em>
			</p>

			<p class="noobtask-setup-actions step">
				<a href="<?php echo esc_url( $this->get_next_step_link() ); ?>"
				   class="button button-primary button-large button-next"><?php esc_html_e( 'Continue' ); ?></a>
			</p>

			<?php
		}

		public function noobtask_setup_help_support() {
			?>
			<h1>Help and Support</h1>
			<p>This plugin comes with 6 months item support from purchase date (with the option to extend this period).
				This license allows you to use this plugin on a single website. Please purchase an additional license to
				use this plugin on another website.</p>
			<p>Item Support can be accessed from <a href="http://dtbaker.net/noobtask/" target="_blank">http://dtbaker.net/noobtask/</a>
				and includes:</p>
			<ul>
				<li>Availability of the author to answer questions</li>
				<li>Answering technical questions about item features</li>
				<li>Assistance with reported bugs and issues</li>
				<li>Help with bundled 3rd party plugins</li>
			</ul>

			<p>Item Support <strong>DOES NOT</strong> Include:</p>
			<ul>
				<li>Customization services (this is available through <a
						href="http://studiotracking.noobtask.com/aff_c?offer_id=4&aff_id=1564&source=DemoInstall"
						target="_blank">Noobtask Studio</a>)
				</li>
				<li>Installation services (this is available through <a
						href="http://studiotracking.noobtask.com/aff_c?offer_id=4&aff_id=1564&source=DemoInstall"
						target="_blank">Noobtask Studio</a>)
				</li>
				<li>Help and Support for non-bundled 3rd party plugins (i.e. plugins you install yourself later on)</li>
			</ul>
			<p>More details about item support can be found in the ThemeForest <a
					href="http://pluginforest.net/page/item_support_policy" target="_blank">Item Support Polity</a>. </p>
			<p class="noobtask-setup-actions step">
				<a href="<?php echo esc_url( $this->get_next_step_link() ); ?>"
				   class="button button-primary button-large button-next"><?php esc_html_e( 'Agree and Continue' ); ?></a>
				<?php wp_nonce_field( 'noobtask-setup' ); ?>
			</p>
			<?php
		}

		/**
		 * Final step
		 */
		public function noobtask_setup_ready() {

			update_option( 'noobtask_setup_complete', time() );
			update_option( 'noobtask_update_notice', strtotime('-4 days') );
			?>

			<a href="https://twitter.com/share" class="twitter-share-button"
			   data-url="http://pluginforest.net/user/dtbaker/portfolio?ref=dtbaker"
			   data-text="<?php echo esc_attr( 'I just installed the ' . $this->app_title . ' #Site plugin from #ThemeForest' ); ?>"
			   data-via="NoobtaskMarket" data-size="large">Tweet</a>
			<script>!function (d, s, id) {
					var js, fjs = d.getElementsByTagName(s)[0];
					if (!d.getElementById(id)) {
						js = d.createElement(s);
						js.id = id;
						js.src = "//platform.twitter.com/widgets.js";
						fjs.parentNode.insertBefore(js, fjs);
					}
				}(document, "script", "twitter-wjs");</script>

			<h1><?php esc_html_e( 'Your Website is Ready!' ); ?></h1>

			<p>Congratulations! Your website is ready. Login to your Site
				dashboard to make changes and modify any of the default content to suit your needs.</p>
			<p>Please come back and <a href="http://pluginforest.net/downloads" target="_blank">leave a 5-star rating</a>
				if you are happy with this plugin. <br/>Follow <a href="https://twitter.com/dtbaker" target="_blank">@dtbaker</a>
				on Twitter to see updates. Thanks! </p>

			<div class="noobtask-setup-next-steps">
				<div class="noobtask-setup-next-steps-first">
					<h2><?php esc_html_e( 'Next Steps' ); ?></h2>
					<ul>
						<li class="setup-product"><a class="button button-primary button-large"
						                             href="https://twitter.com/dtbaker"
						                             target="_blank"><?php esc_html_e( 'Follow @dtbaker on Twitter' ); ?></a>
						</li>
						<li class="setup-product"><a class="button button-next button-large"
						                             href="<?php echo esc_url( home_url() ); ?>"><?php esc_html_e( 'View your new website!' ); ?></a>
						</li>
					</ul>
				</div>
				<div class="noobtask-setup-next-steps-last">
					<h2><?php esc_html_e( 'More Resources' ); ?></h2>
					<ul>
						<li class="documentation"><a href="http://dtbaker.net/noobtask/documentation/"
						                             target="_blank"><?php esc_html_e( 'Read the Theme Documentation' ); ?></a>
						</li>
						<li class="howto"><a href="https://wordpress.org/support/"
						                     target="_blank"><?php esc_html_e( 'Learn how to use Site' ); ?></a>
						</li>
						<li class="rating"><a href="http://pluginforest.net/downloads"
						                      target="_blank"><?php esc_html_e( 'Leave an Item Rating' ); ?></a></li>
						<li class="support"><a href="http://dtbaker.net/noobtask/"
						                       target="_blank"><?php esc_html_e( 'Get Help and Support' ); ?></a></li>
					</ul>
				</div>
			</div>
			<?php
		}

		public function ajax_notice_handler() {
			check_ajax_referer( 'noobtask-ajax-nonce', 'security' );
			// Store it in the options table
			update_option( 'noobtask_update_notice', time() );
		}
		
		/**
		 * @param $array1
		 * @param $array2
		 *
		 * @return mixed
		 *
		 *
		 * @since    1.1.4
		 */
		private function _array_merge_recursive_distinct( $array1, $array2 ) {
			$merged = $array1;
			foreach ( $array2 as $key => &$value ) {
				if ( is_array( $value ) && isset( $merged [ $key ] ) && is_array( $merged [ $key ] ) ) {
					$merged [ $key ] = $this->_array_merge_recursive_distinct( $merged [ $key ], $value );
				} else {
					$merged [ $key ] = $value;
				}
			}

			return $merged;
		}


		/**
		 * Helper function
		 * Take a path and return it clean
		 *
		 * @param string $path
		 *
		 * @since    1.1.2
		 */
		public static function cleanFilePath( $path ) {
			$path = str_replace( '', '', str_replace( array( '\\', '\\\\', '//' ), '/', $path ) );
			if ( $path[ strlen( $path ) - 1 ] === '/' ) {
				$path = rtrim( $path, '/' );
			}

			return $path;
		}

		public function is_submenu_page() {
			return ( $this->parent_slug == '' ) ? false : true;
		}
	}