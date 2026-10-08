<?php
$u = require_role('provider'); $p = current_provider(); $pid = (int)$p['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();

    if (post('action') === 'delete_document') {
        $documentId = (int)post('document_id');

        $document = row(
            'SELECT * FROM provider_documents WHERE id=? AND provider_id=?',
            [$documentId, $pid]
        );

        if (!$document) {
            flash('danger', 'Document not found.');
        } else {
            q(
                'DELETE FROM provider_documents WHERE id=? AND provider_id=?',
                [$documentId, $pid]
            );

            flash('success', 'Document deleted successfully.');
        }

        redirect('provider/verification');
    }

    $type = post('document_type');

    if (!in_array($type, ['CNIC', 'Experience Certificate', 'Other'], true)) {
        flash('danger', 'Please choose a document type.');
    } elseif (empty($_FILES['document']['name'])) {
        flash('danger', 'Please choose a file to upload.');
    } else {
        [$path, $err] = save_upload($_FILES['document'], 'documents', DOC_TYPES, 5 * 1048576, false);

        if ($err) {
            flash('danger', $err);
        } else {
            q(
                "INSERT INTO provider_documents(provider_id,document_type,document_path,verification_status) VALUES(?,?,?,'Pending')",
                [$pid, $type, $path]
            );

            q(
                "UPDATE providers SET verification_status='Pending',verification_note=NULL WHERE id=? AND verification_status<>'Approved'",
                [$pid]
            );

            flash('success', 'Document uploaded. An admin will review it soon.');
        }
    }

    redirect('provider/verification');
}

$p = row('SELECT * FROM providers WHERE id=?', [$pid]);
$docs = rows(
    'SELECT * FROM provider_documents WHERE provider_id=? ORDER BY uploaded_at DESC',
    [$pid]
);

layout('provider', ['title' => 'Verification', 'active' => 'verification']);
?>

<div class="page-head">
  <div>
    <h1>Verification</h1>
    <p>Upload your documents so an admin can verify you.</p>
  </div>
</div>

<div class="row g-4">
  <div class="col-lg-5">
    <div class="card-soft">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="h5 mb-0">Status</h2>
        <?= badge($p['verification_status']) ?>
      </div>

      <?php if ($p['verification_status'] === 'Rejected' && $p['verification_note']): ?>
        <div class="alert alert-danger small">
          <b>Admin note:</b> <?= e($p['verification_note']) ?>
        </div>
      <?php endif; ?>

      <?php if ($p['verification_status'] === 'Approved'): ?>
        <p class="text-success">
          <i class="bi bi-patch-check-fill"></i>
          You are verified and visible to users (once your profile is complete).
        </p>
      <?php endif; ?>

      <form method="post" enctype="multipart/form-data">
        <?= csrf_field() ?>

        <div class="mb-3">
          <label class="form-label" for="document_type">Document type</label>
          <select id="document_type" name="document_type" class="form-select">
            <option>CNIC</option>
            <option>Experience Certificate</option>
            <option>Other</option>
          </select>
        </div>

        <div class="mb-3">
          <label class="form-label" for="document">File</label>
          <input
            id="document"
            type="file"
            name="document"
            class="form-control"
            accept=".pdf,.jpg,.jpeg,.png"
            required
          >
          <div class="form-text">PDF, JPG or PNG, up to 5 MB.</div>
        </div>

        <button class="btn btn-success">Upload document</button>
      </form>
    </div>
  </div>

  <div class="col-lg-7">
    <div class="card-soft">
      <h2 class="h5">Uploaded documents</h2>

      <?php foreach ($docs as $d): ?>
        <div class="d-flex justify-content-between align-items-center border-bottom py-2">
          <div>
            <b><?= e($d['document_type']) ?></b>
            <div class="small text-secondary">
              <?= e(fmt_date($d['uploaded_at'])) ?>
            </div>
          </div>

          <div class="d-flex gap-2 align-items-center">
            <?= badge($d['verification_status']) ?>

            <div class="dropdown">
              <button
                class="btn btn-sm btn-outline-secondary dropdown-toggle"
                type="button"
                data-bs-toggle="dropdown"
                aria-expanded="false"
              >
                Actions
              </button>

              <ul class="dropdown-menu dropdown-menu-end">
                <li>
                  <a
                    class="dropdown-item"
                    target="_blank"
                    rel="noopener"
                    href="<?= e(url('provider/documents/' . $d['id'])) ?>"
                  >
                    <i class="bi bi-box-arrow-up-right me-2"></i>
                    Open
                  </a>
                </li>

                <li><hr class="dropdown-divider"></li>

                <li>
                  <form method="post" onsubmit="return confirm('Are you sure you want to delete this document?');">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="delete_document">
                    <input type="hidden" name="document_id" value="<?= (int)$d['id'] ?>">

                    <button type="submit" class="dropdown-item text-danger">
                      <i class="bi bi-trash me-2"></i>
                      Delete
                    </button>
                  </form>
                </li>
              </ul>
            </div>
          </div>
        </div>
      <?php endforeach; ?>

      <?php if (!$docs): ?>
        <div class="empty-state">
          <i class="bi bi-file-earmark-arrow-up"></i>
          <p class="mb-0 mt-2">No documents uploaded yet.</p>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php layout_end();







