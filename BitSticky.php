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
// $Id: BitSticky.php,v 1.6 2006/01/31 20:21:00 bitweaver Exp $

/**
 * required setup
 */
require_once( LIBERTY_PKG_PATH.'LibertyContent.php' );

define('TIKISTICKY_CONTENT_TYPE_GUID', 'tikisticky' );

/**
 * @package	stickies
 */
class BitSticky extends LibertyContent {

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


	/**
	 * Load a sticky object identified by mStickyId, $this->mNotatedContentId or mContentId in that order
	 *
	 * Populates the mInfo array with the following fields
	 *
	 * sticky_id
	 * notated_content_id
	 * parsed
	 * @return integer count of number of fields in MInfo
	 */
	function load() {
		if( @BitBase::verifyId( $this->mStickyId ) || @BitBase::verifyId( $this->mContentId )  || @BitBase::verifyId( $this->mNotatedContentId ) ) {
			if( @BitBase::verifyId( $this->mStickyId ) ) {
				$whereSql = 'tn.`sticky_id`=?';
				$bindVars = array( $this->mStickyId );
			} elseif( @BitBase::verifyId( $this->mNotatedContentId ) ) {
				global $gBitUser;
				$whereSql = 'tn.`notated_content_id`=? AND tc.`user_id`=?';
				$bindVars = array( $this->mNotatedContentId, $gBitUser->mUserId );
			} elseif( @BitBase::verifyId( $this->mContentId ) ) {
				$whereSql = 'tn.`content_id`=?';
				$bindVars = array( $this->mContentId );
			}
			$query =   "SELECT tn.*, tc.*
						FROM `".BIT_DB_PREFIX."stickies` tn 
						INNER JOIN `".BIT_DB_PREFIX."tiki_content` tc ON (tc.`content_id` = tn.`content_id`) 
						WHERE $whereSql";
			$result = $this->mDb->query( $query, $bindVars );

			if ( $result && $result->numRows() ) {
				$this->mInfo = $result->fields;
				$this->mInfo['parsed'] = $this->parseData();
				$this->mContentId = $result->fields['content_id'];
				$this->mStickyId = $result->fields['sticky_id'];
				LibertyContent::load();
			}
		}
		return( count( $this->mInfo ) );
	}
	
    /**
     * Verify the core class data required to update the tiki_content table entries
	 *
	 * @param array Array of content data to be stored 
	 * pParamHash Array 
	 * (See LibertyContent::verify for details of the core fields - which
	 * appends a [content_store] array with all the values for tiki_content)
	 *
	 * content_type_guid	string	Should contain 'tikisticky'
	 * notated_content_id	integer	content_id of the object to which the stickie isattached
	 * sticky_store - Array of values for entering in stickies
	 *		sticky_id			integer		If existing then current sticky id
	 *										otherwise populate from sequence
	 *		content_id			integer		Content id of the note
	 *		notated_content_id	integer		Content id of the object to which it is attached
	 */
	function verify( &$pParamHash ) {
		// It is possible a derived class set this to something different
		if( !@BitBase::verifyId( $pParamHash['content_type_guid'] ) ) {
			$pParamHash['content_type_guid'] = $this->mContentTypeGuid;
		}

		if( !@BitBase::verifyId( $pParamHash['notated_content_id'] ) ) {
			$this->mErrors['content'] = "No content to notate";
		} else {
			global $gBitUser;
			$query = "SELECT tn.`sticky_id` FROM `".BIT_DB_PREFIX."stickies` tn 
						INNER JOIN `".BIT_DB_PREFIX."tiki_content` tc ON( tn.`content_id`=tc.`content_id` )
					  WHERE tc.`user_id`=? AND tn.`notated_content_id`=?";
			$this->mStickyId = $this->mDb->getOne( $query, array( $gBitUser->mUserId, $pParamHash['notated_content_id'] ) );
			$pParamHash['sticky_store']['notated_content_id'] = $pParamHash['notated_content_id'];
		}
		return( count( $this->mErrors ) == 0 );
	}	
	
    /**
     * Create a new stickies object or update an existing one
	 *
	 * @param array Array of content data to be stored (see verify for details)
	 * @return integer Number of errors detected ( 0 if successful )
	 */
	function store( &$pParamHash ) {
		if( $this->verify( $pParamHash ) ) {
			$this->mDb->StartTrans();
			if( LibertyContent::store( $pParamHash ) ) {
            	if( $this->mStickyId ) {
					$stickyId = array ( "name" => "sticky_id", "value" => $this->mStickyId );
					$result = $this->mDb->associateUpdate( BIT_DB_PREFIX."stickies", $pParamHash['sticky_store'], $stickyId );
				} else {
					$pParamHash['sticky_store']['content_id'] = $pParamHash['content_id'];
					if( @BitBase::verifyId( $pParamHash['sticky_id'] ) ) {
						$pParamHash['sticky_store']['sticky_id'] = $pParamHash['sticky_id'];
					} else {
						$pParamHash['sticky_store']['sticky_id'] = $this->mDb->GenID( 'tiki_stickies_sticky_id_seq');
					}
					$this->mPageId = $pParamHash['sticky_store']['sticky_id'];

					$result = $this->mDb->associateInsert( BIT_DB_PREFIX."stickies", $pParamHash['sticky_store'] );
				}
				$this->mDb->CompleteTrans();
			} else {
				$this->mDb->RollbackTrans();
			}
		}

		return( count( $this->mErrors ) == 0 );
	}
	
	/**
	 * Delete stickies object and related content record
	 */
	function expunge() {
		$ret = FALSE;
		if( $this->isValid() ) {
			$this->mDb->StartTrans();
			$query = "DELETE FROM `".BIT_DB_PREFIX."stickies` WHERE `content_id` = ?";
			$result = $this->mDb->query( $query, array( $this->mContentId ) );
			if( LibertyContent::expunge() ) {
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
