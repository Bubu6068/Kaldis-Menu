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
                                                    <form action="<?= base_url('admin/categories/toggle') ?>" method="POST" class="d-inline">
                                                        <?= \App\Helpers\Csrf::field() ?>
                                                        <input type="hidden" name="type" value="child">
                                                        <input type="hidden" name="id" value="<?= $child['id'] ?>">
                                                        <input type="hidden" name="is_visible" value="<?= (bool)$child['is_visible'] ? '0' : '1' ?>">
                                                        <button type="submit" class="btn btn-sm <?= (bool)$child['is_visible'] ? 'btn-outline-secondary' : 'btn-outline-success' ?> rounded-pill px-2 py-0 extra-small">
                                                            <?= (bool)$child['is_visible'] ? 'Hide' : 'Show' ?>
                                                        </button>
                                                    </form>
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

