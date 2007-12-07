<?php

global $gBitSystem, $gUpgradeFrom, $gUpgradeTo;

$upgrades = array(

	'BWR1' => array(
		'BWR2' => array(
// de-tikify tables
array( 'DATADICT' => array(
	array( 'RENAMETABLE' => array(
		'tiki_stickies' => 'stickies',
	)),
)),

// query: create a stickies_sticky_id_seq and bring the table up to date with the current max sticky_id used in the stickies table - this basically for mysql
array( 'PHP' => '
	$query = $gBitDb->getOne("SELECT MAX(sticky_id) FROM `'.BIT_DB_PREFIX.'stickies`");
	$tempId = $gBitDb->mDb->GenID("`'.BIT_DB_PREFIX.'stickies_sticky_id_seq`", $query);
' ),
		)
	),

);

if( isset( $upgrades[$gUpgradeFrom][$gUpgradeTo] ) ) {
	$gBitSystem->registerUpgrade( STICKIES_PKG_NAME, $upgrades[$gUpgradeFrom][$gUpgradeTo] );
}
?>
