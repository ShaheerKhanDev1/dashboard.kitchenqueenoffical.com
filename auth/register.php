<?php
$pageTitle = 'Create Admin · Kitchen Queen';
$bodyTitle = 'Create Admin · Kitchen Queen';
$assetPrefix = '../';
require __DIR__ . "/../includes/header.php";
?>
<div class="auth-stage">
    <div class="auth-backdrop" aria-hidden="true">
      <div class="orb orb-a"></div>
      <div class="orb orb-b"></div>
      <div class="orb orb-c"></div>
      <div class="ring ring-a"></div>
      <div class="ring ring-b"></div>
      <div class="grid-glow"></div>
    </div>

    <header class="auth-brandbar">
      <p class="eyebrow">First-time setup</p>
      <h1>Create the kitchen admin</h1>
      <p class="lede">Register first. Then sign in. Then verify OTP to open the dashboard.</p>
    </header>

    <section class="auth-panel">
      <div class="auth-card glass-3d" data-tilt>
        <span class="admin-chip"><i class="bi bi-person-badge-fill"></i> Step 1 · Register</span>
        <div class="brand-row">
          <img src="../images/logo.svg" alt="Kitchen Queen" />
          <div>
            <h1>Kitchen Queen</h1>
            <p>Admin first setup</p>
          </div>
        </div>
        <h2>Create admin account</h2>
        <p class="sub">The email you enter becomes the admin email. First login will ask for email OTP once.</p>
        <form id="regForm">
          <div class="field">
            <label>Full Name</label>
            <input type="text" id="name" required minlength="2" placeholder="Queen Admin" autocomplete="name" />
          </div>
          <div class="field">
            <label>Admin Email</label>
            <input type="email" id="email" required placeholder="queen@kitchenqueen.pk" autocomplete="email" />
          </div>
          <div class="field">
            <label>Password</label>
            <input type="password" id="password" required minlength="6" placeholder="Choose a unique password" autocomplete="new-password" />
            <small style="color:#888">Choose a unique password for your administrator account.</small>
          </div>
          <div class="field">
            <label>Phone (optional)</label>
            <input type="tel" id="phone" placeholder="03xxxxxxxxx" autocomplete="tel" />
          </div>
          <button class="btn btn-gold" type="submit">Register & Go to Sign in</button>
        </form>
        <div class="auth-foot">Already registered? <a href="login.php">Go to admin login</a></div>
      </div>
    </section>
  </div>
  <script>
    document.addEventListener("DOMContentLoaded", async () => {
      try {
        const res = await KQApi.setup();
        const data = res.data || {};
        if (data.needs_setup === false) {
          location.href = "login.php";
        }
      } catch (e) {}
    });

    document.getElementById("regForm").addEventListener("submit", async (e) => {
      e.preventDefault();
      const name = document.getElementById("name").value.trim();
      const email = document.getElementById("email").value.trim().toLowerCase();
      const password = document.getElementById("password").value;
      const phone = document.getElementById("phone").value.trim();
      const btn = e.target.querySelector("button");
      btn.disabled = true;
      try {
        await KQApi.register({ name, email, password, phone });
        sessionStorage.setItem("kq_pending_email", email);
        alert("Account ban gaya. Ab sign in karein.");
        location.href = "login.php";
      } catch (err) {
        alert(err.message || "Registration failed");
        btn.disabled = false;
      }
    });
  </script>
<?php require __DIR__ . "/../includes/footer.php"; ?>
</body>
</html>
