{if $gBitSystem->isPackageActive( 'stickies' ) && $gContent->hasUserPermission('p_stickies_create') }
		{if ($structureInfo.structure_id)}
				{assign var='stickyRequest' value="structure_id=`$structureInfo.structure_id`"}
		{else}
				{assign var='stickyRequest' value="notated_content_id=`$gContent->mInfo.content_id`"}
		{/if}
		<a href="{$smarty.const.STICKIES_PKG_URL}edit.php?{$stickyRequest}">{biticon ipackage="stickies" iname="sticky_note" iexplain="Add sticky note"}
{/if}
