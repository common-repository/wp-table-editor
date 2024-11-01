<?php

defined( 'ABSPATH' ) || exit;

global $wpdb;

if ( ! defined( 'WPTABLEEDITOR_TABLE' ) ) define( 'WPTABLEEDITOR_TABLE', $wpdb->prefix . 'wptableeditor_table' );
if ( ! defined( 'WPTABLEEDITOR_PREFIX' ) ) define( 'WPTABLEEDITOR_PREFIX', $wpdb->prefix . 'wptableeditor_table_' );
if ( ! defined( 'WPTABLEEDITOR_COLUMN' ) ) define( 'WPTABLEEDITOR_COLUMN', $wpdb->prefix . 'wptableeditor_column' );
if ( ! defined( 'WPTABLEEDITOR_DATA_PATH' ) ) define( 'WPTABLEEDITOR_DATA_PATH', WP_CONTENT_DIR . DIRECTORY_SEPARATOR . 'wpTableEditor' );
if ( ! defined( 'WPTABLEEDITOR_DATA_URL' ) ) define( 'WPTABLEEDITOR_DATA_URL', content_url() . '/wpTableEditor/' );
if ( ! defined( 'WPTABLEEDITOR_ROBOTS_TXT' ) ) define( 'WPTABLEEDITOR_ROBOTS_TXT', WPTABLEEDITOR_DATA_PATH . DIRECTORY_SEPARATOR . 'robots.txt' );
if ( ! defined( 'WPTABLEEDITOR_HTACCESS' ) ) define( 'WPTABLEEDITOR_HTACCESS', WPTABLEEDITOR_DATA_PATH . DIRECTORY_SEPARATOR . '.htaccess' );
if ( ! defined( 'WPTABLEEDITOR_WEBCONFIG' ) ) define( 'WPTABLEEDITOR_WEBCONFIG', WPTABLEEDITOR_DATA_PATH . DIRECTORY_SEPARATOR . 'web.config' );
if ( ! defined( 'WPTABLEEDITOR_INDEX_PHP' ) ) define( 'WPTABLEEDITOR_INDEX_PHP', WPTABLEEDITOR_DATA_PATH . DIRECTORY_SEPARATOR . 'index.php' );
if ( ! defined( 'WPTABLEEDITOR_INDEX_HTML' ) ) define( 'WPTABLEEDITOR_INDEX_HTML', WPTABLEEDITOR_DATA_PATH . DIRECTORY_SEPARATOR . 'index.html' );

if ( ! class_exists( 'wptableeditor_load' ) ) {
	class wptableeditor_load {
		public static function current_user_can(){
			if( !is_admin() ) return;
			if( !current_user_can( 'manage_options' ) ) {
				wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'wp-table-editor'));
			}
		}
		public static function column_name(){
			global $wpdb;
			$query = "SELECT COLUMN_NAME FROM information_schema.columns WHERE table_schema = %s AND table_name = %s";
			$result = $wpdb->get_results($wpdb->prepare($query, DB_NAME, WPTABLEEDITOR_TABLE));
			$column_name = array();
			foreach($result as $row){
				if(!in_array($row->COLUMN_NAME, array('table_id', 'table_status', 'table_author', 'table_restrictionrole'))){
					$column_name[] = $row->COLUMN_NAME;
				}
			}
			return $column_name;
		}
		public static function license(){
			if(!class_exists('wptableeditor_license') || !wptableeditor_license::check_license()){
				return false;
			}
			return true;
		}
		public static function table_status($table_id){
			global $wpdb;
			$query = "SELECT table_status FROM ".WPTABLEEDITOR_TABLE." WHERE table_id = %d";
			$result = $wpdb->get_var($wpdb->prepare($query, $table_id));
			if($result === 'active'){
				return true;
			}else{
				return false;
			}
		}
		public static function row_status($table, $column_id){
			global $wpdb;
			$query = "SELECT column_status FROM `{$table}` WHERE column_id = %d";
			$result = $wpdb->get_var($wpdb->prepare($query, $column_id));
			if(empty($result) || $result === 'active'){
				return true;
			}else{
				return false;
			}
		}
		public static function column_status($table_id){
			global $wpdb;
			$output = array();
			$query = "SELECT id, column_status FROM ".WPTABLEEDITOR_COLUMN." WHERE table_id = %d";
			$result = $wpdb->get_results($wpdb->prepare($query, $table_id));
			foreach($result as $row){
				if($row->column_status === 'active'){
					$output[] = $row->id;
				}
			}
			return $output;
		}
		public static function table($table_id){
			global $wpdb;
			$query = "SELECT * FROM ".WPTABLEEDITOR_TABLE." WHERE table_id = %d";
			$result = $wpdb->get_row($wpdb->prepare($query, $table_id), ARRAY_A);
			if($result){
				return $result;
			}
			return false;
		}
		public static function tables(){
			$output = array();
			if(self::license() === false){
				$output = array(
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
					'table_reload',
					'table_prefilter',
					'table_jsonsave',
					'table_autosave',
					'table_datasources',
					'table_datatable'
				);
			}
			return $output;
		}
		public static function table_name($table_id){
			global $wpdb;
			$query = "SELECT table_name FROM ".WPTABLEEDITOR_TABLE." WHERE table_id = %d";
			$output = $wpdb->get_var($wpdb->prepare($query, $table_id));
			if(!empty($output)){
				return $output;
			}
			return 'undefined';
		}
		public static function datatable($table_id){
			global $wpdb;
			$query = "SELECT table_datatable FROM ".WPTABLEEDITOR_TABLE." WHERE table_id = %d";
			$datatable = $wpdb->get_var($wpdb->prepare($query, $table_id));
			if(!wptableeditor_init::check_tables(WPTABLEEDITOR_PREFIX.$datatable)){
				return 0;
			}
			return $datatable;
		}
		public static function column($table_id){
			global $wpdb;
			$query = "SELECT * FROM ".WPTABLEEDITOR_COLUMN." WHERE table_id = %d";
			$query .= " ORDER BY column_order ASC";
			$result = $wpdb->get_results($wpdb->prepare($query, $table_id));
			if($result){
				$license = self::license();
				$output = array();
				for($i = 1; $i <= 44; $i++){
					$variable = 'output'.$i;
					$$variable = array();
				}
				$output15 = array('null');
				$output22 = array(0);
				$output36 = array('null');
				$output30 = array('column_id', 'column_order');
				$j = 0;
				$k = 0;
				foreach($result as $row){
					if($row->column_status === 'active'){
						$output[] = $row->column_order;
						$output30[] = $row->column_order;
						if($row->column_filters === 'yes'){
							$output1[] = $row->column_order;
							if($row->column_position === 'default'){
								$output5[] = $row->column_order;
							}
							if($row->column_position === 'footer'){
								$output6[] = $row->column_order;
							}
						}
						if($row->column_hidden === 'yes'){
							$output2[] = $row->column_order;
							$output8[] = $row->column_order;
						}elseif($row->column_hidden === 'no'){
							$output13[] = $row->column_order;
						}
						if($row->column_orderable === 'no'){
							$output3[] = $row->column_order;
						}
						if($row->column_searchable === 'no'){
							$output4[] = $row->column_order;
						}
						if($row->column_total === 'yes'){
							$output7[] = $row->column_order;
						}
						if($row->column_filters === 'yes' && $row->column_filter === 'yes' && !empty($row->column_customfilter)){
							$output9[$row->column_order] = $row->column_customfilter;
						}
						if(isset($row->column_align)){
							if($row->column_align === 'left'){
								$output10[] = $row->column_order;
							}elseif($row->column_align === 'center'){
								$output11[] = $row->column_order;
							}elseif($row->column_align === 'right'){
								$output12[] = $row->column_order;
							}
						}
						if($row->column_nowrap === 'yes'){
							$output43[] = $row->column_order;
						}
						$output14[] = $row->column_names;
						$column_names = '"title": "'.$row->column_names.'",';
						if($row->column_width > 0){
							$output15[] = '{ '.$column_names.'"width": "'.$row->column_width.'%" }';
							$output36[] = '{ '.$column_names.'"width": "'.$row->column_width.'%","data": "'.str_replace('column_', '', $row->column_name).'" }';
						}else{
							$output15[] = '{ "title": "'.$row->column_names.'" }';
							$output36[] = '{ '.$column_names.'"data": "'.str_replace('column_', '', $row->column_name).'" }';
						}
						if(!empty($row->column_backgroundcolor)){
							$output17[$row->column_order] = $row->column_backgroundcolor;
						}
						if(!empty($row->column_fontcolor)){
							$output33[$row->column_order] = $row->column_fontcolor;
						}
						if(!empty($row->column_fontweight)){
							$output37[$row->column_order] = $row->column_fontweight;
						}
						if(!empty($row->column_fontstyle)){
							$output38[$row->column_order] = $row->column_fontstyle;
						}
						if(!empty($row->column_minwidth)){
							$output44[$row->column_order] = $row->column_minwidth;
						}
						if($row->column_type === 'select' && !empty($row->column_customtype)){
							$output18[$row->column_order] = $row->column_customtype;
						}
						if($license !== false && $row->column_index === 'yes'){
							$output19[] = $row->column_order;
						}
						if($row->column_childrows === 'yes'){
							$output24[] = $row->column_order;
						}
						$j = $j + 1;
						$output20[$j] = $row->column_name;
						if(isset($row->column_search) && $row->column_search === 'yes'){
							$output21[] = $row->column_order;
						}
						if(isset($row->column_priority)){
							$output22[$j] = $row->column_priority;
						}
						$output39[] = stripslashes(maybe_unserialize($row->column_render));
						$output40[] = stripslashes(maybe_unserialize($row->column_createdcell));
						if(isset($row->column_characterlimit)){
							$output41[$j] = $row->column_characterlimit;
						}
						if(isset($row->column_optionlimit)){
							$output42[$j] = $row->column_optionlimit;
						}
						if($row->column_filters === 'yes'){
							$output23[$j] = $row->column_name;
						}
						$column_control = explode(',', $row->column_control);
						if($row->column_control === 'desktop,tablet-l,tablet-p,mobile-l,mobile-p'){
							$column_control = array();
						}
						foreach($column_control as $rows){
							if($rows === 'desktop'){
								$output25[] = $row->column_order;
							}elseif($rows === 'tablet-l'){
								$output26[] = $row->column_order;
							}elseif($rows === 'tablet-p'){
								$output27[] = $row->column_order;
							}elseif($rows === 'mobile-l'){
								$output28[] = $row->column_order;
							}elseif($rows === 'mobile-p'){
								$output29[] = $row->column_order;
							}
						}
						$output35[$row->column_names] = $row->column_order;
					}
					$output31[$row->column_name] = $row->column_names;
					$k = $k + 1;
					$output34[$k] = $row->column_name;
				}
				$outputs = array();
				for($i = 1; $i <= 44; $i++){
					$variables = 'outputs'.$i;
					$$variables = array();
				}
				$outputs2 = array(0);
				foreach($output as $key => $value){
					for($i = 1; $i <= 44; $i++){
						$_output = 'output'.$i;
						$_value = 'value'.$i;
						$_outputs = 'outputs'.$i;
						if(is_array($$_output)){
							foreach($$_output as $$_value){
								if($value === $$_value){
									$$_outputs[] = $key + 1;
								}
							}
						}
					}
					foreach($output9 as $key9 => $value9){
						if($value == $key9){
							if(!empty($value9)){
								$select = '';
								foreach(explode(";", $value9) as $rows){
									$select .= '<option value="'.$rows.'">'.$rows.'</option>';
								}
								$outputs9[$key + 1] = $select;
							}
						}
					}
					foreach($output17 as $key17 => $value17){
						if($value == $key17){
							$outputs17[$key + 1] = $value17;
						}
					}
					foreach($output33 as $key33 => $value33){
						if($value == $key33){
							$outputs33[$key + 1] = $value33;
						}
					}
					foreach($output37 as $key37 => $value37){
						if($value == $key37){
							$outputs37[$key + 1] = $value37;
						}
					}
					foreach($output38 as $key38 => $value38){
						if($value == $key38){
							$outputs38[$key + 1] = $value38;
						}
					}
					foreach($output44 as $key44 => $value44){
						if($value == $key44){
							$outputs44[$key + 1] = $value44;
						}
					}
					foreach($output35 as $key35 => $value35){
						if($value === $value35){
							$outputs35[$key + 1] = $key35;
						}
					}
					foreach($output18 as $key18 => $value18){
						if($value === $key18){
							if(!empty($value18)){
								$select = '';
								foreach(explode(";", $value18) as $rows){
									$select .= "<option value=$rows >$rows</option>";
								}
								$outputs18[$key + 1] = $select;
							}
						}
					}
				}
				if($license === false){
					$outputs19 = array();
					$outputs21 = array();
					$outputs24 = array();
					foreach($output22 as $key => $value){
						$output22[$key] = 0;
					}
					foreach($output39 as $key => $value){
						$output39[$key] = stripslashes(maybe_unserialize(''));
					}
					foreach($output40 as $key => $value){
						$output40[$key] = stripslashes(maybe_unserialize(''));
					}
					foreach($output41 as $key => $value){
						$output41[$key] = 0;
					}
					foreach($output42 as $key => $value){
						$output42[$key] = 50;
					}
				}
				$align = array();
				$align['status'] = wp_json_encode(array(0, count($output30)));
				$align['url'] = wp_json_encode(array(0));
				$align['all'] = wp_json_encode(array(0, count($output30), count($output30) + 1, count($output30) + 2));
				$align['edit'] = wp_json_encode(array(0, count($output30), count($output30) + 1));
				$align['count'] = count($output30) - 2;
				$outputs['align'] = $align;
				$outputs['column_filters'] = wp_json_encode($outputs1);
				$outputs['column_hidden'] = wp_json_encode($outputs2);
				$outputs['column_none'] = wp_json_encode($outputs24);
				$outputs['column_show'] = wp_json_encode($outputs13);
				$outputs['column_hiddens'] = wp_json_encode($outputs8);
				$outputs['column_orderable'] = wp_json_encode($outputs3);
				$outputs['column_searchable'] = wp_json_encode($outputs4);
				$outputs['column_default'] = wp_json_encode($outputs5);
				$outputs['column_footer'] = wp_json_encode($outputs6);
				$outputs['column_total'] = $outputs7;
				$outputs['column_customfilter'] = $outputs9;
				$outputs['column_left'] = wp_json_encode($outputs10);
				$outputs['column_center'] = wp_json_encode($outputs11);
				$outputs['column_right'] = wp_json_encode($outputs12);
				$outputs['column_nowrap'] = wp_json_encode($outputs43);
				$outputs['column_count'] = count($output);
				$outputs['column_name'] = $output31;
				$outputs['column_names'] = $output14;
				$outputs['column_width'] = implode(",",$output15);
				$outputs['column_point'] = implode(",",$output36);
				$outputs['column_backgroundcolor'] = $outputs17;
				$outputs['column_fontcolor'] = $outputs33;
				$outputs['column_fontweight'] = $outputs37;
				$outputs['column_fontstyle'] = $outputs38;
				$outputs['column_minwidth'] = $outputs44;
				$outputs['column_index'] = $outputs19;
				$outputs['column_order'] = $output20;
				$outputs['column_orders'] = $output34;
				$outputs['column_search'] = $outputs21;
				$outputs['column_priority'] = $output22;
				$outputs['column_characterlimit'] = $output41;
				$outputs['column_optionlimit'] = $output42;
				$outputs['column_render'] = $output39;
				$outputs['column_createdcell'] = $output40;
				$outputs['column_filter'] = $output23;
				$outputs['column_desktop'] = $outputs25;
				$outputs['column_tablet_l'] = $outputs26;
				$outputs['column_tablet_p'] = $outputs27;
				$outputs['column_mobile_l'] = $outputs28;
				$outputs['column_mobile_p'] = $outputs29;
				$outputs['names'] = $outputs35;
				return $outputs;
			}else{
				return false;
			}
		}
		public static function categorys($table_id){
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
		public static function table_groups($table_id, $table_type){
			global $wpdb;
			$query = "SELECT column_status, column_order, column_name FROM ".WPTABLEEDITOR_COLUMN." WHERE table_id = %d";
			$query .= " ORDER BY column_order ASC";
			$result = $wpdb->get_results($wpdb->prepare($query, $table_id));
			$column_active = array();
			$k = 0;
			foreach($result as $row){
				if($row->column_status == 'active'){
					$k++;
					$column_active[$k] = $row->column_order;
					$column_actives[$k] = $row->column_order - 1;
					$column_name[$k] = str_replace('column_', '', $row->column_name);
				}
			}
			if(count($column_active) == count($result)){
				if(in_array($table_type, array('json', 'sheet'))){
					return $column_name;
				}else{
					return $column_active;
				}
			}
			if(in_array($table_type, array('json', 'sheet'))){
				return $column_name;
			}else{
				return $column_actives;
			}
		}
		public static function order_name_type($table_id){
			global $wpdb;
			$query = "SELECT * FROM ".WPTABLEEDITOR_COLUMN." WHERE table_id = %d ORDER BY column_order ASC";
			$result = $wpdb->get_results($wpdb->prepare($query, $table_id));
			$output = array();
			for($i = 1; $i <= 12; $i++){
				$variable = 'output'.$i;
				$$variable = array();
			}
			$j = 0;
			foreach($result as $row){
				$output[] = $row->column_order;
				if($row->column_status === 'active'){
					$output2[] = $row->column_order;
					$j = $j + 1;
					$output1[$j] = $row->column_name;
					$output3[$j] = $row->column_name;
					$output4[$j] = $row->column_order;
					$output5[$j] = $row->column_type;
					if(isset($row->column_restriction)){
						$output6[$j] = $row->column_restriction;
						if(self::license() === false){
							$output6[$j] = 'no';
						}
					}
					$output7[$j] = $row->id;
					if(isset($row->column_restrictiontitle)){
						$output8[$j] = $row->column_restrictiontitle;
					}
					if($row->column_filters === 'yes'){
						$output9[$row->column_name] = $row->column_order;
					}
					$output10[$row->column_name] = $row->column_order;
					$output11[$row->id] = $row->column_order;
				}
				$output12[$row->column_order] = $row->column_name;
			}
			$outputs9 = array();
			$outputs10 = array();
			$outputs11 = array();
			$k = 0;
			$h = 0;
			$l = 0;
			foreach($output as $key => $value){
				foreach($output9 as $key9 => $value9){
					if($value === $value9){
						$l = $l + 1;
						$outputs9[$l] = $key9;
					}
				}
				foreach($output10 as $key10 => $value10){
					if($value === $value10){
						$k = $k + 1;
						$outputs10[$k] = $key10;
					}
				}
				foreach($output11 as $key11 => $value11){
					if($value === $value11){
						$h = $h + 1;
						$outputs11[$h] = $key11;
					}
				}
			}
			$output1[] = 'column_order';
			$outputs = array();
			$outputs['name'] = $output1;
			$outputs['names'] = $output3;
			$outputs['order'] = $output4;
			$outputs['types'] = $output5;
			$outputs['login'] = $output6;
			$outputs['id'] = $output7;
			$outputs['title'] = $output8;
			$outputs['count'] = count($output1);
			$outputs['column_filters'] = $outputs9;
			$outputs['order_name'] = $outputs10;
			$outputs['order_names'] = $output12;
			$outputs['order_id'] = $outputs11;
			return $outputs;
		}
		public static function column_roles($table_id, $column_id){
			global $wpdb;
			$query = "SELECT column_restrictionrole FROM ".WPTABLEEDITOR_COLUMN." WHERE table_id = %d AND id = %d";
			$permission = 'administrator,';
			$permission .= $wpdb->get_var($wpdb->prepare($query, $table_id, $column_id));
			if(is_user_logged_in()){
				$user = wp_get_current_user();
				$roles = $user->roles;
				foreach($roles as $role){
					if(in_array($role, explode(',', $permission))){
						return true;
					}
				}
			}
			return false;
		}
		public static function column_filter($table_id, $column_name){
			global $wpdb;
			$query = "SELECT column_filter FROM ".WPTABLEEDITOR_COLUMN." WHERE table_id = %d AND column_name = %s";
			$output = $wpdb->get_var($wpdb->prepare($query, $table_id, $column_name));
			return $output;
		}
		public static function set_memory_limit($memory = '512M'){
			ini_set('memory_limit', $memory);
		}
		public static function set_max_execution_time($time = 300){
			ini_set('max_execution_time', $time);
		}
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
		public static function product_types(){
			$output = array();
			foreach (self::woocommerce(array(), 10) as $value){ 
				$i = -1;
				foreach ($value as $keys => $values){ 
					$i= $i + 1;
					$output['column_'.$i] = $keys;
				}
			}
			return $output;
		}
		public static function category($id){
			$category = array();
			if(!empty(get_the_category($id))){
				foreach(get_the_category($id) as $row){
					$category[] = '<a href="'.esc_url( get_category_link($row->term_id)).'">'.esc_html( $row->name ).'</a>';
				}
			}
			return implode(",",$category);
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
		public static function post_type($type){
			$output = array();
			foreach (self::post($type, 0, 10) as $value){
				$i = -1;
				foreach ($value as $keys => $values){
					$i= $i + 1;
					$output['column_'.$i] = $keys;
				}
			}
			return $output;
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
		public static function localize(){
			$localize = array(
				'license' => esc_html__('Pro', 'wp-table-editor'),
				'reset' => esc_html__('Reset', 'wp-table-editor'),
				'type' => esc_html__('Type', 'wp-table-editor'),
				'status' => esc_html__('Status', 'wp-table-editor'),
				'align' => esc_html__('Align', 'wp-table-editor'),
				'least_checkbox' => esc_html__('Please Select at least one checkbox!', 'wp-table-editor'),
				'add_table' => esc_html__('Add Table', 'wp-table-editor'),
				'edit_table' => esc_html__('Edit Table', 'wp-table-editor'),
				'edit_restriction' => esc_html__('Access Restriction', 'wp-table-editor'),
				'edit_style' => esc_html__('Edit Style', 'wp-table-editor'),
				'status_table' => esc_html__('Are you sure you want to change the status of this table?', 'wp-table-editor'),
				'order_table' => esc_html__('Are you sure you want to change the order of this table?', 'wp-table-editor'),
				'updated_table' => esc_html__('Table order has been updated', 'wp-table-editor'),
				'duplicate_table' => esc_html__('Are you sure you want to duplicate this table?', 'wp-table-editor'),
				'delete_table' => esc_html__('Are you sure you want to delete this table?', 'wp-table-editor'),
				'add_column' => esc_html__('Add Column', 'wp-table-editor'),
				'edit_column' => esc_html__('Edit Column', 'wp-table-editor'),
				'status_column' => esc_html__('Are you sure you want to change the status of this column?', 'wp-table-editor'),
				'order_column' => esc_html__('Are you sure you want to change the order of this column?', 'wp-table-editor'),
				'updated_column' => esc_html__('Column order has been updated', 'wp-table-editor'),
				'duplicate_column' => esc_html__('Are you sure you want to duplicate this column?', 'wp-table-editor'),
				'delete_column' => esc_html__('Are you sure you want to delete this column?', 'wp-table-editor'),
				'add_row' => esc_html__('Add Row', 'wp-table-editor'),
				'edit_row' => esc_html__('Edit Row', 'wp-table-editor'),
				'status_row' => esc_html__('Are you sure you want to change the status of this row?', 'wp-table-editor'),
				'order_row' => esc_html__('Are you sure you want to change the order of this row?', 'wp-table-editor'),
				'updated_row' => esc_html__('Row order has been updated', 'wp-table-editor'),
				'duplicate_row' => esc_html__('Are you sure you want to duplicate this row?', 'wp-table-editor'),
				'delete_row' => esc_html__('Are you sure you want to delete this row?', 'wp-table-editor'),
				'sEmptyTable' => esc_html__('No data available in table', 'wp-table-editor'),
				'sInfo' => esc_html__('Showing _START_ to _END_ of _TOTAL_ entries', 'wp-table-editor'),
				'sInfoEmpty' => esc_html__('Showing 0 to 0 of 0 entries', 'wp-table-editor'),
				'sInfoFiltered' => esc_html__('(filtered from _MAX_ total entries)', 'wp-table-editor'),
				'sLoadingRecords' => esc_html__('Loading...', 'wp-table-editor'),
				'searchPlaceholder' => esc_html__('Search...', 'wp-table-editor'),
				'sZeroRecords' => esc_html__('No matching records found', 'wp-table-editor'),
				'sFirst' => esc_html__('First', 'wp-table-editor'),
				'sLast' => esc_html__('Last', 'wp-table-editor'),
				'sNext' => esc_html__('Next', 'wp-table-editor'),
				'sPrevious' => esc_html__('Previous', 'wp-table-editor'),
				'sSortAscending' => esc_html__(': activate to sort column ascending', 'wp-table-editor'),
				'sSortDescending' => esc_html__(': activate to sort column descending', 'wp-table-editor'),
				'visibility' => esc_html__('Column Visibility', 'wp-table-editor'),
				'export' => esc_html__('Export', 'wp-table-editor'),
				'select' => esc_html__('Select', 'wp-table-editor'),
				'searchBuilder' => esc_html__('Search Builder', 'wp-table-editor'),
				'searchPanes' => esc_html__('Search Panes', 'wp-table-editor'),
				'required_field' => esc_html__('This is a required field!', 'wp-table-editor'),
				'import_confirm' => esc_html__('This action is not reversable. Click Ok to continue. Click Cancel to abort.', 'wp-table-editor'),
				'import_success' => esc_html__('Data Successfully Imported', 'wp-table-editor'),
				'import_error' => esc_html__('Error occured. Please try again!', 'wp-table-editor'),
				'import_danger' => esc_html__('The number of columns specified does not match the number of columns in the import file.', 'wp-table-editor'),
			);
			return $localize;
		}
		public static function post_action($xs_type, $table_id, $delete = true, $dataSet = false){
			global $wpdb;
			$table_jsonsave = self::get_column($table_id, 'table_jsonsave');
			if($table_jsonsave == 'no' && $dataSet !== true){
				return;
			}elseif($table_jsonsave == 'yes' && $dataSet === true){
				return self::json_actions($table_id);
			}
			$table = WPTABLEEDITOR_PREFIX.$table_id;
			$category_id = wptableeditor_load::categorys($table_id);
			$table_limit = self::get_column($table_id, 'table_limit');
			$order_name_type = wptableeditor_load::order_name_type($table_id);
			$column_total = $order_name_type['count'];
			$order_login = $order_name_type['login'];
			$order_title = $order_name_type['title'];
			$order_ids = $order_name_type['id'];
			$order_name = $order_name_type['order_names'];
			$order_id = $order_name_type['order_id'];
			$query = "SELECT column_id, column_order, column_custom FROM `{$table}` WHERE column_status = %s ORDER BY column_id ASC";
			$result = $wpdb->get_results($wpdb->prepare($query, 'active'), ARRAY_A);
			$output = array();
			$array = array();
			$arrays = array();
			$data = array();
			foreach($result as $row){
				$array[$row['column_id']] = $row['column_order'];
				$arrays[$row['column_id']] = $row['column_custom'];
			}
			$table_types = wptableeditor_load::post_type($xs_type);
			$column_status = wptableeditor_load::column_status($table_id);
			foreach(wptableeditor_load::post($xs_type, $category_id, $table_limit) as $row){
				$row = array_values($row);
				if(wptableeditor_load::row_status($table, $row[0])){
					$sub_array = array();
					$sub_array[] = $row[0];
					for ($x = 1; $x <= $column_total - 1; $x++){
						if(in_array($order_id[$x], $column_status)){
							if($order_login[$x] === 'yes' && (!is_user_logged_in() || !wptableeditor_load::column_roles($table_id, $order_ids[$x]))){
								$sub_array[] = $order_title[$x];
							}elseif(isset($order_name[$x]) && isset($table_types[$order_name[$x]]) && $table_types[$order_name[$x]] === 'custom'){
								if(isset($arrays[$row[0]]) && $arrays[$row[0]] !== null){
									$sub_array[] = html_entity_decode($arrays[$row[0]]);
								}else{
									$sub_array[] = '';
								}
							}else{
								if(isset($order_name[$x])){
									$value = str_replace('column_', '', $order_name[$x]);
								}
								if(isset($value) && isset($row[$value])){
									$sub_array[] = $row[$value];
								}else{
									$sub_array[] = '';
								}
							}
						}
					}
					if(!isset($array[$row[0]]) || $array[$row[0]] === 0){
						$sub_array[] = $row[0];
					}else{
						$sub_array[] = $array[$row[0]];
					}
					$data[] = array_values($sub_array);
				}
			}
			$output = array("data"	=>	$data);
			if($dataSet !== true){
				self::json_file($output, $table_id, $delete);
			}else{
				return wp_json_encode($data);
			}
		}
		public static function row_action($table_id, $delete = true, $dataSet = false){
			global $wpdb;
			$table_jsonsave = self::get_column($table_id, 'table_jsonsave');
			if($table_jsonsave == 'no' && $dataSet !== true){
				return;
			}elseif($table_jsonsave == 'yes' && $dataSet === true){
				return self::json_actions($table_id);
			}
			$table = WPTABLEEDITOR_PREFIX.$table_id;
			$order_name_type = wptableeditor_load::order_name_type($table_id);
			$table_limit = self::get_column($table_id, 'table_limit');
			$order_names = $order_name_type['names'];
			$order_types = $order_name_type['types'];
			$order_login = $order_name_type['login'];
			$order_title = $order_name_type['title'];
			$order_id = $order_name_type['id'];
			$query = "SELECT * FROM `{$table}` WHERE column_status = %s ORDER BY column_id ASC";
			if($table_limit >= 0){
				$query .= " LIMIT {$table_limit}";
			}
			$result = $wpdb->get_results($wpdb->prepare($query, 'active'), ARRAY_A);
			$data = array();
			$output = array();
			foreach($result as $row){
				$sub_array = array();
				$sub_array[] = $row['column_id'];
				for ($x = 1; $x <= count($row) - 2; $x++){
					if(isset($order_names[$x])){
						$column_name = $order_names[$x];
						$value = $row[$column_name];
						if($order_login[$x] === 'yes' && (!is_user_logged_in() || !wptableeditor_load::column_roles($table_id, $order_id[$x]))){
							$sub_array[] = $order_title[$x];
						}else{
							if($order_types[$x] === 'url'){
								$sub_array[] = make_clickable($value);
							}elseif($order_types[$x] === 'shortcode'){
								$shortcode = explode(' ', ltrim($value))[0];
								if(strpos($shortcode, "[") === 0 && shortcode_exists(trim($shortcode, "["))){
									$sub_array[] = do_shortcode($value);
								}else{
									$sub_array[] = $value;
								}
							}elseif($order_types[$x] === 'html'){
								$sub_array[] = htmlentities($value);
							}elseif($order_types[$x] === 'id'){
								$sub_array[] = $row['column_id'];
							}elseif($order_types[$x] === 'audio' && shortcode_exists('wptableeditor_audio')){
								$sub_array[] = do_shortcode('[wptableeditor_audio fileurl="'.$value.'"]');
							}else{
								$sub_array[] = html_entity_decode($value);
							}
						}
					}
				}
				if($row['column_order'] === 0){
					$sub_array[] = $row['column_id'];
				}else{
					$sub_array[] = $row['column_order'];
				}
				$data[] = array_values($sub_array);
			}
			$output = array("data"	=>	$data);
			if($dataSet !== true){
				self::json_file($output, $table_id, $delete);
			}else{
				return wp_json_encode($data);
			}
		}
		public static function woo_action($table_id, $delete = true, $dataSet = false){
			global $wpdb;
			$table_jsonsave = self::get_column($table_id, 'table_jsonsave');
			if($table_jsonsave == 'no' && $dataSet !== true){
				return;
			}elseif($table_jsonsave == 'yes' && $dataSet === true){
				return self::json_actions($table_id);
			}
			$table = WPTABLEEDITOR_PREFIX.$table_id;
			$category_id = wptableeditor_load::categorys($table_id);
			$table_limit = self::get_column($table_id, 'table_limit');
			$order_name_type = wptableeditor_load::order_name_type($table_id);
			$column_total = $order_name_type['count'];
			$order_login = $order_name_type['login'];
			$order_title = $order_name_type['title'];
			$order_ids = $order_name_type['id'];
			$order_name = $order_name_type['order_names'];
			$order_id = $order_name_type['order_id'];
			$query = "SELECT column_id, column_order, column_custom FROM `{$table}` WHERE column_status = %s ORDER BY column_id ASC";
			$result = $wpdb->get_results($wpdb->prepare($query, 'active'), ARRAY_A);
			$array = array();
			$arrays = array();
			$data = array();
			$output = array();
			foreach($result as $row){
				$array[$row['column_id']] = $row['column_order'];
				$arrays[$row['column_id']] = $row['column_custom'];
			}
			$table_types = wptableeditor_load::product_types();
			$column_status = wptableeditor_load::column_status($table_id);
			foreach(wptableeditor_load::woocommerce($category_id, $table_limit) as $row){
				$row = array_values($row);
				if(wptableeditor_load::row_status($table, $row[0])){
					$sub_array = array();
					$sub_array[] = $row[0];
					for ($x = 1; $x <= $column_total - 1; $x++){
						if(in_array($order_id[$x], $column_status)){
							$value = str_replace('column_', '', $order_name[$x]);
							if($order_login[$x] === 'yes' && (!is_user_logged_in() || !wptableeditor_load::column_roles($table_id, $order_ids[$x]))){
								$sub_array[] = $order_title[$x];
							}elseif(isset($table_types[$order_name[$x]]) && $table_types[$order_name[$x]] === 'custom'){
								if(isset($arrays[$row[0]]) && $arrays[$row[0]] !== null){
									$sub_array[] = html_entity_decode($arrays[$row[0]]);
								}else{
									$sub_array[] = '';
								}
							}else{
								$sub_array[] = $row[$value];
							}
						}
					}
					if(!isset($array[$row[0]]) || $array[$row[0]] === 0){
						$sub_array[] = $row[0];
					}else{
						$sub_array[] = $array[$row[0]];
					}
					$data[] = array_values($sub_array);
				}
			}
			$output = array("data"	=>	$data);
			if($dataSet !== true){
				self::json_file($output, $table_id, $delete);
			}else{
				return wp_json_encode($data);
			}
		}
		public static function woo_order($table_id, $delete = true, $dataSet = false){
			global $wpdb;
			$table_jsonsave = self::get_column($table_id, 'table_jsonsave');
			if($table_jsonsave == 'no' && $dataSet !== true){
				return;
			}elseif($table_jsonsave == 'yes' && $dataSet === true){
				return self::json_actions($table_id);
			}
			$table = WPTABLEEDITOR_PREFIX.$table_id;
			$order_name = wptableeditor_column::order_name($table_id);
			$order_id = wptableeditor_column::order_id($table_id);
			$category_id = wptableeditor_table::category($table_id);
			$output = array();
			$data = array();
			$column_total = wptableeditor_column::rowCount($table_id);
			$limit = wptableeditor_table::limit($table_id);
			$array = array();
			$query = "SELECT * FROM `{$table}` ORDER BY column_id ASC";
			$result = $wpdb->get_results($query);
			foreach($result as $row){
				$row = (array)$row;
				$array[$row['column_id']] = $row['column_order'];
				$arrays[$row['column_id']] = $row['column_custom'];
			}
			$table_types = wptableeditor_woocommerce::order_types();
			foreach(wptableeditor_woocommerce::order($category_id, $limit) as $row){
				$row = array_values($row);
				$xsnonce = wp_create_nonce('xs-table'.$table_id.'xs-row'.$row[0]);
				$sub_array = array();
				$sub_array[] = $row[0];
				for ($x = 1; $x <= $column_total; $x++){
					if(wptableeditor_column::status($table_id, $order_id[$x])){
						if(isset($table_types[$order_name[$x]]) && $table_types[$order_name[$x]] === 'custom'){
							if(isset($arrays[$row[0]]) && $arrays[$row[0]] !== null){
								$sub_array[] = html_entity_decode($arrays[$row[0]]);
							}else{
								$sub_array[] = '';
							}
						}else{
							$value = str_replace('column_', '', $order_name[$x]);
							$sub_array[] = $row[$value];
						}
					}
				}
				if(!isset($array[$row[0]]) || $array[$row[0]] === 0){
					$sub_array[] = $row[0];
				}else{
					$sub_array[] = $array[$row[0]];
				}
				$data[] = array_values($sub_array);
			}
			$output = array("data"	=>	$data);
			if($dataSet !== true){
				self::json_file($output, $table_id, $delete);
			}else{
				return wp_json_encode($data);
			}
		}
		public static function getContentWithCurl($url) {
		    $ch = curl_init($url);
		    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		    $content = curl_exec($ch);
		    curl_close($ch);
		    return ($content !== false) ? $content : '';
		}
		public static function json_action($table_id, $delete = true, $dataSet = false){
			global $wpdb;
			$table_jsonsave = self::get_column($table_id, 'table_jsonsave');
			$data = array();
			if($table_jsonsave == 'no' && $dataSet !== true){
				return;
			}elseif($table_jsonsave == 'yes' && $dataSet === true){
				return self::json_actions($table_id);
			}
			$query = "SELECT table_url, table_datasrc FROM ".WPTABLEEDITOR_TABLE." WHERE table_id = %d";
			$result = $wpdb->get_row($wpdb->prepare($query, $table_id), ARRAY_A);
			$table_url = $result['table_url'];
			$array = json_decode(self::getContentWithCurl($table_url), true);
			if(isset($array[$result['table_datasrc']])){
				$table_datasrc = $result['table_datasrc'];
			}elseif(isset($array['data'])){
				$table_datasrc = 'data';
			}elseif(isset($array['values'])){
				$table_datasrc = 'values';
			}elseif(isset($array['body'])){
				$table_datasrc = 'body';
			}else{
				$table_datasrc = '';
			}
			self::update_column($table_id, 'table_datasrc', $table_datasrc);
			if(isset($array[$table_datasrc])){
				$data = $array[$table_datasrc];
				$element = 1;
				foreach($data as &$value){
					array_push($value, $element);
					$element++;
				}
			}elseif(isset($array[0])){
				foreach($array as $row){
					$data[] = array_values($row);
				}
				$element = 1;
				foreach($data as &$value){
					array_push($value, $element);
					$element++;
				}
			}
			$output = array("data" => $data);
			if($dataSet !== true){
				self::json_file($output, $table_id, $delete);
			}else{
				return wp_json_encode($data);
			}
		}
		public static function json_actions($table_id){
			$table_jsonname = self::get_column($table_id, 'table_jsonname');
			$table_url = WPTABLEEDITOR_DATA_URL.$table_jsonname;
			$array = json_decode(self::getContentWithCurl($table_url), true);
			if(isset($array['data'])){
				return wp_json_encode($array['data']);
			}
		}
		public static function sheet_action($table_id, $delete = true, $dataSet = false){
			global $wpdb;
			$table_jsonsave = self::get_column($table_id, 'table_jsonsave');
			if($table_jsonsave == 'no' && $dataSet !== true){
				return;
			}elseif($table_jsonsave == 'yes' && $dataSet === true){
				return self::json_actions($table_id);
			}
			$query = "SELECT table_apikey, table_sheetid, table_sheetname, table_range FROM ".WPTABLEEDITOR_TABLE." WHERE table_id = %d";
			$result = $wpdb->get_row($wpdb->prepare($query, $table_id), ARRAY_A);
			$spreadsheets = sprintf('https://sheets.googleapis.com/v4/spreadsheets/%s/values/%s!%s?key=%s', $result['table_sheetid'], $result['table_sheetname'], $result['table_range'], $result['table_apikey']);
			$array = json_decode(self::getContentWithCurl($spreadsheets));
			$data = $array->values;
			$element = 1;
			foreach($data as &$value){
				array_push($value, $element);
				$element++;
			}
			$output = array("data"	=>	$data);
			if($dataSet !== true){
				self::json_file($output, $table_id, $delete);
			}else{
				return wp_json_encode($data);
			}
		}
		public static function json_file($output, $table_id, $delete = true){
			global $wpdb;
			$jsonname_new = wp_create_nonce('xs-table'.$table_id.time()).'.json';
			if($delete === true){
				self::json_delete_file($table_id);
			}
			@file_put_contents(WPTABLEEDITOR_DATA_PATH.$jsonname_new, wp_json_encode($output));
			$data = array(
				'table_jsonname' => $jsonname_new,
				'table_jsonsavedate' => time()
			);
			$wpdb->update(WPTABLEEDITOR_TABLE, $data, array('table_id' => $table_id));
		}
		public static function json_delete_file($table_id){
			if(self::get_column($table_id, 'table_jsonsave') == 'no'){
				return;
			}
			$jsonname = self::get_column($table_id, 'table_jsonname');
			if(!empty($jsonname) && file_exists(WPTABLEEDITOR_DATA_PATH.$jsonname)){
				@wp_delete_file(WPTABLEEDITOR_DATA_PATH.$jsonname);
			}
		}
		public static function put_action($table_id, $delete = true, $dataSet = false){
			$table_type = self::get_column($table_id, 'table_type');
			if($table_type === 'default'){
				$output = wptableeditor_load::row_action($table_id, $delete, $dataSet);
			}elseif(in_array($table_type, array('post', 'page'))){
				$output = wptableeditor_load::post_action($table_type, $table_id, $delete, $dataSet);
			}elseif($table_type === 'product'){
				$output = wptableeditor_load::woo_action($table_id, $delete, $dataSet);
			}elseif($table_type === 'order'){
				$output = wptableeditor_load::woo_order($table_id, $delete, $dataSet);
			}elseif($table_type === 'json'){
				$output = wptableeditor_load::json_action($table_id, $delete, $dataSet);
			}elseif($table_type === 'sheet'){
				$output = wptableeditor_load::sheet_action($table_id, $delete, $dataSet);
			}
			if($dataSet === true){
				return $output;
			}
		}
		public static function get_column($table_id, $column){
			global $wpdb;
			$query = "SELECT `{$column}` FROM ".WPTABLEEDITOR_TABLE." WHERE table_id = %d";
			$output = $wpdb->get_var($wpdb->prepare($query, $table_id));
			if(in_array($column, wptableeditor_load::tables())){
				if($output === 'yes'){
					return 'no';
				}
			}
			return $output;
		}
		public static function update_column($table_id, $column, $value){
			global $wpdb;
			$data = array(
				$column => $value
			);
			$wpdb->update(WPTABLEEDITOR_TABLE, $data, array('table_id' => $table_id));
		}
	}
	new wptableeditor_load();
}
?>