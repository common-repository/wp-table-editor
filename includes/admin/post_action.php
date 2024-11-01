<?php

defined( 'ABSPATH' ) || exit;
wptableeditor_load::current_user_can();

if(isset($_POST['action']) && isset($_POST['xs_type']) && isset($_POST['table_id'])){
	$action = sanitize_text_field($_POST['action']);
	$xs_type = sanitize_text_field($_POST['xs_type']);
	$table_id = (int) sanitize_text_field($_POST['table_id']);
	$table = WPTABLEEDITOR_PREFIX.$table_id;
	if(isset($_POST['row_id'])){
		if(is_array($_POST['row_id'])){
			$row_id = array_map('sanitize_text_field', $_POST['row_id']);
		}else{
			$row_id = (int) sanitize_text_field($_POST['row_id']);
		}
	}
	if(in_array($action, array('row_getdata_wpte', 'row_update_order_wpte', 'row_multi_active_wpte', 'row_multi_inactive_wpte'))){
		check_ajax_referer( 'xs-table'.$table_id, '_xsnonce' );
		check_admin_referer( 'xs-table'.$table_id, '_xsnonce' );
	}elseif(isset($row_id)){
		check_ajax_referer( 'xs-table'.$table_id.'xs-row'.$row_id, '_xsnonce' );
		check_admin_referer( 'xs-table'.$table_id.'xs-row'.$row_id, '_xsnonce' );
	}else{
		check_ajax_referer( 'xs-table'.$table_id.'_front', '_xsnonce' );
		check_admin_referer( 'xs-table'.$table_id.'_front', '_xsnonce' );
	}
	wptableeditor_load::set_memory_limit();
	wptableeditor_load::set_max_execution_time();
	if($action === 'row_getdata_wpte'){
		$order_name = wptableeditor_column::order_name($table_id);
		$order_id = wptableeditor_column::order_id($table_id);
		$category_id = wptableeditor_table::category($table_id);
		$output = array();
		$data = array();
		$column_total = wptableeditor_column::rowCount($table_id);
		$limit = wptableeditor_table::limit($table_id);
		$array = array();
		$arrays = array();
		$query = "SELECT column_id, column_order, column_custom FROM `{$table}` ORDER BY column_id ASC";
		$result = $wpdb->get_results($query, ARRAY_A);
		foreach($result as $row){
			$array[$row['column_id']] = $row['column_order'];
			$arrays[$row['column_id']] = $row['column_custom'];
		}
		$table_types = wptableeditor_post::type($xs_type);
		foreach(wptableeditor_post::post($xs_type, $category_id, $limit) as $row){
			$row = array_values($row);
			$xsnonce = wp_create_nonce('xs-table'.$table_id.'xs-row'.$row[0]);
			$sub_array = array();
			$sub_array[] = '<input type="checkbox" name="column_id[]" id="'.$row[0].'" value="'.$row[0].'" />';
			$sub_array['DT_RowClass'] = 'wptableeditor_'.$table_id.'_row_'.$row[0];
			$sub_array['DT_RowId'] = $row[0];
			for ($x = 1; $x <= $column_total; $x++){
				if(wptableeditor_column::status($table_id, $order_id[$x])){
					if(isset($table_types[$order_name[$x]]) && $table_types[$order_name[$x]] === 'custom'){
						if(isset($arrays[$row[0]]) && $arrays[$row[0]] !== null){
							$sub_array[] = html_entity_decode($arrays[$row[0]]);
						}else{
							$sub_array[] = '';
						}
					}else{
						$value = str_replace('column_', '', $order_name[$x]);
						if(isset($row[$value])){
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
			if(wptableeditor_row::check_id($table, $row[0]) && !wptableeditor_row::status($table, $row[0])){
				$status = '<button type="button" name="row_edit_status_wpte" class="btn btn-danger btn-xs row_edit_status_wpte" id="'.$row[0].'" data-status="inactive" data-xsnonce="'.$xsnonce.'">Inactive</button>';
			}else{
				$status = '<button type="button" name="row_edit_status_wpte" class="btn btn-success btn-xs xs-mw-70px row_edit_status_wpte" id="'.$row[0].'" data-status="active" data-xsnonce="'.$xsnonce.'">Active</button>';
			}
			$sub_array[] = $status;
			$sub_array[] = '<button type="button" name="row_edit_wpte" class="btn btn-warning btn-xs row_edit_wpte" data-id="'.$row[0].'" data-xsnonce="'.$xsnonce.'"><span class="dashicons dashicons-edit"></span></button>';
			$data[] = $sub_array;
		}
		$output = array("data"	=>	$data);
		echo wp_json_encode($output);
	}
	if(isset($row_id)){
		if($action === 'row_single_wpte'){
			$query = "SELECT * FROM `{$table}` WHERE column_id = %d";
			$result = $wpdb->get_results($wpdb->prepare($query, $row_id), ARRAY_A);
			$data = array();
			foreach($result as $row){
				foreach($row as $keys => $rows){
					if(str_replace('column_', '', $keys) != $keys){
						$data[$keys] = $rows;
						if($keys === 'column_custom' && $rows !== null){
							$data[$keys] = html_entity_decode($rows);
						}
					}
				}
			}
			if(empty($data)){
				$data['column_order'] = $row_id;
				$data['column_status'] = 'active';
			}
			echo wp_json_encode($data);
		}
		if($action === 'row_edit_wpte'){
			$data = array(
				'column_order'	=>	stripslashes(wp_kses_post(trim($_POST['column_order']))),
				'column_status'	=>	stripslashes(esc_html(trim($_POST['column_status'])))
			);
			if(isset($_POST["column_custom"])){
				$data['column_custom'] = stripslashes(esc_html(trim($_POST['column_custom'])));
			}
			if(!empty($row_id)){
				if(!wptableeditor_row::check_id($table, $row_id)){
					$data['column_id'] = $row_id;
					$result = $wpdb->insert($table, $data);
				}else{
					$result = $wpdb->update($table, $data, array('column_id' => $row_id));
				}
			}
			if($result){
				echo wp_kses_post('<div class="alert alert-success">'.esc_html__('Row Edited', 'wp-table-editor').'</div>');
			}
		}
		if($action === 'row_edit_status_wpte'){
			$status = 'active';
			if(sanitize_text_field($_POST['status']) === 'active'){
				$status = 'inactive';	
			}
			$data = array(
				'column_status'	=>	$status
			);
			if(!wptableeditor_row::check_id($table, $row_id)){
				$result = $wpdb->insert($table, array('column_id' => $row_id, 'column_status' => $status));
			}else{
				$result = $wpdb->update($table, $data, array('column_id' => $row_id));
			}
			if($result){
				echo wp_kses_post('<div class="alert alert-success">'.esc_html__('Row status change to ', 'wp-table-editor').$status.'</div>');
			}
		}
		if($action === 'row_multi_active_wpte'){
			$status = 'active';
			$data = array(
				'column_status'	=>	$status
			);
			$ids = array_unique($row_id);
			foreach($ids as $id){
				if(!wptableeditor_row::check_id($table, $id)){
					$result = $wpdb->insert($table, array('column_id' => $id, 'column_status' => $status));
				}else{
					$result = $wpdb->update($table, $data, array('column_id' => $id));
				}
			}
			if($result){
				echo wp_kses_post('<div class="alert alert-success">'.esc_html__('Row status change to ', 'wp-table-editor').$status.'</div>');
			}
		}
		if($action === 'row_multi_inactive_wpte'){
			$status = 'inactive';
			$data = array(
				'column_status'	=>	$status
			);
			$ids = array_unique($row_id);
			foreach($ids as $id){
				if(!wptableeditor_row::check_id($table, $id)){
					$result = $wpdb->insert($table, array('column_id' => $id, 'column_status' => $status));
				}else{
					$result = $wpdb->update($table, $data, array('column_id' => $id));
				}
			}
			if($result){
				echo wp_kses_post('<div class="alert alert-success">'.esc_html__('Row status change to ', 'wp-table-editor').$status.'</div>');
			}
		}
		if($action === 'row_update_order_wpte' && isset($_POST["start"])){
			$start = sanitize_text_field(trim($_POST["start"]));
			$row_id = array_unique($row_id);
			for($count = 0;  $count < count($row_id); $count++){
				$id = (int) $row_id[$count];
				if(!wptableeditor_row::check_id($table, $id)){
					$result = $wpdb->insert($table, array('column_id' => $id, 'column_order' => $count + $start + 1));
				}else{
					$result = $wpdb->update($table, array('column_order' =>	$count + $start + 1), array('column_id' => $id));
				}
			}
			if($result){
				echo wp_kses_post('<div class="alert alert-success">'.esc_html__('Row order has been updated', 'wp-table-editor').'</div>');
			}
		}
	}
}
?>