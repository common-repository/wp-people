<?php

/*
+----------------------------------------------------------------+
+	cetsHelloWorld-tinymce V1.60
+	by Deanna Schneider
+   required for cetsHelloWorld and WordPress 2.5
+----------------------------------------------------------------+
*/

/** Define the server path to the file wp-config here, if you placed WP-CONTENT outside the classic file structure */

function is_odd($number) {
	return $number & 1; // 0 = even, 1 = odd
}

$path  = ''; // It should be end with a trailing slash    
if ( !defined('WP_LOAD_PATH') ) {
	$root = dirname(dirname(dirname(dirname(dirname(__FILE__)))));
	if (file_exists($root . '/wp-load.php')) {
	  // WP 2.6
	  require_once($root . '/wp-load.php');
	} else {
	  // Before 2.6
	  require_once($root . '/wp-config.php');
	}
}

global $PHP_SELF;
global $userdata;
global $wpdb, $cat_name, $cat_type;

/** Load WordPress Administration Bootstrap */
require_once($root . '/wp-admin/admin.php');

add_thickbox();
wp_enqueue_style( 'colors' );


$hook_suffix = '';
if ( isset($page_hook) )
	$hook_suffix = "$page_hook";
else if ( isset($plugin_page) )
	$hook_suffix = "$plugin_page";
else if ( isset($pagenow) )
	$hook_suffix = "$pagenow";

do_action("admin_print_styles-$hook_suffix");
do_action('admin_print_styles');
do_action("admin_print_scripts-$hook_suffix");
do_action('admin_print_scripts');
do_action("admin_head-$hook_suffix");
do_action('admin_head');

get_currentuserinfo();
$thisUserLevel = $userdata->user_level;
$thisUserId = $userdata->ID;

// check for rights
if ( !is_user_logged_in() || !current_user_can('edit_posts') ) 
	wp_die(__("You are not allowed to be here"));

	$cat_type = 'link_category';
	$cat_name = 'WP People';
	$thisUserLevel = 10;
	
	$cat = is_term($cat_name, $cat_type);			
	$links = get_bookmarks("category=" . $cat['term_id']);
	
	foreach ($links as $link) {
		$addPerson = false;
		$link = sanitize_bookmark($link);
		$thisID = $link->link_id = attribute_escape($link->link_id);
		$real_name = $link->link_name = attribute_escape($link->link_name);
		$nickname = $link->link_description = attribute_escape($link->link_description);		
		$owner =  $link->link_owner = attribute_escape($link->link_owner);
		$bio = $link->link_notes = attribute_escape($link->link_notes);
		if(strlen($bio) > 150){
			$bio = substr($bio, 0 , 150) . "...";
		}
		$url = $link->link_url = attribute_escape($link->link_url );
		$image = $link->link_image = attribute_escape($link->link_image);
		
		if($thisUserLevel < 10){
			if($owner == $thisUserId){
				$addPerson = true;
			}
		} else {
			$addPerson = true;
		}
		
		if($addPerson) {		
			$itemCount++;
			if(is_odd($itemCount)){
				$bgColor = "#FFFFFF;";
			} else {
				$bgColor = "#EFEFEF;";
			}
			$tableRowString .= '<tr><td style="background-color: ' . $bgColor . '"><form name="user' . $thisID .'Form" action"#">';
			$tableRowString .= '<img src="' . $image . '" width="100" height="100" /></td>';
			$tableRowString .= '<td style="background-color: ' . $bgColor . '"><strong>' . $real_name . '</strong>';
			if(strlen($nickname) > 1){
				$tableRowString .= ' (' . $nickname . ')';
			}
			$tableRowString .= '<br/><blockquote>' . $bio . '</blockquote>';
			$tableRowString .= '</td>';
			$tableRowString .= '<td style="background-color: ' . $bgColor . ' vertical-align:center;" align="center"><input class="button" type="button" id="insert' . $thisID .'User" value="Insert" onclick="insertWPPeople(\'' . $real_name .'\');"></td></form></tr>';
		}
	} 

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title>WP People Insert</title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<?php 
		// Add only in Rich Editor mode
		if ( get_user_option('rich_editing') == 'true') { ?>
		<script language="javascript" type="text/javascript" src="<?php echo get_option('siteurl') ?>/wp-includes/js/tinymce/tiny_mce_popup.js"></script>
	<?php } ?>	
	<script language="javascript" type="text/javascript">

	function insertWPPeople(wppname) {
			var tag;
			var win = window.parent || window;
			
			if (wppname != '' ) {
				tag = " " + wppname + " ";
			} else	{
				if ( typeof win.tinyMCE !== 'undefined' && ( win.ed = win.tinyMCE.activeEditor ) && !win.ed.isHidden() ) {
					tinyMCEPopup.close();
				}  else {
					win.tb_remove();
				}
			}
			
			if ( typeof win.tinyMCE !== 'undefined' && ( win.ed = win.tinyMCE.activeEditor ) && !win.ed.isHidden() ) {
				win.ed.focus();
				if (win.tinymce.isIE) {
					win.ed.selection.moveToBookmark(win.tinymce.EditorManager.activeEditor.windowManager.bookmark);
				}
				win.ed.execCommand('mceInsertContent', false, tag);
				tinyMCEPopup.close();
			} else {
				win.edInsertContent(win.edCanvas, tag);
				win.tb_remove();
			}
			
			return;
	}
	</script>
	<base target="_self" />
</head>
<body class="<?php echo apply_filters( 'admin_body_class', '' ); ?>">
	<table class="widefat" cellspacing="0" style="margin-top: 1em;">
	  <thead>
		<tr>
			<th colspan="3">Select a Person</th>
		</tr>
	</thead>
	  <?php if(0 < $itemCount) { echo $tableRowString; } ?>
	</table>
</body>
</html>
<?php
?>