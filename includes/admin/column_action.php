<?php

defined( 'ABSPATH' ) || exit;
wptableeditor_load::current_user_can();

if(isset($_POST["action"]) && isset($_POST['table_id'])){
	$action = sanitize_text_field($_POST["action"]);
	$table_id = (int) sanitize_text_field($_POST['table_id']);
	$table = WPTABLEEDITOR_PREFIX.$table_id;
	$table_type = wptableeditor_table::type($table_id);
	$tables = wptableeditor_column::column();
	$WPTABLEEDITOR_COLUMN = WPTABLEEDITOR_COLUMN;
	if(isset($_POST['column_id'])){
		if(is_array($_POST['column_id'])){
			$column_id = array_map('sanitize_text_field', $_POST['column_id']);
		}else{
			$column_id = (int) sanitize_text_field($_POST['column_id']);
		}
	}
	if(in_array($action, array('column_getdata_wpte', 'column_add_wpte', 'column_update_order_wpte', 'column_multi_active_wpte', 'column_multi_inactive_wpte', 'column_multi_duplicate_wpte', 'column_multi_delete_wpte'))){
		check_ajax_referer( 'xs-table'.$table_id, '_xsnonce' );
		check_admin_referer( 'xs-table'.$table_id, '_xsnonce' );
	}elseif(isset($column_id)){
		check_ajax_referer( 'xs-table'.$table_id.'xs-column'.$column_id, '_xsnonce' );
		check_admin_referer( 'xs-table'.$table_id.'xs-column'.$column_id, '_xsnonce' );
	}
	wptableeditor_load::set_memory_limit();
	wptableeditor_load::set_max_execution_time();
	if($action === 'column_getdata_wpte'){
		$output = array();
		$data = array();
		if($table_type === 'product' && !function_exists('wc_get_products')){
			echo wp_json_encode($data);
			return;
		}elseif(in_array($table_type, array('post', 'page'))){
			$table_types = wptableeditor_post::type($table_type);
		}elseif($table_type === 'product'){
			$table_types = wptableeditor_woocommerce::product_types();
		}elseif($table_type === 'order'){
			$table_types = wptableeditor_woocommerce::order_types();
		}
		$query = "SELECT * FROM `{$WPTABLEEDITOR_COLUMN}` WHERE table_id = %d ORDER BY column_order ASC";
		$result = $wpdb->get_results($wpdb->prepare($query, $table_id));
		foreach($result as $row){
			$column_id = $row->id;
			$xsnonce = wp_create_nonce('xs-table'.$table_id.'xs-column'.$column_id);
			$sub_array = array();
			$sub_array[] = '<input type="checkbox" name="column_id[]" id="'.$column_id.'" value="'.$column_id.'" />';
			$sub_array['DT_RowId'] = $column_id;
			$sub_array[] = $row->column_names;
			$sub_array[] = $row->column_filters;
			$sub_array[] = $row->column_search;
			$sub_array[] = $row->column_hidden;
			$sub_array[] = $row->column_width;
			$sub_array[] = $row->column_align;
			if($table_type === 'default'){
				if(empty($row->column_type)){
					$sub_array[] = 'textarea';
				}else{
					$sub_array[] = $row->column_type;
				}
			}elseif(isset($table_types[$row->column_name])){
				$sub_array[] = $table_types[$row->column_name];
			}else{
				$sub_array[] = $row->column_name;
			}
			$sub_array[] = $row->column_priority;
			$sub_array[] = $row->column_order;
			if($row->column_status === 'active'){
				$status = '<button type="button" name="column_edit_status_wpte" class="btn btn-success btn-xs xs-mw-70px column_edit_status_wpte" id="'.$column_id.'" data-status="'.$row->column_status.'" data-xsnonce="'.$xsnonce.'">Active</button>';
			}else{
				$status = '<button type="button" name="column_edit_status_wpte" class="btn btn-danger btn-xs column_edit_status_wpte" id="'.$column_id.'" data-status="'.$row->column_status.'" data-xsnonce="'.$xsnonce.'">Inactive</button>';
			}
			$sub_array[] = $status;
			$sub_array[] = '<button type="button" name="column_edit_wpte" class="btn btn-warning btn-xs column_edit_wpte" id="'.$column_id.'" data-xsnonce="'.$xsnonce.'"><span class="dashicons dashicons-edit"></span></button>';
			$sub_array[] = '<button type="button" name="column_style_xs" class="btn btn-warning btn-xs column_style_xs" id="'.$column_id.'" data-xsnonce="'.$xsnonce.'"><span class="dashicons dashicons-admin-customizer"></span></button>';
			$sub_array[] = '<button type="button" name="column_delete_wpte" class="btn btn-danger btn-xs column_delete_wpte" data-id="'.$column_id.'" data-xsnonce="'.$xsnonce.'"><span class="dashicons dashicons-no"></span></button>';
			$data[] = $sub_array;
		}
		$output = array("data"	=>	$data);
		echo wp_json_encode($output);
	}

	if($action === 'column_add_wpte'){
		$column_names = sanitize_text_field($_POST["column_names"]);
		if(wptableeditor_init::check_name($WPTABLEEDITOR_COLUMN, $table_id, 'column_names', $column_names)){
			echo wp_kses_post('<div class="alert alert-danger">'.esc_html__('The name has already been taken or invalid', 'wp-table-editor').'</div>');
			return;
		}
		$query = "SELECT MAX(column_order) as column_order FROM `{$WPTABLEEDITOR_COLUMN}` WHERE table_id = %d";
		$result = $wpdb->get_results($wpdb->prepare($query, $table_id));
		foreach($result as $row){
			$max_order = $row->column_order;
		}
		$column_name = 'column_'. (wptableeditor_row::column_name($table) + 1);
		if(isset($_POST["column_name"])){
			$column_name = sanitize_text_field($_POST["column_name"]);
		}
		foreach($tables as $_table){
			if(isset($_POST["$_table"])){
				$data[$_table] = sanitize_text_field($_POST["$_table"]);
			}
		}
		$column_customfilter = implode(";", array_filter(array_unique(array_map('sanitize_text_field', $_POST["column_customfilter"])), 'strlen'));
		$column_customtype = implode(";", array_filter(array_unique(array_map('sanitize_text_field', $_POST["column_customtype"])), 'strlen'));
		$data['column_customfilter'] = $column_customfilter;
		$data['column_customtype'] = $column_customtype;
		$data['table_id'] = $table_id;
		$data['column_order'] = $max_order + 1;
		$data['column_name'] = $column_name;
		$result = $wpdb->insert($WPTABLEEDITOR_COLUMN, $data);
		if($result){
			if($table_type === 'default'){
				$wpdb->query("ALTER TABLE `{$table}` ADD `{$column_name}` LONGTEXT");
			}
			wptableeditor_column::update_columns($table_id);
			echo wp_kses_post('<div class="alert alert-success">'.esc_html__('Column ', 'wp-table-editor').$column_names.esc_html__(' Added', 'wp-table-editor').'</div>');
		}
	}

	if(isset($column_id)){
		if($action === 'column_restrictionrole_single_wpte'){
			$query = "SELECT column_names, column_restrictionrole FROM `{$WPTABLEEDITOR_COLUMN}` WHERE id = %d";
			$result = $wpdb->get_results($wpdb->prepare($query, $column_id));
			$output = array();
			$column_restrictionrole = array();
			foreach($result as $row){
				$column_names = $row->column_names;
				foreach(explode(',', $row->column_restrictionrole) as $role){
					if(!empty($role)){
						$column_restrictionrole[] = trim($role);
					}
				}
			}
			$column_restrictionrole[] = 'administrator';
			$output['column_restrictionrole'] = array_unique($column_restrictionrole);
			$output['column_names'] = $column_names;
			echo wp_json_encode($output);
		}
		if($action === 'column_single_wpte'){
			$query = "SELECT * FROM `{$WPTABLEEDITOR_COLUMN}` WHERE id = %d";
			$result = $wpdb->get_results($wpdb->prepare($query, $column_id));
			$output = array();
			foreach($result as $row){
				foreach($tables as $table){
					if($row->$table !== ''){
						$output["$table"] = $row->$table;
					}
					if($table === 'column_control'){
						$column_control = $row->$table;
					}
					$count = 1;
					$column_customfilter = '';
					if(empty($row->column_customfilter)){
						$dynamic_array = wptableeditor_column::custom_filter($table_id, $column_id);
						if(empty($dynamic_array)){
							$dynamic_array = array('');
						}
					}else{
						$dynamic_array = explode(";", $row->column_customfilter);
					}
					foreach($dynamic_array as $dynamic){
						$button = '';
						if($count > 1){
							$button = '<button type="button" name="remove" id="'.$count.'" class="btn btn-danger btn-xs remove">x</button>';
						}else{
							$button = '<button type="button" name="add_more" id="add_more" class="btn btn-success btn-xs">+</button>';
						}
						$column_customfilter .= '
							<tr id="row'.$count.'">
								<td class="xs-p-5"><input type="text" name="column_customfilter[]" placeholder="option" class="form-control name_list" value="'.$dynamic.'" /></td>
								<td class="text-center xs-w-45px">'.$button.'</td>
							</tr>
						';
						$count++;
					}
					$output["column_customfilter"] = $column_customfilter;
					$count = 1;
					$column_customtype = '';
					$dynamic_array = explode(";", $row->column_customtype);
					foreach($dynamic_array as $dynamic){
						$button = '';
						if($count > 1){
							$button = '<button type="button" name="remove_option" id="'.$count.'" class="btn btn-danger btn-xs remove_option">x</button>';
						}else{
							$button = '<button type="button" name="add_option" id="add_option" class="btn btn-success btn-xs">+</button>';
						}
						$column_customtype .= '
							<tr id="rows'.$count.'">
								<td class="xs-p-5"><input type="text" name="column_customtype[]" placeholder="option" class="form-control name_list" value="'.$dynamic.'" /></td>
								<td class="text-center xs-w-45px">'.$button.'</td>
							</tr>
						';
						$count++;
					}
					$output["column_customtype"] = $column_customtype;
					$output["column_render"] = stripslashes(maybe_unserialize($row->column_render));
					$output["column_createdcell"] = stripslashes(maybe_unserialize($row->column_createdcell));
				}
			}
			if(empty($column_control)){
				$column_control = 'desktop,tablet-l,tablet-p,mobile-l,mobile-p';
			}
			foreach(explode(',', $column_control) as $control){
				$controls[] = trim($control);
			}
			$output['column_control'] = wp_json_encode($controls);
			echo wp_json_encode($output);
		}

		if($action === 'column_edit_status_wpte'){
			$status = 'active';
			if($_POST['status'] === 'active'){
				$status = 'inactive';	
			}
			$data = array(
				'column_status'	=>	$status
			);
			$result = $wpdb->update($WPTABLEEDITOR_COLUMN, $data, array('id' => $column_id));
			if($result){
				wptableeditor_column::update_columns($table_id);
				echo wp_kses_post('<div class="alert alert-success">'.esc_html__('Column status change to ', 'wp-table-editor').$status.'</div>');
			}
		}

		if($action === 'column_edit_wpte'){
			$column_names = sanitize_text_field($_POST["column_names"]);
			if(wptableeditor_init::check_name($WPTABLEEDITOR_COLUMN, $table_id, 'column_names', $column_names, $column_id)){
				echo wp_kses_post('<div class="alert alert-danger">'.esc_html__('The name has already been taken or invalid', 'wp-table-editor').'</div>');
				return;
			}
			foreach($tables as $table){
				if(isset($_POST["$table"])){
					$data[$table] = sanitize_text_field($_POST["$table"]);
				}
			}
			if(isset($_POST["column_customfilter"])){
				$column_customfilter = implode(";", array_filter(array_unique(array_map('sanitize_text_field', $_POST["column_customfilter"])), 'strlen'));
				$data['column_customfilter'] = $column_customfilter;
			}
			if(isset($_POST["column_customtype"])){
				$column_customtype = implode(";", array_filter(array_unique(array_map('sanitize_text_field', $_POST["column_customtype"])), 'strlen'));
				$data['column_customtype'] = $column_customtype;
			}
			$result = $wpdb->update($WPTABLEEDITOR_COLUMN, $data, array('id' => $column_id));
			if($result){
				echo wp_kses_post('<div class="alert alert-success">'.esc_html__('Column ', 'wp-table-editor').$column_names.esc_html__(' Edited', 'wp-table-editor').'</div>');
			}
		}

		if($action === 'column_style_xs'){
			foreach($tables as $table){
				if(isset($_POST["$table"])){
					$data[$table] = sanitize_text_field($_POST["$table"]);
				}
			}
			$column_control = '';
			if(isset($_POST["xs_control"])){
				$xs_control = array_map('sanitize_text_field', $_POST["xs_control"]);
				foreach($xs_control as $control){
					$column_control .= $control.",";
				}
			}else{
				$column_control = "desktop,tablet-l,tablet-p,mobile-l,mobile-p,";
			}
			$data['column_control'] = substr($column_control, 0, -1);
			if(isset($_POST["column_render"])){
				$data['column_render'] = maybe_serialize(sanitize_option( 'column_render', trim($_POST["column_render"] )));
			}
			if(isset($_POST["column_createdcell"])){
				$data['column_createdcell'] = maybe_serialize(sanitize_option( 'column_createdcell', trim($_POST["column_createdcell"] )));
			}
			$result = $wpdb->update($WPTABLEEDITOR_COLUMN, $data, array('id' => $column_id));
			if($result){
				echo wp_kses_post('<div class="alert alert-success">'.esc_html__('The column was saved successfully.', 'wp-table-editor').'</div>');
			}
		}

		if($action === 'column_edit_restriction_wpte' && wptableeditor_column::login($table_id, $column_id) === 'yes'){
			$permission = '';
			if(isset($_POST["xs_role"])){
				$xs_role = array_map('sanitize_text_field', $_POST["xs_role"]);
				foreach($xs_role as $role){
					$permission .= $role.",";
				}
			}
			$data = array(
				'column_restrictionrole'	=>	substr($permission, 0, -1)
			);
			$result = $wpdb->update($WPTABLEEDITOR_COLUMN, $data, array('table_id' => $table_id, 'id' => $column_id));
			if($result){
				echo wp_kses_post('<div class="alert alert-success">'.esc_html__('Changes have been saved.', 'wp-table-editor').'</div>');
			}
		}

		if($action === 'column_delete_wpte'){
			$column_name = wptableeditor_column::id_name($table_id)[$column_id];
			if($table_type === 'default'){
				$wpdb->query("ALTER TABLE `{$table}` DROP `{$column_name}`");
			}
			$result = $wpdb->delete($WPTABLEEDITOR_COLUMN, array('id' => $column_id));
			if($result){
				wptableeditor_column::update($table_id);
				wptableeditor_column::update_columns($table_id);
				echo wp_kses_post('<div class="alert alert-success">'.esc_html__('Column has been Deleted', 'wp-table-editor').'</div>');
			}
		}

		if($action === 'column_multi_active_wpte'){
			$ids = array_unique($column_id);
			$status = 'active';
			$data = array(
				'column_status'	=>	$status
			);
			foreach($ids as $id){
				$result = $wpdb->update($WPTABLEEDITOR_COLUMN, $data, array('id' => (int)$id));
			}
			if($result){
				wptableeditor_column::update_columns($table_id);
				echo wp_kses_post('<div class="alert alert-success">'.esc_html__('Column status change to ', 'wp-table-editor').$status.'</div>');
			}
		}

		if($action === 'column_multi_inactive_wpte'){
			$ids = array_unique($column_id);
			$status = 'inactive';
			$data = array(
				'column_status'	=>	$status
			);
			foreach($ids as $id){
				$result = $wpdb->update($WPTABLEEDITOR_COLUMN, $data, array('id' => (int)$id));
			}
			if($result){
				wptableeditor_column::update_columns($table_id);
				echo wp_kses_post('<div class="alert alert-success">'.esc_html__('Column status change to ', 'wp-table-editor').$status.'</div>');
			}
		}

		if($action === 'column_multi_duplicate_wpte'){
			$ids = array_unique($column_id);
			foreach($ids as $id){
				$query = "SELECT * FROM `{$WPTABLEEDITOR_COLUMN}` WHERE id = %d";
				$result = $wpdb->get_results($wpdb->prepare($query, (int)$id));
				if($result){
					$column_name = 'column_'. (wptableeditor_row::column_name($table) + 1);
					foreach($result as $row){
						$row = (array)$row;
						$row['column_names'] = 'Copy of '.$row['column_names'];
						$row['column_order'] = wptableeditor_row::column_name($table) + 1;
						if($table_type === 'default'){
							$row['column_name'] = $column_name;
						}
						unset($row['id']);
					}
					$result = $wpdb->insert($WPTABLEEDITOR_COLUMN, $row);
					if($result && $table_type === 'default'){
						$wpdb->query("ALTER TABLE `{$table}` ADD `{$column_name}` LONGTEXT");
					}
				}
			}
			if($result){
				wptableeditor_column::update($table_id);
				wptableeditor_column::update_columns($table_id);
				echo wp_kses_post('<div class="alert alert-success">'.esc_html__('Column Copied', 'wp-table-editor').'</div>');
			}
		}

		if($action === 'column_multi_delete_wpte'){
			$ids = array_unique($column_id);
			foreach($ids as $id){
				$column_name = wptableeditor_column::id_name($table_id)[(int)$id];
				if($table_type === 'default'){
					$wpdb->query("ALTER TABLE `{$table}` DROP `{$column_name}`");
				}
				$result = $wpdb->delete($WPTABLEEDITOR_COLUMN, array('id' => (int)$id));
			}
			if($result){
				wptableeditor_column::update($table_id);
				wptableeditor_column::update_columns($table_id);
				echo wp_kses_post('<div class="alert alert-success">'.esc_html__('Column has been Deleted', 'wp-table-editor').'</div>');
			}
		}

		if($action === 'column_update_order_wpte' && isset($_POST["start"])){
			$start = sanitize_text_field(trim($_POST["start"]));
			for($count = 0;  $count < count($column_id); $count++){
				$data = array(
					'column_order'	=>	$count + $start + 1
				);
				$id = (int) $column_id[$count];
				$wpdb->update($WPTABLEEDITOR_COLUMN, $data, array('id' => $id));
			}
		}
	}
}
?>