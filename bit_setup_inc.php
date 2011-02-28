<?php
global $gBitSystem, $gBitSmarty;

$registerHash = array(
	'package_name' => 'stickies',
	'package_path' => dirname( __FILE__ ).'/',
);
$gBitSystem->registerPackage( $registerHash );

if( $gBitSystem->isPackageActive( 'stickies' ) ) {
	$gLibertySystem->registerService( 'stickies', STICKIES_PKG_NAME, array(
		'content_icon_tpl' => 'bitpackage:stickies/service_content_icon_inc.tpl',
		)
	);
}
?>
