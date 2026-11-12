<?php
$pageTitle = 'Menu · Kitchen Queen Admin';
$bodyTitle = 'Menu & Stock Control';
$assetPrefix = '';
require __DIR__ . "/includes/header.php";
?>
<div id="page">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px"><b>Menu on/off</b><a class="btn btn-gold" href="dishes.php">Add new dish</a></div>
    <div class="cat-pills" id="cats"></div>
    <div class="menu-grid" id="grid"></div>
  </div>
  <script>
    let cat = "All";
    let MENU = [];
    const CATS = ["All", "Deals", "Special Deals", "Daal & Sabzi", "Chicken", "Beef & Mutton", "Haleem", "Rice", "Sides & Raita", "Beverages", "Dessert"];
    const CATMAP = {deals:"Deals", special:"Special Deals", daal:"Daal & Sabzi", chicken:"Chicken", beef:"Beef & Mutton", haleem:"Haleem", rice:"Rice", sides:"Sides & Raita", drinks:"Beverages", dessert:"Dessert"};
    function render() {
      const items = MENU.filter(m => cat === "All" || m.category === cat || CATMAP[m.category] === cat);
      document.getElementById("cats").innerHTML = CATS.map(c => `<button class="tab ${c===cat?"active":""}" data-c="${c}">${c}</button>`).join("");
      document.querySelectorAll("#cats .tab").forEach(b => b.onclick = () => { cat = b.dataset.c; render(); });
      if (!items.length) {
        document.getElementById("grid").innerHTML = `<p style="color:var(--muted)">Is category mein item nahi. API/login check karein.</p>`;
        return;
      }
      document.getElementById("grid").innerHTML = items.map(m => `
        <article class="card menu-card">
          <img src="${m.img}" alt="${KQApp.esc(m.name)}" />
          <div class="menu-body">
            <h4>${KQApp.esc(m.name)}</h4>
            <p style="margin:0 0 8px;color:var(--muted);font-size:13px">${KQApp.esc(m.desc)}</p>
            <div class="meta">
              <span class="price">${KQApp.fmtPKR(m.price)}</span>
              <span class="badge ${m.stock?"b-completed":"b-out"}">${m.stock?"In Stock":"Out of Stock"}</span>
            </div>
            <div class="status-toggle ${m.stock?"online":"offline"}" style="margin-top:12px;justify-content:space-between">
              <span>${m.stock ? "Available" : "Hidden"}</span>
              <label class="switch">
                <input type="checkbox" ${m.stock?"checked":""} onchange="toggleStock('${m.id}', this.checked)">
                <span class="slider"></span>
              </label>
            </div>
          </div>
        </article>`).join("");
    }
    window.toggleStock = async (id, on) => {
      try {
        await KQApi.setStock(id, on);
        KQApp.toast(on ? "Item is available" : "Item marked out of stock");
        const item = MENU.find(x => x.id === id);
        if (item) { item.stock = on; item.is_available = on; }
        KQApp.toast(on ? "Item is now In-Stock" : "Item marked Out-of-Stock");
        render();
      } catch (e) { KQApp.toast(e.message || "Failed"); }
    };
    document.addEventListener("DOMContentLoaded", async () => {
      try {
        const res = await KQApi.menu();
        const origin = (window.KQ_SITE_ORIGIN || "").replace(/\/+$/, "");
        MENU = ((res.data && res.data.items) || []).map(m => {
          let img = m.image || "";
          if (img && !/^https?:\/\//i.test(img) && origin) img = origin + "/" + img.replace(/^\/+/, "");
          return {
            ...m,
            category: CATMAP[m.category] || m.category,
            stock: !!m.is_available,
            img,
            desc: m.description || ""
          };
        });
        if (!MENU.length) KQApp.toast("Menu empty — database import check karein");
      } catch (e) { MENU = []; KQApp.toast(e.message || "Could not load menu"); }
      render();
    });
  </script>
<?php require __DIR__ . "/includes/footer.php"; ?>
</body>
</html>
