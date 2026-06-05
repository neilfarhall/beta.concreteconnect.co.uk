(function (Drupal) {
  Drupal.behaviors.newsletterPopupClose = {
    attach: function () {
      if (document.body.dataset.newsletterPopupCloseAdded) {
        return;
      }

      document.body.dataset.newsletterPopupCloseAdded = 'true';

      const storageKey = 'cc_newsletter_popup_closed_at';
      const hideForDays = 30;
      const hideForMs = hideForDays * 24 * 60 * 60 * 1000;

      function getPopup() {
        return document.querySelector('.simple-popup-blocks-global');
      }

      function hidePopup() {
        const popup = getPopup();

        if (popup) {
          popup.style.display = 'none';
        }

        document.body.style.overflow = 'auto';
      }

      function shouldHidePopup() {
        const closedAt = Number(localStorage.getItem(storageKey) || 0);

        return closedAt && (Date.now() - closedAt < hideForMs);
      }

      function closePopup() {
        localStorage.setItem(storageKey, String(Date.now()));
        hidePopup();
      }

      // Hide shortly after the popup module has rendered it.
      if (shouldHidePopup()) {
        hidePopup();
      }

      setTimeout(function () {
        if (shouldHidePopup()) {
          hidePopup();
        }
      }, 50);

      document.addEventListener('click', function (e) {
        const popup = getPopup();
        const popupContent = document.querySelector('.block--ccradix-newslettersignuppopup');

        if (!popup || popup.style.display === 'none') {
          return;
        }

        // Click inside popup content = do nothing.
        if (popupContent && popupContent.contains(e.target)) {
          return;
        }

        // Click outside popup content = close and remember for 30 days.
        closePopup();
      }, true);
    }
  };
})(Drupal);
