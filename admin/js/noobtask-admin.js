(function( $ ) {
	'use strict';

	 $( document ).ready(function() {

		$( '.notice-dismiss' ).on( "click", function() {
			$( this ).parent().hide();
		  });
		
		//CheckForHighlight();
	});
	
	//highlightedElemclass has box shadow or border
	// function CheckForHighlight(){
	// 	let searchParams = new URLSearchParams(window.location.search)
	// 	if(searchParams.has('highlight')){
	// 		let param = searchParams.get('highlight')
	// 		$(param).addClass('highlightedElem');
	// 	}
	// }

})( jQuery );

jQuery(document).ready(function() {

	// Get the modal
	var modal = document.getElementById("noobtaskModal");

	if(!modal){
		return;
	}
	// Get the <span> element that closes the modal
	var span = document.getElementById("noobtaskCloseBtn");

	jQuery(".noobtask-complete-btn").click(function () {
		var taskID = jQuery(this).attr("data-id");
		var taskTag = jQuery(this).attr("data-tag");
		var taskList = jQuery(this).attr("data-list");

		jQuery.ajax({
			type: 'POST',
			dataType: 'json',
			url: ajaxurl, 
			data: { 
				'action' : 'complete_noobtask_ajax',
				'task_id': taskID,
				'task_tag': taskTag,
				'task_list': taskList,
			},
			success: function(data){
				console.log(data);
			}
		});
	});

	jQuery(".noobtask-item").click(function () {
		
		var task = JSON.parse(jQuery(this).attr("data-task"));
		var modal = document.getElementById("noobtaskModal");

		jQuery(modal).find(".noobtask-modal-title").text(task.task_name);
		var completeBtn = jQuery(modal).find(".noobtask-complete-btn");
		var modalLink = jQuery(modal).find(".noobtask-modal-link");

		if(task.task_completed == 1){
			jQuery(modal).find(".noobtask-modal-message").text('Task Complete!');
			jQuery(modal).find(".noobtask-modal-complete-icon").show();
			completeBtn.hide();
			modalLink.hide();
		} else {
			completeBtn.show();
			modalLink.show();
			jQuery(modal).find(".noobtask-modal-message").text('Task NOT Complete!');
			jQuery(modal).find(".noobtask-modal-complete-icon").hide();
			completeBtn.attr('data-id', task.task_id);
			completeBtn.attr('data-tag', task.task_tag);
			completeBtn.attr('data-list', task.task_list);
			if(task.task_link){
				modalLink.attr('href', task.task_link+'?highlight='+task.task_selector);
			} else {
				modalLink.hide();
			}
		}

		modal.style.display = "flex";
	});

	// When the user clicks on <span> (x), close the modal
	span.onclick = function() {
		modal.style.display = "none";
	}

	// When the user clicks anywhere outside of the modal, close it
	window.onclick = function(event) {
		if (event.target == modal) {
			modal.style.display = "none";
		}
	}

});

