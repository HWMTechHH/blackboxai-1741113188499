(function($) {
    'use strict';

    class StormanPlayer {
        constructor(element) {
            this.player = $(element);
            this.audio = this.player.find('audio')[0];
            this.postId = this.player.data('post-id');
            this.playBtn = this.player.find('.sm-playbtn');
            this.progress = this.player.find('.sm-player__progress');
            this.currentTime = this.player.find('.sm-player__current-time');
            this.duration = this.player.find('.sm-player__duration');
            this.volumeSlider = this.player.find('.sm-player__volume-slider');
            this.volumeValue = this.player.find('.sm-player__volume-slider__value');
            this.muteBtn = this.player.find('.sm-player__volume-mute');
            this.speedBtn = this.player.find('.sm-player__speed-option');
            this.errorDisplay = this.player.find('.sm-player__error');
            
            this.isPlaying = false;
            this.isLoading = false;
            this.currentSpeed = 1;
            
            this.initializeEvents();
            this.checkExistingAudio();
        }

        initializeEvents() {
            this.playBtn.on('click', () => this.togglePlay());
            this.muteBtn.on('click', () => this.toggleMute());
            this.speedBtn.on('click', () => this.toggleSpeed());
            
            // Volume control
            this.volumeSlider.on('click', (e) => {
                const rect = e.currentTarget.getBoundingClientRect();
                const x = e.clientX - rect.left;
                const volume = Math.max(0, Math.min(1, x / rect.width));
                this.setVolume(volume);
            });

            // Progress bar interaction
            this.player.find('.sm-player__wave').on('click', (e) => {
                if (this.audio.duration) {
                    const rect = e.currentTarget.getBoundingClientRect();
                    const x = e.clientX - rect.left;
                    const percentage = x / rect.width;
                    this.audio.currentTime = this.audio.duration * percentage;
                }
            });

            // Audio events
            $(this.audio)
                .on('timeupdate', () => this.updateProgress())
                .on('loadedmetadata', () => this.updateDuration())
                .on('ended', () => this.onEnded())
                .on('error', (e) => this.handleError(e));
        }

        checkExistingAudio() {
            if (this.audio.src) {
                this.player.addClass('has-audio');
            }
        }

        togglePlay() {
            if (this.isLoading) return;

            if (!this.audio.src) {
                this.loadAudio();
                return;
            }

            if (this.isPlaying) {
                this.pause();
            } else {
                this.play();
            }
        }

        loadAudio() {
            this.isLoading = true;
            this.player.addClass('loading');
            this.errorDisplay.hide();
            
            $.ajax({
                url: smphAjax.ajaxurl,
                type: 'POST',
                data: {
                    action: 'smph_convert_to_audio',
                    post_id: this.postId,
                    nonce: smphAjax.nonce
                },
                success: (response) => {
                    if (response.success && response.data) {
                        this.audio.src = response.data;
                        this.player.addClass('has-audio');
                        this.play();
                    } else {
                        this.handleError(response.data || 'Conversion failed');
                    }
                },
                error: (xhr, status, error) => {
                    this.handleError(error);
                },
                complete: () => {
                    this.isLoading = false;
                    this.player.removeClass('loading');
                }
            });
        }

        play() {
            const playPromise = this.audio.play();
            
            if (playPromise !== undefined) {
                playPromise
                    .then(() => {
                        this.isPlaying = true;
                        this.player.addClass('playing');
                    })
                    .catch(error => {
                        this.handleError(error);
                    });
            }
        }

        pause() {
            this.audio.pause();
            this.isPlaying = false;
            this.player.removeClass('playing');
        }

        toggleMute() {
            this.audio.muted = !this.audio.muted;
            this.muteBtn.toggleClass('muted', this.audio.muted);
            if (!this.audio.muted) {
                this.setVolume(this.audio.volume);
            }
        }

        setVolume(value) {
            const volume = Math.max(0, Math.min(1, value));
            this.audio.volume = volume;
            this.volumeValue.css('width', `${volume * 100}%`);
            this.muteBtn.toggleClass('muted', volume === 0);
        }

        toggleSpeed() {
            const speeds = [1, 1.25, 1.5, 2];
            const currentIndex = speeds.indexOf(this.currentSpeed);
            const nextIndex = (currentIndex + 1) % speeds.length;
            this.currentSpeed = speeds[nextIndex];
            this.audio.playbackRate = this.currentSpeed;
            this.speedBtn.text(`${this.currentSpeed}X`);
        }

        updateProgress() {
            if (!this.audio.duration) return;
            
            const currentTime = this.audio.currentTime;
            const duration = this.audio.duration;
            const progress = (currentTime / duration) * 100;
            
            this.progress.css('width', `${progress}%`);
            this.currentTime.text(this.formatTime(currentTime));
        }

        updateDuration() {
            if (this.audio.duration) {
                this.duration.text(this.formatTime(this.audio.duration));
            }
        }

        formatTime(time) {
            const minutes = Math.floor(time / 60);
            const seconds = Math.floor(time % 60);
            return `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
        }

        onEnded() {
            this.isPlaying = false;
            this.player.removeClass('playing');
            this.audio.currentTime = 0;
            this.updateProgress();
        }

        handleError(error) {
            console.error('Audio player error:', error);
            this.errorDisplay.show();
            this.player.removeClass('playing loading');
            this.isPlaying = false;
            this.isLoading = false;
        }
    }

    // Initialize players
    $('.sm-player').each(function() {
        new StormanPlayer(this);
    });

})(jQuery);
