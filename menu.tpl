{meta_html css 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css'}
{meta_html js 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js'}
		
<br/>

<div class="clearfix"></div>
<div class="d-flex md:d-block">
	<div class="fg-sidebar-left">

<!--Drop-Down-Menu Start-->

<h3>Drop-Down Menu</h3>
		<select class="select2" placeholder="Select Map" onChange="if(this.value != '') location.href = this.value;">
			<option></option>
			{foreach $mapitems as $mi}
				{if $mi->type == 'rep'}
					<optgroup label="{$mi->title}">
						{foreach $mi->childItems as $p}
							<option value="{$p->url}{if $hide_header}&h=0{/if}">{$p->title}</option>
						{/foreach}
					</optgroup>
				{/if}
			{/foreach}
		</select>
    
<!--Drop-Down-Menu Finish-->


<!--Accordian-Menu Start-->
		<h3>Accordian Menu</h3>
			
		<div class="accordion" id="accordion">
			{foreach $mapitems as $key => $mi}
				{if $mi->type == 'rep'}
					<div class="accordion-group">
						<div class="accordion-heading">
							<a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion" href="#collapse{$key}"> {$mi->title} </a>
						</div>
						<div id="collapse{$key}" class="accordion-body collapse">
							<div class="accordion-inner"> 
								<table class="table">
									{foreach $mi->childItems as $p}
									<tr>
										<td class="border-0">
											<span class="glyphicon glyphicon-usd"></span>
											<a href="{$p->url}{if $hide_header}&h=0{/if}">{$p->title}</a>
										</td>
									</tr>
									{/foreach}
								</table>
							</div>
						</div>
					</div>
				{/if}
			{/foreach}
		</div>
		
<!--Accordian-Menu Finish-->	

<!--Tree-Menu Start-->	
<h3>Tree Menu</h3>

		<ul class="fg-tree m-0">
			{foreach $mapitems as $mi}
				{if $mi->type == 'rep'}
					<li>
						<b>{$mi->title}</b>
						<ul class="fg-tree">
							{foreach $mi->childItems as $p}
								<li>
									<a href="{$p->url}{if $hide_header}&h=0{/if}">{$p->title}</a>
								</li>
							{/foreach}
						</ul>
					</li>
				{/if}
			{/foreach}
		</ul>				
	</div>
<!--Tree-Menu Finish-->
	<div class="fg-sidebar-right">
