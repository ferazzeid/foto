<?php
/*
Plugin Name: Widget Area Builder
Description: Make a sidebar and place it anywhere in post or page with shortcode.
Tags: widget area, sidebar, sidebar, widget, shortcode, widget manager
Author URI: http://din-ecigaret.dk/
Author: Kjeld Hansen
Text Domain: widget_area_builder
Requires at least: 4.0
Tested up to: 4.4.2
Version: 1.0
*/
 if ( ! defined( 'ABSPATH' ) ) exit; 
add_action('admin_menu','widget_area_builder_admin_menu');
function widget_area_builder_admin_menu() { 
    add_menu_page(
		"Widget Area Builder",
		"Widget Area",
		8,
		__FILE__,
		"widget_area_builder_admin_menu_list",
		plugins_url( 'img/sticky-icon.png', __FILE__) , 40
	); 
}

function widget_area_builder_admin_menu_list(){
	include 'sticky-admin.php';
}



if(get_option( 'ri_widget_area_builder_id' )){
	$stky_option = unserialize(get_option( 'ri_widget_area_builder_id' ));
	if($stky_option): $i=1;
		foreach($stky_option as $id=>$val):
			if(get_option( 'ri_widget_area_builder_'.$val )){ $snmd = unserialize(get_option( 'ri_widget_area_builder_'.$val )); }
			$sbn = $snmd[name]; $sbd = $snmd[des]; 
			ri_sidebar_generator($val, $sbn, $sbd);
		endforeach;
	endif;
}

function ri_sidebar_generator($id, $nm, $des){
	
$args = array(
	'name'          => $nm,
    'id'            => 'riwabb'.$id,          
	'description'   => $des,
	'class'         => '',
	'before_widget' => '<li id="%1$s" class="widget ri-sticky-wdg %2$s">',
	'after_widget'  => '</li>',
	'before_title'  => '<h2 class="widgettitle">',
	'after_title'   => '</h2>' ); 
	
	register_sidebar( $args );
}

if (!shortcode_exists('ri_custom_sidebar')) {
	add_shortcode('ri_custom_sidebar', 'ri_custom_sidebar_fn');
}

function ri_custom_sidebar_fn($args){
	$sbid = 'riwabb'.$args[0];
	if(dynamic_sidebar( $sbid )){  }
}

