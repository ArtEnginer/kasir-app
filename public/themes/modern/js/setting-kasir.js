jQuery(document).ready(function () {
	$('select[name="item_layout"]').change(function() {
		if (this.value == 'grid') {
			$('#grid-layout').show();
		} else {
			$('#grid-layout').hide();
		}
	})
	
	$('select[name="qty_pengali"]').change(function() {
		if (this.value =='Y') {
			$('.pengali-suffix-container').show();
		} else {
			$('.pengali-suffix-container').hide();
		}
	});
	$('input[name="qty_pengali_suffix"]').keyup(function(){
		this.value = this.value.replace(/[^a-zA-Z]/g,'');
		this.value = this.value.slice(-1);
		if (this.value == '') {
			this.value = 'a';
		}
	})
	
});
