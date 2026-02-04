(function (document, $, Drupal, once) {

  Drupal.behaviors.ad = {

    attach: function (context, settings) {
      var query = { ads: {} };

      $(once('ad-content', 'ad-content', context))
        .each(function () {
          var $placeholder = $(this);
          query.ads[$placeholder.attr('id')] = {
            size: $placeholder.attr('size'),
            bucket: $placeholder.attr('bucket'),
            arguments: $placeholder.attr('arguments') || {}
          };
        });

      if (Object.keys(query.ads).length > 0) {
        query.uid = settings.user.uid;
        query.url = document.URL;
        query.page_title = document.title;
        query.referrer = document.referrer;

        var url = Drupal.url('ad/content/render');
        $.get(
          url,
          query,
          function (responseData) {
            for (var id in responseData) {
              if (responseData.hasOwnProperty(id)) {
                $('#' + id).html(responseData[id]);
              }
            }
          },
          'json'
        );
      }
    }

  };

})(document, jQuery, Drupal, once);
