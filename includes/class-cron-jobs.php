<?php

namespace WaasHero;

class CronJobs {

    public function update_kartra(){

        $tasks = WaasHero\NoobTask_Api::getDeafult();

        if(!$tasks){ return; }

        foreach($tasks as $task){
            //If task was not completed, dont update Kartra. This may change.
            if(!$task->task_completed){exit;}
            $return = Kartra_Api::postLeadAction( $admin_email, $actions = [
                'assign_tag' => $task->task_tag,
                //'subscribe_lead_to_list' => $task->task_list,
                //'give_points_to_lead' => ''
            ]);
            WaasHero\NoobTask_Options_Api::save("cronjob-".sanitize_title_with_dashes($task->task_tag), $return);
        }
        
    }



}