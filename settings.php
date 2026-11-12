<?php
$pageTitle = 'Settings · Kitchen Queen Admin';
$bodyTitle = 'Store Settings';
$assetPrefix = '';
require __DIR__ . "/includes/header.php";
?>
<div id="page">
    <div class="grid g-2">
      <div class="card">
        <h3 class="section-title" style="margin-top:0">Store Profile</h3>
        <div class="field"><label>Store Name</label><input id="store" /></div>
        <div class="field"><label>Contact Phone</label><input id="phone" /></div>
        <div class="field"><label>Admin order email</label><input id="adminEmail" placeholder="you@example.com" /></div>
        <div class="field"><label>Unavailable message</label><input id="msg" placeholder="Website is currently unavailable" /></div>
        <button class="btn btn-gold" id="saveProfile">Save Settings</button>
      </div>
      <div class="card">
        <h3 class="section-title" style="margin-top:0">WhatsApp Cloud API</h3>
        <p style="color:var(--muted);font-size:13px">Admin login OTP aur order alerts dono existing <b>order_payment_alert</b> utility template se jayenge. Phone Number ID aur Access Token WhatsApp Manager se paste karein.</p>
        <div class="field"><label>Enable WhatsApp</label>
          <label class="switch"><input type="checkbox" id="waEnabled"><span class="slider"></span></label>
        </div>
        <div class="field"><label>Admin WhatsApp number</label><input id="waAdmin" placeholder="" /></div>
        <div class="field"><label>Phone Number ID</label><input id="waPhoneId" placeholder="WhatsApp Manager → Phone numbers" /></div>
        <div class="field"><label>Access Token</label><input id="waToken" placeholder="Permanent Cloud API token" /></div>
        <div class="field"><label>OTP utility template</label><input id="waOtpTpl" placeholder="order_payment_alert" /></div>
        <div class="field"><label>Order utility template</label><input id="waOrderTpl" placeholder="order_payment_alert" /></div>
        <div class="field"><label>Template language</label><input id="waLang" placeholder="en" /></div>
        <button class="btn btn-gold" id="saveWa">Save WhatsApp</button>
        <button class="btn" id="testWa" type="button" style="margin-top:8px">Send test order alert to admin WhatsApp</button>
      </div>
      <div class="card">
        <h3 class="section-title" style="margin-top:0">Store status</h3>
        <p style="color:var(--muted);font-size:13px">Top bar ka Online/Offline button bhi yahi setting save karta hai. Website instantly follow karti hai.</p>
        <div class="status-toggle online" id="setStatusWrap" style="justify-content:space-between;max-width:280px">
          <span id="setStatusLabel">ONLINE</span>
          <label class="switch">
            <input type="checkbox" id="setStoreSwitch" checked>
            <span class="slider"></span>
          </label>
        </div>
      </div>
    </div>
  </div>
  <script>
    document.addEventListener("DOMContentLoaded", async () => {
      let live = {};
      try {
        const res = await KQApi.settings();
        live = res.data || {};
      } catch (e) {}
      document.getElementById("store").value = live.store_name || "Kitchen Queen";
      document.getElementById("phone").value = live.contact_phone || "";
      document.getElementById("adminEmail").value = live.admin_email || "";
      document.getElementById("msg").value = live.store_unavailable_message || "";
      document.getElementById("waEnabled").checked = String(live.wa_enabled ?? "true") !== "false" && live.wa_enabled !== false && live.wa_enabled !== 0;
      document.getElementById("waAdmin").value = live.wa_admin_number || live.admin_whatsapp || "";
      document.getElementById("waPhoneId").value = live.wa_phone_number_id || "";
      document.getElementById("waToken").value = live.wa_access_token || "";
      document.getElementById("waOtpTpl").value = live.wa_otp_template || "order_payment_alert";
      document.getElementById("waOrderTpl").value = live.wa_utility_template || "order_payment_alert";
      document.getElementById("waLang").value = live.wa_template_lang || live.wa_otp_template_lang || "en";
      const on = KQApp.parseOnline(live.store_online);
      KQApp.applyStoreUi(on);

      document.getElementById("setStoreSwitch").onchange = async (e) => {
        const val = e.target.checked;
        e.target.disabled = true;
        try {
          await KQApp.saveStoreOnline(val);
          KQApp.toast(val ? "Store ONLINE — website orders open" : "Store OFFLINE — website orders paused");
        } catch (err) {
          e.target.checked = !val;
          KQApp.applyStoreUi(!val);
          KQApp.toast(err.message || "Failed");
        } finally {
          e.target.disabled = false;
        }
      };

      document.getElementById("saveProfile").onclick = async () => {
        try {
          await KQApi.saveSettings({
            store_name: document.getElementById("store").value,
            contact_phone: document.getElementById("phone").value,
            admin_email: document.getElementById("adminEmail").value,
            store_unavailable_message: document.getElementById("msg").value
          });
          KQApp.toast("Settings saved");
        } catch (e) {
          KQApp.toast(e.message || "Failed");
        }
      };

      document.getElementById("saveWa").onclick = async () => {
        try {
          await KQApi.saveSettings({
            wa_enabled: document.getElementById("waEnabled").checked ? "true" : "false",
            wa_admin_number: document.getElementById("waAdmin").value.trim(),
            admin_whatsapp: document.getElementById("waAdmin").value.trim(),
            wa_phone_number_id: document.getElementById("waPhoneId").value.trim(),
            wa_access_token: document.getElementById("waToken").value.trim(),
            wa_otp_template: document.getElementById("waOtpTpl").value.trim() || "order_payment_alert",
            wa_utility_template: document.getElementById("waOrderTpl").value.trim() || "order_payment_alert",
            wa_template_lang: document.getElementById("waLang").value.trim() || "en",
            wa_otp_template_lang: document.getElementById("waLang").value.trim() || "en"
          });
          KQApp.toast("WhatsApp settings saved");
        } catch (e) {
          KQApp.toast(e.message || "Failed");
        }
      };

      document.getElementById("testWa").onclick = async () => {
        try {
          const res = await fetch((window.KQ_API_BASE || (location.origin + "/api")).replace(/\/+$/, "") + "/admin/whatsapp/test", {
            method: "POST",
            headers: {
              "Content-Type": "application/json",
              "Authorization": "Bearer " + (KQApi.token() || "")
            },
            body: "{}"
          });
          const json = await res.json();
          if (!json || json.ok === false) throw new Error((json && json.message) || "Test failed");
          const d = json.data || {};
          KQApp.toast(d.sent ? ("Test OTP sent to " + (d.to || "admin")) : ("Failed: " + (d.error || json.message)));
        } catch (e) {
          KQApp.toast(e.message || "Test failed");
        }
      };
    });
  </script>
<?php require __DIR__ . "/includes/footer.php"; ?>
</body>
</html>
