$('.select2').each(function() {
		let obj = $(this);
		obj.select2({
			placeholder: obj.attr('placeholder'),
			allowClear: true
		});
	});
	
