<?php
/**
* MyTube - a multicategory video management module
*
* Based upon WF-Links
*
* File: singlevideo.php
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

include 'header.php';

$lid = xtube_cleanRequestVars( $_REQUEST, 'lid', 0 );
$cid = xtube_cleanRequestVars( $_REQUEST, 'cid', 0 );
$lid = intval( $lid );
$cid = intval( $cid );

$sql2 = 'SELECT count(*) FROM ' . $xoopsDB -> prefix( 'xoopstube_videos' ) . ' a LEFT JOIN '
 . $xoopsDB -> prefix( 'xoopstube_altcat' ) . ' b'
 . ' ON b.lid = a.lid'
 . ' WHERE a.published > 0 AND a.published <= ' . time()
 . ' AND (a.expired = 0 OR a.expired > ' . time() . ') AND a.offline = 0'
 . ' AND (b.cid=a.cid OR (a.cid=' . intval($cid) . ' OR b.cid=' . intval($cid) . '))';
list( $count ) = $xoopsDB -> fetchRow( $xoopsDB -> query( $sql2 ) );

if ( false == xtube_checkgroups( $cid ) && $count == 0 ) {
    redirect_header( 'index.php', 1, _MD_XTUBE_MUSTREGFIRST );
    exit();
} 

$sql = 'SELECT * FROM ' . $xoopsDB -> prefix( 'xoopstube_videos' ) . ' WHERE lid=' . intval($lid) . '
		AND (published > 0 AND published <= ' . time() . ')
		AND (expired = 0 OR expired > ' . time() . ')
		AND offline = 0 
		AND cid > 0';
$result = $xoopsDB -> query( $sql );
$video_arr = $xoopsDB -> fetchArray( $result );

if ( !is_array( $video_arr ) ) {
    redirect_header( 'index.php', 1, _MD_XTUBE_NOVIDEOLOAD );
    exit();
} 

$xoopsOption['template_main'] = 'xoopstube_singlevideo.html';
include XOOPS_ROOT_PATH . '/header.php';

// tags support
if (xtube_tag_module_included()) {
	include_once XOOPS_ROOT_PATH . '/modules/tag/include/tagbar.php';
	$xoopsTpl -> assign( 'tagbar', tagBar( $video_arr['lid'], 0 ) );
}

$video['imageheader'] = xtube_imageheader();
$video['id'] = $video_arr['lid'];
$video['cid'] = $video_arr['cid'];
$video['vidid'] = $video_arr['vidid'];
$video['description2'] = $xtubemyts -> displayTarea( $video_arr['description'], 1, 1, 1, 1, 1 );

$mytree = new XoopsTree( $xoopsDB -> prefix( 'xoopstube_cat' ), 'cid', 'pid' );
$pathstring = '<a href="index.php">' . _MD_XTUBE_MAIN . '</a>&nbsp;:&nbsp;';
$pathstring .= $mytree -> getNicePathFromId( $cid, 'title', 'viewcat.php?op=' );
$video['path'] = $pathstring;

// Get video from source
$video['showvideo'] = xtube_showvideo( $video_arr['vidid'], $video_arr['vidsource'], $video_arr['screenshot'], $video_arr['picurl'] );

// Get Social Bookmarks
$video['sbmarks'] = xtube_sbmarks( $video_arr['lid'] );

// Start of meta tags
global $xoopsTpl, $xoTheme, $xoopsModuleConfig;

    $maxWords = 100;
    $words = array();
    $words = explode( ' ', xtube_html2text( $video_arr['description'] ) );
    $newWords = array();
    $i = 0;
    while ( $i < $maxWords-1 && $i < count( $words ) ) {
      if ( isset( $words[$i] ) ) {
       $newWords[] = trim( $words[$i] );
      }
      $i++;
    }
    $video_meta_description = implode(' ', $newWords);

    if ( is_object( $xoTheme ) ) {
		if ( $video_arr['keywords'] ) {
			$xoTheme -> addMeta( 'meta', 'keywords', $video_arr['keywords'] );
		}
		$xoTheme -> addMeta( 'meta', 'title', $video_arr['title'] );
		if ( $xoopsModuleConfig['usemetadescr'] == 1 ) {
			$xoTheme -> addMeta( 'meta', 'description', $video_meta_description );
		}
    } else {
		if ( $video_arr['keywords'] ) {
			$xoopsTpl -> assign( 'xoops_meta_keywords', $video_arr['keywords'] );
		}
		if ( $xoopsModuleConfig['usemetadescr'] == 1 ) {
			$xoopsTpl -> assign( 'xoops_meta_description', $video_meta_description );
		}
    }
    $xoopsTpl -> assign( 'xoops_pagetitle', $video_arr['title'] );
// End of meta tags

$moderate = 0;
include_once XOOPS_ROOT_PATH . '/modules/' . $xoopsModule -> getvar( 'dirname' ) . '/include/videoloadinfo.php';

$xoopsTpl -> assign( 'show_screenshot', false );
if ( isset( $xoopsModuleConfig['screenshot'] ) && $xoopsModuleConfig['screenshot'] == 1 ) {
    $xoopsTpl -> assign( 'shotwidth', $xoopsModuleConfig['shotwidth'] );
    $xoopsTpl -> assign( 'shotheight', $xoopsModuleConfig['shotheight'] );
    $xoopsTpl -> assign( 'show_screenshot', true );
} 

if ( $video['isadmin'] == false ) {
	$count = xtube_updateCounter( $lid );
}

// Show other author videos
$sql = 'SELECT lid, cid, title, published FROM ' . $xoopsDB -> prefix( 'xoopstube_videos' ) . '
        WHERE submitter=' . $video_arr['submitter'] . '
        AND lid <> ' . $video_arr['lid'] . '
        AND published > 0 AND published <= ' . time() . ' AND (expired = 0 OR expired > ' . time() . ')  
        AND offline = 0 ORDER by published DESC'; 
$result = $xoopsDB -> query( $sql, 10, 0 );

while ( $arr = $xoopsDB -> fetchArray( $result ) ) {
    $videouid['title'] = $xtubemyts -> htmlSpecialCharsStrip( $arr['title'] );
    $videouid['lid'] = $arr['lid'];
    $videouid['cid'] = $arr['cid'];
    $videouid['published'] = mytube_time( formatTimestamp( $arr['published'], $xoopsModuleConfig['dateformat'] ) );
    $xoopsTpl -> append( 'video_uid', $videouid );
}

// Copyright notice
if ( isset( $xoopsModuleConfig['copyright'] ) && $xoopsModuleConfig['copyright'] == 1 ) {
    $xoopsTpl -> assign( 'lang_copyright', '' . $video['publisher'] . ' &#0169; ' . _MD_XTUBE_COPYRIGHT . ' ' . formatTimestamp( time(), 'Y' ) . ' - ' . XOOPS_URL );
}

// Show other videos by submitter
if ( isset( $xoopsModuleConfig['othervideos'] ) && $xoopsModuleConfig['othervideos'] == 1 ) {
    $xoopsTpl -> assign( 'other_videos', '<b>' ._MD_XTUBE_OTHERBYUID . '</b>'  . $video['submitter'] . '<br />' );
} else {
    $xoopsTpl -> assign( 'other_videos', '<b>' ._MD_XTUBE_NOOTHERBYUID . '</b>'  . $video['submitter'] . '<br />' );
}

$video['showsbookmarx'] = $xoopsModuleConfig['showsbookmarks'];
$video['othervideox'] = $xoopsModuleConfig['othervideos'];
$xoopsTpl -> assign( 'video', $video );

$xoopsTpl -> assign( 'back' , '<a href="javascript:history.go(-1)"><img src="' . XOOPS_URL . '/modules/' . $xoopsModule -> getvar( 'dirname' ) . '/images/icon/back.png" /></a>' );  // Displays Back button
$xoopsTpl -> assign( 'module_dir', $xoopsModule -> getVar( 'dirname' ) );

include XOOPS_ROOT_PATH . '/include/comment_view.php';
include XOOPS_ROOT_PATH . '/footer.php';
?>