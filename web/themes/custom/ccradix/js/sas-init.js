(function () {
  window.sas = window.sas || {};
  sas.cmd = sas.cmd || [];

  sas.cmd.push(function () {
    sas.setup({
      networkid: 2534,
      domain: "https://www14.smartadserver.com",
      async: true
    });

    sas.call("onecall", {
      siteId: 625270,
      pageId: 1907300,
      formats: [
        { id: 53374 },
        { id: 53336 },
        { id: 53425 },
        { id: 53422 }
      ],
      target: ''
    });
  });
})();
