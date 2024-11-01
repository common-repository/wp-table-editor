<?php

defined( 'ABSPATH' ) || exit;

if(isset($table_id)){
	$xs_table_value = wptableeditor_load::table($table_id);
	$xs_table_values = wptableeditor_load::tables();
	$xs_column_data = wptableeditor_load::column($table_id);
	if($xs_table_value === false || $xs_column_data === false){
		return;
	}
	unset($xs_table_value['table_id']);
	foreach($xs_table_value as $table => $value){
		$$table = $value;
		if($table === 'table_createdrow'){
			$$table = stripslashes(maybe_unserialize($value));
		}
		if(in_array($table, $xs_table_values)){
			if($value === 'yes'){
				$$table = 'no';
			}elseif($value > 0){
				$$table = 0;
			}elseif($table === 'table_createdrow'){
				$$table = stripslashes(maybe_unserialize(''));
			}elseif($table === 'table_datasources'){
				$$table = 'default';
			}
		}
	}
	foreach($xs_column_data as $table => $value){
		$$table = $value;
	}
	$check_roles = false;
	if(is_user_logged_in() && isset($xs_table_value['table_restrictionrole'])){
		$user = wp_get_current_user();
		foreach($user->roles as $role){
			if(in_array($role, explode(',', 'administrator,'.$xs_table_value['table_restrictionrole']))){
				$check_roles = true;
			}
		}
	}
	if(empty($table_pagination)){
		$table_pagination = 'simple_numbers';
	}
	if(($table_serverside === 'yes' || $table_rows >= 10000) && $table_type === 'default'){
		$serverSide = "true";
	}else{
		$serverSide = "false";
	}
	if($table_keytable === 'yes'){
		$table_keytable = "true";
	}else{
		$table_keytable = "false";
	}
	if($table_statesave === 'yes'){
		$stateSave = "true";
	}else{
		$stateSave = "false";
	}
	$spreadsheets = sprintf('https://sheets.googleapis.com/v4/spreadsheets/%s/values/%s!%s?key=%s', $table_sheetid, $table_sheetname, $table_range, $table_apikey);
	$pagelength = array(10, 25, 50, 100);
	if($table_length > 0 && !in_array($table_length, $pagelength)){
		array_push($pagelength, $table_length);
		sort($pagelength);
	}
	$pagelength = '['. wp_json_encode($pagelength). ', ' . wp_json_encode($pagelength) .']';
	$table_dom = str_replace(",", '', $xs_table_value['table_dom']);
	$column_align = $xs_column_data['align'];
	if($table_group > $column_align['count']){
		$table_group = 0;
	}
	$orderFixed = '['. $table_group. ', "' . $xs_table_value['table_sortingtype'] .'"]';
	if($table_group > 0 || $table_sorting > $column_align['count']){
		$table_sorting = -1;
	}
	$table_groups = 0;
	if($table_group > 0){
		$table_groups = wptableeditor_load::table_groups($table_id, $table_type)[$table_group];
	}
	if( $table_sorting > -1 ){
		$table_sortingtype = '[['.$table_sorting. ', "' . $xs_table_value['table_sortingtype'] .'"]]';
	}else{
		if(in_array($table_type, array('json', 'sheet'))){
			$table_sortingtype = '[[1, "asc"]]';
		}else{
			$table_sortingtype = '[['.($column_align['count'] + 1).', "asc"]]';
		}
	}
	$allowed_select = array('option' => array('value' => array()));
}
?>