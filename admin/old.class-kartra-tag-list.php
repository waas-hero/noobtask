<?php

class Tag_List {

    /**
    * Retrieve customer’s data from the database
    *
    * @param int $per_page
    * @param int $page_number
    *
    * @return mixed
    */
    public static function get_local_tags() {

        global $wpdb;
        
        $sql = "SELECT * FROM {$wpdb->prefix}noobtask_tags";
        
        $result = $wpdb->get_results( $sql, 'ARRAY_A' );
        
        return $result;
    }

    /**
    * Delete a tag record.
    *
    * @param int $id customer ID
    */
    public static function delete_tag( $id ) {
        global $wpdb;
        
        $wpdb->delete(
            "{$wpdb->prefix}noobtask_tags",
            [ 'id' => $id ],
            [ '%d' ]
        );
    }

    /**
    * Returns the count of records in the database.
    *
    * @return null|string
    */
    public static function record_count() {
        global $wpdb;
        
        $sql = "SELECT COUNT(*) FROM {$wpdb->prefix}noobtask_tags";
        
        return $wpdb->get_var( $sql );
    }

    /** Text displayed when no customer data is available */
    public function no_items() {
        _e( 'No tags avaliable.', 'sp' );
    }

    /**
    * Method for delete link
    *
    * @param array $item an array of DB data
    *
    * @return string
    */
    public function delete_action( $item ) {

        // create a nonce
        $delete_nonce = wp_create_nonce( 'sp_delete_tag' );
        
        $title = '<strong>' . $item['tag_name'] . '</strong>';
        
        return  sprintf( '<a href="?page=%s&action=%s&tag=%s&_wpnonce=%s">Delete</a>', esc_attr( $_REQUEST['page'] ), 'delete', absint( $item['id'] ), $delete_nonce );

    }

}