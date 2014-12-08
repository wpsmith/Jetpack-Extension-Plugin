<?php

/**
 * Sharing Class
 *
 * @author Travis Smith <http://wpsmith.net>
 */

if ( !class_exists( 'JPE_Customize' ) ) {
	require_once( 'class.jetpack-customize.php' );
}

/**
 * Sharing Class
 *
 * @param array $atts Attributes to apply filters.
 */
class JPE_Customize_Sharing extends JPE_Customize {

	/**
	 * Init method.
	 *
	 * @param array $atts Array of attributes.
	 */
	public function init() {

		// Add prefixed sharing shortcode
		add_shortcode( 'jpe_sharing', array( $this, 'shortcode' ) );

		// Add Jetpack Sharing shortcode
		add_shortcode( 'jetpack_sharing', array( $this, 'shortcode' ) );

	}

	/**
	 * Shortcode init method.
	 *
	 * @param array $atts Array of attributes.
	 */
	public function add_filters() {

		// Ensure icons are shown
		remove_all_filters( 'sharing_show' );
		remove_all_filters( 'sharing_enabled' );
		add_filter( 'sharing_show', '__return_true', 999 );

		//* GLOBAL SERVICE FILTERS
		if ( $this->has_changed( 'permalink' ) ) {
			add_filter( 'sharing_permalink', array( $this, 'permalink' ) );
		}

		if ( $this->has_changed( 'title' ) ) {
			add_filter( 'sharing_title', array( $this, 'title' ) );
		}

		if ( $this->has_changed( 'display_link' ) ) {
			add_filter( 'sharing_display_link', array( $this, 'display_link' ) );
		}

		if ( $this->has_changed( 'static_url' ) ) {
			add_filter( 'jetpack_static_url', array( 'static_url' ) );
		}

		if ( $this->has_changed( 'share_counts' ) || $this->has_changed( 'share_counts_twitter' ) || $this->has_changed( 'share_counts_linkedin' ) || $this->has_changed( 'share_counts_facebook' ) ) {
			add_filter( 'jetpack_register_post_for_share_counts', array( 'share_counts' ), 10, 3 );
		}

		if ( $this->has_changed( 'twitter_via' ) ) {
			add_filter( 'jetpack_sharing_twitter_via', array( 'twitter_via' ) );
		}

		if ( $this->has_changed( 'facebook_like_width' ) ) {
			add_filter( 'sharing_facebook_like_widths', array( 'facebook_like_width' ) );
		}

		if ( $this->has_changed( 'sharing_js' ) ) {
			add_filter( 'sharing_enqueue_scripts', '__return_false' );
			add_filter( 'sharing_js', '__return_false' );
		}

		// keeping jetpack translation string on purpose
		if ( $this->has_changed( 'button_style' ) || $this->has_changed( 'sharing_label' ) || $this->has_changed( 'open_links' ) ) {
			add_filter( 'jpe_sharing_global_options', array( $this, 'global_options' ) );
		}

		if ( $this->has_changed( 'twitter_accounts' ) ) {
			add_filter( 'jetpack_sharing_twitter_related', array( $this, 'twitter_accounts' ) );
		}

		if ( $this->has_changed( 'visible' ) || $this->has_changed( 'hidden' ) ) {
			add_filter( 'sharing_services_enabled', array( $this, 'sharing_services_enabled' ) );
		}

	}

	/**
	 * Custom Jetpack shortcode.
	 *
	 * @param $atts
	 * @param $content
	 *
	 * @return string|void
	 */
	public function shortcode( $atts, $content ) {

		// Get defaults
		$defaults = $this->defaults = $this->get_defaults();

		// Add filters
		$atts       = shortcode_atts( $defaults, $atts );
		$this->args = $atts;

		$this->add_filters();

		// Do sharing icons
		if ( function_exists( 'jpe_sharing_display' ) ) {
			return jpe_sharing_display( $content, false );
		} elseif ( function_exists( 'sharing_display', false ) ) {
			return jpe_sharing_display( $content );
		} else {
			return __( 'Something went wrong...', JPE_PLUGIN_DOMAIN );
		}

	}

	/**
	 * Get defaults method.
	 *
	 * @return array Array of defaults;
	 */
	public function get_defaults() {
		$options  = get_option( 'sharing-options' );

		return array(
			'permalink'             => '',
			'title'                 => '',
			'display_link'          => '',
			'static_url'            => '',
			'share_counts'          => 1,
			'share_counts_twitter'  => 1,
			'share_counts_linkedin' => 1,
			'share_counts_facebook' => 1,
			'twitter_via'           => '',
			'twitter_accounts'      => '',
			'facebook_like_width'   => 90,
			'sharing_js'            => 1,
			'button_style'          => $options['global']['button_style'],
			'sharing_label'         => $options['global']['sharing_label'],
			'open_links'            => $options['global']['open_links'],
			'visible'               => '',
			'hidden'                => '',
		);
	}

	/**
	 * Permalink URL being shared.
	 *
	 * Sample: add_filter( 'sharing_permalink', 'jpe_sharing_permalink', 10, 3 );
	 *
	 * @param $permalink string Permalink
	 * @param $post_id   int    Post ID.
	 * @param $id        int    Service ID.
	 *
	 * @return string
	 */
	public function permalink( $permalink ) {
		return $this->args['permalink'];
	}

	/**
	 * Post/Page Title.
	 *
	 * Sample: add_filter( 'sharing_title, 'jpe_sharing_title', 10, 3 );
	 *
	 * @param $post_title string Post Title.
	 * @param $post_id    int    Post ID.
	 * @param $id         int    Service ID.
	 *
	 * @return string
	 */
	public function title( $title ) {
		return $this->args['title'];
	}

	/**
	 * Sharing display link URL around icon.
	 *
	 * Sample: add_filter( 'sharing_display_link, 'jpe_sharing_display_link' );
	 *
	 * @param $url string URL.
	 */
	public function display_link( $display_link ) {
		return $this->args['display_link'];
	}

	/**
	 * Loading image url.
	 *
	 * Sample: add_filter( 'jetpack_static_url', 'jpe_sharing_static_url' );
	 *
	 * @param string $url URL string.
	 *
	 * @return string
	 */
	public function static_url( $url ) {
		return $this->args['static_url'];
	}

	/**
	 * Whether share counts are being shown.
	 *
	 * Sample: add_filter( 'jetpack_register_post_for_share_counts', 'jpe_sharing_register_post_for_share_counts', 10, 3 );
	 *
	 * @param bool   $show_counts Whether to show counts, default true.
	 * @param int    $post_id     Post ID.
	 * @param string $service     Sharing service slug.
	 *
	 * @return bool
	 */
	public function share_counts( $show_counts, $post_id, $service ) {
		switch ( $service ) {
			case 'twitter':
				if ( !$this->args['share_counts_twitter'] ) {
					return false;
				}
				return $this->args['share_counts'];
			case 'linkedin':
				if ( !$this->args['share_counts_linkedin'] ) {
					return false;
				}
				return $this->args['share_counts'];
			case 'facebook':
				if ( !$this->args['share_counts_facebook'] ) {
					return false;
				}
				return $this->args['share_counts'];
			default:
				return $this->args['share_counts'];
		}

		return $show_counts;
	}

	/**
	 * Array of visible and hidden services.
	 *
	 * Sample: add_filter( 'sharing_services_enabled', 'jpe_sharing_services_enabled' );
	 *
	 * $enabled['visible'|'hidden'][$service]
	 *
	 * @param array $enabled Array of visible/hidden services.
	 */
	public function sharing_services_enabled( $enabled ) {

		$options  = get_option( 'sharing-options' );
		$enabled  = get_option( 'sharing-services' );
		$services = $this->get_all_services();

		if ( ! is_array( $options ) ) {
			$options = array( 'global' => $this->get_global_options() );
		}

		$global = $options['global'];

		if ( !is_array( $enabled ) ) {
			$enabled = array(
				'visible' => array(),
				'hidden' => array()
			);

			if ( '' !== $this->args['visible'] ) {
				$enabled['visible'] = explode( ',', $this->args['visible'] );
			}
			if ( '' !== $this->args['hidden'] ) {
				$enabled['hidden'] = explode( ',', $this->args['hidden'] );
			}

		}

		// Cleanup after any filters that may have produced duplicate services
		$enabled['visible'] = array_unique( $enabled['visible'] );
		$enabled['hidden']  = array_unique( $enabled['hidden'] );

		// Form the enabled services
		$blog = array( 'visible' => array(), 'hidden' => array() );

		foreach ( $blog AS $area => $stuff ) {
			foreach ( (array)$enabled[$area] AS $service ) {
				if ( isset( $services[$service] ) ) {
					$blog[$area][$service] = new $services[$service]( $service, array_merge( $global, isset( $options[$service] ) ? $options[$service] : array() ) );
				}
			}
		}

		return $blog;
	}

	/**
	 * Default global options from 'sharing-options'.
	 * $options = get_option( 'sharing-options' );
	 * $options['global'] = array(
	 *        'button_style'  => 'icon-text',                  // Options: 'icon-text', 'icon', 'text', 'official'
	 *        'sharing_label' => $this->default_sharing_label, // __( 'Share this:', 'jetpack' )
	 *        'open_links'    => 'same',                       // 'new', 'same'
	 *        'show'          => array( 'post', 'page' ),
	 *        'custom'        => isset( $options['global']['custom'] ) ? $options['global']['custom'] : array()
	 *    );
	 *
	 * Sample: add_filter( 'sharing_default_global', 'jpe_sharing_default_global', $options['global'] );
	 *
	 * @param  array $options Array of global options.
	 * @return array $options Modified array of global options.
	 */
	public function global_options( $options ) {

		$_options = array();
		if ( $options['button_style'] !== $this->args['button_style'] && in_array( $this->args['button_style'], array( 'icon-text', 'icon', 'text', 'official', ) ) ) {
			$_options['button_style'] = $this->args['button_style'];
		}

		if ( $options['sharing_label'] !== $this->args['sharing_label'] ) {
			$_options['sharing_label'] = $this->args['sharing_label'];
		} elseif ( $options['sharing_label'] !== $this->args['label'] ) {
			$_options['sharing_label'] = $this->args['label'];
		}

		if ( $options['open_links'] !== $this->args['open_links'] && in_array( $this->args['open_links'], array( 'new', 'same', ) ) ) {
			$_options['open_links'] = $this->args['open_links'];
		}

		return array_merge( $options, $_options );
	}

	/**
	 * Customize Twitter's via tag.
	 *
	 * Sample: add_filter( 'jetpack_sharing_twitter_via', 'jpe_sharing_twitter_via', 10, 2 );
	 *
	 * @param string $tag     Tag.
	 * @param int    $post_id Post ID.
	 *
	 * @return
	 */
	public function twitter_via( $via ) {
		return $this->args['twitter_via'];
	}

	/**
	 * Related twitter accounts with description. Do NOT use commas (,).
	 * Format is 'username' => 'Optional description'
	 *
	 * @param array $tag     Tag.
	 * @param int   $post_id Post ID.
	 */
	public function twitter_accounts( $accounts ) {
		return explode( ',', $this->args['twitter_accounts'] );
	}

	/**
	 * Facebook widths.
	 *
	 * Sample: add_filter( 'sharing_facebook_like_widths', 'jpe_sharing_facebook_like_widths' );
	 *
	 * Defaults:
	 *    $inner_w = 90;
	 *
	 *    // Locale-specific widths/overrides
	 *    $widths = array(
	 *    'bg_BG' => 120,
	 *    'bn_IN' => 100,
	 *    'cs_CZ' => 135,
	 *    'de_DE' => 120,
	 *    'da_DK' => 120,
	 *    'es_ES' => 122,
	 *    'es_LA' => 110,
	 *    'fi_FI' => 100,
	 *    'it_IT' => 100,
	 *    'ja_JP' => 100,
	 *    'pl_PL' => 100,
	 *    'nl_NL' => 130,
	 *    'ro_RO' => 100,
	 *    'ru_RU' => 128,
	 *    );
	 *
	 * @param int $widths Width.
	 */
	public function facebook_like_width( $width ) {
		return $this->args['facebook_like_width'];
	}

	/**
	 * Returns the singleton instance of the class.
	 *
	 * @since 1.0.0
	 *
	 * @return object The class object.
	 */
	public static function get_instance( $args = array() ) {

		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof JPE_Customize_Sharing ) ) {
			self::$instance = new JPE_Customize_Sharing( $args );
		}

		return self::$instance;

	}

}

//* Share_Email FILTERS
/**
 * Email sharing check.
 *
 * Class: Share_Email
 *
 * @param bool    $check     True
 * @param WP_Post $post      Post Object
 * @param array   $post_data Post Object
 *
 * @return bool
 */
//add_filter( 'sharing_email_check, 'jpe_sharing_email_check', 10, 3 );

/**
 * Modify the data; Whether there is data to be sent.
 *
 * Class: Share_Email
 *
 * $data = array(
 *     'post'   => $post,
 *     'source' => $source_email,
 *     'target' => $target_email,
 *     'name'   => $source_name
 * );
 *
 * @param array $data Email data array
 *
 * @return array
 */
//add_filter( 'sharing_email_can_send, 'jpe_sharing_email_can_send' );

/**
 * Sharing email "sent" action hook.
 * Hook fires after the email has been processed regardless of whether it is actually sent.
 *
 * @param array $data         Array of post data.
 *                            $data = array(
 *                            'post'   => $post,
 *                            'source' => $source_email,
 *                            'target' => $target_email,
 *                            'name'   => $source_name
 *                            );
 *
 * @return array
 */
//add_action( 'sharing_email_send_post', 'jpe_sharing_email_send_post' );

//* Share_Twitter FILTERS
/**
 * Customize Twitter's via tag.
 * Later filtered by jetpack_sharing_twitter_via
 *
 * @param string $tag Tag.
 * @param        deprecated
 *
 * @return string
 */
//add_filter( 'jetpack_twitter_cards_site_tag', 'jpe_sharing_twitter_cards_site_tag' );

//* TEMPLATE TAG FILTERS
/**
 * Pass through a filter for final say so
 *
 * @param bool    $show Whether to show the sharing icons.
 * @param WP_Post $post Post object.
 *
 * @return bool
 */
//add_filter( 'sharing_show', 'sharing_show' );

/**
 * Sharing services enabled.
 *
 * @param array $enabled Contains properties for all, visible, & hidden
 */
//add_filter( 'sharing_enabled', 'jpe_sharing_enabled' );

/**
 * Sharing services.
 *
 * Services also held in options array: get_option( 'sharing-services' )
 *
 * Defaults:
 *    $services = array(
 *      'slug'          => 'Share_Class',
 *        'email'         => 'Share_Email',
 *        'print'         => 'Share_Print',
 *        'facebook'      => 'Share_Facebook',
 *        'linkedin'      => 'Share_LinkedIn',
 *        'reddit'        => 'Share_Reddit',
 *        'stumbleupon'   => 'Share_Stumbleupon',
 *        'twitter'       => 'Share_Twitter',
 *        'press-this'    => 'Share_PressThis',
 *        'google-plus-1' => 'Share_GooglePlus1',
 *        'tumblr'        => 'Share_Tumblr',
 *        'pinterest'     => 'Share_Pinterest',
 *        'pocket'        => 'Share_Pocket',
 *    );
 *
 * @param array $services Array of services.
 */
//add_filter( 'sharing_services', 'jpe_sharing_services' );

/**
 * Array of default enabled services.
 *
 * $enabled = array(
 *        'visible' => array(),
 *        'hidden' => array()
 *    );
 *
 * @param array $enabled Array of enabled services.
 */
//add_filter( 'sharing_default_services', 'jpe_sharing_default_services' );

/**
 * Whether to enqueue scripts.
 * If this is true, then there is no need to hook sharing_js as false.
 *
 * @param bool $do_js Whether to enqueue script.
 *
 * @return bool
 */
//add_filter( 'sharing_enqueue_scripts', 'jpe_sharing_enqueue_scripts' );

/**
 * Whether to do footer init script.
 * If this is false and sharing_enqueue_scripts is true, then you need to do custom footer scripts.
 *
 * @param bool $do_js Whether to enqueue script.
 */
//add_filter( 'sharing_js', 'jpe_sharing_js' );
