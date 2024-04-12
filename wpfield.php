<?php
/*
Plugin Name: Gravity Forms 自定义字段
Description: 支持级联地址选择
Version: 1.0
Author: WP POST
*/
defined( 'ABSPATH' ) || die();

/**
 * Path to PPCP root folder.
 *
 * @since 2.0
 */
define( 'WP_POST_FIELD_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );

// After Gravity Forms is loaded, load the Add-On.
add_action( 'gform_loaded', array( 'WP_POST_FIELD_Bootstrap', 'load_addon' ), 5 );

class WP_POST_FIELD_Bootstrap {

	/**
	 * Loads the required files.
	 *
	 * @since  1.0
	 */
	public static function load_addon() {

		require_once WP_POST_FIELD_PLUGIN_PATH . 'class-gf-wp-field.php';
		GFAddOn::register( 'WP_POST_FIELD' );
	}

}

/**
 * Returns an instance of the WP_POST class
 *
 * @since  1.0
 *
 * @return WP_POST_FIELD|bool An instance of the WP_POST_FIELD class
 */
function gf_wp_post_field() {
	return class_exists( 'WP_POST_FIELD' ) ? WP_POST_FIELD::get_instance() : false;
}
