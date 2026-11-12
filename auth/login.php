<?php
$pageTitle = 'Admin Login · Kitchen Queen';
$bodyTitle = 'Admin Login · Kitchen Queen';
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
      <p class="eyebrow">Kitchen Queen · Karachi</p>
      <h1>Admin Control Room</h1>
      <p class="lede">Sign in. OTP aapke admin email par jayega.</p>
    </header>

    <section class="auth-panel">
      <div class="auth-card glass-3d" data-tilt>
        <span class="admin-chip"><i class="bi bi-shield-lock-fill"></i> Step 2 · Sign in</span>
        <div class="brand-row">
          <img src="../images/logo.svg" alt="Kitchen Queen" />
          <div>
            <h1>Kitchen Queen</h1>
            <p>Administrator login</p>
          </div>
        </div>
        <h2>Welcome back</h2>
        <p class="sub">Password ke baad 6-digit OTP email par aata hai.</p>
        <form id="loginForm">
          <div class="field">
            <label>Admin Email</label>
            <input type="email" id="email" required placeholder="queen@kitchenqueen.pk" autocomplete="email" />
          </div>
          <div class="field">
            <label>Password</label>
            <input type="password" id="password" required placeholder="Your password" autocomplete="current-password" />
          </div>
          <button class="btn btn-gold" type="submit"><i class="bi bi-shield-lock-fill"></i> Sign in</button>
        </form>
        <div class="auth-foot">First time here? <a href="register.php">Create admin account</a></div>
      </div>
    </section>
  </div>
  <script>
    document.addEventListener("DOMContentLoaded", () => {
      const saved = sessionStorage.getItem("kq_pending_email");
      if (saved) document.getElementById("email").value = saved;
    });
    document.getElementById("loginForm").addEventListener("submit", async (e) => {
      e.preventDefault();
      const email = document.getElementById("email").value.trim().toLowerCase();
      const password = document.getElementById("password").value;
      const btn = e.target.querySelector("button");
      btn.disabled = true;
      btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Signing in...';
      try {
        if (typeof KQApi === "undefined") {
          throw new Error("Dashboard API script missing. Hard refresh karein (Ctrl+F5).");
        }
        const res = await KQApi.login({ email, password });
        const data = res.data || {};
        if (data.token && !data.needs_otp) {
          KQApi.setToken(data.token);
          KQApp.saveSession({
            name: (data.admin && data.admin.name) || "Admin",
            email: (data.admin && data.admin.email) || email,
            phone: (data.admin && data.admin.phone) || "",
            verified: true
          });
          location.href = new URL("../index.php", location.href).href;
          return;
        }
        if (!data.needs_otp) {
          throw new Error("OTP step missing. Try again.");
        }
        sessionStorage.setItem("kq_pending_email", email);
        sessionStorage.setItem("kq_pending_phone", data.phone || "");
        sessionStorage.setItem("kq_pending_channel", data.channel || "email");
        if (data.dev_code) sessionStorage.setItem("kq_pending_otp", data.dev_code);
        else sessionStorage.removeItem("kq_pending_otp");
        KQApp.saveSession({
          name: (data.admin && data.admin.name) || "Admin",
          email: (data.admin && data.admin.email) || email,
          phone: (data.admin && data.admin.phone) || "",
          verified: false
        });
        location.href = "verify.php";
      } catch (err) {
        alert(err.message || "Login failed");
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-shield-lock-fill"></i> Sign in';
      }
    });
  </script>
<?php require __DIR__ . "/../includes/footer.php"; ?>
</body>
</html>
