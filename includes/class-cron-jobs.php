<?php

namespace WaasHero;

class CronJobs {

    //TODO: Make this into ONE kartra call. Tried but need to figure out array?
    public function update_kartra(){

        $tasks = NoobTask_Api::getDefault();
        
        if(!$tasks){ return; }
        
        $site_type = NoobTask_Options_Api::get('site_type');

        $return = [];

        foreach($tasks as $task){
            
            //Check for Site Type. 
            //If task was not completed, dont update Kartra. This may change.
            if($task['task_completed'] && $task['site_type'] == $site_type){

                $response = Kartra_Api::postLeadAction( get_bloginfo( 'admin_email' ),  [
                    'assign_tag' => $task['task_tag'],
                    //'subscribe_lead_to_list' => $task->task_list,
                    //'give_points_to_lead' => ''
                ]);
                $return[] = $response['data'];

            }

        }

        NoobTask_Options_Api::save("cronjob-kartra", json_encode($return));
        
    }


}