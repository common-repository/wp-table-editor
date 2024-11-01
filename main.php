<?php
/*
Plugin Name: Table Editor
Plugin URI: https://wptableeditor.com/documentation/
Description: Table Editor is a WordPress plugin used to quickly create tables from Excel, CSV, JSON and other data sources. Allows you to create beautiful sortable and responsive tables inside your posts, pages, custom post types or widget area.
Version: 1.5.0
Author: wptableeditor.com
Author URI: https://wptableeditor.com/
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Text Domain: wp-table-editor
Domain Path: /i18n/languages/
*/
defined( 'ABSPATH' ) || exit;
if ( ! defined( 'WPTABLEEDITOR_ASSETS' ) ) define( 'WPTABLEEDITOR_ASSETS', plugins_url( 'assets/', __FILE__ ) );
if ( ! defined( 'WPTABLEEDITOR_VENDOR' ) ) define( 'WPTABLEEDITOR_VENDOR', plugins_url( 'vendor/', __FILE__ ) );
if ( ! defined( 'WPTABLEEDITOR_VERSION' ) ) define( 'WPTABLEEDITOR_VERSION', '1.5.0' );
if ( ! defined( 'WPTABLEEDITOR_SLUG' ) ) define( 'WPTABLEEDITOR_SLUG', basename( plugin_dir_path( __FILE__ ) ) );
if ( ! defined( 'WPTABLEEDITOR_MAIN' ) ) define( 'WPTABLEEDITOR_MAIN', plugin_basename( __FILE__ ) );
if ( ! defined( 'WPTABLEEDITOR_PATH' ) ) define( 'WPTABLEEDITOR_PATH', __DIR__ );
if ( ! defined( 'WPTABLEEDITOR_INCLUDES' ) ) define( 'WPTABLEEDITOR_INCLUDES', __DIR__ . '/includes/' );
if ( ! defined( 'WPTABLEEDITOR_FILE' ) ) define( 'WPTABLEEDITOR_FILE', __FILE__ );
ob_start();

if( !function_exists( 'wp_get_current_user' ) ) {
	include_once( ABSPATH. "wp-includes/pluggable.php" ); 
}
include_once 'includes/load.php';
include_once 'includes/public/shortcode.php';

if ( ! class_exists( 'wptableeditor_main' ) ) {
	class wptableeditor_main {

		private static $api_url = 'https://wptableeditor.com/tracking';

		public function __construct(){
			global $pagenow;
			add_action( 'plugins_loaded', array( $this, 'languages' ) );
			if( is_admin() && current_user_can( 'manage_options' ) ) {
				add_action( 'admin_menu', array( $this, 'admin_menu' ) );
				add_action( 'admin_enqueue_scripts', array( $this, 'xs_enqueue_scripts' ) );
				if($pagenow === 'admin.php' && isset($_GET['page']) && in_array($_GET['page'], array('wptableeditor'))){
					add_action('in_admin_header', array( $this, 'skip_notice'), 10000);
				}
				include_once 'includes/admin/function.php';
				include_once 'includes/admin/ajax.php';
				include_once 'includes/admin/option.php';
			}
			if ( false === get_transient( 'wptableeditor_tracking' ) ) {
				$this->tracking();
			}
		}

		public function languages() {
		    load_plugin_textdomain( 'wp-table-editor', false, dirname( plugin_basename( __FILE__ ) ) . '/i18n/languages' ); 
		}

		public function admin_menu(){
			add_menu_page( 'Table Editor', 'Table Editor', 'manage_options', 'wptableeditor', '', 'dashicons-list-view', 6 );
			add_submenu_page( 'wptableeditor', 'Table Editor', 'All Tables', 'manage_options', 'wptableeditor', array( $this, 'admin_page' ), 1 );
		}

		public function admin_page(){
			global $table_id, $tab_id, $_xsnonce, $table_type;
			if(isset($_GET['table_id'])){
				$table_id = (int) sanitize_text_field($_GET['table_id']);
				$_xsnonce = wp_create_nonce('xs-table'.$table_id);
				$table_type = wptableeditor_table::type($table_id);
				if(isset($_GET['tab'])){
					if(wptableeditor_init::check_tables(WPTABLEEDITOR_PREFIX. $table_id)){
						$tab_id = "&table_id=$table_id&_xsnonce=".$_xsnonce;
						$tab = sanitize_text_field($_GET['tab']);
						$this->tab($tab.$tab_id);
						switch ( $tab ) {
							case 'row' :
								echo '<div class="wrap">';
								include_once 'includes/admin/row.php';
								echo '</div>';
							break;
							case 'column' :
								echo '<div class="wrap">';
								include_once 'includes/admin/column.php';
								echo '</div>';
							break;
							case 'import' :
								if($table_type === 'default'){
									echo '<div class="wrap">';
									include_once 'includes/admin/import.php';
									echo '</div>';
								}else{
									wp_redirect('admin.php?page=wptableeditor');
									exit;
								}
							break;
						}
					}else{
						wp_redirect('admin.php?page=wptableeditor');
						exit;
					}
				}else{
					wp_redirect('admin.php?page=wptableeditor');
					exit;
				}
			}elseif(isset($_GET['tab']) && $_GET['tab'] == 'option'){
				$tab = sanitize_text_field($_GET['tab']);
				$this->tabs($tab);
		    	$options = get_option( 'xs_custom_css' );
		    	$content = isset( $options['xscss-content'] ) && ! empty( $options['xscss-content'] ) ? $options['xscss-content'] : '';
				?>
				<div class="wrap">
		    		<div class="xscontainer">
			    		<div class="panel panel-default">
				    		<div class="panel-heading">
				    			<h5 class="panel-title"><b><?php esc_html_e( 'Custom CSS', 'wp-table-editor' ); ?></b></h5>
				    		</div>
				    		<form method="post" enctype="multipart/form-data">
				    			<div id="xs_template">
				    				<textarea cols="70" rows="10" name="xs_custom_css[xscss-content]" class="xscss-content" id="xs_custom_css[xscss-content]"><?php echo esc_html( stripslashes( $content ) ); ?></textarea>
				    				<div class="panel-footer">
				    					<button type="submit" id="xs_update_css" class="button button-primary" name="xs_update_css" value="xs_update_css">Update CSS</button>
				    				</div>
				    			</div>
				    		</form>
			    		</div>
			    		<hr>
			    		<div class="row">
				    		<div class="col-md-6">
								<h3><?php esc_html_e( 'Uninstall', 'wp-table-editor' ); ?></h3>
								<?php
								if(current_user_can('delete_plugins')){
								echo esc_html__( '- Uninstalling will permanently delete all tables and options from the database.', 'wp-table-editor' ) . '<br />'
									. esc_html__( '- You will manually need to remove the plugin&#8217;s files from the plugin folder afterwards.', 'wp-table-editor' ) . '<br />'
									. esc_html__( '- Be very careful with this and only click the button if you know what you are doing!', 'wp-table-editor' );
								?>
								</p>
								<p><a href="<?php echo esc_url(wptableeditor_init::url_uninstall()); ?>" id="uninstall-wptableeditor" class="btn btn-danger btn-xs" onclick="return confirm('<?php esc_html_e('Do you really want to delete all data?', 'wp-table-editor'); ?>')"><?php esc_html_e( 'Uninstall', 'wp-table-editor' ); ?></a></p>
								<?php } ?>
							</div>
						</div>
					</div>
				</div>
				<?php
			}else{
				$this->tabs();
				echo '<div class="wrap">';
				include_once 'includes/admin/table.php';
				echo '</div>';
			}
		}

		public function tab( $current = 'row' ) {
			global $tab_id, $table_type;
			if(isset($tab_id)){
				$tabs = array( "row$tab_id" => 'Row', "column$tab_id" => 'Column' );
			}
			if(isset($table_type) && $table_type === 'default'){
				$tabs = array( "row$tab_id" => 'Row', "column$tab_id" => 'Column', "import$tab_id" => 'Import' );
			}
			$option = ( 'option' === $current ) ? ' nav-tab-active' : '';
			echo '<div class="wrap">';
			echo '<div class="nav-tab-xs">';
			echo '<h2 class="nav-tab-wrapper">';
			echo "<a class='nav-tab' href='?page=wptableeditor'><span class='dashicons dashicons-admin-home'></span></a>";
			echo "<a class='nav-tab".esc_attr($option)."' href='?page=wptableeditor&tab=option'><span>Options</span></a>";
			if($tabs){
				foreach( $tabs as $tab => $name ) {
					$class = ( $tab === $current ) ? ' nav-tab-active' : '';
					echo '<a class="nav-tab'.esc_attr($class).'" href="?page=wptableeditor&tab='.esc_attr($tab).'">'.esc_html($name).'</a>';
				}
			}
			echo "<a class='nav-tab' href='https://wptableeditor.com/pricing/' target='_blank' style='background-color:#8ab547;color:#fff;'><span>Upgrade to Premium</span><span class='dashicons dashicons-arrow-right-alt' style='line-height:1.3;'></span></a>";
			echo '</h2>';
			echo '</div>';
			echo '</div>';
		}

		public static function tabs( $tab = null ){
			echo '<div class="wrap">';
			echo '<div class="nav-tab-xs">';
			echo '<h2 class="nav-tab-wrapper">';
			$home = ( null === $tab ) ? ' nav-tab-active' : '';
			$option = ( 'option' === $tab ) ? ' nav-tab-active' : '';
			echo "<a class='nav-tab".esc_attr($home)."' href='?page=wptableeditor'><span class='dashicons dashicons-admin-home'></span></a>";
			echo "<a class='nav-tab".esc_attr($option)."' href='?page=wptableeditor&tab=option'><span>Options</span></a>";
			echo "<a class='nav-tab' href='https://wptableeditor.com/pricing/' target='_blank' style='background-color:#8ab547;color:#fff;'><span>Upgrade to Premium</span><span class='dashicons dashicons-arrow-right-alt' style='line-height:1.3;'></span></a>";
			echo '</h2>';
			echo '</div>';
			echo '</div>';
		}

		public function xs_enqueue_scripts(){
			global $pagenow;
			if ( $pagenow === 'admin.php' && isset($_GET['page']) && in_array( $_GET['page'], array( 'wptableeditor' ) ) ) {
				wp_enqueue_style( 'xsbootstrap', WPTABLEEDITOR_VENDOR. 'bootstrap/css/bootstrap.min.css' );
				wp_enqueue_style( 'xsstyle', WPTABLEEDITOR_ASSETS. 'css/style.css' );
				wp_enqueue_style( 'xsadmin', WPTABLEEDITOR_ASSETS. 'css/admin.css' );
				wp_enqueue_script( 'xsbootstrap', WPTABLEEDITOR_VENDOR. 'bootstrap/js/bootstrap.bundle.min.js' );
				if($_GET['page'] === 'wptableeditor'){
					wp_enqueue_style( 'wptableeditor', WPTABLEEDITOR_VENDOR. 'datatables/datatables.min.css' );
					wp_enqueue_script( 'wptableeditor', WPTABLEEDITOR_VENDOR. 'datatables/datatables.min.js' );
					wp_enqueue_script( 'jquery-ui-sortable' );
					if(isset($_GET['tab']) && isset($_GET['table_id'])){
						$tab = sanitize_text_field($_GET['tab']);
						$table_id = (int) sanitize_text_field($_GET['table_id']);
						if($tab === 'column'){
							$localize = wp_json_encode(wptableeditor_column::localize($table_id));
							wp_enqueue_script( 'xscolumn', WPTABLEEDITOR_ASSETS. 'js/column.js' );
							wp_add_inline_script( 'xscolumn', 'var xs_ajax_column = '. $localize, 'before' );
							wp_localize_script( 'xscolumn', 'xs_ajax_localize', wptableeditor_load::localize() );
						}elseif($tab === 'row'){
							wp_enqueue_script( 'xsmoment', WPTABLEEDITOR_VENDOR. 'moment/moment.min.js' );
							wp_enqueue_script( 'xsaudio', WPTABLEEDITOR_ASSETS. 'js/soundmanager.js' );
							$localize = wp_json_encode(wptableeditor_row::localize($table_id));
							wp_enqueue_script( 'xsrow', WPTABLEEDITOR_ASSETS. 'js/row.js' );
							wp_add_inline_script( 'xsrow', 'var xs_ajax_row = '. $localize, 'before' );
							wp_localize_script( 'xsrow', 'xs_ajax_localize', wptableeditor_load::localize() );
						}elseif($tab === 'import'){
							$localize = wp_json_encode(wptableeditor_import::localize($table_id));
							wp_enqueue_script( 'xsimport', WPTABLEEDITOR_ASSETS. 'js/import.js' );
							wp_add_inline_script( 'xsimport', 'var xs_ajax_import = '. $localize, 'before' );
							wp_localize_script( 'xsimport', 'xs_ajax_localize', wptableeditor_load::localize() );
						}
					}else{
						$localize = wp_json_encode(wptableeditor_table::localize());
						wp_enqueue_script( 'xstable', WPTABLEEDITOR_ASSETS. 'js/table.js' );
						wp_add_inline_script( 'xstable', 'var xs_ajax_table = '. $localize, 'before' );
						wp_localize_script( 'xstable', 'xs_ajax_localize', wptableeditor_load::localize() );
					}
				}
			}
		}

		public function skip_notice(){
			global $wp_filter;
			if ( is_network_admin() && isset( $wp_filter["network_admin_notices"] ) ) {
				unset( $wp_filter['network_admin_notices'] ); 
			} elseif ( is_user_admin() && isset( $wp_filter["user_admin_notices"] ) ) {
				unset( $wp_filter['user_admin_notices'] ); 
			} else {
				if ( isset( $wp_filter["admin_notices"] ) ) {
					unset( $wp_filter['admin_notices'] ); 
				}
			}
			if ( isset( $wp_filter["all_admin_notices"] ) ) {
				unset( $wp_filter['all_admin_notices'] ); 
			}
		}

		public function tracking(){
			require ABSPATH . WPINC . '/version.php';
			$locale = apply_filters( 'core_version_check_locale', get_locale() );
			$home_url = home_url();
			$option = array( 'slug' => WPTABLEEDITOR_SLUG, 'version' => WPTABLEEDITOR_VERSION, 'domain' => $home_url, 'action' => 'tracking', 'wp_version' => $wp_version, 'php_version' => PHP_VERSION, 'locale' => $locale );
			$args = array(
				'headers' => array(
					'Content-Type' => 'application/json',
					"User-Agent" => $home_url,
				),
				'timeout' => 15
			);
			$url = add_query_arg( $option, self::$api_url );
			$raw_response = wp_remote_get( $url, $args );
			set_transient( 'wptableeditor_tracking', time(), 2 * HOUR_IN_SECONDS );
		}
	}
	new wptableeditor_main();
}else{
	include_once 'includes/admin/function.php';
}
?>