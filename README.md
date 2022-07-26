# Lizmap Map Menus

Adds a drop-down, accordian, or tree menu for Lizmap.

![alt text](images/Lizmap-Menus.png)

:warning: **You should create a custom theme directory for testing:** 

<code>  
mkdir /var/www/lizmap-web-client-3.5.5/lizmap/var/themes/default/view
</code><br/><br/>
<code>  
chown -R www-data:www-data /var/www/lizmap-web-client-3.5.5/lizmap/var/themes/default/view
</code><br/><br/>
<code>
 cp -a /var/www/lizmap-web-client-3.5.5/lizmap/modules/view/templates/view.tpl /var/www/lizmap-web-client-3.5.5/lizmap/var/themes/default/view/view.tpl
</code><br/><br/>


## Installation: 

Installation of all three menu types is identical.

1. Add the contents of menu.tpl to your view.tpl file, inserting just below <code>{meta_html csstheme 'css/media.css'}</code>



2. Add the contents of menu.css via Lizmap admin Theme CSS UI.

3. Drop-Down Map Only:  Add the contents of drop-down.js to your view.js file.

4. Remove the menu types you do not wish to use.

### Drop-Down Menu: 

Your Lizmap homepage should now look as below: <br/>

![alt text](images/Lizmap-Verify-Menu.png)


### Accordian Menu: 

Your Lizmap homepage should now look as below: <br/>
![alt text](images/Lizmap-Accordian-Menu.png)


### Tree Menu: 

Your Lizmap homepage should now look as below: <br/>
![alt text](images/Lizmap-Tree-Menu.png)


Drop Down Selector built with [select2](https://select2.org)











