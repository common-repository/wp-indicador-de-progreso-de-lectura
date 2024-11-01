<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class WP_IPL {

	/**
	 * The single instance of WP_IPL.
	 * @var 	object
	 * @access  private
	 * @since 	1.0.0
	 */
	private static $_instance = null;

	/**
	 * Settings class object
	 * @var     object
	 * @access  public
	 * @since   1.0.0
	 */
	public $settings = null;

	/**
	 * The version number.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $_version;

	/**
	 * The token.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $_token;

	/**
	 * The main plugin file.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $file;

	/**
	 * The main plugin directory.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $dir;

	/**
	 * The plugin assets directory.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $assets_dir;

	/**
	 * The plugin assets URL.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $assets_url;

	/**
	 * Suffix for Javascripts.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $script_suffix;

	/**
	 * Constructor function.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function __construct ( $file = '', $version = '1.0.0' ) {
		$this->_version = $version;
		$this->_token = 'wpipl';

		// Load plugin environment variables
		$this->file = $file;
		$this->dir = dirname( $this->file );
		$this->assets_dir = trailingslashit( $this->dir ) . 'assets';
		$this->assets_url = esc_url( trailingslashit( plugins_url( '/assets/', $this->file ) ) );

		$this->script_suffix = WPIPL_SCRIPT_SUFFIX;

		register_activation_hook( $this->file, array( $this, 'install' ) );

		// Load frontend JS & CSS
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ), 10 );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ), 10 );


		add_action( 'plugins_loaded', array($this, 'load_plugin_textdomain'));

		// Load API for generic admin functions
		if ( is_admin() ) {
			$this->admin = new WP_IPL_Admin_API();
		}

    // Link para donar en la lista de plugins
    add_filter( 'plugin_row_meta', array( $this, 'donate_link'), 10, 2 );

	} // End __construct ()



	/**
	 * Load frontend CSS.
	 * @access  public
	 * @since   1.0.0
	 * @return void
	 */
	public function enqueue_styles () {
	  // Solo se muestra en entradas, donde se muestra la barra de seguimietno
    if ( is_single() ) {
      wp_enqueue_style( 'wpipl-style', esc_url( $this->assets_url ) . 'css/wpipl_style' . WPIPL_SCRIPT_SUFFIX . '.css' , array(), $this->_version);

    }
	} // End enqueue_styles ()

	/**
	 * Load frontend Javascript.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function enqueue_scripts () {
		if ( is_single() ) {
			//error_log( '=>' . get_option( 'wpipl_color_barra_progreso' ) );
			wp_register_script( 'wpipl-script', esc_url( $this->assets_url ) . 'js/wpipl_scripts' . WPIPL_SCRIPT_SUFFIX . '.js',  array(), $this->_version, true );
			// Localize the script with new data
			$wpipl_opciones = array(	'wpipl_color' => esc_attr(get_option( 'wpipl_color_barra_progreso' )),	);
			wp_localize_script( 'wpipl-script', 'wpipl_parametros', $wpipl_opciones );
			wp_enqueue_script( 'wpipl-script');

		}

	} // End enqueue_scripts ()


	function load_plugin_textdomain() {
		load_plugin_textdomain( WPIPL_TEXTDOMAIN, FALSE, basename( dirname (dirname( __FILE__ )) ) . '/languages/' );

	}

	public function donate_link($links, $file) {

		if ( dirname( $file ) == plugin_basename($this->dir) ) {
			$links[] = '<a href="https://www.paypal.me/jcglp/1.5" target="_blank">' . esc_html__('Donate', WPIPL_TEXTDOMAIN) . '</a>';
		}
		return $links;
	}

	/**
	 * Main WP_IPL Instance
	 *
	 * Ensures only one instance of WP_IPL is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @see WP_IPL()
	 * @return Main WPIPL_VERSION instance
	 */
	public static function instance ( $file = '', $version = '1.0.0' ) {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self( $file, $version );
		}
		return self::$_instance;
	} // End instance ()

	/**
	 * Cloning is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __clone () {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), $this->_version );
	} // End __clone ()

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __wakeup () {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), $this->_version );
	} // End __wakeup ()

	/**
	 * Installation. Runs on activation.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function install () {
		// Color por defecto para la barra
		update_option( $this->_token . '_color_barra_progreso', '#FF0000' );
		$this->_log_version_number();

	} // End install ()

	/**
	 * Log the plugin version number.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	private function _log_version_number () {
		update_option( $this->_token . '_version', $this->_version );

	} // End _log_version_number ()

}
