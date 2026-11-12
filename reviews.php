<?php
$pageTitle = 'Reviews · Kitchen Queen Admin';
$bodyTitle = 'Reviews & Ratings';
$assetPrefix = '';
require __DIR__ . "/includes/header.php";
?>
<div id="page">
    <div class="grid g-2">
      <div class="card stat">
        <div>
          <p>Overall Rating</p>
          <h3 id="avg">★ — / 5.0</h3>
          <div style="color:var(--muted)" id="count"></div>
        </div>
        <div class="icon-bubble"><i class="bi bi-award-fill"></i></div>
      </div>
      <div class="card" id="dist"></div>
    </div>
    <div class="card" style="margin-top:16px">
      <div class="section-title"><h3>Review Feed</h3></div>
      <div id="feed"></div>
    </div>
  </div>
  <script>
    window._REV = [];
    function render() {
      const list = window._REV || [];
      if (!list.length) {
        document.getElementById("avg").textContent = "★ — / 5.0";
        document.getElementById("count").textContent = "0 customer reviews";
        document.getElementById("dist").innerHTML = "";
        document.getElementById("feed").innerHTML = "<p style='color:var(--muted)'>No reviews yet. Customers submit them from the website.</p>";
        return;
      }
      const avg = (list.reduce((s,r)=>s+r.stars,0) / list.length).toFixed(1);
      document.getElementById("avg").textContent = `★ ${avg} / 5.0`;
      document.getElementById("count").textContent = list.length + " customer reviews";
      const dist = [5,4,3,2,1].map(n => ({ n, c: list.filter(r => r.stars === n).length }));
      const max = Math.max(...dist.map(d=>d.c), 1);
      document.getElementById("dist").innerHTML = dist.map(d => `
        <div class="dist-bar"><span>${d.n}★</span><div class="bar"><div class="fill" style="width:${(d.c/max)*100}%"></div></div><span>${d.c}</span></div>`).join("");
      document.getElementById("feed").innerHTML = list.map(r => `
        <div class="review">
          <div style="display:flex;justify-content:space-between;gap:8px;flex-wrap:wrap">
            <strong>${KQApp.esc(r.name)}</strong>
            <span class="stars">${"★".repeat(r.stars)}${"☆".repeat(Math.max(0,5-r.stars))}</span>
          </div>
          <div style="font-size:12px;color:var(--muted);margin:4px 0">${r.date || ""} ${r.orderId ? " · Order " + r.orderId : ""}</div>
          <div>${r.text || ""}</div>
          ${r.reply ? `<div class="owner-reply"><strong>Owner reply:</strong> ${KQApp.esc(r.reply)}</div>` : `
            <div class="reply-box">
              <input placeholder="Write owner reply…" id="in-${r.id}" />
              <button class="btn btn-gold btn-sm" onclick="reply('${r.id}')">Reply</button>
            </div>`}
        </div>`).join("");
    }
    window.reply = async (id) => {
      const val = document.getElementById("in-" + id).value.trim();
      if (!val) return;
      try {
        await KQApi.replyReview(id, { reply: val });
        const item = window._REV.find(x => String(x.id) === String(id));
        if (item) item.reply = val;
        KQApp.toast("Reply saved");
        render();
      } catch (e) {
        KQApp.toast(e.message || "Failed");
      }
    };
    document.addEventListener("DOMContentLoaded", async () => {
      try {
        const res = await KQApi.reviews();
        window._REV = ((res.data && res.data.reviews) || []).map(r => ({
          id: r.id,
          name: r.name,
          stars: Number(r.stars || 0),
          date: String(r.created_at || "").slice(0, 10),
          orderId: r.order_id || "",
          text: r.text || "",
          reply: r.reply || ""
        }));
      } catch (e) {
        window._REV = [];
        KQApp.toast(e.message || "Could not load reviews");
      }
      render();
    });
  </script>
<?php require __DIR__ . "/includes/footer.php"; ?>
</body>
</html>
