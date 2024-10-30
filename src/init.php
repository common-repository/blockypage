<?php

/**
 * Blocks Initializer
 *
 * Enqueue CSS/JS of all the blocks.
 *
 * Enqueue PHP files of all the blocks 
 * 
 * @since   1.0.0
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
	exit;
}

/**
 * Enqueue Gutenberg block assets for both frontend + backend.
 *
 * @uses {wp-editor} for WP editor styles.
 * @since 1.0.0
 */

function blockypage_block_assets()
{ // phpcs:ignore
	/**
	 * 
	 * Register Custom Styles And Scripts 
	 * 
	 *  */

	// Custom JavaScript
	wp_enqueue_script(
		'blpge-front-js',
		plugin_dir_url(__FILE__) . '../dist/customJS/front.js?v=1.2.4',
		array('jquery'),
		'',
		true
	);

	wp_localize_script( 'blpge-front-js', 'blpge_params', array(
		'ajaxUrl' => admin_url( 'admin-ajax.php' )
	));

	// blpge library style
	wp_enqueue_style(
		'blpge-library-style',
		plugin_dir_url(__FILE__) . '../dist/blpge-library/blpge-style.build.css?v=1.2.4',
		'',
		''
	);

	// Blocks Styles
	wp_enqueue_style(
		'blockypage-style-css', // Handle.
		plugin_dir_url(__FILE__) . '../dist/blocks.style.build.css?v=1.2.4', // Block style CSS.
		array('wp-editor'), // Dependency to include the CSS after it.
		''// Version: File modification time.
	);
}

// Hook: Frontend assets.
add_action('enqueue_block_assets', 'blockypage_block_assets');

/**
 * Enqueue Gutenberg block assets for backend editor.
 *
 * @uses {wp-blocks} for block type registration & related functions.
 * @uses {wp-element} for WP Element abstraction — structure of blocks.
 * @uses {wp-i18n} to internationalize the block's text.
 * @uses {wp-editor} for WP editor styles.
 * @since 1.0.0
 */
function blockypage_editor_assets()
{ // phpcs:ignor
	// Custom JavaScript
	wp_register_script(
		'blpge-editor-js',
		plugin_dir_url(__FILE__) . '../dist/customJS/editor.js?v=1.2.4',
		['wp-data'],
		'',
		true
	);

	// blpge library script
	wp_register_script(
		'blpge-library-script',
		plugin_dir_url(__FILE__) . '../dist/blpge-library/blpge-library.build.js?v=1.2.4',
		['wp-blocks', 'wp-i18n', 'wp-element', 'wp-editor', 'wp-data'],
		'',
		false
	);

	wp_localize_script( 'blpge-editor-js', 'blpge_params', array(
		'ajaxUrl' => admin_url( 'admin-ajax.php' )
	));

	// blpge library css
	wp_register_style(
		'blpge-library-editor',
		plugin_dir_url(__FILE__) . '../dist/blpge-library/blpge-editor.build.css!,v=1.2.4',
		'',
		''
	);

	// Font Awesome
	wp_enqueue_style(
		'blpge-font-awesome',
		plugin_dir_url(__FILE__) . '../dist/font-awesome/css/all.min.css?v=1.2.4'
	);

	// Blocks Assets
	wp_enqueue_script(
		'blockypage-block-js', // Handle.
		plugins_url('dist/blocks.build.js?v=1.2.4', dirname(__FILE__)), // Block.build.js: We register the block here. Built with Webpack.
		array('blpge-editor-js', 'blpge-library-script', 'wp-block-editor', 'wp-blocks', 'wp-i18n', 'wp-element', 'wp-editor', 'wp-data'), // Dependencies, defined above.
		'', // Version: File modification time.
		true // Enqueue the script in the footer.
	);

	// Styles.
	wp_enqueue_style(
		'blockypage-block-editor-css', // Handle.
		plugins_url('dist/blocks.editor.build.css?v=1.2.4', dirname(__FILE__)), // Block editor CSS.
		array('blpge-library-style', 'blpge-library-editor', 'wp-edit-blocks') // Dependency to include the CSS after it.
	);
}

// Hook: Editor assets.
add_action('enqueue_block_editor_assets', 'blockypage_editor_assets');

/**
 *
 *  Enqueue Server Components of all the blocks
 *
 **/

function blpge_enqueue_admin_scripts() {
	
	wp_register_style( 'blpge-admin-css', plugin_dir_url(__DIR__) . 'dist/css/style.css?v=1.2.4', '' );
	wp_enqueue_style( 'blpge-admin-css' );

	// blpge library css
	wp_register_style(
		'blpge-library-editor',
		plugin_dir_url(__FILE__) . '../dist/blpge-library/blpge-editor.build.css',
		'',
		plugin_dir_path(__DIR__) . '/dist/blpge-library/blpge-editor.build.cssv=1.2.4'
	);

	wp_register_script( 'blpge_admin_js', plugin_dir_url(__DIR__) . 'dist/js/common-script.js?v=1.2.4', array('jquery'), null, true );

	wp_localize_script( 'blpge_admin_js', 'blpge_admin_js_params', array(
		'ajaxUrl' => admin_url( 'admin-ajax.php' ), 'blpge_ajax_token'  => wp_create_nonce('blpge-nonce')
	));
	wp_enqueue_script( 'blpge_admin_js');
	
}

add_action( 'admin_enqueue_scripts', 'blpge_enqueue_admin_scripts' );


// Meta Data For CSS Post 
function blpge_register_post_meta()
{
	register_meta("post", 'blpge_post_css', array(
		'show_in_rest' => true,
		"single" => true,
		'type' => 'string'
	));

	// blpge mode post meta 
	register_meta("post", 'blpge_mode', array(
		'show_in_rest' => true,
		"single" => true,
		'type' => 'boolean'
	));

	register_meta("post", 'blpge_use_contact', array(
		'show_in_rest' => true,
		"single" => true,
		'type' => 'boolean'
	));
}

add_action('init', 'blpge_register_post_meta');


$args = array(
	'public'       => true,
	'show_in_rest' => true
);

$blpge_post_types = get_post_types($args, 'names');

foreach ($blpge_post_types as $blpge_post_type) {
	add_action('rest_after_insert_' . $blpge_post_type . '', 'blpge_generate_css_file', 10, 3);
}
add_action('wp_head', 'blpge_enqueue_page_style');

function blpge_enqueue_page_style()
{
	global $plpge_post_id;
	$queried_object = get_queried_object();
	if ( empty( $queried_object ) ) {
		return;
	}
	$plpge_post_id = $queried_object->ID;

	$upload_dir = wp_upload_dir();

	$is_user_contact_form = get_post_meta( $plpge_post_id, 'blpge_use_contact' );

	if ( $is_user_contact_form ) {
		wp_register_script("recaptcha", "https://www.google.com/recaptcha/api.js");
		wp_enqueue_script("recaptcha");
	}

	wp_register_style('blpge_single_style', $upload_dir['baseurl'] . '/blockypage/style-' . $plpge_post_id . '.css?v=' . time(), array(), '1.0.0', 'all');
	wp_enqueue_style('blpge_single_style');
}

function blpge_generate_css_file( $post, $request, $creating )
{
	require_once( ABSPATH . 'wp-admin/includes/file.php' );
	global $wp_filesystem;
	$upload_dir = wp_upload_dir();
	$dir = trailingslashit( $upload_dir['basedir'] ) . 'blockypage/'; // Set storage directory path

	WP_Filesystem(); // WP file system
	$wp_filesystem->mkdir($dir);
	$css_content = get_post_meta($post->ID, 'blpge_post_css', true);
	$wp_filesystem->put_contents($dir . 'style-' . $post->ID . '.css', $css_content, 0644); // Finally, store the file :D
}

add_action( 'wp_ajax_blpge_get_layouts', 'blpge_get_all_layouts' );
add_action( 'wp_ajax_blpge_get_layout_by_id', 'blpge_get_layout' );
add_action( 'wp_ajax_blpge_get_categories', 'blpge_get_categories' );
add_action( 'wp_ajax_blpge_contact_form', 'blpge_ajax_send_form' );
add_action( 'wp_ajax_blpge_email_conatct_setting', 'blpge_contact_email_setting' );

function blpge_contact_email_setting() {
	$nonce = $_POST['blpge_token'];
	if ( ! wp_verify_nonce( $nonce, 'blpge-nonce' ) ) {
		wp_send_json('Try again');
	}
	parse_str( $_POST['dataStored'], $output );
	update_option( 'blpge_contact_email', $output['recipient_email'] );
	wp_send_json('Success update');
}

function blpge_ajax_send_form() {

	parse_str( $_POST['dataStored'], $output );

	if ( $output && $output['recaptcha_enable'] == 'true' ) {

		$response = wp_remote_get( "https://www.google.com/recaptcha/api/siteverify?secret=".$output['secret_key']."&response=".$output['g-recaptcha-response'] );
        $responseKeys = json_decode( $response, true );
        if ( intval( $responseKeys["success"] ) !== 1 ) {
			wp_send_json('1');
        } 
	} 
	
	$subject = $output['subject'];
	$emailFrom = $output['email'];
	$name = $output['full-name'];
	$message = $output['message'];
	$email_receiver = get_option( 'blpge_contact_email' );

	$headers[] = 'Content-Type: text/html; charset=UTF-8';
	$headers[] = 'From: '.$name.' <'.$emailFrom.'>';
	$headers[] = 'Reply-To: ' . $name . ' <' . $emailFrom . '>';

	if( empty( $subject ) || empty( $emailFrom ) || empty( $name ) || empty( $message ) ) {
		wp_send_json('1');
	}



	try {
		$sendMail = wp_mail( $email_receiver, $subject, $message, $headers ) ;
		if ($sendMail) {
			wp_send_json('2');
		} else {
			wp_send_json('1');
		}
	} catch (\Exception $e) {
		wp_send_json('1');
	}
}

function blpge_get_all_layouts() {
	$get_args = array( 'timeout' => 120 );
	$category = $_POST['category_id'];
	$apiUrl = 'https://store.blockypage.com/wp-json/wp/v2/posts/?_embed&filter[category_name]='.$category.'&per_page=100';
	$response = wp_remote_get( $apiUrl , $get_args );
	if ( is_array( $response ) ) {
		$body = $response['body']; 
	} 
	echo $body;
	wp_die();
}

function blpge_get_layout() {
	$get_args = array( 'timeout' => 120 ,  'sslverify' => FALSE );
	$id = $_POST['blpge_layout_id'];
	$apiUrl = 'https://store.blockypage.com/wp-json/blockypage/v1/layout/'.$id;
	$response = wp_remote_get( $apiUrl , $get_args );
	if ( is_array( $response ) ) {
		$body = $response['body']; 
	}
	echo $body;
	wp_die();
}

function blpge_get_categories() {
	$get_args = array( 'timeout' => 120 ,  'sslverify' => FALSE );
	$id = $_POST['category_id'];
	$url = $_POST['url'];
	$apiUrl = 'https://store.blockypage.com/wp-json/wp/v2/categories?parent='.$id;
	$response = wp_remote_get( $apiUrl , $get_args );
	if ( is_array( $response ) ) {
		$body = $response['body']; 
	}
	echo $body;
	wp_die();
}

