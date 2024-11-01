<?php

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'wptableeditor_init' ) ) {
	class wptableeditor_init {
		public function __construct(){
			if ( is_admin() && current_user_can( 'manage_options' ) ) {
				register_activation_hook( WPTABLEEDITOR_FILE, array( $this, 'activation_hook' ) );
				add_action( 'admin_post_uninstall_wptableeditor', array( $this, 'uninstall' ) );
				add_action( 'admin_post_export_wptableeditor', array( $this, 'export' ) );
				add_filter( 'upload_mimes', array( $this, 'json_mime_types' ) );
				add_filter( 'mce_buttons_2', array( $this, 'mce_buttons' ) );
				$this->json_folder();
			}
		}
		public static function json_mime_types( $mimes ) {
			$mimes['json'] = 'application/json';
			return $mimes;
		}
		public static function request_filesystem_credentials( $tmp_name ) {
			if (false === ($creds = request_filesystem_credentials( $tmp_name, '', false, false, null ) ) ) {
				$error = esc_html__('Error connecting to filesystem', 'wp-table-editor');
				return $error;
			}
			if ( ! WP_Filesystem($creds) ) {
				request_filesystem_credentials( $tmp_name, '', true, false, null );
				$error = esc_html__('Error connecting to filesystem', 'wp-table-editor');
				return $error;
			}
		}
		public static function uninstall(){
			global $wpdb;
			if(current_user_can('deactivate_plugin', WPTABLEEDITOR_MAIN) && wp_verify_nonce($_GET['_wpnonce'], 'uninstall_wptableeditor')){
				$query = "SELECT table_id FROM ".WPTABLEEDITOR_TABLE;
				$result = $wpdb->get_results($query);
				foreach($result as $row){
					$table = WPTABLEEDITOR_PREFIX.$row->table_id;
					$wpdb->query("DROP TABLE IF EXISTS $table");
				}
				$wpdb->query("DROP TABLE IF EXISTS ".WPTABLEEDITOR_COLUMN);
				$wpdb->query("DROP TABLE IF EXISTS ".WPTABLEEDITOR_TABLE);
				@delete_option( 'xs_custom_css' );
				if(defined('WPTABLEEDITOR_PRO')){
					@delete_option('wptableeditor_license');
				}
				wp_redirect('plugins.php');
				@deactivate_plugins(WPTABLEEDITOR_MAIN);
				@deactivate_plugins('wp-table-editor/main.php');
			}else{
				wp_die( __( 'Sorry, you are not allowed to access this page.', 'wp-table-editor' ), 403 );
			}
		}
		public static function export(){
			if(wp_verify_nonce($_GET['_wpnonce'], 'export_wptableeditor')){
				if(isset($_GET['table_id']) && isset($_GET['xs_type'])){
					$table_id = (int)sanitize_text_field($_GET['table_id']);
					$xs_type = sanitize_text_field($_GET['xs_type']);
					$download_data = wptableeditor_column::data_export($table_id, $xs_type);
					$table_name = 'wptableeditor_table_id_'.$table_id.'.csv';
					// Send download headers for export file.
					header( 'Content-Description: File Transfer' );
					header( 'Content-Type: application/octet-stream' );
					header( "Content-Disposition: attachment; filename=\"{$table_name}\"" );
					header( 'Content-Transfer-Encoding: binary' );
					header( 'Expires: 0' );
					header( 'Cache-Control: must-revalidate' );
					header( 'Pragma: public' );
					header( 'Content-Length: ' . strlen( $download_data ) );
					@ob_end_clean();
					flush();
					echo $download_data;
					exit;
				}
			}else{
				wp_die( __( 'Sorry, you are not allowed to access this page.', 'wp-table-editor' ), 403 );
			}
		}
		public static function activation_hook() {
			global $wpdb;
			$WPTABLEEDITOR_TABLE = WPTABLEEDITOR_TABLE;
			$WPTABLEEDITOR_COLUMN = WPTABLEEDITOR_COLUMN;
			$charset_collate = $wpdb->get_charset_collate();
			$sql_table = "CREATE TABLE IF NOT EXISTS $WPTABLEEDITOR_TABLE (
				table_id bigint(20) NOT NULL AUTO_INCREMENT,
				table_status enum('active','inactive') NOT NULL,
				table_order bigint(20) NOT NULL,
				table_name varchar(250) NOT NULL,
				table_type enum('default','product','order','post','page','json','sheet') NOT NULL,
				table_responsive enum('collapse','scroll','flip') NOT NULL,
				table_responsivetype enum('default','modal') NOT NULL,
				table_paging enum('no','yes') NOT NULL,
				table_footer enum('no','yes') NOT NULL,
				table_button enum('no','yes') NOT NULL,
				table_sortingtype enum('asc','desc') NOT NULL,
				table_serverside enum('no','yes') NOT NULL,
				table_category varchar(250) NOT NULL,
				table_pagination varchar(250) NOT NULL,
				table_hover enum('no','yes') NOT NULL,
				table_ordercolumn enum('no','yes') NOT NULL,
				table_author int(11) NOT NULL,
				table_rows int(11) NOT NULL,
				table_columns int(11) NOT NULL,
				table_scrolly int(11) NOT NULL,
				table_length int(11) NOT NULL,
				table_limit int(11) NOT NULL,
				table_sorting int(2) NOT NULL,
				table_orderfixed enum('no','yes') NOT NULL,
				table_width int(2) NOT NULL,
				table_unit enum('%','px') NOT NULL,
				table_border enum('yes','no') NOT NULL,
				table_hidethead enum('no','yes') NOT NULL,
				table_fontsize varchar(250) NOT NULL,
				table_fontfamily varchar(250) NOT NULL,
				table_headerfontsize varchar(250) NOT NULL,
				table_headerfontfamily varchar(250) NOT NULL,
				table_headerfontweight varchar(250) NOT NULL,
				table_headerfontstyle varchar(250) NOT NULL,
				table_headerbackgroundcolor varchar(250) NOT NULL,
				table_headerfontcolor varchar(250) NOT NULL,
				table_headerlinkcolor varchar(250) NOT NULL,
				table_headersortingcolor varchar(250) NOT NULL,
				table_bodyfontsize varchar(250) NOT NULL,
				table_bodyfontfamily varchar(250) NOT NULL,
				table_bodyfontweight varchar(250) NOT NULL,
				table_bodyfontstyle varchar(250) NOT NULL,
				table_evenbackgroundcolor varchar(250) NOT NULL,
				table_oddbackgroundcolor varchar(250) NOT NULL,
				table_evenfontcolor varchar(250) NOT NULL,
				table_oddfontcolor varchar(250) NOT NULL,
				table_evenlinkcolor varchar(250) NOT NULL,
				table_oddlinkcolor varchar(250) NOT NULL,
				table_buttonbackgroundcolor varchar(250) NOT NULL,
				table_buttonfontcolor varchar(250) NOT NULL,
				table_restrictionrole varchar(250) NOT NULL,
				table_dom text NOT NULL,
				table_url varchar(250) NOT NULL,
				table_datasrc varchar(250) NOT NULL,
				table_apikey varchar(250) NOT NULL,
				table_sheetid varchar(250) NOT NULL,
				table_sheetname varchar(250) NOT NULL,
				table_range varchar(250) NOT NULL,
				table_reload int(11) NOT NULL,
				table_prefilter varchar(250) NOT NULL,
				table_jsonsave enum('no','yes') NOT NULL,
				table_autosave int(2) NOT NULL,
				table_jsonname varchar(250) NOT NULL,
				table_jsonnametemp varchar(250) NOT NULL,
				table_jsonsavedate varchar(250) NOT NULL,
				table_datasources enum('default','javascript') NOT NULL,
				table_datatable int(11) NOT NULL,
				table_rowstatus enum('all','active') NOT NULL,
				table_checkbox enum('yes','no') NOT NULL,
				table_note text NOT NULL,
				table_keytable enum('no','yes') NOT NULL,
				table_select enum('no','yes') NOT NULL,
				table_fixedheader enum('no','yes') NOT NULL,
				table_fixedfooter enum('no','yes') NOT NULL,
				table_searchbuilder enum('no','yes') NOT NULL,
				table_searchpanes enum('no','yes') NOT NULL,
				table_visibility enum('no','yes') NOT NULL,
				table_statesave enum('no','yes') NOT NULL,
				table_restriction enum('no','yes') NOT NULL,
				table_editor enum('no','yes') NOT NULL,
				table_filter enum('no','yes') NOT NULL,
				table_fixedleft int(2) NOT NULL,
				table_fixedright int(2) NOT NULL,
				table_group int(2) NOT NULL,
				table_createdrow text NOT NULL,
				table_createdbutton text NOT NULL,
				table_createstate enum('no','yes') NOT NULL,
				table_predefinedstates enum('no','yes') NOT NULL,
				table_staterestore text NOT NULL,
				PRIMARY KEY (table_id)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
			$sql_column = "CREATE TABLE IF NOT EXISTS $WPTABLEEDITOR_COLUMN (
				id bigint(20) NOT NULL AUTO_INCREMENT,
				table_id bigint(20) NOT NULL,
				column_names varchar(250) NOT NULL,
				column_order bigint(20) NOT NULL,
				column_filters enum('no','yes') NOT NULL,
				column_position enum('default','footer','hidden') NOT NULL,
				column_filter enum('no','yes') NOT NULL,
				column_hidden enum('no','yes') NOT NULL,
				column_total enum('no','yes') NOT NULL,
				column_editable enum('yes','no') NOT NULL,
				column_required enum('no','yes') NOT NULL,
				column_orderable enum('yes','no') NOT NULL,
				column_searchable enum('yes','no') NOT NULL,
				column_type varchar(250) NOT NULL,
				column_width int(2) NOT NULL,
				column_minwidth int(2) NOT NULL,
				column_imageheight int(2) NOT NULL,
				column_modalimage enum('no','yes') NOT NULL,
				column_align enum('left','center','right') NOT NULL,
				column_nowrap enum('no','yes') NOT NULL,
				column_status enum('active','inactive') NOT NULL,
				column_name varchar(250) NOT NULL,
				column_fontweight varchar(250) NOT NULL,
				column_fontstyle varchar(250) NOT NULL,
				column_backgroundcolor varchar(250) NOT NULL,
				column_fontcolor varchar(250) NOT NULL,
				column_customfilter text NOT NULL,
				column_customtype text NOT NULL,
				column_restrictionrole varchar(250) NOT NULL,
				column_control text NOT NULL,
				column_index enum('no','yes') NOT NULL,
				column_childrows enum('no','yes') NOT NULL,
				column_restriction enum('no','yes') NOT NULL,
				column_search enum('no','yes') NOT NULL,
				column_priority int(2) NOT NULL,
				column_optionlimit int(2) NOT NULL,
				column_characterlimit int(2) NOT NULL,
				column_restrictiontitle varchar(250) NOT NULL,
				column_render text NOT NULL,
				column_createdcell text NOT NULL,
				PRIMARY KEY (id)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			dbDelta( $sql_table );
			dbDelta( $sql_column );
		}
		public static function url_uninstall(){
			$args = array('action' => 'uninstall_wptableeditor');
			$url = wp_nonce_url(add_query_arg($args, admin_url('admin-post.php')), 'uninstall_wptableeditor');
			return $url;
		}
		public static function url_export($table_id, $xs_type){
			$args = array('action' => 'export_wptableeditor', 'table_id' => $table_id, 'xs_type' => $xs_type );
			$url = wp_nonce_url(add_query_arg($args, admin_url('admin-post.php')), 'export_wptableeditor');
			return $url;
		}
		public static function move_column($table_name, $column_name, $type, $after_column){
			global $wpdb;
			foreach ( $wpdb->get_col( "DESC $table_name", 0 ) as $column ) {
				if ( $column === $column_name ) {
					$wpdb->query("ALTER TABLE `{$table_name}` ADD `{$column_name}` $type NOT NULL AFTER `{$after_column}`");
				}
			}
		}
		public static function drop_column($table_name, $column_name){
			global $wpdb;
			foreach ( $wpdb->get_col( "DESC $table_name", 0 ) as $column ) {
				if ( $column === $column_name ) {
					$wpdb->query( "ALTER TABLE `{$table_name}` DROP `{$column_name}`" );
					foreach ( $wpdb->get_col( "DESC $table_name", 0 ) as $column ) {
						if ( $column === $column_name ) {
							return false;
						}
					}
				}
			}
			return true;
		}
		public static function rename_table($table_old, $table_new){
			global $wpdb;
			if(self::check_tables($table_old)){
				$wpdb->query("RENAME TABLE `{$table_old}` TO `{$table_new}`");
			}
		}
		public static function check_name($table, $table_id, $key, $value, $id = null){
			global $wpdb;
			if(empty(trim($value))){
				return true;
			}
			$query = "SELECT `{$key}` FROM `{$table}` WHERE table_id = %d AND $key = %s";
			if(!empty($id)){
				$query .= " AND NOT id = %d";
				$result = $wpdb->get_var($wpdb->prepare($query, $table_id, $value, $id));
			}else{
				$result = $wpdb->get_var($wpdb->prepare($query, $table_id, $value));
			}
			if(!empty($result)){
				return true;
			}else{
				return false;
			}
		}
		public static function check_names($table, $key, $value, $table_id = null){
			global $wpdb;
			if(empty(trim($value))){
				return true;
			}
			$query = "SELECT `{$key}` FROM `{$table}` WHERE $key = %s";
			if(!empty($table_id)){
				$query .= " AND NOT table_id = %d";
				$result = $wpdb->get_var($wpdb->prepare($query, $value, $table_id));
			}else{
				$result = $wpdb->get_var($wpdb->prepare($query, $value));
			}
			if(!empty($result)){
				return true;
			}else{
				return false;
			}
		}
		public static function add_column($table_name, $column_name, $type, $after_column = null){
			global $wpdb;
			foreach ( $wpdb->get_col( "DESC $table_name", 0 ) as $column ) {
				if ( $column === $column_name ) {
					return true;
				}
			}
			$query = "ALTER TABLE `{$table_name}` ADD `{$column_name}` $type NOT NULL ";
			if(!empty($after_column)){
				$query .= "AFTER `{$after_column}`";
			}
			$wpdb->query( $query );
			foreach ( $wpdb->get_col( "DESC $table_name", 0 ) as $column ) {
				if ( $column === $column_name ) {
					return true;
				}
			}
			return false;
		}
		public static function rename_column($table_name, $column_old, $column_new, $type){
			global $wpdb;
			foreach ( $wpdb->get_col( "DESC $table_name", 0 ) as $column ) {
				if ( $column === $column_old ) {
					$wpdb->query("ALTER TABLE `{$table_name}` CHANGE `{$column_old}` `{$column_new}` $type NOT NULL");
					foreach ( $wpdb->get_col( "DESC $table_name", 0 ) as $column ) {
						if ( $column === $column_old ) {
							return false;
						}
					}
				}
			}
			return true;
		}
		public static function all_roles(){
			global $wp_roles;
			return $wp_roles->get_names();
		}
		public static function check_roles($table_id){
			$permission = wptableeditor_table::roles($table_id);
			if(!empty($permission)){
				$all_roles = explode(',', $permission);
				if(is_user_logged_in()){
					$user = wp_get_current_user();
					$roles = $user->roles;
					foreach($roles as $role){
						if(in_array($role, $all_roles)){
							return true;
						}
					}
				}
			}
			return false;
		}
		public static function check_tables($table_name){
			global $wpdb;
		    $query = $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $table_name ) );
		    if ( $wpdb->get_var( $query ) === $table_name ) {
		        return true;
		    }
		    return false;
		}
		public static function check_column($table_name, $column_name){
			global $wpdb;
			if(self::check_tables($table_name)){
			    foreach ( $wpdb->get_col( "DESC $table_name", 0 ) as $column ) {
			        if ( $column === $column_name ) {
			            return true;
			        }
			    }
			}
		    return false;
		}
		public static function create_directory( $path ) {
			if ( @is_dir( $path ) ) {
				return true;
			}
			return @mkdir( $path, 0777, true );
		}
		public static function create_file( $path, $content ) {
			if ( ! @file_exists( $path ) ) {
				if ( ! @is_writable( dirname( $path ) ) ) {
					return false;
				}
				if ( ! @touch( $path ) ) {
					return false;
				}
			} elseif ( ! @is_writable( $path ) ) {
				return false;
			}
			// No changes were added
			if ( function_exists( 'md5_file' ) ) {
				if ( @md5_file( $path ) === md5( $content ) ) {
					return true;
				}
			}
			$is_written = false;
			if ( ( $handle = @fopen( $path, 'w' ) ) !== false ) {
				if ( @fwrite( $handle, $content ) !== false ) {
					$is_written = true;
				}
				@fclose( $handle );
			}
			return $is_written;
		}
		public function json_folder() {
			$this->create_directory(WPTABLEEDITOR_DATA_PATH);
			$this->create_robots(WPTABLEEDITOR_ROBOTS_TXT);
			$this->create_htaccess(WPTABLEEDITOR_HTACCESS);
			$this->create_config(WPTABLEEDITOR_WEBCONFIG);
			$this->create_index(WPTABLEEDITOR_INDEX_PHP);
			$this->create_index(WPTABLEEDITOR_INDEX_HTML);
		}
		public static function create_robots( $path ) {
			return self::create_file(
				$path,
				implode(
					PHP_EOL,
					array(
						'User-agent: *',
						'Disallow: /wpTableEditor/',
						'Disallow: /wp-content/wpTableEditor/',
					)
				)
			);
		}
		public static function create_htaccess( $path ) {
			return self::create_file(
				$path,
				implode(
					PHP_EOL,
					array(
						'<IfModule mod_mime.c>',
						'AddType application/octet-stream .json',
						'</IfModule>',
						'<IfModule mod_dir.c>',
						'DirectoryIndex index.php',
						'</IfModule>',
						'<IfModule mod_autoindex.c>',
						'Options -Indexes',
						'</IfModule>',
					)
				)
			);
		}
		public static function create_config( $path ) {
			return self::create_file(
				$path,
				implode(
					PHP_EOL,
					array(
						'<configuration>',
						'<system.webServer>',
						'<staticContent>',
						'<mimeMap fileExtension=".json" mimeType="application/octet-stream" />',
						'</staticContent>',
						'<defaultDocument>',
						'<files>',
						'<add value="index.php" />',
						'</files>',
						'</defaultDocument>',
						'<directoryBrowse enabled="false" />',
						'</system.webServer>',
						'</configuration>',
					)
				)
			);
		}
		public static function create_index( $path ) {
			return self::create_file( $path, 'Silence is golden' );
		}
		function mce_buttons( $buttons ) {
			if( !in_array( 'fontsizeselect', $buttons ) ) {
			    array_unshift( $buttons, 'fontsizeselect' );
			}
			if( !in_array( 'fontselect', $buttons ) ) {
				array_unshift( $buttons, 'fontselect' );
			}
			return $buttons;
		}
	}
	new wptableeditor_init();
}

if ( ! class_exists( 'wptableeditor_table' ) ) {
	class wptableeditor_table {
		public static function get_var($table_id, $column){
			global $wpdb;
			$query = "SELECT `{$column}` FROM ".WPTABLEEDITOR_TABLE." WHERE table_id = %d";
			$output = $wpdb->get_var($wpdb->prepare($query, $table_id));
			return $output;
		}
		public static function get_row($table_id){
			global $wpdb;
			$query = "SELECT * FROM ".WPTABLEEDITOR_TABLE." WHERE table_id = %d";
			$output = $wpdb->get_row($wpdb->prepare($query, $table_id), ARRAY_A);
			return $output;
		}
		public static function name($table_id){
			global $wpdb;
			$query = "SELECT table_name FROM ".WPTABLEEDITOR_TABLE." WHERE table_id = %d";
			$output = $wpdb->get_var($wpdb->prepare($query, $table_id));
			return $output;
		}
		public static function roles($table_id){
			global $wpdb;
			$query = "SELECT table_restrictionrole FROM ".WPTABLEEDITOR_TABLE." WHERE table_id = %d";
			$output = 'administrator,';
			$output .= $wpdb->get_var($wpdb->prepare($query, $table_id));
			return $output;
		}
		public static function login($table_id){
			global $wpdb;
			if(!in_array('table_restriction', self::column())){
				return 'no';
			}
			$query = "SELECT table_restriction FROM ".WPTABLEEDITOR_TABLE." WHERE table_id = %d";
			$output = $wpdb->get_var($wpdb->prepare($query, $table_id));
			return $output;
		}
		public static function type($table_id){
			global $wpdb;
			$query = "SELECT table_type FROM ".WPTABLEEDITOR_TABLE." WHERE table_id = %d";
			$output = $wpdb->get_var($wpdb->prepare($query, $table_id));
			return $output;
		}
		public static function all_type(){
			global $wpdb;
			$query = "SELECT table_type FROM ".WPTABLEEDITOR_TABLE;
			$output = $wpdb->get_results($query);
			$select = '';
			if(!empty($output)){
				foreach($output as $column){
					$types[] = $column->table_type;
				}
			}
			if(isset($types)){
				foreach(array_unique($types) as $type){
					$select .= "<option value=$type >$type</option>";
				}
			}
			return $select;
		}
		public static function limit($table_id){
			global $wpdb;
			$query = "SELECT table_limit FROM ".WPTABLEEDITOR_TABLE." WHERE table_id = %d";
			$output = $wpdb->get_var($wpdb->prepare($query, $table_id));
			return $output;
		}
		public static function category($table_id){
			global $wpdb;
			$output = array();
			$query = "SELECT table_category FROM ".WPTABLEEDITOR_TABLE." WHERE table_id = %d";
			$result = $wpdb->get_var($wpdb->prepare($query, $table_id));
			if(empty($result)){
				return $output;
			}else{
				$output = explode(',', $result);
			}
			if(!empty($output)){
				$outputs = array();
				foreach($output as $id){
					if(is_numeric($id)){
						$outputs[] = $id;
					}
				}
				return $outputs;
			}
			return $output;
		}
		public static function status($table_id){
			global $wpdb;
			$query = "SELECT table_status FROM ".WPTABLEEDITOR_TABLE." WHERE table_id = %d";
			$result = $wpdb->get_var($wpdb->prepare($query, $table_id));
			if($result === 'active'){
				return true;
			}else{
				return false;
			}
		}
		public static function column(){
			global $wpdb;
			$query = "SELECT * FROM information_schema.columns WHERE table_schema = %s AND table_name = %s";
			$result = $wpdb->get_results($wpdb->prepare($query, DB_NAME, WPTABLEEDITOR_TABLE));
			$column_name = array();
			foreach($result as $row){
				if(isset($row->COLUMN_NAME) && !in_array($row->COLUMN_NAME, array('table_id', 'table_status', 'table_author', 'table_restrictionrole'))){
					$column_name[$row->COLUMN_NAME] = $row->COLUMN_NAME;
				}
			}
			$array = array(
				'table_keytable',
				'table_select',
				'table_fixedheader',
				'table_fixedfooter',
				'table_searchbuilder',
				'table_searchpanes',
				'table_visibility',
				'table_statesave',
				'table_restriction',
				'table_editor',
				'table_filter',
				'table_fixedleft',
				'table_fixedright',
				'table_group',
				'table_createdrow',
				'table_createdbutton',
				'table_createstate',
				'table_predefinedstates',
				'table_staterestore',
				'table_reload',
				'table_prefilter',
				'table_jsonsave',
				'table_autosave',
				'table_checkbox'
			);
			if(!class_exists('wptableeditor_license') || !wptableeditor_license::check_license()){
				foreach($array as $value){
				    unset($column_name[$value]);
				}
			}
			return array_values($column_name);
		}
		public static function group($table_id){
			$sort = wptableeditor_table::get_var($table_id, 'table_sortingtype');
			$order = (int) wptableeditor_table::get_var($table_id, 'table_group');
			if($order > wptableeditor_column::align($table_id)['count']){
				$order = 0;
			}
			$output = '['. $order. ', "' . $sort .'"]';
			return $output;
		}
		public static function update($table_id, $column, $value){
			global $wpdb;
			$data = array(
				$column => $value
			);
			$wpdb->update(WPTABLEEDITOR_TABLE, $data, array('table_id' => $table_id));
		}
		public static function tables(){
			global $wpdb;
			$query = "SELECT * FROM information_schema.columns WHERE table_schema = %s AND table_name = %s";
			$result = $wpdb->get_results($wpdb->prepare($query, DB_NAME, WPTABLEEDITOR_TABLE));
			$column_name = array();
			foreach($result as $row){
				if(isset($row->COLUMN_NAME) && !in_array($row->COLUMN_NAME, array('table_id'))){
					$column_name[] = $row->COLUMN_NAME;
				}
			}
			return $column_name;
		}
		public static function localize(){
			$license = false;
			if(wptableeditor_load::license() === true){
				$license = true;
			}
			$tables = self::tables();
			$columns = $tables;
			unset($columns['table_status'], $columns['table_author'], $columns['table_restrictionrole']);
			$colors = array(
				'headerbackgroundcolor',
				'headerfontcolor',
				'headerlinkcolor',
				'headersortingcolor',
				'evenbackgroundcolor',
				'oddbackgroundcolor',
				'evenfontcolor',
				'oddfontcolor',
				'evenlinkcolor',
				'oddlinkcolor',
				'buttonbackgroundcolor',
				'buttonfontcolor',
			);
			$localize = array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'license' => $license,
				'tables' => wp_json_encode($tables),
				'columns' => wp_json_encode($columns),
				'colors' => wp_json_encode($colors),
				'xsnonce' => wp_create_nonce(WPTABLEEDITOR_TABLE) 
			);
			return $localize;
		}
	}
	new wptableeditor_table();
}

if ( ! class_exists( 'wptableeditor_column' ) ) {
	class wptableeditor_column {
		public static function update($table_id){
			global $wpdb;
			$query = "SELECT id FROM ".WPTABLEEDITOR_COLUMN." WHERE table_id = %d ORDER BY column_order ASC";
			$result = $wpdb->get_results($wpdb->prepare($query, $table_id));
			$output = array();
			foreach($result as $row){
				$output[] = $row->id;
			}
			for($count = 0;  $count < count($output); $count++){
				$data = array(
					'column_order'	=>	$count + 1
				);
				$wpdb->update(WPTABLEEDITOR_COLUMN, $data, array('id' => $output[$count]));
			}
		}
		public static function updates($table_id){
			global $wpdb;
			$query = "SELECT id FROM ".WPTABLEEDITOR_COLUMN." WHERE table_id = %d ORDER BY id ASC";
			$result = $wpdb->get_results($wpdb->prepare($query, $table_id));
			$output1 = wptableeditor_row::column_2($table_id);
			$output2 = array();
			foreach($result as $row){
				$output2[] = $row->id;
			}
			foreach ($output2 as $key => $value) {
				if(isset($output1[$key])){
					$data = array(
						'column_name'	=>	$output1[$key]
					);
				    $wpdb->update(WPTABLEEDITOR_COLUMN, $data, array('id' => $value));
			    }
			}
		}
		public static function order_id($table_id){
			global $wpdb;
			$query = "SELECT id, column_order FROM ".WPTABLEEDITOR_COLUMN." WHERE table_id = %d";
			$result = $wpdb->get_results($wpdb->prepare($query, $table_id));
			$output = array();
			foreach($result as $row){
				$output[$row->column_order] = $row->id;
			}
			return $output;
		}
		public static function id_name($table_id){
			global $wpdb;
			$query = "SELECT id, column_name FROM ".WPTABLEEDITOR_COLUMN." WHERE table_id = %d";
			$result = $wpdb->get_results($wpdb->prepare($query, $table_id));
			$output = array();
			if(!empty($result)){
				foreach($result as $row){
					$output[$row->id] = $row->column_name;
				}
			}
			return $output;
		}
		public static function order_name($table_id){
			global $wpdb;
			$query = "SELECT column_order, column_name FROM ".WPTABLEEDITOR_COLUMN." WHERE table_id = %d";
			$result = $wpdb->get_results($wpdb->prepare($query, $table_id));
			$output = array();
			foreach($result as $row){
				$output[$row->column_order] = $row->column_name;
			}
			return $output;
		}
		public static function status($table_id, $id){
			global $wpdb;
			$query = "SELECT column_status FROM ".WPTABLEEDITOR_COLUMN." WHERE table_id = %d AND id = %d";
			$result = $wpdb->get_var($wpdb->prepare($query, $table_id, $id));
			if($result === 'active'){
				return true;
			}else{
				return false;
			}
		}
		public static function name($table_id){
			global $wpdb;
			$query = "SELECT column_name, column_names FROM ".WPTABLEEDITOR_COLUMN." WHERE table_id = %d ORDER BY column_order ASC";
			$result = $wpdb->get_results($wpdb->prepare($query, $table_id));
			$output = array();
			if(!empty($result)){
				foreach($result as $row){
					$output[$row->column_name] = $row->column_names;
				}
			}
			return $output;
		}
		public static function custom($table_id, $type){
			global $wpdb;
			if(in_array($type, array('post', 'page'))){
				$table_types = wptableeditor_post::type($type);
			}elseif($type === 'product'){
				$table_types = wptableeditor_woocommerce::product_types();
			}elseif($type === 'order'){
				$table_types = wptableeditor_woocommerce::order_types();
			}
			$query = "SELECT column_name, column_names FROM ".WPTABLEEDITOR_COLUMN." WHERE table_id = %d ORDER BY column_order ASC";
			$result = $wpdb->get_results($wpdb->prepare($query, $table_id));
			$output = array();
			if(!empty($result)){
				foreach($result as $row){
					if(isset($table_types[$row->column_name]) && $table_types[$row->column_name] === 'custom'){
						$output['column_custom'] = $row->column_names;
					}
				}
			}
			return $output;
		}
		public static function rowCount($table_id, $status = null){
			global $wpdb;
			$query = "SELECT id FROM ".WPTABLEEDITOR_COLUMN." WHERE table_id = %d";
			if(!empty($status)){
				$query .= " AND column_status = 'active'";
			}
			$wpdb->get_results($wpdb->prepare($query, $table_id));
			$rowCount = $wpdb->num_rows;
			return $rowCount;
		}
		public static function update_columns($table_id){
			global $wpdb;
			$query = "SELECT id FROM ".WPTABLEEDITOR_COLUMN." WHERE table_id = %d AND column_status = 'active'";
			$wpdb->get_results($wpdb->prepare($query, $table_id));
			$rowCount = $wpdb->num_rows;
			$data = array(
				'table_columns'	=>	$rowCount
			);
			$wpdb->update(WPTABLEEDITOR_TABLE, $data, array('table_id' => $table_id));
		}
		public static function align($table_id){
			global $wpdb;
			$query = "SELECT column_order FROM ".WPTABLEEDITOR_COLUMN." WHERE table_id = %d AND column_status = %s";
			$result = $wpdb->get_results($wpdb->prepare($query, $table_id, 'active'));
			$output = array('column_id', 'column_order');
			$outputs = array();
			foreach($result as $row){
				$output[] = $row->column_order;
			}
			$output1 = array(0, count($output));
			$output2 = array(0, count($output), count($output) + 1, count($output) + 2);
			$output3 = array(0, count($output), count($output) + 1);
			$outputs['status'] = wp_json_encode($output1);
			$outputs['all'] = wp_json_encode($output2);
			$outputs['edit'] = wp_json_encode($output3);
			$outputs['count'] = count($output) - 2;
			return $outputs;
		}
		public static function column(){
			global $wpdb;
			$query = "SELECT * FROM information_schema.columns WHERE table_schema = %s AND table_name = %s";
			$result = $wpdb->get_results($wpdb->prepare($query, DB_NAME, WPTABLEEDITOR_COLUMN));
			$column_name = array();
			if(!empty($result)){
				foreach($result as $row){
					if(isset($row->COLUMN_NAME) && !in_array($row->COLUMN_NAME, array('id', 'table_id', 'column_status'))){
						$column_name[$row->COLUMN_NAME] = $row->COLUMN_NAME;
					}
				}
			}
			$array = array(
				'column_index',
				'column_childrows',
				'column_restriction',
				'column_search',
				'column_priority',
				'column_optionlimit',
				'column_characterlimit',
				'column_restrictiontitle',
				'column_render',
				'column_createdcell',
			);
			if(!class_exists('wptableeditor_license') || !wptableeditor_license::check_license()){
				foreach($array as $value){
				    unset($column_name[$value]);
				}
			}
			return array_values($column_name);
		}
		public static function names($table_id){
			global $wpdb;
			$query = "SELECT column_name, column_names FROM ".WPTABLEEDITOR_COLUMN." WHERE table_id = %d";
			$result = $wpdb->get_results($wpdb->prepare($query, $table_id));
			$select = '';
			foreach($result as $column){
				$select .= "<option value={$column->column_name} >{$column->column_names}</option>";
			}
			return $select;
		}
		public static function import($table_id){
			$output = array();
			$output1 = array();
			$output2 = array();
			$output3 = array();
			$output4 = array();
			foreach(self::name($table_id) as $name => $names){
				$output1[] = $name;
				$output2[] = $name.':'.$name;
				$output3[] = $name;
				$output4[] = "%s";
			}
			$output['name'] = $output1;
			$output['post'] = implode(",",$output2);
			$output['query'] = implode(",",$output3);
			$output['placeholders'] = implode(",",$output4);
			return $output;
		}
		public static function import_replace($total){
			$output = array();
			for($x = 0; $x <= $total - 1; $x++){
				$output[$x] = $x;
			}
			return $output;
		}
		public static function import_post($total){
			$output = 'column_0:column_0';
			for($x = 1; $x <= $total - 1; $x++){
				$output .= ', column_'.$x.':column_'.$x;
			}
			return $output;
		}
		public static function all_column($table){
			global $wpdb;
			$output = array();
			$columns = array();
			$rows = array();
			$results = $wpdb->get_results( "DESC `{$table}`" );
			foreach ( $results as $column ) {
				$columns[] = $column->Field;
			}
			$query = "SELECT * FROM $table";
			$result = $wpdb->get_results($query);
			foreach ( $result as $row ) {
				$rows[] = array_values((array)$row);
			}
			$output['column'] = $columns;
			$output['row'] = (array)$rows;
			return $output;
		}
		public static function roles($table_id, $column_id){
			global $wpdb;
			$query = "SELECT column_restrictionrole FROM ".WPTABLEEDITOR_COLUMN." WHERE table_id = %d AND id = %d";
			$output = 'administrator,';
			$output .= $wpdb->get_var($wpdb->prepare($query, $table_id, $column_id));
			return $output;
		}
		public static function login($table_id, $column_id){
			global $wpdb;
			if(!in_array('column_restriction', self::column())){
				return 'no';
			}
			$query = "SELECT column_restriction FROM ".WPTABLEEDITOR_COLUMN." WHERE table_id = %d AND id = %d";
			$output = $wpdb->get_var($wpdb->prepare($query, $table_id, $column_id));
			return $output;
		}
		public static function check_roles($table_id, $column_id){
			global $wp_roles;
			$permission = self::roles($table_id, $column_id);
			if(!empty($permission)){
				$all_roles = explode(',', $permission);
				if(is_user_logged_in()){
					$user = wp_get_current_user();
					$roles = $user->roles;
					foreach($roles as $role){
						if(in_array($role, $all_roles)){
							return true;
						}
					}
				}
			}
			return false;
		}
		public static function all_columns($table, $table_id, $xs_type){
			$order_name = wptableeditor_column::order_name($table_id);
			$name = wptableeditor_column::name($table_id);
			$order_id = wptableeditor_column::order_id($table_id);
			$category_id = wptableeditor_table::category($table_id);
			$output = array();
			$data = array();
			$column = array();
			$column_total = wptableeditor_column::rowCount($table_id);
			$limit = wptableeditor_table::limit($table_id);
			if(in_array($xs_type, array('post', 'page'))){
				$array = wptableeditor_post::post($xs_type, $category_id, $limit);
			}elseif($xs_type === 'product'){
				$array = wptableeditor_woocommerce::woocommerce($category_id, $limit);
			}elseif($xs_type === 'order'){
				$array = wptableeditor_woocommerce::order($category_id, $limit);
			}
			foreach($array as $row){
				$row = array_values($row);
				if(wptableeditor_row::status($table, $row[0])){
					$sub_array = array();
					$column = array();
					for ($x = 1; $x <= $column_total; $x++){
						if(wptableeditor_column::status($table_id, $order_id[$x])){
							$value = str_replace('column_', '', $order_name[$x]);
							if(isset($row[$value])){
								$sub_array[] = $row[$value];
							}else{
								$sub_array[] = '';
							}
							$column[] = $name[$order_name[$x]];
						}
					}
					$data[] = $sub_array;
				}
			}
			$output['column'] = array_values($column);
			$output['row'] = (array)$data;
			return $output;
		}
		public static function data_export($table_id, $xs_type){
			global $wpdb;
			$table = WPTABLEEDITOR_PREFIX.$table_id;
			$order_name = wptableeditor_column::order_name($table_id);
			$name = wptableeditor_column::name($table_id);
			$order_id = wptableeditor_column::order_id($table_id);
			$category_id = wptableeditor_table::category($table_id);
			$data = 'column_1';
			$column_total = wptableeditor_column::rowCount($table_id);
			for ($x = 2; $x <= $column_total; $x++){
				$data .= ',column_'.$x;
			}
			$data .= "\n";
			$limit = wptableeditor_table::limit($table_id);
			if(in_array($xs_type, array('post', 'page', 'product'))){
				if(in_array($xs_type, array('post', 'page'))){
					$array = wptableeditor_post::post($xs_type, $category_id, $limit);
				}elseif($xs_type === 'product'){
					$array = wptableeditor_woocommerce::woocommerce($category_id, $limit);
				}
				foreach($array as $row){
					$row = array_values($row);
					if(wptableeditor_row::status($table, $row[0])){
						$sub_array = array();
						for ($x = 1; $x <= $column_total; $x++){
							if(wptableeditor_column::status($table_id, $order_id[$x])){
								$value = str_replace('column_', '', $order_name[$x]);
								if(isset($row[$value])){
									$sub_array[] = self::csv_wrap( $row[$value], ',' );
								}else{
									$sub_array[] = '';
								}
							}
						}
						$data .= implode( ',', $sub_array )."\n";
					}
				}
			}elseif($xs_type === 'default'){
				$query = "SELECT * FROM `{$table}` ORDER BY column_id ASC";
				if($limit >= 0){
					$query .= " LIMIT {$limit}";
				}
				$result = $wpdb->get_results($query);
				foreach($result as $row){
					$row = (array)$row;
					$sub_array = array();
					for ($x = 1; $x <= count($row) - 2; $x++){
						if(isset($order_name[$x])){
							$column_name = $order_name[$x];
							$value = str_replace('”', '"', $row[$column_name]);
							$sub_array[] = self::csv_wrap( $value, ',' );
						}
					}
					$data .= implode( ',', $sub_array )."\n";
				}
			}
			return $data;
		}
		public static function csv_wrap( $string, $delimiter ) {
			$delimiter = preg_quote( $delimiter, '#' );
			if ( 1 === preg_match( '#' . $delimiter . '|"|\n|\r#i', $string ) || ' ' === substr( $string, 0, 1 ) || ' ' === substr( $string, -1 ) ) {
				$string = str_replace( '"', '""', $string );
				$string = '"' . $string . '"';
			}
			return $string;
		}
		public static function type($table_id, $column_name){
			global $wpdb;
			$query = "SELECT column_type FROM ".WPTABLEEDITOR_COLUMN." WHERE table_id = %d AND column_name = %s";
			$output = $wpdb->get_var($wpdb->prepare($query, $table_id, $column_name));
			return $output;
		}
		public static function column_filter($table_id, $column_name){
			global $wpdb;
			$query = "SELECT column_filter FROM ".WPTABLEEDITOR_COLUMN." WHERE table_id = %d AND column_name = %s";
			$output = $wpdb->get_var($wpdb->prepare($query, $table_id, $column_name));
			return $output;
		}
        public static function custom_filter($table_id, $column_id){
            global $wpdb;
            $output = array();
            $table_type = wptableeditor_table::type($table_id);
            $column_name = self::id_name($table_id)[$column_id];
            if(in_array($table_type, array('post', 'page'))){
                $table_types = wptableeditor_post::type($table_type);
                if($table_type === 'post'){
                	if($table_types[$column_name] === 'post_tags'){
	                    $tags = get_tags(array('hide_empty' => true));
	                    foreach ($tags as $tag) {
	                      $output[] = $tag->name;
	                    }
                	}elseif($table_types[$column_name] === 'category'){
                		$args = get_categories(array('hide_empty' => true));
	                    foreach ($args as $arg) {
	                      $output[] = $arg->name;
	                    }
                	}
                    return array_unique($output);
                }else{
                    return array('');
                }
            }elseif($table_type === 'product'){
                $table_types = wptableeditor_woocommerce::product_types();
                if($table_types[$column_name] === 'tags'){
                    $terms = get_terms( 'product_tag' );
                    if ( ! empty( $terms ) && ! is_wp_error( $terms ) ){
                        foreach ( $terms as $term ) {
                            $output[] = $term->name;
                        }
                    }
                    return array_unique($output);
                }elseif($table_types[$column_name] === 'categories'){
                	return wptableeditor_woocommerce::category();
                }else{
                    return array('');
                }
            }elseif($table_type === 'order'){
                return array('');
            }elseif($table_type === 'default'){
				$datatable = (int) wptableeditor_load::datatable($table_id);
				if($datatable > 0){
					$table_ids = $datatable;
				}else{
					$table_ids = $table_id;
				}
                $table = WPTABLEEDITOR_PREFIX.$table_ids;
                if(empty($column_name)){
                	$column_name = 'column_1';
                }
                $query = "SELECT $column_name FROM `{$table}`";
                $result = $wpdb->get_results($query);
                if(!empty($result)){
                    $i = 0;
                    foreach($result as $column){
                        $i = $i + 1;
                        if($i <= 50 && isset($column->$column_name)){
                            $output[] = strip_tags($column->$column_name);
                        }
                    }
                }
            }
            return array_unique($output);
        }
		public static function localize($table_id){
			$license = false;
			if(wptableeditor_load::license() === true){
				$license = true;
			}
			$columns = self::column();
			$colors = array(
				'backgroundcolor',
				'fontcolor',
			);
			$localize = array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'license' => $license,
				'table_type' => wptableeditor_table::type($table_id),
				'table_id' => $table_id,
				'columns' => wp_json_encode($columns),
				'colors' => wp_json_encode($colors),
				'xsnonce' => wp_create_nonce('xs-table'.$table_id) 
			);
			return $localize;
		}
	}
	new wptableeditor_column();
}

if ( ! class_exists( 'wptableeditor_row' ) ) {
	class wptableeditor_row {
		public static function update($table, $column_id){
			global $wpdb;
			$query = "SELECT * FROM `{$table}` WHERE column_id = %d";
			$result = $wpdb->get_results($wpdb->prepare($query, $column_id));
			$data = array();
			if(!empty($result)){
				foreach($result as $row){
					foreach((array)$row as $keys => $rows){
						//if(str_replace('column_', '', $keys) != $keys && $keys != 'column_id' && $keys != 'column_status'){
						if(str_replace('column_', '', $keys) != $keys && $keys != 'column_id'){
							$data[$keys] = $rows;
						}
					}
				}
			}
			return $data;
		}
		public static function column_name($table){
			global $wpdb;
			$database = DB_NAME;
			$query = "SELECT * FROM information_schema.columns WHERE table_schema = %s AND table_name = %s";
			$result = $wpdb->get_results($wpdb->prepare($query, DB_NAME, $table));
			$column_name = array();
			foreach($result as $row){
				if(isset($row->COLUMN_NAME) && !in_array($row->COLUMN_NAME, array('column_id', 'column_status', 'column_order', 'column_custom'))){
					$column_name[] = str_replace('column_', '', $row->COLUMN_NAME);
				}
			}
			if(empty($column_name)){
				return 0;
			}
			return max($column_name);
		}
		public static function check_id($table, $column_id){
			global $wpdb;
			$query = "SELECT column_id FROM `{$table}` WHERE column_id = %d";
			$result = $wpdb->get_var($wpdb->prepare($query, $column_id));
			if(!empty($result)){
				return true;
			}else{
				return false;
			}
		}
		public static function status($table, $column_id){
			global $wpdb;
			$query = "SELECT column_status FROM `{$table}` WHERE column_id = %d";
			$result = $wpdb->get_var($wpdb->prepare($query, $column_id));
			if(empty($result) || $result === 'active'){
				return true;
			}else{
				return false;
			}
		}
		public static function rowCount($table_id, $status = null){
			global $wpdb;
			$rowCount = 0;
			$table = WPTABLEEDITOR_PREFIX.$table_id;
			$query = "SELECT column_id FROM `{$table}`";
			if(!empty($status)){
				$query .= " WHERE column_status = 'active'";
			}
			$result = $wpdb->get_results($query);
			if($result){
				$rowCount = $wpdb->num_rows;
			}
			return $rowCount;
		}
		public static function column($table_id){
			global $wpdb;
			$database = DB_NAME;
			$table = WPTABLEEDITOR_PREFIX.$table_id;
			$query = "SELECT * FROM information_schema.columns WHERE table_schema = %s AND table_name = %s";
			$result = $wpdb->get_results($wpdb->prepare($query, DB_NAME, $table));
			$column_name = array();
			foreach($result as $row){
				//if(!in_array($row->COLUMN_NAME, array('column_id', 'column_status', 'column_order', 'column_custom'))){
				if(isset($row->COLUMN_NAME) && !in_array($row->COLUMN_NAME, array('column_id', 'column_order', 'column_custom'))){
					$column_name[] = $row->COLUMN_NAME;
				}
			}
			return $column_name;
		}
		public static function column_2($table_id){
			global $wpdb;
			$database = DB_NAME;
			$table = WPTABLEEDITOR_PREFIX.$table_id;
			$query = "SELECT * FROM information_schema.columns WHERE table_schema = %s AND table_name = %s";
			$result = $wpdb->get_results($wpdb->prepare($query, DB_NAME, $table));
			$column_name = array();
			foreach($result as $row){
				if(isset($row->COLUMN_NAME) && !in_array($row->COLUMN_NAME, array('column_id', 'column_status', 'column_order', 'column_custom'))){
					$column_name[] = $row->COLUMN_NAME;
				}
			}
			return $column_name;
		}
		public static function columns($table_id){
			global $wpdb;
			$query = "SELECT column_name, column_names FROM ".WPTABLEEDITOR_COLUMN." WHERE table_id = %d";
			$result = $wpdb->get_results($wpdb->prepare($query, $table_id), ARRAY_A);
			$output = '<option value="">Select</option>';
			foreach($result as $row){
				$output .= '<option value="'.$row['column_name'].'" >'.$row['column_names'].'</option>';
			}
			return $output;
		}
		public static function localize($table_id){
			global $wpdb;
			include WPTABLEEDITOR_INCLUDES. 'init.php';
			$html_id = "wptableeditor_$table_id";
			$dataSet = '';
			if($serverSide === 'false' && $table_type === 'json' && empty($column_index) && $table_filter !== 'yes'){
				$dataSet = wptableeditor_load::put_action($table_id, false, true);
			}
			if(isset($table_responsive) && $table_responsive === 'collapse'){
				$responsive = "true";
				$scrollX = "false";
			}else{
				$responsive = "false";
				$scrollX = "true";
			}
			if(isset($table_fixedheader) && $table_fixedheader === 'yes'){
				$header = "true";
			}else{
				$header = "false";
			}
			if(isset($table_fixedfooter) && $table_fixedfooter === 'yes'){
				$footer = "true";
			}else{
				$footer = "false";
			}
			if(isset($table_select) && $table_select === 'yes'){
				$select = "true";
			}else{
				$select = "false";
			}
			if(isset($table_type) && $table_type === 'default'){
				$columns = '['.$column_width.',null,null,null,null]';
				$column_noVis = '{"targets":[0,-1,-2,-3], "className": "noVis text-center"},';
				$priority = '{"targets":[-4], "visible": false, "orderable":true},';
				$select_status = '[-3]';
				$column_all = $column_align['all'];
			}elseif(in_array($table_type, array('product', 'order', 'post', 'page'))){
				$columns = '['.$column_width.',null,null,null]';
				$column_noVis = '{"targets":[0,-1,-2], "className": "noVis text-center"},';
				$priority = '{"targets":[-3], "visible": false, "orderable":true},';
				$serverSide = "false";
				$select_status = '[-2]';
				$column_all = $column_align['edit'];
				if($table_jsonsave === 'yes'){
					if(!empty($table_autosave)){
						if(time() >= ($table_jsonsavedate + $table_autosave * 60)){
							$wpdb->update(WPTABLEEDITOR_TABLE, array('table_jsonnametemp' => $table_jsonname), array('table_id' => $table_id));
							wptableeditor_load::put_action($table_id, false);
						}
					}elseif(time() >= ($table_jsonsavedate + 120 * 60)){
						$wpdb->update(WPTABLEEDITOR_TABLE, array('table_jsonnametemp' => $table_jsonname), array('table_id' => $table_id));
						wptableeditor_load::put_action($table_id, false);
					}
					if(file_exists(WPTABLEEDITOR_DATA_PATH.$table_jsonname)){
						if(file_exists(WPTABLEEDITOR_DATA_PATH.$table_jsonnametemp) && !empty($table_jsonnametemp) && $table_jsonname !== $table_jsonnametemp){
							@wp_delete_file(WPTABLEEDITOR_DATA_PATH.$table_jsonnametemp);
						}
						$columns = '['.$column_width.',null]';
						$column_noVis = '{"targets":[0], "className": "noVis text-center"},';
						$priority = '{"targets":[-1], "visible": false, "orderable":true},';
						$select_status = '[]';
						$column_all = $column_align['url'];
						$table_type = 'json';
						$table_datasrc = 'data';
						$table_url = WPTABLEEDITOR_DATA_URL.$table_jsonname;
					}elseif(defined('WPTABLEEDITOR_PRO')){
						wptableeditor_load::put_action($table_id);
					}
				}
			}elseif(in_array($table_type, array('json', 'sheet'))){
				$columns = '['.$column_point.']';
				$column_noVis = '{"targets":[0], "className": "noVis text-center"},';
				$priority = '{"targets":[], "visible": false, "orderable":true},';
				$select_status = '[]';
				$column_all = $column_align['url'];
				if($table_jsonsave === 'yes'){
					if(!empty($table_autosave)){
						if(time() >= ($table_jsonsavedate + $table_autosave * 60)){
							$wpdb->update(WPTABLEEDITOR_TABLE, array('table_jsonnametemp' => $table_jsonname), array('table_id' => $table_id));
							wptableeditor_load::put_action($table_id, false);
						}
					}
					if(file_exists(WPTABLEEDITOR_DATA_PATH.$table_jsonname)){
						$table_type = 'json';
						$table_datasrc = 'data';
						$table_url = WPTABLEEDITOR_DATA_URL.$table_jsonname;
					}elseif(defined('WPTABLEEDITOR_PRO')){
						wptableeditor_load::put_action($table_id);
					}
				}
			}
			if(isset($table_group) && (int) $table_group > 0 ){
				$rowGroup = '{"dataSrc": '.$table_groups.'}';
				$group = wptableeditor_table::group($table_id);
				$sortingtype = $group;
			}else{
				$rowGroup = "false";
				$group = "false";
				$sortingtype = $table_sortingtype;
			}
			if(!isset($table_dom) || empty($table_dom)){
				$table_dom = 'lcBfrtip';
			}
			$table_doms = str_replace(["l","c"], '', $table_dom);
			$select_default = '';
			$filter_default = array();
			if(isset($column_default) && is_array(json_decode($column_default))){
				foreach(json_decode($column_default) as $column){
					$select_default .= '<"#wptableeditor_'.$table_id.'_selects_'.$column.'.dataTables_length">';
					if(!empty($column_customfilter[$column])){
						$filter_default[$column] = $column_customfilter[$column];
					}else{
						$filter_default[$column] = '';
					}
				}
			}
			$filter_footer = array();
			if(isset($column_footer) && is_array(json_decode($column_footer))){
				foreach(json_decode($column_footer) as $column){
					if(!empty($column_customfilter[$column])){
						$filter_footer[$column] = $column_customfilter[$column];
					}else{
						$filter_footer[$column] = '';
					}
				}
			}
			if(strpos($table_dom, "lc" ) !== false){
				$dom = 'l<"#'.$html_id.'_select.dataTables_length">'.$select_default.$table_doms;
			}elseif(strpos($table_dom, "l") !== false){
				$dom = 'l'.$table_doms;
			}elseif(strpos($table_dom, "c") !== false){
				$dom = '<"#'.$html_id.'_select.dataTables_length">'.$select_default.$table_doms;
			}else{
				$dom = $table_doms;
			}
			if(!isset($table_datasrc) || empty($table_datasrc)){
				$table_datasrc = 'body';
			}
			$localize = array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'html_id' => $html_id,
				'xsnonce' => wp_create_nonce('xs-table'.$table_id),
				'table_id' => $table_id,
				'table_type' => $table_type,
				'table_url' => $table_url,
				'table_datasrc' => $table_datasrc,
				'spreadsheets' => $spreadsheets,
				'table_length' => $table_length,
				'table_prefilter' => trim($table_prefilter),
				'table_pagination' => '"'.$table_pagination.'"',
				'pagination_simple' => '"simple"',
				'table_fixedleft' => $table_fixedleft,
				'table_fixedright' => $table_fixedright,
				'table_sortingtype' => $sortingtype,
				'table_group' => '"'.$table_group.'"',
				'table_visibility' => $table_visibility,
				'table_createdbutton' => $table_createdbutton,
				'table_createstate' => $table_createstate,
				'table_predefinedstates' => $table_predefinedstates,
				'table_staterestore' => $table_staterestore,
				'stateSave' => $stateSave,
				'table_keytable' => $table_keytable,
				'table_checkbox' => $table_checkbox,
				'table_select' => $table_select,
				'table_searchbuilder' => $table_searchbuilder,
				'table_searchpanes' => $table_searchpanes,
				'table_button' => $table_button,
				'table_filter' => $table_filter,
				'table_footer' => $table_footer,
				'column_default' => $column_default,
				'column_filters' => $column_filters,
				'column_hiddens' => $column_hiddens,
				'column_none' => $column_none,
				'column_orderable' => $column_orderable,
				'column_searchable' => $column_searchable,
				'column_search' => wp_json_encode($column_search),
				'column_order' => $column_order,
				'column_orders' => $column_orders,
				'column_width' => $column_width,
				'column_nowrap' => $column_nowrap,
				'column_show' => $column_show,
				'column_all' => $column_all,
				'column_total' => wp_json_encode($column_total),
				'column_priority' => wp_json_encode($column_priority),
				'column_optionlimit' => $column_optionlimit,
				'column_characterlimit' => $column_characterlimit,
				'column_render' => $column_render,
				'column_createdcell' => $column_createdcell,
				'column_noVis' => $column_noVis,
				'pagelength' => $pagelength,
				'responsive' => $responsive,
				'table_responsive' => $table_responsive,
				'table_responsivetype' => $table_responsivetype,
				'scrollX' => $scrollX,
				'header' => $header,
				'footer' => $footer,
				'select' => $select,
				'dataSet' => $dataSet,
				'group' => $group,
				'columns' => $columns,
				'rowGroup' => $rowGroup,
				'priority' => $priority,
				'column_left' => $column_left,
				'column_center' => $column_center,
				'column_right' => $column_right,
				'names' => $names,
				'filter_default' => $filter_default,
				'filter_footer' => $filter_footer,
				'select_status' => $select_status,
				'serverSide' => $serverSide,
				'table_serverside' => $table_serverside,
				'column_index' => wp_json_encode($column_index),
				'dom' => $dom
			);
			return $localize;
		}
		public static function get_var($table_id, $column_id, $column_name){
			global $wpdb;
			$table = WPTABLEEDITOR_PREFIX.$table_id;
			$query = "SELECT `{$column_name}` FROM `{$table}` WHERE column_id = %d";
			$output = $wpdb->get_var($wpdb->prepare($query, $column_id));
			return $output;
		}
	}
	new wptableeditor_row();
}

if ( ! class_exists( 'wptableeditor_woocommerce' ) ) {
	class wptableeditor_woocommerce {
		public static function woocommerce($category_id = array(), $limit = 1000){
			$output = array();
			if(!function_exists('wc_get_products')){
				return $output;
			}
			if($limit === 0) return $output;
			$product_ids = self::product_id($category_id);
			$products = wc_get_products(array('status' => 'publish', 'limit' => $limit));
			foreach ($products as $product){ 
				$product_id = $product->get_id();
				if(in_array($product_id, $product_ids)){
					$output[] = array(
						'id' => $product_id,
						'title' => '<a href="'.get_permalink($product_id).'" target="_self">'.$product->get_title().'</a>',
						'image' => '<a href="'.get_permalink($product_id).'" target="_self"><img src="'.wp_get_attachment_image_url($product->get_image_id(), 'full').'" class="img-thumbnail" width="70" /></a>',
						'categories' => wc_get_product_category_list($product_id),
						'short_description' => $product->get_short_description(),
						'price' => $product->get_price_html(),
						'rating' => '<div class="star-rating">'.wc_get_rating_html($product->get_average_rating(), $product->get_rating_count()).'</div>',
						'buy' => do_shortcode('[add_to_cart id="'.$product_id.'" show_price="false" style = "margin-bottom: unset !important;display: inline-flex;"]'),
						'type' => $product->get_type(),
						'sku' => $product->get_sku(),
						'tags' => wc_get_product_tag_list($product_id, ', '),
						'stock_status' => $product->get_stock_status(),
						'stock_quantity' => $product->get_stock_quantity(),
						'custom' => ''
					);
				}
			}
			return $output;
		}
		public static function product_id($category_id){
			$product_id = array();
			if(empty($category_id)){
				$products = wc_get_products(array('status' => 'publish', 'limit' => -1));
				foreach ($products as $product){ 
					$product_id[] = $product->get_id();
				}
			}else{
				$all_ids = get_posts(array(
					'post_type' => 'product',
					'numberposts' => -1,
					'post_status' => 'publish',
					'fields' => 'ids',
					'tax_query' => array(
						array(
							'taxonomy' => 'product_cat',
							'field' => 'term_id',
							'terms' => $category_id,
							'operator' => 'IN',
						)
					),
				));
				foreach ( $all_ids as $id ) {
					$product_id[] = $id;
				}
			}
			return $product_id;
		}
		public static function category(){
			$category = array();
			$woocommerce_category_id = get_queried_object_id();
			$args = array(
				'parent' => $woocommerce_category_id
			);
			$terms = get_terms( 'product_cat', $args );
			if ( $terms ) {
				foreach ( $terms as $term ) {
					$category[] = $term->name;
				}
			}
			return $category;
		}
		public static function categories($table_id){
			$category_id = wptableeditor_table::category($table_id);
			$categories = '';
			$args = array(
				'taxonomy'     => 'product_cat',
				'orderby'      => 'name',
				'show_count'   => true,
				'pad_counts'   => false,
				'hierarchical' => true,
				'title_li'     => '',
				'hide_empty'   => true
			);
			if(!empty(get_categories( $args ))){
				foreach(get_categories( $args ) as $row){
					if($row->category_parent === 0) {
						$name = $row->name;
						if(empty($category_id)){
							$categories .= "<option value=$name >$name</option>";
						}elseif(in_array($row->term_id, $category_id)){
							$categories .= "<option value=$name >$name</option>";
						}
					}
				}
			}
			return $categories;
		}
		public static function all_categories(){
			$categories = '';
			$args = array(
				'taxonomy'     => 'product_cat',
				'orderby'      => 'name',
				'show_count'   => true,
				'pad_counts'   => false,
				'hierarchical' => true,
				'title_li'     => '',
				'hide_empty'   => true
			);
			if(!empty(get_categories( $args ))){
				foreach(get_categories( $args ) as $row){
					if($row->category_parent === 0) {
						$name = $row->name;
						$id = $row->term_id;
						$categories .= "<option value=$id >$name</option>";
					}
				}
			}
			return $categories;
		}
		public static function types(){
			$output = array(
				'id',
				'title',
				'image',
				'categories',
				'short_description',
				'price',
				'rating',
				'buy',
				'type',
				'sku',
				'tags',
				'stock_status',
				'stock_quantity',
				'custom'
			);
			return $output;
		}
		public static function product_type(){
			$output = '<option value="">Select</option>';
			$i = -1;
			foreach (self::types() as $value){ 
				$i++;
				$output .= '<option value="'.'column_'.$i.'" >'.$value.'</option>';
			}
			return $output;
		}
		public static function product_types(){
			$output = array();
			$i = -1;
			foreach (self::types() as $value){ 
				$i++;
				$output['column_'.$i] = $value;
			}
			return $output;
		}
		public static function download_products($product_id){
			$downloads = array();
			$user_id = get_current_user_id();
			$downloads = wc_get_customer_available_downloads($user_id);
			$output = '';
			if (!empty($downloads)) {
				foreach ($downloads as $download) {
					if($download['product_id'] === $product_id){
						$output = '<a class="button" href="'.$download['download_url'].'">Download</a>';
					}
				}
			}
			return $output;
		}
		public static function order($category_id = array(), $limit = 1000){
			$output = array();
			if(!function_exists('wc_get_products')){
				return $output;
			}
			if($limit === 0) return $output;
			$item_sales = get_posts(array(
				'numberposts' => $limit,
				'post_type'   => 'shop_order',
				'post_status' => array('wc-completed', 'wc-on-hold', 'wc-pending', 'wc-failed', 'wc-cancelled', 'wc-refunded'),
			));
			if($item_sales) {
				$product_ids = self::product_id($category_id);
				foreach( $item_sales as $sale ) {
					$order = wc_get_order( $sale->ID );
					$data = $order->get_data();
					foreach( $order->get_items() as $item ){
					   $product_id = $item->get_product_id();
					   $product_name = $item->get_name();
					}
					if(in_array($product_id, $product_ids)){
						if(isset($data['date_completed'])){
							$date_completed = $data['date_completed']->date('Y-m-d H:i:s');
						}else{
							$date_completed = '';
						}
						if(isset($data['date_paid'])){
							$date_paid = $data['date_paid']->date('Y-m-d H:i:s');
						}else{
							$date_paid = '';
						}
						$billing_phone = !empty($data['billing']['phone']) ? ' ('.$data['billing']['phone'].')' : '';
						$billing_first_name = !empty($data['billing']['first_name']) ? $data['billing']['first_name'] : '';
						$billing_last_name = !empty($data['billing']['last_name']) ? ' '.$data['billing']['last_name'] : '';
						$billing_address_1 = !empty($data['billing']['address_1']) ? ', '.$data['billing']['address_1'] : '';
						$billing_city = !empty($data['billing']['city']) ? ', '.$data['billing']['city'] : '';
						$billing_country = !empty($data['billing']['country']) ? ', '.$data['billing']['country'] : '';
						$shipping_phone = !empty($data['shipping']['phone']) ? ' ('.$data['shipping']['phone'].')' : '';
						$shipping_first_name = !empty($data['shipping']['first_name']) ? $data['shipping']['first_name'] : '';
						$shipping_last_name = !empty($data['shipping']['last_name']) ? ' '.$data['shipping']['last_name'] : '';
						$shipping_address_1 = !empty($data['shipping']['address_1']) ? ', '.$data['shipping']['address_1'] : '';
						$shipping_city = !empty($data['shipping']['city']) ? ', '.$data['shipping']['city'] : '';
						$shipping_country = !empty($data['shipping']['country']) ? ', '.$data['shipping']['country'] : '';
						$output[] = array(
							'id' => $data['id'],
							'order' => '<a href="'.admin_url( 'post.php' ).'?post='.$data['id'].'&action=edit" target="_blank">'.$data['id'].'</a>',
							'date_created' => $data['date_created']->date('Y-m-d'),
							'status' => $data['status'],
							'product_name' => '<a href="'.get_permalink($product_id).'" target="_self">'.$product_name.'</a>',
							'total' => $data['total'],
							'payment_method_title' => $data['payment_method_title'],
							'billing.email' => $data['billing']['email'],
							'billing' => $billing_first_name.$billing_last_name.$billing_phone.$billing_address_1.$billing_city.$billing_country,
							'shipping' => $shipping_first_name.$shipping_last_name.$shipping_phone.$shipping_address_1.$shipping_city.$shipping_country,
							'customer_note' => $data['customer_note'],
							'customer_id' => '<a href="'.get_admin_url().'user-edit.php?user_id='.$data['customer_id'].'" target="_blank">'.$data['customer_id'].'</a>',
							'currency' => $data['currency'],
							'discount_total' => $data['discount_total'],
							'discount_tax' => $data['discount_tax'],
							'shipping_total' => $data['shipping_total'],
							'shipping_tax' => $data['shipping_tax'],
							'cart_tax' => $data['cart_tax'],
							'total_tax' => $data['total_tax'],
							'date_modified' => $data['date_modified']->date('Y-m-d'),
							'billing.first_name' => $data['billing']['first_name'],
							'billing.last_name' => $data['billing']['last_name'],
							'billing.company' => $data['billing']['company'],
							'billing.address_1' => $data['billing']['address_1'],
							'billing.address_2' => $data['billing']['address_2'],
							'billing.city' => $data['billing']['city'],
							'billing.state' => $data['billing']['state'],
							'billing.postcode' => $data['billing']['postcode'],
							'billing.country' => $data['billing']['country'],
							'billing.phone' => $data['billing']['phone'],
							'shipping.first_name' => $data['shipping']['first_name'],
							'shipping.last_name' => $data['shipping']['last_name'],
							'shipping.company' => $data['shipping']['company'],
							'shipping.address_1' => $data['shipping']['address_1'],
							'shipping.address_2' => $data['shipping']['address_2'],
							'shipping.city' => $data['shipping']['city'],
							'shipping.state' => $data['shipping']['state'],
							'shipping.postcode' => $data['shipping']['postcode'],
							'shipping.country' => $data['shipping']['country'],
							'shipping.phone' => $data['shipping']['phone'],
							'date_completed' => $date_completed,
							'date_paid' => $date_paid,
							'product_id' => $product_id,
							'order_subscriptions' => self::subscriptions_for_order($data['id']),
							'custom' => ''
						);
					}
				}
			}
			return $output;
		}
		public static function subscriptions_for_order($order_id){
			if(!function_exists('wcs_get_subscriptions_for_order')) return '';
			foreach (wcs_get_subscriptions_for_order($order_id) as $subscription_id => $subscription_obj){
				return $subscription_id;
			}
		}
		public static function type(){
			$output = array(
				'id',
				'order',
				'date_created',
				'status',
				'product_name',
				'total',
				'payment_method_title',
				'billing.email',
				'billing',
				'shipping',
				'customer_note',
				'customer_id',
				'currency',
				'discount_total',
				'discount_tax',
				'shipping_total',
				'shipping_tax',
				'cart_tax',
				'total_tax',
				'date_modified',
				'billing.first_name',
				'billing.last_name',
				'billing.company',
				'billing.address_1',
				'billing.address_2',
				'billing.city',
				'billing.state',
				'billing.postcode',
				'billing.country',
				'billing.phone',
				'shipping.first_name',
				'shipping.last_name',
				'shipping.company',
				'shipping.address_1',
				'shipping.address_2',
				'shipping.city',
				'shipping.state',
				'shipping.postcode',
				'shipping.country',
				'shipping.phone',
				'date_completed',
				'date_paid',
				'product_id',
				'order_subscriptions',
				'custom'
			);
			return $output;
		}
		public static function order_type(){
			$output = '<option value="">Select</option>';
			$i = -1;
			foreach (self::type() as $value){ 
				$i++;
				$output .= '<option value="'.'column_'.$i.'" >'.$value.'</option>';
			}
			return $output;
		}
		public static function order_types(){
			$output = array();
			$i = -1;
			foreach (self::type() as $value){ 
				$i++;
				$output['column_'.$i] = $value;
			}
			return $output;
		}
	}
	new wptableeditor_woocommerce();
}

if ( ! class_exists( 'wptableeditor_post' ) ) {
	class wptableeditor_post {
		public static function category($id){
			$category = array();
			if(!empty(get_the_category($id))){
				foreach(get_the_category($id) as $row){
					$category[] = '<a href="'.esc_url( get_category_link($row->term_id)).'">'.esc_html( $row->name ).'</a>';
				}
			}
			return implode(",",$category);
		}
		public static function categories($table_id){
			$category_id = wptableeditor_table::category($table_id);
			$categories = '';
			if(!empty(get_categories())){
				foreach(get_categories() as $row){
					$name = $row->name;
					if(empty($category_id)){
						$categories .= "<option value=$name >$name</option>";
					}elseif(in_array($row->term_id, $category_id)){
						$categories .= "<option value=$name >$name</option>";
					}
				}
			}
			return $categories;
		}
		public static function tags($id){
			$tags = array();
			if(!empty(get_the_tags($id))){
				foreach(get_the_tags($id) as $row){
					$tags[] = '<a href="'.esc_url( get_tag_link($row->term_id)).'">'.esc_html( $row->name ).'</a>';
				}
			}
			return implode(",",$tags);
		}
		public static function author($author){
			$user_info = get_userdata($author);
			$user_name = $user_info->display_name;
			return $user_name;
		}
		public static function post($type, $category_id = 0, $limit = 1000){
			if(empty($category_id)){
				$category_id = 0;
			}
			$output = array();
			if($limit === 0) return $output;
			if($type === 'post'){
				$posts = get_posts(array('category'  => $category_id, 'numberposts' => $limit));
			}elseif($type === 'page'){
				if($limit < 0) $limit = 0;
				$posts = get_pages(array('number' => $limit));
			}
			foreach($posts as $row){
				if($row->post_status === 'publish'){
					$output[] = array(
						"post_id" => $row->ID,
						"post_title" => '<a href="'.get_permalink($row->ID).'" target="_self">'.$row->post_title.'</a>',
						"post_thumbnail" => '<img src="'.get_the_post_thumbnail_url($row->ID).'" class="img-thumbnail" width="100" />',
						"category" => self::category($row->ID),
						"post_tags" => self::tags($row->ID),
						"post_date" => date("Y-m-d", strtotime($row->post_date)),
						"post_author" => self::author($row->post_author),
						"post_url" => get_permalink($row->ID),
						"post_type" => $row->post_type,
						"post_modified" => date("Y-m-d", strtotime($row->post_modified)),
						"post_name" => $row->post_name,
						"post_guid" => $row->guid,
						"comment_status" => $row->comment_status,
						"comment_count" => $row->comment_count,
						"custom" => ''
					);
				}
			}
			return $output;
		}
		public static function types(){
			$output = array(
				"post_id",
				"post_title",
				"post_thumbnail",
				"category",
				"post_tags",
				"post_date",
				"post_author",
				"post_url",
				"post_type",
				"post_modified",
				"post_name",
				"post_guid",
				"comment_status",
				"comment_count",
				"custom"
			);
			return $output;
		}
		public static function poss($type){
			$output = '<option value="">Select</option>';
			$i = -1;
			foreach (self::types() as $value){ 
				$i++;
				$output .= '<option value="'.'column_'.$i.'" >'.$value.'</option>';
			}
			return $output;
		}
		public static function type($type){
			$output = array();
			$i = -1;
			foreach (self::types() as $value){
				$i++;
				$output['column_'.$i] = $value;
			}
			return $output;
		}
	}
	new wptableeditor_post();
}

if ( ! class_exists( 'wptableeditor_url' ) ) {
	class wptableeditor_url {
		public static function count($table_url, $table_datasrc){
			$handle = fopen($table_url, 'r');
			$data = '';
			while (!feof($handle)) {
			    $line = fgets($handle);
			    if (strpos($line, '"'.$table_datasrc.'": [') !== false) {
			        while (!feof($handle)) {
			            $line = fgets($handle);
			            $data .= $line;
			            if (strpos($line, ']') !== false) {
			                break;
			            }
			        }
			    }
			}
			fclose($handle);
			$array = json_decode(str_replace('],', ']', $data));
			return count($array);
		}
		public static function column($table_id){
			global $wpdb;
			$query = "SELECT table_url, table_datasrc FROM ".WPTABLEEDITOR_TABLE." WHERE table_id = %d";
			$result = $wpdb->get_row($wpdb->prepare($query, $table_id), ARRAY_A);
			$table_url = $result['table_url'];
			if(!empty($result['table_datasrc'])){
				$table_datasrc = $result['table_datasrc'];
			}else{
				$table_datasrc = '';
			}
			$array = json_decode(wptableeditor_load::getContentWithCurl($table_url), true);
			$output = '<option value="">Select</option>';
			if(isset($array[$table_datasrc][0])){
				for ($i = 0; $i <= count($array[$table_datasrc][0]) - 1; $i++){
					$output .= '<option value="column_'.$i.'" >column_'.$i.'</option>';
				}
			}else{
				$data = array();
				foreach($array as $row){
					$data[] = array_values($row);
				}
				for ($i = 0; $i <= count($data[0]) - 1; $i++){
					$output .= '<option value="column_'.$i.'" >column_'.$i.'</option>';
				}
			}
			if(wptableeditor_load::get_column($table_id, 'table_jsonsave') === 'yes'){
				$output .= '<option value="column_'.$i.'" >column_id</option>';
			}
			return $output;
		}
	}
}

if ( ! class_exists( 'wptableeditor_sheet' ) ) {
	class wptableeditor_sheet {
		public static function column($table_id){
			global $wpdb;
			$query = "SELECT table_apikey, table_sheetid, table_sheetname, table_range FROM ".WPTABLEEDITOR_TABLE." WHERE table_id = %d";
			$result = $wpdb->get_row($wpdb->prepare($query, $table_id), ARRAY_A);
			$spreadsheets = sprintf('https://sheets.googleapis.com/v4/spreadsheets/%s/values/%s!%s?key=%s', $result['table_sheetid'], $result['table_sheetname'], $result['table_range'], $result['table_apikey']);
			$array = json_decode(wptableeditor_load::getContentWithCurl($spreadsheets));
			$output = '<option value="">Select</option>';
			for ($i = 0; $i <= count($array->values[0]) - 1; $i++){
				$output .= '<option value="column_'.$i.'" >column_'.$i.'</option>';
			}
			if(wptableeditor_load::get_column($table_id, 'table_jsonsave') === 'yes'){
				$output .= '<option value="column_'.$i.'" >column_id</option>';
			}
			return $output;
		}
	}
}

if ( ! class_exists( 'wptableeditor_import' ) ) {
	class wptableeditor_import {
		public static function import($table, $total_column){
			global $wpdb;
			$column = array();
			for($x = 1; $x <= $total_column; $x++){
				$column[] = "column_$x longtext NOT NULL";
			}
			$column = implode(",",$column);
			$wpdb->query("DROP TABLE IF EXISTS `{$table}`");
			$charset_collate = $wpdb->get_charset_collate();
			$sql = "CREATE TABLE IF NOT EXISTS $table (
				column_id bigint(20) NOT NULL AUTO_INCREMENT,
				column_status enum('active','inactive') NOT NULL,
				column_order bigint(20) NOT NULL,
				$column,
				PRIMARY KEY (column_id)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			dbDelta( $sql );
		}
		public static function column_name($table, $table_id){
			global $wpdb;
			$query = "SELECT column_name FROM `{$table}` WHERE table_id = %d ORDER BY column_order ASC";
			$result = $wpdb->get_results($wpdb->prepare($query, $table_id));
			$output = '';
			if(!empty($result)){
				foreach($result as $row){
					$output .= $row->column_name.', ';
				}
			}
			return substr($output, 0, -2);
		}
		public static function get_tables() {
			global $wpdb;
			if ( function_exists( 'is_multisite' ) && is_multisite() ) {
				if ( is_main_site() ) {
					$tables 	= $wpdb->get_col( 'SHOW TABLES' );
				} else {
					$blog_id 	= get_current_blog_id();
					$tables 	= $wpdb->get_col( "SHOW TABLES LIKE '" . $wpdb->base_prefix . absint( $blog_id ) . "\_%'" );
				}
			} else {
				$tables = $wpdb->get_col( 'SHOW TABLES' );
			}
			return $tables;
		}
		public static function table(){
			$tables = self::get_tables();
			$output = '';
			foreach($tables as $table){
				$output .= '<option value="'.$table.'" >'.$table.'</option>';
			}
			return $output;
		}
		public static function localize($table_id){
			$column_number = wptableeditor_column::rowCount($table_id);
			$column_import = wptableeditor_column::import($table_id);
			$localize = array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'xsnonce' => wp_create_nonce('xs-import'.$table_id),
				'table_id' => $table_id,
				'column_number' => $column_number,
				'column_names' => $column_import['name'],
				'column_post' => $column_import['post'],
			);
			return $localize;
		}
	}
	new wptableeditor_import();
}
?>