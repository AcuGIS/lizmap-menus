<div class="clearfix"></div>
<div class="d-flex md:d-block">
	<div class="fg-sidebar-left">
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
	<div class="fg-sidebar-right">
