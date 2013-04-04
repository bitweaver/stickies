{strip}
<div class="floaticon">{bithelp}</div>

<div class="admin stickies">
	<div class="header">
		<h1>{tr}Edit Sticky for {/tr}"{$pageInfo.title|escape}"</h1>
	</div>

	{formhelp note="Here you can add personal notes to this page. This information will only be visible to you when you are logged in to your account."}

	<div class="body">
		{form ipackage="stickies" ifile="edit.php"}
			{jstabs}
				{jstab title="Edit Sticky"}
					{legend legend="Edit/Create Sticky Note"}
						<input type="hidden" name="notated_content_id" value="{$stickyInfo.notated_content_id}" />
						<div class="control-group">
							{formfeedback warning=$warning}
							{formlabel label="Title" for="title"}
							{forminput}
								<input type="text" size="50" name="title" value="{$stickyInfo.title|escape}" id="title" />
							{/forminput}
						</div>

						{if $gBitSystem->isPackageActive( 'smileys' )}
							{include file="bitpackage:smileys/smileys_full.tpl"}
						{/if}

						{if $gBitSystem->isPackageActive( 'quicktags' )}
							{include file="bitpackage:quicktags/quicktags_full.tpl"}
						{/if}

						<div class="control-group">
							{forminput}
								<textarea {spellchecker} id="{$textarea_id}" name="edit" rows="{$smarty.cookies.rows|default:40}" cols="50">{if !$preview}{$stickyInfo.data|escape:html}{else}{$edit|escape:html}{/if}</textarea>
							{/forminput}
						</div>

						<div class="control-group submit">
							<input type="submit" class="btn" name="save_sticky" value="{tr}save{/tr}" />
						</div>
					{/legend}
				{/jstab}
				{jstab title="Advanced Options"}
					{legend legend="Advanced Options"}
						{include file="bitpackage:liberty/edit_format.tpl"}
					{/legend}
				{/jstab}
			{/jstabs}
		{/form}
	</div><!-- end .body -->
</div>
{/strip}
