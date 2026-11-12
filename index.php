<?php
declare(strict_types=1);

$pageTitle = 'Overview · Kitchen Queen Admin';
$bodyTitle = 'Dashboard Overview';$assetPrefix = '';
require __DIR__ . "/includes/header.php";
?>
<div id="page">
    <div class="grid g-4" id="liveMetrics"></div>
    <div class="grid g-4" id="stats" style="margin-top:16px"></div>
    <div class="grid g-2" style="margin-top:16px">
      <div class="card">
        <div class="section-title"><h3>Smart Alerts</h3><a href="orders.php" class="btn btn-ghost btn-sm">View orders</a></div>
        <div id="alerts"></div>
      </div>
      <div class="card">
        <div class="section-title"><h3>Recent Reviews</h3><a href="reviews.php" class="btn btn-ghost btn-sm">All reviews</a></div>
        <div id="revSnap"></div>
      </div>
    </div>
    <div class="card" style="margin-top:16px">
      <div class="section-title"><h3>Recent Orders</h3></div>
      <div class="table-wrap">
        <table class="data" id="todayTable"></table>
      </div>
    </div>
  </div>
  <script>
    document.addEventListener("DOMContentLoaded", async () => {
      const paintLive = (m) => {
        const visits = Number(m.visits || 0);
        const ordersN = Number(m.orders || 0);
        const bouncers = Number(m.bouncers || Math.max(0, visits - ordersN));
        const rate = Number(m.conversion_rate || 0);
        const box = document.getElementById("liveMetrics");
        if (!box) return;
        box.innerHTML = `
          <div class="card stat"><div><p>Total Traffic (Visits)</p><h3>${visits.toLocaleString("en-PK")}</h3><small style="color:var(--muted)">Unique visitors today</small></div><div class="icon-bubble"><i class="bi bi-people-fill"></i></div></div>
          <div class="card stat"><div><p>Total Conversions (Orders)</p><h3>${ordersN.toLocaleString("en-PK")}</h3><small style="color:var(--muted)">Confirmed orders today</small></div><div class="icon-bubble"><i class="bi bi-bag-check-fill"></i></div></div>
          <div class="card stat"><div><p>Bouncers / Browsers</p><h3>${bouncers.toLocaleString("en-PK")}</h3><small style="color:var(--muted)">Visits minus orders</small></div><div class="icon-bubble"><i class="bi bi-door-open-fill"></i></div></div>
          <div class="card stat"><div><p>Conversion Rate</p><h3>${rate}%</h3><small style="color:var(--muted)">(Orders ÷ Visits) × 100</small></div><div class="icon-bubble"><i class="bi bi-graph-up-arrow"></i></div></div>`;
      };
      const loadLive = async () => {
        if (!window.KQApi || !KQApi.liveMetrics) return;
        try {
          const res = await KQApi.liveMetrics();
          paintLive((res && res.data) || {});
        } catch (e) {
          paintLive({ visits: 0, orders: 0, bouncers: 0, conversion_rate: 0 });
        }
      };
      paintLive({ visits: 0, orders: 0, bouncers: 0, conversion_rate: 0 });
      loadLive();
      setInterval(loadLive, 7000);

      let orders = [];
      let menu = [];
      let reviews = [];
      try {
        const [oRes, mRes, rRes] = await Promise.all([
          KQApi.orders({ range: "today" }),
          KQApi.menu(),
          KQApi.reviews()
        ]);
        orders = ((oRes.data && oRes.data.orders) || []).map(o => ({
          id: o.order_no, customer: o.customer_name, net: o.total,
          status: KQApp.uiStatus(o.status), rawStatus: o.status,
          payment: o.payment_method, payment_status: o.payment_status,
          items: o.items || [], date: o.placed_at
        }));
        menu = ((mRes.data && mRes.data.items) || []).map(m => ({
          name: m.name, stock: !!m.is_available
        }));
        reviews = ((rRes.data && rRes.data.reviews) || []).map(r => ({
          name: r.name, stars: Number(r.stars || 0), text: r.text || ""
        }));
      } catch (e) {
        document.getElementById("alerts").textContent = e.message || "Could not load live data";
      }
      const active = orders.filter(o => o.status !== "cancelled");
      const sales = active.reduce((s, o) => s + Number(o.net || 0), 0);
      const low = menu.filter(m => !m.stock);
      const pending = orders.filter(o => !["delivered", "cancelled", "awaiting_payment"].includes(o.status));
      const avg = reviews.length ? (reviews.reduce((s, r) => s + r.stars, 0) / reviews.length).toFixed(1) : "—";
      document.getElementById("stats").innerHTML = `
        <div class="card stat"><div><p>Today’s order value</p><h3>${KQApp.fmtPKR(sales)}</h3></div><div class="icon-bubble"><i class="bi bi-currency-exchange"></i></div></div>
        <div class="card stat"><div><p>Total Orders</p><h3>${active.length} Orders</h3></div><div class="icon-bubble"><i class="bi bi-bag-heart-fill"></i></div></div>
        <div class="card stat"><div><p>Store Rating</p><h3>★ ${avg} / 5.0</h3></div><div class="icon-bubble"><i class="bi bi-star-fill"></i></div></div>
        <div class="card stat"><div><p>Pending Kitchen</p><h3>${pending.length} Live</h3></div><div class="icon-bubble"><i class="bi bi-fire"></i></div></div>`;
      const alertHtml = [
        ...low.map(m => `<div class="alert-item"><span class="badge b-out">Stock</span><div><strong>${KQApp.esc(m.name)}</strong><div style="color:var(--muted);font-size:13px">Out of stock — turn on from Menu</div></div></div>`),
        ...orders.filter(o => o.payment === "easypaisa" && o.payment_status === "pending").slice(0, 6).map(o => `<div class="alert-item"><span class="badge b-online">Pay Approve</span><div><strong>${KQApp.esc(o.id)}</strong> · ${KQApp.esc(o.customer)}<div style="color:var(--muted);font-size:13px">EasyPaisa waiting for approval</div></div></div>`),
        ...pending.slice(0, 4).map(o => `<div class="alert-item"><span class="badge ${KQApp.statusClass(o.status)}">${o.status}</span><div><strong>${KQApp.esc(o.id)}</strong> · ${KQApp.esc(o.customer)}<div style="color:var(--muted);font-size:13px">${(o.items || []).map(i => i.name).join(", ")}</div></div></div>`)
      ].join("");
      document.getElementById("alerts").innerHTML = alertHtml || "<p style='color:var(--muted)'>No alerts right now.</p>";
      document.getElementById("revSnap").innerHTML = reviews.slice(0, 3).map(r => `
        <div class="review"><strong>${KQApp.esc(r.name)}</strong> <span class="stars">${"★".repeat(r.stars)}${"☆".repeat(Math.max(0, 5 - r.stars))}</span>
        <div style="font-size:13px;color:var(--muted);margin-top:4px">${KQApp.esc(r.text)}</div></div>`).join("") || "<p style='color:var(--muted)'>No reviews yet.</p>";
      document.getElementById("todayTable").innerHTML = `<thead><tr><th>Order</th><th>Customer</th><th>Items</th><th>Total</th><th>Status</th></tr></thead><tbody>` +
        (active.map(o => `<tr><td>${KQApp.esc(o.id)}</td><td>${KQApp.esc(o.customer)}</td><td>${(o.items || []).length} items</td><td>${KQApp.fmtPKR(o.net)}</td><td><span class="badge ${KQApp.statusClass(o.status)}">${o.status}</span></td></tr>`).join("") || `<tr><td colspan="5" style="color:var(--muted)">No orders today.</td></tr>`) + "</tbody>";
    });
  </script>
<?php require __DIR__ . "/includes/footer.php"; ?>
</body>
</html>