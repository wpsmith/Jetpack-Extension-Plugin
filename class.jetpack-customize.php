<?php

/**
 * Extension Base Class
 *
 * @author Travis Smith <http://wpsmith.net>
 */

/**
 * Sharing Class
 *
 * @param array $atts Attributes to apply filters.
 */
abstract class JPE_Customize implements JPE_Singleton {

	/**
	 * Holds attributes.
	 *
	 * @var array
	 */
	protected $args;

	/**
	 * Defaults.
	 *
	 * @var array
	 */
	public $defaults = array();

	/**
	 * Class singleton instance.
	 *
	 * @var object
	 */
	protected static $instance;

	/**
	 * Constructor
	 *
	 * @param array $atts Array of attributes.
	 */
	public function __construct( $args = array() ) {

		$this->args = $args;

		if ( method_exists( $this, 'init' ) ) {
			$this->init();
		}

	}

	/**
	 * Get defaults method.
	 *
	 * @return array Array of defaults;
	 */
	abstract public function get_defaults();

	/**
	 * Checks to determine whether an arg has a different value from the default.
	 * @param $arg
	 * @param $value
	 *
	 * @return bool
	 */
	protected function has_changed( $arg ) {

		if ( isset( $this->args[ $arg ] ) &&
		     $this->get_default_value( $arg ) !== $this->args[ $arg ] &&
		     $this->defaults[ $arg ] != $this->args[ $arg ] ) {
			return true;
		}

		return false;
	}

	/**
	 * Get default value of a specific arg.
	 *
	 * @param  string $arg Default argument.
	 * @return mixed       Default argument value.
	 */
	protected function get_default_value( $arg ) {
		if ( isset( $this->defaults[ $arg ] ) ) {
			return $this->defaults[ $arg ];
		}
		return null;
	}

	/**
	 * Changes JetPack module's options
	 *
	 * @param string $module_slug Module slug.
	 * @param array $option_value Module new option values.
	 */
	protected function _change_options( $module_slug, $option_value ) {
		$options = Jetpack_Options::get_option( $module_slug );
		$options = array_merge( $options, $option_value );
		Jetpack_Options::update_option( $module_slug, $options );
	}

	/**
	 * Add filter helper.
	 *
	 * @param string $hook     Hook for function.
	 * @param string $function Function to be hooked.
	 */
	protected function add_filter( $hook, $function ) {
		if ( function_exists( $function ) && is_callable( $function ) ) {
			add_filter( $hook, $function );
		}
	}

}

interface JPE_Singleton {

	public static function get_instance( $args = array() );

}