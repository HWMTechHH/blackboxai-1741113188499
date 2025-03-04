<?php
// Check if we already have a cached audio URL
$audio_url = smph_get_cached_audio_url(get_the_ID());
$has_audio = !empty($audio_url);
?>
<div class="sm-player<?php echo $has_audio ? ' has-audio' : ''; ?>" data-post-id="<?php echo get_the_ID(); ?>">
    <div class="sm-player__cta">Diesen Artikel vorlesen lassen</div>
    
    <div class="sm-playbtn">
        <svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 26 26" xml:space="preserve" width="48" height="48">
            <polygon class="sm-playbtn-play" points="9.3,6.7 9.3,19.4 19.3,13" fill="#ca0019"/>
            <g>
                <path d="M13,1c6.6,0,12,5.4,12,12s-5.4,12-12,12S1,19.6,1,13S6.4,1,13,1 M13,0C5.8,0,0,5.8,0,13s5.8,13,13,13s13-5.8,13-13 S20.2,0,13,0L13,0z" fill="#ca0019"/>
            </g>
            <g class="sm-playbtn-pause">
                <rect x="10" y="7.7" width="2" height="10.7" fill="#ca0019"/>
                <rect x="14" y="7.7" width="2" height="10.7" fill="#ca0019"/>
            </g>
        </svg>
        <div class="sm-playbtn__loading">
            <svg class="sm-playbtn__loading-spinner" viewBox="0 0 50 50">
                <circle cx="25" cy="25" r="20" fill="none" stroke="#ca0019" stroke-width="5"></circle>
            </svg>
        </div>
    </div>

    <div class="sm-player__wave">
        <div class="sm-player__progress"></div>
    </div>

    <div class="sm-player__controls">
        <div class="sm-player__time-volume-container">
            <button type="button" class="sm-player__volume-mute">
                <svg width="12" height="12" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M0 5.10001V10.4H3.6L8 14.9V0.700012L3.6 5.10001H0Z" fill="#ca0019"/>
                    <path d="M10 1.8V0C13.5 0.8 16.2 4 16.2 7.8C16.2 11.6 13.6 14.8 10 15.6V13.8C12.5 13 14.4 10.6 14.4 7.8C14.4 4.9 12.6 2.6 10 1.8Z" fill="#ca0019"/>
                    <path d="M10 4.2C11.3 4.9 12.2 6.2 12.2 7.8C12.2 9.4 11.3 10.7 10 11.3V4.2Z" fill="#ca0019"/>
                </svg>
            </button>
            
            <div class="sm-player__time">
                <span class="sm-player__current-time">00:00</span> / <span class="sm-player__duration">00:00</span>
            </div>

            <div class="sm-player__volume-slider">
                <div class="sm-player__volume-slider__value" style="width: 50%"></div>
            </div>
        </div>

        <div class="sm-player__speed">
            <span class="sm-player__speed-option">1X</span>
        </div>

        <a href="#" class="sm-player__brand">Stormarn Media Technik Team</a>
    </div>

    <audio preload="metadata" <?php if ($has_audio) : ?>src="<?php echo esc_url($audio_url); ?>"<?php endif; ?>></audio>

    <div class="sm-player__error" style="display: none;">
        <p>Ein Fehler ist aufgetreten. Bitte versuchen Sie es später erneut.</p>
    </div>
</div>
