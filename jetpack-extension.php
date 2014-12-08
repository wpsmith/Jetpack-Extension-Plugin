<?php

/**
 * To use, do the following:
 *  1. Find & Replace Module Name with your name
 *  2. Find & Replace module-slug with your slug
 *  3. Find & Replace MODULE with your CONST_ABBREVIATION
 *  4. Add filters/actions/code to the last if-then
 */

/*
 * Plugin Name: Jetpack Extension - Module Name
 * Plugin URI: http://www.wpsmith.net/
 * Description: Extends Jetpack's Module Name Module.
 * Version: 0.0.1
 * Author: Travis Smith
 * Author URI: http://www.wpsmith.net/
 * Text Domain: jpe-jetpack-extension-module-slug
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
 * @const JPE_MODULE_PREFIX
 */
define( 'JPE_MODULE_PREFIX', 'jpe' );

/**
 * Simple name constant
 *
 * @const JPE_MODULE_SIMPLE_NAME
 */
define( 'JPE_MODULE_SIMPLE_NAME', 'jetpack-extension-module-slug' );

/**
 * Domain constant
 *
 * @const JPE_MODULE_PLUGIN_DOMAIN
 */
define( 'JPE_MODULE_PLUGIN_DOMAIN', JPE_MODULE_PREFIX . '-' . JPE_MODULE_SIMPLE_NAME );

/**
 * Slug constant
 *
 * @const JPE_MODULE_PLUGIN_SLUG
 */
define( 'JPE_MODULE_PLUGIN_SLUG', dirname( plugin_basename( __FILE__ ) ) );

/**
 * Plugin Version constant
 *
 * @const JPE_MODULE_PLUGIN_VERSION
 */
define( 'JPE_MODULE_PLUGIN_VERSION', '0.0.1' );

/**
 * Setting constant
 *
 * @const JPE_MODULE_SETTINGS_FIELD
 */
define( 'JPE_MODULE_SETTINGS_FIELD', JPE_MODULE_PLUGIN_DOMAIN . '-settings' );

add_action( 'update_option_active_sitewide_plugins', 'jpe_module_deactivate_self', 10, 2 );
add_action( 'update_option_active_plugins', 'jpe_module_deactivate_self', 10, 2 );
/**
 *  Deactivate ourself if Jetpack is deactivated.
 *
 * @param string $plugin               Plugin string.
 * @param string $network_deactivating Whether network deactivating.
 */
function jpe_module_deactivate_self( $plugin, $network_deactivating ) {
	if ( ! is_plugin_active( 'jetpack/jetpack.php' ) ) {
		deactivate_plugins( plugin_basename( __FILE__ ), true );
	}
}

/**
 * Jetpack Modules:
 *  after-the-deadline
 *  blavatar
 *  carousel
 *  comments
 *  contact-form
 *  custom-css
 *  custom-post-types
 *  gplus-authorship
 *  holiday-snow
 *  infinite-scroll
 *  likes
 *  markdown
 *  minileven
 *  omnisearch
 *  photon
 *  post-by-email
 *  publicize
 *  related-posts
 *  sharedaddy
 *  shortcodes
 *  site-icon
 *  subscriptions
 *  theme-tools
 *  tiled-gallery
 *  verification-tools
 *  videopress
 *  widgets
 *  widget-visibility
 */
$jetpack_active_modules = get_option( 'jetpack_active_modules' );


// Extend/Customize Related Posts Module
if ( $jetpack_active_modules && in_array( 'module-slug', $jetpack_active_modules ) ) {

	// Do something fun

}
