jQuery(document).ready(function() {

    if(!noobTasks){ return; }

    const taskArray = [];

    noobTasks.forEach(function(task) {
        if( ! jQuery(task.task_selector).length ){return;};
        taskArray.push({
            element: task.task_selector,   
            //stageBackground: '#ffffff',   // This will override the one set in driver
            popover: {                    // There will be no popover if empty or not given
                position: 'right',
                //className: 'popover-class', 
                title: task.task_name,             
                description: task.task_desc, 
                showButtons: true,         
                doneBtnText: 'Done',
                closeBtnText: 'Close',
                nextBtnText: 'Next',
                prevBtnText: 'Previous',
            },
            onNext: () => {},             // Called when moving to next step from current step
            onPrevious: () => {},         // Called when moving to previous step from current step
        })
    });

    //define all steps in an array & start the tour
    if(taskArray.length){
        const driver = new Driver({animate: true});
        driver.defineSteps(taskArray);
        driver.start();
    }
  
});