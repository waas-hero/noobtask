<?php 
namespace WaasHero;

class Kartra_Api {

    public const KARTRA_API_ENDPOINT = 'https://app.kartra.com/api';
    private const NOOBTASK_APP_ID = 'kNLBdAFmuOhM';
    //TODO: get api key from ENV or other safer method
    private static $noobtask_user_api_key = 'xGrLpEnH';
    private static $noobtask_user_api_pass = 'iRImGnfPLsVX';
    public $tags_object;

    // Declare a public constructor
    public function __construct() { 
        $this->setApiKeys();
    }

    private function setApiKeys(){
        //
    }

    /**
     * @internal never define functions inside callbacks.
     * these functions could be run multiple times; this would result in a fatal error.
     */
    
    /**
     * custom option and settings
     */
    function kartra_settings_init() {

        // Register a new setting for "kartra_options" page.
        register_setting( 'kartra_options', 'kartra_options' );
    
        // Register a new section in the "kartra_options" page.
        add_settings_section(
            'kartra_section_developers',
            __( 'Custom Completion List for New Users', 'kartra_options' ), [$this, 'kartra_section_developers_callback'],
            'kartra_options'
        );
    

        // Register a new field in the "kartra_section_developers" section, inside the "kartra_options" page.
        add_settings_field(
            'kartra_field_name', // As of WP 4.6 this value is used only internally.
                                    // Use $args' label_for to populate the id inside the callback.
                __( 'Tag Name', 'kartra_options' ),
            [$this, 'kartra_field_name_cb'],
            'kartra_options',
            'kartra_section_developers',
            array(
                'label_for'         => 'kartra_field_name',
                'class'             => 'kartra_row',
                'kartra_custom_data' => 'custom',
            )
        );

        add_settings_field(
            'kartra_field_type', // As of WP 4.6 this value is used only internally.
                                    // Use $args' label_for to populate the id inside the callback.
                __( 'Type', 'kartra_options' ),
            [$this, 'kartra_field_type_cb'],
            'kartra_options',
            'kartra_section_developers',
            array(
                'label_for'         => 'kartra_field_type',
                'class'             => 'kartra_row',
                'kartra_custom_data' => 'custom',
            )
        );

        add_settings_field(
            'kartra_field_type', // As of WP 4.6 this value is used only internally.
                                    // Use $args' label_for to populate the id inside the callback.
                __( 'Type', 'kartra_options' ),
            [$this, 'kartra_field_type_cb'],
            'kartra_options',
            'kartra_section_developers',
            array(
                'label_for'         => 'kartra_field_type',
                'class'             => 'kartra_row',
                'kartra_custom_data' => 'custom',
            )
        );

        // Register a new section in the "kartra_options" page.
        add_settings_section(
            'kartra_section_tagtable',
            __( 'Available Tasks', 'kartra_options' ), [$this, 'kartra_section_tagtable_callback'],
            'kartra_options'
        );
    }

    static function createLead( $email ) {
 
        $array = array(
            'app_id' => self::NOOBTASK_APP_ID,
            'api_key' => self::$noobtask_user_api_key,
            'api_password' => self::$noobtask_user_api_pass,
            'lead' => [
                'email' => $email,
            ], 
            'actions' => [ 
                '0' => [ 
                    'cmd' => 'create_lead' 
                ] 
            ]
        );

        $server_json = self::curlPostKartra($array);

        switch ($server_json->status) {
            case "Error" :
                return [
                    'status' => 500,
                    'message' => $server_json->message,
                ];
            case "Success" :
                return [
                    'status' => 200,
                    'message' => $server_json->message,
                    'lead' => $server_json->lead_details
                ];
        }

    }

    static function getLead( $email ) {
 
        $array = array(
            'app_id' => self::NOOBTASK_APP_ID,
            'api_key' => self::$noobtask_user_api_key,
            'api_password' => self::$noobtask_user_api_pass,
            'get_lead' => array(
                'email' => $email,
            ),
        );

        $server_json = self::curlPostKartra($array);

        switch ($server_json->status) {
            case "Error" :
                return [
                    'status' => 500,
                    'message' => $server_json->message,
                ];
            case "Success" :
                return [
                    'status' => 200,
                    'message' => $server_json->message,
                    'lead' => $server_json->lead_details
                ];
        }

    }

    static function curlPostKartra($array){

        $ch = curl_init();
        // CONNECT TO THE API SYSTEM VIA THE APP ID, AND VERIFY MY API KEY AND PASSWORD, IDENTIFY THE LEAD, AND SEND THE ACTIONS…
        curl_setopt($ch, CURLOPT_URL, self::KARTRA_API_ENDPOINT);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($array));
        // REQUEST CONFIRMATION MESSAGE FROM API…
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $server_output = curl_exec ($ch);
        curl_close ($ch);

        return json_decode($server_output);
    }

    static function postLeadAction( $email, $actions = [] ){
        
        //check for lead on kartra. Create one if it doesn't exist.
        $lead = self::getLead( $email );
        if($lead['status'] !== 200){
            self::createLead( $email );
        }

        $actionArray = [];
        
        if(isset($actions['assign_tag'])){
            array_push($actionArray,[
                'cmd' => 'assign_tag',
                'tag_name' => $actions['assign_tag']
            ]);
        }
        if(isset($actions['subscribe_lead_to_list'])){
            array_push($actionArray,[
                'cmd' => 'subscribe_lead_to_list',
                'list_name' => $actions['subscribe_lead_to_list']
            ]);
        }
        if(isset($actions['give_points_to_lead'])){
            array_push($actionArray,[
                'cmd' => 'give_points_to_lead',
                'tag_name' => $actions['give_points_to_lead']
            ]);
        }
      
        $array = array(
            'app_id' => self::NOOBTASK_APP_ID,
            'api_key' => self::$noobtask_user_api_key,
            'api_password' => self::$noobtask_user_api_pass,
            'lead' => array(
                'email' => $email
            ),
            'actions' => $actionArray
        );

        $server_json = self::curlPostKartra($array);

        // CONDITIONAL FOR FURTHER INSTRUCTIONS…
        if ($server_json->status == "Error") {
            return [
                'status' => 500,
                'message' => $server_json->message,
                'type' => $server_json->type
            ];
        } else {
            return [
                'status' => 200,
                'message' => $server_json->actions[0]->assign_tag->message,
                'type' => $server_json->actions[0]->assign_tag->type
            ];
            //echo "lead status: ".$server_json->status."<br>";

            // echo "Status: ".$server_json->actions[0]->assign_tag->status.", Message:".$server_json->actions[0]->assign_tag->message.",Type:".$server_json->actions[0]->assign_tag->type."<br>";

            // echo "Status: ".$server_json->actions[1]->subscribe_lead_to_list->status.",
            // Message: ".$server_json->actions[1]->subscribe_lead_to_list->message.",
            // Type:".$server_json->actions[1]->subscribe_lead_to_list->type."<br>";

            // echo "Status: ".$server_json->actions[2]->give_points_to_lead->status.",
            // Message: ".$server_json->actions[2]->give_points_to_lead->message.",
            // Type:".$server_json->actions[2]->give_points_to_lead->type."<br>";
        }
        
    }

    function getKartraTags(){

        $array = array(
            'app_id' => self::NOOBTASK_APP_ID,
            'api_key' => self::$noobtask_user_api_key,
            'api_password' => self::$noobtask_user_api_pass,
            'actions' => array(
                '0' =>array(
                    'cmd' => 'retrieve_account_tags'
                ),
            )
        );
            
        $server_json = self::curlPostKartra($array);
   
        // CONDITIONAL FOR FURTHER INSTRUCTIONS…
        if ($server_json->status == "Error") {
            return [
                'status' => 500,
                'message' => $server_json->message,
                'type' => $server_json->type
            ];
        } elseif ($server_json->status == "Success") {
            return [
                'status' => 200,
                'account_tags' => $server_json->account_tags
            ];
        }
    }

        /**
     * Developers section callback function.
     *
     * @param array $args  The settings array, defining title, id, callback.
     */
    function kartra_section_developers_callback( $args ) { ?>
        
        <p id="<?php echo esc_attr( $args['id'] ); ?>">
            <?php esc_html_e( 'Customize the tags your new users should complete.', 'kartra' ); ?>
        </p>
        
    <?php }

    /**
     * Developers section callback function.
     *
     * @param array $args  The settings array, defining title, id, callback.
     */
    function kartra_section_tagtable_callback( $args ) { ?>
        
        <define_syslog_variables id="<?php echo esc_attr( $args['id'] );?>">
            Table here <?php  print_r( get_option('kartra_options') ); ?>
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
    function kartra_field_name_cb( $args ) {
        // Get the value of the setting we've registered with register_setting()
        $options = get_option( 'kartra_options' ); ?>

        <input type="text" id="<?php echo esc_attr( $args['label_for'] ); ?>"
                data-custom="<?php echo esc_attr( $args['kartra_custom_data'] ); ?>"
                name="kartra_options[<?php echo esc_attr( $args['label_for'] ); ?>]" 
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
    function kartra_field_type_cb( $args ) {
        // Get the value of the setting we've registered with register_setting()
        $options = get_option( 'kartra_options' ); ?>

        <select id="<?php echo esc_attr( $args['label_for'] ); ?>"
                data-custom="<?php echo esc_attr( $args['kartra_custom_data'] ); ?>"
                name="kartra_options[<?php echo esc_attr( $args['label_for'] ); ?>]"
                style="width:100%; max-width:300px;">
            <option value="level_1" <?php echo isset( $options[ $args['label_for'] ] ) ? ( selected( $options[ $args['label_for'] ], 'red', false ) ) : ( '' ); ?>>
                <?php esc_html_e( 'Level 1', 'kartra' ); ?>
            </option>
            <option value="level_2" <?php echo isset( $options[ $args['label_for'] ] ) ? ( selected( $options[ $args['label_for'] ], 'blue', false ) ) : ( '' ); ?>>
                <?php esc_html_e( 'Level 2', 'kartra' ); ?>
            </option>
            <option value="level_3" <?php echo isset( $options[ $args['label_for'] ] ) ? ( selected( $options[ $args['label_for'] ], 'blue', false ) ) : ( '' ); ?>>
                <?php esc_html_e( 'Level 3', 'kartra' ); ?>
            </option>
        </select>
        
    <?php }
    
    /**
     * Add the top level menu page.
     */
    function kartra_add_menu() {

        add_submenu_page(
            'noobtask',
            'Tags',
            'Tags',
            'manage_options',
            'noobtask-tags',
            [$this, 'kartra_options_page_html']
        );

    }
    
        
    function save_tag_ajax(){
        global $wpdb;

        $tag_name = sanitize_text_field($_POST["tag_name"]);
        $tag_description = sanitize_text_field($_POST["tag_description"]);


        $tableName = "{$wpdb->prefix}noobtask_tags";
        $insert_row = $wpdb->insert( 
                        $tableName, 
                        array( 
                            'tag_name' => $tag_name, 
                            'tag_description' => $tag_description,
                        )
                    );
        // if row inserted in table
        if($insert_row){
            echo json_encode(array('res'=>true, 'message'=>__('New tag added.'), 'data' =>array( 
                'tag_name' => $tag_name, 
                'tag_description' => $tag_description,
            )));
        }else{
            echo json_encode(array('res'=>false, 'message'=>__('Something went wrong. Please try again later.')));
        }
        wp_die();
    }

    
    /**
     * Top level menu callback function
     */
    function kartra_options_page_html() {

        // check user capabilities
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }
        
        
        // check if the user have submitted the settings
        // WordPress will add the "settings-updated" $_GET parameter to the url
        if ( isset( $_GET['settings-updated'] ) ) {
            // add settings saved message with the class of "updated"
            add_settings_error( 'kartra_messages', 'kartra_message', __( 'Settings Saved', 'kartra' ), 'updated' );
        }

        if ( ( isset( $_REQUEST['page'] ) && $_REQUEST['page'] == 'noobtask-tags' ) && ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'delete' ) && ( isset( $_REQUEST['tag'] ) && $_REQUEST['tag']) && isset($_REQUEST['_wpnonce'] ) ){

            if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'sp_delete_tag' ) ) {
                die( 'Nice try script kiddies.' );
            }

            $this->tags_object->delete_tag( $_REQUEST['tag'] );
    
            $redirect_url = admin_url( '/admin.php?page=noobtask-tags' );
            echo "<script>location.href = '$redirect_url';</script>";
            exit;
        }
    
        // show error/update messages
        settings_errors( 'kartra_messages' );
        ?>
        <div class="wrap">

        <style>
            table.widefat {
                border: none;
                box-shadow: none;
            }
        </style>
        <h1 class=""><?php echo get_admin_page_title(); ?></h1>
            
        
        <div class="" style="background:white; padding:20px; border-radius:8px; margin-top:40px;">
                <form id="events-filter" method="get">
                    <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
                    <?php $tags = $this->getKartraTags(); ?>
                    KARTRA TAGS:
                    <ul class="">
                        <?php foreach($tags['account_tags'] as $key => $tag){ ?>
                            <li class="" id="tag-<?php echo $key; ?>">
                                <h4 class=""><?php echo $tag; ?></h4>
                            </li>
                        <?php } ?>
                    </ul>
                </form>
             </div>

        <div class="" style="background:white; padding:20px; border-radius:8px; margin-top:40px;">
        <form method="POST" action="" id="tag_form">
            <table class="form-table" role="presentation">
                <tbody>
                    <tr class="kartra_row">
                        <th scope="row">
                            <label for="tag_name">Tag Name</label>
                        </th>
                        <td>
                            <input type="text" id="tag_name" name="tag_name" style="width:100%; max-width:300px;">
                        </td>
                    </tr>
                    <tr class="kartra_row">
                        <th scope="row">
                            <label for="tag_description">Tag Description</label>
                        </th>
                        <td>
                            <textarea id="tag_description" name="tag_description" style="width:100%; max-width:300px;"></textarea>
                        </td>
                    </tr>
                    
                   
                </tbody>
            </table>
            <input type="submit" value="<?php echo __('Add A New Tag'); ?>" id="submit_tag_form" class="button button-primary" style="margin-top:30px; width:150px;"/>
        </form>
        <?php $redirect_url = admin_url( '/admin.php?page=noobtask-tags' ); ?>
        <script>
            jQuery('form#tag_form').on('submit', function(e){
                e.preventDefault();

                jQuery.ajax({
                    type: 'POST',
                    dataType: 'json',
                    url: "<?php echo admin_url('admin-ajax.php'); ?>", 
                    data: { 
                        'action' : 'save_tag_ajax',
                        'tag_name': this.elements.tag_name.value,
                        'tag_description': this.elements.tag_description.value,
                    },
                    success: function(data){
                        if (data.res == true){
                            // var tagTableLastRow = jQuery('#the-list');
                            // var newId = tagTableLastRow.value + 1;
                            // tagTableLastRow.append(
                            // '<tr><th scope="row" class="check-column"><input type="checkbox" name="bulk-delete[]" value="'+newId+'"></th><td class="tag_name column-tag_name has-row-actions column-primary" data-colname="Name"><p style="font-size:15px;"><b>'+data.data.tag_name+'</b></p><button type="button" class="toggle-row"><span class="screen-reader-text">Show more details</span></button></td><td class="tag_type column-tag_type" data-colname="Type"><p>'+data.data.tag_type+'</p></td><td class="tag_tag column-tag_tag" data-colname="Tag"><i>'+data.data.tag_tag+'</i></td><td class="tag_completed column-tag_completed" data-colname="Status"><p class="text-red" style="color:red;"><i>--</i></p></td></tr>'
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
                    <?php $tags = $this->tags_object->get_local_tags(); ?>
                    LOCAL TAGS:
                    <ul class="">
                        <?php foreach($tags as $tag){ ?>
                            <li class="" id="tag-<?php echo $tag['id']; ?>">
                                <h4 class=""><?php echo $tag['tag_name']; ?></h4>
                                <p class=""><?php echo $tag['tag_description']; ?></p>
                                <?php echo $this->tags_object->delete_action( $tag ); ?>
                            </li>
                        <?php } ?>
                    </ul>
                </form>
             </div>
        </div>
        <?php
    }

}