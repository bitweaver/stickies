<?php
/**
 * @package	stickies
 * @author	spider <spider@steelsun.com>
 */

// +----------------------------------------------------------------------+
// | Copyright (c) 2004, bitweaver.org
// +----------------------------------------------------------------------+
// | All Rights Reserved. See copyright.txt for details and a complete list of authors.
// | Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details
// |
// | For comments, please use phpdocu.sourceforge.net documentation standards!!!
// | -> see http://phpdocu.sourceforge.net/
// +----------------------------------------------------------------------+
// | Authors: spider <spider@steelsun.com>
// +----------------------------------------------------------------------+
//
// $Id: BitSticky.php,v 1.1.1.1.2.4 2005/08/07 16:25:54 lsces Exp $

/**
 * required setup
 */
require_once( LIBERTY_PKG_PATH.'LibertyAttachable.php' );

define('TIKISTICKY_CONTENT_TYPE_GUID', 'tikisticky' );

/**
 * @package	stickies
 * @subpackage BitSticky
 */
class BitSticky extends LibertyAttachable {

	function BitSticky( $pStickyId=NULL, $pContentId=NULL, $pNotatedContentId=NULL ) {
		LibertyAttachable::LibertyAttachable();
		$this->registerContentType( TIKISTICKY_CONTENT_TYPE_GUID, array(
				'content_type_guid' => TIKISTICKY_CONTENT_TYPE_GUID,
				'content_description' => 'Sticky',
				'handler_class' => 'BitSticky',
				'handler_package' => 'stickies',
				'handler_file' => 'BitSticky.php',
				'maintainer_url' => 'http://www.bitweaver.org'
			) );
		$this->mStickyId = $pStickyId;
		$this->mContentId = $pContentId;
		$this->mNotatedContentId = $pNotatedContentId;
		$this->mContentTypeGuid = TIKISTICKY_CONTENT_TYPE_GUID;
	}


	function load() {
		if( !empty( $this->mStickyId ) || !empty( $this->mContentId )  || !empty( $this->mNotatedContentId ) ) {
			if( !empty( $this->mStickyId ) ) {
				$whereSql = 'tn.`sticky_id`=?';
				$bindVars = array( $this->mStickyId );
			} elseif( !empty( $this->mNotatedContentId ) ) {
				global $gBitUser;
				$whereSql = 'tn.`notated_content_id`=? AND tc.`user_id`=?';
				$bindVars = array( $this->mNotatedContentId, $gBitUser->mUserId );
			} elseif( !empty( $this->mContentId ) ) {
				$whereSql = 'tn.`content_id`=?';
				$bindVars = array( $this->mContentId );
			}
			$query =   "SELECT tn.*, tc.*
						FROM `".BIT_DB_PREFIX."tiki_stickies` tn 
						INNER JOIN `".BIT_DB_PREFIX."tiki_content` tc ON (tc.`content_id` = tn.`content_id`) 
						WHERE $whereSql";
			$result = $this->mDb->query( $query, $bindVars );

			if ( $result && $result->numRows() ) {
				$this->mInfo = $result->fields;
				$this->mInfo['parsed'] = $this->parseData();
				$this->mContentId = $result->fields['content_id'];
				$this->mStickyId = $result->fields['sticky_id'];
				LibertyAttachable::load();
			}
		}
		return( count( $this->mInfo ) );
	}
	
	function verify( &$pParamHash ) {
		// It is possible a derived class set this to something different
		if( empty( $pParamHash['content_type_guid'] ) ) {
			$pParamHash['content_type_guid'] = $this->mContentTypeGuid;
		}

		if( empty( $pParamHash['notated_content_id'] ) ) {
			$this->mErrors['content'] = "No content to notate";
		} else {
			global $gBitUser;
			$query = "SELECT tn.`sticky_id` FROM `".BIT_DB_PREFIX."tiki_stickies` tn 
						INNER JOIN `".BIT_DB_PREFIX."tiki_content` tc ON( tn.`content_id`=tc.`content_id` )
					  WHERE tc.`user_id`=? AND tn.`notated_content_id`=?";
			$this->mStickyId = $this->mDb->getOne( $query, array( $gBitUser->mUserId, $pParamHash['notated_content_id'] ) );
			$pParamHash['sticky_store']['notated_content_id'] = $pParamHash['notated_content_id'];
		}
		return( count( $this->mErrors ) == 0 );
	}	
	
	function store( &$pParamHash ) {
		$this->mDb->StartTrans();
		if( $this->verify( $pParamHash ) && LibertyAttachable::store( $pParamHash ) ) {
            if( $this->mStickyId ) {
				$stickyId = array ( "name" => "sticky_id", "value" => $this->mStickyId );
				$result = $this->mDb->associateUpdate( BIT_DB_PREFIX."tiki_stickies", $pParamHash['sticky_store'], $stickyId );
			} else {
				$pParamHash['sticky_store']['content_id'] = $pParamHash['content_id'];
				if( isset( $pParamHash['page_id'] ) && is_numeric( $pParamHash['sticky_id'] ) ) {
					$pParamHash['sticky_store']['sticky_id'] = $pParamHash['sticky_id'];
				} else {
					$pParamHash['sticky_store']['sticky_id'] = $this->mDb->GenID( 'tiki_stickies_sticky_id_seq');
				}
				$this->mPageId = $pParamHash['sticky_store']['sticky_id'];

				$result = $this->mDb->associateInsert( BIT_DB_PREFIX."tiki_stickies", $pParamHash['sticky_store'] );
			}
			$this->mDb->CompleteTrans();
		} else {
			$this->mDb->RollbackTrans();
		}

		return( count( $this->mErrors ) == 0 );
	}
	
	function expunge() {
		$ret = FALSE;
		if( $this->isValid() ) {
			$this->mDb->StartTrans();
			$query = "DELETE FROM `".BIT_DB_PREFIX."tiki_stickies` WHERE `content_id` = ?";
			$result = $this->mDb->query( $query, array( $this->mContentId ) );
			if( LibertyAttachable::expunge() ) {
				$ret = TRUE;
				$this->mDb->CompleteTrans();
			} else {
				$this->mDb->RollbackTrans();
			}
		}
		return $ret;
	}
	
}

?>
