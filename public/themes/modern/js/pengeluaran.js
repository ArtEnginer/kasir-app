/**
* Written by: Agus Prawoto Hadi
* Year		: 2023-2023
* Website	: jagowebdev.com
*/

jQuery(document).ready(function () {
	
	let dataTables = '';
	if ($('#table-result').length > 0) {
		
		const column = $.parseJSON($('#dataTables-column').html());
		const url = $('#dataTables-url').text();
	
		const settings = {
			"processing": true,
			"serverSide": true,
			"scrollX": true,
			"ajax": {
				"url": url,
				"type": "POST"
			},
			"columns": column
		}
		
		let $add_setting = $('#dataTables-setting');
		if ($add_setting.length > 0) {
			add_setting = $.parseJSON($('#dataTables-setting').html());
			for (k in add_setting) {
				settings[k] = add_setting[k];
			}
		}
		
		dataTables =  $('#table-result').DataTable( settings );
	}
	
	// Pembayar
	$('body').delegate('.btn-add-rekanan', 'click', function (e) {
		e.preventDefault();
		$('.modal-backdrop').hide();
		$bootbox.css('z-index', '10');
					  
		$('.modal-backdrop').hide();
		$bootbox.css('z-index', '10');
		$bootbox_form =  bootbox.dialog({
			title: 'Tambah Rekanan',
			message: '<div class="text-center text-secondary"><div class="spinner-border"></div></div>',
			onEscape: function() {
				$('.modal-backdrop').show();
				$bootbox.css('z-index', '');
			},
			buttons: {
				cancel: {
					label: 'Cancel',
					callback: function() {
						$('.modal-backdrop').show();
						$bootbox.css('z-index', '');
					}
				},
				success: {
					label: 'Submit',
					className: 'btn-success submit',
					callback: function() 
					{
											
						$bootbox_form.find('.alert').remove();
						$spinner = $('<span class="spinner-border spinner-border-sm me-2"></span>');
						$button_form_submit.prepend($spinner);
						$button_form.prop('disabled', true);
						
						form = $bootbox_form.find('form')[0];
						$.ajax({
							type: 'POST',
							url: base_url + 'rekanan/ajaxSaveData',
							data: new FormData(form),
							processData: false,
							contentType: false,
							dataType: 'json',
							success: function (data) {
								
								$button_form.prop('disabled', false);
								$spinner.remove();
								if (data.status == 'ok') {
									const Toast = Swal.mixin({
										toast: true,
										position: 'top-end',
										showConfirmButton: false,
										timer: 2500,
										timerProgressBar: true,
										iconColor: 'white',
										customClass: {
											popup: 'bg-success text-light toast p-2'
										},
										didOpen: (toast) => {
											toast.addEventListener('mouseenter', Swal.stopTimer)
											toast.addEventListener('mouseleave', Swal.resumeTimer)
										}
									})
									Toast.fire({
										html: '<div class="toast-content"><i class="far fa-check-circle me-2"></i> Data berhasil disimpan</div>'
									})
									
									$('#id-rekanan').val(data.id_rekanan);
									$('#nama-rekanan').val($bootbox_form.find('[name="nama_rekanan"]').val());
									$('.del-rekanan').show();
									
									$bootbox_form.modal('hide');
									$('.modal-backdrop').show();
									$bootbox.css('z-index', '');
								} else {
									show_alert('Error !!!', data.message, 'error');
								}
							},
							error: function (xhr) {
								$button_form.prop('disabled', false);
								$spinner.remove();
								show_alert('Error !!!', xhr.responseText, 'error');
								console.log(xhr.responseText);
							}
						})
						return false;
					}
				}
			}
		});
		
		$bootbox_form.find('.modal-dialog').css('max-width', '550px');
		var $button_form = $bootbox_form.find('button').prop('disabled', true);
		var $button_form_submit = $bootbox_form.find('button.submit');
		
		$.get(base_url + 'rekanan/ajaxGetFormData', function(html){
			$button_form.prop('disabled', false);
			$bootbox_form.find('.modal-body').empty().append(html);
		});
	});
	
	$('body').delegate('.cari-rekanan', 'click', function(e) {
		
		e.preventDefault();
		$('.modal-backdrop').hide();
		$bootbox.css('z-index', '10');
		
		$bootbox_custom = bootbox.dialog({
			title: 'Pilih Pembayar',
			message: '<div class="text-center text-secondary"><div class="spinner-border"></div></div>',
			onEscape: true,
			backdrop: true,
			buttons: false
		});
		
		$bootbox_custom.find('.modal-dialog').css('max-width', '650px');
		$bootbox_custom.find('.modal-body').addClass('p-4');
		$.get(base_url + '/pengeluaran/getListRekanan', function(html){
			$bootbox_custom.find('.modal-body').empty().append(html);
			const column = $.parseJSON($('#jwdmodal-dataTables-column').html());
			const url = $('#jwdmodal-dataTables-url').text();
		
			const settings = {
				"processing": true,
				"serverSide": true,
				"scrollX": true,
				"ajax": {
					"url": url,
					"type": "POST"
				},
				"columns": column
			}
			
			let $add_setting = $('#jwdmodal-dataTables-setting');
			if ($add_setting.length > 0) {
				add_setting = $.parseJSON($('#jwdmodal-dataTables-setting').html());
				for (k in add_setting) {
					settings[k] = add_setting[k];
				}
			}
			
			dataTablesModal =  $('#jwdmodal-table-result').DataTable( settings );
		});		  
		
		$(document)
		.undelegate('.btn-pilih-rekanan', 'click')
		.delegate('.btn-pilih-rekanan', 'click', function() {
			$('.modal-backdrop').show();
			$bootbox.css('z-index', '');
			
			// Rekanan popup
			$this = $(this);
			$this.attr('disabled', 'disabled');
			rekanan = JSON.parse($(this).next().text())
			$('#id-rekanan').val(rekanan.id_rekanan);
			$('#nama-rekanan').val(rekanan.nama_rekanan);
			$('.del-rekanan').show();
			$bootbox_custom.modal('hide');
		});
	});
	
	$('body').delegate('.del-rekanan', 'click', function() {
		$this = $(this);
		$('#id-rekanan').val('');
		$('#nama-rekanan').val('');
		$this.hide();
	});
	
	$('body').delegate('#using-invoice', 'change', function () {
		if (this.value == 'Y') {
			$(this).parents('form').find('.row-invoice').show();
		} else {
			$(this).parents('form').find('.row-invoice').hide();
		}
	});
	
	$('body').delegate('.btn-edit', 'click', function(e) {
		e.preventDefault();
		showForm('edit', $(this).attr('data-id'))
	})
	
	$('body').delegate('.btn-add', 'click', function(e) {
		e.preventDefault();
		showForm();
	})
	
	function showForm(type='add', id = '') {
		$bootbox =  bootbox.dialog({
			title: 'Edit Data',
			message: '<div class="text-center text-secondary"><div class="spinner-border"></div></div>',
			buttons: {
				cancel: {
					label: 'Cancel'
				},
				success: {
					label: 'Submit',
					className: 'btn-success submit',
					callback: function() 
					{
						$bootbox.find('.alert').remove();
						error_message = '';							
												
						$form = $bootbox.find('form');
						if (!error_message && $form.find('#id-pembayar').val() == '') {
							error_message = 'Nama belum dipilih';
						}
						
						/* if (!error_message && $form.find('.tanggal-invoice').val() == '') {
							error_message = 'Tanggal invoice harus diisi';
						} */
						
						$form = $bootbox.find('form');
						if (!error_message && $form.find('.tanggal-bayar').val() == '') {
							error_message = 'Tanggal bayar harus diisi';
						}
						
						if (!error_message && $('#tabel-list-item-bayar').is(':hidden')) {
							error_message = 'Pembayaran belum dipilih';
						}
						
						if (!error_message) {
							$form.find('.item-nilai-bayar').each (function(i, elm) {
								if ($(elm).val() == '' || parseInt($(elm).val()) == 0) {
									error_message = 'Nilai bayar harus diisi';
								}
							});
						}
						if (error_message) {
							$('.modal-backdrop').hide();
							$bootbox.css('z-index', '10');
							bootbox.alert(error_message, function() {
								$bootbox.css('z-index', '');
								$('.modal-backdrop').show();
							})
							return false;
						}
						
						$spinner = $('<span class="spinner-border spinner-border-sm me-2"></span>');
						$button_submit.prepend($spinner);
						$button.prop('disabled', true);
						
						$.ajax({
							type: 'POST',
							url: base_url + 'pengeluaran/ajaxSaveData',
							data: new FormData($form[0]),
							processData: false,
							contentType: false,
							dataType: 'json',
							success: function (data) {
								
								$spinner.remove();
								$button.prop('disabled', false);
								if (data.status == 'ok') {
									$bootbox.modal('hide');
									const Toast = Swal.mixin({
										toast: true,
										position: 'top-end',
										showConfirmButton: false,
										timer: 2500,
										timerProgressBar: true,
										iconColor: 'white',
										customClass: {
											popup: 'bg-success text-light toast p-2'
										},
										didOpen: (toast) => {
											toast.addEventListener('mouseenter', Swal.stopTimer)
											toast.addEventListener('mouseleave', Swal.resumeTimer)
										}
									})
									Toast.fire({
										html: '<div class="toast-content"><i class="far fa-check-circle me-2"></i> Data berhasil disimpan</div>'
									})
									if (type == 'edit') {
										dataTables.draw(false);
									} else {
										dataTables.draw();
									}
									$('.btn-delete-all-data').prop('disabled', false);
								} else {
									show_alert('Error !!!', data.message, 'error');
								}
							},
							error: function (xhr) {
								$spinner.remove();
								$button.prop('disabled', false);
								show_alert('Error !!!', xhr.responseText, 'error');
								console.log(xhr.responseText);
							}
						})
						return false;
					}
				}
			}
		});
		$bootbox.find('.modal-dialog').css('max-width', '750px');
		var $button = $bootbox.find('button').prop('disabled', true);
		var $button_submit = $bootbox.find('button.submit');
		
		$.get(base_url + 'pengeluaran/ajaxGetFormData?id=' + id, function(html){
			$button.prop('disabled', false);
			$bootbox.find('.modal-body').empty().append(html);
			$('.flatpickr').flatpickr({
				dateFormat: "d-m-Y",
			});
			
			///USAGE
			// $("#list-kategori").select2tree({theme: 'bootstrap-5'});
			// $(".list-kategori").select2tree({theme: 'bootstrap-5'});
			$(".list-kategori").select2tree({theme: 'bootstrap-5', dropdownParent: $(".bootbox")});
			
			/* $("#list-kategori").on("select2:open", function(e) {
				console.log("select2:open", e);
			});
			$("#list-kategori").on("select2:close", function(e) {
				console.log("select2:close", e);
			});
			$("#list-kategori").on("select2:select", function(e) {
				console.log("select2:select", e);
			});
			$("#list-kategori").on("select2:unselect", function(e) {
				console.log("select2:unselect", e);
			}); */
		});
	};
	
	$('body').delegate('.item-nilai-bayar', 'keyup', function() 
	{
		calculate_total();
	});
	function calculate_total() 
	{
		$input_harga = $('.item-nilai-bayar');

		total = 0;
		$input_harga.each(function(i, elm) 
		{
			value = $(elm).val();
			total += setInt( value );
		});
		$('#total-item-nilai-bayar').text(format_ribuan(total));
		// $('#total-pembayaran').trigger('keyup');	
		// $('#total-pembayaran').val(format_ribuan(total));
	}
	
	$('body').delegate('#total-pembayaran', 'keyup', function() {
		$total_item = $('#total-item-nilai-bayar');
		total_item = parseInt($total_item.text().replaceAll('.',''));
		total_pembayaran = parseInt(this.value.replaceAll('.',''));
		console.log(total_item);
		console.log(total_pembayaran);
		if (total_pembayaran > total_item ) {
			kembali = total_pembayaran - total_item;
		} else {
			kembali = 0;
		}
		
		$('#kembali').text(format_ribuan(kembali));
	});
	
	
	$('body').delegate('.jenis-item-pembayaran', 'change', function() {
		$this = $(this);
		$select = $this.parents('tr:eq(0)').find('.spp-bulan').find('select');
		if ($(this).val() == 1) {
			// $select.prop('disabled', false);
			$select.show();
		} else {
			// $select.prop('disabled', true);
			$select.hide();
		}
	})
	
	$('body').delegate('.add-row', 'click', function() {
		$this = $(this);
		$tbody = $this.parents('tbody');
		$new_row = $this.parents('tr').eq(0).clone();
		$new_row.find('input, textarea').val('');
		$new_row.find('button').removeClass('btn-outline-success');
		$new_row.find('button').addClass('btn-outline-danger');
		$new_row.find('button').removeClass('add-row');
		$new_row.find('button').addClass('del-row');
		$new_row.find('i').removeClass('fa-plus');
		$new_row.find('i').addClass('fa-times');
		$new_row.find('td').eq(0).text($tbody.find('tr').length + 1);
		$tbody.append($new_row);
	});
	
	$('body').delegate('.del-row', 'click', function() {
		$this = $(this);

		$this.parents('tr:eq(0)').remove();
		$tr = $('#tabel-list-item-pengeluaran').find('tbody').find('tr');
		$tr.each(function(i, elm) {
			$(elm).find('td').eq(0).text(i + 1);
		})
		calculate_total();
	})
	
	$('body').delegate('.add-file', 'click', function() {
		var $this = $(this);
		$bootbox.hide();
		$('.modal-backdrop').css('z-index', '1');
		jwdfilepicker.init({
			title : 'File Bukti',
			filter_file : '',
			onSelect: function ($elm) 
			{
				$bootbox.show();
				$('.modal-backdrop').css('z-index', '');
				meta_file = JSON.parse($elm.find('.meta-file').html());
				if ($('#id-file-picker-' + meta_file.id_file_picker).length) {
					return;
				}
				
				$this.find('.text').hide();
				$this.find('img').remove();
			
				// console.log(meta_file);
				
				var $ul = $('.list-image-container');
				var $li_first = $ul.find('li').eq(0);
				var $li = $li_first.clone().hide();
					$li.removeAttr('data-initial-item');
				
				if ($li_first.attr('data-initial-item') == 'true') {
					$li_first.remove();
				}
				
				$li.attr('id', 'pengeluaran-' + meta_file.id_file_picker);
				$li.attr('data-id-file', meta_file.id_file_picker);
				$li.find('[name="id_file_picker[]"]').val(meta_file.id_file_picker);
				
				$new_img = $elm.find('img');
				$li.find('img').replaceWith($new_img);

				$ul.prepend($li);
				$li.fadeIn('fast');
			},
			onClose: function() {
				$bootbox.show();
				$('.modal-backdrop').css('z-index', '');
			}
		});
	});
	
	$('body').delegate('.thumbnail-item', 'click', function() {

	    id_image = $(this).attr('data-id-file');
	    $bootbox.hide();
	    $('.modal-backdrop').css('z-index', '1');
		 jwdfilepicker.init({
			title : 'Edit Image',
			id_file : id_image,
			onSelect: function ($elm) 
			{
				$this.find('.text').hide();
				$this.find('img').remove();
				
				$clone = $ul.find('li').eq(0).clone();
				$clone.find('img').replaceWith($elm.find('img'));
				$ul.append($clone);
			},
			onClose: function() {
				$bootbox.show();
				$('.modal-backdrop').css('z-index', '');
			}
		});
	 });
	
	$('body').delegate('.delete-image', 'click', function(e) {
		e.stopPropagation();
		$this = $(this);
		$li = $this.parents('.thumbnail-item').eq(0);
		$li.find('input').val('');
		$li.fadeOut('fast', function(){
			if ($(this).parent().children().length == 1) {
				$(this).attr('data-initial-item', 'true');
				$this.parents('.thumbnail-item').eq(0).find('input').val('');
				// $('.gallery-container').prepend(show_message('error', 'Gambar belum dipilih'));
			} else {
				$(this).remove();
			}
		});		
	})
	
	$('body').delegate('.btn-del-bukti', 'click', function() {
		$(this).parents('.input-group').eq(0).remove();
	})
		
	$('.btn-delete-all-data').click(function() {
		$this = $(this);
		$bootbox =  bootbox.dialog({
			title: 'Hapus Semua Data',
			message: '<div class="px-2">' +
						'<p>Tindakan ini akan menghapus semua data pada database tabel</p><ul class="list-circle"><li>pengeluaran</li><li>pengeluaran_detail</li><li>pengeluaran_file_picker</li></ul>' +
				'</div>'+
			'</form>',
			buttons: {
				cancel: {
					label: 'Cancel'
				},
				success: {
					label: 'Hapus',
					className: 'btn-danger submit',
					callback: function() 
					{
						var $button = $bootbox.find('button').prop('disabled', true);
						var $button_submit = $bootbox.find('button.submit');
						
						$bootbox.find('.alert').remove();
						$spinner = $('<div class="spinner-border spinner-border-sm me-2"></div>');
						$button_submit.prepend($spinner);
						$button.prop('disabled', true);
						
						$.ajax({
							type: 'GET',
							url: base_url + 'pengeluaran/ajaxDeleteAllData',
							dataType: 'text',
							success: function (data) {
								data = $.parseJSON(data);
								console.log(data);
								$spinner.remove();
								$button.prop('disabled', false);
								
								if (data.status == 'ok') 
								{
									$bootbox.modal('hide');
									const Toast = Swal.mixin({
										toast: true,
										position: 'top-end',
										showConfirmButton: false,
										timer: 2500,
										timerProgressBar: true,
										iconColor: 'white',
										customClass: {
											popup: 'bg-success text-light toast p-2'
										},
										didOpen: (toast) => {
											toast.addEventListener('mouseenter', Swal.stopTimer)
											toast.addEventListener('mouseleave', Swal.resumeTimer)
										}
									})
									Toast.fire({
										html: '<div class="toast-content"><i class="far fa-check-circle me-2"></i> Data berhasil dihapus</div>'
									})
									
									dataTables.draw();
									$('#btn-export-container').find('button').prop('disabled', true);
									$this.prop('disabled', true);
								} else {
									Swal.fire({
										title: 'Error !!!',
										html: data.message,
										icon: 'error',
										showCloseButton: true,
										confirmButtonText: 'OK'
									})
								}
							},
							error: function (xhr) {
								console.log(xhr.responseText);
								$spinner.remove();
								$button.prop('disabled', false);
								Swal.fire({
									title: 'Error !!!',
									html: xhr.responseText,
									icon: 'error',
									showCloseButton: true,
									confirmButtonText: 'OK'
								})
							}
						})
						return false;
					}
				}
			}
		});
	});
	
	$('body').delegate('.number', 'keyup', function() {
		this.value = format_ribuan(this.value);
	})
		
	$('body').delegate('.btn-delete', 'click', function(e) {
		e.preventDefault();
		id = $(this).attr('data-id');
		
		$bootbox = bootbox.confirm({
			message: $(this).attr('data-delete-title'),
			buttons: {
				confirm: {
					label: 'Delete',
					className: 'btn-danger'
				},
				cancel: {
					label: 'Cancel',
					className: 'btn-secondary'
				}
			},
			callback: function(confirmed) {
				if (confirmed) {
					$button = $bootbox.find('button');
					$button.attr('disabled', 'disabled');
					$spinner = $('<div class="spinner-border spinner-border-sm me-2"></div>');
					$spinner.prependTo($bootbox.find('.bootbox-accept'));
					$.ajax({
						type: 'POST',
						url: base_url + 'pengeluaran/ajaxDeleteData',
						data: 'id=' + id,
						dataType: 'json',
						success: function (data) {
							$bootbox.modal('hide');
							$spinner.remove();
							$button.removeAttr('disabled');
							if (data.status == 'ok') {
								const Toast = Swal.mixin({
									toast: true,
									position: 'top-end',
									showConfirmButton: false,
									timer: 2500,
									timerProgressBar: true,
									iconColor: 'white',
									customClass: {
										popup: 'bg-success text-light toast p-2'
									},
									didOpen: (toast) => {
										toast.addEventListener('mouseenter', Swal.stopTimer)
										toast.addEventListener('mouseleave', Swal.resumeTimer)
									}
								})
								Toast.fire({
									html: '<div class="toast-content"><i class="far fa-check-circle me-2"></i> Data berhasil dihapus</div>'
								})
								dataTables.draw();
								// $('#option-kelas').trigger('change');
							} else {
								show_alert('Error !!!', data.message, 'error');
							}
						},
						error: function (xhr) {
							$spinner.remove();
							$button.removeAttr('disabled');
							show_alert('Error !!!', xhr.responseText, 'error');
							console.log(xhr.responseText);
						}
					})
					return false;
				}
			},
			centerVertical: true
		});
	})
	
	
	$('#btn-excel').click(function() {
		$this = $(this);
		$this.prop('disabled', true);
		$spinner = $('<div class="spinner-border spinner-border-sm me-2"></div>');
		$spinner.prependTo($this);
		
		filename = 'Daftar Pembayar - ' + format_date('dd-mm-yyyy') + '.xlsx';
		export_url = base_url + 'riwayat-status-pembayar/ajaxExportExcel';
		fetch(export_url)
		  .then(resp => resp.blob())
		  .then(blob => {
				$this.prop('disabled', false);
				$spinner.remove();
				saveAs(blob, filename);
		  })
		.catch((xhr) => {
			$this.prop('disabled', false);
			$spinner.remove();
			console.log(xhr);
			alert('Ajax Error')
			
		});
	})
	
	// Invoice - PDF
	$('body').delegate('.save-pdf', 'click', function(e){
		e.preventDefault();
		$this = $(this);
		url = $this.attr('href');
		filename = $this.attr('data-filename').replace('/','_').replace('\\', '_')
		
		$swal =  Swal.fire({
			title: 'Memproses Invoice',
			text: 'Mohon sabar menunggu...',
			showConfirmButton: false,
			allowOutsideClick: false,
			didOpen: function () {
			  	Swal.showLoading();
			},
			didClose () {
				Swal.hideLoading()
			},
		});
		
		fetch(url)
		  .then(resp => resp.blob())
		  .then(blob => {
				saveAs(blob, filename + '.pdf');
				$swal.close();
		  })
		.catch(() => alert('Ajax Error'));

	})
	
	$('table').delegate('.btn-print-invoice', 'click', function(e) {
		e.preventDefault();
		const url = $(this).attr('data-url');
		window.open(url, top = 500, left = 500, width = 600, height = 600, menubar = 'no', status = 'no', titlebar = 'no'); 
		return false;
	});
	
	$('#daterange').daterangepicker({
		opens: 'right',
		ranges: {
             'Hari ini': [moment(), moment()],
			 'Bulan ini': [moment().startOf('month'), moment()],
             'Tahun ini': [moment().startOf('year'), moment()],
             '7 Hari Terakhir': [moment().subtract('days', 6), moment()],
             '30 Hari Terakhir': [moment().subtract('days', 29), moment()],
             
          },
		showDropdowns: true,
		   "linkedCalendars": false,
		locale: {
			customRangeLabel: 'Pilih Tanggal',
            format: 'DD-MM-YYYY',
			applyLabel: 'Pilih',
			separator: " s.d. ",
				 "monthNames": [
				"Januari",
				"Februari",
				"Maret",
				"April",
				"Mei",
				"Juni",
				"Juli",
				"Agustus",
				"September",
				"Oktober",
				"November",
				"Desember"
			],
        }
	},	function(startDate, endDate, label) 
	{
		start = startDate.format('DD-MM-YYYY');
		end = endDate.format('DD-MM-YYYY');
	
		current_url = document.location.href;
		new_url = new URL(current_url);
		new_url.searchParams.set('daterange', start + ' s.d. ' + end);
		history.pushState({}, null, new_url);

		new_dt_url = base_url + 'pengeluaran/getDataDT?' + new_url.searchParams.toString();
		dataTables.ajax.url( new_dt_url ).load();		
	})
	
	$('#btn-excel-pengeluaran').click(function() {
		$this = $(this);
		$this.prop('disabled', true);
		$spinner = $('<div class="spinner-border spinner-border-sm me-2"></div>');
		$spinner.prependTo($this);
		
		url = base_url + 'pengeluaran/ajaxExportExcel';
		current_url = document.location.href;
		new_url = new URL(current_url);
		query_string = new_url.searchParams.toString();
		if (query_string) {
			url = url + '?' + query_string;
		}
		
		if (query_string) {
			daterange = new_url.searchParams.get('daterange');
			if (!daterange) {
				daterange = $('#daterange').val();
			}
		} else {
			daterange = $('#daterange').val();
		}
		
		filename = 'Rincian Pengeluaran - '  + daterange + '.xlsx';
		export_url = url;
		fetch(export_url)
		  .then(resp => resp.blob())
		  .then(blob => {
				$this.prop('disabled', false);
				$spinner.remove();
				saveAs(blob, filename);
		  })
		.catch((xhr) => {
			$this.prop('disabled', false);
			$spinner.remove();
			console.log(xhr);
			alert('Ajax Error')
			
		});
	})
});