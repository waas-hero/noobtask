<?php

if ( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class Task_List extends WP_List_Table {

    public $tags_object;

    /** Class constructor */
    public function __construct() {

        parent::__construct( [
            'singular' => __( 'Task', 'noobtask' ), //singular name of the listed records
            'plural' => __( 'Tasks', 'noobtask' ), //plural name of the listed records
            'ajax' => false //should this table support ajax?
        ] );
        
        $this->kartra_obj = new WaasHero\Kartra_Api();
        
    }

    /**
    * Retrieve customer’s data from the database
    *
    * @param int $per_page
    * @param int $page_number
    *
    * @return mixed
    */
    public static function get_tasks( $per_page = 5, $page_number = 1 ) {

        global $wpdb;
        
        $sql = "SELECT * FROM {$wpdb->prefix}noobtasks";
        
        if ( ! empty( $_REQUEST['orderby'] ) ) {
            $sql .= ' ORDER BY ' . esc_sql( $_REQUEST['orderby'] );
            $sql .= ! empty( $_REQUEST['order'] ) ? ' ' . esc_sql( $_REQUEST['order'] ) : ' ASC';
        }
        
        $sql .= " LIMIT $per_page";
        
        $sql .= ' OFFSET ' . ( $page_number - 1 ) * $per_page;
        
        $result = $wpdb->get_results( $sql, 'ARRAY_A' );
        
        return $result;
    }

    public static function get_all_tasks() {

        global $wpdb;
        $sql = "SELECT * FROM {$wpdb->prefix}noobtasks";
        return $wpdb->get_results( $sql, 'ARRAY_A' );
        
    }

    /**
    * Delete a task record.
    *
    * @param int $id customer ID
    */
    public static function delete_task( $id ) {
        global $wpdb;
        
        $wpdb->delete(
            "{$wpdb->prefix}noobtasks",
            [ 'task_id' => $id ],
            [ '%d' ]
        );
    }

    /**
     * Gets a list of CSS classes for the WP_List_Table table tag.
     *
     * @since 3.1.0
     *
     * @return string[] Array of CSS classes for the table tag.
     */
    protected function get_table_classes() {
        $mode = get_user_setting( 'posts_list_mode', 'list' );
 
        $mode_class = esc_attr( 'table-view-' . $mode );
 
        return array( 'noobtasks_table', 'widefat', 'fixed', 'striped', $mode_class, $this->_args['plural'] );
    }

    /**
    * Returns the count of records in the database.
    *
    * @return null|string
    */
    public static function record_count() {
        global $wpdb;
        
        $sql = "SELECT COUNT(*) FROM {$wpdb->prefix}noobtasks";
        
        return $wpdb->get_var( $sql );
    }

    /** Text displayed when no customer data is available */
    public function no_items() {
        _e( 'No tasks avaliable.', 'noobtask' );
    }

    /**
    * Method for name column
    *
    * @param array $item an array of DB data
    *
    * @return string
    */
    public function column_task_name( $item ) {

        $title = '<strong>' . $item['task_name'] . '</strong>';
        
        if($item['task_is_default']){
            return $title;
        }

        // create a nonce
        $delete_nonce = wp_create_nonce( 'sp_delete_task' );
        $actions = [
            'delete' => sprintf( '<a href="?page=%s&action=%s&task=%s&_wpnonce=%s">Delete</a>', esc_attr( $_REQUEST['page'] ), 'delete', absint( $item['task_id'] ), $delete_nonce )
        ];
        
        return $title . $this->row_actions( $actions, true );
  
    }

    /**
    * Render a column when no column specific method exists.
    *
    * @param array $item
    * @param string $column_name
    *
    * @return mixed
    */
    public function column_default( $item, $column_name ) {
            switch ( $column_name ) {
            case 'task_name':
                return '<p style="font-size:15px;"><b>'.$item[ $column_name ]."</b></p>";

            case 'task_desc':
            case 'site_type':
            case 'task_link':
            case 'task_selector':

                return "<p>".$item[ $column_name ]."</p>";

            case 'task_list':
            case 'task_tag':
            
                return "<p style='color:blue;'>".$item[ $column_name ]."</p>";

            case 'task_completed':
                if($item[ $column_name ] == 1){
                    return '<p class="text-green" style="color:green;"><i>'.__('Task Complete!').'</i>';
                } else {
                    return '<p class="text-red" style="color:red;"><i>'.__('Task NOT Complete').'</i>';
                }
            default:
            return $item[ $column_name ].print_r( $item, true ); //Show the array for troubleshooting 
        }
    }

    /**
    * Render the bulk edit checkbox
    *
    * @param array $item
    *
    * @return string
    */
    function column_cb( $item ) {
        return sprintf( '<input type="checkbox" name="bulk-delete[]" value="%s" />', $item['task_id'] );
    }

    /**
    * Associative array of columns
    *
    * @return array
    */
    function get_columns() {
        $columns = [
            'cb' => '<input type="checkbox" />',
            'task_name' => __( 'Name', 'noobtask' ),
            'task_desc' => __( 'Description', 'noobtask' ),
            'site_type' => __( 'Site Type', 'noobtask' ),
            'task_link' => __( 'Link', 'noobtask' ),
            'task_selector' => __( 'Selector', 'noobtask' ),
            'task_list' => __( 'Kartra List', 'noobtask' ),
            'task_tag' => __( 'Kartra Tag', 'noobtask' ),
            'task_completed' => __( 'Status', 'noobtask' ),
        ];
        
        return $columns;
    }

    /**
    * Columns to make sortable.
    *
    * @return array
    */
    public function get_sortable_columns() {
        $sortable_columns = array(
            'task_name' => array( 'task_name', true ),
            'task_list' => array( 'task_list', true ),
            'task_tag' => array( 'task_tag', true ),
            'task_list' => array( 'task_list', true ),
            'site_type' => array( 'site_type', true ),
            'task_completed' => array( 'task_completed', true )
        );
        
        return $sortable_columns;
    }

    /**
    * Returns an associative array containing the bulk action
    *
    * @return array
    */
    public function get_bulk_actions() {
        $actions = [
            'bulk-delete' => 'Delete',
        ];
        return $actions;
    }

    /**
    * Handles data query and filter, sorting, and pagination.
    */
    public function prepare_items() {

        $this->_column_headers = $this->get_column_info();
        
        /** Process bulk action */
        $this->process_bulk_action();
        
        $per_page = $this->get_items_per_page( 'tasks_per_page', 20 );
        $current_page = $this->get_pagenum();
        $total_items = self::record_count();
        
        $this->set_pagination_args( [
            'total_items' => $total_items, //WE have to calculate the total number of items
            'per_page' => $per_page //WE have to determine how many items to show on a page
        ]);
        
        $this->items = self::get_tasks( $per_page, $current_page );
    }

    public function process_bulk_action() {

        //Detect when a bulk action is being triggered...
        if ( 'delete' === $this->current_action() ) {
            // In our file that handles the request, verify the nonce.
            $nonce = esc_attr( $_REQUEST['_wpnonce'] );
            if ( ! wp_verify_nonce( $nonce, 'sp_delete_task' ) ) {
                die( 'Go get a life script kiddies' );
            } else if(isset($_GET['task']) && $_GET['task']){
                self::delete_task( absint( $_GET['task'] ) );
                $redirect_url = admin_url( '/?page=noobtask' );
                echo "<script>location.href = '$redirect_url';</script>";
                exit;
            }
        }
            
        // If the delete bulk action is triggered
        if ( ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'bulk-delete' ) || ( isset( $_REQUEST['action2'] ) && $_REQUEST['action2'] == 'bulk-delete' ) ) {
            $delete_ids = esc_sql( $_REQUEST['bulk-delete'] );
            // loop over the array of record IDs and delete them
            foreach ( $delete_ids as $id ) {
                self::delete_task( $id );
            }
            $redirect_url = admin_url( '/?page=noobtask' );
            echo "<script>location.href = '$redirect_url';</script>";
            exit;
        }

    }
}