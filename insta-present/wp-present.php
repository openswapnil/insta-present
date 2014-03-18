<?php
/*
Plugin Name: InstaPresent
Plugin URI: http://wpoets.com/
Description: Enables you to create presentation in WordPress.
Author: Swapnil V. Patil
Version: 0.0.1
Author URI: http://github.com/openswapnil
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

GNU General Public License, Free Software Foundation <http://creativecommons.org/licenses/GPL/2.0/>

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

define( 'insta_present_VERSION', 1 );

/**
 ** WP Present Loader Class
 **
 ** @since 0.9.4
 **/
class insta_present_Loader {

	const OPTION_VERSION  = 'Insta-present-version';
	protected $version = false;

	/* Define and register singleton */
	private static $instance = false;
	public static function instance() {
		if( ! self::$instance ) {
			self::$instance = new self;
		}
		return self::$instance;
	}

	/**
	 * Clone
     *
	 * @since 0.9.0
	 */
	private function __clone() { }

	/**
	 * Constructor
     *
	 * @since 0.9.0
	 */
	private function __construct() {

		// Version Check
		if( $version = get_option( self::OPTION_VERSION, false ) ) {
			$this->version = $version;
		} else {
			$this->version = insta_present_VERSION;
			add_option( self::OPTION_VERSION, $this->version );
		}

		// Load the assets
		require( plugin_dir_path( __FILE__ ) . 'inc/class-Insta-present-core.php' );
		require( plugin_dir_path( __FILE__ ) . 'inc/class-Insta-present-admin.php' );
		require( plugin_dir_path( __FILE__ ) . 'inc/class-Insta-present-settings.php' );
		require( plugin_dir_path( __FILE__ ) . 'inc/class-Insta-present-taxonomy-bridge.php' );

		// Check the things
		//if( isset( $_REQUEST['tag_ID'] ) && isset( $_GET['taxonomy'] ) && insta_present_Core::instance()->taxonomy_slug == $_GET['taxonomy'] ) {

		// @TODO: Use new screen method
		if( is_admin() && ! strpos( $_SERVER['REQUEST_URI'], 'customize.php' ) ) {
			require( plugin_dir_path( __FILE__ ) . 'inc/class-modal-customizer.php' );
		}

		// On Activation
		register_activation_hook( __FILE__, array( $this, 'activate' ) );

		// On Dactivations
		register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );

		// Perform updates if necessary
		add_action( 'init', array( $this, 'action_init_check_version' ) );
	}

	/**
	 * On plugin activation
	 *
	 * @uses flush_rewrite_rules()
	 * @since 0.9.0
	 * @return null
	 */
	public function activate() {
		insta_present_Core::action_init_register_post_type();
		insta_present_Core::action_init_register_taxonomy();
		insta_present_Core::action_init_add_endpoints();
		flush_rewrite_rules();
	}

	/**
	 * On plugin deactivation
	 *
	 * @uses flush_rewrite_rules()
	 * @since 0.9.0
	 * @return null
	 */
	public function deactivate() {
		flush_rewrite_rules();
	}

	/**
	 * Version Check
	 *
	 * @since 0.9.0
	 */
	function action_init_check_version() {
		// Check if the version has changed and if so perform the necessary actions
		if ( ! isset( $this->version ) || $this->version <  insta_present_VERSION ) {

			// Perform updates if necessary

			// Update the version information in the database
			update_option( self::OPTION_VERSION, insta_present_VERSION );
		}
	}

} // Class
insta_present_Loader::instance();

/* Wrappers */
if ( ! function_exists( 'wpp_is_presentation' ) ) {
        function wpp_is_presentation() {
                return insta_present_Core::is_presentation();
        }
}