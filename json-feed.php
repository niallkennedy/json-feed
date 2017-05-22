<?php
/*
Plugin Name: JSON Feed
Plugin URI: https://www.niallkennedy.com/
Description: Support JSONFeed.org format
Version: 1.0
Author: Niall Kennedy
Author URI: https://www.niallkennedy.com/
*/

// PHP namespace autoloader
require_once( dirname( __FILE__ ) . '/autoload.php' );

add_action( 'init', array('\NiallKennedy\WordPress\Plugin\JSONFeed\PluginLoader', 'init') );

register_activation_hook( __FILE__, array('\NiallKennedy\WordPress\Plugin\JSONFeed\PluginLoader','activationHook') );
register_deactivation_hook( __FILE__, array('\NiallKennedy\WordPress\Plugin\JSONFeed\PluginLoader','deactivationHook') );
