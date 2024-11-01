<?php 
/*
Plugin Name: WP People
Plugin URI: http://www.dean-logan.com/plugins-and-widgets/
Description:  This filter finds names from the XFN Links database and creates a link to learn more information about the person. You can view the people selected through the <a href="tools.php?page=wp-people">WP People</a> link in the <a href="tools.php">Tools</a> area.
Version: 3.4.1
Author: Dean Logan
Author URI: http://www.dean-logan.com
*/
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
if (!class_exists('WPPeople')) {
	class WPPeople {
		var $plugin_version = '3.4.1';
		var $dbPluginVersion = '0';
		var $folder = '/wp-content/plugins/wp-people'; // You shouldn't need to change this ;)
		var $plugin_name = "WPPeople";
		var $plugin_url;
		var $wpversion;
		var $preNameText = 'WP People bio for';
		var $cat_type = 'link_category';
		var $cat_name = 'WP People';
		var $blogSiteURL;
		var $thisUserId;
		var $thisUserLevel;
		
		// Don't start this plugin until all other plugins have started up
		function WPPeople() {
			add_action('plugins_loaded', array(&$this, 'Initalization'));
		}
		
		// Initialization stuff
		function Initalization() {
			global $userdata;

			// define URL
			$this->blogSiteURL = get_bloginfo('wpurl');
			$this->plugin_url = plugins_url( '', __FILE__ );
			// define user id and level
			get_currentuserinfo();
			$this->thisUserLevel = $userdata->user_level;
			$this->thisUserId = $userdata->ID;
			
			add_thickbox();
			
			// Check to see if the WP People link category exists.
			$this->checkLinkCategory();
			
			// add button
			// Modify the version when tinyMCE plugins are changed.
			add_filter('tiny_mce_version', array (&$this, 'change_tinymce_version') );
		
			// init process for button control
			add_action('init', array (&$this, 'addbuttons') );
			
			// Figure out the WordPress version
			global $wp_db_version;
			if ( $wp_db_version > 6124 ) // add_meta_box() isn't defined at this point, so db_version works well here
				$this->wpversion = 2.5;
			elseif ( class_exists('WP_Scripts') )
				$this->wpversion = 2.1;
			else
				$this->wpversion = 2.0;
					
			# Register our hooks and filter
			add_action('admin_menu', array( &$this, 'mt_add_pages'));
			add_filter('the_content', array( &$this, 'peopleDefine'));
			
			// Add Quicktag
			if (current_user_can('edit_posts') || current_user_can('edit_pages') ) {
				add_action( 'edit_form_advanced', array(&$this, 'add_quicktags') );
				add_action( 'edit_page_form', array(&$this, 'add_quicktags') );
			}
	
			// Queue Embed JS
			add_action( 'admin_head', array(&$this, 'set_admin_js_vars'));
			add_action('admin_head', array( &$this, 'admin_css' ) );
			wp_enqueue_script( 'wppinsertpeople', plugins_url('/wp-people/js/wpp.js'), array(), $this->version );
			
			// Get Option Values
			if ( strlen(get_option("{$this->plugin_name}preNameText")) > 1 ) {
				$this->preNameText = get_option( "{$this->plugin_name}preNameText" );
			} else {
				add_option("{$this->plugin_name}preNameText", $this->preNameText);
				$this->preNameText = get_option( "{$this->plugin_name}preNameText" );
			}
			if ( strlen(get_option("{$this->plugin_name}cat_name")) > 1 ) {
				$this->cat_name = get_option( "{$this->plugin_name}cat_name" );
			} else {
				add_option("{$this->plugin_name}cat_name", $this->cat_name);
				$this->cat_name = get_option( "{$this->plugin_name}cat_name" );
			}
			if ( strlen(get_option("{$this->plugin_name}plugin_url")) > 1 ) {
				$this->plugin_url = get_option( "{$this->plugin_name}plugin_url" );
			} else {
				add_option("{$this->plugin_name}plugin_url", $this->plugin_url);
				$this->plugin_url = get_option( "{$this->plugin_name}plugin_url" );
			}
			$this->dbPluginVersion = get_option("{$this->plugin_name}plugin_version");		
		}

		// Make our buttons on the write screens
		function addbuttons(){
			// Don't bother doing this stuff if the current user lacks permissions
			if ( !current_user_can('edit_posts') && !current_user_can('edit_pages') ) return;
			
			// Add only in Rich Editor mode
			if ( get_user_option('rich_editing') == 'true') {
				// add the button for wp2.5 in a new way
				add_filter("mce_external_plugins", array (&$this, "add_tinymce_plugin" ), 5);
				add_filter('mce_buttons', array (&$this, 'register_button' ), 5);
			}
		}

		function set_admin_js_vars()
		{
			?>
			<script type="text/javascript" charset="utf-8">
			// <![CDATA[
				if (typeof WPPeople !== 'undefined' && typeof WPPeople.Insert !== 'undefined') {
					WPPeople.Insert.configUrl = "<?php echo plugins_url('/wp-people/tinymce/window.php'); ?>";
				}
			// ]]>	
			</script>
			<?php
		}

		// Add a button to the quicktag view
		function add_quicktags()
		{
			$buttonshtml = '<input type="button" class="ed_button" onclick="WPPeople.Insert.embed.apply(WPPeople.Insert); return false;" title="Insert WP People" value="Insert WP People" />';
			?>
			<script type="text/javascript" charset="utf-8">
			// <![CDATA[
				(function(){
					
					if (typeof jQuery === 'undefined') {
						return;
					}
					
					jQuery(document).ready(function(){
						// Add the buttons to the HTML view
						jQuery("#ed_toolbar").append('<?php echo $buttonshtml; ?>');
					});
				}());
			// ]]>
			</script>
		<?php	
		}

		// used to insert button in wordpress 2.5x editor
		function register_button($buttons) {
			array_push($buttons, "separator", $this->plugin_name );
			return $buttons;
		}
	
		// Load the TinyMCE plugin : editor_plugin.js (wp2.5)
		function add_tinymce_plugin($plugin_array) {    
			$plugin_array[$this->plugin_name] =  wppeople_URLPATH . 'tinymce/editor_plugin.js';
			return $plugin_array;
		}
	
		function change_tinymce_version($version) {
			return ++$version;
		}	
		
		/**
		 ** Check to see if the Link Category of WP People exists
		 ** 	* If exists
		 **			* Do nothing
		 **		* Else
		 **			* Add to link categories
		 **/
		function checkLinkCategory() {
			// , $cat_type, $cat_name
			global $wpdb;
			$wpterm_taxonomy_table = $wpdb->prefix . "wp_term_relationships";
			$wpterms_table = $wpdb->prefix . "wp_terms";
			$cat = is_term($this->cat_name, $this->cat_type);
		
			if($cat['term_id'] == null) {
				$catArgs = array('name' => $this->cat_name, 'slug' => 'wppeople',  'parent' => '', 'description' => 'Tag for adding people to the WP People list.');
				wp_insert_term($this->cat_name , $this->cat_type, $catArgs);
				//print("Created the WP People link category");
			}
			
			return true;
		}

		/*-----------------------------------------------------
			This function is called to create the header information on the admin page
		-----------------------------------------------------*/
		function headerBox()
		{
			global $blogSiteURL;
			?>
			
			<div id="WPPeopleAdmin" class="wrap">
			<table class="widefat" cellspacing="0" style="margin-top: 1em;">
				<thead>
					<tr>
						<th colspan="2"><div id="icon-tools" class="icon32"></div><h1>WP People Administration</h1></th>
					</tr>
				</thead>
				<tbody>
				 <tr>
					<td colspan="2"><p>WP People uses the <a href="<?php echo $this->blogSiteURL; ?>/wp-admin/link-manager.php" /> Links XFN table</a>. <br />
					 It will search the table for any names that have the WP People links category. <br />
					 To add a person to the WP People you just add the <strong><em>WP People</em></strong> link <em>cateogry</em> to their Link.</p>
					 </td>
				</tr>
			 <?php
		}

		/*-----------------------------------------------------
			This function is called to create the footer information on the admin page
		-----------------------------------------------------*/
		function footerBox()
		{
			global $blogSiteURL, $wpdb;
				
				printWPPeopleOptions($this->thisUserId, $this->thisUserLevel, $this->preNameText, $this->plugin_url, $this->dbPluginVersion); ?>
				<thead>
				<tr>
					<th scope="col" colspan="2">Thank you for using my plugin!</th>
				</tr>
				</thead>
				<tr>
					<td>
					<p><img src="<?php echo $this->plugin_url; ?>/deanlogic.png" alt="Dean Logic" align="right" />
					You can view my other plugins through my WordPress Plugin Database <a href="http://wordpress.org/extend/plugins/profile/logansix">profile</a>.<br />
					You can also view what else I am working on at <a href="http://www.dean-logan.com/">my website</a><br /> and make comments about the plugins in <a href="http://www.dean-logan.com/phpBB3/">my forum</a>.
					<br />Thank you for your support!</p>
					<br />Dean <em>a.k.a LoganSix</em>
					</td>
					<td><ul>
							<li>Plugin Name: <?php echo $this->plugin_name; ?></li>
							<li>Plugin Version: <?php echo $this->plugin_version; ?></li>
							<li>Plugin Directory: <?php echo $this->plugin_url; ?></li>
							<li>Plugin Database Version: <?php echo $this->dbPluginVersion; ?></li>
					</ul></td>
				<tr>
				</tbody>
			</table>
			</div>
			 <?php
		}
		
		function printListBox($iUserId, $iUserLevel) {
			printXFNPeopleList($iUserId, $iUserLevel, $this->cat_name, $this->cat_type); 
			printTablePeopleList($iUserId, $iUserLevel); 
		}					
							
		function mt_add_pages()
		{
			add_management_page('WP People', 'WP People', 'publish_posts', 'wp-people', array(&$this, 'wp_people_admin'));
		}
		
		function admin_css(){
			print_r('<link rel="stylesheet" type="text/css" href="' . $this->plugin_url . '/wp-people-css.css" />');
		}

		function wp_people_admin()
		{
			//$plugin_name , $cat_type, $cat_name, $preNameText
			global $PHP_SELF;
			global $userdata;
			global $wpdb;
			$wppeople_table = $wpdb->prefix . "_people";

			include_once("wp-people-table-functions.php");
			include_once("wp-people-xfn-functions.php");
			
			$this->headerBox();
			?>
			<tr>
					<td valign="top" colspan="2">
			<?php
			if(isset($_POST['wppeople_action'])) {
				$wppaction = $_POST['wppeople_action'];
				//print_r("wppaction = $wppaction");
				if($_POST['wppType'] == 'xfn'){
					switch($wppaction) {
						case "Add New Person":
							wppEditXFNPeople(0, $thisUserId, 'ADD');
							break;
						case "Add Person":
							wppUpdateXFNPeople($this->thisUserId, 0, $_POST['fWPPname'], $_POST['fWPPnickname'], $_POST['fWPPbio'], $_POST['fWPPurl'], $_POST['fWPPimgUrl'], 'ADD', $this->cat_name, $this->cat_type);
							$this->printListBox($this->thisUserId, $this->thisUserLevel);
							break;
						case "Edit Person":
							$form_wpp_id = $_POST['fWPPeopleId'];
							wppEditXFNPeople($form_wpp_id, $this->thisUserId, 'EDIT');
							break;
						case "Update Person":
							$form_wpp_id = $_POST['fWPPeopleId'];
							wppUpdateXFNPeople($this->thisUserId, $_POST['fWPPeopleId'], $_POST['fWPPname'], $_POST['fWPPnickname'], $_POST['fWPPbio'], $_POST['fWPPurl'], $_POST['fWPPimgUrl'], 'EDIT', $this->cat_name, $this->cat_type);
							$this->printListBox($this->thisUserId, $this->thisUserLevel);
							break;
						case "Delete Person":
							$form_wpp_id = $_POST['fWPPeopleId'];
							wppDeleteXFNPeople($form_wpp_id, $this->thisUserId);
							$this->printListBox($this->thisUserId, $this->thisUserLevel);
							break;
						case "Update Plugin":
							switch($_POST['fPluginVersion']){
								case '0' :
									if ( strlen(get_option("{$this->plugin_name}plugin_version")) > 1) {
										delete_option("{$this->plugin_name}plugin_version");
										add_option("{$this->plugin_name}plugin_version", $this->plugin_version);
										$this->dbPluginVersion = get_option( "{$this->plugin_name}plugin_version" );
									} else {
										update_option("{$this->plugin_name}plugin_version", $this->plugin_version);
										$this->dbPluginVersion = get_option( "{$this->plugin_name}plugin_version" );
									}
									print_r('<code>Plugin Update to version 3.4.0</code>');
									break;
								default :
									print_r('<code>No plugin update required.</code>');
									break; 
							}
							break;
							$this->printListBox($this->thisUserId, $this->thisUserLevel);
						case "Update Options":
							$newPreNameText = $_POST['fPreNameText'];
							print_r('<code>Updating Option ' . $this->plugin_name . 'preNameText to "' . $newPreNameText . '"</code>');
							if ( get_option("{$this->plugin_name}preNameText") ) {
								update_option("{$this->plugin_name}preNameText", $newPreNameText);
							} else {
								add_option("{$this->plugin_name}preNameText", $newPreNameText);
							}
							$this->preNameText = get_option( "{$this->plugin_name}preNameText" );
							print_r('<br /><code>Update to "' . $this->preNameText . '"</code>');
							$this->printListBox($this->thisUserId, $this->thisUserLevel);
							break;
						default :
							$this->printListBox($this->thisUserId, $this->thisUserLevel);
							break;
					}
				} else {
					switch($wppaction)
					{
						case "Delete Table" :
							wppDropTable();
							$this->printListBox($this->thisUserId, $this->thisUserLevel);
							break;
						case "Delete" :
							$form_wpp_id = $_POST['fWPPeopleId'];
							wppDeletePeople($form_wpp_id);
							$this->printListBox($this->thisUserId, $this->thisUserLevel);
							break;
						case "Copy" :
							$form_wpp_id = $_POST['fWPPeopleId'];
							wppCopyPeople($form_wpp_id, $this->thisUserId);
							$this->printListBox($this->thisUserId, $this->thisUserLevel);
							break;
						case "View" :
							$form_wpp_id = $_POST['fWPPeopleId'];
							wppEditPeople($form_wpp_id);
							break;
						case "Update Table" :
						case "Install" :
							wppInstallTable($_POST['installAction'], true, $thisUserId);
							$this->printListBox($thisUserId, $thisUserLevel);
							break;
						default :
							$this->printListBox($thisUserId, $thisUserLevel);
							break;
					}
				}		
			} else {
				$this->printListBox($this->thisUserId, $this->thisUserLevel);
			}
			?>
				</td>
			</tr>
			<?php
			$this->footerBox();
		}
		
		/*
			This is the actual filter. It will go through the Post or Page and find names that match the XFN database.
			When a name is found, it will replace it with a Nickname and a link to view more information about the person.
		*/		
		function peopleDefine($text)
		{  
			global $wpdb;		
			$args = array(
					'orderby'        => 'name', 
					'order'          => 'ASC',
					'limit'          => -1, 
					'category_name'  => $this->cat_name,);
			
			$links = get_bookmarks($args);
			
			$patternArray;
			$replaceArray;
			$displayNameArray;
		
			wp_enqueue_script('thickbox');
				
			foreach ($links as $link) {
				$link = sanitize_bookmark($link);
				$real_name = $link->link_name = attribute_escape($link->link_name);
				$thisID = $link->link_id = attribute_escape($link->link_id);
				$nickname = $link->link_description = attribute_escape($link->link_description);	
			
				if($nickname == '')
				{
					$display_name = $real_name;
				}
				else
				{
					$display_name = $nickname;
				}
				
				//changes text to the wp-people link
				$replaceText = '<a id="wp-' . $thisID . 'people" href="' . $this->plugin_url;
				$replaceText .= '/wp-people-popup.php?person=';
				$replaceText .= $thisID . '&height=500&width=500" target="_parent" class="thickbox" title="' . $this->preNameText . ' ' . $nickname . '" >';
				$replaceText .= $display_name . '</a>';
				
				$replaceArray[$nameX] = $replaceText;
				$patternArray[$nameX] = "/$real_name/";

				$nameX++;
			}
			
			/* debuggin code
			echo "<pre>";
			print_r($replaceArray);
			print_r($patternArray);
			print_r($displayNameArray);
			echo "</pre>";
			*/
			
			$text = preg_replace($patternArray, $replaceArray, $text, 1);		
			//"#$real_name(?!</(ac|sp))#i"
			return $text;
		}
	}
}

//---------------------------------------------------------------------------------------------
//instantiate the class
if (class_exists('WPPeople')) {
    $WPPeople_var = new WPPeople();
	/*-----------------------------------------------------
		When the plugin is activated, execute the install
		function.  This must be called here, in the
		execution of the plugin itself, because the
		constructor function of the class doesn't get
		called until *after* the plugin has been
		activated.  (I *think* that's what's happening.)
	-----------------------------------------------------*/
	register_activation_hook( __FILE__, array( &$WPPeople, "Initalization" ) );
	// ButtonSnap needs to be loaded outside the class in order to work right
	if ( !class_exists('buttonsnap') ) {
		@include_once( ABSPATH . '/wp-content/plugins/wp-people/lib/buttonsnap.php' );
	}
	/*
	function candidates_pq_getCandidate( $echo = 1 ){
		global $WPPeople;
		if ( $echo ){
			echo $WPPeople->getCandidateInfo();
		}
		else{
			return $WPPeople->getCandidateInfo();
		}
	}
	*/
}
?>