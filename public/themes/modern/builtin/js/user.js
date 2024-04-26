/**
* Written by: Agus Prawoto Hadi
* Year		: 2022
* Website	: jagowebdev.com
*/

jQuery(document).ready(function () {
	
	if ($('#table-result').length) {
		column = $.parseJSON($('#dataTables-column').html());
		url = $('#dataTables-url').text();
		
		 var settings = {
			"processing": true,
			"serverSide": true,
			"scrollX": true,
			"ajax": {
				"url": url,
				"type": "POST",
				/* "dataSrc": function (json) {
					console.log(json)
				} */
			},
			"columns": column,
			"initComplete": function( settings, json ) {
				table.rows().every( function ( rowIdx, tableLoop, rowLoop ) {
					$row = $(this.node());
					/* this
						.child(
							$(
								'<tr>'+
									'<td>'+rowIdx+'.1</td>'+
									'<td>'+rowIdx+'.2</td>'+
									'<td>'+rowIdx+'.3</td>'+
									'<td>'+rowIdx+'.4</td>'+
								'</tr>'
							)
						)
						.show(); */
				} );
			 }
		}
		
		$add_setting = $('#dataTables-setting');
		if ($add_setting.length > 0) {
			add_setting = $.parseJSON($('#dataTables-setting').html());
			for (k in add_setting) {
				settings[k] = add_setting[k];
			}
		}
		
		table =  $('#table-result').DataTable( settings );
	}
	
	$('.select2').select2({
		theme: 'bootstrap-5'
	})
	
	$('.select-role').change(function() {

		list_role = $(this).val();
		list_option = '';
		$('.select-role').find('option').each(function(i, elm) 
		{
			$elm = $(elm)
			value = $elm.attr('value');
			label = $elm.html();
			if (list_role.includes(value)) {
				list_option += '<option value="' + value + '">' + label  + '</option>';
			}
		})
		current_value = $('.default-page-id-role').val();
		$select = $('.default-page-id-role').children('select');
		$select.empty();
		
		if (list_option) {
			
			$select.append(list_option);
			if (!current_value) {
				current_value = $select.find('option:eq(0)').val();
			} 
			$select.val(current_value);
		} else {
			$select.append('<option value="">-- Pilih Role --</option>');
		}
		
	})
	
	$('#option-default-page').change(function(){
		$this = $(this);
		$parent = $this.parent();
		$parent.find('.default-page').hide();
		if ($this.val() == 'url') {
			$parent.find('.default-page-url').show();
		} else if ($this.val() == 'id_module') {
			$parent.find('.default-page-id-module').show();
		} else {
			$parent.find('.default-page-id-role').show();
		}
	})
	
	$('#option-ubah-password').change(function() {
		if ($(this).val() == 'Y') {
			$('#password-container').show();
		} else {
			$('#password-container').hide();
		}
	});
});