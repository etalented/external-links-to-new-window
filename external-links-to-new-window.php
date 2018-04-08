<?php
/*
Plugin Name: External Links to New Window
Plugin URI: http://etalented.co.uk/projects/wordpress-plugin-external-links-to-a-new-window
Description: The external link plugin for WordPress will allow site admins to automatically open external site links to a new window with options for an icon and nofollow rules. 
Author: etalented
Author URI: http://etalented.co.uk/
Version: 2.0.2
*/

/**
 *  External Links to New Window core file
 *
 * This file contains all the logic required for the plugin
 *
 * @link		http://wordpress.org/extend/plugins/external-links-to-new-window/
 *
 * @package 		External Links to New Window
 * @copyright		Copyright (c) 2017 Etalented Limited, credit to Christopher Ross (the original author)
 * @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, v2 (or newer)
 *
 * @since 		External Links to New Window 1.0
 */

function thisismyurl_externallinks_plugin_parse($content) {
	$setting = json_decode(get_option('thisismyurl_externallinks'));
	include_once(dirname(__FILE__).'/lib/simple_html_dom.php');
	$html = str_get_html($content);
	$anchors = $html->find('a');
	foreach($anchors as $a) {
		$href = strtolower($a->href);
		
		if ($setting[0]) {$window = "_blank";}
	    if ($setting[1]) {$nofollow = "nofollow";}
	    if ($setting[2]) {$class = "thisismyurl_external";}
				   
		if(stripos($href, get_bloginfo('url')) === false && substr($href, 0, 4) == 'http') {
			$a->target = $window;
			$a->rel = $nofollow;
			$a->class = $class;
		}
	}
	return $html;
}
add_filter('the_content', 'thisismyurl_externallinks_plugin_parse');

function thisismyurl_externallinks_plugin_css() {
	$setting = json_decode(get_option('thisismyurl_externallinks'));
	if ($setting[2]) {
	?>
	<style type="text/css">
	.thisismyurl_external{ background: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAoAAAAKCAMAAAC67D+PAAAAOVBMVEX////39/fa2trW1tbFxcW9vb2xsbGpqamUlJSIiIiEhIRzc3Nra2tnZ2daWlpSUlJKSkpGRkb///9eei67AAAAE3RSTlP///////////////////////8Asn3cCAAAAEtJREFUCNcdy0kCwCAIBMFJwrgEQcz/Hxu0T3VpfNmIiOnYtEK2eVgVkIBmxsNVHieoJRDSX9CqDSxe43XNA0uAfh/ORko59FjZ5g/2GQR2Q86/9gAAAABJRU5ErkJggg==) no-repeat right;
				padding-right: 14px;
	}
	</style>
	<?php
	}
}
add_action('wp_head','thisismyurl_externallinks_plugin_css');

function thisismyurl_externallinks_admin_css() {
	?>
	<style type="text/css">
		.thisismyurl{ background: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACAAAAAgCAMAAABEpIrGAAAA0lBMVEX///9Jj6xrpLxJj6xrpLxhnbdJj6xxqL5moblhnbdJj6xhnbdJj6xxqL5rpLxmoblJj6xxqL5hnbdJj6x1qsBJj6xrpLxmoblJj6xrpLxJj6x4rMFJj6x1qsBJj6xxqL5Jj6x4rMF1qsBxqL5Jj6z////3+vv0+Pru9ffm7/Tf6/Hd6u/V5ezS4+rO4OjG3OW71eC509+z0Nyvztuqy9ikx9aZwNCRu82OucuMtcWCssZ/sMWAsMV4rMF1qsBrpLxmoblhnbdamrRUlrFSlLBJj6wucLoaAAAAJXRSTlMAESIiMzMzRERERFVVZmZmZnd3d4iImZmZqqq7u8zM3d3u7u7u5Bc3VAAAAehJREFUOMttU9li0zAQdAkNEEoJhXIUp4HEK1vxJeLYiQ/FuGL//5dYWb5I2SdLM56dPWRZQ7z9tj5LHevPr63ncbfN9wEDCi84lNvbC/h6m0cwiShfXU/x27PQt2muM+Qpcdl+MxH5UnKAuJRDlMQPyg89/p5w7yj/iaMHvHxn8DdnTgd5EfRTtHnREtYCvBEXQc/wYL/S+LICGPUrlg1ZgJWviPBDgBiVo2D8jkGQxE0DYBJUR/1XbjCtWQKTL62vGYin9jIAP/eNWMZaXgTpJ+sxhgIbfTzoRhsBbngpxCsLOTSIWqOKQurgQVM9YyIHf2MhAFI8nSV1L9oB8KxiaWeTTPQE/FPpIbHIo3Hu+joARgLGZo47PSlRPSfIYdShTyIdwZOdSUR/XAYWc9ab/GnKRMym69J3lsr8bt2fQBAhnOBe87s+m0Zld9ZcAajBYhvSuEYFHGftsJKpRwg7HFNIbT3uxoUa8TQQmg6vwVULvRCPGXCF8S/X4EmfgEPx0G7UzInBL6nPsaa4qsN9EM6VWcolHdonk8js1OE1p+Lm/VovlWjV696eSqgZuBwfxo2TkQQvWn9NQXS3cObTpzWzVcrGSt1UPVxdvM6FjUUSkk03TAq0F/953/N729EpHPvjbLz9C0A/pR4RVDYdAAAAAElFTkSuQmCC) no-repeat;}
	</style>
	<?php
}
add_action('admin_head','thisismyurl_externallinks_admin_css');



function thisismyurl_externallinks_admin_menu() {
	$thisismyurl_externallinks_settings = add_options_page( 'External Links', 'External Links', 'edit_posts', 'thisismyurl_externallinks', 'thisismyurl_externallinks_help_page');
	add_action('load-'.$thisismyurl_externallinks_settings, 'thisismyurl_externallinks_help_page_scripts');
}
add_action('admin_menu', 'thisismyurl_externallinks_admin_menu');

function thisismyurl_externallinks_help_page_scripts() {
	wp_enqueue_style('dashboard');
	wp_enqueue_script('postbox');
	wp_enqueue_script('dashboard');
}

function thisismyurl_externallinks_help_page() {
	
	if ($_POST) {
		$setting = array($_POST['setting1'],$_POST['setting2'],$_POST['setting3']);
		update_option('thisismyurl_externallinks', json_encode($setting));	
	}
	
	if (empty($setting)) {
		$setting = json_decode(get_option('thisismyurl_externallinks'));
	}
	
	$settingcount = 0;
	foreach ($setting as $settingitem) {
		if ($setting[$settingcount]) {$cb[$settingcount] = 'checked="checked"';}	
		$settingcount++;
	}

	
	echo '<div class="wrap">
			<h2>'.__('Settings for External Links to New Window','thisismyurl_externallinks').'</h2>
			<div class="postbox-container">
				<form method="post" action="options-general.php?page=thisismyurl_externallinks">
	 
				<div class="metabox-holder">
				<div class="meta-box-sortables">
					
					<div id="edit-pages" class="postbox">
					<h3><span>'.__('Plugin Settings','thisismyurl_externallinks').'</span></h3>
					<div class="inside">
						
						<p><input type="checkbox" name="setting1" id="setting1" '.$cb[0].'>&nbsp;<label for="setting1">'.__('Open external content in new window','thisismyurl_externallinks').'</label></p>
						<p><input type="checkbox" name="setting2" id="setting2" '.$cb[1].'>&nbsp;<label for="setting2">'.__('Add nofollow attribute','thisismyurl_externallinks').'</label></p>
						<p><input type="checkbox" name="setting3" id="setting3" '.$cb[2].'>&nbsp;<label for="setting3">'.__('Include Icon after link','thisismyurl_externallinks').'</label></p>
						
					</div><!-- .inside -->
					</div><!-- #edit-pages -->
					<input type="hidden" name="action" value="update" /> 
					<input type="hidden" name="page_options" value="setting1,setting2,setting3" />
					<input type="submit" name="Submit" class="button-primary" value="'.__('Save Settings','thisismyurl_externallinks').'" />
					</form>
				</div><!-- .meta-box-sortables -->
				</div><!-- .metabox-holder -->
				
			</div><!-- .postbox-container -->
	</div><!-- .wrap -->
	
	';
}