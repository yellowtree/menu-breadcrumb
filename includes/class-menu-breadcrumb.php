<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the dashboard.
 *
 * @link       https://github.com/jchristopher/menu-breadcrumb
 * @since      1.0.0
 *
 * @package    Menu_Breadcrumb
 * @subpackage Menu_Breadcrumb/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, dashboard-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Menu_Breadcrumb
 * @subpackage Menu_Breadcrumb/includes
 * @author     Jonathan Christopher <jonathan@mondaybynoon.com>
 */
class Menu_Breadcrumb {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Menu_Breadcrumb_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;


	protected $menu_id;
	protected $menu_items;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the Dashboard and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct( $menu_id = '' ) {

		$this->plugin_name = 'menu-breadcrumb';
		$this->version = '1.0.0';
		$this->menu_id = $menu_id;
		$this->menu_items = wp_get_nav_menu_items( $this->menu_id );

		$this->load_dependencies();
		$this->set_locale();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Menu_Breadcrumb_Loader. Orchestrates the hooks of the plugin.
	 * - Menu_Breadcrumb_i18n. Defines internationalization functionality.
	 * - Menu_Breadcrumb_Admin. Defines all hooks for the dashboard.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-menu-breadcrumb-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-menu-breadcrumb-i18n.php';

		$this->loader = new Menu_Breadcrumb_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Menu_Breadcrumb_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Menu_Breadcrumb_i18n();
		$plugin_i18n->set_domain( $this->get_plugin_name() );

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Menu_Breadcrumb_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

	/**
	 * Format a URL such that we can compare it easily. Escapes, removes protocol, and removes trailing slash
	 *
	 * @since       1.0.0
	 * @param       string          $url URL to format
	 * @return      mixed|string    Formatted URL (no protocol or trailing slash)
	 */
	public function format_url( $url = '' ) {
		$url = esc_url( $url );
		$url = str_replace( array( 'http://', 'https://' ), '', strtolower( $url ) );
		$url = rtrim( $url, '/' );

		return $url;
	}

	/**
	 * Check to see if the page being viewed matches the submitted URL. Requires that we're viewing a WordPress permalink.
	 *
	 * @since       1.0.0
	 * @param       string $url     The URL to check against the current URL
	 * @return      bool            Whether we are viewing the submitted URL
	 */
	public function is_at_url( $url = '' ) {
		global $post;

		if ( empty( $url ) ) {
			return false;
		}

		$url = $this->format_url( $url );
		$current_url = $this->format_url( get_permalink( $post->ID ) );

		return $current_url == $url;

	}

	/**
	 * Retrieve the current Menu item object for the current Menu.
	 *
	 * @since       1.0.0
	 * @return      bool|WP_Post    The current Menu item
	 */
	public function get_current_menu_item_object() {

		$current_menu_item = false;

		// loop through the entire nav menu and determine whether any have a class="current" or are the current URL (e.g. a Custom Link was used)
		foreach ( $this->menu_items as $menu_item ) {

			// if WordPress was able to detect the current page
			if ( is_array( $menu_item->classes ) && in_array( 'current', $menu_item->classes ) ) {
				$current_menu_item = $menu_item;
			}

			// if the current URL matches a Custom Link
			if ( ! $current_menu_item && isset( $menu_item->url ) && $this->is_at_url( $menu_item->url ) ) {
				$current_menu_item = $menu_item;
			}

			if ( $current_menu_item ) {
				break;
			}
		}

		return $current_menu_item;
	}

	/**
	 * Retrieve the current Menu item object's parent Menu item object
	 *
	 * @since       1.0.0
	 * @param       WP_Post $current_menu_item      The current Menu item object
	 * @return      bool|WP_post                    The parent Menu object
	 */
	public function get_parent_menu_item_object( $current_menu_item ) {
		$parent_menu_item = false;
		foreach ( $this->menu_items as $menu_item ) {
			if ( absint( $current_menu_item->menu_item_parent ) == absint( $menu_item->ID ) ) {
				$parent_menu_item = $menu_item;
				break;
			}
		}

		return $parent_menu_item;
	}

	/**
	 * Recursively retrieve an array of parent Menu item objects
	 *
	 * @since       1.0.0
	 * @param       WP_Post $child_menu_item    The child for which we want parent Menu items
	 * @param       array $parents              The existing ancestors
	 * @return      bool|array                  The final ancestors
	 */
	function get_parents( $child_menu_item, $parents = array() ) {
		$parent_menu_item = false;
		foreach ( $this->menu_items as $menu_item ) {
			if ( absint( $child_menu_item->menu_item_parent ) == absint( $menu_item->ID ) ) {
				$parent_menu_item = $menu_item;
				break;
			}
		}

		if ( $parent_menu_item ) {
			$parents[] = $parent_menu_item;
			return $this->get_parents( $parent_menu_item, $parents );
		}

		return false;
	}

	/**
	 * Generate HTML for each breadcrumb
	 *
	 * @since       1.0.0
	 * @param       array $breadcrumbs  WP_Post objects generated from a Menu
	 * @param       string $separator   String to inject between each link
	 * @return      string              The generated markup for the entire breadcrumb trail
	 */
	public function markup( $breadcrumbs, $separator ) {

		// allow for filtration of post object per breadcrumb
		foreach ( $breadcrumbs as $key => $breadcrumb ) {
			$markup = '<a href="' . esc_url( $breadcrumb->url ) . '">';
			$markup .= esc_html( $breadcrumb->title );
			$markup .= '</a>';

			$markup = (string) apply_filters( 'menu_breadcrumb_item_markup', $markup, $breadcrumb );

			$breadcrumbs[ $key ] = $markup;
		}

		return implode( $separator, $breadcrumbs );
	}

	/**
	 * Generate an array of WP_Post objects that constitutes a breadcrumb trail based on a Menu
	 *
	 * @since       1.0.0
	 * @return      array|string    Breadcrumb of WP_Post objects
	 */
	public function generate() {
		$current_menu_item = $this->get_current_menu_item_object( $this->menu_id );

		if ( empty( $current_menu_item ) ) {
			return '';
		}

		// there's at least one level
		$breadcrumb = array( $current_menu_item );

		// work backwards from the current menu item all the way to the top
		while ( $current_menu_item = $this->get_parent_menu_item_object( $current_menu_item ) ) {
			$breadcrumb[] = $current_menu_item;
		}

		// since we worked backwards, we need to reverse everything
		$breadcrumb = array_reverse( $breadcrumb );

		return $breadcrumb;
	}

	/**
	 * Render HTML for the breadcrumb trail
	 *
	 * @since       1.0.0
	 * @param       string $separator   Inserted between each breadcrumb item
	 * @param       string $before      HTML to inject before the entire breadcrumb
	 * @param       string $after       HTML to inject after the entire breadcrumb
	 */
	public function render( $separator = ' &raquo; ', $before = '', $after = '' ) {
		$breadcrumb = $this->generate( $separator, $before, $after );

		// right now it's an array of objects, we need to grab our permalinks
		$breadcrumb = $this->markup( $breadcrumb, $separator );

		echo $before . $breadcrumb . $after;
	}

}