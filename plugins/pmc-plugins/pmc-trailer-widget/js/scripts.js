// init page
jQuery(function() {
	initPopups();
});

// popups function
function initPopups() {
	var _zIndex = 1000;
	var _fadeSpeed = 350;
	var _faderOpacity = 0.65;
	var _faderBackground = '#000';
	var _faderId = 'lightbox-overlay';
	var _closeLink = 'a.btn-close, a.close, a.cancel';
	var _fader;
	var _lightbox = null;
	var _ajaxClass = 'ajax-load';
	var _openers = jQuery('a.open-popup');
	var _page = jQuery(document);
	var _minWidth = jQuery('body > div:eq(0)').outerWidth();
	var _scroll = false;

	// init popup fader
	_fader = jQuery('#'+_faderId);
	if(!_fader.length) {
		_fader = jQuery('<div />');
		_fader.attr('id',_faderId);
		jQuery('body').append(_fader);
	}
	_fader.css({
		opacity:_faderOpacity,
		backgroundColor:_faderBackground,
		position:'absolute',
		overflow:'hidden',
		display:'none',
		top:0,
		left:0,
		zIndex:_zIndex
	});

	// IE6 iframe fix
	if(jQuery.browser.msie && jQuery.browser.version < 7) {
		if(!_fader.children().length) {
			var _frame = jQuery('<iframe src="javascript:false" frameborder="0" scrolling="no" />');
			_frame.css({
				opacity:0,
				width:'100%',
				height:'100%'
			});
			var _frameOverlay = jQuery('<div>');
			_frameOverlay.css({
				top:0,
				left:0,
				zIndex:1,
				opacity:0,
				background:'#000',
				position:'absolute',
				width:'100%',
				height:'100%'
			});
			_fader.empty().append(_frame).append(_frameOverlay);
		}
	}

	// lightbox positioning function
	function positionLightbox() {
		if(_lightbox) {
			var _windowHeight = jQuery(window).height();
			var _windowWidth = jQuery(window).width();
			var _lightboxWidth = _lightbox.outerWidth();
			var _lightboxHeight = _lightbox.outerHeight();
			var _pageHeight = _page.height();

			if (_windowWidth < _minWidth) _fader.css('width',_minWidth);
				else _fader.css('width','100%');
			if (_windowHeight < _pageHeight) _fader.css('height',_pageHeight);
				else _fader.css('height',_windowHeight);

			_lightbox.css({
				position:'absolute',
				zIndex:(_zIndex+1)
			});

			// vertical position
			if (_windowHeight > _lightboxHeight) {
				if (jQuery.browser.msie && jQuery.browser.version < 7) {
					_lightbox.css({
						position:'absolute',
						top: parseInt(jQuery(window).scrollTop()) + (_windowHeight - _lightboxHeight) / 2
					});
				} else {
					_lightbox.css({
						position:'fixed',
						top: (_windowHeight - _lightboxHeight) / 2
					});
				}
			} else {
				var _faderHeight = _fader.height();
				if(_faderHeight < _lightboxHeight) _fader.css('height',_lightboxHeight);
				if (!_scroll) {
					if (_faderHeight - _lightboxHeight > parseInt(jQuery(window).scrollTop())) {
						_faderHeight = parseInt(jQuery(window).scrollTop())
						_scroll = _faderHeight;
					} else {
						_scroll = _faderHeight - _lightboxHeight;
					}
				}
				_lightbox.css({
					position:'absolute',
					top: _scroll
				});
			}

			// horizontal position
			if (_fader.width() > _lightbox.outerWidth()) _lightbox.css({left:(_fader.width() - _lightbox.outerWidth()) / 2});
			else _lightbox.css({left: 0});
		}
	}

	// show/hide lightbox
	function toggleState(_state) {
		if(!_lightbox) return;
		if(_state) {
			_fader.fadeIn(_fadeSpeed,function(){
				_lightbox.fadeIn(_fadeSpeed);
			});
			_scroll = false;
			positionLightbox();
		} else {
			_lightbox.fadeOut(_fadeSpeed,function(){
				_fader.fadeOut(_fadeSpeed);
				_scroll = false;
			});
		}
	}

	// popup actions
	function initPopupActions(_obj) {
		if(!_obj.get(0).jsInit) {
			_obj.get(0).jsInit = true;
			// close link
			_obj.find(_closeLink).click(function(){
				_lightbox = _obj;
				toggleState(false);
				return false;
			});
		}
	}

	// lightbox openers
	_openers.each(function(){
		var _opener = jQuery(this);
		var _target = _opener.attr('href');

		// popup load type - ajax or static
		if(_opener.hasClass(_ajaxClass)) {
			_opener.click(function(){
				// ajax load
				if(jQuery('div[rel*="'+_target+'"]').length == 0) {
					jQuery.ajax({
						url: _target,
						type: "POST",
						dataType: "html",
						success: function(msg){
							// append loaded popup
							_lightbox = jQuery(msg);
							_lightbox.find('img').load(positionLightbox)
							_lightbox.attr('rel',_target).hide().css({
								position:'absolute',
								zIndex:(_zIndex+1),
								top: -9999,
								left: -9999
							});
							jQuery('body').append(_lightbox);

							// init js for lightbox
							initPopupActions(_lightbox);

							// show lightbox
							toggleState(true);
						},
						error: function(msg){
							alert('AJAX error!');
							return false;
						}
					});
				} else {
					_lightbox = jQuery('div[rel*="'+_target+'"]');
					toggleState(true);
				}
				return false;
			});
		} else {
            if(_target && _target.indexOf('#')==0)
			if(jQuery(_target).length) {
				// init actions for popup
				var _popup = jQuery(_target);
				initPopupActions(_popup);
					// open popup
					_opener.click(function(){
					if(_lightbox) {
						_lightbox.fadeOut(_fadeSpeed,function(){
							_lightbox = _popup.hide();
							toggleState(true);
						})
					} else {
						_lightbox = _popup.hide();
						toggleState(true);
					}
					return false;
				});
			}
		}
	});

	// event handlers
	jQuery(window).resize(positionLightbox);
	jQuery(window).scroll(positionLightbox);
	jQuery(document).keydown(function (e) {
		if (!e) evt = window.event;
		if (e.keyCode == 27) {
			toggleState(false);
		}
	})
	_fader.click(function(){
		if(!_fader.is(':animated')) toggleState(false);
		return false;
	})
}
//Youtube Iframe API to contril player
var pmc_youtube_tag = document.createElement('script');
pmc_youtube_tag.src = "//www.youtube.com/iframe_api";
var pmc_youtube_firstScriptTag = document.getElementsByTagName('script')[0];
pmc_youtube_firstScriptTag.parentNode.insertBefore(pmc_youtube_tag, pmc_youtube_firstScriptTag);


var pmc_youtube_player=[];
function onYouTubeIframeAPIReady() {

    jQuery('.pmc-video-lightbox').each(
        function(index){
            pmc_youtube_player[index] =  new YT.Player( jQuery(this).attr("id"), {
                  events: {
                    'onReady': pmc_youtube_onPlayerReady,
                    'onStateChange': pmc_youtube_onPlayerStateChange
                  }
                });
        }
    );
}

function youtube_stop_video(){
    pmc_youtube_player.stopVideo();
}
function pmc_youtube_onPlayerReady(event) {
    //event.target.playVideo();
}
function pmc_youtube_onPlayerStateChange(event){
    //alert('her');
}

jQuery(document).ready( function(){
    jQuery(".pmc-trailer-widget-lightbox .close").click(
        function(){
            jQuery.each(pmc_youtube_player, function(key, value) {
                value.stopVideo();
                setTimeout( value.stopVideo(), 2000);

            });
        }
    );
});
