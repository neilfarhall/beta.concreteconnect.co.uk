/**
 * @file
 * Layout Components behaviors.
 */

(function ($, Drupal, drupalSettings) {

  'use strict';

  var ajax = Drupal.ajax,
    behaviors = Drupal.behaviors;

  behaviors.editBlockInline = {
    attach: function (context) {
      var selector = '.js-layout-builder-block';
      Array.prototype.forEach.call(context.querySelectorAll(selector), function (block) {
        $(block).on('dblclick', function (e) {
          var li = $(this).find('li.layout-builder-block-update > a');
          if (li) {
            li.click();
          }
        })
      })
    }
  }

  behaviors.controlForm = {
    attach: function () {
      $('div[data-drupal-selector="edit-layout-builder-layout-wrapper"], div[data-drupal-selector="edit-settings-block-form"]').removeClass('col-auto');

    }
  };

  /**
   * hiddeFormElements
   *
   * Function to hidde form elements.
   */
  behaviors.hiddeFormElements = {
    attach: function () {
      $('.layoutcomponents-element-hidden').each(function (i) {
        $(this).closest('.draggable').addClass('element-hidden');
      });

      // Hide alteral nav.
      $('ul.inline-block-list li a').on('click', function () {
        $('.ui-dialog-titlebar-close').click();

      });

      // Handle
      $('#layout-builder').on('replaceWith', function (event) {
        alert('closed');
      });
    }
  };

  /**
   * Check if the string is empty.
   *
   * @type {Drupal}
   *
   * @return boolean
   *   True or false.
   */
  Drupal.isEmpty = function (data) {
    if (data == null || data.length < 1 || data === '') {
      return true;
    }
    return false;
  }

  behaviors.parallax = {
    attach: function (context) {
      $('.parallax-window').each(function () {
        let element = $(this);
        $(element).parallax({ imageSrc: element.attr('data-image-src') });
      });
    }
  };




})(jQuery, Drupal, drupalSettings);
