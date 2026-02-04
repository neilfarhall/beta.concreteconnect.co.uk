/**
 * @file
 * Layout Components behaviors.
 */

(function ($, Drupal, once) {

  'use strict';

  var ajax = Drupal.ajax,
      behaviors = Drupal.behaviors;

  behaviors.lcEditTooltip = {
    attach: function (context) {
      $(once('tooltip', '.lc_editor-link, .lc-lateral-info', context)).tooltip({
        tooltipClass:"lc-tooltip"
      });
    }
  }

})(jQuery, Drupal, once);
