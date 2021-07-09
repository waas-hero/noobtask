<?php

class Default_Tasks {


    public static function auto_tasks(){

        return [
            'setup_seller_site' => [
                'task_name' => 'Setup Seller Site',
                'task_link' => '',
                'task_selector' => '',
                'task_completed' => null,
                'task_tag' => 'Setup Seller Site',
                'task_list' => 'Setup Seller Site',
                'task_is_default' => true,
            ],
            'setup_buyer_site' => [
                'task_name' => 'Setup Buyer Site',
                'task_link' => '',
                'task_selector' => '',
                'task_completed' => null,
                'task_tag' => 'Setup Buyer Site',
                'task_list' => 'Setup Buyer Site',
                'task_is_default' => true,
            ],
            'setup_investor_site' => [
                'task_name' => 'Setup Investor Site',
                'task_link' => '',
                'task_selector' => '',
                'task_completed' => null,
                'task_tag' => 'Setup Investor Site',
                'task_list' => 'Setup Investor Site',
                'task_is_default' => true,
            ],
            'first_login_seller_site' => [
                'task_name' => 'First Login (seller site)',
                'task_link' => '',
                'task_selector' => '',
                'task_completed' => null,
                'task_tag' => 'Seller Site First Login',
                //'task_list' => 'Seller Site First Login',
                'task_is_default' => true,
            ],
            'first_login_buyer_site' => [
                'task_name' => 'First Login (buyer site)',
                'task_link' => '',
                'task_selector' => '',
                'task_completed' => null,
                'task_tag' => 'Buyer Site First Login',
                //'task_list' => 'Buyer Site First Login',
                'task_is_default' => true,
            ],
            'first_login_investor_site' => [
                'task_name' => 'First Login (investor site)',
                'task_link' => '',
                'task_selector' => '',
                'task_completed' => null,
                'task_tag' => 'Investor Site First Login',
                //'task_list' => 'Investor Site First Login',
                'task_is_default' => true,
            ],
            'renew_investor_site' => [
                'task_name' => 'Renew Investor Site',
                'task_link' => '',
                'task_selector' => '',
                'task_completed' => null,
                'task_tag' => 'Renew Investor Site',
                'task_list' => 'Renew Investor Site',
                'task_is_default' => true,
            ],
            'renew_investor_site' => [
                'task_name' => 'Renew Investor Site',
                'task_link' => '',
                'task_selector' => '',
                'task_completed' => null,
                'task_tag' => 'Renew Investor Site',
                'task_list' => 'Renew Investor Site',
                'task_is_default' => true,
            ],
            'renew_plan' => [
                'task_name' => 'Renew Plan',
                'task_link' => '',
                'task_selector' => '',
                'task_completed' => null,
                'task_tag' => 'Renew Plan',
                'task_list' => 'Renew Plan',
                'task_is_default' => true,
            ],
            'renew_30_days' => [
                'task_name' => 'Renew 30 Days',
                'task_link' => '',
                'task_selector' => '',
                'task_completed' => null,
                'task_tag' => 'Renew 30 Days',
                'task_list' => 'Renew 30 Days',
                'task_is_default' => true,
            ],
            'login_after_setup' => [
                'task_name' => 'Login After Setup',
                'task_link' => '',
                'task_selector' => '',
                'task_completed' => null,
                'task_tag' => 'Login After Setup',
                //'task_list' => 'Login After Setup',
                'task_is_default' => true,
            ],
            
        ];
		
	}

    //Adds site owner meta to a blog when created. We can use this later.
    function add_site_owner_to_options($blog_id, $user_id){
        add_site_option( 'site_owner_'.$blog_id, $user_id );
    }   

    //add _new_user meta to new users at registration
    function noobtask_register_add_meta($user_id) { 
        add_user_meta($user_id, '_new_user', '1');
    }

    //add last_login time to users meta
    function noobtask_add_last_login($user_login, $user){
        update_user_meta($user->ID, 'last_login', time());
    }

    //check value at user login, and update kartra with Tag and List
    function noobtask_first_user_login($user_login, $user) {

        $new_user = get_user_meta($user->ID, '_new_user', true);
        if ($new_user) {

            global $wpdb;
            $tableName = "{$wpdb->prefix}noobtasks";

            //Constant MUST be defined in wp-config.php or elsewhere, Valid values are buyer, seller, investor
            switch (SUBSITE_TYPE) {
                case 'buyer':
                    $return = WaasHero\Kartra_Api::postLeadAction( $user->user_email, $actions = [
                        'assign_tag' => 'Buyer Site First Login',
                        //'subscribe_lead_to_list' => 'Login After Setup',
                    ]);
                    $updated = $wpdb->update( $tableName, ['task_completed' => true, 'completed_at' => date("Y-m-d H:m:s")], [ 'task_tag' => 'Buyer Site First Login' ] );
                    break;
                case 'seller':
                    $return = WaasHero\Kartra_Api::postLeadAction( $user->user_email, $actions = [
                        'assign_tag' => 'Seller Site First Login',
                        //'subscribe_lead_to_list' => 'Login After Setup',
                    ]);
                    $updated = $wpdb->update( $tableName, ['task_completed' => true, 'completed_at' => date("Y-m-d H:m:s")], [ 'task_tag' => 'Seller Site First Login' ] );
                    break;
                case 'investor':
                    $return = WaasHero\Kartra_Api::postLeadAction( $user->user_email, $actions = [
                        'assign_tag' => 'Investor Site First Login',
                        //'subscribe_lead_to_list' => 'Login After Setup',
                    ]);
                    $updated = $wpdb->update( $tableName, ['task_completed' => true, 'completed_at' => date("Y-m-d H:m:s")], [ 'task_tag' => 'Investor Site First Login' ] );
                    break;
            }
            

            update_user_meta($user->ID, '_new_user', $return);
            $return = WaasHero\Kartra_Api::postLeadAction( $user->user_email, $actions = [
                'assign_tag' => 'Login After Setup',
                //'subscribe_lead_to_list' => 'Login After Setup',
            ]);
            $updated = $wpdb->update( $tableName, ['task_completed' => true, 'completed_at' => date("Y-m-d H:m:s")], [ 'task_name' => 'Login After Setup' ] );
    
            if ( false === $updated ) {
                // There was an error.
                update_user_meta($user->ID, '_new_user', 'Task Not Completed.');
            } 

        }
    }

}