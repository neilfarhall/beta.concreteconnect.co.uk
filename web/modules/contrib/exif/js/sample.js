/**
 * @file
 * File 'sample.js' is used in sample page for add some L&F.
 */

(function ($, Drupal, once) {
  Drupal.behaviors.exif_sample = {
    attach() {
      // Bind a click-handler to the header 'tr' elements.
      $(once('exif_sample', 'tr.metadata-section')).click(function () {
        // Toggle visibility of subsequent 'tr' elements until the next header.
        $(this).nextUntil('tr.metadata-section').toggleClass('hidden');
      });
    },
  };
})(jQuery, Drupal, once);
