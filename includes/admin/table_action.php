<?php

defined( 'ABSPATH' ) || exit;
wptableeditor_load::current_user_can();

if(isset($_POST['action'])){
	$action = sanitize_text_field($_POST['action']);
	$WPTABLEEDITOR_TABLE = WPTABLEEDITOR_TABLE;
	$WPTABLEEDITOR_COLUMN = WPTABLEEDITOR_COLUMN;
	if(isset($_POST['table_id'])){
		if(is_array($_POST['table_id'])){
			$table_id = array_map('sanitize_text_field', $_POST['table_id']);
		}else{
			$table_id = (int) sanitize_text_field($_POST['table_id']);
		}
	}
	if(in_array($action, array('table_getdata_wpte', 'table_update_order_wpte', 'table_add_wpte', 'table_multi_active_wpte', 'table_multi_inactive_wpte', 'table_multi_duplicate_wpte', 'table_multi_delete_wpte'))){
		check_ajax_referer( WPTABLEEDITOR_TABLE, '_xsnonce' );
		check_admin_referer( WPTABLEEDITOR_TABLE, '_xsnonce' );
	}elseif(isset($table_id)){
		check_ajax_referer( 'xs-table'.$table_id, '_xsnonce' );
		check_admin_referer( 'xs-table'.$table_id, '_xsnonce' );
	}
	wptableeditor_load::set_memory_limit();
	wptableeditor_load::set_max_execution_time();
	$tables = wptableeditor_table::column();
	if($action === 'table_getdata_wpte'){
		wptableeditor_init::add_column(WPTABLEEDITOR_COLUMN, 'column_imageheight', "int(2)", 'column_minwidth');
		wptableeditor_init::add_column(WPTABLEEDITOR_COLUMN, 'column_modalimage', "enum('no','yes')", 'column_imageheight');
		$query = "SELECT * FROM `{$WPTABLEEDITOR_TABLE}` ";
		$result = $wpdb->get_results($query);
		$filtered_rows = $wpdb->num_rows;
		$output = array();
		$data = array();
		foreach($result as $row){
			$table_id = $row->table_id;
			$xsnonce = wp_create_nonce('xs-table'.$table_id);
			$shortcode = '[wptableeditor id='.$table_id.']';
			$sub_array = array();
			$sub_array[] = '<input type="checkbox" name="table_id[]" id="'.$table_id.'" value="'.$table_id.'" />';
			$sub_array['DT_RowId'] = $table_id;
			$sub_array[] = $row->table_name;
			$sub_array[] = $shortcode;
			$sub_array[] = $row->table_type;
			$sub_array[] = '<a href="admin.php?page=wptableeditor&tab=column&table_id='.$table_id.'&_xsnonce='.$xsnonce.'">'.$row->table_columns.'</a>';
			$sub_array[] = get_user_by('id', $row->table_author)->display_name;
			if($row->table_order === 0){
				$sub_array[] = $table_id;
			}else{
				$sub_array[] = $row->table_order;
			}
			if($row->table_status === 'active'){
				$status = '<button type="button" name="table_edit_status_wpte" class="btn btn-success btn-xs xs-mw-70px table_edit_status_wpte" id="'.$table_id.'" data-status="'.$row->table_status.'" data-xsnonce="'.$xsnonce.'">Active</button>';
			}else{
				$status = '<button type="button" name="table_edit_status_wpte" class="btn btn-danger btn-xs table_edit_status_wpte" id="'.$table_id.'" data-status="'.$row->table_status.'" data-xsnonce="'.$xsnonce.'">Inactive</button>';
			}
			$sub_array[] = $status;
			$sub_array[] = '<a href="admin.php?page=wptableeditor&tab=row&table_id='.$table_id.'&_xsnonce='.$xsnonce.'"><span class="btn btn-info btn-xs update"><span class="dashicons dashicons-visibility"></span></span></a>';
			$sub_array[] = '<button type="button" name="table_edit_wpte" class="btn btn-warning btn-xs table_edit_wpte" id="'.$table_id.'" data-xsnonce="'.$xsnonce.'"><span class="dashicons dashicons-edit"></span></button>';
			$sub_array[] = '<button type="button" name="table_style_xs" class="btn btn-warning btn-xs table_style_xs" id="'.$table_id.'" data-xsnonce="'.$xsnonce.'"><span class="dashicons dashicons-admin-customizer"></span></button>';
			$sub_array[] = '<button type="button" name="table_delete_wpte" class="btn btn-danger btn-xs table_delete_wpte" data-id="'.$table_id.'" data-xsnonce="'.$xsnonce.'"><span class="dashicons dashicons-no"></span></button>';
			$data[] = $sub_array;
		}
		$output = array("data"	=>	$data);
		echo wp_json_encode($output);
	}
	if($action === 'table_add_wpte' && isset($_POST['table_type'])){
		if(wptableeditor_init::check_names($WPTABLEEDITOR_TABLE, 'table_name', sanitize_text_field($_POST["table_name"]))){
			echo wp_kses_post('<div class="alert alert-danger">'.esc_html__('The name has already been taken or invalid', 'wp-table-editor').'</div>');
			return;
		}
		foreach($tables as $table){
			if(isset($_POST["$table"])){
				$data[$table] = sanitize_text_field($_POST["$table"]);
			}
		}
		$data['table_dom'] = 'l,c,B,f,r,t,i,p';
		$data['table_author'] = get_current_user_id();
		$query = "SELECT MAX(table_order) as table_order FROM `{$WPTABLEEDITOR_TABLE}`";
		$max_order = $wpdb->get_row($query, ARRAY_A);
		$data['table_order'] = $max_order['table_order'] + 1;
		$result = $wpdb->insert($WPTABLEEDITOR_TABLE, $data);
		if($result){
			$id = $wpdb->insert_id;
			$table_new = WPTABLEEDITOR_PREFIX.$id;
			$table_type = sanitize_text_field($_POST['table_type']);
			$total_column = (int) sanitize_text_field($_POST["table_columns"]);
			$column = array();
			if(in_array($table_type, array('json', 'sheet'))){
				$X = 0;
				$total_column = $total_column - 1;
			}else{
				$X = 1;
			}
			for($x = $X; $x <= $total_column; $x++){
				$column[] = "column_$x longtext NOT NULL";
				$wpdb->query($wpdb->prepare("INSERT INTO `{$WPTABLEEDITOR_COLUMN}` (table_id, column_names, column_order, column_name) VALUES ( %d, %s, %d, %s )", $id, "column_$x", $x, "column_$x"));
			}
			if($table_type === 'default'){
				$column = implode(",",$column);
			}else{
				$column = "column_custom longtext NOT NULL";
			}
			$charset_collate = $wpdb->get_charset_collate();
			$sql = "CREATE TABLE IF NOT EXISTS $table_new (
				column_id bigint(20) NOT NULL AUTO_INCREMENT,
				column_status enum('active','inactive') NOT NULL,
				column_order bigint(20) NOT NULL,
				$column,
				PRIMARY KEY (column_id)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			dbDelta( $sql );
			echo wp_kses_post('<div class="alert alert-success">'.esc_html__('The table was added successfully.', 'wp-table-editor').'</div>');
		}
	}
	if(isset($table_id)){
		if($action === 'table_restrictionrole_single_wpte'){
			$query = "SELECT table_name, table_restrictionrole FROM `{$WPTABLEEDITOR_TABLE}` WHERE table_id = %d";
			$result = $wpdb->get_results($wpdb->prepare($query, $table_id));
			$output = array();
			$table_restrictionrole = array();
			foreach($result as $row){
				$table_name = $row->table_name;
				foreach(explode(',', $row->table_restrictionrole) as $role){
					if(!empty($role)){
						$table_restrictionrole[] = trim($role);
					}
				}
			}
			$table_restrictionrole[] = 'administrator';
			$output['table_restrictionrole'] = array_unique($table_restrictionrole);
			$output['table_name'] = $table_name;
			echo wp_json_encode($output);
		}
		if($action === 'table_single_wpte'){
			$query = "SELECT * FROM `{$WPTABLEEDITOR_TABLE}` WHERE table_id = %d";
			$result = $wpdb->get_results($wpdb->prepare($query, $table_id));
			$output = array();
			foreach($result as $row){
				foreach($tables as $table){
					if($row->$table !== ''){
						$output["$table"] = $row->$table;
					}
					if($table === 'table_dom'){
						$table_dom = $row->$table;
					}
					if($table === 'table_serverside' && $row->table_rows >= 10000){
						$output["$table"] = 'yes';
					}
					if(in_array($table, array('table_fontfamily', 'table_headerfontfamily', 'table_bodyfontfamily')) && $row->$table !== ''){
						$output["$table"] = htmlspecialchars(stripslashes($row->$table));
					}
				}
			}
			$output['table_columns'] = wptableeditor_column::rowCount($table_id, 'active');
			if(empty($table_dom)){
				$table_dom = 'l,c,B,f,r,t,i,p';
			}
			foreach(explode(',', $table_dom) as $dom){
				$doms[] = trim($dom);
			}
			$output['table_dom'] = wp_json_encode($doms);
			echo wp_json_encode($output);
		}
		if($action === 'table_edit_wpte'){
			if(wptableeditor_init::check_names($WPTABLEEDITOR_TABLE, 'table_name', sanitize_text_field($_POST["table_name"]), $table_id)){
				echo wp_kses_post('<div class="alert alert-danger">'.esc_html__('The name has already been taken or invalid', 'wp-table-editor').'</div>');
				return;
			}
			foreach($tables as $table){
				if(isset($_POST["$table"]) && $_POST["$table"] !== 'table_type'){
					$data[$table] = sanitize_text_field($_POST["$table"]);
				}
			}
			$result = $wpdb->update($WPTABLEEDITOR_TABLE, $data, array('table_id' => $table_id));
			if($result){
				echo wp_kses_post('<div class="alert alert-success">'.esc_html__('The table was saved successfully.', 'wp-table-editor').'</div>');
			}
		}
		if($action === 'table_style_xs'){
			foreach($tables as $table){
				if(isset($_POST["$table"])){
					$data[$table] = sanitize_text_field($_POST["$table"]);
				}
			}
			$table_dom = '';
			if(isset($_POST["xs_dom"])){
				$xs_dom = array_map('sanitize_text_field', $_POST["xs_dom"]);
				foreach($xs_dom as $dom){
					$table_dom .= $dom.",";
				}
			}else{
				$table_dom = "r,t,";
			}
			$data['table_dom'] = substr($table_dom, 0, -1);
			$result = $wpdb->update($WPTABLEEDITOR_TABLE, $data, array('table_id' => $table_id));
			if($result){
				echo wp_kses_post('<div class="alert alert-success">'.esc_html__('The table was saved successfully.', 'wp-table-editor').'</div>');
			}
		}
		if($action === 'table_edit_restriction_wpte' && wptableeditor_table::login($table_id) === 'yes'){
			$permission = '';
			if(isset($_POST["xs_role"])){
				$xs_role = array_map('sanitize_text_field', $_POST["xs_role"]);
				foreach($xs_role as $role){
					$permission .= $role.",";
				}
			}
			$data = array(
				'table_restrictionrole'	=>	substr($permission, 0, -1)
			);
			$result = $wpdb->update($WPTABLEEDITOR_TABLE, $data, array('table_id' => $table_id));
			if($result){
				echo wp_kses_post('<div class="alert alert-success">'.esc_html__('Changes have been saved.', 'wp-table-editor').'</div>');
			}
		}
		if($action === 'table_edit_status_wpte'){
			$status = 'active';
			if($_POST['status'] === 'active'){
				$status = 'inactive';	
			}
			$data = array(
				'table_status'	=>	$status
			);
			$result = $wpdb->update($WPTABLEEDITOR_TABLE, $data, array('table_id' => $table_id));
			if($result){
				echo wp_kses_post('<div class="alert alert-success">'.esc_html__('Table status change to ', 'wp-table-editor').$status.'</div>');
			}
		}
		if($action === 'table_delete_wpte'){
			$table = WPTABLEEDITOR_PREFIX.$table_id;
			$result = $wpdb->delete($WPTABLEEDITOR_TABLE, array('table_id' => $table_id));
			$result = $wpdb->delete($WPTABLEEDITOR_COLUMN, array('table_id' => $table_id));
			if($result){
				$wpdb->query("DROP TABLE IF EXISTS `{$table}`");
				echo wp_kses_post('<div class="alert alert-success">'.esc_html__('Table Deleted', 'wp-table-editor').'</div>');
			}
		}
		if($action === 'table_multi_active_wpte'){
			$status = 'active';
			$data = array(
				'table_status'	=>	$status
			);
			$ids = array_unique($table_id);
			foreach($ids as $id){
				$result = $wpdb->update($WPTABLEEDITOR_TABLE, $data, array('table_id' => (int)$id));
			}
			if($result){
				echo wp_kses_post('<div class="alert alert-success">'.esc_html__('Table status change to ', 'wp-table-editor').$status.'</div>');
			}
		}
		if($action === 'table_multi_inactive_wpte'){
			$status = 'inactive';
			$data = array(
				'table_status'	=>	$status
			);
			$ids = array_unique($table_id);
			foreach($ids as $id){
				$result = $wpdb->update($WPTABLEEDITOR_TABLE, $data, array('table_id' => (int)$id));
			}
			if($result){
				echo wp_kses_post('<div class="alert alert-success">'.esc_html__('Table status change to ', 'wp-table-editor').$status.'</div>');
			}
		}
		if($action === 'table_multi_duplicate_wpte'){
			$ids = array_unique($table_id);
			foreach($ids as $id){
				$id = (int)$id;
				$query = "SELECT * FROM `{$WPTABLEEDITOR_TABLE}` WHERE table_id = %d";
				$result = $wpdb->get_row($wpdb->prepare($query, $id), ARRAY_A);
				if($result){
					$result['table_name'] = 'Copy of '.$result['table_name'];
					unset($result['table_id']);
					$result1 = $wpdb->insert($WPTABLEEDITOR_TABLE, $result);
					if($result1){
						$id_new = $wpdb->insert_id;
						$query2 = "SELECT * FROM `{$WPTABLEEDITOR_COLUMN}` WHERE table_id = %d";
						$result2 = $wpdb->get_results($wpdb->prepare($query2, $id), ARRAY_A);
						foreach($result2 as $row){
							$row['table_id'] = $id_new;
							unset($row['id']);
							$result3 = $wpdb->insert($WPTABLEEDITOR_COLUMN, $row);
						}
						if($result3){
							$table = WPTABLEEDITOR_PREFIX.$id;
							$table_new = WPTABLEEDITOR_PREFIX.$id_new;
							$result4 = $wpdb->query("CREATE TABLE `{$table_new}` LIKE `{$table}` ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
							if($result4){
								$result5 = $wpdb->query("INSERT INTO `{$table_new}` SELECT * FROM `{$table}`");
							}
						}
					}
				}
			}
			if($result4 || $result5){
				echo wp_kses_post('<div class="alert alert-success">'.esc_html__('Table Copied', 'wp-table-editor').'</div>');
			}
		}
		if($action === 'table_multi_delete_wpte'){
			$ids = array_unique($table_id);
			foreach($ids as $id){
				$id = (int)$id;
				$table = WPTABLEEDITOR_PREFIX.$id;
				$result = $wpdb->delete($WPTABLEEDITOR_TABLE, array('table_id' => $id));
				$result = $wpdb->delete($WPTABLEEDITOR_COLUMN, array('table_id' => $id));
				$wpdb->query("DROP TABLE IF EXISTS `{$table}`");
			}
			if($result){
				echo wp_kses_post('<div class="alert alert-success">'.esc_html__('Table Deleted', 'wp-table-editor').'</div>');
			}
		}
		if($action === 'table_update_order_wpte' && isset($_POST["start"])){
			$start = sanitize_text_field(trim($_POST["start"]));
			for($count = 0;  $count < count($table_id); $count++){
				$data = array(
					'table_order'	=>	$count + $start + 1
				);
				$id = (int) $table_id[$count];
				$wpdb->update($WPTABLEEDITOR_TABLE, $data, array('table_id' => $id));
			}
		}
	}
}
?>