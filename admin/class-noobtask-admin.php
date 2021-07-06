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

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Noobtask_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Noobtask_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/noobtask-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Noobtask_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Noobtask_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/noobtask-admin.js', array( 'jquery' ), $this->version, false );

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
		?>
		<div class="noobtask-list-container">
			<p class="noobtask-title"><?php echo strtoupper(__('Get Started')); ?></p>
			<div class="noobtask-list" style="width:100%; display:flex; list-style-type: none;">
	
				<?php foreach($tasks as $task){ ?>

					<button data-task='<?php echo json_encode($task); ?>' class="noobtask-item <?php if($task['task_completed']){echo 'noobtask-completed';}else{echo 'noobtask-incomplete';} ?>">
						<p id="noobtask-<?php echo $task['task_id']; ?>" class="noobtask-name"><?php echo strtoupper($task['task_name']); ?></p>
					</button>
			
			<?php } ?>
			</div>
		</div>

		<!-- The Noobtask Modal -->
		<div id="noobtaskModal" class="modal" style="">
			<div class="modal-inner" >
				<!-- Modal content -->
				<div class="modal-content">
					<div class="noobtask-modal-header">
						<div class="noobtask-modal-title"></div>
						<span id="noobtaskCloseBtn" class="noobtask-close">&times;</span>
					</div>
					<p class="noobtask-message">You still need to complete this task.</p>
					<a class="noobtask-modal-link"><?php echo __('Complete Task'); ?></a>
				<button class="noobtask-complete-btn"><?php echo __('Mark Task As Complete'); ?></button>
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
			
		<div class="noobtask-list-container">
			<p class="noobtask-title"><?php echo strtoupper(__('Get Started')); ?></p>
			<div class="noobtask-list<?php echo $a['classes']; ?>" style="width:100%; list-style-type: none;<?php echo $a['style']; ?>">
				<button class="task-open-btn"><</button>
				<?php foreach($tasks as $task){ ?>

					<button data-task='<?php echo json_encode($task); ?>' class="noobtask-item <?php if($task['task_completed']){echo 'noobtask-completed';}else{echo 'noobtask-incomplete';} ?>">
						<p id="noobtask-<?php echo $task['task_id']; ?>" class="noobtask-name"><?php echo strtoupper($task['task_name']); ?></p>
					</button>
			
			<?php } ?>
			</div>
		</div>
		<!-- The Modal -->
		<div id="noobtaskModal" class="modal" style="">
			<div class="modal-inner" >
				<!-- Modal content -->
				<div class="modal-content">
				<div class="noobtask-modal-title"></div>
				<span class="close">&times;</span>
				<p class="noobtask-message">You still need to complete this task.</p>
				<a class="noobtask-modal-link"><?php echo __('Complete Task'); ?></a>
				<button class="noobtask-complete-btn"><?php echo __('Mark Task As Complete'); ?></button>
				</div>
			</div>
		</div>
			<script>

				// Get the modal
				var modal = document.getElementById("noobtaskModal");

				// Get the button that opens the modal
				var btn = document.getElementById("noobtaskBtn");

				// Get the <span> element that closes the modal
				var span = document.getElementsByClassName("close")[0];

				jQuery(".noobtask-complete-btn").click(function () {
					var taskID = jQuery(this).attr("data-id");
					var taskTag = jQuery(this).attr("data-tag");
					console.log(taskTag);

					jQuery.ajax({
						type: 'POST',
						dataType: 'json',
						url: "<?php echo admin_url('admin-ajax.php'); ?>", 
						data: { 
							'action' : 'complete_noobtask_ajax',
							'task_id': taskID,
							'task_tag': taskTag,
						},
						success: function(data){
							console.log(data);
						}
					});
				});
				jQuery(".noobtask-item").click(function () {
					var task = JSON.parse(jQuery(this).attr("data-task"));
					var name = task.task_name;
					
					jQuery(".noobtask-modal-title").text(task.task_name);
					jQuery(".noobtask-complete-btn").attr('data-id', task.task_id);
					jQuery(".noobtask-complete-btn").attr('data-tag', task.task_tag);

					if(task.task_completed){
						jQuery(".noobtask-message").text('Task Complete!');
					} 

					if(task.task_link){
						jQuery(".noobtask-modal-link").attr('href', task.task_link+'?highlight='+task.task_selector);
					} else {
						jQuery(".noobtask-modal-link").hide();
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
        return \Task_List::get_tasks();
    }

}
