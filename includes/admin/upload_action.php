<?php

defined( 'ABSPATH' ) || exit;
wptableeditor_load::current_user_can();

if(isset($_POST['import_source']) && sanitize_text_field($_POST['import_source']) == 'file' && $_FILES['file']['name'] != ''){
	$file_array = explode(".", $_FILES['file']['name']);
	$extension = end($file_array);
	if(in_array($extension, array('csv', 'xls', 'xlsx'))){
		$phpversion = 8.0;
		if(phpversion() >= $phpversion){
			include WPTABLEEDITOR_PATH. '/vendor/phpspreadsheet/autoload.php';
		}else{
			$error = esc_html('Require a PHP version >= '.$phpversion);
		}
	}
}
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;

global $wp_filesystem;

if(isset($_POST['table_id']) && isset($_POST['import_type']) && isset($_POST['import_source'])){
	$table_id = (int) sanitize_text_field($_POST['table_id']);
	$import_type = sanitize_text_field($_POST['import_type']);
	$import_source = sanitize_text_field($_POST['import_source']);
	check_ajax_referer( 'xs-import'.$table_id, '_xsnonce' );
	check_admin_referer( 'xs-import'.$table_id, '_xsnonce' );
	wptableeditor_load::set_memory_limit();
	wptableeditor_load::set_max_execution_time();
	session_start();
	$error = '';
	$temp_data = array();
	$column_number = array();
	$header = array();
	$total_line = 0;
	$available = 0;
	if(in_array($import_source, array('file', 'url'))){
		if($import_source === 'file'){
			if($_FILES['file']['name'] != ''){
				$allowed_extension = array('csv', 'xls', 'xlsx', 'json', 'txt');
				$file_array = explode(".", $_FILES['file']['name']);
				$extension = end($file_array);
				if(in_array($extension, $allowed_extension)){
					$phpversion = 8.0;
					if(in_array($extension, array('csv', 'xls', 'xlsx')) && phpversion() < $phpversion){
						$error = esc_html('Require a PHP version >= '.$phpversion);
					}else{
						$file_return = wp_handle_upload($_FILES['file'], array('test_form' => false));
						if(isset($file_return['file'])){
							$tmp_name = $file_return['file'];
							wptableeditor_init::request_filesystem_credentials($tmp_name);
							if(in_array($extension, array('csv', 'xls', 'xlsx'))){
								if(in_array($extension, array('xls', 'xlsx'))){
									$reader = IOFactory::createReader(ucwords($extension));
									$spreadsheet = $reader->load($tmp_name);
									$worksheet = $spreadsheet->getActiveSheet();
									$data = $worksheet->toArray();
								}elseif($extension === 'csv'){
									$data = array_map('str_getcsv', array_filter(explode("\n", $wp_filesystem->get_contents($tmp_name))));
								}
								$header = $data[0];
								unset($data[0]);
								$total_line = count($data);
								wp_delete_file($tmp_name);
							}elseif(in_array($extension, array('json', 'txt'))){
								$data = json_decode($wp_filesystem->get_contents($tmp_name), true);
								if(isset($data['values']) || isset($data['body']) || isset($data['data'])){
									if(isset($data['values'])){
										$data['body'] = $data['values'];
									}elseif(isset($data['data'])){
										$data['body'] = $data['data'];
									}
									if(isset($data['header'])){
										$header = $data['header'];
									}else{
										$header = array_keys($data['body'][0]);
									}
									$total_line = count($data['body']);
								}elseif(isset($data[0])){
									$header = array_keys($data[0]);
									$total_line = count($data);
								}else{
									$error = esc_html__('Incorrect JSON format', 'wp-table-editor');
								}
							}
						}else{
							$error = esc_html__('File format not supported. Put this code in wp-config.php file. define( "ALLOW_UNFILTERED_UPLOADS", true );', 'wp-table-editor');
						}
					}
				}else{
					$error = esc_html__('Only CSV, XLS, XLSX or JSON file format is allowed', 'wp-table-editor');
				}
			}else{
				$error = esc_html__('Please Select File', 'wp-table-editor');
			}
		}elseif($import_source === 'url'){
			$tmp_name = esc_url($_POST['url']);
			wptableeditor_init::request_filesystem_credentials($tmp_name);
			$data = json_decode($wp_filesystem->get_contents($tmp_name), true);
			if(isset($data['values']) || isset($data['body']) || isset($data['data'])){
				if(isset($data['values'])){
					$data['body'] = $data['values'];
				}elseif(isset($data['data'])){
					$data['body'] = $data['data'];
				}
				if(isset($data['header'])){
					$header = $data['header'];
				}else{
					$header = array_keys($data['body'][0]);
				}
				$total_line = count($data['body']);
			}elseif(isset($data[0])){
				$header = array_keys($data[0]);
				$total_line = count($data);
			}else{
				$error = esc_html__('Incorrect JSON format', 'wp-table-editor');
			}
		}
		if($total_line > 0 && count($header) > 0){
			$preview = $total_line > 5 ? 5 : $total_line;
			$html = 'Preview the '.$preview.' first result entries (filtered from '.$total_line.' total entries)';
			$html .= '<table class="table table-bordered">';
			$html .= '<tr id="set_column_data">';
			for($count = 0; $count < count($header); $count++){
				$head = $header[$count];
				if(empty($header[$count]) && $header[$count] !== 0){
					$head = 'column_'.($count + 1);
				}
				$html .= '<th class="xs-mw-100px">'.$head.'<select name="set_column_data_'.$count.'" id="set_column_data_'.$count.'" class="form-control set_column_data" data-column_numbers="'.$count.'">';
				$html .= '<option value="">Select</option>';
				if($import_type === 'append'){
					$html .= wptableeditor_column::names($table_id);
				}else{
					$html .= '<option value="column_'.$count.'">'.$head.'</option>';
				}
				$html .= '</select></th>';
				$column_number[] = 'column_'.$count;
				$keys[] = $header[$count];
			}
			$html .= '</tr>';
			$limit = 0;
			$body = isset($data['body']) ? $data['body'] : $data;
			foreach($body as $row){
				$row = array_values($row);
				$limit++;
				if($limit <= 5){
					$html .= '<tr>';
					foreach($keys as $key => $value){
						if(isset($row[$key])){
							if(is_array($row[$key])){
								$html .= '<td>'.implode(",", $row[$key]).'</td>';
							}else{
								$html .= '<td>'.$row[$key].'</td>';
							}
						}else{
							$html .= '<td></td>';
						}
					}
					$html .= '</tr>';
				}
				foreach($keys as $key => $value){
					if(isset($row[$key])){
						if(is_array($row[$key])){
							$temp[$key] = implode(",", $row[$key]);
						}else{
							$temp[$key] = $row[$key];
						}
					}else{
						$temp[$key] = '';
					}
				}
				if(isset($temp)){
					$temp_data[] = array_values($temp);
				}
			}
			$html .= '</table>';
			$html .= '<div class="text-center xs-mt-15px">';
			$html .= '<button type="button" name="import_file" id="import_file" class="btn btn-secondary btn-xs" disabled>Import</button>';
			$html .= '</div>';
		}
	}elseif($import_source === 'database'){
		$select_table = sanitize_text_field($_POST['select_table']);
		$id = str_replace(WPTABLEEDITOR_PREFIX, '', $select_table);
		$xs_type = wptableeditor_table::type($id);
		if($xs_type && in_array($xs_type, array('post', 'page', 'product', 'order'))){
			$data = wptableeditor_column::all_columns($select_table, $id, $xs_type);
		}else{
			$data = wptableeditor_column::all_column($select_table);
		}
		$header = $data['column'];
		$total_line = count($data['row']);
		$preview = $total_line > 5 ? 5 : $total_line;
		$html = 'Preview the '.$preview.' first result entries (filtered from '.$total_line.' total entries)<p>';
		$html .= '<table class="table table-bordered">';
		$html .= '<tr>';
		for($count = 0; $count < count($header); $count++){
			$html .= '<th class="xs-mw-100px">'.$header[$count].'<select name="set_column_data_'.$count.'" id="set_column_data_'.$count.'" class="form-control set_column_data" data-column_numbers="'.$count.'">';
			$html .= '<option value="">Select</option>';
			if($import_type === 'append'){
				$html .= wptableeditor_column::names($table_id);
			}else{
				$html .= '<option value="column_'.$count.'">'.$header[$count].'</option>';
			}
			$html .= '</select></th>';
			$column_number[] = 'column_'.$count;
		}
		$html .= '</tr>';
		$limit = 0;
		foreach($data['row'] as $row){
			$limit++;
			if($limit <= 5){
				$html .= '<tr>';
				for($count = 0; $count < count($row); $count++){
					$html .= '<td>'.$row[$count].'</td>';
				}
				$html .= '</tr>';
			}
			$temp_data[] = $row;
		}
		$html .= '</table>';
		$html .= '<div class="text-center xs-mt-15px">';
		$html .= '<button type="button" name="import_file" id="import_file" class="btn btn-secondary btn-xs" disabled>Import</button>';
		$html .= '</div>';
	}
	if($error != ''){
		$output = array('error'	=>	$error);
	}else{
		if($import_type === 'append'){
			$available = wptableeditor_row::rowCount($table_id);
		}
		$_SESSION['temp_data'] = $temp_data;
		$_SESSION['column_number'] = $column_number;
		$_SESSION['table_rows'] = intval($available) + intval($total_line);
		$output = array(
			'column_number'	=>	count($header),
			'available'		=>	intval($available),
			'total_line'	=>	intval($total_line),
			'output'		=>	$html
		);
	}
	echo wp_json_encode($output);
}
?>