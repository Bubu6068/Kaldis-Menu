<div class="row g-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-header bg-white border-0 py-3 d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="fw-bold text-espresso mb-0">Menu Category Hierarchy</h6>
                    <span class="text-muted extra-small">Parent Preference &rarr; Child Preference classification</span>
                </div>
                <div>
                    <span class="badge bg-light text-dark border">
                        <?= count($categories) ?> Parent Categories
                    </span>
                </div>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 small">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 50px;">#</th>
                                <th>Category Name</th>
                                <th>Source ID</th>
                                <th>Subcategories (Child Preferences)</th>
                                <th>Public Visibility</th>
                                <th>Sub-category image</th>
                                <th class="text-end">Toggle</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($categories)): ?>
                                <?php foreach ($categories as $idx => $parent): ?>
                                    <tr class="table-light border-top">
                                        <td class="fw-bold text-muted"><?= $idx + 1 ?></td>
                                        <td>
                                            <div class="fw-bold text-espresso fs-6 d-flex align-items-center gap-2">
                                                <i class="bi bi-folder2-open text-gold"></i>
                                                <?= e($parent['name']) ?>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary-subtle text-secondary">
                                                ID: <?= e((string)($parent['source_id'] ?? 'NULL')) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-espresso text-white">
                                                <?= count($parent['children'] ?? []) ?> children
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ((bool)$parent['is_visible']): ?>
                                                <span class="badge bg-success">Visible on Menu</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">Hidden</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="text-muted extra-small">—</span>
                                        </td>
                                        <td class="text-end">
                                            <form action="<?= base_url('admin/categories/toggle') ?>" method="POST" class="d-inline">
                                                <?= \App\Helpers\Csrf::field() ?>
                                                <input type="hidden" name="type" value="parent">
                                                <input type="hidden" name="id" value="<?= $parent['id'] ?>">
                                                <input type="hidden" name="is_visible" value="<?= (bool)$parent['is_visible'] ? '0' : '1' ?>">
                                                <button type="submit" class="btn btn-sm <?= (bool)$parent['is_visible'] ? 'btn-outline-danger' : 'btn-outline-success' ?> rounded-pill px-3 py-1 extra-small">
                                                    <?= (bool)$parent['is_visible'] ? 'Hide Parent' : 'Show Parent' ?>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>

                                    <!-- Nested Children Rows -->
                                    <?php if (!empty($parent['children'])): ?>
                                        <?php foreach ($parent['children'] as $child): ?>
                                            <tr>
                                                <td></td>
                                                <td class="ps-4">
                                                    <div class="d-flex align-items-center gap-2 text-muted">
                                                        <i class="bi bi-arrow-return-right"></i>
                                                        <span class="fw-semibold text-espresso"><?= e($child['name']) ?></span>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="badge bg-light text-muted border">
                                                        Child ID: <?= e((string)($child['source_id'] ?? 'NULL')) ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="text-muted extra-small">Sub-category</span>
                                                </td>
                                                <td>
                                                    <?php if ((bool)$child['is_visible']): ?>
                                                        <span class="badge bg-success-subtle text-success extra-small">Visible</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-danger-subtle text-danger extra-small">Hidden</span>
                                                    <?php endif; ?>
                                                    <?php $hCount = (int)($hiddenBranchCounts[(int)$child['id']] ?? 0); ?>
                                                    <?php if ($hCount > 0): ?>
                                                        <span class="badge bg-warning-subtle text-dark border extra-small d-block mt-1" title="Hidden in <?= $hCount ?> branches">
                                                            <i class="bi bi-eye-slash me-1"></i>Hidden in <?= $hCount ?> <?= $hCount === 1 ? 'branch' : 'branches' ?>
                                                        </span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <div class="d-flex align-items-center gap-2">
                                                        <div class="rounded-3 overflow-hidden bg-light border d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; flex-shrink: 0;">
                                                            <?php if (!empty($child['image_path']) && file_exists(BASE_PATH . '/public/' . ltrim($child['image_path'], '/'))): ?>
                                                                <img src="<?= base_url($child['image_path']) ?>" alt="" style="width: 100%; height: 100%; object-fit: cover;">
                                                            <?php else: ?>
                                                                <i class="bi bi-image text-muted" style="font-size: 0.9rem;"></i>
                                                            <?php endif; ?>
                                                        </div>
                                                        <div class="d-flex align-items-center gap-1">
                                                            <form action="<?= base_url('admin/categories/image') ?>" method="POST" enctype="multipart/form-data" class="d-inline m-0">
                                                                <?= \App\Helpers\Csrf::field() ?>
                                                                <input type="hidden" name="child_id" value="<?= (int)$child['id'] ?>">
                                                                <label class="btn btn-outline-gold btn-xs py-1 px-2 rounded-pill extra-small mb-0" style="cursor: pointer;" title="Upload or change image">
                                                                    <i class="bi bi-upload"></i>
                                                                    <input type="file" name="image" accept="image/jpeg,image/png,image/webp" style="display: none;" onchange="this.form.submit()">
                                                                </label>
                                                            </form>
                                                            <?php if (!empty($child['image_path'])): ?>
                                                                <form action="<?= base_url('admin/categories/image') ?>" method="POST" class="d-inline m-0" onsubmit="return confirm('Remove image for this sub-category?');">
                                                                    <?= \App\Helpers\Csrf::field() ?>
                                                                    <input type="hidden" name="child_id" value="<?= (int)$child['id'] ?>">
                                                                    <input type="hidden" name="action" value="remove">
                                                                    <button type="submit" class="btn btn-outline-danger btn-xs py-1 px-2 rounded-pill extra-small" title="Remove image">
                                                                        <i class="bi bi-trash"></i>
                                                                    </button>
                                                                </form>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="text-end">
                                                    <div class="d-inline-flex align-items-center gap-1">
                                                        <form action="<?= base_url('admin/categories/toggle') ?>" method="POST" class="d-inline m-0">
                                                            <?= \App\Helpers\Csrf::field() ?>
                                                            <input type="hidden" name="type" value="child">
                                                            <input type="hidden" name="id" value="<?= $child['id'] ?>">
                                                            <input type="hidden" name="is_visible" value="<?= (bool)$child['is_visible'] ? '0' : '1' ?>">
                                                            <button type="submit" class="btn btn-sm <?= (bool)$child['is_visible'] ? 'btn-outline-secondary' : 'btn-outline-success' ?> rounded-pill px-2 py-0 extra-small">
                                                                <?= (bool)$child['is_visible'] ? 'Hide' : 'Show' ?>
                                                            </button>
                                                        </form>

                                                        <button type="button" 
                                                                class="btn btn-sm btn-outline-gold rounded-pill px-2 py-0 extra-small btn-branch-visibility" 
                                                                data-child-id="<?= (int)$child['id'] ?>" 
                                                                data-child-name="<?= e($child['name']) ?>"
                                                                title="Configure which branches show or hide this sub-category">
                                                            <i class="bi bi-shop me-1"></i>Hide by Branch
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center py-5 text-muted">No categories found in the database. Run the synchronization to populate.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- =========================================================================
     BRANCH VISIBILITY MODAL ("Hide by Branch")
     ========================================================================= -->
<div class="modal fade" id="branchVisibilityModal" tabindex="-1" aria-labelledby="branchVisModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 500px;">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <form action="<?= base_url('admin/categories/branch-visibility') ?>" method="POST" id="branchVisForm">
                <?= \App\Helpers\Csrf::field() ?>
                <input type="hidden" name="child_id" id="modalChildId" value="0">

                <div class="modal-header bg-white border-bottom py-3 px-4">
                    <div>
                        <h6 class="modal-title fw-bold text-espresso mb-0" id="branchVisModalLabel">
                            <i class="bi bi-shop text-gold me-2"></i>Hide Sub-category by Branch
                        </h6>
                        <span class="text-muted extra-small" id="modalSubcategoryName">Select branches to show or hide this sub-category</span>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body p-4 bg-light">
                    <!-- Helper instruction -->
                    <div class="p-2 mb-3 bg-white rounded-3 border extra-small text-muted">
                        <i class="bi bi-info-circle text-gold me-1"></i>
                        Uncheck any branch where you want to <strong>hide</strong> this sub-category and its products.
                    </div>

                    <!-- Filter & Bulk select -->
                    <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                        <div class="position-relative flex-grow-1">
                            <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-2 text-muted extra-small"></i>
                            <input type="text" id="modalBranchFilter" class="form-control form-control-sm ps-4 extra-small rounded-pill" placeholder="Filter branches...">
                        </div>
                        <div class="btn-group btn-group-sm">
                            <button type="button" class="btn btn-outline-secondary btn-xs py-1 px-2 extra-small rounded-pill me-1" id="btnModalSelectAll">
                                Select All
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-xs py-1 px-2 extra-small rounded-pill" id="btnModalDeselectAll">
                                Deselect All
                            </button>
                        </div>
                    </div>

                    <!-- Scrollable list of active branches -->
                    <div class="border rounded-3 bg-white p-2" style="max-height: 280px; overflow-y: auto;" id="modalBranchListContainer">
                        <?php if (!empty($branches)): ?>
                            <?php foreach ($branches as $b): ?>
                                <div class="modal-branch-row d-flex align-items-center justify-content-between py-2 px-2 border-bottom border-light" data-branch-name="<?= e(strtolower($b['name'])) ?>">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="bi bi-geo-alt-fill text-gold extra-small"></i>
                                        <span class="small fw-semibold text-espresso"><?= e($b['name']) ?></span>
                                        <span class="text-muted extra-small">(<?= e($b['code']) ?>)</span>
                                    </div>
                                    <div class="form-check form-switch m-0">
                                        <input type="hidden" name="branch_visible[<?= (int)$b['id'] ?>]" value="0">
                                        <input 
                                            class="form-check-input modal-branch-switch" 
                                            type="checkbox" 
                                            id="modal_bv_<?= (int)$b['id'] ?>" 
                                            name="branch_visible[<?= (int)$b['id'] ?>]" 
                                            value="1" 
                                            checked
                                        >
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-muted extra-small py-2 text-center">No active branches found.</div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="modal-footer bg-white border-top py-2 px-4 d-flex justify-content-between">
                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-gold rounded-pill px-4 fw-semibold">
                        <i class="bi bi-check-lg me-1"></i> Save Branch Visibility
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const branchVisMap = <?= json_encode($branchVisMap ?? []) ?>;
    const branchVisModalEl = document.getElementById('branchVisibilityModal');
    const branchVisModal = branchVisModalEl ? new bootstrap.Modal(branchVisModalEl) : null;
    const modalChildIdInput = document.getElementById('modalChildId');
    const modalSubcategoryName = document.getElementById('modalSubcategoryName');
    const modalBranchFilter = document.getElementById('modalBranchFilter');
    const branchRows = document.querySelectorAll('.modal-branch-row');
    const branchSwitches = document.querySelectorAll('.modal-branch-switch');
    const btnSelectAll = document.getElementById('btnModalSelectAll');
    const btnDeselectAll = document.getElementById('btnModalDeselectAll');

    document.querySelectorAll('.btn-branch-visibility').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const childId = parseInt(this.getAttribute('data-child-id') || '0', 10);
            const childName = this.getAttribute('data-child-name') || 'Sub-category';

            if (modalChildIdInput) modalChildIdInput.value = childId;
            if (modalSubcategoryName) {
                modalSubcategoryName.innerHTML = 'Configuring: <strong class="text-espresso">' + childName + '</strong>';
            }

            // Populate switches based on branchVisMap
            const childSettings = branchVisMap[childId] || {};
            branchSwitches.forEach(function (sw) {
                const branchId = parseInt(sw.id.replace('modal_bv_', ''), 10);
                if (childSettings[branchId] !== undefined) {
                    sw.checked = (parseInt(childSettings[branchId], 10) === 1);
                } else {
                    // Default visible (checked)
                    sw.checked = true;
                }
            });

            // Reset filter
            if (modalBranchFilter) modalBranchFilter.value = '';
            branchRows.forEach(function (row) { row.style.display = ''; });

            if (branchVisModal) branchVisModal.show();
        });
    });

    // Quick filter branches
    if (modalBranchFilter) {
        modalBranchFilter.addEventListener('input', function () {
            const q = this.value.toLowerCase().trim();
            branchRows.forEach(function (row) {
                const name = row.getAttribute('data-branch-name') || '';
                row.style.display = (!q || name.includes(q)) ? '' : 'none';
            });
        });
    }

    // Bulk Select / Deselect
    if (btnSelectAll) {
        btnSelectAll.addEventListener('click', function () {
            branchRows.forEach(function (row) {
                if (row.style.display !== 'none') {
                    const sw = row.querySelector('.modal-branch-switch');
                    if (sw) sw.checked = true;
                }
            });
        });
    }

    if (btnDeselectAll) {
        btnDeselectAll.addEventListener('click', function () {
            branchRows.forEach(function (row) {
                if (row.style.display !== 'none') {
                    const sw = row.querySelector('.modal-branch-switch');
                    if (sw) sw.checked = false;
                }
            });
        });
    }
});
</script>

