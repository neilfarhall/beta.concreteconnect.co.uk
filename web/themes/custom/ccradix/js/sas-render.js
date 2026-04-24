(function (Drupal, once) {
  Drupal.behaviors.smartAdserverRender = {
    attach(context) {
      once('smartadserver-render', '[id^="sas_"]', context).forEach((el) => {
        const id = el.id.replace('sas_', '');

        if (window.sas && sas.cmd) {
          sas.cmd.push(function () {
            sas.render(id);
          });
        }
      });
    }
  };
})(Drupal, once);
