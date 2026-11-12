<?php
$pageTitle = "Add Dish · Kitchen Queen";
$bodyTitle = "Add Dish";
require __DIR__ . "/includes/header.php";
?>
<div class="page">
  <div class="card" style="max-width:720px">
    <h3>Add a food item</h3>
    <p style="color:var(--muted)">New dish website menu aur dashboard menu dono par aa jayegi. On/off yahin se menu page par hoga.</p>
    <form id="dishForm" class="form-grid">
      <label>Dish name<input name="name" required placeholder="Mong Masoor Ki Daal"></label>
      <label>Category
        <select name="category" id="cat" required>
          <option>Salan / Curry</option>
          <option>Daal & Sabzi</option>
          <option>Breads / Naan</option>
          <option>Rice / Biryani</option>
          <option>Desserts</option>
          <option>Drinks</option>
        </select>
      </label>
      <label>Price (PKR)<input name="price" type="number" min="1" required placeholder="350"></label>
      <label>Serves<input name="serves" type="number" min="1" value="1"></label>
      <label style="grid-column:1/-1">Description<textarea name="description" rows="3" placeholder="Freshly cooked lentils served with special tarka"></textarea></label>
      <label>Photo (JPG, PNG, WEBP)<input name="image" type="file" accept="image/jpeg,image/png,image/webp" required></label>
      <button class="btn btn-gold" type="submit">Save / Publish</button>
    </form>
    <p id="msg"></p>
  </div>
</div>
<script>
document.getElementById("dishForm").addEventListener("submit", async (e) => {
  e.preventDefault();
  const fd = new FormData(e.target);
  const msg = document.getElementById("msg");
  msg.textContent = "Saving...";
  try {
    await KQApi.createMenu(fd);
    msg.textContent = "Dish published. It will show on the website and in Menu.";
    e.target.reset();
  } catch (err) {
    msg.textContent = err.message || "Could not save dish";
  }
});
</script>
<?php require __DIR__ . "/includes/footer.php"; ?>
