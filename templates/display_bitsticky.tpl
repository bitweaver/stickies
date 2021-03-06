{strip}
{if $gBitSystem->isPackageActive( 'stickies' ) and $stickyInfo}
	{box title="Personal Note: `$stickyInfo.title`" class="stickynote"}
		<div class="floaticon">
			<a href="{$smarty.const.STICKIES_PKG_URL}edit.php?notated_content_id={$stickyInfo.notated_content_id}">{booticon ipackage=stickies iname="icon-pushpin" iexplain="edit sticky"}</a>
			<a href="{$smarty.const.STICKIES_PKG_URL}edit.php?notated_content_id={$stickyInfo.notated_content_id}&amp;delete=1">{booticon iname="icon-trash" ipackage="icons" iexplain="delete sticky"}</a>
		</div>
		{$stickyInfo.parsed}
		<div class="footer">
			{tr}Created{/tr}: {$stickyInfo.created|bit_short_date}
			{if $stickyInfo.created != $stickyInfo.last_modified}
				, {tr}Modified{/tr}: {$stickyInfo.last_modified|bit_short_date}
			{/if}
		</div>
	{/box}
{/if}
{/strip}
