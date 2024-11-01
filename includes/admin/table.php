<?php

defined( 'ABSPATH' ) || exit;
wptableeditor_load::current_user_can();

?>
<div class="xscontainer">
	<span id="alert_table"></span>
	<div class="panel panel-default">
		<div class="panel-heading">
			<div class="row">
				<div class="panel_heading_left">
					<h5 class="panel-title"><b><?php esc_html_e('All Tables', 'wp-table-editor'); ?></b></h5>
				</div>
				<div class="panel_heading_right">
					<button type="button" name="add" id="table_add_wpte" class="btn btn-success btn-xs"><span class="dashicons dashicons-plus"></span></button>
					<button type="button" name="table_multi_delete_wpte" id="table_multi_delete_wpte" class="btn btn-danger btn-xs"><span class="dashicons dashicons-no"></span></button>
				</div>
			</div>
		</div>
		<table class="table table-bordered table-striped w-100" id="wptableeditor_table">
			<thead class="text-left">
				<tr>
					<th class="xs-w-1"><input name="xs_select_all" id="xs-select-all" type="checkbox" /></th>
					<th class="xs-w-32"><?php esc_html_e('Table Name', 'wp-table-editor'); ?></th>
					<th class="xs-w-15"><?php esc_html_e('Shortcode', 'wp-table-editor'); ?></th>
					<th class="xs-w-5"><?php esc_html_e('Source', 'wp-table-editor'); ?></th>
					<th class="xs-w-1"><?php esc_html_e('Columns', 'wp-table-editor'); ?></th>
					<th class="xs-w-20"><?php esc_html_e('Author', 'wp-table-editor'); ?></th>
					<th class="xs-w-1">#</th>
					<th class="xs-w-1"><?php esc_html_e('Status', 'wp-table-editor'); ?></th>
					<th class="xs-w-1"><?php esc_html_e('View', 'wp-table-editor'); ?></th>
					<th class="xs-w-1"><?php esc_html_e('Edit', 'wp-table-editor'); ?></th>
					<th class="xs-w-1"><?php esc_html_e('Style', 'wp-table-editor'); ?></th>
					<th class="xs-w-1"><?php esc_html_e('Delete', 'wp-table-editor'); ?></th>
				</tr>
			</thead>
			<tbody id="xstable_body"></tbody>
		</table>
		<div class="panel-footer text-center">
			<button type="button" name="table_multi_active_wpte" id="table_multi_active_wpte" class="btn btn-success btn-xs"><?php esc_html_e('Active', 'wp-table-editor'); ?></button>
			<button type="button" name="table_multi_inactive_wpte" id="table_multi_inactive_wpte" class="btn btn-danger btn-xs"><?php esc_html_e('Inactive', 'wp-table-editor'); ?></button>
			<button type="button" name="table_multi_duplicate_wpte" id="table_multi_duplicate_wpte" class="btn btn-info btn-xs"><?php esc_html_e('Duplicate', 'wp-table-editor'); ?></button>
			<input type="checkbox" class="xswitch-ui-toggle" id="xstablereorder" name="xstablereorder" value="1">
		</div>
	</div>
</div>
<div class="modal-xs">
	<div id="tableModal" class="modal fade">
		<div class="modal-dialog">
			<form method="post" id="tableForm">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title"><b><?php esc_html_e('Add Table', 'wp-table-editor'); ?></b></h5>
						<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
					</div>
					<div class="modal-body">
						<div class="form-group">
							<label><b><?php esc_html_e('Table Name', 'wp-table-editor'); ?></b></label>
							<input type="text" class="form-control" id="table_name" name="table_name" placeholder="Name" required>
						</div>
						<div class="row">
	  						<div class="col-md-6">
	  							<label><?php esc_html_e('Number of Columns', 'wp-table-editor'); ?></label>
	  							<input type="number" name="table_columns" id="table_columns" value="1" min="1" max="40" class="form-control" required/>
	  						</div>
	  						<div class="col-md-6">
								<label><?php esc_html_e('Source Type', 'wp-table-editor'); ?></label>
								<select name="table_type" id="table_type" class="form-control" required >
									<option value=""><?php esc_html_e('Select', 'wp-table-editor'); ?></option>
									<option value="default">Default</option>
									<?php if(function_exists('wc_get_products')){ ?>
									<option value="product">Product</option>
									<option value="order">order</option>
									<?php } ?>
									<option value="post">Post</option>
									<option value="page">Page</option>
									<option value="json">Json</option>
									<option value="sheet">Sheets</option>
								</select>
	  						</div>
						</div>
						<div class="row">
							<div class="col-md-6">
								<label><?php esc_html_e('Responsive', 'wp-table-editor'); ?></label>
								<select name="table_responsive" id="table_responsive" class="form-control" required >
									<option value="collapse" selected>Collapse</option>
									<option value="scroll">Scroll</option>
									<option value="flip">Flip</option>
									<option value="stack">Stack</option>
								</select>
							</div>
							<div class="col-md-6">
	  							<label><?php esc_html_e('scrollY', 'wp-table-editor'); ?></label>
	  							<input type="number" name="table_scrolly" id="table_scrolly" value="-1" min="-1" class="form-control" required/>
							</div>
						</div>
						<div class="row">
							<div class="col-md-6">
								<label><?php esc_html_e('Paging', 'wp-table-editor'); ?></label>
								<select name="table_paging" id="table_paging" class="form-control" required >
									<option value="no">No</option>
									<option value="yes" selected>Yes</option>
								</select>
							</div>
							<div class="col-md-6">
	  							<label><?php esc_html_e('Page Length', 'wp-table-editor'); ?></label>
	  							<input type="number" name="table_length" id="table_length" value="10" min="1" class="form-control" required/>
							</div>
						</div>
						<div class="row">
							<div class="col-md-6">
								<label><?php esc_html_e('Show Footer', 'wp-table-editor'); ?></label>
								<select name="table_footer" id="table_footer" class="form-control" required >
									<option value="no">No</option>
									<option value="yes">Yes</option>
								</select>
							</div>
	  						<div class="col-md-6">
								<label><?php esc_html_e('Show Export', 'wp-table-editor'); ?></label>
								<select name="table_button" id="table_button" class="form-control" required >
									<option value="no">No</option>
									<option value="yes">Yes</option>
								</select>
	  						</div>
						</div>
						<div class="row">
							<div class="col-md-6">
								<label><?php esc_html_e('Categories ID', 'wp-table-editor'); ?></label>
								<input type="text" name="table_category" id="table_category" class="form-control" placeholder="1,2,3" disabled/>
							</div>
							<div class="col-md-6">
								<label><?php esc_html_e('Pagination', 'wp-table-editor'); ?></label>
								<select name="table_pagination" id="table_pagination" class="form-control" required >
									<option value="full">full</option>
									<option value="simple">simple</option>
									<option value="numbers">numbers</option>
									<option value="full_numbers">full_numbers</option>
									<option value="simple_numbers" selected>simple_numbers</option>
									<option value="first_last_numbers">first_last_numbers</option>
								</select>
							</div>
						</div>
						<div class="row">
							<div class="col-md-6">
								<label><?php esc_html_e('Server-side', 'wp-table-editor'); ?></label>
								<select name="table_serverside" id="table_serverside" class="form-control" disabled>
									<option value="no">No</option>
									<option value="yes">Yes</option>
								</select>
							</div>
							<div class="col-md-6">
								<label><?php esc_html_e('Maximum number of rows', 'wp-table-editor'); ?></label>
								<input type="number" name="table_limit" id="table_limit" min="-1" value ="-1" class="form-control" required/>
							</div>
						</div>
						<div class="row">
							<div class="col-md-6">
								<label><?php esc_html_e('Sorting Column/Type', 'wp-table-editor'); ?></label>
								<div class="input-group">
									<input type="number" name="table_sorting" id="table_sorting" min="-1" value="-1" class="form-control" required/>
									<select name="table_sortingtype" id="table_sortingtype" class="form-control" required >
										<option value="asc">ASC</option>
										<option value="desc">DESC</option>
									</select>
								</div>
							</div>
							<div class="col-md-6">
								<label><?php esc_html_e('Fixed ordering', 'wp-table-editor'); ?></label>
								<select name="table_orderfixed" id="table_orderfixed" class="form-control" required>
									<option value="no">No</option>
									<option value="yes">Yes</option>
								</select>
							</div>
						</div>
						<div class="row" id="table_url_field">
							<div class="col-md-6">
								<label><?php esc_html_e('JSON url', 'wp-table-editor'); ?></label>
								<input type="url" class="form-control" id="table_url" name="table_url" placeholder="url" required>
							</div>
							<div class="col-md-6">
								<label><?php esc_html_e('dataSrc', 'wp-table-editor'); ?></label>
								<input type="text" name="table_datasrc" id="table_datasrc" class="form-control" placeholder="body"/>
							</div>
						</div>
						<div class="row" id="table_sheet_field">
							<div class="col-md-6">
								<label><?php esc_html_e('API Key', 'wp-table-editor'); ?></label>
								<input type="text" name="table_apikey" id="table_apikey" class="form-control" placeholder="api_key"/>
							</div>
							<div class="col-md-6">
								<label><?php esc_html_e('Google Sheet Id', 'wp-table-editor'); ?></label>
								<input type="text" class="form-control" id="table_sheetid" name="table_sheetid" placeholder="id" required>
							</div>
							<div class="col-md-6">
								<label><?php esc_html_e('Sheet Name', 'wp-table-editor'); ?></label>
								<input type="text" class="form-control" id="table_sheetname" name="table_sheetname" placeholder="Sheet1" required>
							</div>
							<div class="col-md-6">
								<label><?php esc_html_e('Range', 'wp-table-editor'); ?></label>
								<input type="text" name="table_range" id="table_range" class="form-control" placeholder="A1:F1000"/>
							</div>
						</div>
						<div class="form-group">
							<label><?php esc_html_e('Note', 'wp-table-editor'); ?></label>
							<textarea class="form-control" rows="1" id="table_note" name="table_note"></textarea>
						</div>
					</div>
					<div class="modal-footer">
						<input type="hidden" name="table_id" id="table_id"/>
						<input type="hidden" name="action" id="action"/>
						<input type="hidden" name="_xsnonce" id="_xsnonce"/>
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
							<label><b><?php esc_html_e('Table Name', 'wp-table-editor'); ?></b></label>
							<input type="text" class="form-control" id="table_name_2" name="table_name_2" placeholder="Name" disabled>
						</div>
						<div class="row">
							<div class="col-md-6">
								<label><?php esc_html_e('Width', 'wp-table-editor'); ?></label>
								<div class="input-group">
									<input type="number" min="0" class="form-control" id="table_width" name="table_width" value="100">
									<select name="table_unit" id="table_unit" class="form-control">
										<option value="%">%</option>
										<option value="px">px</option>
									</select>
								</div>
							</div>
							<div class="col-md-6">
								<label><?php esc_html_e('Border', 'wp-table-editor'); ?></label>
								<select name="table_border" id="table_border" class="form-control" required>
									<option value="no">No</option>
									<option value="yes">Yes</option>
								</select>
							</div>
						</div>
						<div class="row">
							<div class="col-md-6">
								<label><?php esc_html_e('Hide Thead', 'wp-table-editor'); ?></label>
								<select name="table_hidethead" id="table_hidethead" class="form-control" required>
									<option value="no">No</option>
									<option value="yes">Yes</option>
								</select>
							</div>
							<div class="col-md-6">
								<label><?php esc_html_e('Responsive Type', 'wp-table-editor'); ?></label>
								<select name="table_responsivetype" id="table_responsivetype" class="form-control" required>
									<option value="default">default</option>
									<option value="modal">modal</option>
								</select>
							</div>
						</div>
						<div class="row">
							<div class="col-md-6">
								<label><?php esc_html_e('Font Size', 'wp-table-editor'); ?></label>
								<input type="number" min="1" class="form-control" id="table_fontsize" name="table_fontsize">
							</div>
	  						<div class="col-md-6">
								<label><?php esc_html_e('Font Family', 'wp-table-editor'); ?></label>
								<input type="text" class="form-control" id="table_fontfamily" name="table_fontfamily">
	  						</div>
						</div>
						<div class="row">
							<div class="col-md-6">
								<label><?php esc_html_e('Header Font Size', 'wp-table-editor'); ?></label>
								<input type="number" min="1" class="form-control" id="table_headerfontsize" name="table_headerfontsize">
							</div>
	  						<div class="col-md-6">
								<label><?php esc_html_e('Header Font Family', 'wp-table-editor'); ?></label>
								<input type="text" class="form-control" id="table_headerfontfamily" name="table_headerfontfamily">
	  						</div>
						</div>
						<div class="row">
							<div class="col-md-6">
								<label><?php esc_html_e('Header Font Weight', 'wp-table-editor'); ?></label>
								<select name="table_headerfontweight" id="table_headerfontweight" class="form-control">
									<option value="100">100</option>
									<option value="200">200</option>
									<option value="300">300</option>
									<option value="400">400</option>
									<option value="500">500</option>
									<option value="600">600</option>
									<option value="700" selected>700</option>
									<option value="800">800</option>
									<option value="900">900</option>
								</select>
							</div>
							<div class="col-md-6">
								<label><?php esc_html_e('Header Font Style', 'wp-table-editor'); ?></label>
								<select name="table_headerfontstyle" id="table_headerfontstyle" class="form-control">
									<option value="Normal">Normal</option>
									<option value="Italic">Italic</option>
									<option value="Oblique">Oblique</option>
								</select>
							</div>
						</div>
						<div class="row">
							<div class="col-md-6">
								<label><?php esc_html_e('Header Background Color', 'wp-table-editor'); ?></label>
								<div class="input-group">
									<input type="color" id="headerbackgroundcolor" class="form-control" value="#ffffff" />
									<input type="text" name="table_headerbackgroundcolor" id="table_headerbackgroundcolor" class="form-control w-50" minlength="7" maxlength="7" autocomplete="off"/>
								</div>
							</div>
							<div class="col-md-6">
								<label><?php esc_html_e('Header Font Color', 'wp-table-editor'); ?></label>
								<div class="input-group">
									<input type="color" id="headerfontcolor" class="form-control" value="#ffffff" />
									<input type="text" name="table_headerfontcolor" id="table_headerfontcolor" class="form-control w-50" minlength="7" maxlength="7" autocomplete="off"/>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-6">
								<label><?php esc_html_e('Header Link Color', 'wp-table-editor'); ?></label>
								<div class="input-group">
									<input type="color" id="headerlinkcolor" class="form-control" value="#ffffff" />
									<input type="text" name="table_headerlinkcolor" id="table_headerlinkcolor" class="form-control w-50" minlength="7" maxlength="7" autocomplete="off"/>
								</div>
							</div>
							<div class="col-md-6">
								<label><?php esc_html_e('Header Sorting Color', 'wp-table-editor'); ?></label>
								<div class="input-group">
									<input type="color" id="headersortingcolor" class="form-control" value="#ffffff" />
									<input type="text" name="table_headersortingcolor" id="table_headersortingcolor" class="form-control w-50" minlength="7" maxlength="7" autocomplete="off"/>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-6">
								<label><?php esc_html_e('Body Font Size', 'wp-table-editor'); ?></label>
								<input type="number" min="1" class="form-control" id="table_bodyfontsize" name="table_bodyfontsize">
							</div>
	  						<div class="col-md-6">
								<label><?php esc_html_e('Body Font Family', 'wp-table-editor'); ?></label>
								<input type="text" class="form-control" id="table_bodyfontfamily" name="table_bodyfontfamily">
	  						</div>
						</div>
						<div class="row">
							<div class="col-md-6">
								<label><?php esc_html_e('Body Font Weight', 'wp-table-editor'); ?></label>
								<select name="table_bodyfontweight" id="table_bodyfontweight" class="form-control">
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
								<label><?php esc_html_e('Body Font Style', 'wp-table-editor'); ?></label>
								<select name="table_bodyfontstyle" id="table_bodyfontstyle" class="form-control">
									<option value="Normal">Normal</option>
									<option value="Italic">Italic</option>
									<option value="Oblique">Oblique</option>
								</select>
							</div>
						</div>
						<div class="row">
							<div class="col-md-6">
								<label><?php esc_html_e('Highlight a row when hovered over', 'wp-table-editor'); ?></label>
								<select name="table_hover" id="table_hover" class="form-control">
									<option value="no">No</option>
									<option value="yes">Yes</option>
								</select>
							</div>
							<div class="col-md-6">
								<label><?php esc_html_e('Highlight the column being ordering upon', 'wp-table-editor'); ?></label>
								<select name="table_ordercolumn" id="table_ordercolumn" class="form-control">
									<option value="no">No</option>
									<option value="yes">Yes</option>
								</select>
							</div>
						</div>
						<div class="row">
							<div class="col-md-6">
								<label><?php esc_html_e('Even Rows Background Color', 'wp-table-editor'); ?></label>
								<div class="input-group">
									<input type="color" id="evenbackgroundcolor" class="form-control" value="#ffffff" />
									<input type="text" name="table_evenbackgroundcolor" id="table_evenbackgroundcolor" class="form-control w-50" minlength="7" maxlength="7" autocomplete="off"/>
								</div>
							</div>
							<div class="col-md-6">
								<label><?php esc_html_e('Odd Rows Background Color', 'wp-table-editor'); ?></label>
								<div class="input-group">
									<input type="color" id="oddbackgroundcolor" class="form-control" value="#ffffff" />
									<input type="text" name="table_oddbackgroundcolor" id="table_oddbackgroundcolor" class="form-control w-50" minlength="7" maxlength="7" autocomplete="off"/>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-6">
								<label><?php esc_html_e('Even Rows Font Color', 'wp-table-editor'); ?></label>
								<div class="input-group">
									<input type="color" id="evenfontcolor" class="form-control" value="#ffffff" />
									<input type="text" name="table_evenfontcolor" id="table_evenfontcolor" class="form-control w-50" minlength="7" maxlength="7" autocomplete="off"/>
								</div>
							</div>
							<div class="col-md-6">
								<label><?php esc_html_e('Odd Rows Font Color', 'wp-table-editor'); ?></label>
								<div class="input-group">
									<input type="color" id="oddfontcolor" class="form-control" value="#ffffff" />
									<input type="text" name="table_oddfontcolor" id="table_oddfontcolor" class="form-control w-50" minlength="7" maxlength="7" autocomplete="off"/>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-6">
								<label><?php esc_html_e('Even Rows Link Color', 'wp-table-editor'); ?></label>
								<div class="input-group">
									<input type="color" id="evenlinkcolor" class="form-control" value="#ffffff" />
									<input type="text" name="table_evenlinkcolor" id="table_evenlinkcolor" class="form-control w-50" minlength="7" maxlength="7" autocomplete="off"/>
								</div>
							</div>
							<div class="col-md-6">
								<label><?php esc_html_e('Odd Rows Link Color', 'wp-table-editor'); ?></label>
								<div class="input-group">
									<input type="color" id="oddlinkcolor" class="form-control" value="#ffffff" />
									<input type="text" name="table_oddlinkcolor" id="table_oddlinkcolor" class="form-control w-50" minlength="7" maxlength="7" autocomplete="off"/>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-6">
								<label><?php esc_html_e('Button Background Color', 'wp-table-editor'); ?></label>
								<div class="input-group">
									<input type="color" id="buttonbackgroundcolor" class="form-control" value="#ffffff" />
									<input type="text" name="table_buttonbackgroundcolor" id="table_buttonbackgroundcolor" class="form-control w-50" minlength="7" maxlength="7" autocomplete="off"/>
								</div>
							</div>
							<div class="col-md-6">
								<label><?php esc_html_e('Button Font Color', 'wp-table-editor'); ?></label>
								<div class="input-group">
									<input type="color" id="buttonfontcolor" class="form-control" value="#ffffff" />
									<input type="text" name="table_buttonfontcolor" id="table_buttonfontcolor" class="form-control w-50" minlength="7" maxlength="7" autocomplete="off"/>
								</div>
							</div>
						</div>
						<div class="form-group" id="xs_dom">
							<label>Dom</label><br/>
				            <div class="table-responsive">
				              <table class="table table-bordered text-center mb-0">
								<thead>
								<tr>
									<th>Length</th>
									<th>Select inputs</th>
									<th>Buttons</th>
									<th>Filtering</th>
									<th class="d-none">pRocessing</th>
									<th class="d-none">Table</th>
									<th>Information</th>
									<th>Pagination</th>
								</tr>
								</thead>
								<tr>
									<td><input type="checkbox" name="xs_dom[]" id="l" value="l" /></td>
									<td><input type="checkbox" name="xs_dom[]" id="c" value="c" /></td>
									<td><input type="checkbox" name="xs_dom[]" id="B" value="B" /></td>
									<td><input type="checkbox" name="xs_dom[]" id="f" value="f" /></td>
									<td class="d-none"><input type="checkbox" name="xs_dom[]" id="r" value="r" checked/></td>
									<td class="d-none"><input type="checkbox" name="xs_dom[]" id="t" value="t" checked/></td>
									<td><input type="checkbox" name="xs_dom[]" id="i" value="i" /></td>
									<td><input type="checkbox" name="xs_dom[]" id="p" value="p" /></td>
				                </tr>
				              </table>
				            </div>
			            </div>
					</div>
					<div class="modal-footer">
						<input type="hidden" name="table_id" id="table_id_3"/>
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