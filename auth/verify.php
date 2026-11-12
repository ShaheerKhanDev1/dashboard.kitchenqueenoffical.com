<?php
$pageTitle = 'Verify OTP · Kitchen Queen Admin';
$bodyTitle = 'Verify OTP · Kitchen Queen Admin';
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
      <p class="eyebrow">Step 3 · Two-step check</p>
      <h1>Confirm it is you</h1>
      <p class="lede">6-digit OTP enter karein, phir dashboard khulega.</p>
    </header>

    <section class="auth-panel">
      <div class="auth-card glass-3d" data-tilt style="text-align:center">
        <div class="brand-row" style="justify-content:center">
          <img src="../images/logo.svg" alt="Kitchen Queen" />
          <div style="text-align:left">
            <h1>Kitchen Queen</h1>
            <p>OTP verification</p>
          </div>
        </div>
        <h2>Verify OTP</h2>
        <p class="sub" id="otpHint">6-digit code aapke admin email par bheja gaya hai.</p>
        <span class="wa-pill"><i class="bi bi-envelope-fill"></i> Email OTP</span>
        <div class="otp-row" id="otpRow">
          <input maxlength="1" inputmode="numeric" />
          <input maxlength="1" inputmode="numeric" />
          <input maxlength="1" inputmode="numeric" />
          <input maxlength="1" inputmode="numeric" />
          <input maxlength="1" inputmode="numeric" />
          <input maxlength="1" inputmode="numeric" />
        </div>
        <button class="btn btn-gold" id="verifyBtn">Verify & Enter Dashboard</button>
        <div class="auth-foot">
          <a href="login.php">Back to login</a>
          · <a href="#" id="resendBtn">Resend OTP</a>
        </div>
      </div>
    </section>
  </div>
  <script>
    const email = sessionStorage.getItem("kq_pending_email") || ((KQApp.session() || {}).email || "");
    if (!email) location.replace("login.php");
    const phone = sessionStorage.getItem("kq_pending_phone") || "";
    const hint = document.getElementById("otpHint");
    const dev = sessionStorage.getItem("kq_pending_otp");
    hint.textContent = email
      ? "OTP sent to " + email + (dev ? " · code: " + dev : "")
      : (dev ? "OTP: " + dev : "Enter the 6-digit OTP.");

    const inputs = [...document.querySelectorAll("#otpRow input")];
    inputs[0].focus();
    inputs.forEach((inp, i) => {
      inp.addEventListener("input", () => {
        inp.value = inp.value.replace(/\D/g, "").slice(0, 1);
        if (inp.value && i < inputs.length - 1) inputs[i + 1].focus();
      });
      inp.addEventListener("keydown", (e) => {
        if (e.key === "Backspace" && !inp.value && i > 0) inputs[i - 1].focus();
      });
    });

    async function verify() {
      const code = inputs.map(i => i.value).join("");
      if (code.length !== 6) { alert("Please enter the 6-digit OTP."); return; }
      const btn = document.getElementById("verifyBtn");
      btn.disabled = true;
      try {
        const res = await KQApi.verifyAdminOtp({ email, code });
        const data = res.data || {};
        if (!data.token) throw new Error("Verification failed");
        KQApi.setToken(data.token);
        const s = KQApp.session() || {};
        s.verified = true;
        s.name = (data.admin && data.admin.name) || s.name || "Admin";
        s.email = (data.admin && data.admin.email) || email;
        s.phone = (data.admin && data.admin.phone) || s.phone || "";
        KQApp.saveSession(s);
        sessionStorage.removeItem("kq_pending_otp");
        location.href = new URL("../index.php", location.href).href;
      } catch (err) {
        alert(err.message || "Invalid OTP");
        btn.disabled = false;
      }
    }
    document.getElementById("verifyBtn").addEventListener("click", verify);
    document.getElementById("resendBtn").addEventListener("click", async (e) => {
      e.preventDefault();
      try {
        const res = await KQApi.requestAdminOtp({ email });
        const data = res.data || {};
        if (data.dev_code) {
          sessionStorage.setItem("kq_pending_otp", data.dev_code);
          hint.textContent = "New OTP sent" + (data.phone ? " to " + data.phone : "") + " · Dev code: " + data.dev_code;
        } else {
          sessionStorage.removeItem("kq_pending_otp");
          hint.textContent = "New OTP sent" + (data.phone ? " to " + data.phone : "") + ".";
        }
        alert("OTP resent");
      } catch (err) {
        alert(err.message || "Could not resend");
      }
    });
  </script>
<?php require __DIR__ . "/../includes/footer.php"; ?>
</body>
</html>
