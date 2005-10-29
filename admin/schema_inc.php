<?php


$tables = array(

'tiki_stickies' => "
	sticky_id I4 PRIMARY,
	content_id I4 NOTNULL,
	notated_content_id I4 NOTNULL
		CONSTRAINTS	', CONSTRAINT `tiki_stickies_content_ref` FOREIGN KEY (`content_id`) REFERENCES `".BIT_DB_PREFIX."tiki_content` (`content_id`)'
		CONSTRAINTS	', CONSTRAINT `tiki_stickies_notated_ref` FOREIGN KEY (`notated_content_id`) REFERENCES `".BIT_DB_PREFIX."tiki_content` (`content_id`)'
"

);

global $gBitInstaller;

foreach( array_keys( $tables ) AS $tableName ) {
	$gBitInstaller->registerSchemaTable( STICKIES_PKG_NAME, $tableName, $tables[$tableName] );
}

$gBitInstaller->registerPackageInfo( STICKIES_PKG_NAME, array(
	'description' => "A personal sticky system for adding stickies to any content.",
	'license' => '<a href="http://www.gnu.org/licenses/licenses.html#LGPL">LGPL</a>',
	'version' => '0.1',
	'state' => 'beta',
	'dependencies' => '',
) );

// ### Indexes
$indices = array (
	'tiki_stickies_content_id_idx' => array( 'table' => 'tiki_stickies', 'cols' => 'content_id', 'opts' => NULL ),
	'tiki_notated_content_id_idx' => array( 'table' => 'tiki_stickies', 'cols' => 'notated_content_id', 'opts' => NULL )
);
// TODO - SPIDERR - following seems to cause time _decrease_ cause bigint on postgres. need more investigation
//	'tiki_blog_posts_created_idx' => array( 'table' => 'tiki_blog_posts', 'cols' => 'created', 'opts' => NULL ),
$gBitInstaller->registerSchemaIndexes( STICKIES_PKG_NAME, $indices );

// ### Sequences
$sequences = array (
	'tiki_stickies_sticky_id_seq' => array( 'start' => 1 ) 
);
$gBitInstaller->registerSchemaSequences( STICKIES_PKG_NAME, $sequences );

// ### Default UserPermissions
$gBitInstaller->registerUserPermissions( STICKIES_PKG_NAME, array(
	array('bit_p_stickies_edit', 'Can create stickies', 'registered', STICKIES_PKG_NAME),
	array('bit_p_stickies_admin', 'Can admin stickies', 'editors', STICKIES_PKG_NAME)
) );


?>
