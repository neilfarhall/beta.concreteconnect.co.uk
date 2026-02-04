/**
 * @file
 * Video Embed Media: Plyr video player behaviours.
 */

(function ($, Drupal, window) {

  /**
   * Attaches the Plyr video player to any applicable elements.
   *
   * @type {Drupal~behavior}
   *
   * @prop {Drupal~behaviorAttach} attach
   *   Attaches modal functionality for views.
   */
  Drupal.behaviors.videoEmbedMediaPlyr = {
    attach: function () {
      if (!window.plyr) {
        return;
      }

      plyr.setup({
        i18n: {
          restart: Drupal.t('Restart'),
          rewind: Drupal.t('Rewind {seektime} secs'),
          play: Drupal.t('Play'),
          pause: Drupal.t('Pause'),
          forward: Drupal.t('Forward {seektime} secs'),
          buffered: Drupal.t('buffered'),
          currentTime: Drupal.t('Current time'),
          duration: Drupal.t('Duration'),
          volume: Drupal.t('Volume'),
          toggleMute: Drupal.t('Toggle Mute'),
          toggleCaptions: Drupal.t('Toggle Captions'),
          toggleFullscreen: Drupal.t('Toggle Fullscreen')
        }
      });
    }
  };

})(jQuery, Drupal, window);
