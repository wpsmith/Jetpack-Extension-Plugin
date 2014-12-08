<?php

/*
 * Plugin Name: Jetpack Extension - Sharing
 * Plugin URI: http://www.wpsmith.net/
 * Description: Extends Jetpack's Sharing Module.
 * Version: 0.0.1
 * Author: Travis Smith
 * Author URI: http://www.wpsmith.net/
 * Text Domain: jpe-jetpack-extension-sharing
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
 * @const JPE_SHARING_PREFIX
 */
define( 'JPE_SHARING_PREFIX', 'jpe' );

/**
 * Simple name constant
 *
 * @const JPE_SHARING_SIMPLE_NAME
 */
define( 'JPE_SHARING_SIMPLE_NAME', 'jetpack-extension-sharing' );

/**
 * Domain constant
 *
 * @const JPE_SHARING_PLUGIN_DOMAIN
 */
define( 'JPE_SHARING_PLUGIN_DOMAIN', JPE_SHARING_PREFIX . '-' . JPE_SHARING_SIMPLE_NAME );

/**
 * Slug constant
 *
 * @const JPE_SHARING_PLUGIN_SLUG
 */
define( 'JPE_SHARING_PLUGIN_SLUG', dirname( plugin_basename( __FILE__ ) ) );

/**
 * Plugin Version constant
 *
 * @const JPE_SHARING_PLUGIN_VERSION
 */
define( 'JPE_SHARING_PLUGIN_VERSION', '0.0.1' );

/**
 * Setting constant
 *
 * @const JPE_SHARING_SETTINGS_FIELD
 */
define( 'JPE_SHARING_SETTINGS_FIELD', JPE_SHARING_PLUGIN_DOMAIN . '-settings' );

add_action( 'update_option_active_sitewide_plugins', 'jpe_deactivate_self', 10, 2 );
add_action( 'update_option_active_plugins', 'jpe_deactivate_self', 10, 2 );
/**
 *  Deactivate ourself if Jetpack is deactivated.
 */
function jpe_deactivate_self( $plugin, $network_deactivating ) {
	if ( ! is_plugin_active( 'jetpack/jetpack.php' ) ) {
		deactivate_plugins( plugin_basename( __FILE__ ), true );
	}
}

// Get active modules
$jetpack_active_modules = get_option( 'jetpack_active_modules' );

// If module active, Extend/Customize Sharedaddy Module
if ( $jetpack_active_modules && in_array( 'sharedaddy', $jetpack_active_modules ) ) {

	require_once( 'class.jetpack-sharing.php' );

	// Remove sharedaddy from the_content & the_excerpt
	remove_filter( 'the_content', 'sharing_display', 19 );
	remove_filter( 'the_excerpt', 'sharing_display', 19 );

	// Add shortcode support for the body of the text widget
	add_filter( 'widget_text', 'do_shortcode' );

	add_action( 'plugins_loaded', 'jpe_init', 999 );
	/**
	 * Init plugin.
	 * Add custom sharing_display shortcode.
	 */
	function jpe_init() {

		// Add shortcode
		JPE_Customize_Sharing::get_instance();

		/*
		 * For WP Engine users, remove the code that prevents email sharing.
		 * Jetpack committed a fix on 8/27 for 3.2, and current version is > 3.8.0
		 *
		 * @link https://github.com/Automattic/jetpack/issues/448
		 *
		 * WP Engine's PHP Documentation notes
		 * Kills the email button in Jetpack's Sharing module due to spam.
		 * Remove this once devs ship a fix. Currently slated for Jetpack 3.2.
		 */
		remove_filter( 'sharing_services', 'wpe_kill_sharedaddy_email' );
	}

}

/**
 * Custom sharing display to display the icons.
 *
 * @param string $text          Content text.
 * @param bool   $echo          Whether the function should output.
 * @param string $text_location Text location: 'before' or 'after'
 *
 * @return string Shortcode HTML.
 */
function jpe_sharing_display( $text = '', $echo = false, $text_location = 'before' ) {
	global $post;

	if ( is_preview() || is_admin() ) {
		return __( 'Preview/Admin: No sharing icons will be shown', JPE_SHARING_PLUGIN_DOMAIN );
	}

	$sharer = new Sharing_Service();
	$global = apply_filters( 'jpe_sharing_global_options', $sharer->get_global_options() );

	// Pass through a filter for final say so
	$show = apply_filters( 'sharing_show', true, $post );

	// Disabled for this post?
	$switched_status = get_post_meta( $post->ID, 'sharing_disabled', false );

	if ( ! empty( $switched_status ) ) {
		$show = false;
	}

	// Allow to be used on P2 ajax requests for latest posts.
	if ( defined( 'DOING_AJAX' ) && DOING_AJAX && isset( $_REQUEST['action'] ) && 'get_latest_posts' == $_REQUEST['action'] ) {
		$show = true;
	}

	$sharing_content = '';
	if ( $show ) {

		$enabled = apply_filters( 'sharing_enabled', $sharer->get_blog_services() );

		if ( count( $enabled['all'] ) > 0 ) {
			global $post;

			$dir = get_option( 'text_direction' );

			// Wrapper
			$sharing_content .= '<div class="sharedaddy sd-sharing-enabled"><div class="robots-nocontent sd-block sd-social sd-social-' . $global['button_style'] . ' sd-sharing">';
			if ( $global['sharing_label'] != '' ) {
				$sharing_content .= '<h3 class="sd-title">' . $global['sharing_label'] . '</h3>';
			}
			$sharing_content .= '<div class="sd-content"><ul>';

			// Visible items
			$visible = '';
			foreach ( $enabled['visible'] as $id => $service ) {
				// Individual HTML for sharing service
				$visible .= '<li class="share-' . $service->get_class() . '">' . $service->get_display( $post ) . '</li>';
			}

			$parts   = array();
			$parts[] = $visible;
			if ( count( $enabled['hidden'] ) > 0 ) {
				if ( count( $enabled['visible'] ) > 0 ) {
					$expand = __( 'More', JPE_SHARING_PLUGIN_DOMAIN );
				} else {
					$expand = __( 'Share', JPE_SHARING_PLUGIN_DOMAIN );
				}
				$parts[] = '<li><a href="#" class="sharing-anchor sd-button share-more"><span>' . $expand . '</span></a></li>';
			}

			if ( $dir == 'rtl' ) {
				$parts = array_reverse( $parts );
			}

			$sharing_content .= implode( '', $parts );
			$sharing_content .= '<li class="share-end"></li></ul>';

			if ( count( $enabled['hidden'] ) > 0 ) {
				$sharing_content .= '<div class="sharing-hidden"><div class="inner" style="display: none;';

				if ( count( $enabled['hidden'] ) == 1 ) {
					$sharing_content .= 'width:150px;';
				}

				$sharing_content .= '">';

				if ( count( $enabled['hidden'] ) == 1 ) {
					$sharing_content .= '<ul style="background-image:none;">';
				} else {
					$sharing_content .= '<ul>';
				}

				$count = 1;
				foreach ( $enabled['hidden'] as $id => $service ) {
					// Individual HTML for sharing service
					$sharing_content .= '<li class="share-' . $service->get_class() . '">';
					$sharing_content .= $service->get_display( $post );
					$sharing_content .= '</li>';

					if ( ( $count % 2 ) == 0 ) {
						$sharing_content .= '<li class="share-end"></li>';
					}

					$count ++;
				}

				// End of wrapper
				$sharing_content .= '<li class="share-end"></li></ul></div></div>';
			}

			$sharing_content .= '</div></div></div>';

			// Register our JS
			if ( apply_filters( 'sharing_enqueue_scripts', true ) ) {
				wp_enqueue_script( 'sharing-js', WP_SHARING_PLUGIN_URL . 'sharing.js', array( 'jquery', ), JPE_SHARING_PLUGIN_VERSION );
				add_filter( 'sharing_enabled', '__return_true', 999 );
				add_action( 'wp_footer', 'sharing_add_footer' );
			}

		}
	}

	if ( 'before' === $text_location ) {
		$output = $text . $sharing_content;
	} else {
		$output = $sharing_content . $text;
	}

	if ( $echo ) {
		echo $output;
	} else {
		return $output;
	}
}
