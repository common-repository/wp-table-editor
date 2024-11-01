<?php

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'wptableeditor_shortcode' ) ) {

	class wptableeditor_shortcode {

		protected $shown_tables = array();

		public function __construct(){
			add_action( 'wp_enqueue_scripts', array( $this, 'xs_enqueue_style' ), 99 );
			if( !shortcode_exists( 'wptableeditor' ) ) {
				add_shortcode( 'wptableeditor', array( $this, 'shortcode' ) );
			}
			if( !shortcode_exists( 'wptableeditor_htabs' ) ) {
				add_shortcode( 'wptableeditor_htabs', array( $this, 'horizontal_tabs' ) );
			}
			if( !shortcode_exists( 'wptableeditor_vtabs' ) ) {
				add_shortcode( 'wptableeditor_vtabs', array( $this, 'vertical_tabs' ) );
			}
			add_action( "wp_ajax_nopriv_row_getdatas_wpte", array( $this, 'row_getdatas_wpte' ));
			add_action( "wp_ajax_nopriv_row_search_wpte", array( $this, 'row_getdatas_wpte' ));
			add_action( "wp_ajax_row_getdatas_wpte", array( $this, 'row_getdatas_wpte' ));
			add_action( "wp_ajax_row_search_wpte", array( $this, 'row_getdatas_wpte' ));
		}

		public function row_getdatas_wpte(){
			global $wpdb;
			if(isset($_POST['xs_type'])){
				if($_POST['xs_type'] === 'default'){
					include_once 'row_action.php';
				}elseif($_POST['xs_type'] === 'product'){
					include_once 'woo_action.php';
				}elseif(in_array($_POST['xs_type'], array('post', 'page'))){
					include_once 'post_action.php';
				}
			}
			wp_die();
		}

		function xs_enqueue_style(){
			if(!is_admin()){
				wp_register_style( 'xsdatatables', WPTABLEEDITOR_VENDOR. 'datatables/datatables.min.css' );
				wp_register_style( 'xsstyle', WPTABLEEDITOR_ASSETS. 'css/style.css' );
				wp_register_style( 'xsfront', WPTABLEEDITOR_ASSETS. 'css/front.css' );
				wp_register_style( 'xstabs', WPTABLEEDITOR_ASSETS. 'css/tabs.css' );
				wp_register_style( 'xsshortcode', false );
				wp_register_script( 'xsdatatables', WPTABLEEDITOR_VENDOR. 'datatables/datatables.min.js', array('jquery') );
				wp_register_script( 'xsmoment', WPTABLEEDITOR_VENDOR. 'moment/moment.min.js' );
				wp_register_script( 'xsshortcode', WPTABLEEDITOR_ASSETS. 'js/shortcode.js' );
				wp_register_script( 'xstabs', WPTABLEEDITOR_ASSETS. 'js/tabs.js', array('jquery') );
			}
		}

		function html_id($string, $table_id){
			$html_id = $string."_".$table_id;
			if ( ! isset( $this->shown_tables[ $html_id ] ) ) {
				$this->shown_tables[ $html_id ] = array(
					'count'		=> 0,
					'instances' => array(),
				);
			}
			$this->shown_tables[ $html_id ]['count']++;
			$count = $this->shown_tables[ $html_id ]['count'];
			if ( $count > 1 ) {
				$html_id .= "-no-{$count}";
			}
			return $html_id;
		}

	    function custom_style(){
			if(!is_admin()){
		        $options     = get_option( 'xs_custom_css' );
		        $raw_content = isset( $options['xscss-content'] ) ? $options['xscss-content'] : '';
		        $content     = stripslashes( $raw_content );
		        $content     = str_replace( '&gt;', '>', $content );
		        $output = strip_tags( $content );
				return wp_kses_post($output);
			}
	    }

	    function general_style($html_id, $width, $unit, $border, $fontsize, $fontfamily){
			if(!is_admin()){
		        $output = '';
		        $output1 = '';
		        $output2 = '';
		        $output3 = '';
		        if(!empty($width) && !empty($unit)){
		        	$output3 .= '#'.str_replace("wptableeditor", "xscontainer", $html_id).'{';
					$output3 .= 'margin-left: auto !important;';
					$output3 .= 'margin-right: auto !important;';
					$output3 .= 'width: '.$width.$unit.';';
					$output3 .= '}';
		        }
		        if(!empty($border) && $border == 'no'){
		        	$output2 .= '#'.$html_id.'_wrapper{';
					$output2 .= 'border: none;';
					$output2 .= 'box-shadow: none;';
					$output2 .= '}';
		        }
		        if(!empty($fontsize)){
		        	$output1 .= 'font-size:'.$fontsize.'px !important;';
		        }else{
		        	$output1 .= 'font-size:15px !important;';
		        }
		        if(!empty($fontfamily)){
		        	$output1 .= 'font-family:'.htmlspecialchars(stripslashes($fontfamily)).' !important;';
		        }else{
		        	$output1 .= 'font-family:"Helvetica Neue",Helvetica,Arial,sans-serif !important;';
		        }
				if(!empty($output1)){
					 $output .= '.'.$html_id.'{';
					 $output .= $output1;
					 $output .= '}';
				}
				if(!empty($output2)){
					 $output .= $output2;
				}
				if(!empty($output3)){
					 $output .= $output3;
				}
				return wp_kses_post($output);
			}
	    }

	    function header_style($html_id, $headerfontsize, $headerfontfamily, $headerfontweight, $headerfontstyle, $headerbackgroundcolor, $headerfontcolor, $headersortingcolor, $hidethead){
			if(!is_admin()){
		        $output = '';
		        $output1 = '';
		        if(!empty($headerfontsize)){
		        	$output1 .= 'font-size:'.$headerfontsize.'px !important;';
		        }else{
		        	$output1 .= 'font-size:inherit !important;';
		        }
		        if(!empty($headerfontfamily)){
		        	$output1 .= 'font-family:'.htmlspecialchars(stripslashes($headerfontfamily)).' !important;';
		        }else{
		        	$output1 .= 'font-family:inherit !important;';
		        }
		        if(!empty($headerfontweight)){
		        	$output1 .= 'font-weight:'.$headerfontweight.' !important;';
		        }
		        if(!empty($headerfontstyle)){
		        	$output1 .= 'font-style:'.$headerfontstyle.' !important;';
		        }
				if(!empty($headerbackgroundcolor)){
					$output1 .= 'background-color:'.$headerbackgroundcolor.' !important;';
				}else{
					$output1 .= 'background-color:#d9edf7 !important;';
				}
				if(!empty($headerfontcolor)){
					$output1 .= 'color:'.$headerfontcolor.' !important;';
				}
				if(!empty($output1)){
					 $output .= '.'.$html_id.'_thead, .'.$html_id.'_thead th, .'.$html_id.'_tfoot, .'.$html_id.'_tfoot th{';
					 $output .= $output1;
					 $output .= '}';
				}
				if(!empty($hidethead) && $hidethead == 'yes'){
					 $output .= '.'.$html_id.'_head {';
					 $output .= 'display: none !important;';
					 $output .= '}';
					 $output .= '.'.$html_id.'_tbody .dtr-title {';
					 $output .= 'display: none !important;';
					 $output .= '}';
				}
				if(!empty($headersortingcolor)){
					$output .= '.'.$html_id.'_thead .sorting_asc, .'.$html_id.'_thead .sorting_desc, .'.$html_id.'_thead .sorting:hover{background-color:'.$headersortingcolor.' !important;}';
				}elseif(empty($headerbackgroundcolor)){
					$output .= '.'.$html_id.'_thead .sorting_asc, .'.$html_id.'_thead .sorting_desc, .'.$html_id.'_thead .sorting:hover{background-color:#aad8ec !important;}';
				}
				return wp_kses_post($output);
			}
	    }

	    function body_style($html_id, $bodyfontsize, $bodyfontfamily, $bodyfontweight, $bodyfontstyle, $evenbackgroundcolor, $oddbackgroundcolor, $evenfontcolor, $oddfontcolor, $evenlinkcolor, $oddlinkcolor, $buttonbackgroundcolor, $buttonfontcolor){
			if(!is_admin()){
		        $output = '';
				$output1 = '';
				$output2 = '';
				$output3 = '';
				$output4 = '';
		        if(!empty($bodyfontsize)){
		        	$output1 .= 'font-size:'.$bodyfontsize.'px !important;';
		        }else{
		        	$output1 .= 'font-size:inherit !important;';
		        }
		        if(!empty($bodyfontfamily)){
		        	$output1 .= 'font-family:'.htmlspecialchars(stripslashes($bodyfontfamily)).' !important;';
		        }else{
		        	$output1 .= 'font-family:inherit !important;';
		        }
		        if(!empty($bodyfontweight)){
		        	$output1 .= 'font-weight:'.$bodyfontweight.' !important;';
		        }
		        if(!empty($bodyfontstyle)){
		        	$output1 .= 'font-style:'.$bodyfontstyle.' !important;';
		        }
				if(!empty($output1)){
					 $output .= '.'.$html_id.'_tbody{';
					 $output .= $output1;
					 $output .= '}';
				}
				if(!empty($oddbackgroundcolor)){
					$output2 .= 'background-color:'.$oddbackgroundcolor.' !important;';
				}
				if(!empty($oddfontcolor)){
					$output2 .= 'color:'.$oddfontcolor.' !important;';
				}
				if(!empty($output2)){
					 $output .= '.'.$html_id.'_tbody tr.odd{';
					 $output .= $output2;
					 $output .= '}';
				}
				if(!empty($evenbackgroundcolor)){
					$output3 .= 'background-color:'.$evenbackgroundcolor.' !important;';
				}
				if(!empty($evenfontcolor)){
					$output3 .= 'color:'.$evenfontcolor.' !important;';
				}
				if(!empty($output3)){
					 $output .= '.'.$html_id.'_tbody tr.even{';
					 $output .= $output3;
					 $output .= '}';
				}
				if(!empty($oddlinkcolor)){
					$output .= '.'.$html_id.'_tbody tr.odd a{color: '.$oddlinkcolor.';}';
				}
				if(!empty($evenlinkcolor)){
					$output .= '.'.$html_id.'_tbody tr.even a{color: '.$evenlinkcolor.';}';
				}
				if(!empty($buttonbackgroundcolor)){
					$output4 .= 'background-color:'.$buttonbackgroundcolor.' !important;';
				}
				if(!empty($buttonfontcolor)){
					$output4 .= 'color:'.$buttonfontcolor.' !important;';
				}
				if(!empty($output4)){
					 $output .= '#'.$html_id.'_search, #'.$html_id.'_search, #'.$html_id.'_reset{';
					 $output .= $output4;
					 $output .= '}';
				}
				return wp_kses_post($output);
			}
	    }

	    function column_style($html_id, $backgroundcolor, $fontcolor, $fontweight, $fontstyle, $minwidth){
			if(!is_admin()){
		        $output = '';
				foreach($backgroundcolor as $column => $color){
					$output .= '.'.$html_id.'_tbody .'.$html_id.'_column_'.$column.'{background-color:'.$color.' !important;}';
				}
				foreach($fontcolor as $column => $color){
					$output .= '.'.$html_id.'_tbody .'.$html_id.'_column_'.$column.'{color:'.$color.' !important;}';
				}
				foreach($fontweight as $column => $weight){
					$output .= '.'.$html_id.'_tbody .'.$html_id.'_column_'.$column.'{font-weight:'.$weight.' !important;}';
				}
				foreach($fontstyle as $column => $style){
					$output .= '.'.$html_id.'_tbody .'.$html_id.'_column_'.$column.'{font-style:'.$style.' !important;}';
				}
				foreach($minwidth as $column => $width){
					$output .= '.'.$html_id.'_tbody .'.$html_id.'_column_'.$column.'{min-width:'.$width.'px !important;}';
				}
				$output .= '.'.$html_id.'_tbody .'.$html_id.'_column_0{font-weight:400 !important;font-style:Normal !important;}';
				return wp_kses_post($output);
			}
	    }

		function horizontal_tabs($atts){
			static $count = 0;
			if(is_admin() || empty($atts) || !class_exists('wptableeditor_init')){
				return;
			}
			ob_start();
			$table_id = array();
			if(isset($atts['id'])){
				$table_id = explode(',', $atts['id']);
			}
			if( ! wp_style_is( 'xstabs' ) ) wp_enqueue_style( 'xstabs' );
			if( ! wp_script_is( 'xstabs' ) ) wp_enqueue_script( 'xstabs' );
			$count++;
			$output = '';
			$output .= '<div class="xscontainer_tab">';
			$output .= '<div id="xscontainer_tab_'.$count.'">';
			$output .= '<nav>';
			$output .= '<ul>';
			foreach($table_id as $id){
				$output .= '<li><a href="#section-'.$count.'-'.$id.'"><span>'.wptableeditor_load::table_name($id).'</span></a></li>';
			}
			$output .= '</ul>';
			$output .= '</nav>';
			$output .= '<div class="xscontainer_content">';
			foreach($table_id as $id){
				$output .= '<section id="section-'.$count.'-'.$id.'">';
				$output .= '<h4 class="xssection_title">'.wptableeditor_load::table_name($id).'</h4>';
				$output .= do_shortcode('[wptableeditor id="'.$id.'"]');
				$output .= '</section>';
			}
			$output .= '</div>';
			$output .= '</div>';
			$output .= '</div>';
			ob_get_clean();
			return $output;
		}

		function vertical_tabs($atts){
			static $count = 0;
			if(is_admin() || empty($atts)){
				return;
			}
			ob_start();
			$table_id = array();
			if(isset($atts['id'])){
				$table_id = explode(',', $atts['id']);
			}
			if( ! wp_style_is( 'xstabs' ) ) wp_enqueue_style( 'xstabs' );
			if( ! wp_script_is( 'xstabs' ) ) wp_enqueue_script( 'xstabs' );
			$count++;
			if($count > 10){
				return;
			}
			$output = '';
			$output .= '<div id="xscontainer_tabs_'.$count.'" class="xscontainer_tabs">';
			$output .= '<ul>';
			foreach($table_id as $key => $id){
				if($key === 0){
					$output .= '<li><a href="#sections-'.$count.'-'.$id.'" class="tab-link_'.$count.' active"><span>'.wptableeditor_load::table_name($id).'</span></a></li>';
				}else{
					$output .= '<li><a href="#sections-'.$count.'-'.$id.'" class="tab-link_'.$count.'"><span>'.wptableeditor_load::table_name($id).'</span></a></li>';
				}
			}
			$output .= '</ul>';
			foreach($table_id as $key => $id){
				if($key === 0){
					$output .= '<section id="sections-'.$count.'-'.$id.'" class="tab-body_'.$count.' entry-content active active-content">';
				}else{
					$output .= '<section id="sections-'.$count.'-'.$id.'" class="tab-body_'.$count.' entry-content">';
				}
				$output .= do_shortcode('[wptableeditor id="'.$id.'"]');
				$output .= '</section>';
			}
			$output .= '</div>';
			ob_get_clean();
			return $output;
		}

		function shortcode($atts){
			static $count = 0;
			if(is_admin() || empty($atts)){
				return;
			}
			if(isset($atts['id'])){
				$table_id = intval($atts['id'], 10);
			}else{
				if(is_user_logged_in() && current_user_can('manage_options')){
					return '<p>' . esc_html__('Please enter the identifier of the table.', 'wp-table-editor') . '</p>';
				}
			}
			$html_id = $this->html_id('wptableeditor', $table_id);
			$xscontainer_id = $this->html_id('xscontainer', $table_id);
            include WPTABLEEDITOR_INCLUDES. 'init.php';
            if($xs_table_value === false || $xs_column_data === false){
            	if(is_user_logged_in() && current_user_can('manage_options')){
            		return '<p>' . esc_html__('There is no table associated with this shortcode.', 'wp-table-editor') . '</p>';
            	}else{
            		return;
            	}
            }
			if($table_status === 'active'){
				ob_start();
				if(isset($table_restriction) && $table_restriction === 'yes'){
					if(!is_user_logged_in() || !$check_roles){
						//return '<p>' . esc_html__('You do not have sufficient permissions to access this content.', 'wp-table-editor') . '</p>';
						return;
					}
				}
				if($table_type === 'order' || !in_array($table_type, array('default', 'product', 'page', 'post', 'json', 'sheet')) || ($table_type === 'product' && !function_exists('wc_get_products'))){
					return;
				}
				$dataSet = '';
				if($table_type === 'json'){
					$dataSet = wptableeditor_load::put_action($table_id, false, true);
				}
				$count++;
	            if(is_user_logged_in() && current_user_can('manage_options') && $count > 100){
	                return '<p>' . esc_html__("You can't use more than 100 shortcodes.", 'wp-table-editor') . '</p>';
	            }
				if(isset($table_responsive) && $table_responsive === 'collapse'){
					$responsive = "true";
					$scrollX = "false";
				}elseif($table_responsive === 'flip'){
					$responsive = "false";
					$scrollX = "false";
				}else{
					$responsive = "false";
					$scrollX = "true";
				}
				if(isset($table_scrolly) && $table_scrolly <= 0){
					$scrollY = "false";
				}else{
					$scrollY = $table_scrolly;
				}
				if(isset($table_paging) && $table_paging === 'yes'){
					$paging = "true";
				}else{
					$paging = "false";
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
				$columns = '['.$column_width.',null]';
				if(in_array($table_type, array('json', 'sheet'))){
					$columns = '['.$column_point.']';
				}
				if(!isset($table_dom) || empty($table_dom)){
					$table_dom = 'lcBfrtip';
				}
				$table_doms = str_replace(["l","c"], '', $table_dom);
				$select_default = '';
				$filter_default = array();
				if(isset($column_default) && is_array(json_decode($column_default))){
					foreach(json_decode($column_default) as $column){
						$select_default .= '<"#'.$html_id.'_selects_'.$column.'.dataTables_length">';
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
				if(isset($table_group) && (int) $table_group > 0 ){
					$rowGroup = '{"dataSrc": '.$table_group.'}';
					$group = $orderFixed;
					$sortingtype = "false";
				}else{
					$rowGroup = "false";
					$group = "false";
					$sortingtype = $table_sortingtype;
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
				$inline_script = array(
					'ajax_url' => admin_url( 'admin-ajax.php' ),
					'table_id' => $table_id,
					'html_id' => $html_id,
					'table_type' => $table_type,
					'table_url' => $table_url,
					'table_datasrc' => $table_datasrc,
					'spreadsheets' => $spreadsheets,
					'table_pagination' => '"'.$table_pagination.'"',
					'pagination_simple' => '"simple"',
					'serverSide' => $serverSide,
					'table_serverside' => $table_serverside,
					'responsive' => $responsive,
					'table_responsive' => $table_responsive,
					'table_responsivetype' => $table_responsivetype,
					'scrollX' => $scrollX,
					'scrollY' => $scrollY,
					'table_fixedleft' => $table_fixedleft,
					'table_fixedright' => $table_fixedright,
					'header' => $header,
					'footer' => $footer,
					'select' => $select,
					'dataSet' => $dataSet,
					'dom' => $dom,
					'table_visibility' => $table_visibility,
					'stateSave' => $stateSave,
					'table_button' => $table_button,
					'table_keytable' => $table_keytable,
					'table_select' => $table_select,
					'table_searchbuilder' => $table_searchbuilder,
					'table_searchpanes' => $table_searchpanes,
					'column_default' => $column_default,
					'column_filters' => $column_filters,
					'column_show' => $column_show,
					'table_filter' => $table_filter,
					'filter_footer' => $filter_footer,
					'filter_default' => $filter_default,
					'column_hidden' => $column_hidden,
					'column_none' => $column_none,
					'table_sortingtype' => $sortingtype,
					'table_orderfixed' => $table_orderfixed,
					'table_footer' => $table_footer,
					'pagelength' => $pagelength,
					'paging' => $paging,
					'table_length' => $table_length,
					'table_createdrow' => $table_createdrow,
					'column_search' => wp_json_encode($column_search),
					'column_width' => $column_width,
					'columns' => $columns,
					'table_hover' => $table_hover,
					'table_ordercolumn' => $table_ordercolumn,
					'column_priority' => wp_json_encode($column_priority),
					'column_optionlimit' => $column_optionlimit,
					'column_characterlimit' => $column_characterlimit,
					'column_render' => $column_render,
					'column_createdcell' => $column_createdcell,
					'column_left' => $column_left,
					'column_center' => $column_center,
					'column_right' => $column_right,
					'column_nowrap' => $column_nowrap,
					'names' => $names,
					'desktop' => wp_json_encode($column_desktop),
					'tablet_l' => wp_json_encode($column_tablet_l),
					'tablet_p' => wp_json_encode($column_tablet_p),
					'mobile_l' => wp_json_encode($column_mobile_l),
					'mobile_p' => wp_json_encode($column_mobile_p),
					'column_orderable' => $column_orderable,
					'column_searchable' => $column_searchable,
					'column_total' => wp_json_encode($column_total),
					'rowGroup' => $rowGroup,
					'group' => $group,
					'column_count' => $column_count,
					'column_index' => wp_json_encode($column_index),
					'xsnonce' => wp_create_nonce('xs-table'.$table_id.'_front')
				);
				$general_style = self::general_style($html_id, $table_width, $table_unit, $table_border, $table_fontsize, $table_fontfamily);
				$header_style = self::header_style($html_id, $table_headerfontsize, $table_headerfontfamily, $table_headerfontweight, $table_headerfontstyle, $table_headerbackgroundcolor, $table_headerfontcolor, $table_headersortingcolor, $table_hidethead);
				$body_style = self::body_style($html_id, $table_bodyfontsize, $table_bodyfontfamily, $table_bodyfontweight, $table_bodyfontstyle, $table_evenbackgroundcolor, $table_oddbackgroundcolor, $table_evenfontcolor, $table_oddfontcolor, $table_evenlinkcolor, $table_oddlinkcolor, $table_buttonbackgroundcolor, $table_buttonfontcolor);
				$column_style = self::column_style($html_id, $column_backgroundcolor, $column_fontcolor, $column_fontweight, $column_fontstyle, $column_minwidth);
				$inline_style = self::custom_style().$general_style.$header_style.$body_style.$column_style;
				if( wp_script_is( 'tablepress-datatables' ) ) wp_dequeue_script( 'tablepress-datatables' );
				if( ! wp_style_is( 'xsdatatables' ) ) wp_enqueue_style( 'xsdatatables' );
				if( ! wp_style_is( 'xsstyle' ) ) wp_enqueue_style( 'xsstyle' );
				if( ! wp_style_is( 'xsfront' ) ) wp_enqueue_style( 'xsfront' );
				if( ! wp_style_is( 'xsshortcode' ) ) wp_enqueue_style( 'xsshortcode' );
				if( ! wp_style_is( 'dashicons' ) ) wp_enqueue_style( 'dashicons' );
				if( ! wp_script_is( 'xsdatatables' ) ) wp_enqueue_script( 'xsdatatables' );
				if( ! wp_script_is( 'xsmoment' ) ) wp_enqueue_script( 'xsmoment' );
				if( ! wp_script_is( 'xsshortcode' ) ) wp_enqueue_script( 'xsshortcode' );
				wp_add_inline_script( 'xsshortcode', 'var xs_ajax_shortcode'.$count.' = '. wp_json_encode($inline_script), 'before' );
				wp_localize_script( 'xsshortcode', 'xs_ajax_localize', wptableeditor_load::localize() );
				wp_add_inline_style( 'xsshortcode', $inline_style );
				$output = '';
				$output .= '<div class="xscontainer" id="'.$xscontainer_id.'">';
				$output .= '<div class="'.$html_id.'">';
				$output .= '<table class="stripe xs-w-100 xs-b-spacing xs-m-bottom" id="'.$html_id.'">';
					$output .= '<thead class="'.$html_id.'_thead">';
					if(empty($dataSet)){
						$output .= '<tr class="'.$html_id.'_head" id="'.$html_id.'_head" style="display:none">';
						$i = 0;
						$output .= '<th class="'.$html_id.'_head_'.$i.'">ID</th>';
						foreach($column_names as $_names){
							$i = $i + 1;
							$output .= '<th class="'.$html_id.'_head_'.$i.'" id="'.$html_id.'_head_'.$i.'">'.$_names.'</th>';
						}
						if(!in_array($table_type, array('json', 'sheet'))){
							$output .= '<th class="'.$html_id.'_head_'.$i.'"></th>';
						}
						$output .= '</tr>';
					}
					$output .= '</thead>';
					$output .= '<tbody class="'.$html_id.'_tbody"></tbody>';
					if($table_footer === 'yes' /*&& empty($dataSet)*/){
						$i = 0;
						$output .= '<tfoot class="'.$html_id.'_tfoot" id="'.$html_id.'_tfoot" style="display:none"><tr class="'.$html_id.'_foot">';
							$output .= '<th class="'.$html_id.'_foot_'.$i.'">ID</th>';
							foreach($column_names as $_names){
								$i = $i + 1;
								$output .= '<th class="'.$html_id.'_foot_'.$i.'" id="'.$html_id.'_foot_'.$i.'">'.$_names.'</th>';
							}
							if(!in_array($table_type, array('json', 'sheet'))){
								$output .= '<th></th>';
							}
						$output .= '</tr></tfoot>';
					}
				$output .= '</table>';
				$output .= '</div>';
				$output .= '</div>';
				if(is_user_logged_in() && current_user_can('manage_options')){
					$edit_table_url = admin_url('admin.php?page=wptableeditor&tab=row&table_id='.$table_id.'&_xsnonce='.wp_create_nonce('xs-table'.$table_id));
					$output .= '<a href="'.esc_url($edit_table_url).'" rel="nofollow">Edit</a>';
				}
				ob_get_clean();
				return $output;
			}else{
				if(is_user_logged_in() && current_user_can('manage_options')){
					return '<p>' . esc_html__('The ID of this shortcode has been disabled.', 'wp-table-editor') . '</p>';
				}
			}
		}
	}
	new wptableeditor_shortcode();
}