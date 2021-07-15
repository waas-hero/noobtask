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
	
			<h2>Section 1</h2>
			<table class="form-table">
				<tr>
					<th scope="row"><label for="some_field">Some option</label></th>
					<td>
						<input name="some_field" class="regular-text" type="text" id="some_field" value="<?php echo esc_attr( get_site_option( 'some_field') ); ?>" />
						<p class="description">Field description can be added here.</p>
					</td>
				</tr>
			</table>
			<h2>Section 2</h2>
			<table class="form-table">
				<tr>
					<th scope="row">Some checkbox</th>
					<td><label><input name="some_checkbox" type="checkbox" value="1" <?php checked( get_site_option( 'some_checkbox'), '1' ) ?>>Yes, check this checkbox</label></td>
				</tr>
			</table>
			<?php echo submit_button(); ?>
		</form></div>

    <?php
    }

    function save_settings(){

        check_admin_referer( 'noobtask-validate' ); // Nonce security check


        $some_checkbox = intval($_POST["some_checkbox"]);
        $some_field = sanitize_text_field($_POST["some_field"]);

        update_site_option( 'some_field', $some_field );
        update_site_option( 'some_checkbox', $some_checkbox );

        wp_redirect( add_query_arg( array(
            'page' => 'noobtask-options',
            'updated' => true ), network_admin_url( 'admin.php?page=noobtask-options', 'https' )
        ));

        exit;
    }
}