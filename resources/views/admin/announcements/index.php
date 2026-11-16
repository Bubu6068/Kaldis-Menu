<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
    <div>
        <h4 class="fw-bold mb-1 text-espresso">
            <i class="bi bi-megaphone text-gold me-2"></i> New Product Announcements & Moving Banner
        </h4>
        <p class="text-muted small mb-0">
            Create attractive, animated moving message banners to spotlight new products, seasonal drinks, and special promotions.
        </p>
    </div>
    <div class="d-flex align-items-center gap-2">
        <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#bannerSettingsModal">
            <i class="bi bi-sliders me-1"></i> Banner Settings
        </button>
        <button type="button" class="btn btn-gold btn-sm rounded-pill px-3 fw-semibold shadow-sm" id="btnOpenCreateModal">
            <i class="bi bi-plus-circle me-1"></i> Add Announcement
        </button>
    </div>
</div>

<?php if (!empty($success)): ?>
    <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm mb-4">
        <i class="bi bi-check-circle-fill me-2"></i> <?= e($success) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-sm mb-4">
        <i class="bi bi-exclamation-triangle-fill me-2"></i> <?= e($error) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- Stats Overview Cards -->
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-muted extra-small text-uppercase fw-bold">Total Announcements</span>
                <span class="badge bg-gold-subtle text-gold-dark rounded-pill">
                    <i class="bi bi-card-heading"></i>
                </span>
            </div>
            <h3 class="fw-bold text-espresso mb-1"><?= (int)($stats['total'] ?? 0) ?></h3>
            <span class="extra-small text-muted">Configured announcements</span>
        </div>
    </div>

    <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-muted extra-small text-uppercase fw-bold">Live & Active</span>
                <span class="badge bg-success-subtle text-success rounded-pill">
                    <i class="bi bi-broadcast"></i>
                </span>
            </div>
            <h3 class="fw-bold text-success mb-1"><?= (int)($stats['active'] ?? 0) ?></h3>
            <span class="extra-small text-muted">Currently visible on menu</span>
        </div>
    </div>

    <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-muted extra-small text-uppercase fw-bold">Scheduled</span>
                <span class="badge bg-primary-subtle text-primary rounded-pill">
                    <i class="bi bi-calendar-event"></i>
                </span>
            </div>
            <h3 class="fw-bold text-primary mb-1"><?= (int)($stats['scheduled'] ?? 0) ?></h3>
            <span class="extra-small text-muted">Upcoming planned campaigns</span>
        </div>
    </div>

    <div class="col-6 col-lg-3">
        <?php $bannerGloballyOn = ($settings['announcements_enabled']['value'] ?? '1') === '1'; ?>
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-muted extra-small text-uppercase fw-bold">Menu Banner State</span>
                <span class="badge <?= $bannerGloballyOn ? 'bg-success text-white' : 'bg-secondary text-white' ?> rounded-pill">
                    <?= $bannerGloballyOn ? 'ENABLED' : 'PAUSED' ?>
                </span>
            </div>
            <div class="d-flex align-items-center gap-2 mt-1">
                <i class="bi <?= $bannerGloballyOn ? 'bi-check-circle-fill text-success' : 'bi-pause-circle text-muted' ?> fs-4"></i>
                <div>
                    <div class="small fw-bold text-espresso"><?= $bannerGloballyOn ? 'Broadcasting Banner' : 'Banner Disabled' ?></div>
                    <span class="extra-small text-muted">Public digital menu</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- LIVE ANIMATED TICKER PREVIEW -->
<div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden">
    <div class="card-header bg-white py-3 px-4 border-0 d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-eye-fill text-gold"></i>
            <span class="fw-bold text-espresso small">Live Animated Ticker Preview</span>
            <span class="badge bg-gold-subtle text-gold-dark extra-small">Hover to pause</span>
        </div>
        <span class="extra-small text-muted">Real-time customer view</span>
    </div>
    <div class="card-body p-0">
        <div class="kaldis-admin-preview-container">
            <?php
            // Sample preview announcement if list is empty
            $previewItems = !empty($announcements) ? $announcements : [
                [
                    'badge_text'   => 'NEW PRODUCT ALERT!',
                    'title'        => 'Caramel Macchiato',
                    'message'      => 'Try our NEW Caramel Macchiato – 250 ETB. Available Now at Kaldis Coffee!',
                    'bg_color'     => '#2C1810',
                    'text_color'   => '#FDF8EE',
                    'accent_color' => '#C5A059',
                    'scroll_duration' => 25,
                    'product_image'=> null,
                ]
            ];
            $firstItem = $previewItems[0] ?? [];
            $prevBg = $firstItem['bg_color'] ?? '#2C1810';
            $prevText = $firstItem['text_color'] ?? '#FDF8EE';
            $prevAccent = $firstItem['accent_color'] ?? '#C5A059';
            $prevDuration = $firstItem['scroll_duration'] ?? 25;
            ?>
            <div id="adminLiveTickerBar" class="kaldis-ticker-bar" style="background: <?= e($prevBg) ?>; color: <?= e($prevText) ?>; border-top: 1px solid rgba(197, 160, 89, 0.35); border-bottom: 1px solid rgba(197, 160, 89, 0.35);">
                <div class="kaldis-ticker-prefix">
                    <span class="kaldis-ticker-prefix-pill" style="background: <?= e($prevAccent) ?>; color: #2C1810;">
                        <i class="bi bi-cup-hot-fill me-1"></i> KALDIS SPECIALS
                    </span>
                </div>
                <div class="kaldis-ticker-track-wrapper">
                    <div id="adminLiveTickerTrack" class="kaldis-ticker-track" style="animation-duration: <?= (int)$prevDuration ?>s;">
                        <!-- Track items loop -->
                        <?php for ($rep = 0; $rep < 2; $rep++): ?>
                            <div class="kaldis-ticker-sequence d-flex align-items-center">
                                <?php foreach ($previewItems as $item): ?>
                                    <div class="kaldis-ticker-item d-inline-flex align-items-center">
                                        <span class="kaldis-ticker-badge" style="background: <?= e($item['accent_color'] ?? $prevAccent) ?>; color: #1c100b;">
                                            <i class="bi bi-stars me-1"></i><?= e($item['badge_text'] ?? 'NEW PRODUCT') ?>
                                        </span>
                                        <span class="kaldis-ticker-icon text-gold mx-2"><i class="bi bi-cup-hot"></i></span>
                                        <span class="kaldis-ticker-text fw-medium">
                                            <?= e($item['message']) ?>
                                        </span>
                                        <span class="kaldis-ticker-sep mx-4 opacity-50">&bull; ☕ &bull;</span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endfor; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ANNOUNCEMENTS TABLE -->
<div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
    <div class="card-header bg-white py-3 px-4 border-bottom d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div>
            <h5 class="fw-bold mb-0 text-espresso">Active & Scheduled Announcements</h5>
            <div class="extra-small text-muted">Manage banners running across your digital menu.</div>
        </div>
        <div>
            <span class="badge bg-light text-muted border px-3 py-2 rounded-pill small">
                Showing <?= count($announcements) ?> items
            </span>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light extra-small text-uppercase tracking-wider">
                <tr>
                    <th style="width: 40px;" class="ps-4">#</th>
                    <th style="width: 140px;">Badge</th>
                    <th>Announcement Message</th>
                    <th>Promoted Product</th>
                    <th>Schedule</th>
                    <th>Appearance</th>
                    <th class="text-center" style="width: 100px;">Status</th>
                    <th class="text-end pe-4" style="width: 130px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($announcements)): ?>
                    <?php foreach ($announcements as $index => $ann): ?>
                        <?php
                            $today = date('Y-m-d');
                            $isScheduled = !empty($ann['start_date']) && $ann['start_date'] > $today;
                            $isExpired   = !empty($ann['end_date']) && $ann['end_date'] < $today;
                            $isCurrentlyActive = (bool)$ann['is_active'] && !$isScheduled && !$isExpired;
                        ?>
                        <tr>
                            <td class="ps-4 text-muted extra-small"><?= $index + 1 ?></td>
                            <td>
                                <span class="badge rounded-pill fw-bold extra-small py-1 px-2 shadow-xs" style="background-color: <?= e($ann['accent_color'] ?: '#C5A059') ?>; color: #2C1810;">
                                    <?= e($ann['badge_text']) ?>
                                </span>
                            </td>
                            <td>
                                <div class="fw-bold text-espresso small mb-0"><?= e($ann['title']) ?></div>
                                <div class="extra-small text-muted text-truncate" style="max-width: 320px;" title="<?= e($ann['message']) ?>">
                                    <?= e($ann['message']) ?>
                                </div>
                            </td>
                            <td>
                                <?php if (!empty($ann['product_name'])): ?>
                                    <div class="d-flex align-items-center gap-2">
                                        <?php if (!empty($ann['product_image']) && file_exists(BASE_PATH . '/public/' . ltrim($ann['product_image'], '/'))): ?>
                                            <img src="<?= base_url($ann['product_image']) ?>" alt="" style="width: 34px; height: 34px; object-fit: cover;" class="rounded-2 shadow-xs">
                                        <?php else: ?>
                                            <div class="rounded-2 bg-light d-flex align-items-center justify-content-center text-gold" style="width: 34px; height: 34px;">
                                                <i class="bi bi-cup-hot"></i>
                                            </div>
                                        <?php endif; ?>
                                        <div>
                                            <span class="small fw-semibold text-espresso d-block lh-sm"><?= e($ann['product_name']) ?></span>
                                            <span class="extra-small text-muted">#<?= e($ann['product_code']) ?></span>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <span class="text-muted extra-small italic">General Announcement</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($ann['start_date']) || !empty($ann['end_date'])): ?>
                                    <div class="extra-small">
                                        <i class="bi bi-calendar3 me-1 text-muted"></i>
                                        <span><?= !empty($ann['start_date']) ? date('M j, Y', strtotime($ann['start_date'])) : 'Anytime' ?></span>
                                        <span class="text-muted">&rarr;</span>
                                        <span><?= !empty($ann['end_date']) ? date('M j, Y', strtotime($ann['end_date'])) : 'Ongoing' ?></span>
                                    </div>
                                    <?php if ($isScheduled): ?>
                                        <span class="badge bg-info-subtle text-info extra-small mt-1">Starts <?= date('M j', strtotime($ann['start_date'])) ?></span>
                                    <?php elseif ($isExpired): ?>
                                        <span class="badge bg-danger-subtle text-danger extra-small mt-1">Expired</span>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="badge bg-light text-secondary border extra-small">Always Active</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="rounded-circle border shadow-xs d-inline-block" style="width: 18px; height: 18px; background: <?= e($ann['bg_color']) ?>;" title="Background: <?= e($ann['bg_color']) ?>"></span>
                                    <span class="rounded-circle border shadow-xs d-inline-block" style="width: 18px; height: 18px; background: <?= e($ann['text_color']) ?>;" title="Text: <?= e($ann['text_color']) ?>"></span>
                                    <span class="rounded-circle border shadow-xs d-inline-block" style="width: 18px; height: 18px; background: <?= e($ann['accent_color']) ?>;" title="Badge Accent: <?= e($ann['accent_color']) ?>"></span>
                                    <span class="extra-small text-muted ms-1"><?= (int)$ann['scroll_duration'] ?>s</span>
                                </div>
                            </td>
                            <td class="text-center">
                                <form action="<?= base_url("admin/announcements/{$ann['id']}/toggle") ?>" method="POST" class="d-inline">
                                    <input type="hidden" name="_token" value="<?= \App\Helpers\Csrf::token() ?>">
                                    <button type="submit" class="btn btn-sm p-0 border-0" title="<?= (bool)$ann['is_active'] ? 'Click to deactivate' : 'Click to activate' ?>">
                                        <?php if ((bool)$ann['is_active']): ?>
                                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-1 extra-small">
                                                <i class="bi bi-check-circle-fill me-1"></i> Active
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-2 py-1 extra-small">
                                                <i class="bi bi-dash-circle me-1"></i> Disabled
                                            </span>
                                        <?php endif; ?>
                                    </button>
                                </form>
                            </td>
                            <td class="text-end pe-4">
                                <div class="btn-group btn-group-sm">
                                    <button 
                                        type="button" 
                                        class="btn btn-outline-secondary btn-sm px-2 py-1 edit-ann-btn"
                                        data-ann='<?= htmlspecialchars(json_encode($ann), ENT_QUOTES, 'UTF-8') ?>'
                                        title="Edit announcement"
                                    >
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <form action="<?= base_url("admin/announcements/{$ann['id']}/delete") ?>" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this announcement?');">
                                        <input type="hidden" name="_token" value="<?= \App\Helpers\Csrf::token() ?>">
                                        <button type="submit" class="btn btn-outline-danger btn-sm px-2 py-1 ms-1" title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" class="text-center py-5">
                            <div class="py-3">
                                <i class="bi bi-megaphone display-4 text-gold opacity-50 mb-3 d-block"></i>
                                <h5 class="fw-bold text-espresso mb-1">No Custom Announcements Yet</h5>
                                <p class="text-muted small mb-3" style="max-width: 480px; margin: 0 auto;">
                                    Create your first moving product banner to alert customers about new arrivals, barista recommendations, or limited specials!
                                </p>
                                <button type="button" class="btn btn-gold btn-sm rounded-pill px-4 fw-semibold" id="btnEmptyStateCreate">
                                    <i class="bi bi-plus-circle me-1"></i> Create First Announcement
                                </button>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- =========================================================================
     MODAL 1: ADD / EDIT ANNOUNCEMENT
     ========================================================================= -->
<div class="modal fade" id="announcementModal" tabindex="-1" aria-labelledby="announcementModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <form action="<?= base_url('admin/announcements') ?>" method="POST" id="announcementForm">
                <input type="hidden" name="_token" value="<?= \App\Helpers\Csrf::token() ?>">
                <input type="hidden" name="id" id="ann_id" value="0">

                <div class="modal-header bg-espresso text-white px-4 py-3 border-0">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-megaphone text-gold fs-5"></i>
                        <h5 class="modal-title fw-bold" id="announcementModalLabel">New Product Announcement</h5>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body px-4 py-4">
                    <!-- Live Dynamic Preview inside Modal -->
                    <div class="mb-4">
                        <label class="form-label extra-small text-uppercase fw-bold text-muted mb-1">
                            <i class="bi bi-play-circle text-gold me-1"></i> Real-time Banner Preview
                        </label>
                        <div id="modalLiveBanner" class="p-2 rounded-3 text-center position-relative overflow-hidden" style="background: #2C1810; color: #FDF8EE; min-height: 48px; border: 1px solid rgba(197, 160, 89, 0.4);">
                            <div class="d-flex align-items-center justify-content-center gap-2 flex-wrap py-1">
                                <span id="modalPreviewBadge" class="badge rounded-pill fw-bold extra-small py-1 px-2" style="background: #C5A059; color: #2C1810;">
                                    NEW PRODUCT
                                </span>
                                <span class="text-gold"><i class="bi bi-cup-hot-fill"></i></span>
                                <span id="modalPreviewText" class="small fw-semibold">
                                    Try our NEW Caramel Macchiato – 250 ETB. Available Now at Kaldis Coffee!
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Row 1: Active switch & Badge text -->
                    <div class="row g-3 mb-3">
                        <div class="col-sm-4">
                            <div class="p-3 bg-light rounded-3 border h-100">
                                <div class="form-check form-switch mb-1">
                                    <input class="form-check-input" type="checkbox" id="ann_is_active" name="is_active" value="1" checked>
                                    <label class="form-check-label fw-bold text-espresso small" for="ann_is_active">Active & Live</label>
                                </div>
                                <div class="extra-small text-muted">Toggle visibility on the digital menu.</div>
                            </div>
                        </div>
                        <div class="col-sm-8">
                            <label for="ann_badge_text" class="form-label small fw-semibold text-espresso mb-1">
                                Prominent Badge Label <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" id="ann_badge_text" name="badge_text" value="NEW PRODUCT" required placeholder="e.g. NEW PRODUCT, SPECIAL OFFER">
                            <div class="d-flex align-items-center gap-1 mt-2 flex-wrap">
                                <span class="extra-small text-muted me-1">Quick presets:</span>
                                <button type="button" class="btn btn-outline-secondary btn-xs py-0 px-2 extra-small rounded-pill badge-preset-btn">NEW PRODUCT</button>
                                <button type="button" class="btn btn-outline-secondary btn-xs py-0 px-2 extra-small rounded-pill badge-preset-btn">NEW PRODUCT ALERT!</button>
                                <button type="button" class="btn btn-outline-secondary btn-xs py-0 px-2 extra-small rounded-pill badge-preset-btn">LIMITED TIME</button>
                                <button type="button" class="btn btn-outline-secondary btn-xs py-0 px-2 extra-small rounded-pill badge-preset-btn">BARISTA'S PICK</button>
                                <button type="button" class="btn btn-outline-secondary btn-xs py-0 px-2 extra-small rounded-pill badge-preset-btn">SPECIAL OFFER</button>
                            </div>
                        </div>
                    </div>

                    <!-- Row 2: Title & Product Link -->
                    <div class="row g-3 mb-3">
                        <div class="col-sm-6">
                            <label for="ann_title" class="form-label small fw-semibold text-espresso mb-1">
                                Internal Title / Headline <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" id="ann_title" name="title" required placeholder="e.g. Caramel Macchiato Launch">
                            <div class="form-text extra-small">Used for identification and admin reporting.</div>
                        </div>
                        <div class="col-sm-6">
                            <label for="ann_product_id" class="form-label small fw-semibold text-espresso mb-1">
                                Link to Catalog Product <span class="text-muted fw-normal">(Optional)</span>
                            </label>
                            <select class="form-select" id="ann_product_id" name="product_id">
                                <option value="">-- No specific product / General Brand Banner --</option>
                                <?php foreach ($products as $p): ?>
                                    <?php 
                                        $pPrice = !empty($p['branches_price']) ? number_format((float)$p['branches_price'] * 1.20, 2) . ' ETB' : (!empty($p['default_value']) ? number_format((float)$p['default_value'] * 1.20, 2) . ' ETB' : '');
                                    ?>
                                    <option 
                                        value="<?= (int)$p['id'] ?>"
                                        data-name="<?= e($p['name']) ?>"
                                        data-price="<?= e($pPrice) ?>"
                                        data-code="<?= e($p['article_code']) ?>"
                                        data-image="<?= e($p['image_path'] ?? '') ?>"
                                    >
                                        <?= e($p['name']) ?> (<?= e($p['article_code']) ?>) <?= $pPrice ? '— ' . $pPrice : '' ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text extra-small">Automatically pulls product name, verified price, and image.</div>
                        </div>
                    </div>

                    <!-- Row 3: Announcement Message & Auto-craft button -->
                    <div class="mb-3">
                        <div class="d-flex align-items-center justify-content-between mb-1">
                            <label for="ann_message" class="form-label small fw-semibold text-espresso mb-0">
                                Moving Banner Message Text <span class="text-danger">*</span>
                            </label>
                            <button type="button" class="btn btn-outline-warning text-dark btn-xs py-0 px-2 rounded-pill extra-small" id="btnAutoCraftMessage">
                                <i class="bi bi-magic me-1 text-gold"></i> Auto-Generate from Product
                            </button>
                        </div>
                        <textarea class="form-control" id="ann_message" name="message" rows="3" required placeholder="Try our NEW Caramel Macchiato – 250 ETB. Available Now at Kaldis Coffee!"></textarea>
                        <div class="form-text extra-small">This text continuously scrolls across the digital menu from right to left.</div>
                    </div>

                    <!-- Row 4: Date Range Scheduling -->
                    <div class="row g-3 mb-3">
                        <div class="col-sm-6">
                            <label for="ann_start_date" class="form-label small fw-semibold text-espresso mb-1">Start Date (Optional)</label>
                            <input type="date" class="form-control" id="ann_start_date" name="start_date">
                            <div class="form-text extra-small">Leave empty to activate immediately.</div>
                        </div>
                        <div class="col-sm-6">
                            <label for="ann_end_date" class="form-label small fw-semibold text-espresso mb-1">End Date (Optional)</label>
                            <input type="date" class="form-control" id="ann_end_date" name="end_date">
                            <div class="form-text extra-small">Leave empty to keep active indefinitely.</div>
                        </div>
                    </div>

                    <!-- Row 5: Animation Speed & Color Themes -->
                    <div class="row g-3 mb-3">
                        <div class="col-sm-5">
                            <label for="ann_scroll_duration" class="form-label small fw-semibold text-espresso mb-1">
                                Scroll Duration / Speed: <span id="durationValueBadge" class="fw-bold text-gold">25s</span>
                            </label>
                            <input type="range" class="form-range" id="ann_scroll_duration" name="scroll_duration" min="12" max="60" step="1" value="25">
                            <div class="d-flex justify-content-between extra-small text-muted">
                                <span>Faster (12s)</span>
                                <span>Smooth (25s)</span>
                                <span>Relaxed (60s)</span>
                            </div>
                        </div>

                        <div class="col-sm-7">
                            <label class="form-label small fw-semibold text-espresso mb-1">Theme Presets</label>
                            <div class="d-flex align-items-center gap-2 flex-wrap mb-2">
                                <button type="button" class="btn btn-outline-secondary btn-xs py-1 px-2 extra-small rounded-pill theme-preset-btn" data-bg="#2C1810" data-text="#FDF8EE" data-accent="#C5A059">
                                    ☕ Kaldis Classic
                                </button>
                                <button type="button" class="btn btn-outline-secondary btn-xs py-1 px-2 extra-small rounded-pill theme-preset-btn" data-bg="#3E2723" data-text="#FFF8E1" data-accent="#FFA000">
                                    🍫 Warm Mocha
                                </button>
                                <button type="button" class="btn btn-outline-secondary btn-xs py-1 px-2 extra-small rounded-pill theme-preset-btn" data-bg="#1A1A1A" data-text="#FFFFFF" data-accent="#D4AF37">
                                    👑 Royal Velvet
                                </button>
                                <button type="button" class="btn btn-outline-secondary btn-xs py-1 px-2 extra-small rounded-pill theme-preset-btn" data-bg="#3B1115" data-text="#FFF0F0" data-accent="#FF8A80">
                                    🍷 Ruby Roast
                                </button>
                            </div>
                            <!-- Custom Color Pickers -->
                            <div class="d-flex align-items-center gap-3">
                                <div>
                                    <label for="ann_bg_color" class="extra-small text-muted d-block mb-1">Background</label>
                                    <div class="d-flex align-items-center gap-1">
                                        <input type="color" class="form-control form-control-color p-0 border-0" id="ann_bg_color" name="bg_color" value="#2C1810" style="width: 32px; height: 32px;">
                                        <span class="extra-small font-monospace text-muted" id="bgColorHex">#2C1810</span>
                                    </div>
                                </div>
                                <div>
                                    <label for="ann_text_color" class="extra-small text-muted d-block mb-1">Text Color</label>
                                    <div class="d-flex align-items-center gap-1">
                                        <input type="color" class="form-control form-control-color p-0 border-0" id="ann_text_color" name="text_color" value="#FDF8EE" style="width: 32px; height: 32px;">
                                        <span class="extra-small font-monospace text-muted" id="textColorHex">#FDF8EE</span>
                                    </div>
                                </div>
                                <div>
                                    <label for="ann_accent_color" class="extra-small text-muted d-block mb-1">Badge Accent</label>
                                    <div class="d-flex align-items-center gap-1">
                                        <input type="color" class="form-control form-control-color p-0 border-0" id="ann_accent_color" name="accent_color" value="#C5A059" style="width: 32px; height: 32px;">
                                        <span class="extra-small font-monospace text-muted" id="accentColorHex">#C5A059</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-top bg-light px-4 py-3 d-flex justify-content-between">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-gold rounded-pill px-4 fw-bold shadow-sm" id="btnSaveAnnouncement">
                        <i class="bi bi-check-lg me-1"></i> Save & Publish
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- =========================================================================
     MODAL 2: GLOBAL BANNER SETTINGS
     ========================================================================= -->
<div class="modal fade" id="bannerSettingsModal" tabindex="-1" aria-labelledby="bannerSettingsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <form action="<?= base_url('admin/announcements/settings') ?>" method="POST">
                <input type="hidden" name="_token" value="<?= \App\Helpers\Csrf::token() ?>">
                <div class="modal-header bg-espresso text-white px-4 py-3 border-0">
                    <h5 class="modal-title fw-bold" id="bannerSettingsModalLabel">
                        <i class="bi bi-sliders text-gold me-2"></i> Announcement Banner Settings
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body px-4 py-4">
                    <div class="p-3 bg-light rounded-4 border mb-3">
                        <div class="form-check form-switch mb-1">
                            <input class="form-check-input" type="checkbox" id="announcements_enabled" name="announcements_enabled" value="1" <?= ($settings['announcements_enabled']['value'] ?? '1') === '1' ? 'checked' : '' ?>>
                            <label class="form-check-label fw-bold text-espresso" for="announcements_enabled">
                                Enable Announcement Banner on Menu
                            </label>
                        </div>
                        <div class="extra-small text-muted mt-1">
                            When enabled, the animated moving message banner appears across public customer menus.
                        </div>
                    </div>

                    <div class="p-3 bg-light rounded-4 border">
                        <div class="form-check form-switch mb-1">
                            <input class="form-check-input" type="checkbox" id="announcements_auto_products" name="announcements_auto_products" value="1" <?= ($settings['announcements_auto_products']['value'] ?? '1') === '1' ? 'checked' : '' ?>>
                            <label class="form-check-label fw-bold text-espresso" for="announcements_auto_products">
                                Auto-Feature Newly Added Products
                            </label>
                        </div>
                        <div class="extra-small text-muted mt-1">
                            If no custom announcements are scheduled or active, automatically create rotating banners highlighting your newest menu items with their verified prices.
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top bg-light px-4 py-3 d-flex justify-content-between">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-gold rounded-pill px-4 fw-bold">Save Settings</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
/* ==========================================================================
   Admin Interactive Ticker Preview Styles
   ========================================================================== */
.kaldis-admin-preview-container {
    width: 100%;
    overflow: hidden;
    position: relative;
}

.kaldis-ticker-bar {
    display: flex;
    align-items: center;
    position: relative;
    width: 100%;
    padding: 10px 0;
    box-shadow: 0 4px 14px rgba(44, 24, 16, 0.12);
    overflow: hidden;
}

.kaldis-ticker-prefix {
    flex-shrink: 0;
    padding: 0 16px;
    z-index: 5;
    position: relative;
}

.kaldis-ticker-prefix-pill {
    font-size: 0.72rem;
    font-weight: 800;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    padding: 5px 12px;
    border-radius: 9999px;
    display: inline-flex;
    align-items: center;
    box-shadow: 0 2px 8px rgba(0,0,0,0.2);
}

.kaldis-ticker-track-wrapper {
    flex-grow: 1;
    overflow: hidden;
    position: relative;
    mask-image: linear-gradient(to right, transparent, black 4%, black 96%, transparent);
    -webkit-mask-image: linear-gradient(to right, transparent, black 4%, black 96%, transparent);
}

.kaldis-ticker-track {
    display: flex;
    white-space: nowrap;
    width: max-content;
    will-change: transform;
    animation: kaldisMarquee linear infinite;
}

.kaldis-ticker-bar:hover .kaldis-ticker-track {
    animation-play-state: paused;
}

.kaldis-ticker-sequence {
    display: flex;
    flex-shrink: 0;
}

.kaldis-ticker-item {
    padding: 0 16px;
    font-size: 0.92rem;
}

.kaldis-ticker-badge {
    font-size: 0.7rem;
    font-weight: 800;
    padding: 3px 8px;
    border-radius: 9999px;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    display: inline-flex;
    align-items: center;
}

.kaldis-ticker-sep {
    color: rgba(197, 160, 89, 0.6);
}

@keyframes kaldisMarquee {
    0% {
        transform: translate3d(0, 0, 0);
    }
    100% {
        transform: translate3d(-50%, 0, 0);
    }
}

@media (prefers-reduced-motion: reduce) {
    .kaldis-ticker-track {
        animation: none !important;
        transform: none !important;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const announcementModal = new bootstrap.Modal(document.getElementById('announcementModal'));
    const form = document.getElementById('announcementForm');
    const modalTitle = document.getElementById('announcementModalLabel');

    const idInput = document.getElementById('ann_id');
    const titleInput = document.getElementById('ann_title');
    const badgeInput = document.getElementById('ann_badge_text');
    const productSelect = document.getElementById('ann_product_id');
    const messageInput = document.getElementById('ann_message');
    const startDateInput = document.getElementById('ann_start_date');
    const endDateInput = document.getElementById('ann_end_date');
    const durationInput = document.getElementById('ann_scroll_duration');
    const durationBadge = document.getElementById('durationValueBadge');
    const activeSwitch = document.getElementById('ann_is_active');

    const bgInput = document.getElementById('ann_bg_color');
    const textInput = document.getElementById('ann_text_color');
    const accentInput = document.getElementById('ann_accent_color');

    const bgColorHex = document.getElementById('bgColorHex');
    const textColorHex = document.getElementById('textColorHex');
    const accentColorHex = document.getElementById('accentColorHex');

    // Live Preview elements
    const modalLiveBanner = document.getElementById('modalLiveBanner');
    const modalPreviewBadge = document.getElementById('modalPreviewBadge');
    const modalPreviewText = document.getElementById('modalPreviewText');

    function updateModalLivePreview() {
        if (!modalLiveBanner) return;
        const bg = bgInput.value || '#2C1810';
        const txt = textInput.value || '#FDF8EE';
        const acc = accentInput.value || '#C5A059';

        modalLiveBanner.style.background = bg;
        modalLiveBanner.style.color = txt;

        if (modalPreviewBadge) {
            modalPreviewBadge.textContent = badgeInput.value || 'NEW PRODUCT';
            modalPreviewBadge.style.background = acc;
        }

        if (modalPreviewText) {
            modalPreviewText.textContent = messageInput.value || (titleInput.value ? 'Try our ' + titleInput.value : 'Try our exciting new arrivals!');
        }

        if (bgColorHex) bgColorHex.textContent = bg;
        if (textColorHex) textColorHex.textContent = txt;
        if (accentColorHex) accentColorHex.textContent = acc;
        if (durationBadge) durationBadge.textContent = durationInput.value + 's';
    }

    // Input listeners for real-time live preview
    [badgeInput, titleInput, messageInput, bgInput, textInput, accentInput].forEach(el => {
        if (el) el.addEventListener('input', updateModalLivePreview);
    });

    if (durationInput) {
        durationInput.addEventListener('input', () => {
            if (durationBadge) durationBadge.textContent = durationInput.value + 's';
        });
    }

    // Preset Badge Buttons
    document.querySelectorAll('.badge-preset-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            badgeInput.value = this.textContent.trim();
            updateModalLivePreview();
        });
    });

    // Theme Preset Buttons
    document.querySelectorAll('.theme-preset-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            bgInput.value = this.getAttribute('data-bg');
            textInput.value = this.getAttribute('data-text');
            accentInput.value = this.getAttribute('data-accent');
            updateModalLivePreview();
        });
    });

    // Auto-craft message button
    const btnAutoCraft = document.getElementById('btnAutoCraftMessage');
    if (btnAutoCraft) {
        btnAutoCraft.addEventListener('click', function () {
            const opt = productSelect.options[productSelect.selectedIndex];
            if (opt && opt.value) {
                const pName = opt.getAttribute('data-name');
                const pPrice = opt.getAttribute('data-price');
                const pricePart = pPrice ? ' – ' + pPrice : '';
                messageInput.value = 'Try our NEW ' + pName + pricePart + '. Available Now at Kaldis Coffee!';
                if (!titleInput.value) {
                    titleInput.value = pName + ' Feature';
                }
            } else if (titleInput.value) {
                messageInput.value = 'Exciting Announcement: ' + titleInput.value + '. Handcrafted fresh at Kaldis Coffee!';
            } else {
                messageInput.value = 'Discover fresh handcrafted specialty coffee and delicious bites today at Kaldis Coffee!';
            }
            updateModalLivePreview();
        });
    }

    // Product change listener: auto-suggest title if empty
    if (productSelect) {
        productSelect.addEventListener('change', function () {
            const opt = this.options[this.selectedIndex];
            if (opt && opt.value && !titleInput.value) {
                titleInput.value = opt.getAttribute('data-name');
            }
            if (opt && opt.value && (!messageInput.value || messageInput.value.includes('Try our NEW'))) {
                const pName = opt.getAttribute('data-name');
                const pPrice = opt.getAttribute('data-price');
                const pricePart = pPrice ? ' – ' + pPrice : '';
                messageInput.value = 'Try our NEW ' + pName + pricePart + '. Available Now at Kaldis Coffee!';
            }
            updateModalLivePreview();
        });
    }

    // Open Create Modal
    function openCreateModal() {
        form.reset();
        idInput.value = '0';
        modalTitle.textContent = 'Add New Product Announcement';
        bgInput.value = '#2C1810';
        textInput.value = '#FDF8EE';
        accentInput.value = '#C5A059';
        durationInput.value = '25';
        activeSwitch.checked = true;
        updateModalLivePreview();
        announcementModal.show();
    }

    const btnCreate = document.getElementById('btnOpenCreateModal');
    if (btnCreate) btnCreate.addEventListener('click', openCreateModal);

    const btnEmptyCreate = document.getElementById('btnEmptyStateCreate');
    if (btnEmptyCreate) btnEmptyCreate.addEventListener('click', openCreateModal);

    // Edit Announcement button
    document.querySelectorAll('.edit-ann-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const data = JSON.parse(this.getAttribute('data-ann'));
            idInput.value = data.id || '0';
            modalTitle.textContent = 'Edit Announcement: ' + (data.title || '');
            titleInput.value = data.title || '';
            badgeInput.value = data.badge_text || 'NEW PRODUCT';
            productSelect.value = data.product_id || '';
            messageInput.value = data.message || '';
            startDateInput.value = data.start_date || '';
            endDateInput.value = data.end_date || '';
            durationInput.value = data.scroll_duration || '25';
            bgInput.value = data.bg_color || '#2C1810';
            textInput.value = data.text_color || '#FDF8EE';
            accentInput.value = data.accent_color || '#C5A059';
            activeSwitch.checked = parseInt(data.is_active) === 1;

            updateModalLivePreview();
            announcementModal.show();
        });
    });
});
</script>
