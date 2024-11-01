<?php

defined( 'ABSPATH' ) || exit;

if(isset($_POST["action"]) && isset($_POST['table_id'])){
	$action = sanitize_text_field($_POST["action"]);
	$table_id = (int) sanitize_text_field($_POST['table_id']);
	check_ajax_referer( 'xs-table'.$table_id.'_front', '_xsnonce' );
	check_admin_referer( 'xs-table'.$table_id.'_front', '_xsnonce' );
	wptableeditor_load::set_memory_limit();
	wptableeditor_load::set_max_execution_time();
	if(in_array($action, array('row_getdatas_wpte'))){
		$table = WPTABLEEDITOR_PREFIX.$table_id;
		$xs_table_value = wptableeditor_load::table($table_id);
		$order_name_type = wptableeditor_load::order_name_type($table_id);
		if($action === 'row_getdatas_wpte'){
			global $wpdb;
			if($xs_table_value['table_rows'] >= 10000){
				$table_serverside = 'yes';
			}else{
				$table_serverside = $xs_table_value['table_serverside'];
			}
			$table_limit = $xs_table_value['table_limit'];
			$order_names = $order_name_type['names'];
			$order_types = $order_name_type['types'];
			$order_login = $order_name_type['login'];
			$order_title = $order_name_type['title'];
			$order_id = $order_name_type['id'];
			if($table_serverside != 'yes'){
				$query = "SELECT * FROM `{$table}` WHERE column_status = %s ORDER BY column_id ASC";
				if($table_limit >= 0){
					$query .= " LIMIT {$table_limit}";
				}
				$result = $wpdb->get_results($wpdb->prepare($query, 'active'), ARRAY_A);
			}else{
				$order_name = $order_name_type['name'];
				$column_total = $order_name_type['count'];
				$query = "SELECT * FROM `{$table}` WHERE column_status = 'active' ";
				$wild = '%';
				$like = array();
				$query1 = '';
				for($x = 1; $x <= $column_total; $x++){
					if(isset($_POST["columns"][$x]["search"]["value"]) && !empty($_POST["columns"][$x]["search"]["value"])){
						$value = str_replace(array('^', '\\', '$'), '', sanitize_text_field($_POST["columns"][$x]["search"]["value"]));
						$like[] = $wild . $wpdb->esc_like( $value ) . $wild;
						$query1 .= 'AND '.$order_names[$x].' LIKE "%s" ';
					}
				}
				if(isset($_POST["search"]["value"]) && !empty($_POST["search"]["value"])){
					$value = str_replace(array('^', '$'), '', sanitize_text_field($_POST["search"]["value"]));
					$like[] = $wild . $wpdb->esc_like( $value ) . $wild;
					$query1 .= 'AND (column_id LIKE "%s" ';
					for ($x = 1; $x <= $column_total; $x++) {
						$like[] = $wild . $wpdb->esc_like( $value ) . $wild;
						$query1 .= 'OR '.$order_name[$x].' LIKE "%s" ';
					}
					$query1 .= ') ';
				}
				if(isset($_POST["order"])){
					$column = (int) sanitize_text_field($_POST['order']['0']['column']);
					$type = sanitize_text_field($_POST['order']['0']['dir']);
					$order = $order_name[$column].' '.$type;
					$query1 .= 'ORDER BY '.$order.' ';
				}
				$query2 = '';
				if(isset($_POST["length"]) && $_POST["length"] != -1){
					$start = (int) sanitize_text_field($_POST['start']);
					$length = (int) sanitize_text_field($_POST['length']);
					$limit = $start.', '.$length;
					$query2 .= 'LIMIT '.$limit;
				}
				if(!empty($like)){
					$result = $wpdb->get_results($wpdb->prepare($query.$query1.$query2, $like), ARRAY_A);
				}else{
					$result = $wpdb->get_results($query.$query1.$query2, ARRAY_A);
				}
			}
			$data = array();
			$output = array();
			foreach($result as $row){
				$xsnonce = wp_create_nonce('xs-table'.$table_id.'xs-row'.$row['column_id']);
				$sub_array = array();
				$sub_array[] = $row['column_id'];
				$sub_array['DT_RowClass'] = 'wptableeditor_'.$table_id.'_row_'.$row['column_id'];
				for ($x = 1; $x <= count($row) - 2; $x++){
					if(isset($order_names[$x])){
						$column_name = $order_names[$x];
						$value = $row[$column_name];
						$html_entity_decode = $value !== null ? html_entity_decode($value) : "";
						if($order_login[$x] === 'yes' && (!is_user_logged_in() || !wptableeditor_load::column_roles($table_id, $order_id[$x]))){
							$sub_array[] = $order_title[$x];
						}else{
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
								$sub_array[] = number_format($value);
							}elseif($order_types[$x] === 'link'){
								if(!empty($value) && filter_var($value, FILTER_VALIDATE_URL)){
									$sub_array[] = '<a href="'.$value.'" target="_blank"><span class="btn btn-info btn-xs update"><span class="dashicons dashicons-admin-links"></span></span></a>';
								}else{
									$sub_array[] = $html_entity_decode;
								}
							}elseif($order_types[$x] === 'image'){
								if(!empty($value) && filter_var($value, FILTER_VALIDATE_URL)){
									$sub_array[] = '<button type="button" name="image_views_wpte" class="img-thumbnail image_views_wpte" data-row_id="'.$row['column_id'].'" data-image_url="'.$value.'" data-xsnonce="'.$xsnonce.'"><img src="'.$value.'" style="height: 120px;width: auto;max-width: fit-content;"></button>';
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
				}
				if($row['column_order'] === 0){
					$sub_array[] = $row['column_id'];
				}else{
					$sub_array[] = $row['column_order'];
				}
				$data[] = $sub_array;
			}
			if($table_serverside != 'yes'){
				$output = array("data"	=>	$data);
			}else{
				if(!empty($like)){
					$wpdb->get_results($wpdb->prepare($query.$query1, $like));
				}else{
					$wpdb->get_results($query.$query1);
				}
				$recordsFiltered = $wpdb->num_rows;
				$wpdb->get_results($query);
				$recordsTotal = $wpdb->num_rows;
				$output = array(
					"draw"    			=>	intval(sanitize_text_field($_POST["draw"])),
					"recordsTotal"  	=>	$recordsTotal,
					"recordsFiltered"	=>	$recordsFiltered,
					"data"    			=>	$data
				);
			}
			echo wp_json_encode($output);
		}
	}
}
?>