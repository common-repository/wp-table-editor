/**@wptableeditor**/
( function( $ ) {
	'use strict';
	$(document).ready(function(){
		var shortcode = [];
		var shortcodes = [];
		for(let i = 1; i <= 100; i++){
			if(typeof(window['xs_ajax_shortcode'+i]) !== "undefined" && window['xs_ajax_shortcode'+i] !== null){
				shortcode.push(window['xs_ajax_shortcode'+i]);
			}
			if(typeof(window['xs_ajax_shortcodes'+i]) !== "undefined" && window['xs_ajax_shortcodes'+i] !== null){
				shortcodes.push(window['xs_ajax_shortcodes'+i]);
			}
		}
		/*shortcode*/
		$.each(shortcode, function (indexs, values){
			window['xs_ajax_shortcode'] = values;
			if(typeof(xs_ajax_shortcode) !== "undefined" && xs_ajax_shortcode !== null) {
				$( document.body ).on( 'change input', 'input.qty', function() {
					var qty = $( this ),
						atc = $( this ).next( '.add_to_cart_button' );
						atc.attr( 'data-quantity', qty.val() );
				});
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
				function columnDefs(shortcode){
					var column_priority = JSON.parse(shortcode.column_priority);
					var columnDefs = '[';
					$.each(column_priority, function(key, value){
						columnDefs += '{"responsivePriority":'+value+',"targets":'+key+'},';
					});
					columnDefs += '{"targets":'+shortcode.column_filters+',"searchPanes": {"show": true}},';
					for(let i = 0; i <= shortcode.column_count; i++){
						columnDefs += '{"targets":'+i+',"className": "'+shortcode.html_id+'_column_'+i+'"},';
					}
					if($.inArray(shortcode.table_type, ['json', 'sheet']) !== -1){
						columnDefs += '{"targets":[0],"visible": false,"className": "noVis text-center never"},';
					}else{
						columnDefs += '{"targets":[0,-1],"visible": false,"className": "noVis text-center never"},';
					}
					columnDefs += '{"targets":'+shortcode.column_hidden+',"visible": false,"className": "noVis never"},';
					columnDefs += '{"targets":'+shortcode.desktop+',"className": "desktop"},';
					columnDefs += '{"targets":'+shortcode.tablet_l+',"className": "tablet-l"},';
					columnDefs += '{"targets":'+shortcode.tablet_p+',"className": "tablet-p"},';
					columnDefs += '{"targets":'+shortcode.mobile_l+',"className": "mobile-l"},';
					columnDefs += '{"targets":'+shortcode.mobile_p+',"className": "mobile-p"},';
					columnDefs += '{"targets":'+shortcode.column_none+',"className": "none"},';
					columnDefs += '{"targets":'+shortcode.column_left+',"className": "text-left"},';
					columnDefs += '{"targets":'+shortcode.column_center+',"className": "text-center"},';
					columnDefs += '{"targets":'+shortcode.column_right+',"className": "text-right"},';
					columnDefs += '{"targets":'+shortcode.column_orderable+',"orderable": false},';
					columnDefs += '{"targets":'+shortcode.column_searchable+',"searchable": false},';
					columnDefs += '{"targets":'+shortcode.column_nowrap+',"className": "dt-nowrap"},';
					columnDefs += ']';
					var columnDefs = JSON.parse(columnDefs.replace("},]", "}]"));
					shortcode.column_render.forEach(function(item, index){
						var index = index + 1;
						if(item !== ''){
							columnDefs.push({targets: index,render: $.fn.dataTable.renders( item )});
						}
					});
					shortcode.column_createdcell.forEach(function(item, index){
						var index = index + 1;
						if(item !== ''){
							columnDefs.push({targets: index,createdCell: function (td, cellData, rowData, row, col) { eval(item) }});
						}
					});
					return columnDefs;
				}
				function buttons(shortcode){
					var buttons = '[';
					if(shortcode.table_visibility === 'yes'){
						buttons += '{"extend": "colvis","columns": ":not(.noVis)"},';
					}
					if(shortcode.table_button === 'yes'){
						buttons += '{"extend": "collection","text": "'+xs_ajax_localize.export+'","className": "custom-html-collection","buttons": [{"extend": "json","text": "JSON"},{"extend": "excelHtml5","footer": true,"exportOptions": {"columns": ":visible"},"title": ""},{"extend": "csvHtml5","footer": true,"exportOptions": {"columns": ":visible"},"title": ""},{"extend": "print","footer": true,"exportOptions": {"stripHtml" : false,"columns": ":visible"},"title": ""}]},';
					}
					if(shortcode.table_select === 'yes'){
						buttons += '{"extend": "collection","text": "'+xs_ajax_localize.select+'","className": "custom-html-collection","buttons": ["selectNone","selectRows","selectColumns","selectCells"]},';
					}
					if(shortcode.table_searchbuilder === 'yes'){
						buttons += '{"extend": "searchBuilder","text": "'+xs_ajax_localize.searchBuilder+'","config": {"depthLimit": 2,"columns": '+shortcode.column_filters+'}},';
					}
					if(JSON.parse(shortcode.serverSide) !== true && shortcode.table_searchpanes === 'yes'){
						buttons += '{"extend": "searchPanes","text": "'+xs_ajax_localize.searchPanes+'","config": {"columns": '+shortcode.column_filters+'}},';
					}
					buttons += '{"extend": "reload","text": "'+xs_ajax_localize.reset+'","header": true},';
					buttons += ']';
					var buttons = buttons.replace("},]", "}]");
					return buttons;
				}
				function buildSelect(table, shortcode){
					var column_optionlimit = shortcode.column_optionlimit;
					var column_characterlimit = shortcode.column_characterlimit;
					$.each(shortcode.filter_default, function(key, value) {
						table.columns(key).every( function () {
							var column = table.column( this, {search: 'applied'} );
							var select = $('<select id="'+shortcode.html_id+'_select_'+key+'"><option value="">'+shortcode.names[key]+'</option></select>')
								.appendTo( $("#"+shortcode.html_id+'_selects_'+key).empty() )
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
					$.each(shortcode.filter_footer, function(key, value) {
						table.columns(key).every( function () {
							var column = table.column( this, {search: 'applied'} );
							var select = $('<select id="'+shortcode.html_id+'_select_'+key+'" class="xs-mw-100"><option value="">'+shortcode.names[key]+'</option></select>')
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
				function dataRecord(shortcode){
					if(shortcode.table_footer === 'yes'){
						$('#'+shortcode.html_id+'_tfoot').show();
					}
					if(shortcode.dataSet === ''){
						$('#'+shortcode.html_id+'_head').show();
					}
					if(JSON.parse(shortcode.responsive) !== true && JSON.parse(shortcode.scrollY) > 0 && JSON.parse(shortcode.paging) === true){
						$("#"+shortcode.html_id).addClass("nowrap");
						var scroller = true;
					}else{
						var scroller = false;
					}
					var reload = {
						className:'buttons-reload',
						action: function ( e, dt, node, config ) {
							$("input[type='text']").each(function () { 
								$(this).val(''); 
							})
							JSON.parse(shortcode.column_filters).forEach(function(item, index){
								$('#'+shortcode.html_id+'_select_'+item).val('');
							});
							dt.columns().every( function () {
								var column = this;
								column.search( '' ).draw();
							} );
							dt.search('').draw();
							dt.searchBuilder.rebuild();
							dt.searchPanes.rebuildPane();
							dt.state.clear();
							dt.page.len(JSON.parse(shortcode.table_length)).draw();
							dt.order(JSON.parse(shortcode.table_sortingtype)).draw();
							dt.columns(JSON.parse(shortcode.column_show)).visible( true );
							dt.columns.adjust().draw();
							dt.rows().deselect();
							dt.columns().deselect();
							dt.cells().deselect();
						}
					};
					var json = {
						className:'buttons-json',
						action: function ( e, dt, button, config ) {
							var data = dt.buttons.exportData();
							$.fn.dataTable.fileSave(
								new Blob( [ JSON.stringify( data ) ] ),
								'Export.json'
							);
						}
					};
					$.fn.dataTable.ext.buttons.reload = reload;
					$.fn.dataTable.ext.buttons.json = json;
					if(shortcode.table_type === 'json'){
						var ajax = {
							"url": shortcode.table_url,
							"dataSrc": shortcode.table_datasrc,
						};
					}else if(shortcode.table_type === 'sheet'){
						var ajax = {
							"url": shortcode.spreadsheets,
							"dataSrc": 'values',
						};
					}else{
						var ajax = {
							"url": shortcode.ajax_url,
							"type": "POST",
							"data": {action:"row_getdatas_wpte", table_id:shortcode.table_id, xs_type:shortcode.table_type, _xsnonce:shortcode.xsnonce},
						};
					}
					if(shortcode.table_hover === 'yes'){
						$("#"+shortcode.html_id).addClass("hover");
					}
					if(shortcode.table_ordercolumn === 'yes'){
						$("#"+shortcode.html_id).addClass("order-column");
					}
					if(shortcode.table_responsive === 'flip'){
						$("."+shortcode.html_id).addClass("flip-scroll");
					}
					JSON.parse(shortcode.column_search).forEach(function(item, index){
						$('#'+shortcode.html_id+'_foot_'+item).each( function () {
							var title = $(this).text();
							$(this).html( '<input type="text" placeholder="'+title+'" />' );
						} );
					});
					if($(window).width() <= 600){
						var pagingType = shortcode.pagination_simple;
					}else{
						var pagingType = shortcode.table_pagination;
					}
					var parameter = {
						"processing": true,
						"pagingType": JSON.parse(pagingType),
						"serverSide": JSON.parse(shortcode.serverSide),
						"stateSave": JSON.parse(shortcode.stateSave),
						"deferRender": true,
						"responsive": JSON.parse(shortcode.responsive),
						"scrollX": JSON.parse(shortcode.scrollX),
						"scroller": scroller,
						"scrollCollapse": true,
						"paging": JSON.parse(shortcode.paging),
						fixedColumns: {
							left: JSON.parse(shortcode.table_fixedleft),
							right: JSON.parse(shortcode.table_fixedright)
						},
						fixedHeader: {
							header: JSON.parse(shortcode.header),
							footer: JSON.parse(shortcode.footer),
						},
						"createdRow": function( row, data, dataIndex, cells ) {
							eval(shortcode.table_createdrow);
						},
						"keys": JSON.parse(shortcode.table_keytable),
						"select": JSON.parse(shortcode.select),
						"dom": shortcode.dom,
						"buttons": JSON.parse(buttons(shortcode)),
						"destroy": true,
						"ajax": ajax,
						initComplete: function () {
							if(shortcode.table_filter === 'yes'){
								buildSelect( dataRecord, shortcode );
								dataRecord.on( 'draw', function () {
									buildSelect( dataRecord, shortcode );
								} );
							}else{
								buildSelect( this.api(), shortcode );
							}
							if(shortcode.table_footer === 'yes' && JSON.parse(shortcode.column_search).length > 0){
								this.api().columns().every( function () {
									var that = this;
									$( 'input', this.footer() ).on( 'keyup change clear input', function () {
										if ( that.search() !== this.value.trim() ) {
											that.search( this.value.trim(), true, false ).draw();
										}
									} );
								} );
							}
						},
						"lengthMenu": JSON.parse(shortcode.pagelength),
						"pageLength": JSON.parse(shortcode.table_length),
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
						"columns": JSON.parse(shortcode.columns),
						"columnDefs": columnDefs(shortcode),
						"footerCallback": function ( row, data, start, end, display ) {
							var api = this.api();
							var intVal = function ( i ) {
								return typeof i === 'string' ?
									i.replace(/[\$,]/g, '')*1 :
									typeof i === 'number' ?
										i : 0;
							};
							JSON.parse(shortcode.column_total).forEach(function(item, index){
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
						"orderFixed": JSON.parse(shortcode.group),
						"rowGroup": JSON.parse([shortcode.rowGroup]),
						"order" : JSON.parse(shortcode.table_sortingtype),
					};
					if(JSON.parse(shortcode.scrollY) > 0){
						parameter["scrollY"] = JSON.parse(shortcode.scrollY);
					}
					if(shortcode.table_orderfixed === 'yes'){
						parameter["orderFixed"] = JSON.parse(shortcode.table_sortingtype);
					}
					if(shortcode.table_responsive === 'collapse' && shortcode.table_responsivetype === 'modal'){
						parameter["responsive"] = {
							details: {
								display: $.fn.dataTable.Responsive.display.modal( {
									header: function ( row ) {
										var data = row.data();
										return 'Details';
									}
								} ),
								renderer: $.fn.dataTable.Responsive.renderer.tableAll({tableClass: 'xstablemodal'})
							}
						};
					}else if(shortcode.table_responsive === 'stack'){
						parameter["responsive"] = {
							details: {
								display: $.fn.dataTable.Responsive.display.childRowImmediate,
								type: 'none',
								target: ''
							}
						};
					}
					if(JSON.parse(shortcode.serverSide) !== true && shortcode.dataSet !== ''){
						parameter["ajax"] = false;
						parameter["data"] = JSON.parse(shortcode.dataSet);
					}
					$.fn.dataTable.ext.errMode = 'none';
					var dataRecord = $('#'+shortcode.html_id).DataTable(parameter);
					if(JSON.parse(shortcode.serverSide) !== true && shortcode.dataSet !== ''){
						$("."+shortcode.html_id+" thead tr").addClass(shortcode.html_id+"_head");
						for(let i = 0; i <= shortcode.column_count; i++){
							$("."+shortcode.html_id+" thead th."+shortcode.html_id+"_column_"+i).addClass(shortcode.html_id+"_head_"+i).attr('id', shortcode.html_id+"_head_"+i);
						}
					}
					if(JSON.parse(shortcode.serverSide) === true){
						dataRecord.on('draw.dt', function () {
							var info = dataRecord.page.info();
							JSON.parse(shortcode.column_index).forEach(function(item, index){
								dataRecord.column(item, { search: 'applied', order: 'applied', page: 'applied' }).nodes().each(function (cell, i) {
									cell.innerHTML = i + 1 + info.start;
								});
							});
						});
					}else if($.inArray(shortcode.table_type, ['json', 'sheet']) !== -1){
						dataRecord.on('draw.dt', function () {
							var info = dataRecord.page.info();
							JSON.parse(shortcode.column_index).forEach(function(item, index){
								dataRecord.column(item, { search: 'applied', order: 'applied', page: 'current' }).nodes().each(function (cell, i) {
									cell.innerHTML = i + 1 + info.start;
								});
							});
						});
					}else{
						dataRecord.on('order.dt search.dt', function () {
							JSON.parse(shortcode.column_index).forEach(function(item, index){
								var i = 1;
								dataRecord.cells(null, item, { search: 'applied', order: 'applied' }).every(function (cell) {
									this.data(i++);
								});
							});
						});
					}
					return dataRecord;
				}
				var dataRecords = dataRecord(xs_ajax_shortcode);
				for(let i = 1; i <= 100; i++){
					$("#xscontainer_tab_"+i+" ul").on("click", function() {
						dataRecords.columns.adjust().draw();
					});
					$("#xscontainer_tabs_"+i+" .tab-link_"+i).on("click", function() {
						dataRecords.columns.adjust().draw();
					});
				}
			}
		});
		/*shortcodes*/
		$.each(shortcodes, function (indexs, values){  
			window['xs_ajax_shortcodes'] = values;
			if(typeof(xs_ajax_shortcodes) !== "undefined" && xs_ajax_shortcodes !== null) {
				function columnDefs(shortcodes){
					var column_priority = JSON.parse(shortcodes.column_priority);
					var columnDefs = '[';
					$.each(column_priority, function(key, value) {
						columnDefs += '{"responsivePriority":'+value+',"targets":'+key+'},';
					});
					columnDefs += '{"targets":'+shortcodes.column_filters+',"searchPanes": {"show": true}},';
					columnDefs += '{"targets": [0], "visible": false, "className": "noVis text-center"},';
					columnDefs += '{"targets":'+shortcodes.column_hidden+', "className": "noVis never"},';
					columnDefs += '{"targets":'+shortcodes.column_none+', "className": "none"},';
					columnDefs += '{"targets":'+shortcodes.column_left+',"className": "text-left"},';
					columnDefs += '{"targets":'+shortcodes.column_center+',"className": "text-center"},';
					columnDefs += '{"targets":'+shortcodes.column_right+',"className": "text-right"},';
					columnDefs += '{"targets":'+shortcodes.column_orderable+',"orderable": false},';
					columnDefs += '{"targets":'+shortcodes.column_searchable+',"searchable": false},';
					columnDefs += '{"targets":'+shortcodes.column_nowrap+',"className": "dt-nowrap"},';
					columnDefs += ']';
					var columnDefs = columnDefs.replace("},]", "}]");
					return columnDefs;
				}
				function dataRecord(shortcodes){
					var data = {action: "row_search_wpte", table_id:shortcodes.table_id, xs_type:shortcodes.table_type, _xsnonce:shortcodes.xsnonce};
					$('#'+shortcodes.html_id+'_search').click(function(){
						$.each(shortcodes.column_filter, function(key, value) {
							window[value] = $('#'+shortcodes.html_id+'_'+value).val();
							data[value] = window[value];
						});
						var anyFieldIsEmpty = $("#xs-row_"+shortcodes.html_id+" input, #xs-row_"+shortcodes.html_id+" select").filter(function() {
							return $.trim(this.value).length === 0;
						}).length > 0;
						if (anyFieldIsEmpty) {
							alert(xs_ajax_localize.required_field);
						}else{
							$('#'+shortcodes.html_id).show();
							$('#'+shortcodes.html_id).DataTable().destroy();
							$('#'+shortcodes.html_id).DataTable(parameter);
						}
					});
					var parameter = {
						"processing": true,
						"deferRender": true,
						"responsive": JSON.parse(shortcodes.responsive),
						"scrollX": JSON.parse(shortcodes.scrollX),
						fixedColumns: {
							left: JSON.parse(shortcodes.table_fixedleft),
							right: JSON.parse(shortcodes.table_fixedright)
						},
						"keys": JSON.parse(shortcodes.table_keytable),
						"select": JSON.parse(shortcodes.select),
						"dom": shortcodes.dom,
						"destroy": true,
						"ajax": {
							"url": shortcodes.ajax_url,
							"type": "POST",
							"data" : data,
						},
						"pageLength": JSON.parse(shortcodes.table_length),
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
							"sSearch":         xs_ajax_localize.sSearch,
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
						"columns": JSON.parse(shortcodes.columns),
						"columnDefs": JSON.parse(columnDefs(shortcodes)),
						"footerCallback": function ( row, data, start, end, display ) {
							var api = this.api();
							var intVal = function ( i ) {
								return typeof i === 'string' ?
									i.replace(/[\$,]/g, '')*1 :
									typeof i === 'number' ?
										i : 0;
							};
							JSON.parse(shortcodes.column_total).forEach(function(item, index){
								total = api
									.column( item )
									.data()
									.reduce( function (a, b) {
										return intVal(a) + intVal(b);
									}, 0 );
								pageTotal = api
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
						"orderFixed": JSON.parse(shortcodes.group),
						"rowGroup": JSON.parse([shortcodes.rowGroup]),
						"order" : [[1, 'asc']],
					};
					$.fn.dataTable.ext.errMode = 'none';
					var dataRecord = $('#'+shortcodes.html_id).DataTable(parameter);
					if(JSON.parse(shortcodes.serverSide) === true){
						dataRecord.on('draw.dt', function () {
							var info = dataRecord.page.info();
							JSON.parse(shortcodes.column_index).forEach(function(item, index){
								dataRecord.column(item, { search: 'applied', order: 'applied', page: 'applied' }).nodes().each(function (cell, i) {
									cell.innerHTML = i + 1 + info.start;
								});
							});
						});
					}else if($.inArray(shortcodes.table_type, ['json', 'sheet']) !== -1){
						dataRecord.on('draw.dt', function () {
							var info = dataRecord.page.info();
							JSON.parse(shortcodes.column_index).forEach(function(item, index){
								dataRecord.column(item, { search: 'applied', order: 'applied', page: 'current' }).nodes().each(function (cell, i) {
									cell.innerHTML = i + 1 + info.start;
								});
							});
						});
					}else{
						dataRecord.on('order.dt search.dt', function () {
							JSON.parse(shortcodes.column_index).forEach(function(item, index){
								var i = 1;
								dataRecord.cells(null, item, { search: 'applied', order: 'applied' }).every(function (cell) {
									this.data(i++);
								});
							});
						});
					}
					$('#'+shortcodes.html_id+'_wrapper').hide();
					$('#'+shortcodes.html_id+'_reset').click(function(){
						$('#'+shortcodes.html_id+'_wrapper').hide();
						dataRecord.clear().draw();
						$.each(shortcodes.column_filter, function(key, value) {
							$('#'+shortcodes.html_id+'_'+value).val('');
						});
					});
					return dataRecord;
				}
				var dataRecords = dataRecord(xs_ajax_shortcodes);
			}
		});
	});
})( jQuery );