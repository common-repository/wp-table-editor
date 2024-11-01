/** @wptableeditor **/
( function( $ ) {
	'use strict';
	$(document).ready(function(){
		if(typeof(xs_ajax_row) != "undefined" && xs_ajax_row !== null) {
			$.fn.dataTable.ext.buttons.reload = {
				className: 'buttons-reload',
				action: function ( e, dt, node, config ) {
					$("input[type='text']").each(function () { 
						$(this).val(''); 
					})
					$("select").val('');
					$('input:checkbox').prop('checked', false);
					dt.columns().every( function () {
						var column = this;
						column.search( '' ).draw();
					} );
					dt.search('').draw();
					dt.searchBuilder.rebuild();
					dt.searchPanes.rebuildPane();
					dt.state.clear();
					dt.page.len(JSON.parse(xs_ajax_row.table_length)).draw();
					dt.order(JSON.parse(xs_ajax_row.table_sortingtype)).draw();
					dt.columns(JSON.parse(xs_ajax_row.column_hiddens)).visible( false );
					if(xs_ajax_row.table_type === 'default'){
						dt.columns([-4]).visible( false );
					}else{
						dt.columns([-3]).visible( false );
					}
					dt.columns(JSON.parse(xs_ajax_row.column_show)).visible( true );
					dt.columns.adjust().draw();
					dt.rows().deselect();
					dt.columns().deselect();
					dt.cells().deselect();
				}
			};
			$.fn.dataTable.ext.buttons.json = {
				className: 'buttons-json',
				action: function ( e, dt, button, config ) {
					var data = dt.buttons.exportData();
					$.fn.dataTable.fileSave(
						new Blob( [ JSON.stringify( data ) ] ),
						'Export.json'
					);
				}
			};
			$.fn.dataTable.renders = function ( item ) {
				if(item === ''){
					return $.fn.dataTable.render.text();
				}
				if(item.replace(/dataTable.render/gi, "") !== item){
					return eval(item);
				}
				return function ( data, type, row ) {
					return eval(item);
				}
			};
			function columnDefs(){
				var column_priority = JSON.parse(xs_ajax_row.column_priority);
				var columnDefs = '[';
				$.each(column_priority, function(key, value) {
					columnDefs += '{"responsivePriority":'+value+',"targets":'+key+'},';
				});
				if($.inArray(xs_ajax_row.table_type, ['json', 'sheet']) !== -1){
					columnDefs += '{"targets":[0],"visible": false,"className": "noVis text-center never"},';
				}
				columnDefs += '{"targets":'+xs_ajax_row.column_filters+',"searchPanes": {"show": true}},';
				columnDefs += xs_ajax_row.column_noVis;
				columnDefs += '{"targets":'+xs_ajax_row.column_all+',"className": "text-center","orderable":false},';
				columnDefs += '{"targets":'+xs_ajax_row.column_hiddens+',"visible": false,"orderable":false,"searchable": true,"className": "noVis never"},';
				columnDefs += xs_ajax_row.priority;
				columnDefs += '{"targets":'+xs_ajax_row.column_none+', "className": "none"},';
				columnDefs += '{"targets":'+xs_ajax_row.column_left+',"className": "text-left"},';
				columnDefs += '{"targets":'+xs_ajax_row.column_center+',"className": "text-center"},';
				columnDefs += '{"targets":'+xs_ajax_row.column_right+',"className": "text-right"},';
				columnDefs += '{"targets":'+xs_ajax_row.column_orderable+',"orderable": false},';
				columnDefs += '{"targets":'+xs_ajax_row.column_searchable+',"searchable": false},';
				columnDefs += '{"targets":'+xs_ajax_row.column_nowrap+',"className": "dt-nowrap"},';
				columnDefs += ']';
				var columnDefs = JSON.parse(columnDefs.replace("},]", "}]"));
				xs_ajax_row.column_render.forEach(function(item, index){
					var index = index + 1;
					if(item !== ''){
						columnDefs.push({targets: index,render: $.fn.dataTable.renders( item )});
					}
				});
				return columnDefs;
			}
			function buttons(){
				var buttons = '[';
				if(xs_ajax_row.table_visibility === 'yes'){
					buttons += '{"extend": "colvis","columns": ":not(.noVis)"},';
				}
				if(xs_ajax_row.table_button === 'yes'){
					buttons += '{"extend": "collection","text": "'+xs_ajax_localize.export+'","className": "custom-html-collection","buttons": [{"extend": "json","text": "JSON"},{"extend": "excelHtml5","footer": true,"exportOptions": {"columns": ":visible"},"title": ""},{"extend": "csvHtml5","footer": true,"exportOptions": {"columns": ":visible"},"title": ""},{"extend": "print","footer": true,"exportOptions": {"stripHtml" : false,"columns": ":visible"},"title": ""}]},';
				}
				if(xs_ajax_row.table_select === 'yes'){
					buttons += '{"extend": "collection","text": "'+xs_ajax_localize.select+'","className": "custom-html-collection","buttons": ["selectNone","selectRows","selectColumns","selectCells"]},';
				}
				if(xs_ajax_row.table_searchbuilder === 'yes'){
					buttons += '{"extend": "searchBuilder","text": "'+xs_ajax_localize.searchBuilder+'","config": {"depthLimit": 2,"columns": '+xs_ajax_row.column_filters+'}},';
				}
				if(JSON.parse(xs_ajax_row.serverSide) !== true && xs_ajax_row.table_searchpanes === 'yes'){
					buttons += '{"extend": "searchPanes","text": "'+xs_ajax_localize.searchPanes+'","config": {"columns": '+xs_ajax_row.column_filters+'}},';
				}
				buttons += '{"extend": "reload","text": "'+xs_ajax_localize.reset+'","header": true},';
				buttons += ']';
				var buttons = buttons.replace("},]", "}]");
				return buttons;
			}
			if(xs_ajax_row.table_type === 'json'){
				var ajax = {
					"url": xs_ajax_row.table_url,
					"dataSrc": xs_ajax_row.table_datasrc,
				};
			}else if(xs_ajax_row.table_type === 'sheet'){
				var ajax = {
					"url": xs_ajax_row.spreadsheets,
					"dataSrc": 'values',
				};
			}else{
				var ajax = {
					"url": xs_ajax_row.ajax_url,
					"type": "POST",
					"data": {action:"row_getdata_wpte", table_id:xs_ajax_row.table_id, xs_type:xs_ajax_row.table_type, _xsnonce:xs_ajax_row.xsnonce},
				};
			}
			if($(window).width() <= 600){
				var pagingType = xs_ajax_row.pagination_simple;
			}else{
				var pagingType = xs_ajax_row.table_pagination;
			}
			var parameter = {
				"processing": true,
				"ajax": ajax,
				"columnDefs": columnDefs(),
				"pagingType": JSON.parse(pagingType),
				"lengthMenu": JSON.parse(xs_ajax_row.pagelength),
				"pageLength": JSON.parse(xs_ajax_row.table_length),
				"deferRender": true,
				"responsive": JSON.parse(xs_ajax_row.responsive),
				"scrollX": JSON.parse(xs_ajax_row.scrollX),
				fixedColumns: {
					left: JSON.parse(xs_ajax_row.table_fixedleft),
					right: JSON.parse(xs_ajax_row.table_fixedright)
				},
				"language": {
					"decimal": ',',
					"thousands": '.',
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
					searchBuilder: {title: {0: '', _: 'Filters (%d)'},button: xs_ajax_localize.searchBuilder},
					searchPanes: {collapse: xs_ajax_localize.searchPanes},
					buttons: {colvis: xs_ajax_localize.visibility}
				},
				"paging":   true,
				"searching": true,
				fixedHeader: {
					header: JSON.parse(xs_ajax_row.header),
					footer: JSON.parse(xs_ajax_row.footer),
				},
				"keys": JSON.parse(xs_ajax_row.table_keytable),
				"select": JSON.parse(xs_ajax_row.select),
				"dom": xs_ajax_row.dom,
				"buttons": JSON.parse(buttons()),
				"columns": JSON.parse(xs_ajax_row.columns),
				"serverSide": JSON.parse(xs_ajax_row.serverSide),
				"stateSave": JSON.parse(xs_ajax_row.stateSave),
				initComplete: function () {
					if(xs_ajax_row.table_filter === 'yes'){
						buildSelect( rowRecords );
						rowRecords.on( 'draw', function () {
							buildSelect( rowRecords );
						} );
					}else{
						buildSelect( this.api() );
					}
					if(xs_ajax_row.table_footer === 'yes' && JSON.parse(xs_ajax_row.column_search).length > 0){
						this.api().columns().every( function () {
							var that = this;
							$( 'input', this.footer() ).on( 'keyup change clear input', function () {
								if ( that.search() !== this.value.trim() ) {
									that.search( this.value.trim(), true, false ).draw();
								}
							} );
						} );
					}
					this.api().columns(JSON.parse(xs_ajax_row.select_status)).every( function () {
						var column = this;
						var select = $('<select id="select_status"><option value="">'+xs_ajax_localize.status+'</option></select>')
							.appendTo("#"+xs_ajax_row.html_id+"_select")
							.on( 'change', function () {
								var val = $.fn.dataTable.util.escapeRegex(
									$(this).val()
								);
								column.search( val ? '^'+val+'$' : '', true, false ).draw();
							} );
						select.append( '<option value="Active">Active</option><option value="Inactive">Inactive</option>' );
					} );
				},
				"footerCallback": function ( row, data, start, end, display ) {
					var api = this.api();
					var intVal = function ( i ) {
						return typeof i === 'string' ?
							i.replace(/[\$,]/g, '')*1 :
							typeof i === 'number' ?
								i : 0;
					};
					JSON.parse(xs_ajax_row.column_total).forEach(function(item, index){
						$('#'+item).attr('disabled', false);
						var total = api
							.column( item )
							.data()
							.reduce( function (a, b) {
								return intVal(a) + intVal(b);
							}, 0 );
						var pageTotal = api
							.column( item, { page: 'current'} )
							.data()
							.reduce( function (a, b) {
								return intVal(a) + intVal(b);
							}, 0 );
						$( api.column( item ).footer() ).html(
							(Math.round(pageTotal * 100)/100).toLocaleString() +'/'+ (Math.round(total * 100)/100).toLocaleString()
						);
					});
				},
				"orderFixed": JSON.parse(xs_ajax_row.group),
				"rowGroup": JSON.parse([xs_ajax_row.rowGroup]),
				"order" : JSON.parse(xs_ajax_row.table_sortingtype),
			};
			if(xs_ajax_row.table_responsive === 'collapse' && xs_ajax_row.table_responsivetype === 'modal'){
				var xstablemodal = 'table';
				if($.inArray(xs_ajax_row.table_type, ['json', 'sheet']) !== -1){
					var xstablemodal = 'xstablemodal';
				}else if($.inArray(xs_ajax_row.table_type, ['product', 'order', 'post', 'page']) !== -1){
					var xstablemodal = 'xstablemodal_3';
				}else if(xs_ajax_row.table_type === 'default'){
					var xstablemodal = 'xstablemodal_2';
				}
				parameter["responsive"] = {
					details: {
						display: $.fn.dataTable.Responsive.display.modal( {
							header: function ( row ) {
								var data = row.data();
								return 'Details';
							}
						} ),
						renderer: $.fn.dataTable.Responsive.renderer.tableAll({tableClass: xstablemodal})
					}
				};
			}
			if(JSON.parse(xs_ajax_row.serverSide) !== true && xs_ajax_row.dataSet !== ''){
				parameter["ajax"] = false;
				parameter["data"] = JSON.parse(xs_ajax_row.dataSet);
			}
			$.fn.dataTable.ext.errMode = 'none';
			var rowRecords = $('#'+xs_ajax_row.html_id).DataTable(parameter);
			function buildSelect( table ) {
				var column_optionlimit = xs_ajax_row.column_optionlimit;
				var column_characterlimit = xs_ajax_row.column_characterlimit;
				$.each(xs_ajax_row.filter_default, function(key, value) {
					table.columns(key).every( function () {
						var column = table.column( this, {search: 'applied'} );
						var select = $('<select id="'+xs_ajax_row.html_id+'_select_'+key+'"><option value="">'+xs_ajax_row.names[key]+'</option></select>')
							.appendTo( $("#"+xs_ajax_row.html_id+'_selects_'+key).empty() )
							.on( 'change', function () {
								var val = $.fn.dataTable.util.escapeRegex(
									$(this).val()
								);
								if(value != ''){
									column.search( val ? val : '', true, false ).draw();
								}else{
									column.search( val ? '^'+val+'$' : '', true, false ).draw();
								}
							} );
						if(value != ''){
							select.append(value);
						}else{
							column.data().unique().sort().each( function ( d, j ) {
								if( column_optionlimit[key] <= 0 ) {
									column_optionlimit[key] = 50;
								}
								if(j < column_optionlimit[key]){
									var val = $('<div/>').html(d).text();
									if( column_characterlimit[key] > 0 && val.length > column_characterlimit[key] ) {
										select.append( '<option value="'+val+'">'+val.substr(0,column_characterlimit[key])+'...</option>' );
									}else if( val.length !== 0 ) {
										select.append( '<option value="'+val+'">'+val+'</option>' );
									}
								}
							} );
						}
						var currSearch = column.search();
						if ( currSearch ) {
							if(value != ''){
								select.val( currSearch.replace(/[\\]/g, '') );
							}else{
								select.val( currSearch.substring(1, currSearch.length-1).replace(/[\\]/g, '') );
							}
						}
					});
				});
				$.each(xs_ajax_row.filter_footer, function(key, value) {
					table.columns(key).every( function () {
						var column = table.column( this, {search: 'applied'} );
						var select = $('<select id="'+xs_ajax_row.html_id+'_select_'+key+'" class="w-100;"><option value="">'+xs_ajax_row.names[key]+'</option></select>')
							.appendTo( $(column.footer()).empty() )
							.on( 'change', function () {
								var val = $.fn.dataTable.util.escapeRegex(
									$(this).val()
								);
								if(value != ''){
									column.search( val ? val : '', true, false ).draw();
								}else{
									column.search( val ? '^'+val+'$' : '', true, false ).draw();
								}
							} );
						if(value != ''){
							select.append(value);
						}else{
							column.data().unique().sort().each( function ( d, j ) {
								if( column_optionlimit[key] <= 0 ) {
									column_optionlimit[key] = 50;
								}
								if(j < column_optionlimit[key]){
									var val = $('<div/>').html(d).text();
									if( column_characterlimit[key] > 0 && val.length > column_characterlimit[key] ) {
										select.append( '<option value="'+val+'">'+val.substr(0,column_characterlimit[key])+'...</option>' );
									}else if( val.length !== 0 ) {
										select.append( '<option value="'+val+'">'+val+'</option>' );
									}
								}
							} );
						}
						var currSearch = column.search();
						if ( currSearch ) {
							if(value != ''){
								select.val( currSearch.replace(/[\\]/g, '') );
							}else{
								select.val( currSearch.substring(1, currSearch.length-1).replace(/[\\]/g, '') );
							}
						}
					});
				});
			}
			if(JSON.parse(xs_ajax_row.serverSide) === true){
				rowRecords.on('draw.dt', function () {
					var info = rowRecords.page.info();
					JSON.parse(xs_ajax_row.column_index).forEach(function(item, index){
						rowRecords.column(item, { search: 'applied', order: 'applied', page: 'applied' }).nodes().each(function (cell, i) {
							cell.innerHTML = i + 1 + info.start;
						});
					});
				});
			}else if($.inArray(xs_ajax_row.table_type, ['json', 'sheet']) !== -1){
				rowRecords.on('draw.dt', function () {
					var info = rowRecords.page.info();
					JSON.parse(xs_ajax_row.column_index).forEach(function(item, index){
						rowRecords.column(item, { search: 'applied', order: 'applied', page: 'current' }).nodes().each(function (cell, i) {
							cell.innerHTML = i + 1 + info.start;
						});
					});
				});
			}else{
				rowRecords.on('order.dt search.dt', function () {
					JSON.parse(xs_ajax_row.column_index).forEach(function(item, index){
						var i = 1;
						rowRecords.cells(null, item, { search: 'applied', order: 'applied' }).every(function (cell) {
							this.data(i++);
						});
					});
				});
			}
			JSON.parse(xs_ajax_row.column_search).forEach(function(item, index){
				$('#'+xs_ajax_row.html_id+'_foot_'+item).each( function () {
					var title = $(this).text();
					$(this).html( '<input type="text" placeholder="'+title+'" />' );
				} );
			});
			$('.dataTables_filter input').off().on('keyup change clear input', function() {
				$('#'+xs_ajax_row.html_id).DataTable().search(this.value.trim(), true, false).draw();
			});
			$('#xs-select-all').on('click', function(){
				var rows = rowRecords.rows({ 'search': 'applied' }).nodes();
				$('input[type="checkbox"]', rows).prop('checked', this.checked);
			});
			$('#'+xs_ajax_row.html_id+' tbody').on('change', 'input[type="checkbox"]', function(){
				var isChecked = $(this).prop("checked");
				var isHeaderChecked = $("#xs-select-all").prop("checked");
				if (isChecked === false && isHeaderChecked)
					$("#xs-select-all").prop('checked', isChecked);
				else {
					$('#'+xs_ajax_row.html_id+' tr:has(td)').find('input[type="checkbox"]').each(function() {
						if ($(this).prop("checked") === false)
						isChecked = false;
					});
					$("#xs-select-all").prop('checked', isChecked);
				}
			});
			$('#xsrowreorder').click(function() {
				var isChecked = $(this).prop("checked");
				if (isChecked === false){
					$("#xsrow_body").removeClass('xsortHandle').sortable( "disable" );
				}else{
					$('#xsrow_body').addClass('xsortHandle').sortable({
						placeholder : "ui-state-highlight",
						update : function(event, ui){
							var id = new Array();
							$('#'+xs_ajax_row.html_id+' tbody tr').each(function(){
								id.push($(this).attr('id'));
							});
							var info = rowRecords.page.info();
							if(confirm(xs_ajax_localize.order_row)){
								$.ajax({
									url:xs_ajax_row.ajax_url,
									method:"POST",
									data:{row_id:id, start:info.start, action:'row_update_order_wpte', table_id:xs_ajax_row.table_id, xs_type:xs_ajax_row.table_type, _xsnonce:xs_ajax_row.xsnonce},
									success:function(){
										$('#alert_row').fadeIn().html('<div class="alert alert-success">'+xs_ajax_localize.updated_row+'</div>');
										rowRecords.ajax.reload(null, false);
									}
								})
							}else{
								return false;
							}
						}
					});
					$("#xsrow_body").sortable( "enable" );
				}
			});
			$('#row_form').on('submit', function(event){
				event.preventDefault();
				var form_data = $(this).serialize();		
				$.ajax({
					url:xs_ajax_row.ajax_url,
					method:"POST",
					data:form_data,
					success:function(data){
						$('#rowModal').modal('hide');
						$('#row_form')[0].reset();
						$('#submit_button').attr('disabled', false);
						$('#alert_row').html(data);
						$("#xs-select-all").prop('checked', false);
						rowRecords.ajax.reload(null, false);
					}
				})
			});
			$(document).on('click', '.row_edit_wpte', function(){
				var id = $(this).data('id');
				var xsnonce = $(this).data("xsnonce");
				$('#form_message').html('');
				$.ajax({
					url:xs_ajax_row.ajax_url,
					method:"POST",
					data:{row_id:id, action:'row_single_wpte', table_id:xs_ajax_row.table_id, xs_type:xs_ajax_row.table_type, _xsnonce:xsnonce},
					dataType:'JSON',
					success:function(data){
						$('#rowModal').modal('show');
						$('#row_form')[0].reset();
						if(xs_ajax_row.table_type === 'default'){
							$.each(xs_ajax_row.column_orders, function(key, value) {
								$('#column_'+key).val(data['column_'+key]);
							});
						}else{
							$('#column_custom').val(data.column_custom);
						}
						$('#modal_title').html('<b>'+xs_ajax_localize.edit_row+'</b>');
						$('#action').val('row_edit_wpte');
						$('#submit_button').val('Edit');
						$('#row_id').val(id);
						$('#column_order').val(data.column_order);
						$('#column_status').val(data.column_status);
						if(data.length == 0){
							$('#column_order').val(id);
						}
						$('#_xsnonce').val(xsnonce);
					}
				})
			});
			$('#row_add_wpte').click(function(){
				$('#rowModal').modal('show');
				$('#row_form')[0].reset();
				$('#modal_title').html('<b>'+xs_ajax_localize.add_row+'</b>');
				$('#action').val('row_add_wpte');
				$('#_xsnonce').val(xs_ajax_row.xsnonce);
				$('#submit_button').val('Add');
				$('#form_message').html('');
			});
			$(document).on('click', '.row_delete_wpte', function(){
				var id = $(this).data('id');
				var xsnonce = $(this).data("xsnonce");
				if(confirm(xs_ajax_localize.delete_row)){
					$.ajax({
						url:xs_ajax_row.ajax_url,
						method:"POST",
						data:{row_id:id, action:'row_delete_wpte',table_id:xs_ajax_row.table_id, _xsnonce:xsnonce},
						success:function(data){
							$('#alert_row').html(data);
							$("#xs-select-all").prop('checked', false);
							rowRecords.ajax.reload(null, false);
						}
					});
				}
			});
			$(document).on('click', '.row_edit_status_wpte', function(){
				var id = $(this).attr('id');
				var status = $(this).data("status");
				var xsnonce = $(this).data("xsnonce");
				var action = 'row_edit_status_wpte';
				if(confirm(xs_ajax_localize.status_row)){
					$.ajax({
						url:xs_ajax_row.ajax_url,
						method:"POST",
						data:{row_id:id, status:status, action:action, table_id:xs_ajax_row.table_id, xs_type:xs_ajax_row.table_type, _xsnonce:xsnonce},
						success:function(data){
							$('#alert_row').fadeIn().html(data);
							$("#xs-select-all").prop('checked', false);
							rowRecords.ajax.reload(null, false);
						}
					})
				}else{
					return false;
				}
			});
			$('#row_multi_active_wpte').click(function(){
				if(confirm(xs_ajax_localize.status_row)){
					var id = [];
					var action = "row_multi_active_wpte";
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
							url:xs_ajax_row.ajax_url,
							method:"POST",
							data:{row_id:id, action:action, table_id:xs_ajax_row.table_id, xs_type:xs_ajax_row.table_type, _xsnonce:xs_ajax_row.xsnonce},
							success:function(data){
								$('#alert_row').fadeIn().html(data);
								$("#xs-select-all").prop('checked', false);
								rowRecords.ajax.reload(null, false);
							}
						});
					}
				}else{
					return false;
				}
			});
			$('#row_multi_inactive_wpte').click(function(){
				if(confirm(xs_ajax_localize.status_row)){
					var id = [];
					var action = "row_multi_inactive_wpte";
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
							url:xs_ajax_row.ajax_url,
							method:"POST",
							data:{row_id:id, action:action, table_id:xs_ajax_row.table_id, xs_type:xs_ajax_row.table_type, _xsnonce:xs_ajax_row.xsnonce},
							success:function(data){
								$('#alert_row').fadeIn().html(data);
								$("#xs-select-all").prop('checked', false);
								rowRecords.ajax.reload(null, false);
							}
						});
					}
				}else{
					return false;
				}
			});
			$('#row_multi_duplicate_wpte').click(function(){
				if(confirm(xs_ajax_localize.duplicate_row)){
					var id = [];
					var action = "row_multi_duplicate_wpte";
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
							url:xs_ajax_row.ajax_url,
							method:"POST",
							data:{row_id:id, action:action, table_id:xs_ajax_row.table_id, _xsnonce:xs_ajax_row.xsnonce},
							success:function(data){
								$('#alert_row').fadeIn().html(data);
								$("#xs-select-all").prop('checked', false);
								rowRecords.ajax.reload(null, false);
							}
						});
					}
				}else{
					return false;
				}
			});
			$('#row_multi_delete_wpte').click(function(){
				if(confirm(xs_ajax_localize.delete_row)){
					var id = [];
					var action = "row_multi_delete_wpte";
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
							url:xs_ajax_row.ajax_url,
							method:"POST",
							data:{row_id:id, action:action, table_id:xs_ajax_row.table_id, _xsnonce:xs_ajax_row.xsnonce},
							success:function(data){
								$('#alert_row').fadeIn().html(data);
								$("#xs-select-all").prop('checked', false);
								rowRecords.ajax.reload(null, false);
							}
						});
					}
				}else{
					return false;
				}
			});
		}
	});
})( jQuery );