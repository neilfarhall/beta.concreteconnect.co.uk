
(function($, Drupal, drupalSettings) {
  "use strict";

  // To understand behaviors, see https://www.drupal.org/node/2269515
  Drupal.behaviors.basic = {
    attach: function (context, settings) {
      $('.quicktabs-tabs .media-gallery a').click(function () {
        $('.quicktabs-tabpage .flexslider').resize(); // this is it
      });
    }
  };

  // this does detaiching click from quicktabs, and leaves them for reload.
  // we'd need to rewrite the a href='' on these tabs in some preprocess function and ensure they actually work/show
  // $(document).ready(function() {
  //   // ------------------------------------------------------------
  //   // Quick Tabs deep linking work around
  //   // ------------------------------------------------------------
  //   // $('.quicktabs-tabs a').each(function(){
  //   //   // Unbind the quick tabs action
  //   //   $(this).unbind('click');
  //   //
  //   //   // Refresh page whenever the tab is clicked
  //   //   $(this).click(function(){
  //   //     var qtlink = $(this).attr('href');
  //   //     window.location.href = qtlink.substring(0, qtlink.indexOf('#'));
  //   //   });
  //   // });
  // });

}(jQuery, Drupal, drupalSettings));
