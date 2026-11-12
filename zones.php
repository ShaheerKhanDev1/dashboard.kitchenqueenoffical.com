<?php
$pageTitle = "Delivery Zones · Kitchen Queen";
$bodyTitle = "Delivery Zones";
require __DIR__ . "/includes/header.php";
?>
<div class="page">
  <div class="card">
    <h3>Add New Delivery Zone</h3>
    <p style="color:var(--muted)">Nayi area aur price yahan se add karein. Website par foran show hogi.</p>
    <div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:16px">
      <input id="newName" type="text" placeholder="Area name e.g. DHA Phase 5" style="flex:1;min-width:160px">
      <input id="newFee" type="number" placeholder="Delivery fee Rs" style="max-width:140px">
      <button class="btn btn-gold" type="button" id="addZone">Add Zone</button>
    </div>
    <h3>Existing Zones</h3>
    <p style="color:var(--muted)">Fee update karein. Checkout isi fee ko use karega.</p>
    <div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:12px">
      <input id="flat" type="number" placeholder="Flat +Rs" style="max-width:140px">
      <input id="percent" type="number" placeholder="% surge" style="max-width:140px">
      <button class="btn btn-gold" type="button" id="surge">Apply to all zones</button>
    </div>
    <div id="zones"></div>
  </div>
</div>
<script>
async function load() {
  const res = await KQApi.areas();
  const rows = (res.data && res.data.areas) || [];
  document.getElementById("zones").innerHTML = rows.map((a) => `
    <div class="zone-row" style="display:flex;gap:8px;align-items:center;padding:8px 0;border-bottom:1px solid var(--border)">
      <b style="flex:1">${KQApp.esc(a.name)}</b>
      <input type="number" value="${a.delivery_fee}" data-id="${a.id}" style="width:100px">
      <button class="btn btn-ghost btn-sm" data-save="${a.id}">Save</button>
    </div>`).join("") || "<p>No zones yet.</p>";
  document.querySelectorAll("[data-save]").forEach((btn) => btn.addEventListener("click", async () => {
    const id = btn.getAttribute("data-save");
    const fee = document.querySelector(`input[data-id="${id}"]`).value;
    btn.disabled = true;
    await KQApi.saveArea(id, { delivery_fee: Number(fee) });
    KQApp.toast("Zone updated — website par foran");
    btn.disabled = false;
  }));
}
document.getElementById("addZone").addEventListener("click", async () => {
  const name = document.getElementById("newName").value.trim();
  const fee = Number(document.getElementById("newFee").value || 0);
  if (!name) return KQApp.toast("Area name likhein");
  const btn = document.getElementById("addZone");
  btn.disabled = true;
  try {
    // Assuming API supports create; if not, this may need backend
    await KQApi.createArea({ name, delivery_fee: fee });
    KQApp.toast("Nayi zone add ho gayi — website par foran show");
    document.getElementById("newName").value = "";
    document.getElementById("newFee").value = "";
    load();
  } catch (e) {
    KQApp.toast("Add failed: " + (e.message || "API check karein"));
  }
  btn.disabled = false;
});
document.getElementById("surge").addEventListener("click", async () => {
  await KQApi.surgeAreas({ flat: Number(document.getElementById("flat").value || 0), percent: Number(document.getElementById("percent").value || 0) });
  KQApp.toast("All zones updated");
  load();
});
document.addEventListener("DOMContentLoaded", load);
</script>
<?php require __DIR__ . "/includes/footer.php"; ?>
