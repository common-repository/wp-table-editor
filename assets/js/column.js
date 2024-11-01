/** @wptableeditor **/
( function( $ ) {
	'use strict';
	$(document).ready(function(){
		if(typeof(xs_ajax_column) != "undefined" && xs_ajax_column !== null) {
			if(xs_ajax_column.license === false){
				$('[data-toggle="tooltip"]').attr('title',xs_ajax_localize.license).tooltip().attr('disabled', true);
			}else{
				['column_index', 'column_childrows', 'column_restriction', 'column_search', 'column_priority', 'column_render', 'column_createdcell'].forEach(function(item, index){
					$('#'+item).attr('disabled', false);
				});
			}
			$.fn.dataTable.ext.errMode = 'none';
			var columnRecords = $('#wptableeditor_column').DataTable({
				"processing": true,
				"ajax": {
					"url": xs_ajax_column.ajax_url,
					"type": "POST",
					"data" : {action: "column_getdata_wpte", table_id:xs_ajax_column.table_id, _xsnonce:xs_ajax_column.xsnonce},
				},
				"dom": 'l<"#xs_select.dataTables_length">Bfrtip',
				"buttons": [
					{
						extend: 'colvis',
						columns: ':not(.noVis)'
					},
					{
						text: xs_ajax_localize.reset,
						header: true,
						action: function ( e, dt, node, config ) {
							$("input[type='text']").each(function () { 
								$(this).val(''); 
							})
							$('input:checkbox').prop('checked', false);
							$("#select_align").val('');
							$("#select_status").val('');
							dt.columns().every( function () {
								var column = this;
								column.search( '' ).draw();
							} );
							dt.search('').draw();
							dt.searchBuilder.rebuild();
							dt.searchPanes.rebuildPane();
							dt.state.clear();
							dt.page.len(10).draw();
							dt.order([[9, 'asc']]).draw();
							dt.columns([9]).visible( false );
							dt.columns([1, 2, 3, 4, 5, 6, 7, 8]).visible( true );
							dt.columns.adjust().draw();
							dt.rows().deselect();
							dt.columns().deselect();
							dt.cells().deselect();
						}
					},
				],
				"columnDefs":[
					{"targets":[2,3,4,5,6,8,9],"className": "text-center",},
					{"targets":[-5], "visible": false, "orderable":true},
					{"targets": [0,-1,-2,-3,-4], "className": 'noVis text-center', "orderable":false},
				],
				initComplete: function () {
					this.api().columns([10]).every( function () {
						var column = this;
						var select = $('<select id="select_status"><option value="">'+xs_ajax_localize.status+'</option></select>')
							.appendTo("#xs_select.dataTables_length")
							.on( 'change', function () {
								var val = $.fn.dataTable.util.escapeRegex(
									$(this).val()
								);
								column.search( val ? '^'+val+'$' : '', true, false ).draw();
							} );
						select.append( '<option value="Active">Active</option><option value="Inactive">Inactive</option>' );
					} );
					this.api().columns([6]).every( function () {
						var column = this;
						var select = $('<select id="select_align"><option value="">'+xs_ajax_localize.align+'</option></select>')
							.appendTo("#xs_select.dataTables_length")
							.on( 'change', function () {
								var val = $.fn.dataTable.util.escapeRegex(
									$(this).val()
								);
								column.search( val ? '^'+val+'$' : '', true, false ).draw();
							} );
						select.append( '<option value="left">Left</option><option value="center">Center</option><option value="right">Right</option>' );
					} );
				},
				"deferRender": true,
				"responsive": true,
				"stateSave": false,
				"language": {
					"sEmptyTable":     xs_ajax_localize.sEmptyTable,
					"sInfo":           xs_ajax_localize.sInfo,
					"sInfoEmpty":      xs_ajax_localize.sInfoEmpty,
					"sInfoFiltered":   xs_ajax_localize.sInfoFiltered,
					"sInfoPostFix":    "",
					"sInfoThousands":  ",",
					"sLengthMenu":     "_MENU_",
					"sLoadingRecords": xs_ajax_localize.sLoadingRecords,
					"sSearch":         "",
					"searchPlaceholder": xs_ajax_localize.searchPlaceholder,
					"sZeroRecords":    xs_ajax_localize.sZeroRecords,
					"oPaginate": {
						"sFirst":    xs_ajax_localize.sFirst,
						"sLast":     xs_ajax_localize.sLast,
						"sNext":     xs_ajax_localize.sNext,
						"sPrevious": xs_ajax_localize.sPrevious
					},
					"oAria": {
						"sSortAscending":  xs_ajax_localize.sSortAscending,
						"sSortDescending": xs_ajax_localize.sSortDescending
					},
					buttons: {colvis: xs_ajax_localize.visibility}
				},
				"paging":   true,
				"searching": true,
				"order":[[9, 'asc']],
			});
			$('#xscolumnreorder').click(function() {
				var isChecked = $(this).prop("checked");
				if (isChecked === false){
					$("#xscolumn_body").removeClass('xsortHandle').sortable( "disable" );
				}else{
					$('#xscolumn_body').addClass('xsortHandle').sortable({
						placeholder : "ui-state-highlight",
						update : function(event, ui){
							var id = new Array();
							$('#wptableeditor_column tbody tr').each(function(){
								id.push($(this).attr('id'));
							});
							var info = columnRecords.page.info();
							if(confirm(xs_ajax_localize.order_column)){
								$.ajax({
									url:xs_ajax_column.ajax_url,
									method:"POST",
									data:{column_id:id, start:info.start, action:'column_update_order_wpte', table_id:xs_ajax_column.table_id, _xsnonce:xs_ajax_column.xsnonce},
									success:function(){
										$('#alert_column').fadeIn().html('<div class="alert alert-success">'+xs_ajax_localize.updated_column+'</div>');
										columnRecords.ajax.reload(null, false);
									}
								})
							}else{
								return false;
							}
						}
					});
					$("#xscolumn_body").sortable( "enable" );
				}
			});
			$(document).on('submit','#column_form', function(event){
				event.preventDefault();
				$('#submit_button').attr('disabled','disabled');
				var form_data = $(this).serialize();
				$.ajax({
					url:xs_ajax_column.ajax_url,
					method:"POST",
					data:form_data,
					success:function(data){
						$('#columnModal').modal('hide');
						$('#column_form')[0].reset();
						$('#alert_column').fadeIn().html(data);
						$('#submit_button').attr('disabled', false);
						columnRecords.ajax.reload(null, false);
					}
				})
			});
			$("#styleModal").on('submit','#styleForm', function(event){
				event.preventDefault();
				$('#submit_button_3').attr('disabled','disabled');
				var formData = $(this).serialize();
				$.ajax({
					url:xs_ajax_column.ajax_url,
					method:"POST",
					data:formData,
					success:function(data){				
						$('#styleModal').modal('hide');
						$('#styleForm')[0].reset();					
						$('#submit_button_3').attr('disabled', false);
						$('#alert_column').fadeIn().html(data);
						$("#xs-select-all").prop('checked', false);
						columnRecords.ajax.reload(null, false);
					}
				})
			});
			$("#permissionModal").on('submit','#permission_form', function(event){
				event.preventDefault();
				$('#submit_buttons').attr('disabled','disabled');
				var formData = $(this).serialize();
				$.ajax({
					url:xs_ajax_column.ajax_url,
					method:"POST",
					data:formData,
					success:function(data){				
						$('#permissionModal').modal('hide');
						$('#permission_form')[0].reset();			
						$('#submit_buttons').attr('disabled', false);
						$('#alert_column').fadeIn().html(data);
						$("#xs-select-all").prop('checked', false);
						columnRecords.ajax.reload(null, false);
					}
				})
			});
			$('#column_add_wpte').click(function(){
				$('#columnModal').modal('show');
				$('#column_form')[0].reset();
				$('#column_position').attr('disabled', true);
				$('#column_filter').attr('disabled', true);
				$('#column_optionlimit').attr('disabled', true);
				$('#column_characterlimit').attr('disabled', true);
				if($.inArray(xs_ajax_column.table_type, ['json', 'sheet']) !== -1){
					$('#column_restriction').attr('disabled', true).val('no');
				}
				$('#column_restrictiontitle').attr('disabled', true);
				$('#custom_filter').hide();
				$('#dynamic_filter').html('');
				$('#custom_type').hide();
				$('#dynamic_type').html('');
				add_dynamic_filter(1);
				add_dynamic_type(1);
				$('.modal-title').html('<b>'+xs_ajax_localize.add_column+'</b>');
				$('#submit_button').val('Add');
				$('#action').val('column_add_wpte');
				$('#_xsnonce').val(xs_ajax_column.xsnonce);
			});
			$(document).on('click', '.column_edit_status_wpte', function(){
				var id = $(this).attr('id');
				var status = $(this).data("status");
				var xsnonce = $(this).data("xsnonce");
				var action = 'column_edit_status_wpte';
				if(confirm(xs_ajax_localize.status_column)){
					$.ajax({
						url:xs_ajax_column.ajax_url,
						method:"POST",
						data:{column_id:id, status:status, action:action, table_id:xs_ajax_column.table_id, _xsnonce:xsnonce},
						success:function(data){
							$('#alert_column').fadeIn().html(data);
							columnRecords.ajax.reload(null, false);
						}
					})
				}else{
					return false;
				}
			});
			$(document).on('click', '.column_edit_wpte', function(){
				var id = $(this).attr("id");
				var xsnonce = $(this).data("xsnonce");
				var action = 'column_single_wpte';
				$.ajax({
					url:xs_ajax_column.ajax_url,
					method:"POST",
					data:{column_id:id, action:action, table_id:xs_ajax_column.table_id, _xsnonce:xsnonce},
					dataType:"json",
					success:function(data){
						$('#columnModal').modal('show');
						$('#column_form')[0].reset();
						JSON.parse(xs_ajax_column.columns).forEach(function(item, index){
							if(data[item]){
								$('#'+item).val(data[item]);
							}
						});
						JSON.parse(xs_ajax_column.colors).forEach(function(item, index){
							if(data['column_'+item] && data['column_'+item] !== ''){
								$('#'+item).val(data['column_'+item]);
							}
						});
						$('#dynamic_filter').html(data.column_customfilter);
						$('#dynamic_type').html(data.column_customtype);
						if(data.column_filters === 'no'){
							$('#column_position').attr('disabled', true);
							$('#column_filter').attr('disabled', true);
							$('#column_optionlimit').attr('disabled', true);
							$('#column_characterlimit').attr('disabled', true);
							$('#custom_filter').hide();
						}else{
							$('#column_position').attr('disabled', false);
							$('#column_filter').attr('disabled', false);
							if(xs_ajax_column.license === true){
								$('#column_optionlimit').attr('disabled', false);
								$('#column_characterlimit').attr('disabled', false);
							}
							if(data.column_filter === 'no'){
								$('#custom_filter').hide();
							}else{
								$('#custom_filter').show();
							}
						}
						if($.inArray(xs_ajax_column.table_type, ['json', 'sheet']) !== -1){
							$('#column_restriction').attr('disabled', true).val('no');
						}
						if(typeof(data.column_restriction) != "undefined" && data.column_restriction !== null) {
							if(data.column_restriction === 'no'){
								$('#column_restrictiontitle').attr('disabled', true);
							}else{
								$('#column_restrictiontitle').attr('disabled', false);
							}
						}
						if(data.column_type === 'select'){
							$('#custom_type').show();
						}else{
							$('#custom_type').hide();
						}
						JSON.parse(data.column_control).forEach(function(item, index){
							$('#'+item).prop("checked", true);
						});
						$('.modal-title').html('<b>'+xs_ajax_localize.edit_column+'</b>');
						$('#column_id').val(id);
						$('#_xsnonce').val(xsnonce);
						$('#action').val("column_edit_wpte");
						$('#submit_button').val("Edit");
					}
				})
			});
			$(document).on('click', '.column_edit_restriction_wpte', function(){
				var id = $(this).attr("id");
				var xsnonce = $(this).data("xsnonce");
				var action = 'column_restrictionrole_single_wpte';
				$.ajax({
					url:xs_ajax_column.ajax_url,
					method:"POST",
					data:{column_id:id, action:action, table_id:xs_ajax_column.table_id, _xsnonce:xsnonce},
					dataType:"json",
					success:function(data){
						$('#permissionModal').modal('show');
						$('#permission_form')[0].reset();
						data.column_restrictionrole.forEach(function(item, index){
							$('#'+item).prop("checked", true);
						});
						$('#column_names_3').val(data.column_names);
						$('#table_ids').val(xs_ajax_column.table_id);
						$('#column_ids').val(id);
						$('#_xsnonces').val(xsnonce);
						$('.modal-title').html('<b>'+xs_ajax_localize.edit_restriction+'</b>');
						$('#submit_buttons').val('Edit');
						$('#actions').val("column_edit_restriction_wpte");
					}
				})
			});
			$(document).on('click', '.column_style_xs', function(){
				var id = $(this).attr("id");
				var xsnonce = $(this).data("xsnonce");
				var action = 'column_single_wpte';
				$.ajax({
					url:xs_ajax_column.ajax_url,
					method:"POST",
					data:{column_id:id, action:action, table_id:xs_ajax_column.table_id, _xsnonce:xsnonce},
					dataType:"json",
					success:function(data){
						$('#styleModal').modal('show');
						$('#styleForm')[0].reset();
						JSON.parse(xs_ajax_column.columns).forEach(function(item, index){
							if(data[item]){
								$('#'+item).val(data[item]);
							}
						});
						JSON.parse(xs_ajax_column.colors).forEach(function(item, index){
							if(data['column_'+item] && data['column_'+item] !== ''){
								$('#'+item).val(data['column_'+item]);
							}
						});
						JSON.parse(data.column_control).forEach(function(item, index){
							$('#'+item).prop("checked", true);
						});
						$('#column_names_2').val(data.column_names);
						$('.modal-title').html('<b>'+xs_ajax_localize.edit_style+'</b>');
						$('#table_id_3').val(xs_ajax_column.table_id);
						$('#column_id_3').val(id);
						$('#_xsnonce_3').val(xsnonce);
						$('#action_3').val("column_style_xs");
						$('#submit_button_3').val("Edit");
					}
				})
			});
			$(document).on('click', '.column_delete_wpte', function(){
				var id = $(this).data('id');
				var xsnonce = $(this).data("xsnonce");
				if(confirm(xs_ajax_localize.delete_column)){
					$.ajax({
						url:xs_ajax_column.ajax_url,
						method:"POST",
						data:{column_id:id, action:'column_delete_wpte', table_id:xs_ajax_column.table_id, _xsnonce:xsnonce},
						success:function(data){
							$('#alert_column').fadeIn().html(data);
							columnRecords.ajax.reload(null, false);
						}
					});
				}
			});
			$('#xs-select-all').click(function() {
				var isChecked = $(this).prop("checked");
				$('#wptableeditor_column tr:has(td)').find('input[type="checkbox"]').prop('checked', isChecked);
			});
			$('#wptableeditor_column tbody').on('change', 'input[type="checkbox"]', function(){
				var isChecked = $(this).prop("checked");
				var isHeaderChecked = $("#xs-select-all").prop("checked");
				if (isChecked === false && isHeaderChecked)
					$("#xs-select-all").prop('checked', isChecked);
				else {
					$('#wptableeditor_column tr:has(td)').find('input[type="checkbox"]').each(function() {
						if ($(this).prop("checked") === false)
						isChecked = false;
					});
					$("#xs-select-all").prop('checked', isChecked);
				}
			});
			$('#column_multi_active_wpte').click(function(){
				if(confirm(xs_ajax_localize.status_column)){
					var id = [];
					var action = "column_multi_active_wpte";
					$(':checkbox:checked').each(function(i){
						if(!isNaN($(this).val())){
							id[i] = $(this).val();
						}
					});
					if(id.length === 0){
						alert(xs_ajax_localize.least_checkbox);
					}else{
						id = id.filter(item => item);
						$.ajax({
							url:xs_ajax_column.ajax_url,
							method:"POST",
							data:{column_id:id, action:action, table_id:xs_ajax_column.table_id, _xsnonce:xs_ajax_column.xsnonce},
							success:function(data){
								$('#alert_column').fadeIn().html(data);
								$("#xs-select-all").prop('checked', false);
								columnRecords.ajax.reload(null, false);
							}
						});
					}
				}else{
					return false;
				}
			});
			$('#column_multi_inactive_wpte').click(function(){
				if(confirm(xs_ajax_localize.status_column)){
					var id = [];
					var action = "column_multi_inactive_wpte";
					$(':checkbox:checked').each(function(i){
						if(!isNaN($(this).val())){
							id[i] = $(this).val();
						}
					});
					if(id.length === 0){
						alert(xs_ajax_localize.least_checkbox);
					}else{
						id = id.filter(item => item);
						$.ajax({
							url:xs_ajax_column.ajax_url,
							method:"POST",
							data:{column_id:id, action:action, table_id:xs_ajax_column.table_id, _xsnonce:xs_ajax_column.xsnonce},
							success:function(data){
								$('#alert_column').fadeIn().html(data);
								$("#xs-select-all").prop('checked', false);
								columnRecords.ajax.reload(null, false);
							}
						});
					}
				}else{
					return false;
				}
			});
			$('#column_multi_duplicate_wpte').click(function(){
				if(confirm(xs_ajax_localize.duplicate_column)){
					var id = [];
					var action = "column_multi_duplicate_wpte";
					$(':checkbox:checked').each(function(i){
						if(!isNaN($(this).val())){
							id[i] = $(this).val();
						}
					});
					if(id.length === 0){
						alert(xs_ajax_localize.least_checkbox);
					}else{
						id = id.filter(item => item);
						$.ajax({
							url:xs_ajax_column.ajax_url,
							method:"POST",
							data:{column_id:id, action:action, table_id:xs_ajax_column.table_id, _xsnonce:xs_ajax_column.xsnonce},
							success:function(data){
								$('#alert_column').fadeIn().html(data);
								$("#xs-select-all").prop('checked', false);
								columnRecords.ajax.reload(null, false);
							}
						});
					}
				}else{
					return false;
				}
			});
			$('#column_multi_delete_wpte').click(function(){
				if(confirm(xs_ajax_localize.delete_column)){
					var id = [];
					var action = "column_multi_delete_wpte";
					$(':checkbox:checked').each(function(i){
						if(!isNaN($(this).val())){
							id[i] = $(this).val();
						}
					});
					if(id.length === 0){
						alert(xs_ajax_localize.least_checkbox);
					}else{
						id = id.filter(item => item);
						$.ajax({
							url:xs_ajax_column.ajax_url,
							method:"POST",
							data:{column_id:id, action:action, table_id:xs_ajax_column.table_id, _xsnonce:xs_ajax_column.xsnonce},
							success:function(data){
								$('#alert_column').fadeIn().html(data);
								$("#xs-select-all").prop('checked', false);
								columnRecords.ajax.reload(null, false);
							}
						});
					}
				}else{
					return false;
				}
			});
			$('#column_filters').change(function(){
				var column_filters = $('#column_filters').val();
				if(column_filters === 'no'){
					$('#column_position').attr('disabled', true);
					$('#column_filter').attr('disabled', true);
					$('#column_optionlimit').attr('disabled', true);
					$('#column_characterlimit').attr('disabled', true);
					$('#custom_filter').hide();
				}else{
					$('#column_position').attr('disabled', false);
					$('#column_filter').attr('disabled', false);
					if(xs_ajax_column.license === true){
						$('#column_optionlimit').attr('disabled', false);
						$('#column_characterlimit').attr('disabled', false);
					}
					var column_filter = $('#column_filter').val();
					if(column_filter === 'yes'){
						$('#custom_filter').show();
					}
				}
				
			});
			$('#column_restriction').change(function(){
				var column_restriction = $('#column_restriction').val();
				if(column_restriction === 'no'){
					$('#column_restrictiontitle').attr('disabled', true);
				}else{
					$('#column_restrictiontitle').attr('disabled', false);
				}
			});
			$('#column_type').change(function(){
				var column_type = $('#column_type').val();
				if(column_type === 'select'){
					$('#custom_type').show();
				}else{
					$('#custom_type').hide();
				}
			});
			$('#column_filter').change(function(){
				var column_filter = $('#column_filter').val();
				if(column_filter === 'no'){
					$('#custom_filter').hide();
				}else{
					$('#custom_filter').show();
				}
			});
			var count = 1;
			function add_dynamic_filter(count){
				var button = '';
				if(count > 1){
					button = '<button type="button" name="remove" id="'+count+'" class="btn btn-danger btn-xs remove">x</button>';
				}else{
					button = '<button type="button" name="add_more" id="add_more" class="btn btn-success btn-xs">+</button>';
				}
				var output = '<tr id="row'+count+'">';
				output += '<td class="xs-p-5"><input type="text" name="column_customfilter[]" placeholder="option" class="form-control name_list" /></td>';
				output += '<td class="text-center xs-w-45px">'+button+'</td></tr>';
				$('#dynamic_filter').append(output);
			}
			function add_dynamic_type(count){
				var button = '';
				if(count > 1){
					button = '<button type="button" name="remove_option" id="'+count+'" class="btn btn-danger btn-xs remove_option">x</button>';
				}else{
					button = '<button type="button" name="add_option" id="add_option" class="btn btn-success btn-xs">+</button>';
				}
				var output = '<tr id="rows'+count+'">';
				output += '<td class="xs-p-5"><input type="text" name="column_customtype[]" placeholder="option" class="form-control name_list" /></td>';
				output += '<td class="text-center xs-w-45px">'+button+'</td></tr>';
				$('#dynamic_type').append(output);
			}
			$(document).on('click', '#add_more', function(){
				count = count + 1;
				add_dynamic_filter(count);
			});
			$(document).on('click', '.remove', function(){
				var row_id = $(this).attr("id");
				$('#row'+row_id).remove();
			});
			$(document).on('click', '#add_option', function(){
				count = count + 1;
				add_dynamic_type(count);
			});
			$(document).on('click', '.remove_option', function(){
				var row_id = $(this).attr("id");
				$('#rows'+row_id).remove();
			});
			JSON.parse(xs_ajax_column.colors).forEach(function(item, index){
				$("#"+item).on('keyup change clear input',function(){
					$("#column_"+item).val($("#"+item).val());
				});
				$("#column_"+item).on('keyup change clear input',function(){
					if($("#column_"+item).val().length < 7){
						$("#"+item).val('#ffffff');
					}else{
						$("#"+item).val($("#column_"+item).val());
					}
				});
			});
		}
	});
})( jQuery );