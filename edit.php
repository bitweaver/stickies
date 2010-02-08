<?php
/**
 * @package	stickies
 * @subpackage functions
 * @author	spider <spider@steelsun.com>
 */

/**
 * required setup
 */
require_once( '../kernel/setup_inc.php' );
require_once( STICKIES_PKG_PATH.'BitSticky.php' );

$gBitSystem->verifyPackage( 'stickies' );

// Now check permissions to access this page
$gBitSystem->verifyPermission( 'p_stickies_create' );

if( isset($_REQUEST["blog_id"]) ) {
	$blog_id = $_REQUEST["blog_id"];
	$blog_data = $gBlog->get_blog($blog_id);
} else {
	$blog_id = NULL;
}

// cheap wiki-only hack for now
require_once( WIKI_PKG_PATH.'lookup_page_inc.php' );
require_once( LIBERTY_PKG_PATH.'lookup_content_inc.php' );
if( !$gContent->isvalid() && !empty( $_REQUEST['notated_content_id'] ) ) {
	if( $viewContent = $gContent->getLibertyObject( $_REQUEST['notated_content_id'] ) ) {
		$gBitSmarty->assign_by_ref( 'pageInfo', $viewContent->mInfo );
		$gContent = &$viewContent;
		$gBitSmarty->assign_by_ref( 'gContent', $gContent );
	}
}

if( !$gContent->isvalid() ) {
	$gBitSystem->fatalError( tra( 'Unknown content to create sticky note' ));
}

$gSticky = new BitSticky( (isset( $_REQUEST['sticky_id'] ) ? $_REQUEST['sticky_id'] : ''), NULL, $gContent->mContentId );
if( !$gSticky->load() ) {
	$gSticky->mInfo['notated_content_id'] = $gContent->mContentId;
}

if( !empty( $_REQUEST['save_sticky'] ) ) {
	if( $gSticky->store( $_REQUEST ) ) {
		header( 'Location: '.$gContent->getDisplayUrl() );
	}
} elseif( !empty( $_REQUEST['preview'] ) ) {
} elseif( !empty( $_REQUEST['delete'] ) ) {
	$formHash['content_id'] = $gContent->mContentId;
	$formHash['sticky_id'] = $gSticky->mStickyId;
	$formHash['delete'] = TRUE;
	if( !empty( $_POST['confirm'] ) ) {
		if( $gSticky->expunge() ) {
			header( 'Location: '.$gContent->getDisplayUrl() );
			die;
		} else {
			$gBitSystem->confirmDialog( $formHash, 
				array( 
					'error'=> implode( $gContent->mErrors ), 
					'warning' => tra('Are you sure you want to remove this sticky note?') . ' ' .$gSticky->mInfo['title'],
				)
			);
		}
	} else {
		$gBitSystem->confirmDialog( $formHash, 
			array( 
					'warning' => tra('Are you sure you want to remove this sticky note?') . ' ' .$gSticky->mInfo['title'],
			)
		);
	}
}

// WYSIWYG and Quicktag variable
$gBitSmarty->assign( 'textarea_id', LIBERTY_TEXT_AREA );

$gBitSmarty->assign_by_ref( 'stickyInfo', $gSticky->mInfo );

$gBitSystem->display( 'bitpackage:stickies/edit_sticky.tpl', tra( 'Edit Sticky Note for ').$gContent->getTitle() , array( 'display_mode' => 'edit' ));

?>
