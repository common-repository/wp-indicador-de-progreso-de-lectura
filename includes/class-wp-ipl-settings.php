<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class WP_IPL_Settings {

	/**
	 * The single instance of WP_IPL_Settings.
	 * @var 	object
	 * @access  private
	 * @since 	1.0.0
	 */
	private static $_instance = null;

	/**
	 * The main plugin object.
	 * @var 	object
	 * @access  public
	 * @since 	1.0.0
	 */
	public $parent = null;

	/**
	 * Prefix for plugin settings.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $base = '';

	/**
	 * Available settings for plugin.
	 * @var     array
	 * @access  public
	 * @since   1.0.0
	 */
	public $settings = array();

	public function __construct ( $parent ) {
		$this->parent = $parent;

		$this->base = 'wpipl_';

		// Initialise settings
		add_action( 'init', array( $this, 'init_settings' ), 11 );

		// Register plugin settings
		add_action( 'admin_init' , array( $this, 'register_settings' ) );

		// Add settings page to menu
		add_action( 'admin_menu' , array( $this, 'add_menu_item' ) );

		// Add settings link to plugins page
		add_filter( 'plugin_action_links_' . plugin_basename( $this->parent->file ) , array( $this, 'add_settings_link' ) );

	}

	/**
	 * Initialise settings
	 * @return void
	 */
	public function init_settings () {
		$this->settings = $this->settings_fields();
	}

	/**
	 * Add settings page to admin menu
	 * @return void
	 */
	public function add_menu_item () {
		$page = add_options_page( __( 'Reading Progress Indicator', WPIPL_TEXTDOMAIN ) , __( 'Reading Progress Indicator', WPIPL_TEXTDOMAIN ) , 'manage_options' , $this->parent->_token . '_settings' ,  array( $this, 'settings_page' ) );
		add_action( 'admin_print_styles-' . $page, array( $this, 'settings_assets' ) );
	}

	/**
	 * Load settings JS & CSS
	 * @return void
	 */
	public function settings_assets () {

		// We're including the farbtastic script & styles here because they're needed for the colour picker
		// If you're not including a colour picker field then you can leave these calls out as well as the farbtastic dependency for the wpt-admin-js script below
		wp_enqueue_style( 'farbtastic' );
		wp_enqueue_script( 'farbtastic' );

		// We're including the WP media scripts here because they're needed for the image upload field
		// If you're not including an image upload then you can leave this function call out
		wp_register_script( $this->parent->_token . '-settings-js', $this->parent->assets_url . 'js/settings' . $this->parent->script_suffix . '.js', array( 'farbtastic', 'jquery' ), '1.0.0' );
		wp_enqueue_script( $this->parent->_token . '-settings-js' );
	}

	/**
	 * Add settings link to plugin list table
	 * @param  array $links Existing links
	 * @return array 		Modified links
	 */
	public function add_settings_link ( $links ) {
		$settings_link = '<a href="options-general.php?page=' . $this->parent->_token . '_settings">' . __( 'Setting', WPIPL_TEXTDOMAIN ) . '</a>';
  		array_push( $links, $settings_link );
  		return $links;
	}

	/**
	 * Build settings fields
	 * @return array Fields to be displayed on settings page
	 */
	private function settings_fields () {

		$settings['inicio'] = array(
				'title'					=> '',
				'description'			=> __( 'Welcome to the WordPress plugin that will allow you to display a reading tracking bar of the current post.', WPIPL_TEXTDOMAIN ),
				'fields'				=> array(
						array(
								'id' 			=> 'color_barra_progreso',
								'label'			=> __( 'Color of Progress Bar', WPIPL_TEXTDOMAIN ),
								'description'	=> __( 'Select the color for the progress bar.', WPIPL_TEXTDOMAIN ),
								'type'			=> 'color',
								'default'		=> '#FF0000',
								'callback'	=> 'sanitize_hex_color'
						),
				)
		);

		$settings = apply_filters( $this->parent->_token . '_settings_fields', $settings );

		return $settings;

	}

	/**
	 * Register plugin settings
	 * @return void
	 */
	public function register_settings () {
		if ( is_array( $this->settings ) ) {

			foreach ( $this->settings as $section => $data ) {

				// Add section to page
				add_settings_section( $section, $data['title'], array( $this, 'settings_section' ), $this->parent->_token . '_settings' );

				foreach ( $data['fields'] as $field ) {

					// Validation callback for field
					$validation = '';
					if ( isset( $field['callback'] ) ) {
						$validation = $field['callback'];
					}

					// Register field
					$option_name = $this->base . $field['id'];
					register_setting( $this->parent->_token . '_settings', $option_name, $validation );

					// Add field to page
					add_settings_field( $field['id'], $field['label'], array( $this->parent->admin, 'display_field' ), $this->parent->_token . '_settings', $section, array( 'field' => $field, 'prefix' => $this->base ) );
				}

			}
		}
	}

	public function settings_section ( $section ) { ?>
		<p>
			<?php
			echo esc_html($this->settings[ $section['id'] ]['description']); ?>
		</p>
	<?php
	}

	/**
	 * Load settings page content
	 * @return void
	 */
	public function settings_page () { ?>

		<div class="wrap" id="<?php echo esc_attr($this->parent->_token.'_settings'); ?> ">
		<h2> <?php esc_html_e('WP Reading Progress Indicator' , WPIPL_TEXTDOMAIN ); ?> </h2>
		<div>
			<form method="post" action="options.php" enctype="multipart/form-data">
				<?php
				settings_fields( $this->parent->_token . '_settings' );
				$this->do_settings_sections( $this->parent->_token . '_settings' ); ?>
			</form>
		</div>
	<?php
	}


	function do_settings_sections( $page ) {
		global $wp_settings_sections, $wp_settings_fields;

		if ( ! isset( $wp_settings_sections[$page] ) )
			return;

		foreach ( (array) $wp_settings_sections[$page] as $section ) {
			if ( $section['title'] ): ?>
				<h3><?php echo  esc_html($section['title']); ?> </h3>
			<?php
			endif;

			if ( $section['callback'] )
				call_user_func( $section['callback'], $section );

			if ( ! isset( $wp_settings_fields ) || !isset( $wp_settings_fields[$page] ) || !isset( $wp_settings_fields[$page][$section['id']] ) )
				continue;

			?>
			<table class="form-table">
				<?php
				$this->do_settings_fields( $page, $section['id'] ); ?>
			</table>
			<input name="Submit" type="submit" class="button-primary" value="<?php  esc_attr_e( 'Save Changes' , WPIPL_TEXTDOMAIN ); ?>" />
			<?php
		}
	}


	function do_settings_fields($page, $section) {
		global $wp_settings_fields;

		if ( ! isset( $wp_settings_fields[$page][$section] ) )
			return;


		foreach ( (array) $wp_settings_fields[$page][$section] as $field ) {

			$class = false;

			if ( !empty( $field['args']['class'] ) ):
				$class =  $field['args']['class'];
			endif;?>

			<tr class="<?php echo esc_attr($class); ?>">

				<?php
				$label_for = false;
				if (!empty( $field['args']['label_for'] ) ):
					$label_for = $field['args']['label_for'];
				endif;

				$title = false;
				if (!empty ($field['title']) ):
					$title = $field['title'];
				endif; ?>

				<th scope="col">
					<?php
					if ($label_for): ?>
						<label for="<?php echo  esc_attr( $label_for ); ?>">
							<?php echo  esc_html($title); ?>
						</label>

					<?php
					else:
						echo  esc_html($title);

					endif; ?>
				</th>
			</tr>

			<tr class="<?php echo esc_attr($class); ?>">
				<td>
					<?php
					call_user_func($field['callback'], $field['args']); ?>
				</td>
			</tr>

		<?php

		}

		// Una vez mostrados el campo para seleccionar el colorpicker
		// ponemos el boton de grabar debajo , y un bloque de infomración del plugin
		?>
		<tr><td>
			<div class="">
				<input  type="submit" class="button-primary" value="<?php  esc_attr_e( 'Save Changes' , WPIPL_TEXTDOMAIN ); ?>" />
			</div>
		</td></tr>

		<tr>
			<td>
				<style media="screen">
					.wp-ipl-info{
						display: grid;
						grid-template-columns: 1fr 1fr;
					}
					.wp-ipl-soporte{
						grid-column-start: 1;
						grid-column-end: 3;
						grid-row-start: 1;
						grid-row-end: span 2;
					}
					.cinco-estrellas:before {
						font-family: "dashicons";
						color: #fddb5a;
						content: "\f155\f155\f155\f155\f155";
					}
				</style>

				<div class="wp-ipl-info">

					<div class="wp-ipl-soporte" >
						<h3><?php esc_html_e('Support', WPIPL_TEXTDOMAIN); ?></h3>
						<p> <?php esc_html_e('Do you have any questions or suggestions? Here are some links that can help you.', WPIPL_TEXTDOMAIN); ?></p>
						<ul>
							<li><a target="_blank" href="https://mispinitoswp.wordpress.com/contacto/"><?php esc_html_e('Suggest improvements', WPIPL_TEXTDOMAIN); ?></a></li>
							<li><a target="_blank" href="https://wordpress.org/support/plugin/wp-indicador-de-progreso-de-lectura"><?php esc_html_e('Report a Bug', WPIPL_TEXTDOMAIN); ?></a></li>
						</ul>
					</div>

					<div class="">
						<h3><?php esc_html_e('Rate the plugin', WPIPL_TEXTDOMAIN); ?> <span class="cinco-estrellas"></span></h3>
						<p>
						<?php
						printf(__('Do you like the plugin? Are you using it on your website? Well, you can rate the plugin in <a href="%s" target="_blank">WordPress.org</a>, that I would be very grateful to you :-)', WPIPL_TEXTDOMAIN )
						, esc_url('https://wordpress.org/support/view/plugin-reviews/wp-indicador-de-progreso-de-lectura?filter=5') );
						?>
						</p>
					</div>

					<div class="">
						<h3><?php esc_html_e('Invite me to a coffee', WPIPL_TEXTDOMAIN); ?></h3>
						<p><?php esc_html_e('Well that, push the button to give me a good dose of caffeine.', WPIPL_TEXTDOMAIN); ?></p>
						<p>
							<a href="https://www.paypal.me/jcglp/1.5" title="<?php esc_attr_e('Invite me to a coffee', WPIPL_TEXTDOMAIN); ?>" target="_blank">
							<img src="<?php echo esc_url(WPIPL_URL . '/assets/images/btn_donate_LG.gif'); ?>" alt="paypal logo">
							</a>
						</p>
						</p>
					</div>
				</div>
			</td>
		</tr>
		<?php

	}

	/**
	 * Main WP_IPL_Settings Instance
	 *
	 * Ensures only one instance of WP_IPL_Settings is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @see WP_IPL()
	 * @return Main WP_IPL_Settings instance
	 */
	public static function instance ( $parent ) {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self( $parent );
		}
		return self::$_instance;
	} // End instance()

	/**
	 * Cloning is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __clone () {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), $this->parent->_version );
	} // End __clone()

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __wakeup () {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), $this->parent->_version );
	} // End __wakeup()

}
