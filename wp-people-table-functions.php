<?php 
/**
 **	Check to see if the WP People table exits
 ** 	* If the table does exists
 **			* Check to see if all of the columns exist
 **				* If all the columns exists
 **					* Do a row count
 **						* If more than one row
 **							* Display Records
 **						* Else
 **							* Delete Table
 **				* Else
 **					* Add the missing columns
 **		* Else
 **			* Do nothing.  Table does not need to be installed
 **/
function wppTableCheck($thisUserLevel, $thisUserId)
{
	global $wpdb;
	$wppeople_table = $wpdb->prefix . "_people";
	$tableExists = false;
	
	$sql = "CHECK TABLE `" . $wppeople_table . "`";
	$result = mysql_query ($sql);
	$row = mysql_fetch_assoc ($result);
	if ($row['Msg_type'] == 'status' && $row['Msg_text'] == 'OK') {
		$columnCheck = wppTableColumnCheck();
		if($columnCheck !=0 ) {
			if($thisUserLevel == 10){
				wppInstallTable($columnCheck, false, $thisUserId);
			} else {
				printMessageBox("You do not have access to update the WP People table. Please inform the Blog Admin that the table needs to be updated.");
			}
		} else {
			if(wppTableCount() > 0) {
				$tableExists = true;				
			} else {
				// delete table
				if($thisUserLevel == 10){
				?>
					<table class="widefat" style="margin-top: 1em;">
					<thead>
					<tr>
						<th>WP People Update</th>
					</tr>
					</thead>
					<tbody>
					<tr><td>
					<p>The WP People table (<?php echo $wppeople_table; ?>) is empty and is no longer needed.  You can remove the table without affecting WP People.
					The WP People will get information from the XFN Links database.
					<br />Click the update button to perform the database changes now.</p>
					<form action="<?php $PHP_SELF ?>?page=wp-people" method="POST" name="addPeople">
						<input type="hidden" name="wppType" value="table" />
						<input type="submit" class="button-primary" name="wppeople_action" value="<?php _e('Delete Table', 'Delete Table') ?>" /><br/> 
					</form>
					</td></tr></table>
				<?php 
				} else {
					printMessageBox("You do not have access to update the WP People table. Please inform the Blog Admin that the table needs to be deleted.");
				}
			}
		}
	} else {
		// no table, nothing to do
		$tableExists = false;
	} 
	return $tableExists;
}
/**
 ** Check to see if all columns exist for the Word Press People table
 **		* If columns don't exist
 **			* Return true
 **		* Else
 **			* Return false
 **/
function wppTableColumnCheck()
{
	global $wpdb;
	$wppeople_table = $wpdb->prefix . "_people";
	$columnMissing = 0;
 	/* Check to see if all the table and columns are in the database
	* columnlist == the current list of columns needed in the table 
	*/
	$columnList = array ("people_ID","people_name","people_bio","people_url","people_image_url","people_image_title_nm","people_nickname","wpuser_ID");

	//check columns
	$sql2 = "SHOW COLUMNS FROM `" . $wppeople_table . "`";
	$result2 = mysql_query ($sql2);
	$row2Count = mysql_num_rows($result2);
	
	if($row2Count != 0)	{
		$columnMissing = 0;
		$tableColumnList = array();
		
		while($row2 = mysql_fetch_assoc ($result2))	{
			array_push($tableColumnList, $row2['Field']);
		}	
		
		$arrayDiff = array_diff($columnList, $tableColumnList);		
		// compares the database table column count to the array list
		if(count($arrayDiff) != 0)	{
			// checks for first added column
			$check1 = mysql_query("SHOW COLUMNS FROM " . $wppeople_table . " LIKE 'wpuser_ID'") 
				or die(mysql_error());
			// checks for second added column	
			$check2 = mysql_query("SHOW COLUMNS FROM " . $wppeople_table . " LIKE 'people_nickname'") 
				or die(mysql_error());	
			if($check1) {
				$columnMissing = 1;
				if($check2){
					$columnMissing = 3;
				}
			}  else {
				if($check2) {
					$columnMissing = 2;
				} 
			}
			
			print("columnMissing = " . $columnMissing);
		}
	} else	{
		$columnMissing = 0;
	}
	return $columnMissing;
}

function wppTableCount() {
	global $wpdb;
	$wppeople_table = $wpdb->prefix . "_people";
	//check row count for table
	$sql = "SELECT * FROM `" . $wppeople_table . "`";
	//$result = get_results($sql);
	$result = mysql_query($sql);
	$num_rows = mysql_num_rows($result);
	
	return $num_rows;
}

function wppDropTable() {
	global $wpdb;
	$wppeople_table = $wpdb->prefix . "_people";
	// Drop Table
	$sql = "DROP TABLE `" . $wppeople_table . "`";
	$wpdb->query($sql);
	
	printMessageBox("WP People Tabled deleted.  You can now just us the XFN Links database to add more WP People. <br /><code>" . $sql . "</code>");
}

function wppInstallTable($installAction, $doInstall, $thisUserId) {
	global $wpdb;
	$wppeople_table = $wpdb->prefix . "_people";
	?>
	<table class="widefat" style="margin-top: 1em;">
			<thead>
			<tr>
				<th scope="col" colspan="2">WP People Install/Update</th>
			</tr>
			</thead>
			<tbody>
		<tr><td>
	<?php
	switch($installAction){
		case 1:
			if($doInstall)
			{
				$sql = "ALTER TABLE `" . $wppeople_table . "`";
				$sql .= " ADD `wpuser_ID` BIGINT( 20 ) DEFAULT '". $thisUserId ."' NOT NULL AFTER `people_ID`";
				$wpdb->query($sql);			
				printMessageBox("Updated table " . $wppeople_table . ". Added column &quot;wpuser_ID&quot;. <br /><code>" . $sql . "</code>");
			} else {
				?>
					<p>The WP People table (<?php echo $wppeople_table; ?>) is not up-to-date.  You are missing the &quot;<strong>wpuser_ID</strong>&quot; column.
					<br />Click the update button to perform the database changes now.</p>
					<form action="<?php $PHP_SELF ?>?page=wp-people" method="POST" name="addPeople">
						<input type="hidden" name="installAction" value="1" />
						<input type="hidden" name="wppType" value="table" />
						<input type="submit" class="button-primary" name="wppeople_action" value="<?php _e('Update Table', 'install') ?>" /><br/> 
					</form> 
				<?php
			}
			break;
		case 2:
			if($doInstall)
			{
				$sql = "ALTER TABLE `" . $wppeople_table . "`";
				$sql .= "ADD `people_nickname` VARCHAR( 100 ) NOT NULL AFTER `people_name`" ;
				$wpdb->query($sql);
				printMessageBox("Updated table " . $wppeople_table . ". Added column &quot;people_nickname&quot;. <br /><code>" . $sql . "</code>");
			}
			else
			{
				?>
					<p>The WP People table (<?php echo $wppeople_table; ?>) is not up-to-date.  You are missing the &quot;<strong>people_nickname</strong>&quot; column.
					<br />Click the update button to perform the database changes now.</p>
					<form action="<?php $PHP_SELF ?>?page=wp-people" method="POST" name="addPeople">
					 	<input type="hidden" name="installAction" value="2" />
						<input type="hidden" name="wppType" value="table" />	
						<input type="submit" class="button-primary" name="wppeople_action" value="<?php _e('Update Table', 'install') ?>" /><br/> 
					</form> 
				<?php
			}
			break;
		case 3:
			if($doInstall)
			{
				$sql = "ALTER TABLE `" . $wppeople_table . "`";
				$sql .= " ADD `wpuser_ID` BIGINT( 20 ) DEFAULT '". $thisUserId ."' NOT NULL AFTER `people_ID`, ";
				$sql .= " ADD `people_nickname` VARCHAR( 100 ) NOT NULL AFTER `people_name`";
				$wpdb->query($sql);
				printMessageBox("Updated table " . $wppeople_table . ".  Added column &quot;wpuser_ID&quot;. Added column &quot;people_nickname&quot;. <br /><code>" . $sql . "</code>");
			} else {
				?>
				<p>The WP People table (<?php echo $wppeople_table; ?>) is not up-to-date.  You are missing the &quot;<strong>wpuser_ID</strong>&quot; column.				
				<p>The WP People table (<?php echo $wppeople_table; ?>) is not up-to-date.  You are missing the &quot;<strong>people_nickname</strong>&quot; column.
				<br />Click the update button to perform the database changes now.</p>
				<form action="<?php $PHP_SELF ?>?page=wp-people" method="POST" name="addPeople">
					<input type="hidden" name="installAction" value="3" />
					<input type="hidden" name="wppType" value="table" />	
					<input type="submit" class="button-primary" name="wppeople_action" value="<?php _e('Update Table', 'install') ?>" /><br/> 
				</form> 
			<?php
			}
			break;
		case 4:
			// obsolete, should never ask to install table
			$sql = "CREATE TABLE `" . $wppeople_table . "` (
			  `people_ID` int(11) NOT NULL auto_increment,
			  `wpuser_ID` bigint(20) NOT NULL default '1',
			  `people_name` varchar(160) NOT NULL default '',
			  `people_nickname` varchar(100) NOT NULL default '',
			  `people_bio` text NOT NULL,
			  `people_url` varchar(255) default NULL,
			  `people_image_url` varchar(255) default NULL,
			  `people_image_title_nm` varchar(160) default NULL,
			  PRIMARY KEY  (`people_ID`)
			) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
			//require_once(ABSPATH . 'wp-admin/upgrade-functions.php');
			//dbDelta($sql);
			
			printMessageBox("Table " . $wppeople_table . " code. <br /><code>" . $sql . "</code>");
			break;
	}
	?>
	</td></tr>
	</tbody>
	</table>
	<?php	
}

/* This creates the edit form */
function printEditBox($thisId, $thisName, $thisNickname, $thisBio, $thisUrl, $thisPhoto, $thisPhotoTitle)
{
	global $blogSiteURL;
	?>
	<form name="editBio" action="<?php echo $PHP_SELF; ?>?page=wp-people" method=POST>
		<input type="hidden" name="action" value="save">
		<input type="hidden" name="wppType" value="table" />
		<input type="hidden" name="wppeople_id" value="<?php echo $thisId; ?>">
		<table class="widefat" style="margin-top: 1em;">
			<thead>
			<tr>
				<th scope="col" colspan="3">View WP People</th>
			</tr>
			</thead>
			<tbody>
		<tr>
			<td colspan="3"><p>Use this form to view the user's name and details.<br>
			If you have moved this person to the <a href="<?php echo $blogSiteURL; ?>/wp-admin/link-manager.php" /> Links XFN table</a>,
			then use the delete button to remove the person from your listing.</p></td>
		</tr>
		<tr>
			<td width="110" align="right" class="labels">Name:</td>
			<td colspan="2"><input type="text" name="wpp_name" value="<?php echo $thisName; ?>" size="40"></td>
		</tr>
		<tr>
			<td width="110" align="right" class="labels">Nickname:</td>
			<td colspan="2"><input type="text" name="wpp_nickname" value="<?php echo $thisNickname; ?>" size="40"></td>
		</tr>
		<tr>
			<td align="right" valign="top"><span class="labels">Bio:</span> </td>
			<td><textarea name="wpp_bio" rows=15 cols=50><?php echo $thisBio; ?></textarea></td>
			<td width="78" rowspan="5" align="center" valign="top"><?php    
				if (!$thisPhoto){
					$thisPhoto = "/images/nophoto.jpg";
					$thisPhotoTitle = "no photo";
				}
				?>
			  <span class="labels" style="font-size: 8pt;">(100 x 100)</span><br/>
				<div class="shadow">
				<img src="<?php echo $thisPhoto ?>" title="<?php echo $thisPhotoTitle ?>" alt="<?php echo $thisPhotoTitle ?>" width="100" height="100" />
				<span class="labels" style="font-size: 8pt;"><?php echo $thisPhotoTitle ?></span>
				</div>
		  </td>
		</tr>
		<tr>
			<td align="right" class="labels">URL:</td>
			<td width="441"><input type="text" name="wpp_url" value="<?php echo $thisUrl; ?>" size="50"></td>
		  </tr> 
		<tr>
			<td width="110" align="right" class="labels">Photo:</td>
			<td><input type="text" name="wpp_img_url" value="<?php echo $thisPhoto; ?>" size="40"></td>
		</tr>
		<tr>
			<td width="110" align="right" class="labels">Photo Title:</td>
			<td><input type="text" name="wpp_img_title" value="<?php echo $thisPhotoTitle; ?>" size="40"></td>
		</tr>
		<tr>
			<td align="center">&nbsp;</td>
			<td align="center">&nbsp;</td>
		</tr>
		<tr>
			<td align="center">&nbsp;</td>
			<td align="center">
			<input type="submit" class="button" name="wppeople_action" value="<?php _e('Back', 'default') ?>" />
			<input type="submit" class="button-primary" name="wppeople_action" value="<?php _e('Copy', 'copy') ?>" />
			<input type="submit" class="button" name="wppeople_action" value="<?php _e('Delete', 'delete') ?>" onclick="if (confirm ('Are you sure you want to delete person \'' + editPeople.id.options[editPeople.id.selectedIndex].text + '\'?'))"></td>
		</tr>
		</table>
	</form>
<?
}

function printTablePeopleList($thisUserId, $thisUserLevel) {
	global $wpdb;
	$wppeople_table = $wpdb->prefix . "_people";
	$dropListString;
	$itemCount = 0;
	$viewList = false;
	
	$viewList = wppTableCheck($thisUserLevel, $thisUserId);
	
	if($viewList) {
		$sql = "SELECT `people_ID`, `people_name` 
				FROM `" . $wppeople_table . "`";
		if($thisUserLevel != 10) {
			$sql .= " WHERE `wpuser_ID` = " . $thisUserId;
		}
		$sql .= " ORDER BY `people_name`;";
		
		$result = $wpdb->get_results($sql, ARRAY_A);
		if($result)	{
			foreach($result as $resultItem)	{
				$itemCount++;
				$dropListString .= '<option value="' . $resultItem['people_ID'] . '">' . $resultItem['people_name'] . '</option>'; 
			}
		}		
			// create edit list ?>
			<table class="widefat" width="95%" cellspacing="0" style="margin-top: 1em;">
			<thead>
				<tr>
					<th scope="col" colspan="2">WP People List (WP People Table)</th>
				</tr>
			</thead>
			<tr><td>
			<?php
			if($thisUserLevel == 10){
				?> <em>As an administrator you see all WP People table records.</em><br /><br /> <?php
			}
			?>
			There are currently <strong><?php echo wppTableCount(); ?></strong>&nbsp; in the WP People table.<br />
			Please select a person's bio to view or remove:<br /><br /> 
			<form action="<?php echo $PHP_SELF ?>?page=wp-people"  method="POST" name="editPeople">
			<input type="hidden" name="wppType" value="table" />
			<select name="wppeople_id">
			  <?php 
				if (0 < $itemCount)	{
				  echo $dropListString; 
				 }
				?> 
			</select>
			<input type="submit" class="button-primary" name="wppeople_action" value="<?php _e('View', 'view') ?>">
			<input type="submit" class="button" name="wppeople_action" value="<?php _e('Delete', 'delete') ?>" onclick="if (confirm ('Are you sure you want to delete this person?'))">			 
			</form>
		</td></tr>
		</tbody>
		</table>
		<?php 
	}
}	

// function used to view WP People, edit function not used
function wppEditPeople($thisPeopleId) {
	global $wpdb;
	$wppeople_table = $wpdb->prefix . "_people";
	$sql = "SELECT `people_name`, `people_nickname`, `people_bio`, 
			`people_url`, `people_image_url`, `people_image_title_nm`  
			FROM `" . $wppeople_table . "` 
			WHERE `people_ID` =". $thisPeopleId . ";";
	$result = $wpdb->get_results($sql, ARRAY_A);
	if($result)	{
		foreach($result as $resultItem)	{
			printEditBox($thisPeopleId, $resultItem['people_name'], $resultItem['people_nickname'], $resultItem['people_bio'], $resultItem['people_url'], $resultItem['people_image_url'], $resultItem['people_image_title_nm']);
		}
	} else {
		printMessageBox("No record found for id " . $thisPeopleId . " in table " . $wppeople_table);
	}
}

// function  copies
function wppCopyPeople($thisPeopleId, $thisUserId) {
	global $wpdb, $blogSiteURL;
	
	$cat_type = 'link_category';
	$cat_name = 'WP People';
	$blogSiteURL = get_bloginfo('wpurl');
	
	$wppeople_table = $wpdb->prefix . "_people";
	$cat = is_term($cat_name, $cat_type);
  	$sql = "SELECT `people_name`, `people_nickname`, `people_bio`, 
			`people_url`, `people_image_url`, `people_image_title_nm`  
			FROM `" . $wppeople_table . "` 
			WHERE `people_ID` =". $thisPeopleId . ";";
	$result = $wpdb->get_results($sql, ARRAY_A);	
  	if($result)	{
		foreach($result as $resultItem)	{
			$link_name = $resultItem['people_name'];
			$link_description = $resultItem['people_nickname'];
			$link_notes = $resultItem['people_bio'];
			$link_url = $resultItem['people_url'];
			$link_image = $resultItem['people_image_url'];
			$link_visible = "Y";
			$link_target = "_parent";	
		}
		$link_owner = $thisUserId;
		
		$wpdb->query( $wpdb->prepare("INSERT INTO $wpdb->links (link_url, link_name, link_image, link_target, link_description, link_visible, link_owner, link_rating, link_rel, link_notes, link_rss) VALUES(%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)",
			$link_url,$link_name, $link_image, $link_target, $link_description, $link_visible, $link_owner, $link_rating, $link_rel, $link_notes, $link_rss) );
		$link_id = (int) $wpdb->insert_id;
	
 	  	$cats = array($cat['term_id']);

		wp_set_link_cats($wpdb->insert_id, $cats);
	  	printMessageBox("WP People record copied into XFN links.");
	}
}

// function to delete a person from the WP People table
function wppDeletePeople($thisPeopleId)	{
	global $wpdb; 
	
	$wppeople_table = $wpdb->prefix . "_people";
	
	$sql = "DELETE FROM `" . $wppeople_table . "` " 
		   . " WHERE `people_ID` = ". $thisPeopleId . ";";
	$wpdb->query($sql);
	printMessageBox("WP Person deleted.");
}

?>

