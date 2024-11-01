<?php

defined( 'ABSPATH' ) || exit;
wptableeditor_load::current_user_can();

if(isset($_POST["action"]) && isset($_POST["table_id"])){
	if(sanitize_text_field($_POST["action"]) === 'process_xs'){
		$table_id = (int) sanitize_text_field($_POST["table_id"]);
		check_ajax_referer( 'xs-import'.$table_id, '_xsnonce' );
		check_admin_referer( 'xs-import'.$table_id, '_xsnonce' );
		wptableeditor_load::set_memory_limit();
		wptableeditor_load::set_max_execution_time();
		$table = WPTABLEEDITOR_PREFIX.$table_id;
		$query = "SELECT * FROM `{$table}` ";
		$result = $wpdb->get_results($query);
		echo esc_attr($wpdb->num_rows);
	}
}
?>