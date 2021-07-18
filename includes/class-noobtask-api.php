<?php 

namespace WaasHero;

class NoobTask_Api {

    public static $table_name = 'noobtasks';
    
    static function get($whereArray){
        global $wpdb;
        $tableName = self::$table_name;
        $row = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}$tableName WHERE $whereArray" );
        if($row){
            return $row;
        } else {
            return null;
        }
    }

    static function getById($id){
        global $wpdb;
        $tableName = self::$table_name;
        $row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}$tableName WHERE id = %d", $id ) );
        if($row){
            return $row;
        } else {
            return null;
        }
    }

    static function getByName($name){
        global $wpdb;
        $tableName = self::$table_name;

        $row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}$tableName WHERE task_name = %s", $name ), ARRAY_A );

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
            return json_encode([ 'success'=>true, 'message'=>__('Tasks found.'), 'data' =>$row ]);
        } else {
            return json_encode([ 'success'=>false, 'message'=>__('No Tasks found.') ]);
        }
    }

    static function getDefault(){

        global $wpdb;
        $tableName = self::$table_name;
        return $wpdb->get_results($wpdb->prepare( 
            "SELECT * FROM {$wpdb->prefix}$tableName WHERE task_is_default = %d",
            true
        ), ARRAY_A);
		
	}

    static function save($dataArray){
        global $wpdb;
        $insert_row = $wpdb->insert( $wpdb->prefix.self::$table_name, $dataArray );
        if($insert_row){
            return json_encode([ 'success'=>true, 'message'=>__('New option added.'), 'data' =>$dataArray ]);
        } else {
            return json_encode([ 'success'=>false, 'message'=>__('Error. Failed to save option.'), 'data' =>$dataArray ]);
        }
    }

    static function update($dataArray, $whereArray){
        global $wpdb;
        $insert_row = $wpdb->update( $wpdb->prefix.self::$table_name, $dataArray, $whereArray );
        if($insert_row){
            return json_encode([ 'success'=>true, 'message'=>__('Option updated.'), 'data' =>$dataArray ]);
        } else {
            return json_encode([ 'success'=>false, 'message'=>__('Error. Failed to update option.'), 'data' =>$dataArray ]);
        }
    }

    static function delete($whereArray){
        global $wpdb;
        $delete_row = $wpdb->delete( $wpdb->prefix.self::$table_name, $whereArray );
        if($delete_row){
            return json_encode([ 'success'=>true, 'message'=>__('Option Deleted.'), 'data' =>$whereArray ]);
        } else {
            return json_encode([ 'success'=>false, 'message'=>__('Error. Failed to delete option.'), 'data' =>$whereArray ]);
        }
    }

}