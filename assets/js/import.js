/** @wptableeditor **/
( function( $ ) {
	'use strict';
	$(document).ready(function(){
		if(typeof(xs_ajax_import) != "undefined" && xs_ajax_import !== null) {
			$('#file').on("change", function(){
				if($("#file").val() === ''){
					$('#upload_file').attr('disabled', 'disabled');
				}else{
					$('#upload_file').attr('disabled', false);
				}
			})
			$('#select_table').on("change", function(){
				if($("#select_table").val() === ''){
					$('#upload_file').attr('disabled', 'disabled');
				}else{
					$('#upload_file').attr('disabled', false);
				}
			})
			$('#import_source').on("change", function(){
				var import_source = $("#import_source").val();
				if(import_source === 'file'){
					$('#select_table').hide().attr('disabled', true);
					$('#file').show().attr('disabled', false);
					$('#url').hide().attr({'disabled':true,'required':false});
				}else if(import_source === 'database'){
					$('#select_table').show().val('').attr('disabled', false);
					$('#file').hide().attr('disabled', true);
					$('#url').hide().attr({'disabled':true,'required':false});
				}else if(import_source === 'url'){
					$('#select_table').hide().val('').attr('disabled', true);
					$('#file').hide().attr('disabled', true);
					$('#url').show().attr({'disabled':false,'required':true});
					if($("#url").val() === ''){
						$('#upload_file').attr('disabled', true);
					}else{
						$('#upload_file').attr('disabled', false);
					}
					$("#url").on('keyup change clear input',function(){
						if($("#url").val() === ''){
							$('#upload_file').attr('disabled', true);
						}else{
							$('#upload_file').attr('disabled', false);
						}
					});
				}
			})
			$('#xs_import_form').on('submit', function(event){
				event.preventDefault();
				var import_type = $("#import_type").val();
				var column_number = $('#column_number').val();
				var form_data = new FormData(this);
				form_data.append("action", "upload_xs");
				$.ajax({
					url:xs_ajax_import.ajax_url,
					method:"POST",
					data:form_data,
					dataType:'json',
					contentType:false,
					cache:false,
					processData:false,
					beforeSend:function(){
						$('#message').hide();
						$('#upload_file').hide();
						$('#spin_upload_file').show();
					},
					success:function(data){
						if(data.error){
							$('#message').html('<div class="alert alert-danger">'+data.error+'</div>').show();
							$('#upload_file').show().attr('disabled', 'disabled');
						}else{
							$('#total_data').text(data.total_line);
							$('#hidden_field').text(data.available);
							$('#process_area').html(data.output).css('display', 'block');
							$("#column_number").attr({"max":data.column_number});
							$('#xs_import_form').css('display', 'none');
							const entries = Object.entries(column_data);
							for(const [key, value] of entries){
								delete column_data[key];
							}
							if(data.column_number < column_number){
								$('#message').html('<div class="alert alert-danger">'+xs_ajax_localize.import_danger+'</div>').show();
							}
						}
						$('#spin_upload_file').hide();
					},
					error:function(xhr){
						$('#message').html('<div class="alert alert-danger">'+xs_ajax_localize.import_error+'</div>').show();
						$('#file').val('').attr('disabled', false);
						$('#spin_upload_file').hide();
						$('#upload_file').show().attr('disabled', 'disabled');
					},
				});
			});
			var total_selection = 0;
			var column_data = [];
			if(import_type === 'append'){
				xs_ajax_import.column_names.forEach(function(item, index){
					window[item] = 0;
				});
			}else if(import_type === 'replace'){
				var column_numbers = $(this).data('column_numbers');
				var import_replace = [];
				for (var i = 0; i <= column_numbers; i++) {
					import_replace.push(i);
				}
				import_replace.forEach(function(item, index){
					window['column_'+item] = 0;
				});
			}
			$(document).on('change', '.set_column_data', function(){
				var column_name = $(this).val();
				if(column_name != ''){
					$('.set_column_data option[value='+column_name+']').hide();
				}
				var column_numbers = $(this).data('column_numbers');
				if(column_name in column_data){
					alert('You have already define '+column_name+ ' column');
					$(this).val('');
					return false;
				}
				if(column_name != ''){
					const entries = Object.entries(column_data);
					for(const [key, value] of entries){
						if(value === column_numbers){
							delete column_data[key];
							$('.set_column_data option[value='+key+']').show();
						}
					}
					column_data[column_name] = column_numbers;
				}else{
					const entries = Object.entries(column_data);
					for(const [key, value] of entries){
						if(value === column_numbers){
							delete column_data[key];
							$('.set_column_data option[value='+key+']').show();
						}
					}
				}
				total_selection = Object.keys(column_data).length;
				var column_number = $('#column_number').val();
				if(import_type === 'append'){
					var column_number = xs_ajax_import.column_number;
				}
				if(total_selection == column_number){
					var import_type = $("#import_type").val();
					$('#import_file').attr('disabled', false);
					if(import_type === 'append'){
						xs_ajax_import.column_names.forEach(function(item, index){
							window[item] = column_data[item];
						});
					}else if(import_type === 'replace'){
						var import_replace = [];
						for (var i = 0; i <= column_numbers; i++) {
							import_replace.push(i);
						}
						import_replace.forEach(function(item, index){
							window['column_'+item] = column_data['column_'+item];
						});
					}
				}else{
					$('#import_file').attr('disabled', 'disabled');
				}
			});
			$('#import_type').change(function(){
				var import_type = $('#import_type').val();
				$('#process_area').css('display', 'none');
				if(import_type === 'replace'){
					$('#column_number').attr('disabled', false).val(1);
					$('#import_source').attr('disabled', false);
					$('#file').attr('disabled', false);
					$('#select_table').attr('disabled', false);
				}else if(import_type === 'append'){
					$('#column_number').attr('disabled', true).val(xs_ajax_import.column_number);
					$('#import_source').attr('disabled', false);
					$('#file').attr('disabled', false);
					$('#select_table').attr('disabled', false);
				}else{
					$('#column_number').attr('disabled', true).val(1);
					$('#import_source').attr('disabled', 'disabled');
					$('#file').attr('disabled', 'disabled');
					$('#select_table').attr('disabled', 'disabled');
				}
			});
			$('#column_number').change(function(){
				var column_number = $('#column_number').val();
				if(total_selection == column_number){
					$('#import_file').attr('disabled', false);
				}else{
					$('#import_file').attr('disabled', true);
				}
			});
			var clear_timer;
			$(document).on('click', '#import_file', function(event){
				event.preventDefault();
				if(confirm(xs_ajax_localize.import_confirm)){
					$.ajax({
						url:xs_ajax_import.ajax_url,
						method:"POST",
						dataType:'json',
						data:{action:'confirm_xs', table_id:xs_ajax_import.table_id, _xsnonce:xs_ajax_import.xsnonce},
						beforeSend:function(){
							$('#file').attr('disabled', 'disabled');
							$('#upload_file').attr('disabled', 'disabled');
							$('#column_number').attr('disabled', 'disabled');
							$('#import_type').attr('disabled', 'disabled');
							$('#xs_import_form').css('display', 'block');
							$('#import_file').attr('disabled', 'disabled').text('Importing').hide();
							$('#process_area').css('display', 'none');
							$('#spin_upload_file').show();
						},
						success:function(data){
							if(data.success){
								start_import();
								clear_timer = setInterval(get_import_data, 1000);
							}else{
								$('#message').html('<div class="alert alert-danger">'+xs_ajax_localize.import_error+'</div>').show();
								$('#file').val('').attr('disabled', false);
								$('#column_number').val(1);
								$('#import_type').attr('disabled', false).val('');
							}
						}
					})
				}else{
					return false;
				}
			});
			function start_import(){
				var import_type = $('#import_type').val();
				var column_number = $('#column_number').val();
				var data = {action:'import_xs',import_type:import_type,column_number:column_number,table_id:xs_ajax_import.table_id,_xsnonce:xs_ajax_import.xsnonce};
				$('#process_xs').css('display', 'block');
				if(import_type === 'append'){
					xs_ajax_import.column_names.forEach(function(item, index){
						data[item] = column_data[item];
					});
				}else if(import_type === 'replace'){
					var import_replace = [];
					for (var i = 0; i <= 50; i++) {
						import_replace.push(i);
					}
					import_replace.forEach(function(item, index){
						data['column_'+item] = column_data['column_'+item];
					});
				}else{
					return;
				}
				$.ajax({
					url:xs_ajax_import.ajax_url,
					method:"POST",
					dataType:'json',
					data:data,
					success:function(){}
				})
			}
			function get_import_data(){
				var import_type = $('#import_type').val();
				var column_number = $('#column_number').val();
				$.ajax({
					url:xs_ajax_import.ajax_url,
					method:"POST",
					data:{action:'process_xs', table_id:xs_ajax_import.table_id, import_type:import_type, column_number:column_number, _xsnonce:xs_ajax_import.xsnonce},
					dataType:'JSON',
					success:function(data){
						var available = $('#hidden_field').text();
						$('#process_data').text(data - available);
						var total_data = $('#total_data').text();
						var width = Math.round(((data - available)/total_data)*100);
						$('.progress-bar').css('width', width + '%');
						if(width >= 100){
							clearInterval(clear_timer);
							$('#process_xs').css('display', 'none');
							$('#message').html('<div class="alert alert-success">'+xs_ajax_localize.import_success+'</div>').show();
							$('#file').val('').attr('disabled', false);
							$('#upload_file').show();
							$('#spin_upload_file').hide();
							$('#column_number').val(1);
							$('#import_type').attr('disabled', false).val('');
							$('.progress-bar').css('width', 0 + '%');
						}
					}
				})
			}
		}
	});
})( jQuery );