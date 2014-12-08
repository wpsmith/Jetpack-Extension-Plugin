<?php

/**
 * Related Posts Class
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
class JPE_RP_Customize_Related_Posts extends JPE_Customize {

	/**
	 * Init method
	 *
	 * @param array $atts Array of attributes.
	 */
	public function init() {
		$this->defaults = $this->get_defaults();

		if (
			$this->has_changed( 'size' ) ||
			$this->has_changed( 'show_headline' ) ||
			$this->has_changed( 'show_thumbnails' ) ) {
			$this->change_options();
		}

		if ( $this->has_changed( 'headline' ) ) {
			add_filter( 'jetpack_relatedposts_filter_headline', array( $this, 'headline' ) );
		}

		if ( $this->has_changed( 'exclude_post_ids' ) ) {
			add_filter( 'jetpack_relatedposts_filter_exclude_post_ids', array( $this, 'exclude_post_ids' ) );
		}

		if ( $this->has_changed( 'post_type' ) ) {
			add_filter( 'jetpack_relatedposts_filter_post_type', array( $this, 'post_type' ) );
		}

		if ( $this->has_changed( 'date_range' ) ) {
			add_filter( 'jetpack_relatedposts_filter_date_range', array( $this, 'date_range' ) );
		}

		if ( $this->has_changed( 'thumbnail_size' ) ) {
			add_filter( 'jetpack_relatedposts_filter_thumbnail_size', array( $this, 'thumbnail_size' ) );
		}

		if ( $this->has_changed( 'has_terms' ) ) {
			add_filter( 'jetpack_relatedposts_filter_has_terms', array( $this, 'has_terms' ) );
		}

		if ( $this->has_changed( 'post_context_cb' ) ) {
			$this->add_filter( 'jetpack_relatedposts_filter_post_context', $this->args['post_context_cb'] );
		}

		if ( $this->has_changed( 'post_category_context_cb' ) ) {
			add_filter( 'jetpack_relatedposts_post_category_context', $this->args['post_category_context_cb'] );
		}

		if ( $this->has_changed( 'post_tag_context_cb' ) ) {
			add_filter( 'jetpack_relatedposts_post_tag_context', $this->args['post_tag_context_cb'] );
		}

	}

	/**
	 * Get defaults array.
	 *
	 * @return array Array of defaults.
	 */
	public function get_defaults() {
		return array(
			'show_headline'            => 1,
			'show_thumbnails'          => 1,
			'thumbnail_size'           => array(
				'width'  => 350,
				'height' => 200,
			),
			'size'                     => 3,
			'headline'                 => '',
			'post_type'                => get_post_type( get_the_ID() ),
			'date_range'               => array(),
			'exclude_post_ids'         => array(),
			'has_terms'                => array(),

			// Callbacks
			'post_context_cb'          => '',
			'post_category_context_cb' => '',
			'post_tag_context_cb'      => '',

			// Holder for future development
			'returned_results'   => null,
			'hits'               => null,

		);
	}

	/**
	 * Changes relatedposts options
	 */
	public function change_options() {

		$options = array();
		if ( $this->has_changed( 'show_headline' ) ) {
			$options['show_headline'] = $this->args['show_headline'];
		}
		if ( $this->has_changed( 'show_thumbnails' ) ) {
			$options['show_thumbnails'] = $this->args['show_thumbnails'];
		}
		if ( $this->has_changed( 'size' ) ) {
			$options['size'] = $this->args['size'];
		}

		$this->_change_options( 'relatedposts', $options );

	}

	/**
	 * Filter headline.
	 *
	 * @param  string $headline HTML headline string.
	 * @return string           Modified HTML headline string.
	 */
	public function headline( $headline ) {
		return $this->args['headline'];
	}

	/**
	 * Filter thumbnail size.
	 *
	 * @param  string $headline HTML headline string.
	 * @return string           Modified HTML headline string.
	 */
	public function thumbnail_size( $headline ) {
		return $this->args['thumbnail_size'];
	}

	/**
	 * Filter posts exclusions.
	 *
	 * @param array $exclude_post_ids Array of post IDs to exclude.
	 * @return array                   Modified array of post IDs to exclude.
	 */
	function exclude_post_ids( $exclude_post_ids ) {
		return array_merge( $exclude_post_ids, (array) $this->args['exclude_post_ids'] );
	}

	/**
	 * Filter date range.
	 *
	 * Example:
	 *      array(
	 *			'from' => strtotime( '-6 months' ),
	 *			'to'   => time(),
	 *		)
	 *
	 * @param  string $date_range HTML headline string.
	 * @return array              Array of date ranges.
	 *
	 */
	public function date_range( $date_range ) {
		return $this->args['date_range'];
	}

	/**
	 * Generates a context for the category of the related content.
	 * Order of importance:
	 *   - First category (Not 'Uncategorized')
	 *   - First post tag
	 *   - Number of comments
	 *
	 * Filtered by jetpack_relatedposts_filter_post_context
	 * Default: sprintf(
	 *		_x( 'In "%s"', 'in {category/tag name}', 'jetpack' ),
	 *		$category->name
	 *	);
	 *
	 * @param  string $context Context string.
	 * @return string          Modified context string.
	 */
	public function callback() {
	}

	/**
	 * Returns the singleton instance of the class.
	 *
	 * @since 1.0.0
	 *
	 * @return object The class object.
	 */
	public static function get_instance( $args = array() ) {

		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof JPE_RP_Customize_Related_Posts ) ) {
			self::$instance = new JPE_RP_Customize_Related_Posts( $args );
		}

		return self::$instance;

	}

}





/*
 * Filters args for use in ElasticSearch filters.
 *
 * $defaults = array(
		'size'             => (int)$options['size'],
		'post_type'        => get_post_type( $post_id ),
		'has_terms'        => array(),
		'date_range'       => array(),
		'exclude_post_ids' => array(),
	);
 *
 */
// add_filter( 'jetpack_relatedposts_filter_args', 'jpe_related_posts_filter_args', 10, 2 );

/**
 * Filters filtered results of parsed args for use in ElasticSearch filters.
 *
 */
// add_filter( 'jetpack_relatedposts_filter_filters', 'jpe_related_posts_filter_filters', 10, 2 );

/**
 * Filters the returned results (array of data populated related posts).
 */
// add_filter( 'jetpack_relatedposts_returned_results', 'jpe_related_posts_returned_results', 10, 2 );

/**
 * Filters the hits (array of post IDs).
 */
// add_filter( 'jetpac0k_relatedposts_filter_hits', 'jpe_related_posts_filter_hits', 10, 2 );


