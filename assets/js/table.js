/** @wptableeditor **/
( function( $ ) {
	'use strict';
	$(document).ready(function(){
		if(typeof(xs_ajax_table) != "undefined" && xs_ajax_table !== null) {
			if(xs_ajax_table.license === false){
				$('[data-toggle="tooltip"]').attr('title',xs_ajax_localize.license).tooltip().attr('disabled', true);
			}else{
				JSON.parse(xs_ajax_table.tables).forEach(function(item, index){
					$('#'+item).attr('disabled', false);
				});
			}
			$.fn.dataTable.ext.errMode = 'none';
			var tableRecords = $('#wptableeditor_table').DataTable({
				"processing": true,
				"ajax": {
					"url": xs_ajax_table.ajax_url,
					"type": "POST",
					"data" : {action: "table_getdata_wpte", _xsnonce:xs_ajax_table.xsnonce},
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
							$("#select_type").val('');
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
							dt.order([[6, 'asc']]).draw();
							dt.columns([5, 6]).visible( false );
							dt.columns([1, 2, 3, 4, 7, 8, 9, 10, 11]).visible( true );
							dt.columns.adjust().draw();
							dt.rows().deselect();
							dt.columns().deselect();
							dt.cells().deselect();
						}
					},
				],
				"columnDefs":[
					{
						"targets":[0, 7, 8, 9, 10, 11],
						"className": "text-center",
						"orderable":false
					},
					{
						"targets":[5],
						"visible": false,
						"orderable":true,
						"searchable": true,
					},
					{"targets": [0, 4, -1, -2, -3, -4, -5], "className": 'noVis text-center'},
					{"targets": [-6], "visible": false, "className": 'text-center'},
					{"targets": [2], "className": 'dt-nowrap'},
				],
				initComplete: function () {
					this.api().columns([3]).every( function () {
						var column = this;
						var select = $('<select id="select_type"><option value="">'+xs_ajax_localize.type+'</option></select>')
							.appendTo("#xs_select.dataTables_length")
							.on( 'change', function () {
								var val = $.fn.dataTable.util.escapeRegex(
									$(this).val()
								);
								column.search( val ? '^'+val+'$' : '', true, false ).draw();
							} );
						column.data().unique().sort().each(function (d, j) {
							select.append('<option value="' + d + '">' + d + '</option>');
						});
					} );
					this.api().columns([7]).every( function () {
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
				"order":[[6, 'asc']],
			});
			$('#xstablereorder').click(function() {
				var isChecked = $(this).prop("checked");
				if (isChecked === false){
					$("#xstable_body").removeClass('xsortHandle').sortable( "disable" );
				}else{
					$('#xstable_body').addClass('xsortHandle').sortable({
						placeholder : "ui-state-highlight",
						update : function(event, ui){
							var id = new Array();
							$('#wptableeditor_table tbody tr').each(function(){
								id.push($(this).attr('id'));
							});
							var info = tableRecords.page.info();
							if(confirm(xs_ajax_localize.order_table)){
								$.ajax({
									url:xs_ajax_table.ajax_url,
									method:"POST",
									data:{table_id:id, start:info.start, action:'table_update_order_wpte', _xsnonce:xs_ajax_table.xsnonce},
									success:function(){
										$('#alert_table').fadeIn().html('<div class="alert alert-success">'+xs_ajax_localize.updated_table+'</div>');
										tableRecords.ajax.reload(null, false);
									}
								})
							}else{
								return false;
							}
						}
					});
					$("#xstable_body").sortable( "enable" );
				}
			});
			$('#table_add_wpte').click(function(){
				$('#tableModal').modal('show');
				$('#tableForm')[0].reset();
				$('#table_column_number').show();
				$('#table_url_field').hide();
				$('#table_sheet_field').hide();
				$('#table_columns').attr('required', true).attr('disabled', false);
				$('#table_type').attr('required', true).attr('disabled', false);
				$('#table_category').attr('disabled', true);
				$('#table_fixedleft').attr('disabled', true);
				$('#table_fixedright').attr('disabled', true);
				if(xs_ajax_table.license === true){
					$('#table_searchpanes').attr('disabled', false);
				}
				$('.modal-title').html('<b>'+xs_ajax_localize.add_table+'</b>');
				$('#action').val('table_add_wpte');
				$('#_xsnonce').val(xs_ajax_table.xsnonce);
				$('#submit_button').val('Add');
			});
			$(document).on('click', '.table_edit_status_wpte', function(){
				var id = $(this).attr('id');
				var status = $(this).data("status");
				var xsnonce = $(this).data("xsnonce");
				var action = 'table_edit_status_wpte';
				if(confirm(xs_ajax_localize.status_table)){
					$.ajax({
						url:xs_ajax_table.ajax_url,
						method:"POST",
						data:{table_id:id, status:status, action:action, _xsnonce:xsnonce},
						success:function(data){
							$('#alert_table').fadeIn().html(data);
							$("#xs-select-all").prop('checked', false);
							tableRecords.ajax.reload(null, false);
						}
					})
				}else{
					return false;
				}
			});
			$(document).on('click', '.table_edit_restriction_wpte', function(){
				var id = $(this).attr("id");
				var xsnonce = $(this).data("xsnonce");
				var action = 'table_restrictionrole_single_wpte';
				$.ajax({
					url:xs_ajax_table.ajax_url,
					method:"POST",
					data:{table_id:id, action:action, _xsnonce:xsnonce},
					dataType:"json",
					success:function(data){
						$('#permissionModal').modal('show');
						$('#permission_form')[0].reset();
						data.table_restrictionrole.forEach(function(item, index){
							$('#'+item).prop("checked", true);
						});
						$('#table_name_3').val(data.table_name);
						$('#table_id_2').val(id);
						$('#_xsnonce_2').val(xsnonce);
						$('.modal-title').html('<b>'+xs_ajax_localize.edit_restriction+'</b>');
						$('#submit_button_2').val('Edit');
						$('#action_2').val("table_edit_restriction_wpte");
					}
				})
			});
			$(document).on('click', '.table_edit_wpte', function(){
				$('#table_columns').attr('required', false);
				$('#table_type').attr('required', false);
				var id = $(this).attr("id");
				var xsnonce = $(this).data("xsnonce");
				var action = 'table_single_wpte';
				$.ajax({
					url:xs_ajax_table.ajax_url,
					method:"POST",
					data:{table_id:id, action:action, _xsnonce:xsnonce},
					dataType:"json",
					success:function(data){
						$('#tableModal').modal('show');
						$('#tableForm')[0].reset();
						JSON.parse(xs_ajax_table.columns).forEach(function(item, index){
							if(data[item]){
								$('#'+item).val(data[item]);
							}
						});
						if(data.table_type === 'default'){
							$('#table_serverside').val(data.table_serverside).attr('disabled', false);
						}else{
							$('#table_serverside').val('no').attr('disabled', true);
							if(xs_ajax_table.license === true){
								$('#table_searchpanes').attr('disabled', false);
							}
						}
						if(xs_ajax_table.license === true){
							JSON.parse(xs_ajax_table.tables).forEach(function(item, index){
								$('#'+item).val(data[item]).attr('disabled', false);
							});
							if(data.table_serverside === 'yes'){
								$('#table_searchpanes').attr('disabled', true);
							}else{
								$('#table_searchpanes').attr('disabled', false);
							}
						}
						$('#table_columns').val(data.table_columns).attr('disabled', true);
						$('#table_type').attr('disabled', true);
						if(data.table_type === 'default' || data.table_type === 'page' || data.table_type === 'json'){
							$('#table_category').val('').attr('disabled', true);
						}else{
							$('#table_category').val(data.table_category).attr('disabled', false);
						}
						if(data.table_type === 'json'){
							$('#table_url_field').show();
							$('#table_url').attr('required', true);
							$('#table_serverside').val('no').attr('disabled', true);
						}else{
							$('#table_url_field').hide();
							$('#table_url').attr('required', false);
						}
						if(data.table_type === 'sheet'){
							$('#table_sheet_field').show();
							$('#table_sheetid').attr('required', true);
							$('#table_apikey').attr('required', true);
							$('#table_sheetname').attr('required', true);
							$('#table_range').attr('required', true);
							$('#table_serverside').val('no').attr('disabled', true);
						}else{
							$('#table_sheet_field').hide();
							$('#table_sheetid').attr('required', false);
							$('#table_apikey').attr('required', false);
							$('#table_sheetname').attr('required', false);
							$('#table_range').attr('required', false);
						}
						if(data.table_responsive === 'collapse'){
							$('#table_fixedleft').attr('disabled', true);
							$('#table_fixedright').attr('disabled', true);
						}else{
							if(xs_ajax_table.license === true){
								$('#table_fixedleft').attr('disabled', false);
								$('#table_fixedright').attr('disabled', false);
							}
						}
						$('#table_id').val(id);
						$('#_xsnonce').val(xsnonce);
						$('.modal-title').html('<b>'+xs_ajax_localize.edit_table+'</b>');
						$('#action').val('table_edit_wpte');
						$('#submit_button').val('Edit');
					}
				})
			});
			$(document).on('click', '.table_style_xs', function(){
				var id = $(this).attr("id");
				var xsnonce = $(this).data("xsnonce");
				var action = 'table_single_wpte';
				$.ajax({
					url:xs_ajax_table.ajax_url,
					method:"POST",
					data:{table_id:id, action:action, _xsnonce:xsnonce},
					dataType:"json",
					success:function(data){
						$('#styleModal').modal('show');
						$('#styleForm')[0].reset();
						JSON.parse(xs_ajax_table.columns).forEach(function(item, index){
							if(data[item]){
								$('#'+item).val(data[item]);
							}
						});
						JSON.parse(xs_ajax_table.colors).forEach(function(item, index){
							if(data['table_'+item] && data['table_'+item] !== ''){
								$('#'+item).val(data['table_'+item]);
							}
						});
						JSON.parse(data.table_dom).forEach(function(item, index){
							$('#'+item).prop("checked", true);
						});
						$('#table_name_2').val(data.table_name);
						$('#table_id_3').val(id);
						$('#_xsnonce_3').val(xsnonce);
						$('.modal-title').html('<b>'+xs_ajax_localize.edit_style+'</b>');
						$('#action_3').val('table_style_xs');
						$('#submit_button_3').val('Edit');
					}
				})
			});
			$(document).on('click', '.table_delete_wpte', function(){
				var id = $(this).data('id');
				var xsnonce = $(this).data("xsnonce");
				var action = "table_delete_wpte";
				if(confirm(xs_ajax_localize.delete_table)) {
					$.ajax({
						url:xs_ajax_table.ajax_url,
						method:"POST",
						data:{table_id:id, action:action, _xsnonce:xsnonce},
						success:function(data){
							$('#alert_table').fadeIn().html(data);
							$("#xs-select-all").prop('checked', false);
							tableRecords.ajax.reload(null, false);
						}
					})
				} else {
					return false;
				}
			});	
			$('#xs-select-all').on('click', function(){
				var rows = tableRecords.rows({ 'search': 'applied' }).nodes();
				$('input[type="checkbox"]', rows).prop('checked', this.checked);
			});
			$('#table_multi_active_wpte').click(function(){
				if(confirm(xs_ajax_localize.status_table)){
					var id = [];
					var action = "table_multi_active_wpte";
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
							url:xs_ajax_table.ajax_url,
							method:"POST",
							data:{table_id:id, action:action, _xsnonce:xs_ajax_table.xsnonce},
							success:function(data){
								$('#alert_table').fadeIn().html(data);
								$("#xs-select-all").prop('checked', false);
								tableRecords.ajax.reload(null, false);
							}
						});
					}
				}else{
					return false;
				}
			});
			$('#table_multi_inactive_wpte').click(function(){
				if(confirm(xs_ajax_localize.status_table)){
					var id = [];
					var action = "table_multi_inactive_wpte";
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
							url:xs_ajax_table.ajax_url,
							method:"POST",
							data:{table_id:id, action:action, _xsnonce:xs_ajax_table.xsnonce},
							success:function(data){
								$('#alert_table').fadeIn().html(data);
								$("#xs-select-all").prop('checked', false);
								tableRecords.ajax.reload(null, false);
							}
						});
					}
				}else{
					return false;
				}
			});
			$('#table_multi_duplicate_wpte').click(function(){
				if(confirm(xs_ajax_localize.duplicate_table)){
					var id = [];
					var action = "table_multi_duplicate_wpte";
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
							url:xs_ajax_table.ajax_url,
							method:"POST",
							data:{table_id:id, action:action, _xsnonce:xs_ajax_table.xsnonce},
							success:function(data){
								$('#alert_table').fadeIn().html(data);
								$("#xs-select-all").prop('checked', false);
								tableRecords.ajax.reload(null, false);
							}
						});
					}
				}else{
					return false;
				}
			});
			$('#table_multi_delete_wpte').click(function(){
				if(confirm(xs_ajax_localize.delete_table)){
					var id = [];
					var action = "table_multi_delete_wpte";
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
							url:xs_ajax_table.ajax_url,
							method:"POST",
							data:{table_id:id, action:action, _xsnonce:xs_ajax_table.xsnonce},
							success:function(data){
								$('#alert_table').fadeIn().html(data);
								$("#xs-select-all").prop('checked', false);
								tableRecords.ajax.reload(null, false);
							}
						});
					}
				}else{
					return false;
				}
			});
			$("#tableModal").on('submit','#tableForm', function(event){
				event.preventDefault();
				$('#submit_button').attr('disabled','disabled');
				var formData = $(this).serialize();
				$.ajax({
					url:xs_ajax_table.ajax_url,
					method:"POST",
					data:formData,
					success:function(data){				
						$('#tableModal').modal('hide');
						$('#tableForm')[0].reset();					
						$('#submit_button').attr('disabled', false);
						$('#alert_table').fadeIn().html(data);
						$("#xs-select-all").prop('checked', false);
						tableRecords.ajax.reload(null, false);
					}
				})
			});
			$("#styleModal").on('submit','#styleForm', function(event){
				event.preventDefault();
				$('#submit_button_3').attr('disabled','disabled');
				var formData = $(this).serialize();
				$.ajax({
					url:xs_ajax_table.ajax_url,
					method:"POST",
					data:formData,
					success:function(data){				
						$('#styleModal').modal('hide');
						$('#styleForm')[0].reset();					
						$('#submit_button_3').attr('disabled', false);
						$('#alert_table').fadeIn().html(data);
						$("#xs-select-all").prop('checked', false);
						tableRecords.ajax.reload(null, false);
					}
				})
			});
			$("#permissionModal").on('submit','#permission_form', function(event){
				event.preventDefault();
				$('#submit_button_2').attr('disabled','disabled');
				var formData = $(this).serialize();
				$.ajax({
					url:xs_ajax_table.ajax_url,
					method:"POST",
					data:formData,
					success:function(data){				
						$('#permission_form')[0].reset();
						$('#permissionModal').modal('hide');				
						$('#submit_button_2').attr('disabled', false);
						$('#alert_table').fadeIn().html(data);
						$("#xs-select-all").prop('checked', false);
						tableRecords.ajax.reload(null, false);
					}
				})
			});
			$('#wptableeditor_table tbody').on('change', 'input[type="checkbox"]', function(){
				var isChecked = $(this).prop("checked");
				var isHeaderChecked = $("#xs-select-all").prop("checked");
				if (isChecked === false && isHeaderChecked)
					$("#xs-select-all").prop('checked', isChecked);
				else {
					$('#wptableeditor_table tr:has(td)').find('input[type="checkbox"]').each(function() {
						if ($(this).prop("checked") === false)
						isChecked = false;
					});
					$("#xs-select-all").prop('checked', isChecked);
				}
			});
			$('#table_type').change(function(){
				var table_type = $('#table_type').val();
				if(table_type === '' || table_type === 'default' || table_type === 'page' || table_type === 'json'){
					$('#table_category').attr('disabled', true);
				}else{
					$('#table_category').attr('disabled', false);
				}
				if(table_type === 'default'){
					$('#table_serverside').attr('disabled', false);
				}else{
					$('#table_serverside').attr('disabled', true);
				}
				if(table_type === 'json'){
					$('#table_url_field').show();
					$('#table_url').attr('required', true);
					$('#table_serverside').val('no');
				}else{
					$('#table_url_field').hide();
					$('#table_url').attr('required', false);
				}
				if(table_type === 'sheet'){
					$('#table_sheet_field').show();
					$('#table_sheetid').attr('required', true);
					$('#table_apikey').attr('required', true);
					$('#table_sheetname').attr('required', true);
					$('#table_range').attr('required', true);
					$('#table_serverside').val('no');
				}else{
					$('#table_sheet_field').hide();
					$('#table_sheetid').attr('required', false);
					$('#table_apikey').attr('required', false);
					$('#table_sheetname').attr('required', false);
					$('#table_range').attr('required', false);
				}
			});
			$('#table_serverside').change(function(){
				var table_serverside = $('#table_serverside').val();
				if(table_serverside === 'yes'){
					$('#table_limit').val('-1');
					$('#table_searchpanes').attr('disabled', true);
				}else{
					$('#table_searchpanes').attr('disabled', false);
				}
			});
			$('#table_limit').change(function(){
				var table_limit = $('#table_limit').val();
				if(table_limit >= 0){
					$('#table_serverside').val('no');
				}
			});
			$('#table_fixedfooter').change(function(){
				var table_fixedfooter = $('#table_fixedfooter').val();
				var table_footer = $('#table_footer').val();
				if(table_fixedfooter === 'yes' && table_footer === 'no'){
					$('#table_footer').val('yes');
				}
				
			});
			$('#table_responsive').change(function(){
				var table_responsive = $('#table_responsive').val();
				if(table_responsive === 'collapse'){
					$('#table_fixedleft').attr('disabled', true);
					$('#table_fixedright').attr('disabled', true);
				}else{
					if(xs_ajax_table.license === true){
						$('#table_fixedleft').attr('disabled', false);
						$('#table_fixedright').attr('disabled', false);
					}
				}
			});
			JSON.parse(xs_ajax_table.colors).forEach(function(item, index){
				$("#"+item).on('keyup change clear input',function(){
					$("#table_"+item).val($("#"+item).val());
				});
				$("#table_"+item).on('keyup change clear input',function(){
					if($("#table_"+item).val().length < 7){
						$("#"+item).val('#ffffff');
					}else{
						$("#"+item).val($("#table_"+item).val());
					}
				});
			});
		}
	});
})( jQuery );