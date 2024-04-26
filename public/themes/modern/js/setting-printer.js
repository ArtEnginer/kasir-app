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
		const $paper_width = $('#paper-size-width').attr('readonly', 'readonly');
		const $paper_height = $('#paper-size-height').attr('readonly', 'readonly');
		
		
		if (this.value == 'a4') {
			w = 210;
			h = 297;
		} else if (this.value == 'f4') {
			w = 215;
			h = 330;
		} else {
			w = 210;
			h = 297;
			$paper_width.removeAttr('readonly');
			$paper_height.removeAttr('readonly');
			
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
	
	function mm(value) {
		point = value * 2.83465; // 1mm to point
		dxa = 20;
		return point * dxa; 
	}
});