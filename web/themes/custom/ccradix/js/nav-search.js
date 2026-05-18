(function (Drupal) {
  Drupal.behaviors.ccradixNavSearch = {
    attach: function (context) {
      const toggle = context.querySelector('.nav-search-toggle');
      const panel = context.querySelector('.nav-search-panel');

      if (!toggle || !panel || toggle.dataset.searchReady) {
        return;
      }

      toggle.dataset.searchReady = 'true';

      toggle.addEventListener('click', function () {
        const isHidden = panel.hasAttribute('hidden');

        if (isHidden) {
          panel.removeAttribute('hidden');
          const input = panel.querySelector('input[type="search"]');
          if (input) {
            input.focus();
          }
        }
        else {
          panel.setAttribute('hidden', '');
        }
      });
    }
  };
})(Drupal);
