# Lizmap Map Menus

Add a drop-down, accordian, or tree menu for Lizmap.

## View [online demo](https://lizmap-demo.acugis.com)

![alt text](images/Lizmap-Map-Menus.png)<br/><br/>






## Installation: 

Installation of all three menu types is identical.

1. Create a custom theme directory for testing:

```console  
mkdir /var/www/lizmap-web-client-3.5.5/lizmap/var/themes/default/view
```
2. Copy the view.tpl file to the new directory
```console
 cp -a lizmap/modules/view/templates/view.tpl lizmap/var/themes/default/view/view.tpl
```

3. Add the contents of the [menu.tpl](https://github.com/AcuGIS/lizmap-menus/blob/main/menu.tpl) to your view.tpl file, inserting just below <code>{meta_html csstheme 'css/media.css'}</code>

4. Add the contents of the [menu.css](https://github.com/AcuGIS/lizmap-menus/blob/main/menu.css) via Lizmap admin Theme CSS UI.

5. Remove the menu types you do not wish to use.

6. <b>Drop-Down Map Only</b>:  Add the contents of drop-down.js to your view.js file.

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











