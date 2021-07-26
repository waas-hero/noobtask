<?php 

namespace WaasHero;

class NoobTask_Options_Api {

    public static $table_name = 'noobtask_options';
    
    static function get($name){
        global $wpdb;
        $tableName = self::$table_name;

        $row = $wpdb->get_var( $wpdb->prepare( "SELECT value FROM {$wpdb->prefix}$tableName WHERE name = %s", $name ) );

        if($row){
            return $row;
        } else {
            return null;
        }
    }

    static function getAll(){
        global $wpdb;
        $tableName = self::$table_name;
        $row = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}$tableName" );
        if($row){
            return json_encode([ 'success'=>true, 'message'=>__('Options found.'), 'data' =>$row ]);
        } else {
            return json_encode([ 'success'=>false, 'message'=>__('No Options found.') ]);
        }
    }

    static function save($name, $value){
        global $wpdb;
        $tableName = self::$table_name;
        $count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) from {$wpdb->prefix}$tableName where name = %s", $name) );

        //if name is found then update value
        if($count > 0){
            return self::update($name, $value);
        }

        //name not found so insert new
        $insert_row = $wpdb->insert( $wpdb->prefix.self::$table_name, ['name' => $name, 'value' => $value] );

        if($insert_row){
            return json_encode([ 'success'=>true, 'message'=>__('New option added.'), 'response' => $insert_row ]);
        } else {
            return json_encode([ 'success'=>false, 'message'=>__('Error. Failed to save option.'), 'response' => $insert_row ]);
        }
    }

    static function update($name, $value){
        global $wpdb;
        $insert_row = $wpdb->update( $wpdb->prefix.self::$table_name, ['name' => $name, 'value' => $value], ['name' => $name] );
        if($insert_row){
            return json_encode([ 'success'=>true, 'message'=>__('Option updated.'), 'response' =>$insert_row ]);
        } else {
            return json_encode([ 'success'=>false, 'message'=>__('Error. Failed to update option.'), 'response' =>$insert_row ]);
        }
    }

    static function delete($name){
        global $wpdb;

        $delete_row = $wpdb->delete( $wpdb->prefix.self::$table_name, ['name' => $name]);
        if($delete_row){
            return json_encode([ 'success'=>true, 'message'=>__('Option Deleted.'), 'response' =>$delete_row ]);
        } else {
            return json_encode([ 'success'=>false, 'message'=>__('Error. Failed to delete option.'), 'response' =>$delete_row ]);
        }
    }

}