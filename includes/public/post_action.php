<?php

defined( 'ABSPATH' ) || exit;

if(isset($_POST['action']) && isset($_POST['xs_type']) && isset($_POST['table_id'])){
	$action = sanitize_text_field($_POST['action']);
	$xs_type = sanitize_text_field($_POST['xs_type']);
	$table_id = (int) sanitize_text_field($_POST['table_id']);
	check_ajax_referer( 'xs-table'.$table_id.'_front', '_xsnonce' );
	check_admin_referer( 'xs-table'.$table_id.'_front', '_xsnonce' );
	wptableeditor_load::set_memory_limit();
	wptableeditor_load::set_max_execution_time();
	if($action === 'row_getdatas_wpte'){
		$table = WPTABLEEDITOR_PREFIX.$table_id;
		$category_id = wptableeditor_load::categorys($table_id);
		$xs_table_value = wptableeditor_load::table($table_id);
		$table_limit = $xs_table_value['table_limit'];
		$order_name_type = wptableeditor_load::order_name_type($table_id);
		$column_total = $order_name_type['count'];
		$order_login = $order_name_type['login'];
		$order_title = $order_name_type['title'];
		$order_ids = $order_name_type['id'];
		$order_name = $order_name_type['order_names'];
		$order_id = $order_name_type['order_id'];
		$query = "SELECT column_id, column_order, column_custom FROM `{$table}` WHERE column_status = %s ORDER BY column_id ASC";
		$result = $wpdb->get_results($wpdb->prepare($query, 'active'), ARRAY_A);
		$output = array();
		$array = array();
		$arrays = array();
		$data = array();
		foreach($result as $row){
			$array[$row['column_id']] = $row['column_order'];
			$arrays[$row['column_id']] = $row['column_custom'];
		}
		$table_types = wptableeditor_load::post_type($xs_type);
		$column_status = wptableeditor_load::column_status($table_id);
		foreach(wptableeditor_load::post($xs_type, $category_id, $table_limit) as $row){
			$row = array_values($row);
			if(wptableeditor_load::row_status($table, $row[0])){
				$sub_array = array();
				$sub_array[] = $row[0];
				$sub_array['DT_RowClass'] = 'wptableeditor_'.$table_id.'_row_'.$row[0];
				for ($x = 1; $x <= $column_total - 1; $x++){
					if(in_array($order_id[$x], $column_status)){
						if($order_login[$x] === 'yes' && (!is_user_logged_in() || !wptableeditor_load::column_roles($table_id, $order_ids[$x]))){
							$sub_array[] = $order_title[$x];
						}elseif(isset($order_name[$x]) && isset($table_types[$order_name[$x]]) && $table_types[$order_name[$x]] === 'custom'){
							if(isset($arrays[$row[0]]) && $arrays[$row[0]] !== null){
								$sub_array[] = html_entity_decode($arrays[$row[0]]);
							}else{
								$sub_array[] = '';
							}
						}else{
							if(isset($order_name[$x])){
								$value = str_replace('column_', '', $order_name[$x]);
							}
							if(isset($value) && isset($row[$value])){
								$sub_array[] = $row[$value];
							}else{
								$sub_array[] = '';
							}
						}
					}
				}
				if(!isset($array[$row[0]]) || $array[$row[0]] === 0){
					$sub_array[] = $row[0];
				}else{
					$sub_array[] = $array[$row[0]];
				}
				$data[] = $sub_array;
			}
		}
		$output = array("data"	=>	$data);
		echo wp_json_encode($output);
	}
}
?>