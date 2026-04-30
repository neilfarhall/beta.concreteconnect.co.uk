/**
 * @file
 * A JavaScript file for the theme.
 *
 * In order for this JavaScript to be loaded on pages, see the instructions in
 * the README.txt next to this file.
 */

// JavaScript should be made compatible with libraries other than jQuery by
// wrapping it with an "anonymous closure". See:
// - https://drupal.org/node/1446420
// - http://www.adequatelygood.com/2010/3/JavaScript-Module-Pattern-In-Depth
(function ($, Drupal, once) {

  'use strict';

  // To understand behaviors, see https://drupal.org/node/756722#behaviors
  Drupal.behaviors.rotateImage = {
    attach: function (context) {
      once('rotate-image', $(context).find('.rotate-icon')).forEach(function (element) {
        $(element).on('click', function (e) {
          // Prevent default click on rotate icon
          e.preventDefault();
          const $rotate_input = $(this).closest('.image-widget').find('.rotate');
          let rotate = parseInt($rotate_input.attr('value'));
          const $image = $(this).closest('.image-widget').find('img')
            .add($(this).closest('.media-library-add-form li').find('[data-drupal-selector="edit-preview"] img'))
            .add($(this).closest('.js-media-library-add-form li').find('[data-drupal-selector="edit-preview"] img'));

          // Remove current rotate CSS class
          $image.removeClass('rotate-' + rotate);

          // Update rotate value
          rotate = rotate < 270 ? rotate + 90 : 0;

          // Add new rotate CSS class and update rotate value in elements.
          $image.addClass('rotate-' + rotate);
          $rotate_input.attr('value', rotate);
          let message = Drupal.t('Image rotated 90 degrees.');
          if (rotate === 0) {
            message = Drupal.t('Image is again in initial position.');
          }
          Drupal.announce(message);
        });
      });
    }
  };
})(jQuery, Drupal, once);
