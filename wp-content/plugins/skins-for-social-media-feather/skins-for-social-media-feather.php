<?php
/*
Plugin Name: Skins for Social Media Feather
Plugin URI: https://susanwrotethis.com/plugins/skins-for-social-media-feather
Description: Social Media Feather is a popular social media plugin, but its styling options are limited to the available skins. This plugin provides six new skins created with the help of the Socicon generator.
Version: 1.0
Author: Susan Walker
Author URI: https://susanwrotethis.com
License: GPL v2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: swt-ssmf
Domain Path: /lang/
*/

// Exit if loaded from outside of WP
if ( !defined( 'ABSPATH' ) ) exit;

// SCRIPT LOADING AND LANGUAGE SUPPORT SETUP BEGINS HERE /////////////////////////////////
define( 'SWT_SSMF_IMG_PATH', plugin_dir_path( __FILE__ ) );
define( 'SWT_SSMF_IMG_URL', plugin_dir_url( __FILE__ ) );

// Load plugin textdomain
function swt_ssmf_load_textdomain()
{
	load_plugin_textdomain( 'swt-ssmf', false, dirname( plugin_basename( __FILE__ ) ).'/lang/' );
}
add_action( 'init', 'swt_ssmf_load_textdomain' );

// SOCIAL MEDIA FEATHER FILTER FUNCTION BEGINS HERE //////////////////////////////////////
function swt_ssmf_add_icon_skins( $icons )
{
	$add_ons = array(
		'color-white-square' => array(
			'label' => esc_html__('Color Icons on White Squares', 'swt-ssmf' ), 
			'image' => SWT_SSMF_IMG_URL.'/img/color-white-square/preview.png',
			'folder' => '/img/color-white-square/', 
			'path' => SWT_SSMF_IMG_PATH.'/img/color-white-square/',
			'uri' => SWT_SSMF_IMG_URL.'/img/color-white-square/'
		),
		'white-black-square' => array(
			'label' => esc_html__('White Icons on Black Squares', 'swt-ssmf' ), 
			'image' => SWT_SSMF_IMG_URL.'/img/white-black-square/preview.png', 
			'folder' => '/img/white-black-square/', 
			'path' => SWT_SSMF_IMG_PATH.'/img/white-black-square/',
			'uri' => SWT_SSMF_IMG_URL.'/img/white-black-square/'
		),
		'white-color-square' => array(
			'label' => esc_html__('White Icons on Color Squares', 'swt-ssmf' ), 
			'image' => SWT_SSMF_IMG_URL.'/img/white-color-square/preview.png',
			'folder' => '/img/white-color-square/', 
			'path' => SWT_SSMF_IMG_PATH.'/img/white-color-square/',
			'uri' => SWT_SSMF_IMG_URL.'/img/white-color-square/'
		),
		'color-white-round' => array(
			'label' => esc_html__('Color Icons on White Circles', 'swt-ssmf' ), 
			'image' => SWT_SSMF_IMG_URL.'/img/color-white-round/preview.png',
			'folder' => '/img/color-white-round/', 
			'path' => SWT_SSMF_IMG_PATH.'/img/color-white-round/',
			'uri' => SWT_SSMF_IMG_URL.'/img/color-white-round/'
		),
		'white-black-round' => array(
			'label' => esc_html__('White Icons on Black Circles', 'swt-ssmf' ), 
			'image' => SWT_SSMF_IMG_URL.'/img/white-black-round/preview.png', 
			'folder' => '/img/white-black-round/', 
			'path' => SWT_SSMF_IMG_PATH.'/img/white-black-round/',
			'uri' => SWT_SSMF_IMG_URL.'/img/white-black-round/'
		),
		'white-color-round' => array(
			'label' => esc_html__('White Icons on Color Circles', 'swt-ssmf' ), 
			'image' => SWT_SSMF_IMG_URL.'/img/white-color-round/preview.png',
			'folder' => '/img/white-color-round/', 
			'path' => SWT_SSMF_IMG_PATH.'/img/white-color-round/',
			'uri' => SWT_SSMF_IMG_URL.'/img/white-color-round/'
		),
	);
	return array_merge( $icons, $add_ons );
}
 add_filter( 'synved_social_icon_skin_list', 'swt_ssmf_add_icon_skins' );