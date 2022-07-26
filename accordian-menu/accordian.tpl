<div class="clearfix"></div>
<div class="d-flex md:d-block">
	<div class="fg-sidebar-left">
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
