<?php

/*
 * Plugin Name: Jetpack Extension - Related Posts
 * Plugin URI: http://www.wpsmith.net/
 * Description: Extends Jetpack's Related Posts Module.
 * Version: 0.0.1
 * Author: Travis Smith
 * Author URI: http://www.wpsmith.net/
 * Text Domain: jpe-jetpack-extension-related-posts
 * Domain Path: languages
 * License: GPLv2

    Copyright 2014  Travis Smith  (email : http://wpsmith.net/contact)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

//* Constants
/**
 * Prefix constant
 * 
 * @const JPE_RP_PREFIX
 */
define( 'JPE_RP_PREFIX', 'jpe' );

/**
 * Simple name constant
 *
 * @const JPE_RP_SIMPLE_NAME
 */
define( 'JPE_RP_SIMPLE_NAME', 'jetpack-extension-related-posts' );

/**
 * Domain constant
 *
 * @const JPE_RP_PLUGIN_DOMAIN
 */
define( 'JPE_RP_PLUGIN_DOMAIN', JPE_RP_PREFIX . '-' . JPE_RP_SIMPLE_NAME );

/**
 * Slug constant
 *
 * @const JPE_RP_PLUGIN_SLUG
 */
define( 'JPE_RP_PLUGIN_SLUG', dirname( plugin_basename( __FILE__ ) ) );

/**
 * Plugin Version constant
 *
 * @const JPE_RP_PLUGIN_VERSION
 */
define( 'JPE_RP_PLUGIN_VERSION', '0.0.1' );

/**
 * Setting constant
 *
 * @const JPE_RP_SETTINGS_FIELD
 */
define( 'JPE_RP_SETTINGS_FIELD', JPE_RP_PLUGIN_DOMAIN . '-settings' );

add_action( 'update_option_active_sitewide_plugins', 'jpe_related_posts_deactivate_self', 10, 2 );
add_action( 'update_option_active_plugins', 'jpe_related_posts_deactivate_self', 10, 2 );
/**
 *  Deactivate ourself if Jetpack is deactivated.
 */
function jpe_related_posts_deactivate_self( $plugin, $network_deactivating ) {
	if ( ! is_plugin_active( 'jetpack/jetpack.php' ) ) {
		deactivate_plugins( plugin_basename( __FILE__ ), true );
	}
}

// Get active modules
$jetpack_active_modules = get_option( 'jetpack_active_modules' );
//$jetpack_active_modules[] = 'related-posts';
//update_option( 'jetpack_active_modules', array_unique( $jetpack_active_modules ) );
//WPS_Utils::pr($jetpack_active_modules);

// If module active, Extend/Customize Related Posts Module
if ( $jetpack_active_modules && in_array( 'related-posts', $jetpack_active_modules ) ) {

	require_once( 'class.jetpack-relatedposts.php' );

	add_action( 'wp', 'jpe_related_posts_remove_related_posts', 20 );
	/**
	 * Remove related posts from the_content end.
	 */
	function jpe_related_posts_remove_related_posts() {
		if ( class_exists( 'Jetpack_RelatedPosts' ) ) {
			$jprp = Jetpack_RelatedPosts::init();
			remove_filter( 'the_content', array( $jprp, 'filter_add_target_to_dom' ), 40 );
		}
	}

	add_action( 'plugins_loaded', 'jpe_init_related_posts' );
	/**
	 * Init related posts customization.
	 */
	function jpe_init_related_posts() {

		if ( is_admin() ) {
			return;
		}

		global $_genesis_displayed_ids;

		$args = array(
			'headline'                  => sprintf(
				'<h2 class="jp-relatedposts-headline entry-title">%s</h2>',
				__( 'Related Posts', JPE_RP_PLUGIN_DOMAIN )
			),
			'post_context_cb'           => '__return_empty_string',
			'post_category_context_cb'  => '',
			'post_tag_context_cb'       => '',
			'exclude_post_ids'          => $_genesis_displayed_ids,
			'post_type'                 => 'all',
			'thumbnail_size'            => array( 'width' => 330, 'height' => 200 ),
			'date_range'                => array(
				'from' => strtotime( '-6 months' ),
				'to'   => time(),
			),
			'size'                      => 3,
		);

		JPE_RP_Customize_Related_Posts::get_instance( $args );

	}

}
