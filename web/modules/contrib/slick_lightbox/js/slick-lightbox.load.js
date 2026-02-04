/**
 * @file
 * Provides SlickLightbox loader.
 */

(function ($, Drupal, drupalSettings, _db, _win) {

  'use strict';

  var _id = 'slick';
  var _name = _id + '-lightbox';
  var _idOnce = _name;
  var _galleryId = _name + '-gallery';
  var _mounted = _galleryId + '--on';
  var _selector = '[data-' + _galleryId + ']:not(.' + _mounted + ')';
  var _trigger = '[data-' + _name + '-trigger]';
  var _base = _name + '-slick';
  var _baseSel = '.' + _base;
  var _nick = 'slbox';
  var _isPlaying = 'is-playing';
  var _isPaused = 'is-paused';
  var _selClose = '.media__icon--close';
  var _selPlay = '.media__icon--play';
  var _blazy = Drupal.blazy || {};
  var _sanitizer = _db.sanitizer;

  /**
   * SlickLightbox utility functions.
   *
   * @param {HTMLElement} elm
   *   The SlickLightbox gallery HTML element.
   */
  function process(elm) {
    var sbox;
    var $slider = $('> .slick__slider', elm);
    var $container = $slider.length ? $slider : $(elm);
    var boxSettings = drupalSettings.slickLightbox.lightbox || {};
    var slickSettings = drupalSettings.slickLightbox.slick || {};

    _trigger = boxSettings.itemSelector || _trigger;
    var $triggers = $(_trigger, elm);

    // Initializes slick with video supports.
    function initSlick(modalElement) {
      var $slick = $(_baseSel, modalElement);
      var $slides = $slick.children();
      var $instance;

      /**
       * Build out the media player.
       *
       * @param {int} i
       *   The index of the current element.
       * @param {HTMLElement} box
       *   The gallery item HTML element which triggers the lightbox.
       */
      function buildOutMedia(i, box) {
        var html;
        var $media;
        var $box = $(box);
        var $slide = $($slides[i]);
        var $player = $('.media--player', $slide);
        // @todo remove the second at 3.x:
        var media = $box.data('bMedia') || $box.data('media') || {};
        var $img;

        // Hook into native to fix potential lazy load issues.
        $('img', $slide).attr('loading', 'lazy');

        if (media) {
          $slide.addClass('slick-slide--' + media.type);

          if ('html' in media) {
            try {
              html = media.html;

              if (media.encoded) {
                html = atob(html);
              }

              html = _sanitizer.sanitize(html);
              $media = Drupal.theme('slickLightbox', {
                html: html,
                media: media
              });

              $slide.find(_baseSel + '-img').replaceWith($media);

              $img = $slide.find('img');
              if ($img.length) {
                $img.addClass(_base + '-img');
                if ($slide.find(_baseSel + '-caption').length) {
                  _win.setTimeout(function () {
                    $img.each(function () {
                      if (this.complete) {
                        resizeImage.call(this);
                      }
                      else {
                        $(this).one('load', resizeImage);
                      }
                    });
                  }, 10);
                }
              }
            }
            catch (e) {
              // Ignore.
            }

            var $wrapper = $('.media', $slide);
            if ($wrapper.length) {
              Drupal.attachBehaviors($wrapper[0]);
            }
          }
          else {
            if ($box.data('boxUrl') && !$player.length) {
              $media = Drupal.theme('blazyMedia', {el: box});
              $img = $(_baseSel + '-img', $slide);

              $img.replaceWith($($media));

              _win.setTimeout(function () {
                $player = $slide.find('.media--player');

                if ($player.length) {
                  Drupal.attachBehaviors($player[0]);
                }
              });

            }
          }
        }
      }

      function checkBlazy() {
        if (_blazy.load) {
          _blazy.load($slick[0]);
        }
      }

      /**
       * Trigger the media close.
       */
      function closeOut() {
        var $playing = $slick.find('.' + _isPlaying);
        var $video = $slick.find('video');

        // Clean up any pause marker at slider container.
        $slick.removeClass(_isPaused);

        if ($playing.length) {
          $playing.removeClass(_isPlaying).find(_selClose).click();
        }

        if ($video.length) {
          $video[0].pause();
        }
      }

      /**
       * Trigger pause on slick instance when playing a video.
       */
      function pause() {
        $slick.addClass(_isPaused).slick('slickPause');
      }

      /**
       * Resize the responsive image.
       */
      function resizeImage() {
        var t = $(this);
        var w = t.width();
        var p = t.closest('.media-wrapper--box');
        var c = p.next(_baseSel + '-caption');
        var ph = p.parent().height();

        if (c.length) {
          ph = ph - (c.height() + parseInt(c.css('margin-top').replace('px', ''), 0));
        }

        t.css('max-height', ph);
        p.css({width: w});
      }

      function filterOutClones($items) {
        if (!$slider.length) {
          return $items;
        }

        return $items.filter(function () {
          var $item = $(this);
          return !$item.hasClass('slick-cloned')
            && $item.parents('.slick-cloned').length === 0;
        });
      }

      // Initializes slick.
      if (!$slick.hasClass('slick-initialized')) {
        $slick.on('init', function () {
          $triggers = filterOutClones($triggers);
          $triggers.each(buildOutMedia);
        });
      }

      $instance = $slick.slick(slickSettings);

      $slick.on('beforeChange.' + _nick, checkBlazy);
      $slick.on('afterChange.' + _nick, closeOut);
      $slick.on('click.' + _nick, _selClose, closeOut);
      $slick.on('click.' + _nick, _selPlay, pause);

      // Fix for broken display when only has 1 slide, like usual.
      if ($slides.length === 1) {
        var slick = $slick.slick('getSlick');
        $slick.addClass(_base + '--unslick');
        slick.$slideTrack.css({left: '', transform: ''});
      }
      return $instance;
    }

    var options = {
      // Prevents clicking a video player button from closing the lightbox.
      // @todo re-enable when the library provides a fix for this.
      closeOnBackdropClick: false,
      itemSelector: _trigger,
      caption: function (target, info) {
        var $caption = $(target).next();
        if ($caption.length && $caption.hasClass('visually-hidden')) {
          return _sanitizer.sanitize($caption[0].innerHTML);
        }
        return '';
      },
      src: function (target) {
        var $target = $(target);
        return $target.data('boxUrl') || $target.attr('href');
      },
      slick: initSlick
    };

    var events = {
      'show.slickLightbox': function () {
        // Prevents media player with aspect ratio from being squeezed.
        $('.slick-slide--video .' + _base + '-item-inner').removeAttr('style');

        // Overrides closeOnBackdropClick as otherwise clicking video play
        // button closes the entire lightbox.
        // @todo remove when the library fixes this.
        $($container[0].slickLightbox.$modalElement).on('click.' + _nick, _baseSel + '-item', function (e) {
          if (e.target === this) {
            $('.' + _name + '-close').click();
          }
        });
      }
    };

    // Initializes slick lightbox.
    var boxOptions = boxSettings ? $.extend({}, options, boxSettings) : options;

    // Open the lightbox, and sets the correct delta.
    function open(e) {
      var link = e.currentTarget;
      var $this = $(link);
      var delta = parseInt($this.attr('data-b-delta'), 0);
      var containers = boxSettings.itemContainers;

      // Delta is set per field which is always 0 within a view, fix it.
      if (containers && delta === 0 && !$slider.length) {
        var len = containers.length;

        for (var i = 0; i < len; i++) {
          var $item = $this.closest(containers[i]);
          if ($item.length) {
            var check = $item.find(_trigger);

            if ($this[0] === check[0]) {
              delta = $item.index();
              break;
            }
          }
        }
      }

      setTimeout(function () {
        sbox = $container[0].slickLightbox;
        // The library doesn't go to the delta correctly.
        if (sbox && sbox.slick) {
          sbox.slick.slick('slickGoTo', delta || 0, true);
        }
      });
    }

    // Init the lightbox.
    $container.slickLightbox(boxOptions).on(events);

    // @todo remove when the library provides index argument to its initSlick().
    var _clink = 'click.' + _nick + 'link';
    $container.off(_clink).on(_clink, _trigger, open);
    $(elm).addClass(_mounted);
  }

  /**
   * Theme function for a blazy PhotoSwipe (Remote|local) video.
   *
   * @param {Object} settings
   *   An object with the following keys:
   * @param {Array} settings.item
   *   The array of item properties containing: media and html.
   *
   * @return {HTMLElement}
   *   Returns a HTMLElement object.
   */
  Drupal.theme.slickLightbox = function (settings) {
    var media = settings.media;
    var html;

    html = '<div class="media-wrapper media-wrapper--box media-wrapper--inline" style="width:' + media.width + 'px">';
    html += settings.html;
    html += '</div>';

    return html;
  };

  /**
   * Attaches Slick Lightbox gallery behavior to HTML element.
   *
   * @type {Drupal~behavior}
   */
  Drupal.behaviors.slickLightbox = {
    attach: function (context) {
      _db.once(process, _idOnce, _selector, context);
    },
    detach: function (context, settings, trigger) {
      if (trigger === 'unload') {
        _db.once.removeSafely(_idOnce, _selector, context);
      }
    }
  };

})(jQuery, Drupal, drupalSettings, dBlazy, this);
