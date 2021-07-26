<?php

namespace WaasHero;

/**
 * The plugin class that registers and handles task options page.
 *
 *
 * @since      1.0.0
 * @package    Noobtask
 * @subpackage Noobtask/admin
 * @author     J Hanlon <j@waashero.com>
 */

class Task_Network_Options_Page {

    /**
     * Add a menu page.
     */
    function add_menu() {
        add_menu_page(
            'Starter Task Global Options',
            'Starter Tasks',
            'manage_options',
            'noobtask-options',
            [$this, 'page_html']
        );
    }

    /**
     * sub level menu callback function
     */
    function page_html() {

        // check user capabilities
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        ?>
        <div class="wrap">
		<h1><?php echo get_admin_page_title(); ?></h1>

		<form method="post" action="edit.php?action=noobtaskaction">
			<?php wp_nonce_field( 'noobtask-validate' ); ?>
	
			<h2>Network Options</h2>
			<table class="form-table">
            <tr>
					<th scope="row"><label for="noobtask_app_title">Application Title</label></th>
					<td>
						<input name="noobtask_app_title" class="regular-text" type="text" id="noobtask_app_title" value="<?php echo esc_attr( get_site_option( 'noobtask_app_title') ); ?>" />
						<p class="description">The name that users see when your application is referred to.</p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="noobtask_widget_title">Widget Title</label></th>
					<td>
						<input name="noobtask_widget_title" class="regular-text" type="text" id="noobtask_widget_title" value="<?php echo esc_attr( get_site_option( 'noobtask_widget_title') ); ?>" />
						<!-- <p class="description">Field description can be added here.</p> -->
					</td>
				</tr>
                
			</table>
			<h2>Extra Options</h2>
			<table class="form-table">
				<tr>
					<th scope="row" style="color:red;">Delete Data On Deactivate</th>
					<td><label><input name="delete_noobtask_on_deactivate" type="checkbox" value="1" <?php checked( get_site_option( 'delete_noobtask_on_deactivate'), '1' ) ?>>Warning: This is a destructive option, and will delete all Starter Task data from the DB. ALWAYS create a backup before making any changes to your DB.</label></td>
				</tr>
			</table>
			<?php echo submit_button(); ?>
		</form></div>

    <?php
    }

    function save_settings(){

        check_admin_referer( 'noobtask-validate' ); // Nonce security check


        $delete_noobtask_on_deactivate = intval($_POST["delete_noobtask_on_deactivate"]);
        $noobtask_widget_title = sanitize_text_field($_POST["noobtask_widget_title"]);
        $noobtask_app_title = sanitize_text_field($_POST["noobtask_app_title"]);

        update_site_option( 'noobtask_widget_title', $noobtask_widget_title );
        update_site_option( 'noobtask_app_title', $noobtask_app_title );
        update_site_option( 'delete_noobtask_on_deactivate', $delete_noobtask_on_deactivate );

        wp_redirect( add_query_arg( array(
            'page' => 'noobtask-options',
            'updated' => true ), network_admin_url( 'admin.php?page=noobtask-options', 'https' )
        ));

        exit;
    }
}