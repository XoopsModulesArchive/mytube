<?php
/**
* MyTube - a multicategory video management module
*
* Based upon WF-Links
*
* File: admin/permissions.php
*
* @copyright		http://www.xoops.org/ The XOOPS Project
* @copyright		XOOPS_copyrights.txt
* @copyright		http://www.impresscms.org/ The ImpressCMS Project
* @license		GNU General Public License (GPL)
*				a copy of the GNU license is enclosed.
* ----------------------------------------------------------------------------------------------------------
* @package		WF-Links 
* @since			1.03
* @author		John N
* ----------------------------------------------------------------------------------------------------------
* 				MyTube
* @since			1.00
* @author		McDonald
* @version		$Id$
*/

include 'admin_header.php';
include_once '../../../include/cp_header.php';
include_once XOOPS_ROOT_PATH . '/class/xoopsform/grouppermform.php';

xoops_cp_header();
xtube_adminmenu( _AM_XTUBE_PERM_MANAGEMENT );

$permtoset = isset( $_POST['permtoset'] ) ? intval( $_POST['permtoset'] ) : 1;
$selected = array( '', '', '', '', '' );
$selected[$permtoset-1] = 'selected';
echo "<form method='post' name='fselperm' action='permissions.php'><table border=0><tr><td><select name='permtoset' onChange='javascript: document.fselperm.submit()'>
<option value='1'" . $selected[0] . ">" . _AM_XTUBE_PERM_CPERMISSIONS . "</option>
<option value='2'" . $selected[1] . ">" . _AM_XTUBE_PERM_SPERMISSIONS . "</option>
<option value='3'" . $selected[2] . ">" . _AM_XTUBE_PERM_APERMISSIONS . "</option>
<option value='4'" . $selected[3] . ">" . _AM_XTUBE_PERM_AUTOPERMISSIONS . "</option>
<option value='5'" . $selected[4] . ">" . _AM_XTUBE_PERM_RATEPERMISSIONS . "</option>
</select></td></tr><tr><td><input type='submit' name='go'/></td></tr></table></form>";
$module_id = $xoopsModule -> getVar('mid');

switch( $permtoset ) {
	case 1:
		$title_of_form = _AM_XTUBE_PERM_CPERMISSIONS;
		$perm_name = 'XTubeCatPerm';
		$perm_desc = _AM_XTUBE_PERM_CSELECTPERMISSIONS;
		break;
	case 2:
		$title_of_form = _AM_XTUBE_PERM_SPERMISSIONS;
		$perm_name = 'XTubeSubPerm';
		$perm_desc = _AM_XTUBE_PERM_SPERMISSIONS_TEXT;
		break;
	case 3:
		$title_of_form = _AM_XTUBE_PERM_APERMISSIONS;
		$perm_name = 'XTubeAppPerm';
		$perm_desc = _AM_XTUBE_PERM_APERMISSIONS_TEXT;
		break;
	case 4:
		$title_of_form = _AM_XTUBE_PERM_AUTOPERMISSIONS;
		$perm_name = 'XTubeAutoApp';
		$perm_desc = _AM_XTUBE_PERM_AUTOPERMISSIONS_TEXT;
		break;
	case 5:
		$title_of_form = _AM_XTUBE_PERM_RATEPERMISSIONS;
		$perm_name = 'XTubeRatePerms';
		$perm_desc = _AM_XTUBE_PERM_RATEPERMISSIONS_TEXT;
		break;
}

$permform = new XoopsGroupPermForm( $title_of_form, $module_id, $perm_name, $perm_desc );
$result = $xoopsDB -> query( 'SELECT cid, pid, title FROM ' . $xoopsDB -> prefix( 'xoopstube_cat' ) . ' ORDER BY title ASC' );
if ( $xoopsDB -> getRowsNum( $result ) ) {
    while ( $permrow = $xoopsDB -> fetcharray( $result ) ) {
        $permform -> addItem( $permrow['cid'], '&nbsp;' . $permrow['title'], $permrow['pid']);
    }
    echo $permform -> render();
} else {
    echo '<div><b>' . _AM_XTUBE_PERM_CNOCATEGORY . '</b></div>';
} 
unset( $permform );

echo _AM_XTUBE_PERM_PERMSNOTE . '<br />';

xoops_cp_footer();
?>