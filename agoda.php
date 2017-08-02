<?php
if ( ! defined( 'ABSPATH' ) ) exit;
include 'includes/AGLinkConverter.php';
global $wpdb;


/**
 * Plugin Name:  Old-to-New Agoda Link Converter
 * Plugin URI: http://www.agoda.com
 * Description: With the Old-to-New Agoda Link Converter plug-in, existing old link structure Agoda affiliate links will be converted to new link structures for improved tracking as well as automatic conversion of popular destination keywords to Agoda affiliate links.
 * Version: 1.4.2
 * Author: Agoda Partners
 * Author URI: https://partners.agoda.com
 * License: GPL2
 */


/**
 * Activation settings
 */
register_activation_hook( __FILE__, 'agoda_activate' );
function agoda_activate()
{
    global $wpdb;
    $sql = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}agoda_settings` (
          `setting_id` int(11) NOT NULL DEFAULT '1',
          `affiliate_id` VARCHAR(255) NULL DEFAULT 'XXXXXX',
		  `max_links_per_page` int(11) NULL,
		  `exclude_post_ids` VARCHAR (255)  NULL
		) ENGINE=MyISAM DEFAULT CHARSET=latin1;
		
		INSERT INTO `{$wpdb->prefix}agoda_settings` (`setting_id`, `max_links_per_page`, `exclude_post_ids`) VALUES
		(1, '', '');";
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}


register_deactivation_hook( __FILE__, 'agoda_deactivate' );
function agoda_deactivate()
{
    global $wpdb;
    $wpdb->query("DROP TABLE ".$wpdb->prefix ."agoda_settings");

}


/**
 * Add the menu
 */
function agoda_plugin_menu()
{
    add_menu_page('Agoda','Agoda Site Configuration', 'manage_options', 'agoda-plugin', 'agoda_settings');
    add_action('admin_menu', 'agoda-config');
}
add_action('admin_menu', 'agoda_plugin_menu');




/**
 * Settings for the plugin
 */
function agoda_settings()
{

    include 'includes/settings.php';
}



/**
 * Add the filter to change the links...
 */
add_filter( 'the_content', 'agoda_the_content_filter' );
function agoda_the_content_filter( $content ) {
    global $wpdb;
    $settings = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."agoda_settings  WHERE setting_id = '1'", ARRAY_A);

    $exclude_post_ids = explode(',',$settings['exclude_post_ids']);
    $post = get_post();
    $max_links_per_page = $settings['max_links_per_page'];
    $doc = new DOMDocument();
    libxml_use_internal_errors(true);
    if(!empty($content)) {

        $doc->loadHTML($content);
        $tags = $doc->getElementsByTagName('a');

        $Linkchanger = new AGLinkConverter_Linkchanger($settings['affiliate_id']);
        foreach ($tags as $tag) {
            if(strpos($tag->getAttribute('href'),'agoda.com') !== false) {
                $content = str_replace($tag->getAttribute('href'),$Linkchanger->contentChangerFactory($tag->getAttribute('href')),$content);

            }
        }

        if(!in_array($post->ID,$exclude_post_ids)) {
            $content = $Linkchanger->transformPopulairDestinations($content,$max_links_per_page);
        }

    }



    return $content;
}

