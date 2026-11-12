<?php
$assetPrefix = $assetPrefix ?? '';
$isAuth = (($assetPrefix ?? '') === '../') || (strpos((string) ($_SERVER['SCRIPT_NAME'] ?? ''), '/auth/') !== false);
$current = basename((string) ($_SERVER['SCRIPT_NAME'] ?? 'index.php'));
if (!$isAuth):
  $dockMain = [
    ['index.php', 'Home', 'bi-house-fill'],
    ['orders.php', 'Orders', 'bi-bag-check-fill'],
  ];
  $moreLinks = [
    ['menu.php', 'Menu', 'bi-egg-fried'],
    ['dishes.php', 'Add Dish', 'bi-plus-circle-fill'],
    ['zones.php', 'Zones', 'bi-geo-alt-fill'],
    ['reviews.php', 'Reviews', 'bi-star-fill'],
    ['finance.php', 'Finance', 'bi-wallet2'],
    ['analytics.php', 'Analytics', 'bi-graph-up-arrow'],
    ['finduser.php', 'Customers', 'bi-search'],
    ['settings.php', 'Settings', 'bi-gear-fill'],
  ];
?>
    </div>
    <nav class="dock" aria-label="Mobile navigation">
      <?php foreach ($dockMain as $item): ?>
        <a class="dock-item<?= $current === $item[0] ? ' active' : '' ?>" href="<?= htmlspecialchars($item[0]) ?>">
          <span class="dock-ico"><i class="bi <?= htmlspecialchars($item[2]) ?>"></i></span>
          <span class="dock-lbl"><?= htmlspecialchars($item[1]) ?></span>
        </a>
      <?php endforeach; ?>
      <button type="button" class="dock-item" id="moreDockBtn" aria-label="More pages">
        <span class="dock-ico"><i class="bi bi-grid-fill"></i></span>
        <span class="dock-lbl">More</span>
      </button>
    </nav>
    <div class="more-sheet" id="moreSheet">
      <div class="more-panel">
        <div class="more-grid">
          <?php foreach ($moreLinks as $item): ?>
            <a class="more-item" href="<?= htmlspecialchars($item[0]) ?>">
              <i class="bi <?= htmlspecialchars($item[2]) ?>"></i>
              <?= htmlspecialchars($item[1]) ?>
            </a>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>
</div>
<script>
  document.addEventListener("DOMContentLoaded", async () => {
    const moreBtn = document.getElementById("moreDockBtn");
    const sheet = document.getElementById("moreSheet");
    if (moreBtn && sheet) {
      moreBtn.addEventListener("click", () => sheet.classList.toggle("open"));
      sheet.addEventListener("click", (e) => {
        if (e.target === sheet) sheet.classList.remove("open");
      });
    }
    const logout = document.getElementById("kqLogoutBtn");
    if (logout) {
      logout.addEventListener("click", async () => {
        try { if (window.KQApi) { await KQApi.logout(); KQApi.setToken(""); } } catch (e) {}
        location.href = "auth/login.php";
      });
    }
    const sw = document.getElementById("hdrStoreSwitch");
    const applySide = (on) => {
      const dot = document.getElementById("sideStoreDot");
      const txt = document.getElementById("sideStoreText");
      if (dot) dot.classList.toggle("off", !on);
      if (txt) txt.textContent = on ? "ONLINE" : "OFFLINE";
    };
    try {
      if (window.KQApi && KQApi.orders) {
        const ores = await KQApi.orders({ range: "today" });
        const list = (ores.data && ores.data.orders) || [];
        const waiting = list.filter((o) => ["pending", "call_pending", "confirmed"].includes(String(o.status || ""))).length;
        const badge = document.getElementById("callBadge");
        if (badge) badge.textContent = waiting + " orders awaiting call";
      }
      if (window.KQApi && KQApi.settings) {
        const res = await KQApi.settings();
        const live = (res && res.data) || {};
        const on = window.KQApp ? KQApp.parseOnline(live.store_online) : true;
        if (window.KQApp) KQApp.applyStoreUi(on);
        applySide(on);
      }
    } catch (e) {}
    if (sw && window.KQApp) {
      sw.addEventListener("change", async () => {
        const val = sw.checked;
        sw.disabled = true;
        try {
          await KQApp.saveStoreOnline(val);
          KQApp.applyStoreUi(val);
          applySide(val);
          KQApp.toast(val ? "Store ONLINE" : "Store OFFLINE");
        } catch (err) {
          sw.checked = !val;
          KQApp.applyStoreUi(!val);
          applySide(!val);
          KQApp.toast((err && err.message) || "Failed");
        } finally {
          sw.disabled = false;
        }
      });
    }
  });
</script>
<?php endif; ?>
<script>
  if (typeof window.KQApi === "undefined") {
    console.error("KQApi missing — check assets/js/api.js");
  }
</script>

<?php if (!$isAuth): ?><script src="assets/js/operations.js?v=20261028b"></script><?php endif; ?>
