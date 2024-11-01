=== WP People ===
Contributors: Dean Logan
Donate link: http://www.dean-logan.com/plugins-and-widgets
Tags: xfn, links, blogroll, people
Requires at least: 2.7.1
Tested up to: 2.8.4
Stable tag: 3.4.1

This is a filter that will switch out people's names for XFN Links information.

== Description ==
This plug-in will search a post and find names that match database records of people maked with the WP People Category 
in the XFN Links. When it finds a match, it will replace the name with a link to the person. There is a administration 
screen for adding people and their bios to the database viewing the current people marked for the filter. More than 
one person can be linked on a post. A individual name will only be linked once per post.

The original author of the hack stopped supporting it a while ago. I took his original idea and used another 
hack (acronymit) as a guide to make this work. The original worked with the my-hacks script used in 
WordPress 1.0.1, so this is beyond the functionality of the original.

If you were using the version 2 of Word Press People, then you will be able to see any current people in 
WP People and COPY them to the XFN database. 

== Installation ==
1. Upload `wp-people` to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Go to the WP People administration page under the Tools section.  The first time there will create the "WP People" Category
1. Go to the Links administration page. Add new Links or add the WP Category to those people who you want to be included in WP People.
1. Optionally Install the 'WordPress Force Word Wrapping' plugin (http://www.seocompany.ca/seo-blog/wordpress-force-word-wrapping-plugin/) to prevent
the description text from extednig past the popup window area.

== Frequently Asked Questions ==
= What is the field mapping from the Link to WP People? =
The field on the Links form match up the following way:

* Name is the Real Name (searched name) in WP People
* Description is the Nick Name (displayed name) in WP People
* Web Address is the Link in WP People
* Advanced Notes is the Description/Bio in WP People
* Advanced Image Link is the Photo in WP People

= How do people get highlighted in my posts and pages? =
The filter goes through the posts and pages and find matches to full names.  When it finds a full name, it replaces the name with a nick name and adds the link.

= Should I always use full names for people? =
Yes. WP People will replace the full name with a nickname is stated.  However, it can't distinguish between 'John' and 'John'.  It is better to have 'John Tiberius Smith' and 'John Jimmy Smith' in order to have unique records for each.

= Do I have to add a person to WP People before putting them in a post? =
No.  If the person's name matches any name in WP People it will be highlighted, no matter when it was put on the blog.

= Do I need to add a person through the Links area? =
As of version 3.1.0, you can add people using the WP People Tool directly into the XFN Links.  The form will only show the fields used by WP People and will automatically give the link the category for WP People.

= Can I update the style of the popup? =
The popup style sheet is in the WP People folder (wp-people-css.css)

= What size is the image? = 
The image is set at 100px x 100px.  It is best to resize the image to fit this value.

= What if I don't have a photo for the person I am linking? =
Included with WP People is the "nophoto.jpg" file.  Copy this image into the `images` directory of any theme you are using.

= Are older version of WP People upgradeable? =
As of version 3.1.0, you can now upgrade your older version of WP People and Copy records from the WP People table to the XFN Database.  When you are done copying and removing old record, the application will ask you to delete the old table.

= Can I alter the text that is seen when you hover of the link? =
The pre-text can be altered by the site administrator.  The name shown is the nickname value of the person being linked to.

== Screenshots ==
1. Link in Admin Tools Menu
2. Main WP People Admin Screen
3. WP People Admin Edit/Add Form
4. Post/Page tinyMCE toolbar with WP People button
5. Post/Page tinyMCE WP People Insert window
6. Post/Page simple editor toolbar with WP People button
7. Post/Page simple editor WP People Insert window

You can view <a href="http://www.dean-logan.com/blog/plugins-and-widgets/wp-people/test-page">the sample page</a>.

== ChangeLog ==
**Version 3.4.1**

* Options were not being initialized correctly

**Version 3.4.0**

* Changed the link title to show the name of the person being linked to plus some pre-text
* Added ability to determine the pre-text for the link title.  This also changes the title of the popup box
* Clean up of code, make the class work like it is supposed to
* Clean up of form structure to use less tables
* Added 'Option' values for Plugin Version, Plugin URL, Link Category Name, and the PreText value
* Added footer with information to site and information about plugin

**Version 3.3.0**

* Fixed popup to use Thickbox from WordPress core, images do not show
* Fixed errors with IE and tinyMCE buttons (tested both FF and IE and all editor windows worked)
* Removed unneeded code

**Version 3.2.1**

* tinyMCE for FireFox was not receiving the name to insert into the post/page

**Version 3.2.0**

* Added a button to the tinyMCE and basic Post/Page editor to easily insert WP People into Posts and Pages.
* Added screen shots of the plugin admin page and Post/Page edit

**Version 3.1.1**

* Fixed readme file to state that older version CAN Copy WP People table to XFN Database

**Version 3.1.0**

* Fixed the backwards compatibility issue.  Now previous version can update to the new version
* Added capability to Copy WP People from table to XFN database
* Added capability to Add/Edit/Delete XFN links used in WP People

**Version 3.03**

* Fixed the directory structure in the svn database.  A duplicate folder was being uploaded causing an error
with the application.

**Version 3.01**

* Renamed the readme file to readme.txt

**Version 3.0**

* Converted the plugin to work with WordPress 2.7 and use the XFN database

**Version 2.0**

* converted it from a Hack to a Plugin

**Version 1.6.1**

* fixed minor error in instructions and create table action 

**Version 1.6**

* fixed errors for installing plug-in and adding table error message

**Version 1.5**

* made changes to use themes and other aspects released in WP [1.5]

**Version 1.4**

* added line to convert query string to local variable in wp-people-popup.php

**Version 1.3**

* slight typo in JavaScript code on my-hacks-script.txt file

**Version 1.2**

* made search and replace of people name case insensitive

**Version 1.1**

* typo and clarification fixes to readme file and my-hacks-script.txt; 
* also added default "no photo" image to zip file

**Version 1.0**

* Inital release.
