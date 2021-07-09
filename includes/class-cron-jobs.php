<?php

namespace WaasHero;

class CronJobs {

    public function get_default_tasks_from_db(){

        global $wpdb;
        $tableName = "{$wpdb->prefix}noobtasks";
        return $wpdb->get_results($wpdb->prepare( 
            "SELECT * FROM $tableName WHERE task_is_default = %d",
            true
        ));
		
	}

    public function update_kartra(){

        $tasks = $this->get_default_tasks_from_db();
        if(!$tasks){ return; }

        $admin_email = get_bloginfo( 'admin_email' );
        foreach($tasks as $task){

            //If task was not completed, dont update Kartra. This may change.
            if(!$task->task_completed){exit;}

            //valid values are only subscribe_lead_to_list, assign_tag, and give_points_to_lead
            $return = Kartra_Api::postLeadAction( $admin_email, $actions = [
                'assign_tag' => $task->task_tag,
                //'subscribe_lead_to_list' => $task->task_list,
            ]);

            $user = get_user_by_email( $admin_email );
            update_user_meta($user->ID, "noobtask-cronjob-".sanitize_title_with_dashes($task->task_tag), $return);
        }
        
    }



}