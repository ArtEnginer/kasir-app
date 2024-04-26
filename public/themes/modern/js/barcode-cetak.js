/**
* Written by: Agus Prawoto Hadi
* Year		: 2021
* Website	: jagowebdev.com
*/

jQuery(document).ready(function () {
	
	$('.barcode').keypress(function(e) {
		if (e.which == 13) {
			return false;
		}
	})
	const button = $('#print,#export-pdf,#export-word');
	let $container = $('#barcode-print-container');
	$('.barcode').keyup(function(e) {
		
		$this = $(this);
		value = $this.val().replace(/\D/g,'');
		this.value = value.substr(0,13);
		// console.log(value.length);
		if (value.length >= 13) 
		{
			value = value.substr(0,13);
			$spinner = $('<div class="spinner-border text-secondary spinner" style="height: 18px; width:18px; position:absolute; right:15px; top:7px" role="status"><span class="visually-hidden">Loading...</span></div>');
			$parent = $this.parent();
			$parent.find('.spinner').remove();
			$spinner.appendTo($parent);
			$this.attr('disabled', 'disabled');
			$('.add-barang').attr('disabled', 'disabled').addClass('disabled');
			$.ajax({
				url : base_url + 'pembelian/ajaxGetBarangByBarcode?code=' + value
				, success : function (data) {
					console.log(data);
					
					$parent.find('.spinner').remove();
					$this.removeAttr('disabled');
					$('.add-barang').removeAttr('disabled').removeClass('disabled');
					
					data = JSON.parse(data);
					if (data.status == 'ok') {
						addBarang(data.data);
						
					} else {
						const Toast = Swal.mixin({
							toast: true,
							position: 'bottom-end',
							showConfirmButton: false,
							timer: 2500,
							timerProgressBar: true,
							iconColor: 'white',
							customClass: {
								popup: 'bg-danger text-light toast p-2 mb-3'
							},
							didOpen: (toast) => {
								toast.addEventListener('mouseenter', Swal.stopTimer)
								toast.addEventListener('mouseleave', Swal.resumeTimer)
							}
						})
						Toast.fire({
							html: '<div class="toast-content"><i class="far fa-check-circle me-2"></i> Data tidak ditemukan</div>'
						})
					}
				}, error: function() {
					
				}
			})
		}
		
	})
	
	$('table').delegate('.jml-cetak', 'keyup', function() {
		
		jml_cetak = 0;
		$('table').find('.jml-cetak').each(function(i, el) {
			jml_cetak += setInt($(el).val());
		});

		if (jml_cetak == 0) {
			$('#preview').attr('disabled', 'disabled');
		} else {
			$('#preview').removeAttr('disabled');
		}
		this.value = format_ribuan(this.value);
	});
	
	// console.log (format_ribuan(10000));
	$('.add-barang').click(function() {
		$this = $(this);
		if ($this.hasClass('disabled')) {
			return false;
		}
		var $modal = jwdmodal({
			title: 'Pilih Barang',
			url: base_url + '/barcode-cetak/getDataDTListBarang',
			width: '650px',
			action :function () 
			{
				$table = $('#list-barang');
				$trs = $table.find('tbody').eq(0).find('tr');
				var list_barang = '<span class="belum-ada mb-2">Silakan pilih barang</span>';
				if ($table.is(':visible')) {
					var list_barang = '';
					$trs.each (function (i, elm) {
						$td = $(elm).find('td');
						list_barang += '<small  class="px-3 py-2 me-2 mb-2 text-success bg-success bg-opacity-10 border border-success rounded-2">' + $td.eq(1).html() + '</small>';
					});
				}
				$('.jwd-modal-header-panel').prepend('<div class="list-barang-terpilih">' + list_barang + '</div>');
			}
			
		});
		
		$(document)
		.undelegate('.pilih-barang', 'click')
		.delegate('.pilih-barang', 'click', function() {
			
			$('#using-list-barang').val(1);
			$table = $('#list-barang');

			// Barang Popup
			$tr = $(this).parents('tr').eq(0);
			$td = $tr.find('td');
			barang = JSON.parse($tr.find('.detail-barang').text());
			
			// List barang
			$tbody = $table.find('tbody').eq(0);
			
			$trs = $tbody.find('tr');
			$tr = $trs.eq(0).clone();
			num = $trs.length;
			if ($table.is(':hidden')) {
				$trs.remove();
				num = 0;
			}

			$td = $tr.find('td');
			$td.eq(0).html(num + 1);
			$td.eq(1).html(barang.nama_barang);
			$td.eq(2).html(barang.barcode);
			$tr.find('.jml-cetak').val(10);
						
			$table.show();
			$tbody.append($tr);
						
			$('.list-barang-terpilih').find('.belum-ada').remove();
			$('.list-barang-terpilih').append('<small  class="px-3 py-2 me-2 mb-2 text-success bg-success bg-opacity-10 border border-success rounded-2">' + barang.nama_barang + '</small>');
			
			$('#preview').removeAttr('disabled');
			
			generateBarcode();
			
			// $(document);
		});
	});
		
	$('table').delegate('.del-row', 'click', function() 
	{
		$this = $(this);
		$table = $this.parents('table');
		$tbody = $table.find('tbody').eq(0);
		$trs = $tbody.find('tr');
		id = $table.attr('id');

		if ($trs.length == 1) {
			$trs.find('input').val('');
			$tbody.parent().hide();
			if (id == 'list-pembayaran') {
				$('#using-pembayaran').val(0);
			} else if (id == 'list-barang') {
				$('#using-list-barang').val(0);
			}
		} else {
			$this.parents('tr').eq(0).remove();
			$new_trs = $tbody.find('tr');
			$new_trs.each(function(i, elm) {
				$(elm).find('td').eq(0).html(i + 1);
			});
		}
		
		if (id == 'list-pembayaran') {
			$tbody.find('.item-bayar').eq(0).trigger('keyup');
		} else if (id == 'list-barang') {
			$tbody.find('.harga-satuan').eq(0).trigger('keyup');
		}
		
		generateBarcode();
	});
	
	$('#paper-size-width, #paper-size-height').keyup(function() {
		this.value = setInt(this.value);
		if (this.value > 300) {
			this.value = 300;
		}
		
		w = parseInt($('#paper-size-width').val()) * pixel;
		h = parseInt($('#paper-size-height').val()) * pixel;
		$container.css('width', w);
		
		$container = $('#barcode-print-container');
		if ($container.find('canvas').eq(0).length) {
			$container.css({minHeight: h});
		}
	})
	
	$('#paper-size-width, #paper-size-height').blur(function() {
		if (this.value < 100) {
			this.value = 100;
		}
	})
	
	$('#paper-size').change(function() {
		
		let w = 0;
		let h = 0;
		const $paper_width = $('#paper-size-width').attr('disabled', 'disabled');
		const $paper_height = $('#paper-size-height').attr('disabled', 'disabled');
		
		
		if (this.value == 'a4') {
			w = 210;
			h = 297;
		} else if (this.value == 'f4') {
			w = 215;
			h = 330;
		} else {
			w = 210;
			h = 297;
			$paper_width.removeAttr('disabled');
			$paper_height.removeAttr('disabled');
			
		}
		
		paper_width = $paper_width.val(w);
		paper_height = $paper_height.val(h);
		
		w = w * pixel;
		h = h * pixel;
		$container.css('width', w);
		
		$container = $('#barcode-print-container');
		if ($container.find('canvas').eq(0).length) {
			$container.css({minHeight: h});
		}
		// $container.css('width', w);
		// generateBarcode();
	})
	
	$('#display-value').change(function() {
		generateBarcode();
	})
	
	$('#barcode-height').on('input', function() {
		generateBarcode();
	})
	
	$('#barcode-width, #barcode-item-margin-left-first-column, #barcode-item-margin-left, #barcode-item-margin-top').on('input', function() {
		generateBarcode();
	})
	
	$('table').delegate('.jml-cetak', 'keyup', function() {
		generateBarcode();
	})
	
	function setEmptyBarcode() {
		$container = $('#barcode-print-container').empty();
		$container.css('height', 'auto');
		$container.css('text-align', 'center');
		$container.html('PREVIEW');
		
		button.attr('disabled', 'disabled');
	}
	
	const pixel = 3.7795275591; // 1 mm => pixel
	const milimeter = 0.2645833333; //1 px  => mm
	function generateBarcode() 
	{
		if ($('#list-barang').is(':hidden')) {
			setEmptyBarcode();
			return false;
		} else {
			
			let jml_cetak = 0;
			$barcode_barang = $('.barcode-barang');
			$barcode_barang.each(function(i, elm) {
				$elm = $(elm);
				$tr = $elm.parents('tr').eq(0);
				jml_cetak += setInt( $tr.find('.jml-cetak').val() );
			});
			
			if (jml_cetak == 0) {
				setEmptyBarcode();
				return false;
			}
		}
		
		button.removeAttr('disabled');
		
		container_width = parseInt($('#barcode-print-container').width());
		
		// ukuran_kertas = $('#paper-size').val();
		/* let w = 0;
		let h = 0;
		if (ukuran_kertas == 'a4') {
			w = 210;
			h = 297;
		} else if (ukuran_kertas == 'f4') {
			w = 215;
			h = 330;
		}
		
		w = w * pixel;
		h = h * pixel; */
		
		h = $('#paper-size-height').val() * pixel;
		$container.empty();
		// $container.css('width', w);
		$container.css({minHeight: h});
		$container.css('text-align', 'left');
		
		$('.barcode-barang').each(function(i, elm) 
		{
			$elm = $(elm);
			$tr = $elm.parents('tr').eq(0);
			jml_cetak = setInt( $tr.find('.jml-cetak').val() );
			nama_barang = $tr.find('.nama-barang').text();
			for ( let i = 1; i <= jml_cetak; i++) 
			{
				// barcode_margin_top = $('#barcode-item-margin-top').val();
				// barcode_margin_left = parseInt($('#barcode-item-margin-left').val());
				barcode_margin_top = barcode_margin_left = 0;
				$canvas_container = $('<div style="float:left;margin-top:' + barcode_margin_top + 'px;margin-left:' + barcode_margin_left + 'px"><div style="margin-top:-5px;text-align:center; padding: 0 10px;width:250px" class="nama-barang">' + nama_barang + '</div></div>');
				id = 'barcode-' + i + '-' + $elm.text();
				$canvas = $('<canvas/>');
				$canvas.attr({'id': id});
				$canvas.css('padding-right', 0);
				$canvas.appendTo($canvas_container);
				$canvas_container.appendTo($container);
		
				JsBarcode('#' + id, $elm.text(), {
					format: "ean13",
					width: $('#barcode-width').val(),
					height: $('#barcode-height').val(),
					displayValue: $('#display-value').val() == 'Y' ? true : false
				});
				canvas_width = $canvas_container.find('canvas').width();
				$canvas_container.find('.nama-barang').width(canvas_width);
				
				
				/* canvas_container_width = parseInt($canvas_container.width());
			
				first_margin = parseInt($('#barcode-item-margin-left-first-column').val());
				first_column_width = first_margin + canvas_container_width;
								
				next_column_width = barcode_margin_left + canvas_container_width;
				sisa = container_width - first_column_width;
				
				jml_barcode = 1 + Math.floor(sisa / next_column_width);
				// jml_barcode = Math.floor( parseInt(container_width) / parseInt(canvas_container_width)) + 1;
				barcode_margin_left_first_column = $('#barcode-item-margin-left-first-column').val();
				if ( jml_barcode == 1) {
					$canvas_container.css('margin-left', barcode_margin_left_first_column + 'px');
				} else {
					if (i % jml_barcode == 1) {
						$canvas_container.css('margin-left', barcode_margin_left_first_column + 'px');
						// console.log(jml_barcode);
					}
				} */
				
			}
		})
		
		$container.show();
	}
	
	// console.log(Math.floor(2.6));
	
	$('#export-pdf').click(function() 
	{
		mm_to_point = 2.83465;
		margin_left = 10 * mm_to_point; // mm
		margin_top = 10 * mm_to_point;
		
		paper_width = setInt($('#paper-size-width').val()) * mm_to_point;
		paper_height = setInt($('#paper-size-height').val()) * mm_to_point;
		
		const page_orientation = paper_width > paper_height ? 'lanscape' : 'portrait';
		
		table_body = [];
		content = [];
		$container = $('#barcode-print-container');
		
		barcode_width = parseInt($container.find('canvas').eq(0).width()) * 0.75; // pt
		column = Math.floor( (paper_width - (2 * margin_left)) / barcode_width );
		
		console.log(parseInt($container.find('canvas').eq(0).width()));
		$container.find('canvas').each(function(i, elm) 
		{
			const $elm = $(elm);
			const image_string = $(elm)[0].toDataURL();
			nama_barang = $elm.prev().text();
			width = parseFloat($elm.width());
			height = parseFloat($elm.height());
			
			i = i+1;

			if (i % column == 0) {
				content.push({alignment: "center", borderColor: ['#CCCCCC', '#CCCCCC', '#CCCCCC', '#CCCCCC'], stack:[nama_barang, {image:image_string, width: barcode_width}]});
				table_body.push(content);
				content = [];
			} else {
				// content.push(nama_barang);
				content.push({alignment: "center", borderColor: ['#CCCCCC', '#CCCCCC', '#CCCCCC', '#CCCCCC'], stack:[nama_barang, {image:image_string, width:barcode_width}]});
			}
			
			/* if (i < 2) {
				content.push(nama_barang);
			} else {
				if (i == 2 || (i > 2 && i % 5 == 0)) {
					content.push(nama_barang);
					table_body.push(content);
					content = [];
				} else {
					console.log('else-' + i);
					content.push(nama_barang);
				}
			} */
		});
		
		var docDefinition = {
			
			pageSize: { width: paper_width, height: paper_height },
			pageMargins: [ margin_left, margin_top ],
			pageOrientation: page_orientation,
			content: [
				{
					style: 'tableExample',
					table: {
						
						body: table_body,
						alignment: "center"
					}
				}
			]
		
		}

		pdfMake.createPdf(docDefinition).download();
		
		

		/* paper_width = setInt($('#paper-size-width').val());
		paper_height = setInt($('#paper-size-height').val());
		
		const orientation = paper_width > paper_height ? 'lanscape' : 'portrait';
		
		window.jsPDF = window.jspdf.jsPDF;
		const pdf = new jsPDF({
		  orientation: orientation,
		  unit: "mm",
		  format: [paper_height, paper_width]
		});
		
		margin_left = 5;
		margin_top = 5;

		row_width = 0;
		index_col = 0;
		index_row = 0;
		
		barcode_margin_right = 5;
		barcode_margin_bottom = 5;
		
		x = 0;
		y = 10;
		
		$container = $('#barcode-print-container');
		$container.find('canvas').each(function(i, elm) 
		{
			const $elm = $(elm);
			const image_string = $(elm)[0].toDataURL();
			nama_barang = $elm.prev().text();
			width = parseFloat($elm.width()) * milimeter;
			height = parseFloat($elm.height()) * milimeter;
			
			x = margin_left + (index_col * width) + ( index_col * barcode_margin_right );
			row_width = x + width + barcode_margin_right;

			if (row_width > paper_width) {
				index_col = 0;
				row_width = 0;
				index_row++;
				x = margin_left + (index_col * width) + ( index_col * barcode_margin_right );
			} 
			
			index_col++;
			y = margin_top + (index_row * height) + ( index_row * barcode_margin_bottom );			
			
			if (y + height + barcode_margin_bottom > paper_height) {

				pdf.addPage([paper_height, paper_width], orientation);
				index_col = 1;
				row_width = 0;
				index_row = 0;
				y = margin_top + (index_row * height) + ( index_row * barcode_margin_bottom );
			}
			pdf.setFontSize(12);
			pdf.setFont('Arial');
// pdf.text(nama_barang, x, y, {maxWidth: 10});
 splitText = pdf.splitTextToSize('hey I am a veeeeeeeeeeery long string', 10)
text_w = pdf.text('hey I am a veeeeeeeeeeery long string', 10, 10, { align: 'center', maxWidth:20})
height = pdf.getLineHeight();
console.log(height);

console.log(text_w.length);
// pdf.text('2222 hey I am a veeeeeeeeeeery long string', 10, 10, { align: 'center', maxWidth:20})
			// pdf.addImage(image_string, 'PNG', x, y, width, height);
			
		});
		pdf.save("Barcode-cetak.pdf"); */
	})
	
	$('#print').click(function()
	{
		margin_left = 10; //mm
		margin_top = 10; //mm

		row_width = 0;
		index_col = 0;

		barcode_margin_right = 0;
		barcode_margin_bottom = 0;

		$container = $('#barcode-print-container');
		
		$table = $('<table id="table-print">');
		$tbody = $('<tbody>');
		$tr = $('<tr>');
		$container.find('canvas').each(function(i, elm) {
			const $elm = $(elm);
			const $elm_new = $elm.clone();
			const image_string = $(elm)[0].toDataURL();
			width = parseFloat($elm.width()) * milimeter;
			height = parseFloat($elm.height()) * milimeter;
			
			row_width = margin_left + (index_col * width) + ( index_col * barcode_margin_right );
			index_col++;
			
			cek_width = row_width + (width * milimeter);
			// console.log(cek_width);
			if ( cek_width > 210 ) 
			{
				index_col = 1;
				row_width = 0;
				$tbody.append($tr);
				$tr = $('<tr>');
			}
			
			$td = $('<td>');

			$td.html('<div style="margin-top:-5px;text-align:center; padding: 0 10px;max-width:250px" class="nama-barang">' + $elm.prev().text() + '</div><img src="' + image_string + '" style="width:' + $elm.width() + 'px; max-width:' +  $elm.width() + 'px; height: ' + $elm.height() + 'px"/>');
			$tr.append($td);
			console.log('fff');
		});
		
		if ($tr.children('td').length) {
			$tbody.append($tr);
		}
		
		$('#print-container').remove();
		$print_container = $('<div id="print-container" style="padding:10px">');
		$table.append($tbody);
		$print_container.append($table);
		$print_container.appendTo($('.card-body'));
		
		
		printJS({printable: 'print-container', type: 'html', style:'td {padding-left:' + $('#barcode-item-margin-left').val() + 'pt} table tr td:first-child {padding-left:' + $('#barcode-item-margin-left-first-column').val() + 'pt !important}' ,css: base_url + 'public/themes/modern/css/barcode-cetak-print.css?r=' + Math.random()});
		$('#print-container').remove();
		
	})
	
	function mm(value) {
		point = value * 2.83465; // 1mm to point
		dxa = 20;
		return point * dxa; 
	}
	
	$('#export-word').click(function() {
		
		paper_width = setInt($('#paper-size-width').val());
		paper_height = setInt($('#paper-size-height').val());
		
		margin_left = 10; //mm
		margin_top = 10; //mm

		row_width = 0;
		index_col = 0;

		barcode_margin_right = 0;
		barcode_margin_bottom = 0;

		table_row = [];
		table_col = [];
		$container = $('#barcode-print-container');
		$container.find('canvas').each(function(i, elm) {
			
			const $elm = $(elm);
			nama_barang = $(elm).prev().text();
			const image_string = $(elm)[0].toDataURL();
			width = parseFloat($elm.width());
			height = parseFloat($elm.height());
		
			row_width = margin_left + (index_col * width * milimeter) + ( index_col * barcode_margin_right );
			index_col++;
			
			cek_width = row_width + (width * milimeter);
			if ( cek_width > paper_width) 
			{
				index_col = 1;
				row_width = 0;

				table_row.push(
					new docx.TableRow({
						children: table_col	
					})
				)
				
				table_col = [];				
			}
			
			table_col.push(
				new docx.TableCell({							
					children: [
						new docx.Paragraph({
							// text: nama_barang,
							alignment: docx.AlignmentType.CENTER,
							spacing: {
								before: 100,
								after: 0,
								left: 50,
								right: 50,
								outlineLevel: 3,
							},
							children: [
								 new docx.TextRun({
									text: nama_barang,
									bold: false,
									font: "Arial",
									size: "24pt"
								 })
							]
							
						}),
						new docx.Paragraph({
							children : [
								new docx.ImageRun({
								data: image_string,
								transformation: {
										width: width,
										height: height,
									}
								})
							]
						})
						
					]
					
				})
			)
			
		});
		
		if (table_col) {
			table_row.push(
				new docx.TableRow({
					children: table_col	
				})
			)
		}
		
		const orientation = paper_width > paper_height ? docx.PageOrientation.LANSCAPE : docx.PageOrientation.PORTRAIT;
	 	const doc = new docx.Document({
			creator: "Jagowebdev",
			description: "Barcode",
			title: "Barcode",
			sections: [
				{
					properties: {
						page: {
							margin: {
								top: mm(margin_top),
								right: mm(10),
								bottom: mm(10),
								left: mm(margin_left)
							},
							size: {
								orientation: orientation,
								height: docx.convertMillimetersToTwip(paper_height),
								width: docx.convertMillimetersToTwip(paper_width),
							},
						},
					},
					children: [
						new docx.Table({
							rows: table_row
						})
					]
				}
			]
		});
		
		docx.Packer.toBlob(doc).then(blob => {
		saveAs(blob, "Barcode-cetak.docx");
		});
	})
});