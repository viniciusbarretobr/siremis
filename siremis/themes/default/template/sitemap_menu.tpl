<form id="{$form.name}" name="{$form.name}">

<div style="padding-left:25px; ">

	<div>
		<div ><h2>{$widget.title}</h2></div>
	</div>
		
		{if $widget.description}
		<p class="input_row" style="line-height:20px;padding-bottom:20px;">		
		<span>{$widget.description}</span>
		</p>
		{else}
		<div style="height:15px;"></div>
		{/if}

	{assign var='i' value=0}
	<table class="dashboard" >
	{foreach item=item from=$widget.menu}
	  {if $i % 3 == 0}
	     <tr>
	  {/if}
	  	<td valign="top">
	  		<div class="{$item->m_IconCSSClass}">
				<h3>{$item->m_Name}</h3>
				<p>{$item->m_Description}</p>	
				{if is_array($item->m_ChildNodes)}
				{if $item->m_ChildNodes|@count > 0}
				<ul>
				{foreach item=subitem from=$item->m_ChildNodes}													
					<li><a href="{if $subitem->m_URL}{$subitem->m_URL}{else}javascript:{/if}">{$subitem->m_Name}</a></li>					
					{if is_array($subitem->m_ChildNodes)}
					{if $subitem->m_ChildNodes|@count > 0}
						{foreach item=thirditem from=$subitem->m_ChildNodes}													
							<li><a href="{if $thirditem->m_URL}{$thirditem->m_URL}{else}javascript:{/if}"> &raquo; {$thirditem->m_Name}</a></li>					
						{/foreach}	
					{/if}
					{/if}
				{/foreach}	
				</ul>
				{assign var='i' value=$i+1}	
				{/if}
				{/if}
			</div>
	  	</td>
	  {if $i % 3 == 0}
	     <tr>
	  {/if}
	{/foreach}
	</table>
</div>

</form>		
