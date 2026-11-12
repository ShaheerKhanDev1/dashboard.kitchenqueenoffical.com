(function () {
  var host = location.hostname;
  var local = host === "localhost" || host === "127.0.0.1";
  if (local) {
    window.KQ_API_BASE = "/kitchenqueenoffical.com/api";
    window.KQ_SITE_ORIGIN = "/kitchenqueenoffical.com";
    return;
  }
  window.KQ_SITE_ORIGIN = location.protocol + "//" + host.replace(/^dashboard\./i, "");
  window.KQ_API_BASE = window.KQ_SITE_ORIGIN + "/api";
})();
