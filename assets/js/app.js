(function () {
  const SKEY = "kq_admin_session";
  const fmtPKR = (n) => "Rs " + Number(n || 0).toLocaleString("en-PK");

  function session() {
    try { return JSON.parse(sessionStorage.getItem(SKEY) || "null"); } catch (e) { return null; }
  }
  function saveSession(s) {
    try { sessionStorage.setItem(SKEY, JSON.stringify(s || {})); } catch (e) {}
  }
  function toast(msg) {
    let el = document.getElementById("kqToast");
    if (!el) {
      el = document.createElement("div");
      el.id = "kqToast";
      el.style.cssText = "position:fixed;bottom:18px;right:18px;z-index:9999;background:#111;color:#fff;padding:10px 14px;border-radius:10px;font:500 13px Poppins,sans-serif";
      document.body.appendChild(el);
    }
    el.textContent = msg;
    el.style.opacity = "1";
    setTimeout(() => { el.style.opacity = "0"; }, 2600);
  }

  function uiStatus(st) {
    const s = String(st || "").toLowerCase();
    if (["preparing", "ready", "accepted"].includes(s)) return "preparing";
    if (s === "out_for_delivery") return s;
    if (["completed", "delivered"].includes(s)) return "delivered";
    if (["cancelled", "payment_rejected"].includes(s)) return "cancelled";
    if (s === "awaiting_payment") return "awaiting_payment";
    return "pending";
  }
  function statusLabel(st) {
    const map = { pending: "Call pending", call_pending: "Call pending", preparing: "Kitchen preparing", delivered: "Delivered", cancelled: "Cancelled", awaiting_payment: "Awaiting Payment", completed: "Delivered", out_for_delivery: "Out for delivery", confirmed: "Call pending" };
    return map[st] || map[uiStatus(st)] || st;
  }
  function statusClass(st) {
    const s = uiStatus(st);
    if (s === "preparing") return "b-prep";
    if (s === "delivered") return "b-done";
    if (s === "cancelled") return "b-cancel";
    return "b-wait";
  }
  function parseOnline(v) {
    return !(v === false || v === 0 || v === "0" || v === "false" || v === "off" || v === "offline");
  }
  function applyStoreUi(on) {
    const sw = document.getElementById("setStoreSwitch") || document.getElementById("hdrStoreSwitch");
    const lab = document.getElementById("setStatusLabel") || document.getElementById("hdrStatusLabel");
    const wrap = document.getElementById("setStatusWrap") || document.getElementById("hdrStatusWrap");
    if (sw) sw.checked = !!on;
    if (lab) lab.textContent = on ? "ONLINE" : "OFFLINE";
    if (wrap) wrap.classList.toggle("online", !!on);
  }
  async function saveStoreOnline(val) {
    return window.KQApi.saveSettings({ store_online: val ? "true" : "false" });
  }
  function mediaUrl(u) {
    if (!u) return "";
    if (/^https?:/i.test(u)) return u;
    const origin = (window.KQ_SITE_ORIGIN || location.origin).replace(/\/+$/, "");
    return origin + (u.charAt(0) === "/" ? u : "/" + u);
  }

  function guard() {
    const path = location.pathname;
    const authPage = /\/auth\//.test(path);
    if (authPage) return;
    if (!window.KQApi || !KQApi.token()) {
      location.replace((path.includes("/auth/") ? "" : "auth/") + "login.php");
    }
  }

  window.KQApp = {
    esc: (s) => String(s ?? "").replace(/[&<>"']/g, c => ({"&":"&amp;","<":"&lt;",">":"&gt;",'"':"&quot;","'":"&#39;"}[c])),
    fmtPKR, session, saveSession, toast, uiStatus, statusLabel, statusClass,
    parseOnline, applyStoreUi, saveStoreOnline, mediaUrl, guard
  };

  document.addEventListener("DOMContentLoaded", guard);
})();
