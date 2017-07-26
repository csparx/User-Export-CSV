<?php
/*
   Plugin Name: User Export
   Description: A plugin to export users to an excel file
   Version: 1.0
   Author: Christy Sparks
   Author URI: http://christysparks.com
   License: GPL2
   */

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/*
* Main plugin class
*/
class CnsUserExport{

	/*
	* Class constructor
	*/
	public function __construct() {
		add_action('admin_menu', array( $this, 'cns_add_admin_menu' ) );
		add_action('init', array( $this, 'cns_user_export' ));
	}

	/*
	* Add Admin Menu
	*/
	public function cns_add_admin_menu() {
		add_users_page( 'Export Users', 'Export Users', 'create_users', 'cns-export-users', array( $this, 'cns_create_admin_page' ) );
	}

	/*
	* Form action - export user data to csv file
	*/
	public function cns_user_export() {
		if( isset( $_POST['_wpnonce_cns_user_export'] ) ) {

			check_admin_referer( 'cns_user_export', '_wpnonce_cns_user_export' );
			
			$args = array(
				'fields'	=> 'all'
			);
			$users = get_users( $args );

			$filename = 'users.csv';

			header( 'Content-Description: File Transfer' );
			header( 'Content-Disposition: attachment; filename=' . $filename );
			header( 'Content-Type: text/csv; charset=' . get_option( 'blog_charset' ), true );

			global $wpdb;

			$data_keys = array(
				'ID', 'user_login', 'user_pass',
				'user_nicename', 'user_email', 'user_url',
				'user_registered', 'user_activation_key', 'user_status',
				'display_name'
			);
			$meta_keys = $wpdb->get_results( "SELECT distinct(meta_key) FROM $wpdb->usermeta" );
			$meta_keys = wp_list_pluck( $meta_keys, 'meta_key' );
			$fields = array_merge( $data_keys, $meta_keys );
			
			$headers = array();
			foreach ( $fields as $key => $field ) {
				$headers[] = '"' . strtolower( $field ) . '"';
			}
			echo implode( ',', $headers ) . "\n";

			foreach ( $users as $user ) {
				$data = array();
				foreach ( $fields as $field ) {
					$value = isset( $user->{$field} ) ? $user->{$field} : '';
					$value = is_array( $value ) ? serialize( $value ) : $value;
					$data[] = '"' . str_replace( '"', '""', $value ) . '"';
				}
				echo implode( ',', $data ) . "\n";
			}

			exit;
		}
	}

	/*
	* Add Admin Menu
	*/
	public function cns_create_admin_page() {
		// Check to see if current user can create users
		if( !current_user_can( 'create_users' ) ) {
			echo '<div class="notice notice-error"><p>You do not have permission to view this page.</p></div>';
		}
		?>
		
		<!-- Start admin page form -->
		<form method="post" action="" enctype="multipart/form-data">
			<?php wp_nonce_field( 'cns_user_export', '_wpnonce_cns_user_export' ); ?>
			<h2>Click the submit button below to generate and save the csv file.</h2>
			

			<input type="submit">
		</form>

		<?php
	}

}


new CnsUserExport;