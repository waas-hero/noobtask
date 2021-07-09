<?php 
namespace WaasHero;

class Kartra_Api {

    public const KARTRA_API_ENDPOINT = 'https://app.kartra.com/api';
    private const NOOBTASK_APP_ID = 'kNLBdAFmuOhM';
    //TODO: get api key from ENV or other safer method
    private static $noobtask_user_api_key = KARTRA_API_KEY ? KARTRA_API_KEY : '';
    private static $noobtask_user_api_pass = KARTRA_API_PASS ? KARTRA_API_PASS : '';
    public $tags_object;

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
                    'data' => $server_json,
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
                    'data' => $server_json,
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
                'data' => $server_json
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

    function getKartraLists(){

        $array = array(
            'app_id' => self::NOOBTASK_APP_ID,
            'api_key' => self::$noobtask_user_api_key,
            'api_password' => self::$noobtask_user_api_pass,
            'actions' => array(
                '0' =>array(
                    'cmd' => 'retrieve_account_lists'
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
                'account_lists' => $server_json->account_lists
            ];
        }
    }

}