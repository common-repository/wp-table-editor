<?php

defined( 'ABSPATH' ) || exit;
wptableeditor_load::current_user_can();

global $table_id, $table_type, $_xsnonce;

check_ajax_referer( 'xs-table'.$table_id, '_xsnonce' );
check_admin_referer( 'xs-table'.$table_id, '_xsnonce' );

?>
<div class="xscontainer">
	<span id="alert_column"></span>
	<div class="panel panel-default">
		<div class="panel-heading">
			<div class="row">
				<div class="panel_heading_left">
					<h5 class="panel-title"><b><?php echo esc_html(wptableeditor_table::name($table_id)); ?></b></h5>
				</div>
				<div class="panel_heading_right">
				<button type="button" name="add" id="column_add_wpte" data-toggle="modal" data-target="#columnModal" class="btn btn-success btn-xs"><span class="dashicons dashicons-plus"></span></button>
				<button type="button" name="column_multi_delete_wpte" id="column_multi_delete_wpte" class="btn btn-danger btn-xs"><span class="dashicons dashicons-no"></span></button>
				</div>
			</div>
		</div>
		<table class="table table-bordered table-striped w-100" id="wptableeditor_column">
			<thead>
				<tr>
					<th class="xs-w-1"><input name="xs_select_all" id="xs-select-all" type="checkbox" /></th>
					<th><?php esc_html_e('Column Name', 'wp-table-editor'); ?></th>
					<th class="xs-w-8"><?php esc_html_e('Filter', 'wp-table-editor'); ?></th>
					<th class="xs-w-8"><?php esc_html_e('Search', 'wp-table-editor'); ?></th>
					<th class="xs-w-8"><?php esc_html_e('Hidden', 'wp-table-editor'); ?></th>
					<th class="xs-w-8"><?php esc_html_e('Width(%)', 'wp-table-editor'); ?></th>
					<th class="xs-w-8"><?php esc_html_e('Align', 'wp-table-editor'); ?></th>
					<th class="xs-w-8"><?php esc_html_e('Type', 'wp-table-editor'); ?></th>
					<th class="text-center xs-w-1"><?php esc_html_e('Priority', 'wp-table-editor'); ?></th>
					<th class="text-center xs-w-1">#</th>
					<th class="text-center xs-w-1"><?php esc_html_e('Status', 'wp-table-editor'); ?></th>
					<th class="text-center xs-w-1"><?php esc_html_e('Edit', 'wp-table-editor'); ?></th>
					<th class="text-center xs-w-1"><?php esc_html_e('Style', 'wp-table-editor'); ?></th>
					<th class="text-center xs-w-1"><?php esc_html_e('Delete', 'wp-table-editor'); ?></th>
				</tr>
			</thead>
			<tbody id="xscolumn_body"></tbody>
		</table>
		<div class="panel-footer text-center">
			<button type="button" name="column_multi_active_wpte" id="column_multi_active_wpte" class="btn btn-success btn-xs"><?php esc_html_e('Active', 'wp-table-editor'); ?></button>
			<button type="button" name="column_multi_inactive_wpte" id="column_multi_inactive_wpte" class="btn btn-danger btn-xs"><?php esc_html_e('Inactive', 'wp-table-editor'); ?></button>
			<button type="button" name="column_multi_duplicate_wpte" id="column_multi_duplicate_wpte" class="btn btn-info btn-xs"><?php esc_html_e('Duplicate', 'wp-table-editor'); ?></button>
			<input type="checkbox" class="xswitch-ui-toggle" id="xscolumnreorder" name="xscolumnreorder" value="1">
		</div>
	</div>
</div>
<div class="modal-xs">
	<div id="columnModal" class="modal fade">
		<div class="modal-dialog modal-dialog-scrollable">
			<form method="post" id="column_form">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title"><b><?php esc_html_e('Add Column', 'wp-table-editor'); ?></b></h5>
						<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
					</div>
					<div class="modal-body">
						<div class="form-group">
							<label><b><?php esc_html_e('Column Name', 'wp-table-editor'); ?></b></label>
							<input type="text" name="column_names" id="column_names" class="form-control" required />
						</div>
						<div class="row">
							<div class="col-md-6">
								<label><?php esc_html_e('Filter', 'wp-table-editor'); ?></label>
								<select name="column_filters" id="column_filters" class="form-control" required >
									<option value="no">No</option>
									<option value="yes">Yes</option>
								</select>
							</div>
							<div class="col-md-6">
								<label><?php esc_html_e('Position', 'wp-table-editor'); ?></label>
								<select name="column_position" id="column_position" class="form-control" required >
									<option value="default">Default</option>
									<option value="footer">Footer</option>
									<option value="hidden">Hidden</option>
								</select>
							</div>
						</div>
						<div class="row">
							<div class="col-md-6">
								<label><?php esc_html_e('Hidden', 'wp-table-editor'); ?></label>
								<select name="column_hidden" id="column_hidden" class="form-control" required >
									<option value="no">No</option>
									<option value="yes">Yes</option>
								</select>
							</div>
							<div class="col-md-6">
								<label><?php esc_html_e('Total for column', 'wp-table-editor'); ?></label>
								<select name="column_total" id="column_total" class="form-control" required >
									<option value="no">No</option>
									<option value="yes">Yes</option>
								</select>
							</div>
						</div>
						<div class="row">
							<div class="col-md-6">
								<label><?php esc_html_e('Orderable', 'wp-table-editor'); ?></label>
								<select name="column_orderable" id="column_orderable" class="form-control" required >
									<option value="no">No</option>
									<option value="yes" selected>Yes</option>
								</select>
							</div>
							<div class="col-md-6">
								<label><?php esc_html_e('Searchable', 'wp-table-editor'); ?></label>
								<select name="column_searchable" id="column_searchable" class="form-control" required >
									<option value="no">No</option>
									<option value="yes" selected>Yes</option>
								</select>
							</div>
						</div>
						<div class="row">
							<?php if($table_type === 'product'){ ?>
							<div class="col-md-6">
								<label><?php esc_html_e('Column Type', 'wp-table-editor'); ?></label>
								<select name="column_name" id="column_name" class="form-control" required >
								<?php echo wp_kses(wptableeditor_woocommerce::product_type(), array('option' => array('value' => array()))); ?>
								</select>
							</div>
							<?php }elseif($table_type === 'order'){ ?>
							<div class="col-md-6">
								<label><?php esc_html_e('Column Type', 'wp-table-editor'); ?></label>
								<select name="column_name" id="column_name" class="form-control" required >
								<?php echo wp_kses(wptableeditor_woocommerce::order_type(), array('option' => array('value' => array()))); ?>
								</select>
							</div>
							<?php }elseif($table_type === 'post'){ ?>
							<div class="col-md-6">
								<label><?php esc_html_e('Column Type', 'wp-table-editor'); ?></label>
								<select name="column_name" id="column_name" class="form-control" required >
								<?php echo wp_kses(wptableeditor_post::poss('post'), array('option' => array('value' => array()))); ?>
								</select>
							</div>
							<?php }elseif($table_type === 'page'){ ?>
							<div class="col-md-6">
								<label><?php esc_html_e('Column Type', 'wp-table-editor'); ?></label>
								<select name="column_name" id="column_name" class="form-control" required >
								<?php echo wp_kses(wptableeditor_post::poss('page'), array('option' => array('value' => array()))); ?>
								</select>
							</div>
							<?php }elseif($table_type === 'json'){ ?>
							<div class="col-md-6">
								<label><?php esc_html_e('Column Type', 'wp-table-editor'); ?></label>
								<select name="column_name" id="column_name" class="form-control" required >
								<?php echo wp_kses(wptableeditor_url::column($table_id), array('option' => array('value' => array()))); ?>
								</select>
							</div>
							<?php }elseif($table_type === 'sheet'){ ?>
							<div class="col-md-6">
								<label><?php esc_html_e('Column Type', 'wp-table-editor'); ?></label>
								<select name="column_name" id="column_name" class="form-control" required >
								<?php echo wp_kses(wptableeditor_sheet::column($table_id), array('option' => array('value' => array()))); ?>
								</select>
							</div>
							<?php }else{ ?>
							<div class="col-md-6">
								<label><?php esc_html_e('Column Type', 'wp-table-editor'); ?></label>
								<select name="column_type" id="column_type" class="form-control" >
									<option value="">textarea</option>
									<option value="text">text</option>
									<option value="id">id</option>
									<option value="url">url</option>
									<option value="date">date</option>
									<option value="datetime-local">datetime-local</option>
									<option value="month">month</option>
									<option value="week">week</option>
									<option value="time">time</option>
									<option value="number">number</option>
									<option value="number_format">number_format</option>
									<option value="color">color</option>
									<option value="tel">tel</option>
									<option value="select">select</option>
									<option value="shortcode">shortcode</option>
									<option value="html">html</option>
									<option value="link">link</option>
									<option value="image">image</option>
									<option value="empty">empty</option>
								</select>
							</div>
							<?php } ?>
							<div class="col-md-6">
								<label><?php esc_html_e('Custom Filter', 'wp-table-editor'); ?></label>
								<select name="column_filter" id="column_filter" class="form-control" required >
									<option value="no" selected>No</option>
									<option value="yes">Yes</option>
								</select>
							</div>
						</div>
						<div class="form-group text-center" id="custom_filter">
							<label><?php esc_html_e('Dynamic Field', 'wp-table-editor'); ?></label>
			      			<table class="w-100" id="dynamic_filter"></table>
						</div>
						<div class="form-group text-center xs-d-none" id="custom_type">
							<label><?php esc_html_e('Create a drop-down list', 'wp-table-editor'); ?></label>
			      			<table class="w-100" id="dynamic_type"></table>
						</div>
					</div>
					<div class="modal-footer">
						<input type="hidden" name="table_id" id="table_id" value="<?php echo esc_attr($table_id); ?>"/>
						<input type="hidden" name="column_id" id="column_id"/>
						<input type="hidden" name="action" id="action"/>
						<input type="hidden" name="_xsnonce" id="_xsnonce" value="<?php echo esc_attr($_xsnonce); ?>"/>
						<input type="submit" name="submit" id="submit_button" class="btn btn-success btn-xs"/>
						<button type="button" class="btn btn-secondary btn-xs" data-bs-dismiss="modal">Close</button>
					</div>
				</div>
			</form>
		</div>
	</div>
	<div id="styleModal" class="modal fade">
		<div class="modal-dialog">
			<form method="post" id="styleForm">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title"><b><?php esc_html_e('Edit Style', 'wp-table-editor'); ?></b></h5>
						<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
					</div>
					<div class="modal-body">
						<div class="form-group">
							<label><b><?php esc_html_e('Column Name', 'wp-table-editor'); ?></b></label>
							<input type="text" name="column_names_2" id="column_names_2" class="form-control" disabled />
						</div>
						<div class="row">
							<div class="col-md-6">
								<label><?php esc_html_e('Font Weight', 'wp-table-editor'); ?></label>
								<select name="column_fontweight" id="column_fontweight" class="form-control">
									<option value="100">100</option>
									<option value="200">200</option>
									<option value="300">300</option>
									<option value="400" selected>400</option>
									<option value="500">500</option>
									<option value="600">600</option>
									<option value="700">700</option>
									<option value="800">800</option>
									<option value="900">900</option>
								</select>
							</div>
							<div class="col-md-6">
								<label><?php esc_html_e('Font Style', 'wp-table-editor'); ?></label>
								<select name="column_fontstyle" id="column_fontstyle" class="form-control">
									<option value="Normal">Normal</option>
									<option value="Italic">Italic</option>
									<option value="Oblique">Oblique</option>
								</select>
							</div>
						</div>
						<div class="row">
							<div class="col-md-6">
								<label><?php esc_html_e('Background Color', 'wp-table-editor'); ?></label>
								<div class="input-group">
									<input type="color" id="backgroundcolor" class="form-control h-30px" value="#ffffff"/>
									<input type="text" name="column_backgroundcolor" id="column_backgroundcolor" class="form-control h-30px w-50" minlength="7" maxlength="7" autocomplete="off"/>
								</div>
							</div>
							<div class="col-md-6">
								<label><?php esc_html_e('Font Color', 'wp-table-editor'); ?></label>
								<div class="input-group">
									<input type="color" id="fontcolor" class="form-control h-30px" value="#ffffff"/>
									<input type="text" name="column_fontcolor" id="column_fontcolor" class="form-control h-30px w-50" minlength="7" maxlength="7" autocomplete="off"/>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-6">
								<label><?php esc_html_e('Width(%)', 'wp-table-editor'); ?></label>
								<input type="number" name="column_width" id="column_width" min="0" max="99" value ="0" class="form-control" required/>
							</div>
							<div class="col-md-6">
								<label><?php esc_html_e('Min Width(px)', 'wp-table-editor'); ?></label>
								<input type="number" name="column_minwidth" id="column_minwidth" min="0" value ="0" class="form-control" required/>
							</div>
						</div>
						<div class="row">
							<div class="col-md-6">
								<label><?php esc_html_e('Align', 'wp-table-editor'); ?></label>
								<select name="column_align" id="column_align" class="form-control" required >
									<option value="left">Left</option>
									<option value="center">Center</option>
									<option value="right">Right</option>
								</select>
							</div>
							<div class="col-md-6">
								<label><?php esc_html_e('All text in the cells is on a single line', 'wp-table-editor'); ?></label>
								<select name="column_nowrap" id="column_nowrap" class="form-control" required >
									<option value="no">No</option>
									<option value="yes">Yes</option>
								</select>
							</div>
						</div>
						<div class="form-group" id="xs_control">
							<label>Class control</label><br/>
				            <div class="table-responsive">
				              <table class="table table-bordered text-center">
								<thead>
								<tr>
									<th>desktop</th>
									<th>tablet-l</th>
									<th>tablet-p</th>
									<th>mobile-l</th>
									<th>mobile-p</th>
								</tr>
								</thead>
								<tr>
									<td><input type="checkbox" name="xs_control[]" id="desktop" value="desktop" /></td>
									<td><input type="checkbox" name="xs_control[]" id="tablet-l" value="tablet-l" /></td>
									<td><input type="checkbox" name="xs_control[]" id="tablet-p" value="tablet-p" /></td>
									<td><input type="checkbox" name="xs_control[]" id="mobile-l" value="mobile-l" /></td>
									<td><input type="checkbox" name="xs_control[]" id="mobile-p" value="mobile-p" /></td>
				                </tr>
				              </table>
				            </div>
			            </div>
					</div>
					<div class="modal-footer">
						<input type="hidden" name="table_id" id="table_id_3"/>
						<input type="hidden" name="column_id" id="column_id_3"/>
						<input type="hidden" name="action" id="action_3"/>
						<input type="hidden" name="_xsnonce" id="_xsnonce_3"/>
						<input type="submit" name="submit" id="submit_button_3" class="btn btn-success btn-xs"/>
						<button type="button" class="btn btn-secondary btn-xs" data-bs-dismiss="modal">Close</button>
					</div>
				</div>
			</form>
		</div>
	</div>
</div>
<?php