<?php
/*
Plugin Name: Gravity Forms HEIC to JPG Converter
Plugin URI: https://github.com/sterlingmchase/gf-heic-to-jpg
Description: Converts HEIC files uploaded via Gravity Forms to JPG format.
Version: 1.0.1
Author: Sterling Chase
Author URI: https://frontierwp.com
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/

if (!class_exists('GitHub_Updater')) {
    require_once(plugin_dir_path(__FILE__) . '../includes/class-github-updater.php');
}

if (class_exists('GitHub_Updater')) {
    new GitHub_Updater(__FILE__, 'sterlingmchase', 'gf-heic-to-jpg');
}

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Define plugin path
define( 'GF_HEIC_TO_JPG_PATH', plugin_dir_path( __FILE__ ) );

// Include required files
require_once GF_HEIC_TO_JPG_PATH . 'includes/class-gravity-forms-handler.php';

// Enqueue scripts and styles
function gf_heic_to_jpg_enqueue_assets() {
    wp_enqueue_script( 'heic2any', 'https://cdn.jsdelivr.net/npm/heic2any/dist/heic2any.min.js', [], null, true );
    wp_enqueue_script( 'gf-heic-to-jpg', plugin_dir_url( __FILE__ ) . 'assets/js/heic-to-jpg.js', [ 'jquery', 'heic2any' ], '1.0.0', true );
    wp_enqueue_style( 'gf-heic-to-jpg', plugin_dir_url( __FILE__ ) . 'assets/css/heic-to-jpg.css', [], '1.0.0' );
}
add_action( 'wp_enqueue_scripts', 'gf_heic_to_jpg_enqueue_assets' );

// Initialize the plugin
function gf_heic_to_jpg_init() {
    if ( class_exists( 'GFForms' ) ) {
        new GF_HEIC_To_JPG\Gravity_Forms_Handler();
    }
}
add_action( 'plugins_loaded', 'gf_heic_to_jpg_init' );

// Add HEIC MIME type support
function gf_heic_to_jpg_mime_types( $mimes ) {
    $mimes['heic'] = 'image/heic';
    $mimes['heif'] = 'image/heif';
    return $mimes;
}
add_filter( 'upload_mimes', 'gf_heic_to_jpg_mime_types' );

// Override file type check to allow HEIC files
function gf_allow_heic_upload( $types, $file, $filename, $mimes ) {
    if ( ! is_array( $types ) ) {
        $types = array();
    }

    if ( false !== strpos( $filename, '.heic' ) ) {
        $types['ext'] = 'heic';
        $types['type'] = 'image/heic';
    }

    return $types;
}
add_filter( 'wp_check_filetype_and_ext', 'gf_allow_heic_upload', 10, 4 );
