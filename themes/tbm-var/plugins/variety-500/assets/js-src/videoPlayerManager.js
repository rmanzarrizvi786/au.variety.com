/**
 * Provide video player functionality.
 */
const VideoPlayerManager = {

	/**
	 * Initialize.
	 *
	 * @returns {void}
	 */
	init: function init() {
		jQuery('.c-player').each(this.setupPlayer);
	},

	/**
	 * Set up the video player.
	 *
	 * @returns {void}
	 */
	setupPlayer: function setupPlayer() {
		const $player = jQuery(this);
		const $iframe = $player.find('iframe');
		let src = $iframe.data('src') || '';

		// Make sure player is static when there's no `src` on the iframe.
		if (!$player.hasClass('is-static') && $iframe.attr('src') === '') {
			$player.addClass('is-static');
		}

		// Add `autoplay=1` to the `src` so that the user doesn't have to click twice.
		if (src.indexOf('autoplay') !== -1) {
			src = src.replace(/autoplay=[01]/i, 'autoplay=1');
		} else {
			src = `${src}&autoplay=1`;
		}

		// Activate the player.
		$player.on('click', '.c-player__link', e => {
			e.preventDefault();

			if (!$player.hasClass('is-static')) {
				return;
			}

			$iframe.attr('src', src);
			$player.removeClass('is-static');
		});

		// Deactivate the player.
		$player.on('player:reset', () => {
			$iframe.attr('src', '');
			$player.addClass('is-static');
		});
	},
};

export default VideoPlayerManager;
