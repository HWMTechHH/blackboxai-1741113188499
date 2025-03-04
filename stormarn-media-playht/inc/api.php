<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Convert text to speech using PlayHT Streaming API
 * Based on: https://docs.play.ht/reference/api-getting-started
 */
function smph_convert_post_to_audio($post_id) {
    $post = get_post($post_id);
    if (!$post) {
        return new WP_Error('invalid_post', 'Post not found');
    }

    // Clean content - remove HTML tags and decode entities
    $content = wp_strip_all_tags($post->post_content);
    $content = html_entity_decode($content);

    // API endpoint for streaming
    $api_url = 'https://api.play.ht/api/v2/tts/stream';
    
    $response = wp_remote_post($api_url, array(
        'headers' => array(
            'Authorization' => 'Bearer ' . SMPH_PLAYHT_SECRET_ID,
            'X-User-ID' => SMPH_PLAYHT_USER_ID,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json'
        ),
        'body' => json_encode(array(
            'text' => $content,
            'voice' => 'de-DE-Standard-F', // German female voice
            'quality' => 'medium',
            'output_format' => 'mp3',
            'speed' => 1,
            'sample_rate' => 24000,
            'voice_engine' => 'PlayHT2.0', // Using PlayHT2.0 for better quality
            'emotion' => 'neutral',
            'voice_guidance' => 4, // Medium-high voice uniqueness
            'style_guidance' => 15, // Medium emotion strength
            'text_guidance' => 1.5 // Balance between fluidity and accuracy
        )),
        'timeout' => 45,
        'stream' => true // Enable streaming response
    ));

    if (is_wp_error($response)) {
        return $response;
    }

    $response_code = wp_remote_retrieve_response_code($response);
    if ($response_code !== 200) {
        $error_message = wp_remote_retrieve_response_message($response);
        $body = json_decode(wp_remote_retrieve_body($response), true);
        if (isset($body['error_message'])) {
            $error_message = $body['error_message'];
        }
        return new WP_Error(
            'api_error',
            sprintf('PlayHT API Error (%d): %s', $response_code, $error_message)
        );
    }

    // Get the audio content
    $audio_content = wp_remote_retrieve_body($response);
    
    // Generate a unique filename
    $upload_dir = wp_upload_dir();
    $filename = sprintf('playht-audio-%d-%s.mp3', $post_id, uniqid());
    $filepath = $upload_dir['path'] . '/' . $filename;
    
    // Save the audio file
    $saved = file_put_contents($filepath, $audio_content);
    if ($saved === false) {
        return new WP_Error('file_save_error', 'Failed to save audio file');
    }

    // Get the URL for the saved file
    $file_url = $upload_dir['url'] . '/' . $filename;
    
    // Store the audio URL in post meta
    update_post_meta($post_id, '_smph_audio_url', $file_url);
    
    return $file_url;
}

/**
 * Get cached audio URL if available
 */
function smph_get_cached_audio_url($post_id) {
    return get_post_meta($post_id, '_smph_audio_url', true);
}
