<?php
/** 
  	This is the pop-up window script.  It selects a row from the wp_people table
     based on people_ID.  The data is displayed in an HTML table. 
**/ 
/**  Copyright 2006-2009  Dean Logan  (email : wp-dev@dean-logan.com)

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

To view a copy of the GNU General Public License
go to <http://www.gnu.org/licenses/>.
**/

	/* Don't remove this line, it calls the Wordpress function files ! 
	// Since the document doesn't have a connection to the WordPress functions, 
	// we have to find the blog root manually to get the header
	*/
	define('WP_USE_THEMES', false);
	
	
	$uriPieces = explode("/",$_SERVER['PHP_SELF']);
	$key = array_search('wp-content', $uriPieces);
	$blogRoot;  
	if($key > 0)
	{
		$blogRoot = "/" . $uriPieces[$key - 1];
	}
	else
	{
		$blogRoot = "/" . $uriPieces[$key];
	}
	$blogHeader = $blogRoot . "/wp-blog-header.php";
	
	require($_SERVER['DOCUMENT_ROOT'] . $blogHeader);
	$templateDirectory = bloginfo('template_directory ');
	$defaultImage = $blogRoot . $templateDirectory  . "/images/nophoto.jpg";
	
	$person = $_GET['person'];
	global $wpdb;
	$wplinks_table = $wpdb->prefix . "links";
	
	 //calls row from database that matches ID passed in through URL
	$request = "SELECT `link_name` , `link_url` , `link_image` , `link_description` , `link_notes`
				FROM " . $wplinks_table . "
				WHERE `link_id` = $person";
	
	 $personResults = $wpdb->get_results($request);
	
	 foreach ($personResults as $personResults)
	 {
		$bio = $personResults->link_notes;
		$real_name = $personResults->link_name;
		$nick_name = $personResults->link_description;
		$url = $personResults->link_url;
		$photo = $personResults->link_image;
	  }
		  
   //replace patters
   $patterns[0] = "/%blogname%/";
   $patterns[1] = "/%real_name%/";
   $patterns[2] = "/%nick_name%/";
   $patterns[3] = "/%siteName%/";

   $replacements[0] = $blogname;
   $replacements[1] = $real_name;
   $replacements[2] = $nick_name;
   $replacements[3] = $siteName;

print_r('<link rel="stylesheet" href="http://' . $_SERVER['SERVER_NAME'] . '/' . $blogRoot . '/wp-content/plugins/wp-people/wp-people-css.css" type="text/css" media="screen" />');
?>
<div id="wpPeopleContent">
	<!--// display the person's name //-->
	<div class="personName"><?php echo $real_name . "&nbsp;(" . $nick_name . ")" ?></div>
	<?php 
		/* displays a photo.  If no photo has been entered into the database, 
		then the default "nophoto.jpg" photo is displayed. 
		*** Make sure that the "nophoto.jp" is stored in the root images directory or
		change the directory information below. ***
		*/
		if (!$photo)
		{
			$photo = $defaultImage;
			$photoTitle = "no photo";
		}
		
	?>
	<div id="personPhoto">
		<img src="<?php echo $photo; ?>" alt="<?php echo $photoTitle; ?>" class="shadow" width="100" height="100" />
	</div>
	<div id="personBio"><div class="labels">bio :</div>
		<?php 
			$bio = peopleDefine($bio);
			$displayText = apply_filters('the_content', $bio);
			echo $displayText;	
		?>
	</div>	
	<?php
		/* Displays the person's URL if entered into the database. */
		if ($url)
		{
			?>
			<div id="personUrl"><div class="labels">url :</div>
			<a href="<?php echo $url; ?>" title="<?php echo $real_name; ?>'s web site" target="_blank"><?php echo $url; ?></a> 
			</div>
			<?php
		} 
	?> 
</div>