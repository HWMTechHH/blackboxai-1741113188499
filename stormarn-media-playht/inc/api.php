<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Convert text to speech using PlayHT API
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

    // First, create a conversion
    $create_conversion_url = 'https://play.ht/api/v2/tts';
    
    $response = wp_remote_post($create_conversion_url, array(
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
            'sample_rate' => 24000
        )),
        'timeout' => 45
    ));

    if (is_wp_error($response)) {
        return $response;
    }

    $body = json_decode(wp_remote_retrieve_body($response), true);
    
    if (!isset($body['transcriptionId'])) {
        return new WP_Error(
            'invalid_response', 
            'Invalid response from PlayHT API: ' . wp_remote_retrieve_response_message($response)
        );
    }

    // Store the transcription ID in post meta for future reference
    update_post_meta($post_id, '_smph_transcription_id', $body['transcriptionId']);

    // Now get the audio URL
    $get_audio_url = 'https://play.ht/api/v2/tts/' . $body['transcriptionId'];
    
    $audio_response = wp_remote_get($get_audio_url, array(
        'headers' => array(
            'Authorization' => 'Bearer ' . SMPH_PLAYHT_SECRET_ID,
            'X-User-ID' => SMPH_PLAYHT_USER_ID,
            'Accept' => 'application/json'
        )
    ));

    if (is_wp_error($audio_response)) {
        return $audio_response;
    }

    $audio_body = json_decode(wp_remote_retrieve_body($audio_response), true);
    
    if (!isset($audio_body['audioUrl'])) {
        return new WP_Error(
            'invalid_audio_response', 
            'Invalid audio response from PlayHT API'
        );
    }

    // Store the audio URL in post meta
    update_post_meta($post_id, '_smph_audio_url', $audio_body['audioUrl']);

    return $audio_body['audioUrl'];
}

/**
 * Check audio conversion status
 */
function smph_check_conversion_status($transcription_id) {
    $status_url = 'https://play.ht/api/v2/tts/' . $transcription_id;
    
    $response = wp_remote_get($status_url, array(
        'headers' => array(
            'Authorization' => 'Bearer ' . SMPH_PLAYHT_SECRET_ID,
            'X-User-ID' => SMPH_PLAYHT_USER_ID,
            'Accept' => 'application/json'
        )
    ));

    if (is_wp_error($response)) {
        return $response;
    }

    return json_decode(wp_remote_retrieve_body($response), true);
}

/**
 * Get cached audio URL if available
 */
function smph_get_cached_audio_url($post_id) {
    return get_post_meta($post_id, '_smph_audio_url', true);
}
