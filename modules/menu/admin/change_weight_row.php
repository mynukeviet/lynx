<?php

/**
 * @Project NUKEVIET 3.1
 * @Author VINADES.,JSC (contact@vinades.vn)
 * @Copyright (C) 2012 VINADES.,JSC. All rights reserved
 * @Createdate 20-03-2011 20:08
 */

if( ! defined( 'NV_IS_FILE_ADMIN' ) ) die( 'Stop!!!' );
if( ! defined( 'NV_IS_AJAX' ) ) die( 'Wrong URL' );

$id = $nv_Request->get_int( 'id', 'post', 0 );
$mid = $nv_Request->get_int( 'mid', 'post', 0 );
$parentid = $nv_Request->get_int( 'parentid', 'post', 0 );
$new_weight = $nv_Request->get_int( 'new_weight', 'post', 0 );

$query = "SELECT weight FROM " . NV_PREFIXLANG . "_" . $module_data . "_rows WHERE id=" . $id . " AND parentid=" . $parentid;
$result = $db->query( $query );
$num = $result->rowCount();

if( $num != 1 ) die( 'NO_' . $id );

$row = $result->fetch();
if( empty( $new_weight ) ) die( 'NO_' . $id );

$query = "SELECT id FROM " . NV_PREFIXLANG . "_" . $module_data . "_rows WHERE id !=" . $id . " AND parentid=" . $parentid . " AND mid=" . $mid . " ORDER BY weight ASC";
$result = $db->query( $query );

$weight = 0;
while( $row = $result->fetch() )
{
	++$weight;
	if( $weight == $new_weight ) ++$weight;
	$sql = "UPDATE " . NV_PREFIXLANG . "_" . $module_data . "_rows SET weight=" . $weight . " WHERE id=" . $row['id'];
	$db->query( $sql );
}

$sql = "UPDATE " . NV_PREFIXLANG . "_" . $module_data . "_rows SET weight=" . $new_weight . " WHERE id=" . $id . " AND parentid=" . $parentid;
$db->query( $sql );

nv_del_moduleCache( $module_name );

include NV_ROOTDIR . '/includes/header.php';
echo 'OK_' . $id . "_" . $mid . "_" . $parentid;
include NV_ROOTDIR . '/includes/footer.php';

?>