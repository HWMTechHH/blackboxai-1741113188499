<?php
/**
 * Plugin Name: Stormarn Media PlayHT Integration
 * Description: Adds an audio player using PlayHT API to automatically convert post content to speech
 * Version: 1.0.0
 * Author: Stormarn Media
 * Text Domain: stormarn-media-playht
 */

if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('SMPH_VERSION', '1.0.0');
define('SMPH_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('SMPH_PLUGIN_URL', plugin_dir_url(__FILE__));
define('SMPH_PLAYHT_USER_ID', 'ntCobGClKZXyHZLIrIbZpOC3cW43');
define('SMPH_PLAYHT_SECRET_ID', '12ab42082eca43aab60df1471b4f3ece');

// Include required files
require_once SMPH_PLUGIN_DIR . 'inc/api.php';

// Enqueue scripts and styles
function smph_enqueue_scripts() {
    if (is_single()) {
        wp_enqueue_style('smph-fonts', SMPH_PLUGIN_URL . 'css/fonts.css', array(), SMPH_VERSION);
        wp_enqueue_style('smph-styles', SMPH_PLUGIN_URL . 'css/style.css', array('smph-fonts'), SMPH_VERSION);
        wp_enqueue_script('smph-player', SMPH_PLUGIN_URL . 'js/player.js', array('jquery'), SMPH_VERSION, true);
        
        wp_localize_script('smph-player', 'smphAjax', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('smph-nonce')
        ));
    }
}
add_action('wp_enqueue_scripts', 'smph_enqueue_scripts');

// Add player to post content
function smph_add_player_to_content($content) {
    if (is_single() && in_the_loop() && is_main_query()) {
        ob_start();
        include SMPH_PLUGIN_DIR . 'templates/player-template.php';
        $player = ob_get_clean();
        return $player . $content;
    }
    return $content;
}
add_filter('the_content', 'smph_add_player_to_content');

// AJAX handler for audio conversion
function smph_convert_to_audio() {
    check_ajax_referer('smph-nonce', 'nonce');
    
    $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
    if (!$post_id) {
        wp_send_json_error('Invalid post ID');
    }
    
    $result = smph_convert_post_to_audio($post_id);
    if (is_wp_error($result)) {
        wp_send_json_error($result->get_error_message());
    }
    
    wp_send_json_success($result);
}
add_action('wp_ajax_smph_convert_to_audio', 'smph_convert_to_audio');
add_action('wp_ajax_nopriv_smph_convert_to_audio', 'smph_convert_to_audio');
