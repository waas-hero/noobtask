<?php

namespace WaasHero;

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
 * The plugin class that registers and handles task custom post type.
 *
 *
 * @since      1.0.0
 * @package    Noobtask
 * @subpackage Noobtask/includes
 * @author     J Hanlon <j@waashero.com>
 */
class Task_Settings_Init {

    // task WP_List_Table object
    public $tasks_obj;
    public $tags_object;

    /**
     * @internal never define functions inside callbacks.
     * these functions could be run multiple times; this would result in a fatal error.
     */
    
    /**
     * custom option and settings
     */
    function noobtask_settings_init() {

        $this->tasks_obj = new \Task_List();
        $this->kartra_obj = new Kartra_Api();

        // Register a new setting for "noobtask" page.
        register_setting( 'noobtask', 'noobtask_options' );
    
        // Register a new section in the "noobtask" page.
        add_settings_section(
            'noobtask_section_developers',
            __( 'Custom Completion List for New Users', 'noobtask' ), [$this, 'noobtask_section_developers_callback'],
            'noobtask'
        );
    
        // Register a new field in the "noobtask_section_developers" section, inside the "noobtask" page.
        // add_settings_field(
        //     'noobtask_field_pill', // As of WP 4.6 this value is used only internally.
        //                             // Use $args' label_for to populate the id inside the callback.
        //         __( 'Pill', 'noobtask' ),
        //     [$this, 'noobtask_field_pill_cb'],
        //     'noobtask',
        //     'noobtask_section_developers',
        //     array(
        //         'label_for'         => 'noobtask_field_pill',
        //         'class'             => 'noobtask_row',
        //         'noobtask_custom_data' => 'custom',
        //     )
        // );

        // Register a new field in the "noobtask_section_developers" section, inside the "noobtask" page.
        add_settings_field(
            'noobtask_field_name', // As of WP 4.6 this value is used only internally.
                                    // Use $args' label_for to populate the id inside the callback.
                __( 'Task Name', 'noobtask' ),
            [$this, 'noobtask_field_name_cb'],
            'noobtask',
            'noobtask_section_developers',
            array(
                'label_for'         => 'noobtask_field_name',
                'class'             => 'noobtask_row',
                'noobtask_custom_data' => 'custom',
            )
        );

        add_settings_field(
            'noobtask_field_type', // As of WP 4.6 this value is used only internally.
                                    // Use $args' label_for to populate the id inside the callback.
                __( 'Type', 'noobtask' ),
            [$this, 'noobtask_field_type_cb'],
            'noobtask',
            'noobtask_section_developers',
            array(
                'label_for'         => 'noobtask_field_type',
                'class'             => 'noobtask_row',
                'noobtask_custom_data' => 'custom',
            )
        );

        add_settings_field(
            'noobtask_field_type', // As of WP 4.6 this value is used only internally.
                                    // Use $args' label_for to populate the id inside the callback.
                __( 'Type', 'noobtask' ),
            [$this, 'noobtask_field_type_cb'],
            'noobtask',
            'noobtask_section_developers',
            array(
                'label_for'         => 'noobtask_field_type',
                'class'             => 'noobtask_row',
                'noobtask_custom_data' => 'custom',
            )
        );

        // Register a new section in the "noobtask" page.
        add_settings_section(
            'noobtask_section_tasktable',
            __( 'Available Tasks', 'noobtask' ), [$this, 'noobtask_section_tasktable_callback'],
            'noobtask'
        );
    }
    
    
    
    /**
     * Developers section callback function.
     *
     * @param array $args  The settings array, defining title, id, callback.
     */
    function noobtask_section_developers_callback( $args ) { ?>
        
        <p id="<?php echo esc_attr( $args['id'] ); ?>">
            <?php esc_html_e( 'Customize the tasks your new users should complete.', 'noobtask' ); ?>
        </p>
        
    <?php }

    /**
     * Developers section callback function.
     *
     * @param array $args  The settings array, defining title, id, callback.
     */
    function noobtask_section_tasktable_callback( $args ) { ?>
        
        <define_syslog_variables id="<?php echo esc_attr( $args['id'] );?>">
            Table here <?php  print_r( get_option('noobtask_options') ); ?>
        </div>
        
    <?php }
    

    /**
     * Name field callback function.
     *
     * WordPress has magic interaction with the following keys: label_for, class.
     * - the "label_for" key value is used for the "for" attribute of the <label>.
     * - the "class" key value is used for the "class" attribute of the <tr> containing the field.
     * Note: you can add custom key value pairs to be used inside your callbacks.
     *
     * @param array $args
     */
    function noobtask_field_name_cb( $args ) {
        // Get the value of the setting we've registered with register_setting()
        $options = get_option( 'noobtask_options' ); ?>

        <input type="text" id="<?php echo esc_attr( $args['label_for'] ); ?>"
                data-custom="<?php echo esc_attr( $args['noobtask_custom_data'] ); ?>"
                name="noobtask_options[<?php echo esc_attr( $args['label_for'] ); ?>]" 
                style="width:100%; max-width:300px;"
        />
           
    <?php }


    /**
     * Type field callback function.
     *
     * WordPress has magic interaction with the following keys: label_for, class.
     * - the "label_for" key value is used for the "for" attribute of the <label>.
     * - the "class" key value is used for the "class" attribute of the <tr> containing the field.
     * Note: you can add custom key value pairs to be used inside your callbacks.
     *
     * @param array $args
     */
    function noobtask_field_type_cb( $args ) {
        // Get the value of the setting we've registered with register_setting()
        $options = get_option( 'noobtask_options' ); ?>

        <select id="<?php echo esc_attr( $args['label_for'] ); ?>"
                data-custom="<?php echo esc_attr( $args['noobtask_custom_data'] ); ?>"
                name="noobtask_options[<?php echo esc_attr( $args['label_for'] ); ?>]"
                style="width:100%; max-width:300px;">
            <option value="level_1" <?php echo isset( $options[ $args['label_for'] ] ) ? ( selected( $options[ $args['label_for'] ], 'red', false ) ) : ( '' ); ?>>
                <?php esc_html_e( 'Level 1', 'noobtask' ); ?>
            </option>
            <option value="level_2" <?php echo isset( $options[ $args['label_for'] ] ) ? ( selected( $options[ $args['label_for'] ], 'blue', false ) ) : ( '' ); ?>>
                <?php esc_html_e( 'Level 2', 'noobtask' ); ?>
            </option>
            <option value="level_3" <?php echo isset( $options[ $args['label_for'] ] ) ? ( selected( $options[ $args['label_for'] ], 'blue', false ) ) : ( '' ); ?>>
                <?php esc_html_e( 'Level 3', 'noobtask' ); ?>
            </option>
        </select>
        
    <?php }
    
    /**
     * Add the top level menu page.
     */
    function noobtask_options_page() {
        add_menu_page(
            'Noob Tasks',
            'Noob Tasks',
            'manage_options',
            'noobtask',
            [$this, 'noobtask_options_page_html']
        );
        add_submenu_page(
            'noobtask',
            'Tasks',
            'Tasks',
            'manage_options',
            'noobtask',
            [$this, 'noobtask_options_page_html']
        );

        

    }
    
        
    function save_noobtask_ajax(){
        global $wpdb;

        $task_name = sanitize_text_field($_POST["task_name"]);
        $task_type = sanitize_text_field($_POST["task_type"]);
        $task_tag = sanitize_text_field($_POST["task_tag"]);
        $task_link = sanitize_url($_POST["task_link"]);
        $task_selector = sanitize_text_field($_POST["task_selector"]);

        $tableName = "{$wpdb->prefix}noobtasks";
        $insert_row = $wpdb->insert( 
                        $tableName, 
                        array( 
                            'task_name' => $task_name, 
                            'task_link' => $task_link,
                            'task_selector' => $task_selector,
                            'task_type' => $task_type, 
                            'task_tag' => $task_tag, 
                            'task_completed' => false,
                        )
                    );
        // if row inserted in table
        if($insert_row){
            echo json_encode(array('res'=>true, 'message'=>__('New task added.'), 'data' =>array( 
                'task_name' => $task_name, 
                'task_link' => $task_link,
                'task_selector' => $task_selector,
                'task_type' => $task_type, 
                'task_tag' => $task_tag, 
                'task_completed' => false,
            )));
        }else{
            echo json_encode(array('res'=>false, 'message'=>__('Something went wrong. Please try again later.')));
        }
        wp_die();
    }

    function complete_noobtask_ajax(){
        global $wpdb;

        $task_id = intval($_POST["task_id"]);
        $task_tag = sanitize_text_field($_POST["task_tag"]);

        $tableName = "{$wpdb->prefix}noobtasks";

        $updated = $wpdb->update( $tableName, ['task_completed' => true, 'completed_at' => date("Y-m-d H:m:s")], [ 'task_id' => $task_id ] );
 
        if ( false === $updated ) {
            // There was an error.
            echo json_encode(array(
                'res'=>false, 
                'message'=>__('Error: Task NOT marked complete.'),
                'data' => $updated
            ));
        } else {
            
            $current_user = wp_get_current_user();
            $return = Kartra_Api::postLeadAction( $current_user->user_email, $actions = [
                'assign_tag' => $task_tag,
            ]);
            
            // No error. You can check updated to see how many rows were changed.
            echo json_encode(array(
                'res'=>true, 
                'message'=>__('Task marked complete.'),
                'data' => $return
            ));
        }
        wp_die();
    }

    
    /**
     * Top level menu callback function
     */
    function noobtask_options_page_html() {

        // check user capabilities
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }
        
        // check if the user have submitted the settings
        // WordPress will add the "settings-updated" $_GET parameter to the url
        if ( isset( $_GET['settings-updated'] ) ) {
            // add settings saved message with the class of "updated"
            add_settings_error( 'noobtask_messages', 'noobtask_message', __( 'Settings Saved', 'noobtask' ), 'updated' );
        }
    
        // show error/update messages
        settings_errors( 'noobtask_messages' );
        ?>
        <div class="wrap">

        <style>
            table.widefat {
                border: none;
                box-shadow: none;
            }
        </style>
        <h1 class=""><?php echo esc_html( 'Noob Tasks' ); ?></h1>
            
        <div class="" style="background:white; padding:20px; border-radius:8px; margin-top:20px;">
        <form method="POST" action="" id="task_form">
            <table class="form-table" role="presentation">
                <tbody>
                    <tr class="noobtask_row">
                        <th scope="row">
                            <label for="task_name">Task Name</label>
                        </th>
                        <td>
                            <input type="text" id="task_name" name="task_name" style="width:100%; max-width:300px;">
                        </td>
                    </tr>
                    <tr class="noobtask_row">
                        <th scope="row">
                            <label for="task_link">Task Page Link</label>
                        </th>
                        <td>
                            <input type="text" id="task_link" name="task_link" style="width:100%; max-width:300px;">
                        </td>
                    </tr>
                    <tr class="noobtask_row">
                        <th scope="row">
                            <label for="task_selector">Task Element Selector</label>
                        </th>
                        <td>
                            <input type="text" id="task_selector" name="task_selector" style="width:100%; max-width:300px;">
                        </td>
                    </tr>
                    <tr class="noobtask_row">
                        <th scope="row">
                            <label for="task_tag">Kartra Tag</label>
                        </th>
                        <td>
                        <?php $tags = $this->kartra_obj->getKartraTags(); ?>
                            <select id="task_tag" name="task_tag" style="width:100%; max-width:300px;">
                                <?php foreach($tags['account_tags'] as $tag){ ?>
                                    <option value="<?php echo $tag; ?>"><?php echo $tag; ?></option>
                                <?php } ?>
                            </select>
                        </td>
                    </tr> 
                    <tr class="noobtask_row">
                        <th scope="row">
                            <label for="task_type">Task Level</label>
                        </th>
                        <td>
                            <select id="task_type" name="task_type" style="width:100%; max-width:300px;">
                                <option value="level_1">
                                    Level 1            </option>
                                <option value="level_2">
                                    Level 2            </option>
                                <!-- <option value="level_3">
                                    Level 3            </option>
                                <option value="level_4">
                                    Level 4            </option> -->
                            </select>
                        </td>
                    </tr>  
                   
                </tbody>
            </table>
            <input type="submit" value="<?php echo __('Add A New Task'); ?>" id="submit_task_form" class="button button-primary" style="margin-top:30px; width:150px;"/>
        </form>
        <?php $redirect_url = admin_url( '/?page=noobtask' ); ?>
        <script>
            jQuery('form#task_form').on('submit', function(e){
                e.preventDefault();

                jQuery.ajax({
                    type: 'POST',
                    dataType: 'json',
                    url: "<?php echo admin_url('admin-ajax.php'); ?>", 
                    data: { 
                        'action' : 'save_noobtask_ajax',
                        'task_name': this.elements.task_name.value,
                        'task_link': this.elements.task_link.value,
                        'task_selector': this.elements.task_selector.value,
                        'task_type': this.elements.task_type.value,
                        'task_tag': this.elements.task_tag.value,
                    },
                    success: function(data){
                        if (data.res == true){
                            // var taskTableLastRow = jQuery('#the-list');
                            // var newId = taskTableLastRow.value + 1;
                            // taskTableLastRow.append(
                            // '<tr><th scope="row" class="check-column"><input type="checkbox" name="bulk-delete[]" value="'+newId+'"></th><td class="task_name column-task_name has-row-actions column-primary" data-colname="Name"><p style="font-size:15px;"><b>'+data.data.task_name+'</b></p><button type="button" class="toggle-row"><span class="screen-reader-text">Show more details</span></button></td><td class="task_type column-task_type" data-colname="Type"><p>'+data.data.task_type+'</p></td><td class="task_tag column-task_tag" data-colname="Tag"><i>'+data.data.task_tag+'</i></td><td class="task_completed column-task_completed" data-colname="Status"><p class="text-red" style="color:red;"><i>--</i></p></td></tr>'
                            // );
                            location.href = '<?php echo $redirect_url; ?>';
                        }else{
                            alert(data.message);    // fail
                        }
                    }
                });
            });
            </script>

            </div>

            <div class="" style="background:white; padding:20px; border-radius:8px; margin-top:40px;">
                <form id="events-filter" method="get">
                    <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
                    <?php $this->tasks_obj->prepare_items(); $this->tasks_obj->display(); ?>
                </form>
             </div>
        </div>
        <?php
    }
}