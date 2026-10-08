<?php include __DIR__ . '/head.php'; ?>
<body class="auth-body">
<div class="auth-bg">

  <div class="auth-wrap" style="max-width:<?= (int)($width ?? 460) ?>px">
    <div class="auth-card">
      <div class="text-center mb-4"><?php include APP_ROOT . '/includes/partials/brand.php'; ?></div>
      <div id="alertHostTop"><?php render_flashes(); ?></div>

