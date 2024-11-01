<?php

defined( 'ABSPATH' ) || exit;
wptableeditor_load::current_user_can();

if ( ! class_exists( 'wptableeditor_option' ) ) {
	class wptableeditor_option {
		public function __construct(){
			global $pagenow;
			if( current_user_can( 'manage_options' ) ) {
				if($pagenow === 'admin.php' && isset($_GET['page']) && $_GET['page'] === 'wptableeditor' && isset($_GET['tab']) && $_GET['tab'] == 'option'){
			        add_action( 'admin_enqueue_scripts', array( $this, 'xs_enqueue_style' ));
			        add_action( 'admin_init', array( $this, 'custom_css' ) );
		    	}
		    	add_filter( 'plugin_action_links_'. WPTABLEEDITOR_MAIN, array( $this, 'pro' ) );
	    	}
		}
		public function custom_css() {
			if ( isset( $_POST['xs_update_css'] ) && isset( $_POST['xs_custom_css'] ) ) {
				$result = update_option( 'xs_custom_css', $_POST['xs_custom_css'] );
				if($result){
					$message = esc_html__('Custom CSS updated successfully.', 'wp-table-editor');
					add_action( 'in_admin_header', function() use ($message) { echo wp_kses_post('<div class="notice notice-success is-dismissible" style="margin: 10px 0 -10px;"><p>'.$message.'</p></div>'); } );
				}
			}
		}
		public function xs_enqueue_style() {
	        wp_enqueue_style( 'xs-custom', WPTABLEEDITOR_ASSETS. 'css/custom.css', array(), '20220306' );
	    	wp_enqueue_code_editor( array( 'type' => 'text/html' ) );
	        wp_enqueue_script( 'xs-custom', WPTABLEEDITOR_ASSETS. 'js/custom.js', array( 'jquery' ), '20220306', true );
	    }
		public function pro( $links ) {
			$pro = '<a style="color:#35b747;font-weight:700;" target="_blank" href="https://wptableeditor.com/pricing/">'.esc_html__( 'Get Pro', 'wp-table-editor' ).'</a>'; 
			array_unshift( $links, $pro ); 
			return $links; 
		}
	}
	new wptableeditor_option();
}
?>