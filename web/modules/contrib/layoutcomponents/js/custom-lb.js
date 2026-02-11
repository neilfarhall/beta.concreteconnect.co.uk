/**
 * @file
 * Layout Components behaviors.
 */

(function ($, Drupal, drupalSettings) {

  'use strict';

  var ajax = Drupal.ajax,
    behaviors = Drupal.behaviors;

  behaviors.customLB = {
    attach: function (context) {
      $('.js-layout-builder-block *').on('mouseenter', function(){
        if(!$(this).closest('.js-layout-builder-block').hasClass('intoBlock')){
          $(this).closest('.js-layout-builder-block').addClass('intoBlock');
        }
      });
      $('.js-layout-builder-block').on('mouseleave', function(){
        if($(this).hasClass('intoBlock')){
          $(this).removeClass('intoBlock');
        }
      });
    }
  }
})(jQuery, Drupal, drupalSettings);
