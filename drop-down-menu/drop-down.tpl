<!--{meta_html csstheme 'css/media.css'}-->
{meta_html css 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css'}
{meta_html js 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js'}


	<div class="pull-right mt-8">
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
	</div>

<br/><br/>
</div>
	<div class="fg-sidebar-right">
