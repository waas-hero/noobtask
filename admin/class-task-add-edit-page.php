<?php

namespace WaasHero;

/**
 * The plugin class that registers and handles task index/add/edit page.
 *
 *
 * @since      1.0.0
 * @package    Noobtask
 * @subpackage Noobtask/admin
 * @author     J Hanlon <j@waashero.com>
 */

class Task_Add_Edit_Page {

    // task WP_List_Table object
    public $tasks_obj;
    public $tags_object;
    
    /**
     * custom settings objects
     */
    function init() {

        $this->tasks_obj = new \Task_List();
        $this->kartra_obj = new Kartra_Api();

    }
    
    /**
     * Add a top level menu page.
     */
    function add_menu() {
        add_menu_page(
            'Starter Tasks',
            'Starter Tasks',
            'manage_options',
            'noobtask'
        );
        add_submenu_page(
            'noobtask',
            'Starter Tasks',
            'Tasks',
            'manage_options',
            'noobtask',
            [$this, 'page_html']
        );
    }
    
    function save_noobtask_ajax(){
        global $wpdb;

        $task_name = sanitize_text_field($_POST["task_name"]);
        $task_list = sanitize_text_field($_POST["task_list"]);
        $task_tag = sanitize_text_field($_POST["task_tag"]);
        $task_link = sanitize_url($_POST["task_link"]);
        $task_selector = sanitize_text_field($_POST["task_selector"]);
        $task_desc = sanitize_text_field($_POST["task_desc"]);
        $visible = intval($_POST["visible"]);
        $site_type = sanitize_text_field($_POST["site_type"]);

        $dataArray = array( 
            'task_name' => $task_name, 
            'task_desc' => $task_desc,
            'task_link' => $task_link,
            'task_selector' => $task_selector,
            'task_list' => $task_list, 
            'task_tag' => $task_tag, 
            'visible' => $visible,
            'site_type' => $site_type,
            'task_completed' => false,
        );

        $response = NoobTask_Api::save($dataArray);
        echo $response;
        wp_die();
    }

    function save_noobtask_options_ajax(){
        global $wpdb;

        $name = sanitize_text_field($_POST["name"]);
        $value = sanitize_text_field($_POST["value"]);

        $response = NoobTask_Options_Api::save($name,$value);
        echo $response;

        wp_die();
    }

    function complete_noobtask_ajax(){
        global $wpdb;

        $task_id = intval($_POST["task_id"]);
        $task_tag = sanitize_text_field($_POST["task_tag"]);
        $task_list = sanitize_text_field($_POST["task_list"]);
 
        $dataArray = [
            'task_completed' => true, 
            'completed_at' => date("Y-m-d H:m:s")
        ];
        $whereArray = [ 
            'task_id' => $task_id 
        ];

        $response = NoobTask_Api::update($dataArray, $whereArray);
        
        if ( $response['res'] == false ) {
            // There was an error.
            echo $response;

        } else {
            
            //valid values are only subscribe_lead_to_list, assign_tag, and give_points_to_lead
            $current_user = wp_get_current_user();
            $return = Kartra_Api::postLeadAction( $current_user->user_email, $actions = [
                'assign_tag' => $task_tag,
                'subscribe_lead_to_list' => $task_list,
            ]);
            
            // No error. 
            echo $response;
        }
        wp_die();
    }

    
    /**
     * Top level menu callback function
     */
    function page_html() {

        // check user capabilities
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }
        
        if(!KARTRA_API_KEY){
            $noApiKeyError = '<b>Don\'t forget to add define(\'KARTRA_API_KEY\', \'yourapikey\'); and define(\'KARTRA_API_PASS\', \'yourapipassword\'); to wp-config.php.<br/> The key and password values can be found in your Kartra Account using the following link. <a target="_blank" href="https://app.kartra.com/integrations/api/key">KARTRA API KEY</a></b>';
        }
        $site_type = NoobTask_Options_Api::get('site_type');
        ?>
        <div class="wrap">

        <style>
            table.widefat {
                border: none;
                box-shadow: none;
            }
        </style>

        <h1 class=""><?php echo get_admin_page_title(); ?></h1>
            
        <div class="noobtask-adminbox" style="background:white; padding:20px; border-radius:8px; margin-top:20px;">

            <form method="POST" action="" id="task_options_form">
                <table class="form-table" role="presentation">
                    <tbody>
                        <tr class="noobtask_row">
                            <th scope="row">
                                <label for="site_type">REI Site Type</label>
                            </th>
                            <td>
                                <input type="text" value="<?php echo $site_type; ?>" id="site_type" name="site_type" style="width:100%; max-width:300px;">
                                <p class="description">Valid options are <b>seller</b>, <b>buyer</b>, or <b>investor</b>.</p>

                            </td>
                        </tr>
                    </tbody>
                </table>
                <input type="submit" value="<?php echo __('Save Options'); ?>" id="submit_task_options_form" class="button button-primary" style="margin-top:30px; width:150px;"/>
            </form>
        </div>
        <div class="" style="background:white; padding:20px; border-radius:8px; margin-top:20px;">
        
        <?php if( isset($noApiKeyError) ){ 
            echo "<div class='text-danger' style='color:red;'>".__($noApiKeyError)."</div>";
         } ?>

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
                            <label for="task_desc">Task Description</label>
                        </th>
                        <td>
                            <textarea id="task_desc" name="task_desc" style="width:100%; max-width:300px;">
                            </textarea>
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
                            <label for="site_type">Site Type</label>
                        </th>
                        <td>
                            <input type="text" id="site_type" name="site_type" style="width:100%; max-width:300px;">
                        </td>
                    </tr>
  
                    <tr class="noobtask_row">
                        <th scope="row">
                            <label for="task_tag">Kartra Tag</label>
                        </th>
                        <td>
                        <?php $tags = $this->kartra_obj->getKartraTags(); ?>
                            <select id="task_tag" name="task_tag" style="width:100%; max-width:300px;">
                                <?php if( isset($tags['account_tags']) ){ ?>
                                    <option value=""><?php echo __('Don\'t Use A Tag'); ?></option>
                                <?php foreach($tags['account_tags'] as $tag){?>
                                    <option value="<?php echo $tag; ?>"><?php echo $tag; ?></option>
                                <?php } } else { ?>
                                    <option value=""><?php echo __('No Tags Found'); ?></option>
                                <?php } ?>
                            </select>
                        </td>
                    </tr> 
                    <tr class="noobtask_row">
                        <th scope="row">
                            <label for="task_tag">Kartra List</label>
                        </th>
                        <td>
                        <?php $lists = $this->kartra_obj->getKartraLists(); ?>
                            <select id="task_list" name="task_list" style="width:100%; max-width:300px;">
                                <?php if( isset($lists['account_lists']) ){ ?>
                                    <option value=""><?php echo __('Don\'t Use A List'); ?></option>
                                <?php foreach($lists['account_lists'] as $list){?>
                                    <option value="<?php echo $list; ?>"><?php echo $list; ?></option>
                                <?php } } else { ?>
                                    <option value=""><?php echo __('No Lists Found'); ?></option>
                                <?php } ?>
                            </select>
                        </td>
                    </tr> 
                    <tr class="noobtask_row">
                        <th scope="row"><label for="visible">Visible</label></th>
                        <td><label><input name="visible" id="visible" type="checkbox" value="1" <?php checked( get_site_option( 'visible'), '1' ) ?>>Make this step visible.</label></td>
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
                        'task_desc': this.elements.task_desc.value,
                        'task_link': this.elements.task_link.value,
                        'task_selector': this.elements.task_selector.value,
                        'task_list': this.elements.task_list.value,
                        'task_tag': this.elements.task_tag.value,
                        'site_type': this.elements.site_type.value,
                        'visible': this.elements.visible.value,
                    },
                    success: function(data){
                        if (data.res == true){
                            location.href = '<?php echo $redirect_url; ?>';
                        }else{
                            alert(data.message);    // fail
                        }
                    }
                });
            });
            </script>

        <script>
            jQuery('form#task_options_form').on('submit', function(e){
                e.preventDefault();

                jQuery.ajax({
                    type: 'POST',
                    dataType: 'json',
                    url: "<?php echo admin_url('admin-ajax.php'); ?>", 
                    data: { 
                        'action' : 'save_noobtask_options_ajax',
                        'name': this.elements.site_type.name,
                        'value': this.elements.site_type.value,
                    },
                    success: function(data){
                        if (data.res == true){
                            location.href = '<?php echo $redirect_url; ?>'+'&updated=true';
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

    function admin_notices(){

        if( isset($_GET['page']) && $_GET['page'] == 'noobtask' && isset( $_GET['updated'] )  ) {
            echo '<div id="message" class="updated notice is-dismissible"><p>Options updated.</p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>';
        }
    
    }
}