// Docu : http://wiki.moxiecode.com/index.php/TinyMCE:Create_plugin/3.x#Creating_your_own_plugins

(function() {
	// Load plugin specific language pack
	tinymce.PluginManager.requireLangPack('WPPeople');
	
	tinymce.create('tinymce.plugins.WPPeople', {
		/**
		 * Initializes the plugin, this will be executed after the plugin has been created.
		 * This call is done before the editor instance has finished it's initialization so use the onInit event
		 * of the editor instance to intercept that event.
		 *
		 * @param {tinymce.Editor} ed Editor instance that the plugin is initialized in.
		 * @param {string} url Absolute URL to where the plugin is located.
		 */
		init : function(ed, url) {
			// Register the command so that it can be invoked by using tinyMCE.activeEditor.execCommand('mceExample');

			ed.addCommand('mceWPPeople', function() {
				ed.windowManager.open({
					file : url + '/window.php',
					width : 500 + ed.getLang('WPPeople.delta_width', 0),
					height : 260 + ed.getLang('WPPeople.delta_height', 0),
					inline : 1
				}, {
					plugin_url : url // Plugin absolute URL
				});
			});

			// Register example button
			ed.addButton('WPPeople', {
				title : 'WPPeople.desc',
				cmd : 'mceWPPeople',
				image : url + '/addPeople.png'
			});

			// Add a node change handler, selects the button in the UI when a image is selected
			ed.onNodeChange.add(function(ed, cm, n) {
				cm.setActive('mceWPPeople', n.nodeName == 'IMG');
			});
		},

		/**
		 * Returns information about the plugin as a name/value array.
		 * The current keys are longname, author, authorurl, infourl and version.
		 *
		 * @return {Object} Name/value array containing information about the plugin.
		 */
		getInfo : function() {
			return {
					longname  : 'WPPeople',
					author 	  : 'Dean Logan',
					authorurl : 'http://www.dean-logan.com',
					infourl   : 'http://www.dean-logan.com',
					version   : "3.3.0"
			};
		}
	});

	// Register plugin
	tinymce.PluginManager.add('WPPeople', tinymce.plugins.WPPeople);
})();