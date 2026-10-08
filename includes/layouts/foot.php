<script>window.AASRA={base:<?= json_encode(BASE_URL) ?>,csrf:<?= json_encode(csrf_token()) ?>};</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= e(asset('js/app.js')) ?>"></script>
<?php foreach (($js ?? []) as $f): ?><script src="<?= e(asset('js/' . $f)) ?>"></script><?php endforeach; ?>
</body></html>
