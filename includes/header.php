<?php
require_once __DIR__ . "/connection.php";
$pageTitle = $pageTitle ?? "Kitchen Queen Admin";
$bodyTitle = $bodyTitle ?? "Dashboard";
$assetPrefix = $assetPrefix ?? '';
$isAuth = (($assetPrefix ?? '') === '../') || (strpos((string) ($_SERVER['SCRIPT_NAME'] ?? ''), '/auth/') !== false);
$current = basename((string) ($_SERVER['SCRIPT_NAME'] ?? 'index.php'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?= htmlspecialchars($pageTitle) ?></title>
  <link rel="icon" type="image/png" href="<?= $assetPrefix ?>favicon.png" />


  <link href="<?= $assetPrefix ?>assets/vendor/bootstrap-icons.css" rel="stylesheet" />
  <link rel="stylesheet" href="<?= $assetPrefix ?>assets/css/style.css?v=20261028b" />
  <link rel="stylesheet" href="<?= $assetPrefix ?>assets/css/professional.css?v=20261028b" />
  <script src="<?= $assetPrefix ?>assets/js/config.js?v=20261028b"></script>
  <script src="<?= $assetPrefix ?>assets/js/api.js?v=20261028b"></script>
  <script src="<?= $assetPrefix ?>assets/js/data.js?v=20261028b"></script>
  <script src="<?= $assetPrefix ?>assets/js/app.js?v=20261028b"></script>
</head>
<body class="<?= $isAuth ? 'auth-body' : '' ?>" data-title="<?= htmlspecialchars($bodyTitle) ?>">
<?php if (!$isAuth):
  $nav = [
    ['index.php', 'Overview', 'bi-grid-1x2-fill'],
    ['orders.php', 'Orders', 'bi-bag-check-fill'],
    ['menu.php', 'Menu', 'bi-egg-fried'],
    ['dishes.php', 'Add Dish', 'bi-plus-circle-fill'],
    ['zones.php', 'Delivery Zones', 'bi-geo-alt-fill'],
    ['reviews.php', 'Reviews', 'bi-star-fill'],
    ['finance.php', 'Finance', 'bi-wallet2'],
    ['analytics.php', 'Analytics', 'bi-graph-up-arrow'],
    ['finduser.php', 'Customers', 'bi-search'],
    ['settings.php', 'Settings', 'bi-gear-fill'],
  ];
?>
<div class="app">
  <aside class="sidebar">
    <div class="side-brand">
      <img src="<?= $assetPrefix ?>favicon.png" alt="Kitchen Queen" />
      <div>
        <strong>Kitchen Queen</strong>
        <span>Admin panel</span>
      </div>
    </div>
    <nav class="nav-list">
      <?php foreach ($nav as $item): ?>
        <a class="nav-item<?= $current === $item[0] ? ' active' : '' ?>" href="<?= htmlspecialchars($item[0]) ?>">
          <i class="bi <?= htmlspecialchars($item[2]) ?>"></i> <?= htmlspecialchars($item[1]) ?>
        </a>
      <?php endforeach; ?>
    </nav>
    <div class="side-foot">
      <div class="online-chip" id="sideStoreChip">
        <span><span class="dot" id="sideStoreDot"></span> Store</span>
        <b id="sideStoreText">—</b>
      </div>
      <button class="btn btn-ghost btn-sm" type="button" id="kqLogoutBtn" style="width:100%;margin-top:10px">
        <i class="bi bi-box-arrow-right"></i> Logout
      </button>
    </div>
  </aside>
  <div class="main">
    <header class="topbar">
      <div class="top-left">
        <h2><?= htmlspecialchars($bodyTitle) ?></h2>
        <small>Kitchen operations · Karachi</small>
      </div>
      <div class="top-right">
        <div class="status-toggle online" id="hdrStatusWrap">
          <span id="hdrStatusLabel">ONLINE</span>
          <label class="switch">
            <input type="checkbox" id="hdrStoreSwitch" checked>
            <span class="slider"></span>
          </label>
        </div>
        <a class="call-badge" id="callBadge" href="orders.php">0 calls waiting</a>
        <a class="btn btn-ghost btn-sm" href="orders.php?status=awaiting_payment" id="paymentCount">Payments <b>0</b></a>
      </div>
    </header>
    <div class="content">
<?php endif; ?>
