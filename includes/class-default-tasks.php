<?php

class Default_Tasks {


    public static function auto_tasks(){

        return [
            [
                'task_name' => 'Setup Seller Site',
                'task_desc' => 'Finish setting up your seller site.',
                'task_link' => '',
                'task_selector' => '.noobtask-0',
                'task_completed' => null,
                'task_tag' => 'Setup Seller Site',
                //'task_list' => 'Setup Seller Site',
                'site_type' => 'seller',
                'visible' => true,
                'task_is_default' => true,
            ],
            [
                'task_name' => 'Setup Buyer Site',
                'task_desc' => 'Finish setting up your buyer site.',
                'task_link' => '',
                'task_selector' => '.noobtask-1',
                'task_completed' => null,
                'task_tag' => 'Setup Buyer Site',
                //'task_list' => 'Setup Buyer Site',
                'site_type' => 'buyer',
                'visible' => true,
                'task_is_default' => true,
            ],
            [
                'task_name' => 'Setup Investor Site',
                'task_desc' => 'Finish setting up your investor site.',
                'task_link' => '',
                'task_selector' => '.noobtask-2',
                'task_completed' => null,
                'task_tag' => 'Setup Investor Site',
                //'task_list' => 'Setup Investor Site',
                'site_type' => 'investor',
                'visible' => true,
                'task_is_default' => true,
            ],
            [
                'task_name' => 'First Login (seller site)',
                'task_desc' => 'User logged in to their seller site.',
                'task_link' => '',
                'task_selector' => '.noobtask-3',
                'task_completed' => null,
                'task_tag' => 'Seller Site First Login',
                //'task_list' => 'Seller Site First Login',
                'site_type' => 'seller',
                'visible' => false,
                'task_is_default' => true,
            ],
            [
                'task_name' => 'First Login (buyer site)',
                'task_desc' => 'User logged in to their buyer site.',
                'task_link' => '',
                'task_selector' => '.noobtask-4',
                'task_completed' => null,
                'task_tag' => 'Buyer Site First Login',
                //'task_list' => 'Buyer Site First Login',
                'site_type' => 'buyer',
                'visible' => false,
                'task_is_default' => true,
            ],
            [
                'task_name' => 'First Login (investor site)',
                'task_desc' => 'User logged in to their investor site.',
                'task_link' => '',
                'task_selector' => '.noobtask-5',
                'task_completed' => null,
                'task_tag' => 'Investor Site First Login',
                //'task_list' => 'Investor Site First Login',
                'site_type' => 'investor',
                'visible' => false,
                'task_is_default' => true,
            ],
            [
                'task_name' => 'Renew Plan',
                'task_desc' => 'User renewed their plan.',
                'task_link' => '',
                'task_selector' => '.noobtask-6',
                'task_completed' => null,
                'task_tag' => 'Renew Plan',
                //'task_list' => 'Renew Plan',
                'site_type' => null,
                'visible' => false,
                'task_is_default' => true,
            ],
            [
                'task_name' => 'Renew 30 Days',
                'task_desc' => 'User has 30 days to renew their plan.',
                'task_link' => '',
                'task_selector' => '.noobtask-7',
                'task_completed' => null,
                'task_tag' => 'Renew 30 Days',
                //'task_list' => 'Renew 30 Days',
                'site_type' => null,
                'visible' => false,
                'task_is_default' => true,
            ],
            [
                'task_name' => 'Add A Custom Logo',
                'task_desc' => 'Add your companies logo to your website. This is necessary to provide consistent branding across your platforms.',
                'task_link' => '',
                'task_selector' => '.noobtask-8',
                'task_completed' => null,
                'task_tag' => 'Added Custom Logo',
                //'task_list' => 'Login After Setup',
                'site_type' => null,
                'visible' => true,
                'task_is_default' => true,
            ],
            
        ];
		
	}

    //Adds site owner id to a blog option when it's created. We can use this later.
    function add_site_owner_to_options($blog_id, $user_id){
        add_option( 'site_owner', $user_id );
    }   

    //Adds _new_user meta to new users at registration
    function noobtask_register_add_meta($user_id) { 
        add_user_meta($user_id, '_new_user', '1');
    }

    //Track last user logins
    function track_user_login($user_login, $user) {
        update_user_meta($user->ID, 'last_login', time());
    }
    //Checks value at user login, and updates Kartra with Tag and/or List
    function noobtask_first_user_login($user_login, $user) {

        $new_user = get_user_meta($user->ID, '_new_user', true);

        if ($new_user) {

            $site_type = WaasHero\NoobTask_Options_Api::get('site_type');
            //Constant MUST be defined in wp-config.php or elsewhere, Valid values are buyer, seller, investor
            switch (strtolower($site_type)) {
                case 'buyer':
                    WaasHero\NoobTask_Api::update(['task_completed' => true, 'completed_at' => date("Y-m-d H:m:s")],[ 'task_name' => 'Buyer Site First Login' ]);
                    break;
                case 'seller':
                    WaasHero\NoobTask_Api::update(['task_completed' => true, 'completed_at' => date("Y-m-d H:m:s")],[ 'task_name' => 'Seller Site First Login' ]);
                    break;
                case 'investor':
                    WaasHero\NoobTask_Api::update(['task_completed' => true, 'completed_at' => date("Y-m-d H:m:s")],[ 'task_name' => 'Investor Site First Login' ]);
                    break;
            }
        
            if ( $return['status'] == 200 ) {
                update_user_meta($user->ID, '_new_user', $return);
            }

        }
    }

    function check_for_custom_logo(){
        $task = WaasHero\NoobTask_Api::getByName('Add A Custom Logo');
        if( has_custom_logo() && $task && !$task['task_completed'] ){
            return WaasHero\NoobTask_Api::update(['task_completed' => true, 'completed_at' => date("Y-m-d H:m:s")],[ 'task_name' => 'Add A Custom Logo' ]);
        }
        return false;
    }

}