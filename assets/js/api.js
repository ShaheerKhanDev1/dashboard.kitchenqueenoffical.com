/* Kitchen Queen admin API client */
(function () {
  const BASE = (window.KQ_API_BASE || (location.origin + "/api")).replace(/\/+$/, "");
  const TOKEN_KEY = "kq_admin_token";

  function token() {
    try { return localStorage.getItem(TOKEN_KEY) || ""; } catch (e) { return ""; }
  }
  function setToken(t) {
    try {
      if (t) localStorage.setItem(TOKEN_KEY, t);
      else localStorage.removeItem(TOKEN_KEY);
    } catch (e) {}
  }

  async function req(path, opts) {
    const headers = Object.assign({ Accept: "application/json" }, (opts && opts.headers) || {});
    const t = token();
    if (t) headers["Authorization"] = "Bearer " + t;
    if (opts && opts.body && !(opts.body instanceof FormData) && !headers["Content-Type"]) {
      headers["Content-Type"] = "application/json";
    }
    const controller = new AbortController();
    const timeout = setTimeout(() => controller.abort(), 20000);
    let res;
    try { res = await fetch(BASE + path, Object.assign({}, opts, { headers, signal:controller.signal, cache:'no-store' })); }
    finally { clearTimeout(timeout); }
    const text = await res.text();
    let json = null;
    try { json = text ? JSON.parse(text) : null; } catch (e) { json = null; }
    if (!res.ok || !json || json.ok === false) {
      const err = new Error((json && json.message) || ("Request failed (" + res.status + ")"));
      err.status = res.status;
      err.payload = json;
      throw err;
    }
    return json;
  }

  function qs(params) {
    const u = new URLSearchParams();
    Object.keys(params || {}).forEach((k) => {
      if (params[k] !== undefined && params[k] !== null && params[k] !== "") u.set(k, params[k]);
    });
    const s = u.toString();
    return s ? ("?" + s) : "";
  }

  window.KQApi = {
    base: BASE,
    token, setToken,
    setup() { return req("/admin/setup"); },
    register(body) { return req("/admin/register", { method: "POST", body: JSON.stringify(body) }); },
    login(body) { return req("/admin/login", { method: "POST", body: JSON.stringify(body) }); },
    requestAdminOtp(body) { return req("/admin/otp/request", { method: "POST", body: JSON.stringify(body) }); },
    verifyAdminOtp(body) { return req("/admin/otp/verify", { method: "POST", body: JSON.stringify(body) }); },
    me() { return req("/admin/me"); },
    logout() { return req("/admin/logout", { method: "POST", body: "{}" }); },
    orders(params) { return req("/admin/orders" + qs(params)); },
    order(id) { return req("/admin/orders/" + id); },
    setStatus(id, status) { return req("/admin/orders/" + id + "/status", { method: "POST", body: JSON.stringify({ status }) }); },
    decidePayment(id, decision) { return req("/admin/orders/" + id + "/payment", { method: "POST", body: JSON.stringify({ decision }) }); },
    menu() { return req("/admin/menu"); },
    saveMenu(id, body) { return req("/admin/menu/" + id, { method: "POST", body: JSON.stringify(body) }); },
    setStock(id, on) { return req("/admin/menu/" + encodeURIComponent(id), { method: "POST", body: JSON.stringify({ is_available: on ? 1 : 0 }) }); },
    createMenu(formData) { return req("/admin/menu", { method: "POST", body: formData }); },
    categories() { return req("/admin/categories"); },
    areas() { return req("/admin/areas"); },
    createArea(body) { return req("/admin/areas", { method: "POST", body: JSON.stringify(body) }); },
    saveArea(id, body) { return req("/admin/areas/" + id, { method: "POST", body: JSON.stringify(body) }); },
    surgeAreas(body) { return req("/admin/areas/surge", { method: "POST", body: JSON.stringify(body) }); },
    settings() { return req("/admin/settings"); },
    saveSettings(body) { return req("/admin/settings", { method: "POST", body: JSON.stringify(body) }); },
    reviews() { return req("/admin/reviews"); },
    saveReview(id, body) { return req("/admin/reviews/" + id, { method: "POST", body: JSON.stringify(body) }); },
    analytics() { return req("/admin/analytics"); },
    finance() { return req("/admin/finance"); },
    customers() { return req('/admin/customers'); },
    setCustomer(id, active) { return req('/admin/users/'+id,{method:'POST',body:JSON.stringify({is_active:active})}); },
    async slip(id) {
      const res=await fetch(BASE+'/admin/orders/'+id+'/slip',{headers:{Authorization:'Bearer '+token()}});
      if (!res.ok) throw new Error('Payment screenshot unavailable');
      return URL.createObjectURL(await res.blob());
    },
    notifications() { return req("/admin/notifications"); },
    liveMetrics() { return req("/admin/live-metrics"); }
  };
})();
