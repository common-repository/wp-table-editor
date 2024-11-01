<?php

defined( 'ABSPATH' ) || exit;
wptableeditor_load::current_user_can();

global $wpdb, $table_id, $table_type, $_xsnonce;
check_ajax_referer( 'xs-table'.$table_id, '_xsnonce' );
check_admin_referer( 'xs-table'.$table_id, '_xsnonce' );

$html_id = "wptableeditor_$table_id";
$table_value = $wpdb->get_row($wpdb->prepare("SELECT table_name, table_footer, table_editor FROM ".WPTABLEEDITOR_TABLE." WHERE table_id = %d", $table_id), ARRAY_A);
$table_name = $table_value['table_name'];
$table_footer = $table_value['table_footer'];
if(wptableeditor_load::license() === true){
	$table_editor = $table_value['table_editor'];
}
$column_data = $wpdb->get_results($wpdb->prepare("SELECT column_name, column_names, column_status, column_type, column_customtype FROM ".WPTABLEEDITOR_COLUMN." WHERE table_id = %d ORDER BY column_order ASC", $table_id));
$j = 0;
$column_customtype = array();
foreach($column_data as $row){
	$column_name[$row->column_name] = $row->column_names;
	if($row->column_status === 'active'){
		$column_names[] = $row->column_names;
	}
	$j++;
	$column_order[$j] = $row->column_name;
	if($row->column_type === 'select' && !empty($row->column_customtype)){
		$select = '';
		foreach(explode(";", $row->column_customtype) as $rows){
			$select .= "<option value='$rows'>$rows</option>";
		}
		$column_customtype[$j] = $select;
	}
}
if($table_type === 'default'){
	?>
	<div class="xscontainer">
		<div class="<?php echo esc_attr($html_id); ?>">
			<span id="alert_row"></span>
			<div class="panel panel-default">
				<div class="panel-heading">
					<div class="row">
						<div class="panel_heading_left">
							<h5 class="panel-title"><b><?php echo esc_html($table_name); ?></b></h5>
						</div>
						<div class="panel_heading_right">
							<button type="button" name="row_add_wpte" id="row_add_wpte" class="btn btn-success btn-xs"><span class="dashicons dashicons-plus"></span></button>
							<button type="button" name="row_multi_delete_wpte" id="row_multi_delete_wpte" class="btn btn-danger btn-xs"><span class="dashicons dashicons-no"></span></button>
						</div>
					</div>
				</div>
				<table class="table table-bordered table-striped w-100" id="<?php echo esc_attr($html_id); ?>">
					<thead class="<?php echo esc_attr($html_id.'_thead'); ?>">
						<tr class="<?php echo esc_attr($html_id.'_head'); ?>">
							<?php $i = 0; ?>
							<th class="<?php echo esc_attr($html_id.'_head_'.$i); ?> xs-w-1"><input name="xs_select_all" id="xs-select-all" type="checkbox" /></th>
							<?php
							foreach($column_names as $names){
								$i++;
								echo wp_kses_post('<th class="'.esc_attr($html_id.'_head_'.$i).'">'.$names.'</th>');
							}?>
							<th class="xs-w-1">#</th>
							<th class="xs-w-1"><?php esc_html_e('Status', 'wp-table-editor'); ?></th>
							<th class="xs-w-1"><?php esc_html_e('Edit', 'wp-table-editor'); ?></th>
							<th class="xs-w-1"><?php esc_html_e('Delete', 'wp-table-editor'); ?></th>
						</tr>
					</thead>
					<tbody id="xsrow_body"></tbody>
					<?php if(isset($table_footer) && $table_footer === 'yes'){ ?>
					<tfoot class="<?php echo esc_attr($html_id.'_tfoot'); ?>">
						<tr class="<?php echo esc_attr($html_id.'_foot'); ?>">
							<?php 
							$i = 0;
							echo wp_kses_post('<th class="'.esc_attr($html_id.'_foot_'.$i).'"></th>');
							foreach($column_names as $names){
								$i++;
								echo wp_kses_post('<th class="'.esc_attr($html_id.'_foot_'.$i).'" id="'.esc_attr($html_id.'_foot_'.$i).'">'.$names.'</th>');
							}?>
							<th>#</th>
							<th><?php esc_html_e('Status', 'wp-table-editor'); ?></th>
							<th><?php esc_html_e('Edit', 'wp-table-editor'); ?></th>
							<th><?php esc_html_e('Delete', 'wp-table-editor'); ?></th>
						</tr>
					</tfoot>
					<?php } ?>
				</table>
				<div class="panel-footer text-center">
					<button type="button" name="row_multi_active_wpte" id="row_multi_active_wpte" class="btn btn-success btn-xs"><?php esc_html_e('Active', 'wp-table-editor'); ?></button>
					<button type="button" name="row_multi_inactive_wpte" id="row_multi_inactive_wpte" class="btn btn-danger btn-xs"><?php esc_html_e('Inactive', 'wp-table-editor'); ?></button>
					<button type="button" name="row_multi_duplicate_wpte" id="row_multi_duplicate_wpte" class="btn btn-info btn-xs"><?php esc_html_e('Duplicate', 'wp-table-editor'); ?></button>
					<a href="<?php echo esc_url(wptableeditor_init::url_export($table_id, $table_type)); ?>" id="export-wptableeditor" class="btn btn-secondary btn-xs" onclick="return confirm('<?php esc_html_e('Are you sure you want to export this table?', 'wp-table-editor'); ?>')"><?php esc_html_e( 'Export', 'wp-table-editor' ); ?></a>
					<input type="checkbox" class="xswitch-ui-toggle" id="xsrowreorder" name="xsrowreorder" value="1">
				</div>
			</div>
		</div>
	</div>
	<div class="modal-xs">
		<div id="rowModal" class="modal fade">
		  	<div class="modal-dialog modal-dialog-scrollable">
		    	<form method="post" id="row_form" enctype="multipart/form-data">
		      		<div class="modal-content">
		        		<div class="modal-header">
		          			<h5 class="modal-title" id="modal_title"><b><?php esc_html_e('Add Row', 'wp-table-editor'); ?></b></h5>
		          			<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
		        		</div>
		        		<div class="modal-body">
		        			<span id="form_message"></span>
							<?php foreach($column_name as $key => $row){ ?>
		                    <div class="form-group">
		                        <label><?php echo esc_html($row); ?></label>
		                        <?php if(isset($table_editor) && $table_editor === 'yes'){
		                        	wp_editor(wp_specialchars_decode(''), $key, array('textarea_name' => $key, 'editor_class' => 'form-control', 'tinymce' => false, 'quicktags' => array('buttons' => 'strong,em,link,del,ins,img,code,spell,close',), 'textarea_rows' => 1, 'default_editor' => 'html'));
		                        }else{
		                        	$column_type = wptableeditor_column::type($table_id, $key);
		                        	if(in_array($column_type, array('text', 'url', 'date','datetime-local', 'month', 'week', 'time', 'email', 'number', 'color', 'tel'))){
		                        		?><input type="<?php echo esc_attr($column_type); ?>" id="<?php echo esc_attr($key); ?>" class="form-control" name="<?php echo esc_attr($key); ?>"><?php
		                        	}elseif($column_type === 'select' && isset(array_flip($column_order)[$key]) && !empty($column_customtype[array_flip($column_order)[$key]])){ ?>
										<select name="<?php echo esc_attr($key); ?>" id="<?php echo esc_attr($key); ?>">
											<option value="">Select</option>
											<?php echo wp_kses($column_customtype[array_flip($column_order)[$key]], array('option' => array('value' => array()))); ?>
										</select> <?php
		                        	}else{
		                        		?><textarea name="<?php echo esc_attr($key); ?>" id="<?php echo esc_attr($key); ?>" class="form-control" rows="1"></textarea> <?php
		                        	}
		                        } ?>
		                    </div>
							<?php } ?>
							<div class="row">
								<div class="col-md-6">
			                        <label><?php esc_html_e('Row Order', 'wp-table-editor'); ?></label>
			                        <input type="number" id="column_order" name="column_order" class="form-control" min="1">
		                        </div>
								<div class="col-md-6">
									<label><?php esc_html_e('Row Status', 'wp-table-editor'); ?></label>
									<select name="column_status" id="column_status" class="form-control" required >
										<option value="active">Active</option>
										<option value="inactive">Inactive</option>
									</select>
								</div>
	                        </div>
		        		</div>
		        		<div class="modal-footer">
							<input type="hidden" name="row_id" id="row_id"/>
							<input type="hidden" name="table_id" id="table_id" value="<?php echo esc_attr($table_id); ?>"/>
							<input type="hidden" name="_xsnonce" id="_xsnonce" value="<?php echo esc_attr($_xsnonce); ?>"/>
		          			<input type="hidden" name="action" id="action" value="" />
		          			<input type="submit" name="submit" id="submit_button" class="btn btn-success btn-xs" value="" />
		          			<button type="button" class="btn btn-secondary btn-xs" data-bs-dismiss="modal">Close</button>
		        		</div>
		      		</div>
		    	</form>
		  	</div>
		</div>
	</div>
	<?php
}elseif(in_array($table_type, array('product', 'order', 'post', 'page'))){
	?>
	<div class="xscontainer">
		<div class="<?php echo esc_attr($html_id); ?>">
			<span id="alert_row"></span>
			<div class="panel panel-default">
				<div class="panel-heading">
					<div class="row">
						<div class="panel_heading_left">
							<h5 class="panel-title"><b><?php echo esc_html($table_name); ?></b></h5>
						</div>
						<div class="panel_heading_right text-right"></div>
					</div>
				</div>
				<table class="table table-bordered table-striped w-100" id="<?php echo esc_attr($html_id); ?>">
					<thead class="<?php echo esc_attr($html_id.'_thead'); ?>">
						<tr class="<?php echo esc_attr($html_id.'_head'); ?>">
							<?php $i = 0; ?>
							<th class="<?php echo esc_attr($html_id.'_head_'.$i); ?> xs-w-1"><input name="xs_select_all" id="xs-select-all" type="checkbox" /></th>
							<?php
							foreach($column_names as $names){
								$i++;
								echo wp_kses_post('<th class="'.esc_attr($html_id.'_head_'.$i).'">'.$names.'</th>');
							}?>
							<th class="xs-w-1">#</th>
							<th class="xs-w-1"><?php esc_html_e('Status', 'wp-table-editor'); ?></th>
							<th class="xs-w-1"><?php esc_html_e('Edit', 'wp-table-editor'); ?></th>
						</tr>
					</thead>
					<tbody id="xsrow_body"></tbody>
					<?php if(isset($table_footer) && $table_footer === 'yes'){ ?>
					<tfoot class="<?php echo esc_attr($html_id.'_tfoot'); ?>">
						<tr class="<?php echo esc_attr($html_id.'_foot'); ?>">
							<?php 
							$i = 0;
							echo wp_kses_post('<th class="'.esc_attr($html_id.'_foot_'.$i).'"></th>');
							foreach($column_names as $names){
								$i++;
								echo wp_kses_post('<th class="'.esc_attr($html_id.'_foot_'.$i).'" id="'.esc_attr($html_id.'_foot_'.$i).'">'.$names.'</th>');
							}?>
							<th>#</th>
							<th><?php esc_html_e('Status', 'wp-table-editor'); ?></th>
							<th><?php esc_html_e('Edit', 'wp-table-editor'); ?></th>
						</tr>
					</tfoot>
					<?php } ?>
				</table>
				<div class="panel-footer text-center">
					<?php if($table_type != 'order'){ ?>
					<button type="button" name="row_multi_active_wpte" id="row_multi_active_wpte" class="btn btn-success btn-xs"><?php esc_html_e('Active', 'wp-table-editor'); ?></button>
					<button type="button" name="row_multi_inactive_wpte" id="row_multi_inactive_wpte" class="btn btn-danger btn-xs"><?php esc_html_e('Inactive', 'wp-table-editor'); ?></button>
					<a href="<?php echo esc_url(wptableeditor_init::url_export($table_id, $table_type)); ?>" id="export-wptableeditor" class="btn btn-secondary btn-xs" onclick="return confirm('<?php esc_html_e('Are you sure you want to export this table?', 'wp-table-editor'); ?>')"><?php esc_html_e( 'Export', 'wp-table-editor' ); ?></a>
					<?php } ?>
					<input type="checkbox" class="xswitch-ui-toggle" id="xsrowreorder" name="xsrowreorder" value="1">
				</div>
			</div>
		</div>
	</div>
	<div class="modal-xs">
		<div id="rowModal" class="modal fade">
		  	<div class="modal-dialog modal-dialog-scrollable">
		    	<form method="post" id="row_form" enctype="multipart/form-data">
		      		<div class="modal-content">
		        		<div class="modal-header">
		          			<h5 class="modal-title" id="modal_title"><b><?php esc_html_e('Add Row', 'wp-table-editor'); ?></b></h5>
		          			<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
		        		</div>
		        		<div class="modal-body">
		        			<span id="form_message"></span>
							<?php foreach(wptableeditor_column::custom($table_id, $table_type) as $key => $row){ ?>
		                    <div class="form-group">
		                        <label><?php echo esc_html($row); ?></label>
		                        <?php if(isset($table_editor) && $table_editor === 'yes'){
		                        	wp_editor(wp_specialchars_decode(''), $key, array('textarea_name' => $key, 'editor_class' => 'form-control', 'tinymce' => false, 'quicktags' => array('buttons' => 'strong,em,link,del,ins,img,code,spell,close',), 'textarea_rows' => 1, 'default_editor' => 'html'));
		                        }else{
		                        	$column_type = wptableeditor_column::type($table_id, $key);
		                        	if(in_array($column_type, array('text', 'url', 'date','datetime-local', 'month', 'week', 'time', 'email', 'number', 'color', 'tel'))){
		                        		?><input type="<?php echo esc_attr($column_type); ?>" id="<?php echo esc_attr($key); ?>" class="form-control" name="<?php echo esc_attr($key); ?>"><?php
		                        	}elseif($column_type === 'select' && isset(array_flip($column_order)[$key]) && !empty($column_customtype[array_flip($column_order)[$key]])){ ?>
										<select name="<?php echo esc_attr($key); ?>" id="<?php echo esc_attr($key); ?>">
											<option value="">Select</option>
											<?php echo wp_kses($column_customtype[array_flip($column_order)[$key]], array('option' => array('value' => array()))); ?>
										</select> <?php
		                        	}else{
		                        		?><textarea name="<?php echo esc_attr($key); ?>" id="<?php echo esc_attr($key); ?>" class="form-control" rows="1"></textarea> <?php
		                        	}
		                        } ?>
		                    </div>
							<?php } ?>
							<div class="row">
								<div class="col-md-6">
			                        <label><?php esc_html_e('Row Order', 'wp-table-editor'); ?></label>
			                        <input type="number" id="column_order" name="column_order" class="form-control" min="1">
		                        </div>
								<div class="col-md-6">
									<label><?php esc_html_e('Row Status', 'wp-table-editor'); ?></label>
									<select name="column_status" id="column_status" class="form-control" required >
										<option value="active">Active</option>
										<option value="inactive">Inactive</option>
									</select>
								</div>
	                        </div>
		        		</div>
		        		<div class="modal-footer">
							<input type="hidden" name="row_id" id="row_id"/>
							<input type="hidden" name="table_id" id="table_id" value="<?php echo esc_attr($table_id); ?>"/>
							<input type="hidden" name="xs_type" id="xs_type" value="<?php echo esc_attr($table_type); ?>"/>
							<input type="hidden" name="_xsnonce" id="_xsnonce" value="<?php echo esc_attr($_xsnonce); ?>"/>
		          			<input type="hidden" name="action" id="action" value="" />
		          			<input type="submit" name="submit" id="submit_button" class="btn btn-success btn-xs" value="" />
		          			<button type="button" class="btn btn-secondary btn-xs" data-bs-dismiss="modal">Close</button>
		        		</div>
		      		</div>
		    	</form>
		  	</div>
		</div>
	</div>
	<?php
}elseif(in_array($table_type, array('json', 'sheet'))){
	?>
	<div class="xscontainer">
		<div class="<?php echo esc_attr($html_id); ?>">
			<span id="alert_row"></span>
			<div class="panel panel-default">
				<div class="panel-heading">
					<div class="row">
						<div class="panel_heading_left">
							<h5 class="panel-title"><b><?php echo esc_html($table_name); ?></b></h5>
						</div>
					</div>
				</div>
				<table class="table table-bordered table-striped w-100" id="<?php echo esc_attr($html_id); ?>">
					<thead class="<?php echo esc_attr($html_id.'_thead'); ?>">
						<tr class="<?php echo esc_attr($html_id.'_head'); ?>">
							<?php $i = 0;?>
							<th class="<?php echo esc_attr($html_id.'_head_'.$i); ?> xs-w-1"></th>
							<?php
							foreach($column_names as $names){
								$i++;
								echo wp_kses_post('<th class="'.esc_attr($html_id.'_head_'.$i).'">'.$names.'</th>');
							}?>
						</tr>
					</thead>
					<tbody id="xsrow_body"></tbody>
					<?php if(isset($table_footer) && $table_footer === 'yes'){ ?>
					<tfoot class="<?php echo esc_attr($html_id.'_tfoot'); ?>">
						<tr class="<?php echo esc_attr($html_id.'_foot'); ?>">
							<?php 
							$i = 0;
							echo wp_kses_post('<th class="'.esc_attr($html_id.'_foot_'.$i).'"></th>');
							foreach($column_names as $names){
								$i++;
								echo wp_kses_post('<th class="'.esc_attr($html_id.'_foot_'.$i).'" id="'.esc_attr($html_id.'_foot_'.$i).'">'.$names.'</th>');
							}?>
						</tr>
					</tfoot>
					<?php } ?>
				</table>
			</div>
		</div>
	</div>
	<?php
}