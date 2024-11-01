<?php

defined( 'ABSPATH' ) || exit;
wptableeditor_load::current_user_can();

global $table_id;
check_ajax_referer( 'xs-table'.$table_id, '_xsnonce' );
check_admin_referer( 'xs-table'.$table_id, '_xsnonce' );
$_xsnonce = wp_create_nonce('xs-import'.$table_id);

?>
<div class="xscontainer">
	<h1 class="text-center" id="xs_import_label"><?php esc_html_e('Import from a CSV, XLS, XLSX, JSON File or Database', 'wp-table-editor'); ?></h1>
	<span id="message"></span>
	<div class="panel panel-default">
		<div class="panel-heading">
			<div class="row">
				<div class="panel_heading_left">
					<h5 class="panel-title"><b><?php echo esc_html(wptableeditor_table::name($table_id)); ?></b></h5>
				</div>
			</div>
		</div>
		<div class="panel-body">
			<form id="xs_import_form" method="POST" enctype="multipart/form-data" class="form-horizontal">
				<div class="row">
					<div class="col-6 col-md-3">
					<label><b><?php esc_html_e('Type', 'wp-table-editor'); ?></b></label>
					<select name="import_type" id="import_type" class="form-select" required >
						<option value=""><?php esc_html_e('Select', 'wp-table-editor'); ?></option>
						<option value="replace"><?php esc_html_e('Replace existing table', 'wp-table-editor'); ?></option>
						<option value="append"><?php esc_html_e('Append rows to existing table', 'wp-table-editor'); ?></option>
					</select>
					</div>
					<div class="col-6 col-md-3">
						<label><b><?php esc_html_e('Number of Columns', 'wp-table-editor'); ?></b></label>
						<input type="number" name="column_number" id="column_number" min="1" max="20" value="1" class="form-control" required disabled/>
					</div>
					<div class="col-6 col-md-3">
						<label><b><?php esc_html_e('Source Type', 'wp-table-editor'); ?></b></label>
					<select name="import_source" id="import_source" class="form-select" required disabled>
						<option value="file">File</option>
						<option value="url">JSON url</option>
						<option value="database">Database</option>
					</select>
					</div>
					<div class="col-6 col-md-3">
						<label><b><?php esc_html_e('Select', 'wp-table-editor'); ?></b></label>
						<input type="file" class="form-control" name="file" id="file" disabled/>
						<select name="select_table" id="select_table" class="form-select xs-d-none">
							<option value=""><?php esc_html_e('Select', 'wp-table-editor'); ?></option>
							<?php echo wp_kses(wptableeditor_import::table(), array('option' => array('value' => array()))); ?>
						</select>
						<input type="url" name="url" id="url" class="form-control xs-d-none"/>
					</div>
				</div>
				<div class="form-group text-center xs-mt-15px">
					<input type="hidden" name="table_id" value="<?php echo esc_attr($table_id); ?>" />
					<input type="hidden" name="hidden_field" id="hidden_field" />
					<input type="hidden" name="_xsnonce" id="_xsnonce" value="<?php echo esc_attr($_xsnonce); ?>"/>
					<input type="submit" name="upload_file" id="upload_file" class="btn btn-secondary btn-xs" value="Load" disabled/><img id="spin_upload_file" class="xs-mh-spin xs-d-none" src="<?php echo esc_url(admin_url('images/spinner-2x.gif')); ?>" alt="...">
				</div>
			</form>
			<div class="form-group xs-mtb-15px xs-d-none" id="process_xs">
				<div class="progress mb-0">
					<div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" aria-valuemin="0" aria-valuemax="100">
						<span id="process_data">0</span> - <span id="total_data">0</span>
					</div>
				</div>
			</div>
			<div class="table-responsive" id="process_area"></div>
		</div>
	</div>
</div>