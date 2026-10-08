    </div>
  </div>
</div>
<div class="modal fade" id="rejectModal" tabindex="-1" aria-hidden="true"><div class="modal-dialog modal-dialog-centered"><div class="modal-content">
  <div class="modal-header"><h5 class="modal-title">Reject verification</h5><button class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
  <div class="modal-body"><label class="form-label" for="rejectNote">Reason for the provider (optional)</label><textarea id="rejectNote" class="form-control" rows="3" maxlength="500" placeholder="e.g. The CNIC photo is not readable. Please upload a clearer copy."></textarea></div>
  <div class="modal-footer"><button class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button><button class="btn btn-danger" id="rejectConfirm">Reject provider</button></div>
</div></div></div>
<?php $js = array_merge(['admin.js'], $js ?? []); include __DIR__ . '/foot.php'; ?>
