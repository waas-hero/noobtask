<?php

namespace WaasHero;

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://waashero.com/
 * @since      1.0.0
 *
 * @package    Noobtask
 * @subpackage Noobtask/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Noobtask
 * @subpackage Noobtask/admin
 * @author     J Hanlon <j@waashero.com>
 */
class Noobtask_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		add_shortcode( 'task_shortcode', array( $this, 'taskShortcode' ));
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/noobtask-admin.css', array(), $this->version, 'all' );

		wp_register_style( 'driver-js-css', plugin_dir_url( __FILE__ ) . 'css/driver.min.css', array(), $this->version, false );

		wp_enqueue_style( 'driver-js-css' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/noobtask-admin.js', array( 'jquery' ), $this->version, false );

		wp_register_script( 'driver-js', plugin_dir_url( __FILE__ ) . 'js/driver.min.js', array( 'jquery' ), $this->version, false );

		wp_localize_script( 'driver-js', 'noobTasks', self::get_tasks() );

		wp_enqueue_script( 'driver-js' );

		wp_register_script( 'noobtask-tour', plugin_dir_url( __FILE__ ) . 'js/noobtask-tour.js', array( 'driver-js' ), $this->version, false );

		wp_enqueue_script( 'noobtask-tour' );
	}
	
	
	/**
	 * Add a widget to the dashboard.
	 *
	 * This function is hooked into the 'wp_dashboard_setup' action.
	 */
	public function add_dashboard_widgets() {
		wp_add_dashboard_widget(
			'noobtask_dashboard_widget',                         
			esc_html__( 'Tasks Widget', 'noobtask' ), 
			[$this, 'dashboard_widget_render']                  
		); 
	}

	/**
	 * Method to output the content of our Dashboard Widget.
	 */
	public function dashboard_widget_render() { 
		$tasks = self::get_tasks();
		// $job = new CronJobs;
		// print_r($job->get_default_tasks_from_db());
		echo 'Site Type: '.SUBSITE_TYPE;
		?>
		<div class="noobtask-list-container">
			
			<p class="noobtask-title"><?php echo strtoupper(__('Get Started')); ?></p>
			<div class="noobtask-list" style="width:100%; display:flex; flex-direction:column;list-style-type: none;">
	
				<?php foreach($tasks as $task){ ?>

					<button data-task='<?php echo json_encode($task); ?>' class="noobtask-item <?php if($task['task_completed']){echo 'noobtask-completed';}else{echo 'noobtask-incomplete';} ?>">
						<p id="noobtask-<?php echo $task['task_id']; ?>" class="noobtask-name"><?php echo strtoupper($task['task_name']); ?></p>
					</button>
			
			<?php } ?>
			</div>
		</div>

		<!-- The Noobtask Modal -->
		<div id="noobtaskModal" class="noobtask-modal" style="">
			<div class="noobtask-modal-inner" >
				<!-- Modal content -->
				<div class="noobtask-modal-content">
					<div class="noobtask-modal-header">
						<span class="noobtask-text">
							<span class="">Task: </span>
							<span class="noobtask-modal-title"></span>
						</span>
						
						<span id="noobtaskCloseBtn" class="noobtask-close">&times;</span>
					</div>
				<p class="noobtask-modal-message">You still need to complete this task.</p>
				<a class="noobtask-modal-btn noobtask-modal-link"><?php echo __('Complete Task'); ?></a>
				<button class="noobtask-modal-btn noobtask-complete-btn"><?php echo __('Mark Task As Complete'); ?></button>
				</div>
			</div>
		</div>

	<?php }


	/**
	 * Method to output the content of our task shortcode.
	 */
	public function taskShortcode( $atts, $content = "" ) {
		$a = shortcode_atts( array(
			'classes'=>'',
			'style'=>'',
			'hidden'=>'',					
			), $atts );

		$tasks = self::get_tasks();
		?>
			
		<div class="noobtask-list-container <?php echo $a['classes']; ?>">
		
			<button class="task-open-btn"><</button>
			<div class="">
			<p class="noobtask-title"><?php echo strtoupper(__('Get Started')); ?></p>
			<div class="noobtask-list">
				
				<?php foreach($tasks as $task){ ?>

					<button data-task='<?php echo json_encode($task); ?>' class="noobtask-item <?php if($task['task_completed']){echo 'noobtask-completed';}else{echo 'noobtask-incomplete';} ?>">
						<p id="noobtask-<?php echo $task['task_id']; ?>" class="noobtask-name"><?php echo strtoupper($task['task_name']); ?></p>
					</button>
			
				<?php } ?>
				</div>
			</div>
		</div>
		<!-- The Modal -->
		<div id="noobtaskModal" class="noobtask-modal" style="">
			<div class="noobtask-modal-inner" >
				<!-- Modal content -->
				<div class="noobtask-modal-content">
					<div class="noobtask-modal-header">
						<span class="noobtask-text">
							<span class="">Task: </span>
							<span class="noobtask-modal-title"></span>
						</span>
						
						<span id="noobtaskCloseBtn" class="noobtask-close">&times;</span>
					</div>
				<p class="noobtask-modal-message">You still need to complete this task.</p>
				<a class="noobtask-modal-btn noobtask-modal-link"><?php echo __('Complete Task'); ?></a>
				<button class="noobtask-modal-btn noobtask-complete-btn"><?php echo __('Mark Task As Complete'); ?></button>
				</div>
			</div>
		</div>
			<script>

				// Get the modal
				var modal = document.getElementById("noobtaskModal");

				// Get the button that opens the modal
				var btn = document.getElementById("noobtaskBtn");

				// Get the <span> element that closes the modal
				var span = document.getElementById("noobtaskCloseBtn");

				jQuery(".noobtask-complete-btn").click(function () {
					var taskID = jQuery(this).attr("data-id");
					var taskTag = jQuery(this).attr("data-tag");
					var taskList = jQuery(this).attr("data-list");
					console.log(taskTag);

					jQuery.ajax({
						type: 'POST',
						dataType: 'json',
						url: "<?php echo admin_url('admin-ajax.php'); ?>", 
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
			</script>
	<?php }

	/**
    * Retrieve customer’s task data from the database
    *
    * @return mixed
    */
    public static function get_tasks() {        
        return \Task_List::get_all_tasks();
    }

}
