<?php

/**
 * @author Deanna Schneider
 * @copyright 2008
 * @description Use WordPress Shortcode API for more features
 * @Docs http://codex.wordpress.org/Shortcode_API
 */

class wppAddPeople_shortcodes {
	
	var $count = 1;
	
	// register the new shortcodes
	function wppAddPeople_shortcodes() {
	
		add_shortcode( 'wppHW', array(&$this, 'show_RSS') );
			
	}

	
	function show_RSS( $atts ) {
	
		global $wppAddPeople;
	
		extract(shortcode_atts(array(
			'id' 		=> false
		), $atts ));
		
		//$out = __('[TEST THIS]','cetsHW');
		$out = '<span style="color: red">Hello ' . $id . '</span>';
			
		return $out;
	}

	
}

// let's use it
$wppAddPeople_Shortcodes = new wppAddPeople_Shortcodes;	

?>