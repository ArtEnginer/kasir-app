 $(document).ready(function() {
	 	 
	 
	function show_message(type, content) {
		return '<div class="alert alert-danger">' + content + '</div>'; 
	}
	
	drag_image_gallery = dragula([document.getElementById('list-image-container')], {
		moves: function (el, container, handle) {
			return handle.classList.contains('grip') || handle.parentNode.classList.contains('grip');
		}
	});
	
	drag_image_gallery.on('dragend', function(el)
	{	
		$li = $('.gallery-container').find('li.thumbnail-item');
		
		list_id = [];
		$li.each(function(i, elm){
			list_id.push( $(elm).attr('id').split('-')[1] );
		});
	});
 });