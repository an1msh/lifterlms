<?php
/**
 * @author 		codeBOX
 * @category 	Admin
 * @package 	LifterLMS/Classes
 * @version     0.1
 */

/**
 * Restrict direct access
 */

include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'LLMS_Install' ) ) :

/**
 * LLMS_Install Class
 */

class LLMS_Install {

	public $version = '1.0';
	protected $min_wp_version = '3.5';
	public $current_wp_version;

	/**
	 * LLMS_Install Constructor.
	 * @access public
	 * @return LLMS_Install
	 */
	public function __construct() {
		$this->current_wp_version = get_bloginfo( 'version' );
		register_activation_hook( LLMS_PLUGIN_FILE, array( $this, 'install' ) );
		add_action( 'admin_init', array( $this, 'check_wp_version' ) );	
		add_action( 'admin_init', array( $this, 'install_settings' ) );
	}

	//custom error notice ~ this needs to be moved to it's own class / factory
	public function custom_error_notice(){
     	global $current_screen;
     	if ( $current_screen->parent_base == 'plugins' )
         	echo '<div class="error"><p>Warning - LifterLMS is only compatable with Wordpress version '
         		. $this->min_wp_version . ' or higher. Your current version of Wordpress is ' . $this->current_wp_version  .
         		'. You may experience issues with this plugin until you upgrade your version of Wordpress.</p></div>';
	}

	//testing message only. You can dump shit out in this message. 
	public function custom_dump_notice(){
     	global $current_screen;
     	if ( $current_screen->parent_base == 'plugins' )
         	echo '<div class="error"><p>ok. we got this far. '
         		. $this->min_wp_version . ' or higher. Your current version of Wordpress is ' . $this->current_wp_version  .
         		'. You may experience issues with this plugin until you upgrade your version of Wordpress.</p></div>';
	}

	/**
	 * Check if installed WP version is compatable with plugin requirements. 
	 */
	public function check_wp_version() {
		
		if ( version_compare( get_bloginfo( 'version' ), $this->min_wp_version, '<' ) ) {
			add_action( 'admin_notices', array( $this, 'custom_error_notice' ));
		}

		$this->install();
	}

	/**
	 * Install LLMS
	 */
	public function install() {
		
		$this->create_options();
		$this->create_tables();
		$this->create_roles();
		
		
		// Register Post Types	
		include_once( 'class.llms.post-types.php' );
		LLMS_Post_Types::register_post_types();
		LLMS_Post_Types::register_taxonomies();

		LLMS()->query->init_query_vars();
		LLMS()->query->add_endpoints();

		flush_rewrite_rules();
	}

	public function install_settings() {

		$installation_complete = get_option( 'lifterlms_settings_installed', 'no' ) === 'yes' ? true : false;

		if ( ! $installation_complete ) {

			self::create_pages();
			self::create_posts();
			update_option( 'lifterlms_settings_installed', 'yes' );

		}
	}

	/**
	 * Create starter pages
	 *
	 * TODO: This could be refactored to loop through and gen the pages.
	 */
	public static function create_pages() {

		$membership_page = apply_filters( 'lifterlms_new_page', array(
			'post_type' 	=> 'page',
			'post_title' 	=> 'Memberships',
			'post_author' 	=> 1,
			'post_status'   => 'publish',
			'post_content'  => '',
		) );

		$shop_page = apply_filters( 'lifterlms_new_page', array(
			'post_type' 	=> 'page',
			'post_title' 	=> 'Courses',
			'post_author' 	=> 1,
			'post_status'   => 'publish',
			'post_content'  => '',
		) );

		$checkout_page = apply_filters( 'lifterlms_new_page', array(
			'post_type' 	=> 'page',
			'post_title' 	=> 'Purchase',
			'post_author' 	=> 1,
			'post_status'   => 'publish',
			'post_content'  => '[lifterlms_checkout]',
		) );

		$account_page = apply_filters( 'lifterlms_new_page', array(
			'post_type' 	=> 'page',
			'post_title' 	=> 'My Courses',
			'post_author' 	=> 1,
			'post_status'   => 'publish',
			'post_content'  => '[lifterlms_my_account]',
		) );

		$membership_page_id = wp_insert_post( $membership_page, true );
		$shop_page_id = wp_insert_post( $shop_page, true );
		$checkout_page_id = wp_insert_post( $checkout_page, true );
		$account_page_id = wp_insert_post( $account_page, true );

	}

	public function create_posts() {
		$new_user_email = apply_filters( 'lifterlms_new_page', array(
			'post_type' 	=> 'llms_email',
			'post_title'    => 'Welcome Email',
			'post_content'  => '<h2><>Hey there {user_login},</span></h2>
								<p>Thanks for creating an account on {site_title}!</p>
								Your username is <strong>{user_login}.</strong>
								You can access your account here: <a href="{site_url}">{site_title}</a>.',
			'post_status'   => 'publish',
			'post_author'   => 1,
		) );

		$new_user_email_id = wp_insert_post( $new_user_email, true );
		update_post_meta($new_user_email_id,'_event_id', 'email_new_user');

	}

	public function create_options() {

		//store installed version
		add_option('lifterlms_current_version', $this->version);
		add_option('lifterlms_is_activated', '');
		add_option('lifterlms_update_key', '');
		add_option('lifterlms_authkey', 'YA5j24mKX38yyLZf2CD6YX6i78Kr94tg');
		
		include_once( 'admin/class.llms.admin.settings.php' );

		$settings = LLMS_Admin_Settings::get_settings_tabs();

		foreach ( $settings as $section ) {
			foreach ( $section->get_settings() as $value ) {
				if ( isset( $value['default'] ) && isset( $value['id'] ) ) {
					$autoload = isset( $value['autoload'] ) ? (bool) $value['autoload'] : true;
					add_option( $value['id'], $value['default'], '', ( $autoload ? 'yes' : 'no' ) );
				}
			}
		}

	}

	/**
	 * Create liftLMS tables
	 */
	public function create_tables() {

		global $wpdb, $lifterlms;

		$wpdb->hide_errors();

		$collate = '';

		if ( $wpdb->has_cap( 'collation' ) ) {
			if ( ! empty($wpdb->charset ) ) {
				$collate .= "DEFAULT CHARACTER SET $wpdb->charset";
			}
			if ( ! empty($wpdb->collate ) ) {
				$collate .= " COLLATE $wpdb->collate";
			}
		}

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		//lifterLMS Tables
		$lifterlms_tables = "
			CREATE TABLE {$wpdb->prefix}lifterlms_order (
			  order_id bigint(20) NOT NULL auto_increment,
			  user_id bigint(20) NOT NULL,
			  created_date datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
			  order_completed varchar(200) NOT NULL DEFAULT 'no',
			  completed_date datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
			  product_id bigint(20) NOT NULL,
			  order_post_id bigint(20) NULL,
			  PRIMARY KEY  (order_id),
			  KEY user_id (user_id)
			) $collate;
			CREATE TABLE {$wpdb->prefix}lifterlms_attribute_taxonomies (
			  attribute_id bigint(20) NOT NULL auto_increment,
			  attribute_name varchar(200) NOT NULL,
			  attribute_label longtext NULL,
			  attribute_type varchar(200) NOT NULL,
			  attribute_orderby varchar(200) NOT NULL,
			  PRIMARY KEY  (attribute_id),
			  KEY attribute_name (attribute_name)
			) $collate;
			CREATE TABLE {$wpdb->prefix}lifterlms_order_items (
			  order_item_id bigint(20) NOT NULL auto_increment,
			  order_item_name longtext NOT NULL,
			  order_item_type varchar(200) NOT NULL DEFAULT '',
			  order_id bigint(20) NOT NULL,
			  PRIMARY KEY  (order_item_id),
			  KEY order_id (order_id)
			) $collate;
			CREATE TABLE {$wpdb->prefix}lifterlms_order_itemmeta (
			  meta_id bigint(20) NOT NULL auto_increment,
			  order_item_id bigint(20) NOT NULL,
			  meta_key varchar(255) NULL,
			  meta_value longtext NULL,
			  PRIMARY KEY  (meta_id),
			  KEY order_item_id (order_item_id),
			  KEY meta_key (meta_key)
			) $collate;
			CREATE TABLE {$wpdb->prefix}lifterlms_user_postmeta (
			  meta_id bigint(20) NOT NULL auto_increment,
			  user_id bigint(20) NOT NULL,
			  post_id bigint(20) NOT NULL,
			  meta_key varchar(255) NULL,
			  meta_value longtext NULL,
			  updated_date datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
			  PRIMARY KEY  (meta_id),
			  KEY user_id (user_id),
			  KEY post_id (post_id)
			) $collate;
		";

		try
		{
		$conn = dbDelta( $lifterlms_tables );
		}
		catch (Exception $e)
		{
		 throw new Exception( 'Instalation failed. Error creating lifterLMS tables in database.', 0, $e);
		}	

	}

	public function create_roles() {
		global $wp_roles;

		$methods = $this->get_capabilities();
	}

	public function get_capabilities() {
		$capabilities = array();

		$capabilities['core'] = array(
			'manage_lifterlms',
			'view_lifterlms_reports'
			);

		$capability_types = array( 'course', 'section', 'lesson', 'order', 'llms_email' );

		foreach( $capability_types as $capability_type ) {
			$capability_types[ $capability_type  ] = array(
				"edit_{$capability_type}",
				"read_{$capability_type}",
				"delete_{$capability_type}",
				"edit_{$capability_type}s",
				"edit_others_{$capability_type}s",
				"publish_{$capability_type}s",
				"read_private_{$capability_type}s",
				"delete_{$capability_type}s",
				"delete_private_{$capability_type}s",
				"delete_published_{$capability_type}s",
				"delete_others_{$capability_type}s",
				"edit_private_{$capability_type}s",
				"edit_published_{$capability_type}s",
				"manage_{$capability_type}_terms",
				"edit_{$capability_type}_terms",
				"delete_{$capability_type}_terms",
				"assign_{$capability_type}_terms"
			);
		}
		return $capabilities;
	}

}

endif;

return new LLMS_Install();