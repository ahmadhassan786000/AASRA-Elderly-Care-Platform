<?php include __DIR__ . '/head.php'; ?>

<body class="auth-body">
  <div class="auth-bg">

    <div class="auth-wrap" style="max-width:<?= (int)($width ?? 460) ?>px">
      <div class="auth-card">
        <div class="text-center mb-4"><a href="<?= e(url('')) ?>" aria-label="AASRA home"><img src="<?= e(asset('images/logo.svg')) ?>" alt="AASRA" width="48" height="48"></a></div>
        <div id="alertHostTop"><?php render_flashes(); ?></div>