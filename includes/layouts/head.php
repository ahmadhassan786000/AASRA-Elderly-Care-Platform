<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?= e(($title ?? 'AASRA') . ($title === 'AASRA' ? '' : ' — AASRA')) ?></title>
    <meta name="description" content="AASRA connects elderly and differently-abled people with verified service providers.">
    <link rel="icon" type="image/svg+xml" href="<?= e(asset('images/logo.svg')) ?>">
    <meta name="color-scheme" content="light">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Figtree:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= e(asset('css/app.css')) ?>">
    <?php if (!empty($motion)): ?><script>
            /* Hidden "before reveal" styles apply only when JS runs and motion is allowed. */
            (function(d) {
                if (window.matchMedia && matchMedia('(prefers-reduced-motion: reduce)').matches) return;
                d.classList.add('js-motion');
                addEventListener('load', function() {
                    setTimeout(function() {
                        if (!window.AASRA_MOTION) d.classList.remove('js-motion');
                    }, 2500);
                });
            })(document.documentElement);
        </script><?php endif; ?>
</head>