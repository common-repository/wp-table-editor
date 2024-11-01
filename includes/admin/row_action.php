<?php

defined( 'ABSPATH' ) || exit;
wptableeditor_load::current_user_can();

if(isset($_POST["action"]) && isset($_POST['table_id'])){
	$action = sanitize_text_field($_POST["action"]);
	$table_id = (int) sanitize_text_field($_POST['table_id']);
	$table = WPTABLEEDITOR_PREFIX.$table_id;
	if(isset($_POST['row_id'])){
		if(is_array($_POST['row_id'])){
			$row_id = array_map('sanitize_text_field', $_POST['row_id']);
		}else{
			$row_id = (int) sanitize_text_field($_POST['row_id']);
		}
	}
	if(in_array($action, array('row_getdata_wpte', 'row_add_wpte', 'row_update_order_wpte', 'row_multi_active_wpte', 'row_multi_inactive_wpte', 'row_multi_duplicate_wpte', 'row_multi_delete_wpte'))){
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
		global $wpdb;
		$data = [];
		$limit = wptableeditor_table::limit($table_id);
		$order_name_type = wptableeditor_load::order_name_type($table_id);
		$order_names = $order_name_type['names'];
		$order_types = $order_name_type['types'];
		$table_serverside = wptableeditor_table::get_var($table_id, 'table_serverside');
		$table_rows = wptableeditor_table::get_var($table_id, 'table_rows');
		if($table_rows >= 10000 && $table_serverside === 'no'){
			$table_serverside = 'yes';
			wptableeditor_table::update($table_id, 'table_serverside', 'yes');
		}
		$recordsTotal = wptableeditor_row::rowCount($table_id);
		if($recordsTotal != $table_rows){
			wptableeditor_table::update($table_id, 'table_rows', $recordsTotal);
		}
		if($table_serverside != 'yes'){
			$query = "SELECT * FROM `{$table}` ORDER BY column_id ASC";
			if($limit >= 0){
				$query .= " LIMIT {$limit}";
			}
			$result = $wpdb->get_results($query, ARRAY_A);
		}else{
			$query = "SELECT * FROM `{$table}` WHERE ";
			$order_name = $order_name_type['name'];
			$column_total = $order_name_type['count'];
			for($x = 1; $x <= $column_total + 1; $x++){
				if(isset($_POST["columns"][$x]["search"]["value"]) && !empty($_POST["columns"][$x]["search"]["value"])){
					$value = str_replace(array('^', '\\', '$'), '', sanitize_text_field($_POST["columns"][$x]["search"]["value"]));
					if($x <= $column_total){
						$query .= $order_names[$x].' LIKE "%'.$value.'%" AND ';
					}
					if($x > $column_total){
						$query .= 'column_status = "'.$value.'" AND ';
					}
				}
			}
			if(isset($_POST["search"]["value"])){
				$value = str_replace(array('^', '$'), '', sanitize_text_field($_POST["search"]["value"]));
				$query .= '(column_id LIKE "%'.$value.'%" ';
				for ($x = 1; $x <= $column_total; $x++) {
					$query .= 'OR '.$order_name[$x].' LIKE "%'.$value.'%" ';
				}
				$query .= ') ';
			}
			if(isset($_POST["order"])){
				$column = (int) sanitize_text_field($_POST['order']['0']['column']);
				$type = sanitize_text_field($_POST['order']['0']['dir']);
				$query .= 'ORDER BY '.$order_name[$column].' '.$type.' ';
			}
			$query1 = '';
			if(isset($_POST["length"]) && $_POST["length"] != -1){
				$start = (int) sanitize_text_field($_POST['start']);
				$length = (int) sanitize_text_field($_POST['length']);
				$query1 .= 'LIMIT ' . $start . ', ' . $length;
			}
			$result = $wpdb->get_results($query. $query1, ARRAY_A);
		}
		$output = array();
		foreach($result as $row){
			$xsnonce = wp_create_nonce('xs-table'.$table_id.'xs-row'.$row['column_id']);
			$sub_array = array();
			$sub_array[] = '<input type="checkbox" name="column_id[]" id="'.$row['column_id'].'" value="'.$row['column_id'].'" />';
			$sub_array['DT_RowClass'] = 'wptableeditor_'.$table_id.'_row_'.$row['column_id'];
			$sub_array['DT_RowId'] = $row['column_id'];
			for ($x = 1; $x <= count($row) - 2; $x++){
				if(isset($order_names[$x])){
					$column_name = $order_names[$x];
					if(isset($row[$column_name])){
						$value = $row[$column_name];
					}else{
						$value = '';
					}
					$html_entity_decode = $value !== null ? html_entity_decode($value) : "";
					if($order_types[$x] === 'url'){
						$sub_array[] = make_clickable($value);
					}elseif($order_types[$x] === 'shortcode'){
						$shortcode = explode(' ', ltrim($value))[0];
						if(strpos($shortcode, "[") === 0 && shortcode_exists(trim($shortcode, "["))){
							$sub_array[] = do_shortcode(html_entity_decode($value));
						}else{
							$sub_array[] = $value;
						}
					}elseif($order_types[$x] === 'html'){
						$sub_array[] = htmlentities($value);
					}elseif($order_types[$x] === 'id'){
						$sub_array[] = $row['column_id'];
					}elseif($order_types[$x] === 'select'){
						if($value === 'dashicons dashicons-yes'){
							$sub_array[] = html_entity_decode('<span class="dashicons dashicons-yes" style="color: green;font-weight: 600;font-size: 25px"></span>');
						}elseif($value === 'dashicons dashicons-no'){
							$sub_array[] = html_entity_decode('<span class="dashicons dashicons-no" style="color: red;font-size: 25px"></span>');
						}else{
							$sub_array[] = $html_entity_decode;
						}
					}elseif($order_types[$x] === 'number_format'){
						if(is_numeric($value)){
							$sub_array[] = number_format($value);
						}else{
							$sub_array[] = 0;
						}
					}elseif($order_types[$x] === 'link'){
						if(!empty($value) && filter_var($value, FILTER_VALIDATE_URL)){
							$sub_array[] = '<a href="'.$value.'" target="_blank"><span class="btn btn-info btn-xs update"><span class="dashicons dashicons-admin-links"></span></span></a>';
						}else{
							$sub_array[] = $html_entity_decode;
						}
					}elseif($order_types[$x] === 'image'){
						if(!empty($value) && filter_var($value, FILTER_VALIDATE_URL)){
							$sub_array[] = '<button type="button" name="image_view_wpte" class="img-thumbnail image_view_wpte" data-row_id="'.$row['column_id'].'" data-image_url="'.$value.'" data-xsnonce="'.$xsnonce.'"><img src="'.$value.'" style="height: 120px;width: auto;max-width: fit-content;"></button>';
						}else{
							$sub_array[] = $html_entity_decode;
						}
					}elseif($order_types[$x] === 'empty'){
						$sub_array[] = "";
					}else{
						if(!empty($value)){
							$sub_array[] = $html_entity_decode;
						}else{
							$sub_array[] = $value;
						}
					}
				}
			}
			if($row['column_order'] === 0){
				$sub_array[] = $row['column_id'];
			}else{
				$sub_array[] = $row['column_order'];
			}
			if($row['column_status'] === 'active'){
				$status = '<button type="button" name="row_edit_status_wpte" class="btn btn-success btn-xs xs-mw-70px row_edit_status_wpte" id="'.$row['column_id'].'" data-status="'.$row['column_status'].'" data-xsnonce="'.$xsnonce.'">Active</button>';
			}else{
				$status = '<button type="button" name="row_edit_status_wpte" class="btn btn-danger btn-xs row_edit_status_wpte" id="'.$row['column_id'].'" data-status="'.$row['column_status'].'" data-xsnonce="'.$xsnonce.'">Inactive</button>';
			}
			$sub_array[] = $status;
			$sub_array[] = '<button type="button" name="row_edit_wpte" class="btn btn-warning btn-xs row_edit_wpte" data-id="'.$row['column_id'].'" data-xsnonce="'.$xsnonce.'"><span class="dashicons dashicons-edit"></span></button>';
			$sub_array[] = '<button type="button" name="row_delete_wpte" class="btn btn-danger btn-xs row_delete_wpte" data-id="'.$row['column_id'].'" data-xsnonce="'.$xsnonce.'"><span class="dashicons dashicons-no"></span></button>';
			$data[] = $sub_array;
		}
		if($table_serverside != 'yes'){
			$output = array("data"	=>	$data);
		}else{
			$wpdb->get_results($query);
			$recordsFiltered = $wpdb->num_rows;
			$output = array(
				"draw"    			=>	intval($_POST["draw"]),
				"recordsTotal"  	=>	$recordsTotal,
				"recordsFiltered"	=>	$recordsFiltered,
				"data"    			=>	$data
			);
		}
		echo wp_json_encode($output);
	}
	if($action === 'row_add_wpte'){
		$data = wptableeditor_row::column($table_id);
		$rows = array();
		foreach($data as $row){
			if(isset($_POST[$row])){
				$rows[$row] = stripslashes(esc_html(trim($_POST[$row])));
			}
		}
		$wpdb->insert($table, $rows);
		$result = $wpdb->update($table, array('column_order' => $wpdb->insert_id), array('column_id' => $wpdb->insert_id));
		if($result){
			echo wp_kses_post('<div class="alert alert-success">'.esc_html__('Row Added', 'wp-table-editor').'</div>');
		}
	}
	if(isset($row_id)){
		if($action === 'row_single_wpte'){
			$query = "SELECT * FROM `{$table}` WHERE column_id = %d";
			$result = $wpdb->get_row($wpdb->prepare($query, $row_id));
			$data = array();
			foreach((array)$result as $keys => $rows){
				if(str_replace('column_', '', $keys) != $keys){
					if(!empty($rows)){
						$data[$keys] = html_entity_decode($rows);
					}else{
						$data[$keys] = $rows;
					}
					if($keys === 'column_id'){
						$column_id = $rows;
					}
					if($keys === 'column_order' && $rows == 0){
						$data[$keys] = $column_id;
					}
				}
			}
			echo wp_json_encode($data);
		}
		if($action === 'row_edit_wpte'){
			$data = wptableeditor_row::update($table, $row_id);
			$rows = array();
			foreach($data as $key => $row){
				if(isset($_POST[$key])){
					$rows[$key] = stripslashes(esc_html(trim($_POST[$key])));
				}
			}
			if(!empty($row_id)){
				$result = $wpdb->update($table, $rows, array('column_id' => $row_id));
				if($result){
					echo wp_kses_post('<div class="alert alert-success">'.esc_html__('Row Edited', 'wp-table-editor').'</div>');
				}
			}
		}
		if($action === 'row_edit_status_wpte'){
			$status = 'active';
			if($_POST['status'] === 'active'){
				$status = 'inactive';	
			}
			$data = array(
				'column_status'	=>	$status
			);
			$result = $wpdb->update($table, $data, array('column_id' => $row_id));
			if($result){
				echo wp_kses_post('<div class="alert alert-success">'.esc_html__('Row status change to ', 'wp-table-editor').$status.'</div>');
			}
		}
		if($action === 'row_delete_wpte'){
			$result = $wpdb->delete($table, array('column_id' => $row_id));
			if($result){
				echo wp_kses_post('<div class="alert alert-success">'.esc_html__('Row has been Deleted', 'wp-table-editor').'</div>');
			}
		}
		if($action === 'row_multi_active_wpte'){
			$status = 'active';
			$data = array(
				'column_status'	=>	$status
			);
			$ids = array_unique($row_id);
			foreach($ids as $id){
				if(!wptableeditor_row::check_id($table, (int)$id)){
					$result = $wpdb->insert($table, array('column_id' => (int)$id, 'column_status' => $status));
				}else{
					$result = $wpdb->update($table, $data, array('column_id' => (int)$id));
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
				if(!wptableeditor_row::check_id($table, (int)$id)){
					$result = $wpdb->insert($table, array('column_id' => (int)$id, 'column_status' => $status));
				}else{
					$result = $wpdb->update($table, $data, array('column_id' => (int)$id));
				}
			}
			if($result){
				echo wp_kses_post('<div class="alert alert-success">'.esc_html__('Row status change to ', 'wp-table-editor').$status.'</div>');
			}
		}
		if($action === 'row_multi_duplicate_wpte'){
			$ids = array_unique($row_id);
			foreach($ids as $id){
				$query = "SELECT * FROM `{$table}` WHERE column_id = %d";
				$result = $wpdb->get_results($wpdb->prepare($query, (int)$id));
				if($result){
					foreach($result as $row){
						$row = (array)$row;
						unset($row['column_id']);
					}
				}
				$result = $wpdb->insert($table, $row);
			}
			if($result){
				echo wp_kses_post('<div class="alert alert-success">'.esc_html__('Row Copied', 'wp-table-editor').'</div>');
			}
		}
		if($action === 'row_multi_delete_wpte'){
			$ids = array_unique($row_id);
			foreach($ids as $id){
				$result = $wpdb->delete($table, array('column_id' => (int)$id));
			}
			if($result){
				echo wp_kses_post('<div class="alert alert-success">'.esc_html__('Row has been Deleted', 'wp-table-editor').'</div>');
			}
		}
		if($action === 'row_update_order_wpte' && isset($_POST["start"])){
			$start = sanitize_text_field(trim($_POST["start"]));
			for($count = 0;  $count < count($row_id); $count++){
				$id = (int) $row_id[$count];
				$data = array('column_order' => $count + $start + 1);
				$wpdb->update($table, $data, array('column_id' => $id));
			}
		}
	}
}
?>