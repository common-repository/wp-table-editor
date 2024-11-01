<?php

defined( 'ABSPATH' ) || exit;
wptableeditor_load::current_user_can();

if ( ! class_exists( 'wptableeditor_ajax' ) ) {
	class wptableeditor_ajax {
		public function __construct(){
			$actions = array(
				'_getdata_wpte',
				'_multi_active_wpte',
				'_multi_inactive_wpte',
				'_multi_duplicate_wpte',
				'_multi_delete_wpte',
				'_single_wpte',
				'_edit_status_wpte',
				'_update_order_wpte',
				'_restrictionrole_single_wpte',
				'_edit_restriction_wpte',
				'_add_wpte',
				'_edit_wpte',
				'_delete_wpte',
			);
			$types = array('table', 'row', 'column');
			foreach($types as $type){
				foreach($actions as $action){
					add_action( "wp_ajax_$type$action", array( $this, $type ));
				}
			}
			$imports = array('confirm_xs', 'import_xs', 'process_xs', 'upload_xs');
			foreach($imports as $import){
				add_action( "wp_ajax_$import", array( $this, $import ));
			}
			add_action( "wp_ajax_table_style_xs", array( $this, 'table' ));
			add_action( "wp_ajax_column_style_xs", array( $this, 'column' ));
		}
		public function table(){
			global $wpdb;
			include_once 'table_action.php';
			wp_die();
		}
		public function row(){
			global $wpdb;
			if(isset($_POST['xs_type'])){
				if($_POST['xs_type'] === 'default'){
					include_once 'row_action.php';
				}elseif($_POST['xs_type'] === 'product'){
					include_once 'woo_action.php';
				}elseif(in_array($_POST['xs_type'], array('post', 'page'))){
					include_once 'post_action.php';
				}elseif($_POST['xs_type'] === 'order'){
					include_once 'woo_order.php';
				}
			}else{
				include_once 'row_action.php';
			}
			wp_die();
		}
		public function column(){
			global $wpdb;
			include_once 'column_action.php';
			wp_die();
		}
		public function confirm_xs(){
			global $wpdb;
			include_once 'import_action.php';
			wp_die();
		}
		public function import_xs(){
			global $wpdb;
			include_once 'import_action.php';
			wp_die();
		}
		public function process_xs(){
			global $wpdb;
			include_once 'process_action.php';
			wp_die();
		}
		public function upload_xs(){
			global $wpdb;
			include_once 'upload_action.php';
			wp_die();
		}
	}
	new wptableeditor_ajax();
}
?>