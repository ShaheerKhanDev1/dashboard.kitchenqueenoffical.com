<?php
$pageTitle = 'Analytics · Kitchen Queen Admin';
$bodyTitle = 'Sales Analytics';
$assetPrefix = '';
require __DIR__ . "/includes/header.php";
?>
<div id="page">
    <div class="card">
      <div class="section-title">
        <h3>Sales Overview</h3>
        <div class="tabs">
          <button class="tab active" data-p="daily">Daily</button>
        </div>
      </div>
      <canvas id="salesChart" height="110"></canvas>
    </div>
    <div class="grid g-2" style="margin-top:16px">
      <div class="card">
        <h3 class="section-title" style="margin-top:0">Payment Mode</h3>
        <canvas id="payChart" height="180"></canvas>
      </div>
      <div class="card">
        <h3 class="section-title" style="margin-top:0">Peak Hours Heatmap</h3>
        <canvas id="peakChart" height="180"></canvas>
      </div>
    </div>
    <div class="card" style="margin-top:16px">
      <div class="section-title"><h3>Top-Selling Menu Items</h3></div>
      <div class="table-wrap">
        <table class="data" id="top"></table>
      </div>
    </div>
  </div>
  <script src="assets/vendor/chart.umd.min.js"></script>
  <script>
    const gold = "#ffc107", accent = "#f3a812";
    Chart.defaults.color = "#6f6f6f";
    Chart.defaults.borderColor = "rgba(243,168,18,.18)";
    document.addEventListener("DOMContentLoaded", async () => {
      let data = { daily: [], payments: [], top_items: [], hours: [] };
      try {
        const res = await KQApi.analytics();
        data = res.data || data;
      } catch (e) {
        KQApp.toast(e.message || "Could not load analytics");
      }
      const daily = (data.daily || []).slice().reverse();
      new Chart(document.getElementById("salesChart"), {
        type: "line",
        data: {
          labels: daily.map(d => d.d) || ["No data"],
          datasets: [{ label: "Sales (PKR)", data: daily.map(d => Number(d.s || 0)), borderColor: accent, backgroundColor: "rgba(255,193,7,.22)", fill: true, tension: .35 }]
        },
        options: { plugins: { legend: { display: false } }, scales: { y: { ticks: { callback: v => v.toLocaleString() } } } }
      });
      const pay = data.payments || [];
      const payLabels = pay.map(p => p.payment_method === "cod" ? "Cash on Delivery" : (p.payment_method || "Other"));
      const payData = pay.map(p => Number(p.s || 0));
      new Chart(document.getElementById("payChart"), {
        type: "doughnut",
        data: { labels: payLabels.length ? payLabels : ["No sales yet"], datasets: [{ data: payData.length ? payData : [1], backgroundColor: [gold, accent, "#888"], borderWidth: 0 }] },
        options: { plugins: { legend: { position: "bottom" } } }
      });
      const hourMap = {};
      (data.hours || []).forEach(h => { hourMap[Number(h.h)] = Number(h.c || 0); });
      const hours = [...Array(24)].map((_, i) => hourMap[i] || 0);
      new Chart(document.getElementById("peakChart"), {
        type: "bar",
        data: { labels: [...Array(24)].map((_,i)=>i+":00"), datasets: [{ label: "Orders", data: hours, backgroundColor: hours.map(v => v>8?"#e03131":gold) }] },
        options: { plugins: { legend: { display: false } }, scales: { x: { ticks: { maxRotation: 0, autoSkip: true, maxTicksLimit: 12 } } } }
      });
      const top = data.top_items || [];
      document.getElementById("top").innerHTML = `<thead><tr><th>#</th><th>Dish</th><th>Units</th><th>Revenue</th></tr></thead><tbody>` +
        (top.map((m,i)=>`<tr><td>${i+1}</td><td>${KQApp.esc(m.name)}</td><td>${m.qty}</td><td>${KQApp.fmtPKR(m.revenue)}</td></tr>`).join("") || `<tr><td colspan="4" style="color:var(--muted)">No sales yet.</td></tr>`) + "</tbody>";
    });
  </script>
<?php require __DIR__ . "/includes/footer.php"; ?>
</body>
</html>
