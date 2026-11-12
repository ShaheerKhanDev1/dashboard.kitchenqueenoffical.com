<?php
$pageTitle = 'Finance · Kitchen Queen Admin';
$bodyTitle = 'Finance & Invoices';
$assetPrefix = '';
require __DIR__ . "/includes/header.php";
?>
<div id="page">
    <div class="grid g-3" id="sum"></div>
    <div class="card" style="margin-top:16px">
      <div class="section-title">
        <h3>Payout history</h3>
        <div style="display:flex;gap:8px;flex-wrap:wrap">
          <button class="btn btn-gold btn-sm" id="xlsxBtn"><i class="bi bi-file-earmark-spreadsheet"></i> Export Excel</button>
          <button class="btn btn-red btn-sm" id="pdfBtn"><i class="bi bi-printer"></i> Print Invoice PDF</button>
        </div>
      </div>
      <div class="table-wrap">
        <table class="data" id="tbl"></table>
      </div>
    </div>
  </div>
  <script>
    document.addEventListener("DOMContentLoaded", async () => {
      let pack = { week_total: 0, month_total: 0, completed_count: 0, orders: [] };
      try {
        const res = await KQApi.finance();
        pack = res.data || pack;
      } catch (e) {
        KQApp.toast(e.message || "Could not load finance");
      }
      const done = (pack.orders || []).map(o => ({
        id: o.order_no,
        date: String(o.placed_at || "").slice(0, 10),
        customer: o.customer_name,
        payment: o.payment_method === "cod" ? "COD" : "EasyPaisa",
        subtotal: o.subtotal, discount: o.discount, delivery: o.delivery_fee, tax: o.tax, net: o.total
      }));
      document.getElementById("sum").innerHTML = `
        <div class="card stat"><div><p>This Week Earnings</p><h3>${KQApp.fmtPKR(pack.week_total)}</h3></div><div class="icon-bubble"><i class="bi bi-wallet2"></i></div></div>
        <div class="card stat"><div><p>Monthly Earnings</p><h3>${KQApp.fmtPKR(pack.month_total)}</h3></div><div class="icon-bubble"><i class="bi bi-graph-up"></i></div></div>
        <div class="card stat"><div><p>Completed Orders</p><h3>${pack.completed_count || done.length}</h3></div><div class="icon-bubble"><i class="bi bi-check2-circle"></i></div></div>`;
      document.getElementById("tbl").innerHTML = `<thead><tr><th>Order</th><th>Date</th><th>Customer</th><th>Mode</th><th>Net</th></tr></thead><tbody>` +
        (done.map(o => `<tr><td>${KQApp.esc(o.id)}</td><td>${o.date}</td><td>${KQApp.esc(o.customer)}</td><td>${o.payment}</td><td>${KQApp.fmtPKR(o.net)}</td></tr>`).join("") || `<tr><td colspan="5" style="color:var(--muted)">No completed orders yet.</td></tr>`) + "</tbody>";

      document.getElementById("xlsxBtn").onclick = () => {
        const rows = [["Order ID","Date","Customer","Payment","Subtotal","Discount","Delivery","Tax","Net"]];
        done.forEach(o => rows.push([o.id,o.date,o.customer,o.payment,o.subtotal,o.discount,o.delivery,o.tax,o.net]));
        const csv = rows.map(r => r.map(v => '"' + String(v).replace(/^[=+@-]/, "'$&").replaceAll('"','""') + '"').join(',')).join("\n");
        const blob = new Blob(["\uFEFF"+csv], { type: "text/csv;charset=utf-8;" });
        const a = document.createElement("a");
        a.href = URL.createObjectURL(blob);
        a.download = "KitchenQueen-Orders-Summary.csv";
        a.click();
        KQApp.toast("Excel-ready CSV downloaded");
      };
      document.getElementById("pdfBtn").onclick = () => {
        const w = window.open("", "_blank");
        if (!w) { KQApp.toast("Popup blocked — allow popups"); return; }
        const rows = done.map(o => `<tr><td>${KQApp.esc(o.id)}</td><td>${KQApp.esc(o.customer)}</td><td>${o.payment}</td><td>PKR ${o.net}</td></tr>`).join("");
        const total = done.reduce((s, o) => s + Number(o.net || 0), 0);
        const html = `<!DOCTYPE html><html><head><meta charset="utf-8"><title>Invoice</title>
          <style>body{font-family:Georgia,serif;padding:32px;color:#111}h1{color:#0d9488}table{width:100%;border-collapse:collapse;margin-top:16px}th,td{border-bottom:1px solid #eee;padding:8px;text-align:left}</style></head><body>
          <h1>Kitchen Queen</h1><p>North Nazimabad, Karachi<br>Invoice pack · ${new Date().toLocaleDateString()}</p>
          <table><tr><th>Order</th><th>Customer</th><th>Payment</th><th>Net</th></tr>
          ${rows}
          </table><p><strong>Total: PKR ${total}</strong></p>
          </body></html>`;
        w.document.open();
        w.document.write(html);
        w.document.close();
        setTimeout(() => { try { w.print(); } catch(e){} }, 300);
      };
    });
  </script>
<?php require __DIR__ . "/includes/footer.php"; ?>
</body>
</html>
