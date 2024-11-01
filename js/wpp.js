var WPPeople = window.WPPeople || {};

(WPPeople.Insert = function() {
	
	return {
	
		embed : function() {
			
			var win = window.parent || window;

			//if ( typeof win.tinyMCE !== 'undefined' && ( win.ed = win.tinyMCE.activeEditor ) && !win.ed.isHidden() ) {
				//
			//} else if(window.tinyMCE) {
				//
			//} else {
				if (typeof this.configUrl !== 'string' || typeof tb_show !== 'function') {
					return;
				}
				
				var url = this.configUrl + ((this.configUrl.match(/\?/)) ? "&" : "?") + "TB_iframe=true";
				
				tb_show('WP People Insert', url , false);
			//}
		}
	};
	
}());

(WPPeople.View = function() {
	
	return {
	
		embed : function() {
			
			var win = window.parent || window;

			//if ( typeof win.tinyMCE !== 'undefined' && ( win.ed = win.tinyMCE.activeEditor ) && !win.ed.isHidden() ) {
				//
			//} else if(window.tinyMCE) {
				//
			//} else {
				if (typeof this.configUrl !== 'string' || typeof tb_show !== 'function') {
					return;
				}
				
				var url = this.configUrl + ((this.configUrl.match(/\?/)) ? "&" : "?") + "TB_iframe=true";
				
				tb_show('WP People', url , false);
			//}
		}
	};
	
}());

/*
	Generator specific script
*/
