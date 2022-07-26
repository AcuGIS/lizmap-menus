// The last element of the default view.js looks like below:
// .hide()}()}));  
// past the code below in the 'PASTE HERE' section shown below:
// .hide()}() PASTE HERE })); 

.hide()}()
	$('.select2').each(function() {
		let obj = $(this);
		obj.select2({
			placeholder: obj.attr('placeholder'),
			allowClear: true
		});
	});
