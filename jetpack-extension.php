<?php

/*
 * Plugin Name: Burnaway Jetpack Extension
 * Plugin URI: http://www.wpsmith.net/
 * Description: Extends Jetpack.
 * Version: 0.0.1
 * Author: Travis Smith
 * Author URI: http://www.wpsmith.net/
 * Text Domain: ba-jetpack
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
define( 'BAJP_PREFIX', 'ba' );
define( 'BAJP_SIMPLE_NAME', 'jetpack' );
define( 'BAJP_PLUGIN_DOMAIN', BAJP_PREFIX . '-' . BAJP_SIMPLE_NAME );
define( 'BAJP_PLUGIN_SLUG', dirname( plugin_basename( __FILE__ ) ) );
define( 'BAJP_PLUGIN_VERSION', '0.0.1' );
define( 'BAJP_SETTINGS_FIELD', BAJP_PLUGIN_DOMAIN . '-settings' );

add_action( 'update_option_active_sitewide_plugins', 'bajp_deactivate_self', 10, 2 );
add_action( 'update_option_active_plugins', 'bajp_deactivate_self', 10, 2 );
/**
 *  Deactivate ourself if Gravity Forms is deactivated.
 */
function bajp_deactivate_self( $plugin, $network_deactivating ) {
	if ( ! is_plugin_active( 'jetpack/jetpack.php' ) ) {
		deactivate_plugins( plugin_basename( __FILE__ ), true );
	}
}

$jetpack_active_modules = get_option( 'jetpack_active_modules' );
if ( $jetpack_active_modules && in_array( 'related-posts', $jetpack_active_modules ) ) {

	add_action( 'wp', 'bajp_relatedposts_remove_related_posts', 20 );
	function bajp_relatedposts_remove_related_posts() {
		$jprp = Jetpack_RelatedPosts::init();
		remove_filter( 'the_content', array( $jprp, 'filter_add_target_to_dom' ), 40 );
	}

	add_filter( 'jetpack_relatedposts_filter_headline', 'bajp_relatedposts_headline' );
	function bajp_relatedposts_headline( $headline ) {
		$headline = sprintf(
			'<h2 class="jp-relatedposts-headline entry-title">%s</h2>',
			__( 'Related Posts', BAJP_PLUGIN_DOMAIN )
		);

		return $headline;
	}

	add_filter( 'jetpack_relatedposts_filter_exclude_post_ids', 'bajp_relatedposts_exclude_post_ids' );
	function bajp_relatedposts_exclude_post_ids( $exclude_post_ids ) {
		global $_genesis_displayed_ids;

		return array_merge( $exclude_post_ids, (array) $_genesis_displayed_ids );
	}

	add_filter( 'jetpack_relatedposts_filter_post_context', '__return_empty_string' );

	add_filter( 'jetpack_relatedposts_filter_post_type', 'bajp_relatedposts_post_types' );
	function bajp_relatedposts_post_types( $post_types ) {
		return 'all';
		//	WPS_Utils::pr(BACore::get_instance()->get_post_types());
		$pts = BACore::get_instance()->get_post_types();

		return array_unique( array_merge( (array) $post_types, (array) $pts ) );
	}

	add_filter( 'jetpack_relatedposts_filter_thumbnail_size', 'bajp_relatedposts_thumbnail_size' );
	function bajp_relatedposts_thumbnail_size( $size ) {
		return array( 'width' => 330, 'height' => 200 );
	}

	add_filter( 'jetpack_relatedposts_post_category_context', 'bajp_relatedposts_post_category_context' );
	function bajp_relatedposts_post_category_context( $context ) {
		return $context;
	}

	add_filter( 'jetpack_relatedposts_post_tag_context', 'bajp_relatedposts_post_tag_context' );
	function bajp_relatedposts_post_tag_context( $context ) {
		return $context;
	}

	add_filter( 'jetpack_relatedposts_filter_date_range', 'bajp_related_posts_past_halfyear_only' );
	function bajp_related_posts_past_halfyear_only( $date_range ) {
		$date_range = array(
			'from' => strtotime( '-6 months' ),
			'to'   => time(),
		);

		return $date_range;
	}
}

if ( $jetpack_active_modules && in_array( 'sharedaddy', $jetpack_active_modules ) ) {
	remove_filter( 'the_content', 'sharing_display', 19 );
	remove_filter( 'the_excerpt', 'sharing_display', 19 );
	add_filter( 'widget_text', 'do_shortcode' );

	add_action( 'plugins_loaded', 'bajp_add_shortcode', 999 );
	function bajp_add_shortcode() {
		add_shortcode( 'ba_sharing', 'bajp_sharing_shortcode' );
		add_shortcode( 'jetpack_sharing', 'bajp_sharing_shortcode' );
		remove_filter( 'sharing_services', 'wpe_kill_sharedaddy_email' );
	}

	function bajp_sharing_shortcode( $atts, $content ) {
		$defaults = array();
		$atts     = shortcode_atts( $defaults, $atts );

		if ( function_exists( 'bajp_sharing_display' ) ) {
			return bajp_sharing_display();
		} else {
			return __( 'Something went wrong...', BAJP_PLUGIN_DOMAIN );
		}
	}
}

function bajp_sharing_display( $text = '', $echo = false ) {
	global $post;

	if ( is_preview() || is_admin() ) {
		return __( 'Preview: No sharing icons will be shown', BAJP_PLUGIN_DOMAIN );
	}

	$sharer = new Sharing_Service();
	$global = $sharer->get_global_options();

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
					$expand = __( 'More', BAJP_PLUGIN_DOMAIN );
				} else {
					$expand = __( 'Share', BAJP_PLUGIN_DOMAIN );
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
			wp_enqueue_script( 'sharing-js', WP_SHARING_PLUGIN_URL . 'sharing.js', array( 'jquery', ), '20140920' );
			add_filter( 'sharing_enqueue_scripts', '__return_true' );
			add_filter( 'sharing_enabled', '__return_true' );
			add_action( 'wp_footer', 'sharing_add_footer' );
		}
	}

	if ( $echo ) {
		echo $text . $sharing_content;
	} else {
		return $text . $sharing_content;
	}
}
