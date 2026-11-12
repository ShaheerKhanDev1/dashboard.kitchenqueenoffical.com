<?php
$pageTitle = 'Orders · Kitchen Queen Admin';
$bodyTitle = 'Order Management';
$assetPrefix = '';
require __DIR__ . "/includes/header.php";
?>
<div id="page">
    <div class="card">
      <div class="tabs" id="tabs">
        <button class="tab active" data-range="all">All</button>
        <button class="tab" data-range="today">Today</button>
        <button class="tab" data-range="yesterday">Yesterday</button>
        <button class="tab" data-range="7">Last 7 Days</button>
        <button class="tab" data-range="30">Last 30 Days</button>
        <button class="tab" data-range="custom">Custom</button>
      </div>
      <div class="searchbar" style="margin-top:14px">
        <input id="q" placeholder="Search Order ID or customer" />
        <select id="st">
          <option value="">All statuses</option>
          <option value="pending">Pending</option>
          <option value="preparing">Preparing</option>
          <option value="delivered">Delivered</option>
          <option value="cancelled">Cancelled</option>
          <option value="awaiting_payment">Awaiting Payment</option>
        </select>
        <input type="date" id="from" style="display:none" />
        <input type="date" id="to" style="display:none" />
      </div>
    </div>
    <div class="card" style="margin-top:16px">
      <div class="table-wrap">
        <table class="data" id="tbl"></table>
      </div>
    </div>
  </div>
  <div class="modal-back" id="modal">
    <div class="modal" id="modalBody"></div>
  </div>
  <script>
    let range = "all";
    document.getElementById("st").value=new URLSearchParams(location.search).get("status") || "";
    let ORDERS = [];
    function ui(o) {
      return {
        raw: o,
        id: o.order_no,
        dbId: o.id,
        date: (o.placed_at || "").replace(" ", "T"),
        customer: o.customer_name,
        phone: o.customer_phone,
        status: KQApp.statusLabel(o.status),
        uiStatus: KQApp.uiStatus(o.status),
        payment: o.payment_method === "cod" ? "COD" : "EasyPaisa",
        type: o.order_type === "pickup" ? "Pickup" : "Delivery",
        items: (o.items || []).map(i => ({ name: i.name, qty: i.qty, extra: [i.spice, i.extras].filter(Boolean).join(" · "), price: i.unit_price })),
        subtotal: o.subtotal, discount: o.discount, delivery: o.delivery_fee, tax: o.tax, net: o.total,
        address: [o.address_line, o.area_name].filter(Boolean).join(", "),
        timeline: o.timeline || [],
        payment_status: o.payment_status,
        screenshot: o.screenshot,
        payment_status: o.payment_status,
        payment_method: o.payment_method
      };
    }
    async function load() {
      const params = { range };
      const st = document.getElementById("st").value;
      const q = document.getElementById("q").value.trim();
      if (st) params.status = st;
      if (q) params.q = q;
      if (range === "custom") {
        if (document.getElementById("from").value) params.from = document.getElementById("from").value;
        if (document.getElementById("to").value) params.to = document.getElementById("to").value;
      }
      try {
        const res = await KQApi.orders(params);
        ORDERS = ((res.data && res.data.orders) || []).map(ui);
      } catch (e) {
        ORDERS = [];
        KQApp.toast(e.message || "Could not load orders");
      }
      render();
    }
    function render() {
      const rows = ORDERS;
      document.getElementById("tbl").innerHTML = `<thead><tr><th>ID</th><th>When</th><th>Customer</th><th>Pay</th><th>Total</th><th>Status</th><th></th></tr></thead><tbody>` +
        rows.map(o => `<tr>
          <td>${KQApp.esc(o.id)}</td>
          <td>${(o.date || "").replace("T"," ").slice(0,16)}</td>
          <td>${KQApp.esc(o.customer)}<div style="color:var(--muted);font-size:12px"><a href="tel:${KQApp.esc(o.phone)}">${KQApp.esc(o.phone)}</a></div></td>
          <td><span class="badge ${o.payment==="COD"?"b-cod":"b-online"}">${o.payment}</span></td>
          <td>${KQApp.fmtPKR(o.net)}</td>
          <td><span class="badge ${KQApp.statusClass(o.uiStatus)}">${o.status}</span></td>
          <td><button class="btn btn-ghost btn-sm" onclick="openOrder(${o.dbId})">Details</button></td>
        </tr>`).join("") + (rows.length ? "" : `<tr><td colspan="7" style="color:var(--muted)">No orders in this filter.</td></tr>`) + "</tbody>";
    }
    window.openOrder = function (id) {
      const o = ORDERS.find(x => x.dbId === id);
      if (!o) return;
      const logs = o.timeline;
      const has = (s) => logs.some(l => l.to_status === s);
      const when = (s) => { const l = logs.find(x => x.to_status === s); return l ? String(l.created_at).slice(11,16) : "—"; };
      const nexts = ["call_pending","preparing","out_for_delivery","delivered","cancelled"];
      document.getElementById("modalBody").innerHTML = `
        <div style="display:flex;justify-content:space-between;align-items:center">
          <h3 style="font-family:var(--font-s);margin:0">${KQApp.esc(o.id)}</h3>
          <button class="btn btn-ghost btn-sm" onclick="closeModal()">Close</button>
        </div>
        <p style="color:var(--muted)">${KQApp.esc(o.customer)}<br>${KQApp.esc(o.address)}</p>
        <div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:10px">
          <a class="btn btn-gold btn-sm" href="tel:${KQApp.esc(o.phone)}"><i class="bi bi-telephone-fill"></i> Call ${KQApp.esc(o.phone)}</a>
          <a class="btn btn-ghost btn-sm" href="https://wa.me/92${String(o.phone||"").replace(/\D/g,"").slice(-10)}" target="_blank">WhatsApp</a>
          <button class="btn btn-ghost btn-sm" onclick="navigator.clipboard.writeText('${KQApp.esc(o.phone)}')">Copy number</button>
        </div>
        <div class="timeline">
          <div class="t-step ${has("confirmed")||has("awaiting_payment")?"done":""}">Received<br>${when("confirmed")!=="—"?when("confirmed"):when("awaiting_payment")}</div>
          <div class="t-step ${has("accepted")?"done":""}">Accepted<br>${when("accepted")}</div>
          <div class="t-step ${has("preparing")||has("ready")?"done":""}">Prepared<br>${when("preparing")}</div>
          <div class="t-step ${has("completed")||has("out_for_delivery")?"done":""}">Delivered<br>${when("completed")}</div>
        </div>
        <table class="data" style="min-width:0">
          <thead><tr><th>Item</th><th>Qty</th><th>Add-on</th><th>Price</th></tr></thead>
          <tbody>${o.items.map(i=>`<tr><td>${KQApp.esc(i.name)}</td><td>${i.qty}</td><td>${i.extra||"—"}</td><td>${KQApp.fmtPKR(i.price*i.qty)}</td></tr>`).join("")}</tbody>
        </table>
        <div style="margin-top:12px;font-size:14px;line-height:1.8">
          Subtotal: ${KQApp.fmtPKR(o.subtotal)}<br>
          Discounts: − ${KQApp.fmtPKR(o.discount)}<br>
          Delivery Fee: ${KQApp.fmtPKR(o.delivery)}<br>
          Taxes: ${KQApp.fmtPKR(o.tax)}<br>
          <strong>Order total: ${KQApp.fmtPKR(o.net)}</strong>
        </div>
        <div style="margin-top:10px"><span class="badge ${o.payment==="COD"?"b-cod":"b-online"}">${o.payment}</span>
        <span class="badge">${o.payment_status}</span></div>
        ${o.screenshot && o.screenshot.url ? `<div class="pay-slip" style="margin-top:10px"><p style="margin:0 0 6px;font-size:13px">EasyPaisa slip</p><img id="orderSlip" alt="Payment screenshot loading"></div>` : ""}
        ${o.raw.payment_method==="easypaisa" && (o.payment_status==="pending" || o.raw.status==="awaiting_payment") ? `<div style="margin-top:10px;padding:10px;border:1px dashed #c9a227;border-radius:8px">
          <strong>Payment approval required</strong>
          <p style="margin:6px 0 10px;font-size:13px;color:var(--muted)">Order stays unconfirmed until you approve the EasyPaisa payment.</p>
          <div style="display:flex;gap:8px">
          <button class="btn btn-gold btn-sm" onclick="pay(${o.dbId},'approve')">Approve Payment</button>
          <button class="btn btn-red btn-sm" onclick="pay(${o.dbId},'reject')">Reject Payment</button>
          </div>
        </div>` : ""}
        <div style="margin-top:12px;display:flex;flex-wrap:wrap;gap:8px">
          ${nexts.map(s=>`<button class="btn btn-ghost btn-sm" onclick="setSt(${o.dbId},'${s}')">${s.replaceAll("_"," ")}</button>`).join("")}
        </div>`;
      document.getElementById("modal").classList.add("show");
      if(o.screenshot) KQApi.slip(o.dbId).then(url=>{const img=document.getElementById('orderSlip');if(img){img.onload=()=>URL.revokeObjectURL(url);img.src=url;}}).catch(e=>KQApp.toast(e.message));
    };
    window.setSt = async (id, st) => { try {await KQApi.setStatus(id, st); closeModal(); await load();} catch(e){KQApp.toast(e.message);} };
    window.pay = async (id, d) => { try {await KQApi.decidePayment(id, d); closeModal(); await load();} catch(e){KQApp.toast(e.message);} };
    window.closeModal = () => document.getElementById("modal").classList.remove("show");
    document.getElementById("modal").addEventListener("click", (e) => { if (e.target.id === "modal") closeModal(); });
    document.querySelectorAll(".tab").forEach(btn => btn.addEventListener("click", () => {
      document.querySelectorAll(".tab").forEach(t => t.classList.remove("active"));
      btn.classList.add("active");
      range = btn.dataset.range;
      const show = range === "custom";
      document.getElementById("from").style.display = show ? "block" : "none";
      document.getElementById("to").style.display = show ? "block" : "none";
      load();
    }));
    ["q","st","from","to"].forEach(id => document.getElementById(id).addEventListener("change", load));
    document.getElementById("q").addEventListener("input", () => { clearTimeout(window._oq); window._oq = setTimeout(load, 300); });
    document.addEventListener("DOMContentLoaded", load);
    document.addEventListener('kq:payment',load);
    document.addEventListener('keydown',e=>{if(e.key==='Escape')closeModal();});
    setInterval(()=>{if(!document.hidden&&!document.getElementById('modal').classList.contains('show'))load();},10000);
  </script>
<?php require __DIR__ . "/includes/footer.php"; ?>
</body>
</html>
