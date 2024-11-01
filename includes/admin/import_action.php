<?php

defined( 'ABSPATH' ) || exit;
wptableeditor_load::current_user_can();

header('Content-type: text/html; charset=utf-8');
header("Cache-Control: no-cache, must-revalidate");
header("Pragma: no-cache");
set_time_limit(300);
ob_implicit_flush(1);
session_start();

if(isset($_POST["action"]) && isset($_POST["table_id"])){
	$action = sanitize_text_field($_POST["action"]);
	$table_id = (int) sanitize_text_field($_POST["table_id"]);
	check_ajax_referer( 'xs-import'.$table_id, '_xsnonce' );
	check_admin_referer( 'xs-import'.$table_id, '_xsnonce' );
	wptableeditor_load::set_memory_limit();
	wptableeditor_load::set_max_execution_time();
	if($action === 'confirm_xs'){
		$output = array(
			'success'	=>	true,
		);
		echo wp_json_encode($output);
	}elseif($action === 'import_xs' && isset($_POST["import_type"]) && isset($_SESSION['temp_data']) && isset($_SESSION['table_rows']) && isset($_SESSION['column_number'])){
		$temp_data = (array)$_SESSION['temp_data'];
		$table_rows = intval($_SESSION['table_rows']);
		$column_names = array_map('sanitize_text_field', (array)$_SESSION['column_number']);
		unset($_SESSION['temp_data']);
		unset($_SESSION['table_rows']);
		unset($_SESSION['column_number']);
		$table = WPTABLEEDITOR_PREFIX.$table_id;
		$WPTABLEEDITOR_COLUMN = WPTABLEEDITOR_COLUMN;
		$import_type = sanitize_text_field($_POST["import_type"]);
		if($import_type === 'append'){
			$import = wptableeditor_column::import($table_id);
			$query1 = $import['query'];
			$column_names = $import['name'];
			$placeholders = $import['placeholders'];
		}else{
			$total_column = (int) sanitize_text_field($_POST['column_number']);
			$query1 = array();
			$placeholders = array();
			wptableeditor_import::import($table, $total_column);
			$wpdb->delete($WPTABLEEDITOR_COLUMN, array('table_id' => $table_id));
			for($x = 1; $x <= $total_column; $x++){
				$query1[] = "column_$x";
				$placeholders[] = "%s";
				$wpdb->query($wpdb->prepare("INSERT INTO `{$WPTABLEEDITOR_COLUMN}` (table_id, column_names, column_order, column_name) VALUES ( %d, %s, %d, %s )", $table_id, "column_$x", $x, "column_$x"));
			}
			$query1 = implode(",",$query1);
			$placeholders = implode(",",$placeholders);
			$wpdb->update(WPTABLEEDITOR_TABLE, array('table_columns' => $total_column), array('table_id' => $table_id));
		}
		foreach($temp_data as $row){
			//$row = array_map('wp_kses_post', $row);
			$value = array();
			foreach($column_names as $name){
				if(isset($_POST[$name])){
					$value[] = trim($row[sanitize_text_field($_POST[$name])]);
				}
			}
			$wpdb->query($wpdb->prepare("INSERT INTO `{$table}` ($query1) VALUES ($placeholders)", $value));
			$wpdb->update($table, array('column_order' => $wpdb->insert_id), array('column_id' => $wpdb->insert_id));
			if(ob_get_level() > 0){
				ob_end_flush();
			}
		}
		wptableeditor_table::update($table_id, 'table_rows', $table_rows);
	}
}
?>