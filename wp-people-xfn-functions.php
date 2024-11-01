<?php
// Handles error message output
function printMessageBox($messageString)
{ 
	?>
		<div id="messageBox" class="error"><strong><?php echo $messageString; ?></strong></div>
	<?php
}

// function Displays XFN data for WP People based on ID selected
function wppEditXFNPeople($form_wpp_id, $iUserId, $thisAction) {
	if($thisAction == 'EDIT'){
		$links = get_bookmark($form_wpp_id, ARRAY_A);	
		if($links)	{
			$links = sanitize_bookmark($links);
			$real_name = $links['link_name'];
			$nickname = $links['link_description'];
			$bio = $links['link_notes'];
			$url = $links['link_url'];
			$image = $links['link_image'];
			
			printEditXFNBox($form_wpp_id, $real_name, $nickname, $bio, $url, $image, $thisAction);
		} else {
			printMessageBox("No record found for id " . $thisPeopleId . " in table the XFN Links database");
		}
	} else {
		printEditXFNBox(0, 'New WP People Name', '', 'Enter a description of the person here.', '', '', $thisAction);
	}
}

// function Deletes XFN data based on ID selected
function wppDeleteXFNPeople($form_wwp_id, $iUserId) {
	global $wpdb;

	$sql = "DELETE FROM $wpdb->links WHERE link_id = " . $form_wwp_id;
	$wpdb->query($sql);
	printMessageBox("WP Person deleted from XFN Links database.");
}

// function Updates XFN data based on ID selected
function wppUpdateXFNPeople($iUserId, $thisId, $thisName, $thisNickname, $thisBio, $thisUrl, $thisPhoto, $actionType, $iCatName, $iCatType){
	global $wpdb;
	
	if($actionType == 'ADD'){
		$link_owner = $thisUserId;
		$sql = "INSERT INTO $wpdb->links (link_url, link_name, link_image, link_description, link_visible, link_owner, link_notes)" .
			" VALUES('" . $thisUrl . "','" . $thisName . "','" . $thisPhoto . "','" . $thisNickname . "','Y'," . $iUserId . ",'" . $thisBio . "')";
		print_r($sql);
		
		$wpdb->query( $sql );
		$link_id = (int) $wpdb->insert_id;
	
		$cat = is_term($iCatName, $iCatType);
		$cats = array($cat['term_id']);
		wp_set_link_cats($wpdb->insert_id, $cats);
		printMessageBox("WP Person added to XFN Links database.");
	} else {
		$sql = "UPDATE $wpdb->links SET link_name = '" . $thisName 
			. "', link_description = '" .  $thisNickname
			. "', link_url = '" . $thisUrl
			. "', link_notes = '" . $thisBio
			. "', link_image = '" . $thisPhoto
			. "' WHERE link_id = " . $thisId;		
		$wpdb->query($sql);
		printMessageBox("WP Person update in XFN Links database.");
	}
}

/* This creates the edit form */
function printEditXFNBox($thisId, $thisName, $thisNickname, $thisBio, $thisUrl, $thisPhoto, $actionType) {
	global $blogSiteURL;
	?>
	<form name="wppeopleForm" class="wppeopleForm" action="<?php echo $PHP_SELF; ?>?page=wp-people" method=POST>
		<input type="hidden" name="wppType" value="xfn" />
		<input type="hidden" name="fWPPeopleId" value="<?php echo $thisId; ?>" />

			<h2><?php echo $actionType; ?> WP People</h2>
			<p>Use this form to update the user's name and details.	
			Only the fields used in WP People are show on this form.</p>
			<ul>
			<li><label for="fWPPname">Name :</label>
			<input type="text" id="fWPPname" name="fWPPname" value="<?php echo $thisName; ?>" size="40"></li>
			<?php    
				if (!$thisPhoto){
					$thisPhoto = "/images/nophoto.jpg";
					$thisPhotoTitle = "no photo";
				}
				?>
			<li>
			<label for="fWPPnickname">Nickname :</label>
			<input id="fWPPnickname" name="fWPPnickname" type="text" value="<?php echo $thisNickname; ?>" size="40"></li>
			<li><label for="fWPPurl">URL :</label>
			<input id="fWPPurl" name="fWPPurl" type="text" value="<?php echo $thisUrl; ?>" size="50" ></li>
			<li>
			<img src="<?php echo $thisPhoto ?>" title="<?php echo $thisName ?>" alt="<?php echo $thisName ?>" align="right" width="100" height="100" />
			<label for="fWPPimgUrl">Photo(100 x 100) :</label>
			<input id="fWPPimgUrl" name="fWPPimgUrl" type="text"  value="<?php echo $thisPhoto; ?>" size="40"></li>
			<li><label for="fWPPbio">Bio :</label>
			<textarea id="fWPPbio" name="fWPPbio" rows=15 cols=50><?php echo $thisBio; ?></textarea></li>
			<li>
			<?php
				if($actionType == 'ADD'){
					?>
						<input type="submit" class="button-primary" name="wppeople_action" value="<?php _e('Add Person', 'Add Person') ?>" />
						<input type="submit" class="button" name="wppeople_action" value="<?php _e('Back', 'default') ?>" /> 
					<?php
				} else {
					?>
						<input type="submit" class="button-primary" name="wppeople_action" value="<?php _e('Update Person', 'Update Person') ?>" />
						<input type="submit" class="button" name="wppeople_action" value="<?php _e('Delete Person', 'Delete Person') ?>" onclick="if (confirm ('Are you sure you want to delete person \'' + editPeople.id.options[editPeople.id.selectedIndex].text + '\'?'))">
						<input type="submit" class="button" name="wppeople_action" value="<?php _e('Back', 'default') ?>" />
					<?php
				}
			?></li>
			</ul>

	</form>
<?
}

function printXFNPeopleList($iUserId, $iUserLevel, $iCatName, $iCatType) {
	global $wpdb, $blogSiteURL;			
	$args = array(
    	'orderby'        => 'name', 
		'order'          => 'ASC',
		'limit'          => -1, 
		'category_name'  => $iCatName,);
	
	$links = get_bookmarks($args);
	foreach ($links as $link) {
		$link = sanitize_bookmark($link);
		$thisID = $link->link_id = attribute_escape($link->link_id);
		$real_name = $link->link_name = attribute_escape($link->link_name);
		$nickname = $link->link_description = attribute_escape($link->link_description);		
		$owner =  $link->link_owner = attribute_escape($link->link_owner);
		if($iUserLevel < 10){
			if($owner == $iUserId){
				$itemCount++;
				$dropListString .= '<option value="' . $thisID . '">' . $real_name;
				if(strlen($nickname) > 1){
			 	$dropListString .= ' (' . $nickname . ')';
			 }
			 $dropListString .= '</option> ';
			}
		} else {
			$itemCount++;
			$dropListString .= '<option value="' . $thisID . '">' . $real_name;
			if(strlen($nickname) > 1){
			 	$dropListString .= ' (' . $nickname . ')';
			 }
			 $dropListString .= '</option> ';
		}
	} 
	
	?>

	<h2>WP People List (XFN Table)</h2>
	<?php if($thisUserLevel == 10) {
		?> <em>As an administrator you see all WP People XFN records.</em> <br /><br /> <?php
	} ?>
	Select a Person to view their XFN information.<br />  
	Fields will be limited to those valid for WP People.<br /><br /> 
	<form name="wppeopleEdit" class="wppeopleForm" action="<?php echo $PHP_SELF; ?>?page=wp-people" method=POST>
		<input type="hidden" name="wppType" value="xfn" />
		<input type="hidden" name="action" value="view">
		<ul><li><label for="fWPPeopleId">WP Person :</label>
		<select id="fWPPeopleId" name="fWPPeopleId">
			<?php if(0 < $itemCount) { echo $dropListString; } ?>
		</select></li>
		<li><input type="submit" class="button-primary" name="wppeople_action"  value="<?php _e('Edit Person', 'Edit Person') ?>" />
		<input type="submit" class="button" name="wppeople_action"  value="<?php _e('Add New Person', 'Add New Person') ?>" /></li>
		</ul>
	</form>
	</td></tr>
	<?php
}
function printWPPeopleOptions($iUserId, $iUserLevel, $iPreNameText, $iPluginUrl, $iDbPluginVersion) {
	?>
	<thead>
	<tr>
		<th scope="col" colspan="2" style="background: url(<?php echo $iPluginUrl; ?>/blue-grad.png);">WP People Options and Key</th>
	</tr>
	</thead>
	<tr>
		<td>
	<?php
	if($iUserLevel == 10) {
		?> <p>As an administrator you can determine WP People Options.</p>
		<form name="wppeopleOptions"  class="wppeopleForm"action="<?php echo $PHP_SELF; ?>?page=wp-people" method=POST>
			<input type="hidden" name="wppType" value="xfn" />
			<ul>
				<li><label for="fPreNameText">Pre Name Text :</label>
				<input type="text" name="fPreNameText" id="fPreNameText" value="<?php echo $iPreNameText; ?>" /></li>
				<li><input type="submit" class="button-primary" name="wppeople_action"  value="<?php _e('Update Options', 'Update Options') ?>" /></li>
			</ul>
		</form>
		<?php switch($iDbPluginVersion) {
			case '0': ?>
				<hr size="1" width="70%" noshade />
				<p>This plugin has new features. Click the "Update Plugin" button to store the plugin version into the WordPress database.</p>
				<form name="wppeopleOptions"  class="wppeopleForm"action="<?php echo $PHP_SELF; ?>?page=wp-people" method=POST>
					<input type="hidden" name="wppType" value="xfn" />
					<input type="hidden" name="fPluginVersion" value="<?php echo $iDbPluginVersion; ?>" />
					<input type="submit" class="button-primary" name="wppeople_action"  value="<?php _e('Update Plugin', 'Update Plugin') ?>" />
				</form>
			<?php break;
		}
	} else { ?>
			<em>There are no administration options to change.</em>
		<?php }
	?>
		</td>
		<td>The field on the Links form match up the following way: <br />
			 <ul>
			 <li><strong>Name</strong> is the <strong>Real Name (searched name)</strong> in WP People</li>
			 <li><strong>Description</strong> is the <strong>Nick Name (displayed name)</strong> in WP People</li>
			 <li><strong>Web Address</strong> is the <strong>Link</strong> in WP People</li>
			 <li><strong><em>Advanced</em> Notes</strong> is the <strong>Description/Bio</strong> in WP People</li>
			 <li><strong><em>Advanced</em> Image Link</strong> is the <strong>Photo</strong> in WP People</li>
			 </ul>
		</td>
	</tr>
	<?php	
}
 ?>
