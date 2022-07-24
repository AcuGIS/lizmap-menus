# Lizmap Map Menu

Adds a searchable, drop-down menu for maps.

Usage:

1. Add the contents of drop-down.tpl to your view.tpl file.  

It is best to first should create a custom Default Theme directory as below.

<code>  
mkdir /var/www/lizmap-web-client-3.5.4/lizmap/var/themes/default/view/default
</code><br/><br/>
<code>
cp -a /lizmap/modules/view/templates/main.tpl /var/themes/default/view/default
</code><br/><br/>

Insert the contents of drop-down.tpl just below<br/>

<code>{meta_html csstheme 'css/media.css'}</code><br/>


2. Add the contents of dropdown.css to the bottom of your view.css file (or add via Lizmap admin Theme UI)

3. Add the contents of drop-down.js to your view.js file

$('.select2').each(function() {
		let obj = $(this);
		obj.select2({
			placeholder: obj.attr('placeholder'),
			allowClear: true
		});
	});
}));
//# sourceMappingURL=view.js.map


