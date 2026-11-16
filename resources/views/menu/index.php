<?php
/**
 * Kaldis Coffee PLC — Digital Menu & Branch Selection
 * Mode 1: Branch Selection (when no branch is selected) -> Matches Screenshot 1
 * Mode 2: Category & Product Exploration (when branch is selected) -> Matches Screenshot 2
 */

$logoUrl = !empty($settings['custom_logo']['value']) && file_exists(BASE_PATH . '/public/' . ltrim($settings['custom_logo']['value'], '/'))
    ? base_url($settings['custom_logo']['value'])
    : null;

// Map categories to curated food & drink photography matching Screenshot 2
$categoryPhotos = [
    'hot beverage'             => 'https://images.unsplash.com/photo-1541167760496-1628856ab772?q=80&w=700&auto=format&fit=crop',
    'hot beverages'            => 'https://images.unsplash.com/photo-1541167760496-1628856ab772?q=80&w=700&auto=format&fit=crop',
    'bottled beverage'         => 'https://images.unsplash.com/photo-1622483767028-3f66f32aef97?q=80&w=700&auto=format&fit=crop',
    'cold & iced beverages'    => 'https://images.unsplash.com/photo-1517701604599-bb29b565090c?q=80&w=700&auto=format&fit=crop',
    'beverage'                 => 'https://images.unsplash.com/photo-1622483767028-3f66f32aef97?q=80&w=700&auto=format&fit=crop',
    'juice'                    => 'https://images.unsplash.com/photo-1553530666-ba11a7da3888?q=80&w=700&auto=format&fit=crop',
    'torta'                    => 'https://images.unsplash.com/photo-1578985545062-69928b1d9587?q=80&w=700&auto=format&fit=crop',
    'ice cream'                => 'https://images.unsplash.com/photo-1563805042-7684c019e1cb?q=80&w=700&auto=format&fit=crop',
    'fast food'                => 'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?q=80&w=700&auto=format&fit=crop',
    'food'                     => 'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?q=80&w=700&auto=format&fit=crop',
    'milk'                     => 'https://images.unsplash.com/photo-1517256064527-09c73fc73e38?q=80&w=700&auto=format&fit=crop',
    'dairy product'            => 'https://images.unsplash.com/photo-1517256064527-09c73fc73e38?q=80&w=700&auto=format&fit=crop',
    'cake'                     => 'https://images.unsplash.com/photo-1588195538326-c5b1e9f80a1b?q=80&w=700&auto=format&fit=crop',
    'extra'                    => 'https://images.unsplash.com/photo-1525351484163-7529414344d8?q=80&w=700&auto=format&fit=crop',
    'breakfast & light meals'  => 'https://images.unsplash.com/photo-1525351484163-7529414344d8?q=80&w=700&auto=format&fit=crop',
    'pastry'                   => 'https://images.unsplash.com/photo-1555507036-ab1f4038808a?q=80&w=700&auto=format&fit=crop',
    'bakery & fresh pastries'  => 'https://images.unsplash.com/photo-1555507036-ab1f4038808a?q=80&w=700&auto=format&fit=crop',
    'coffee beans & retail goods' => 'https://images.unsplash.com/photo-1587734195503-904fca47e0e9?q=80&w=700&auto=format&fit=crop',
    'packed coffee'            => 'https://images.unsplash.com/photo-1587734195503-904fca47e0e9?q=80&w=700&auto=format&fit=crop',
    'traditional teas & infusions' => 'https://images.unsplash.com/photo-1576092768241-dec231879fc3?q=80&w=700&auto=format&fit=crop',
    'wine'                     => 'https://images.unsplash.com/photo-1510812431401-41d2bd2722f3?q=80&w=700&auto=format&fit=crop',
];

// Calculate item count per child category and per parent category
$childCategoryCounts  = [];
$parentCategoryCounts = [];
if (!empty($products['data'])) {
    foreach ($products['data'] as $p) {
        $cId = (int)($p['child_category_id'] ?? 0);
        $pId = (int)($p['parent_category_id'] ?? 0);
        if ($cId > 0) {
            $childCategoryCounts[$cId] = ($childCategoryCounts[$cId] ?? 0) + 1;
        }
        if ($pId > 0) {
            $parentCategoryCounts[$pId] = ($parentCategoryCounts[$pId] ?? 0) + 1;
        }
    }
}

$currentParent = isset($_GET['parent']) ? (int)$_GET['parent'] : 0;
$currentChild  = isset($_GET['child']) ? (int)$_GET['child'] : 0;

// Build flat list of all visible child categories for Explore Menu
$exploreChildCategories = [];
$activeChildObj = null;

foreach ($categories as $pCat) {
    if (!(bool)$pCat['is_visible']) continue;
    foreach ($pCat['children'] ?? [] as $cCat) {
        if (!(bool)$cCat['is_visible']) continue;
        $cId = (int)$cCat['id'];
        $itemCount = $childCategoryCounts[$cId] ?? 0;
        
        $childData = [
            'id'          => $cId,
            'name'        => $cCat['name'],
            'parent_id'   => (int)$pCat['id'],
            'parent_name' => $pCat['name'],
            'image_path'  => $cCat['image_path'] ?? null,
            'item_count'  => $itemCount,
        ];

        if ($currentChild > 0 && $cId === $currentChild) {
            $activeChildObj = $childData;
        }

        // Only display child categories that have active products in this branch
        if ($itemCount > 0) {
            $exploreChildCategories[] = $childData;
        }
    }
}
?>

<?php if (empty($branch)): ?>
    <!-- ====================================================================
         SCREENSHOT 1: BRANCH SELECTION VIEW ("SELECT YOUR NEAREST BRANCH")
         ==================================================================== -->
    <div class="branch-selection-wrapper">
        <!-- Hero Section -->
        <section class="branch-hero text-center">
            <div class="container" style="max-width: 820px;">
                <?php if ($logoUrl): ?>
                    <div class="branch-hero-logo shadow-sm mb-3">
                        <img src="<?= $logoUrl ?>" alt="Kaldis Coffee Logo" class="branch-hero-logo-img">
                    </div>
                <?php else: ?>
                    <div class="brand-crest mx-auto mb-3 shadow" style="width: 58px; height: 58px;">
                        <i class="bi bi-cup-hot-fill text-gold fs-3"></i>
                    </div>
                <?php endif; ?>

                <h1 class="branch-hero-title mb-1"><?= e($settings['menu_title']['value'] ?? 'Kaldis Coffee') ?></h1>
                <div class="branch-hero-subtitle mb-4">SELECT YOUR NEAREST BRANCH</div>

                <!-- Live Branch Search Input -->
                <div class="branch-search-box position-relative">
                    <i class="bi bi-search branch-search-icon"></i>
                    <input 
                        type="search" 
                        id="branchSearchInput" 
                        class="form-control branch-search-input" 
                        placeholder="Search branches or locations..."
                        autocomplete="off"
                    >
                    <button type="button" id="branchSearchClear" class="btn btn-sm text-white-50 position-absolute end-0 top-50 translate-middle-y me-2" style="display: none;">
                        <i class="bi bi-x-circle-fill"></i>
                    </button>
                </div>
            </div>
        </section>

        <?php if (!empty($announcements)): ?>
            <?php
            $mainAnn = $announcements[0];
            $bBg = $mainAnn['bg_color'] ?: '#2C1810';
            $bText = $mainAnn['text_color'] ?: '#FDF8EE';
            $bAccent = $mainAnn['accent_color'] ?: '#C5A059';
            $bDuration = !empty($mainAnn['scroll_duration']) ? (int)$mainAnn['scroll_duration'] : 25;
            ?>
            <div class="kaldis-moving-banner-container" style="background: <?= e($bBg) ?>; color: <?= e($bText) ?>;">
                <div class="kaldis-moving-banner-inner">
                    <div class="kaldis-moving-banner-badge-fixed" style="background: <?= e($bAccent) ?>; color: #2C1810;">
                        <i class="bi bi-megaphone-fill me-1"></i>
                        <span class="d-none d-sm-inline">WHAT'S NEW</span>
                        <span class="d-inline d-sm-none">NEW</span>
                    </div>
                    <div class="kaldis-moving-banner-track-viewport">
                        <div class="kaldis-moving-banner-track" style="animation-duration: <?= $bDuration ?>s;">
                            <?php for ($loop = 0; $loop < 2; $loop++): ?>
                                <div class="kaldis-moving-banner-group" <?= $loop > 0 ? 'aria-hidden="true"' : '' ?>>
                                    <?php foreach ($announcements as $ann): ?>
                                        <div class="kaldis-moving-banner-item" <?= !empty($ann['product_id']) ? 'data-product-id="' . (int)$ann['product_id'] . '"' : '' ?>>
                                            <span class="kaldis-banner-tag" style="background: <?= e($ann['accent_color'] ?: $bAccent) ?>; color: #1c100b;">
                                                <i class="bi bi-stars me-1"></i><?= e($ann['badge_text'] ?: 'NEW PRODUCT') ?>
                                            </span>
                                            <span class="kaldis-banner-cup"><i class="bi bi-cup-hot-fill"></i></span>
                                            <?php if (!empty($ann['product_image']) && file_exists(BASE_PATH . '/public/' . ltrim($ann['product_image'], '/'))): ?>
                                                <img src="<?= base_url($ann['product_image']) ?>" alt="" class="kaldis-banner-prod-img">
                                            <?php endif; ?>
                                            <span class="kaldis-banner-text">
                                                <?= e($ann['message']) ?>
                                            </span>
                                            <span class="kaldis-banner-sep">&bull; ☕ &bull;</span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endfor; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Branch Grid Section -->
        <section class="container py-4 py-md-5">
            <div class="row g-3 g-md-4" id="branchesGrid">
                <?php if (!empty($branches)): ?>
                    <?php foreach ($branches as $b): ?>
                        <?php 
                            $bNameLower = strtolower(trim($b['name']));
                            $bCodeLower = strtolower(trim($b['code']));
                            $isAirport = ($bNameLower === 'airport' || $bNameLower === 'airport 2' || str_starts_with($bNameLower, 'airport') || in_array($bCodeLower, ['43516', '43532', 'airport', 'airport 2', 'airport_2', 'airport-2'], true));
                            $isEca = ($bNameLower === 'eca' || str_starts_with($bNameLower, 'eca ') || in_array($bCodeLower, ['43492', 'eca'], true));

                            if ($isAirport) {
                                $pricingLabel = '$ USD PRICING';
                            } elseif ($isEca) {
                                $pricingLabel = 'ECA PRICING';
                            } else {
                                $pricingLabel = 'STANDARD PRICING';
                            }
                        ?>
                        <div class="col-12 col-md-6 col-lg-4 branch-card-col" 
                             data-name="<?= e(strtolower($b['name'])) ?>" 
                             data-address="<?= e(strtolower($b['address'] ?? '')) ?>">
                            <a href="<?= base_url('menu?branch=' . urlencode($b['code'])) ?>" class="branch-card d-flex flex-column h-100 text-decoration-none">
                                <!-- Watermark paper plane/cursor icon -->
                                <div class="branch-watermark">
                                    <i class="bi bi-cursor-fill"></i>
                                </div>

                                <div class="branch-card-header d-flex align-items-start gap-3">
                                    <div class="branch-icon-crest">
                                        <i class="bi bi-cup-hot text-gold"></i>
                                    </div>
                                    <div class="branch-card-info flex-grow-1">
                                        <h5 class="branch-card-name"><?= e($b['name']) ?></h5>
                                        <div class="branch-card-address">
                                            <i class="bi bi-geo-alt-fill text-gold flex-shrink-0"></i>
                                            <span class="text-truncate"><?= e($b['address'] ?? 'Official Branch') ?></span>
                                        </div>
                                    </div>
                                </div>

                                <div class="branch-card-footer mt-auto d-flex align-items-center justify-content-between pt-3">
                                    <span class="branch-pricing-tag"><?= $pricingLabel ?></span>
                                    <span class="branch-view-btn">
                                        View Menu <i class="bi bi-arrow-right ms-1"></i>
                                    </span>
                                </div>
                            </a>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Empty State for Branch Search -->
            <div id="branchEmptyState" class="text-center py-5 my-4" style="display: none;">
                <div class="mb-3 text-gold fs-1"><i class="bi bi-geo-alt"></i></div>
                <h4 class="fw-bold text-espresso">No Branches Found</h4>
                <p class="text-muted small">We couldn't find a branch matching your search.</p>
                <button type="button" class="btn btn-outline-gold rounded-pill px-4 btn-sm" id="resetBranchSearchBtn">
                    View All Branches
                </button>
            </div>
        </section>
    </div>

<?php else: ?>
    <!-- ====================================================================
         SCREENSHOT 2: EXPLORE MENU & CATEGORIES VIEW
         ==================================================================== -->
    <div class="menu-view-wrapper">
        <!-- Hero Header -->
        <section class="menu-hero">
            <div class="container" style="max-width: 820px;">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="menu-hero-left">
                        <?php
                            $currNameLower = !empty($branch['name']) ? strtolower(trim($branch['name'])) : '';
                            $currCodeLower = !empty($branch['code']) ? strtolower(trim($branch['code'])) : '';
                            $isCurrentAirport = ($currNameLower === 'airport' || $currNameLower === 'airport 2' || str_starts_with($currNameLower, 'airport') || in_array($currCodeLower, ['43516', '43532', 'airport', 'airport 2', 'airport_2', 'airport-2'], true));
                            $isCurrentEca = ($currNameLower === 'eca' || str_starts_with($currNameLower, 'eca ') || in_array($currCodeLower, ['43492', 'eca'], true));
                        ?>
                        <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                            <h1 class="menu-branch-title mb-0"><?= e($branch['name']) ?></h1>
                            <?php if ($isCurrentAirport): ?>
                                <span class="badge bg-gold text-dark extra-small fw-bold px-2 py-1 rounded-pill">
                                    <i class="bi bi-currency-dollar me-1"></i>USD Menu
                                </span>
                            <?php elseif ($isCurrentEca): ?>
                                <span class="badge bg-gold text-dark extra-small fw-bold px-2 py-1 rounded-pill">
                                    <i class="bi bi-tag-fill me-1"></i>ECA Menu
                                </span>
                            <?php endif; ?>
                        </div>
                        <div class="menu-branch-pill">
                            <i class="bi bi-geo-alt-fill text-gold me-1"></i>
                            <span class="branch-address-text"><?= e($branch['address'] ?? 'Official Branch') ?></span>
                            <span class="mx-1 text-white-50">&bull;</span>
                            <a href="<?= base_url('menu?change_branch=1') ?>" class="menu-change-link" title="Switch to another branch">
                                CHANGE
                            </a>
                        </div>
                    </div>

                    <div class="menu-hero-right">
                        <?php if ($logoUrl): ?>
                            <a href="<?= base_url('menu?change_branch=1') ?>" title="Kaldis Coffee">
                                <img src="<?= $logoUrl ?>" alt="Kaldis Logo" class="menu-header-logo-badge shadow-sm">
                            </a>
                        <?php else: ?>
                            <div class="brand-crest" style="width: 48px; height: 48px;">
                                <i class="bi bi-cup-hot-fill text-gold fs-4"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Product Search Input -->
                <div class="menu-search-box position-relative mt-2">
                    <i class="bi bi-search menu-search-icon"></i>
                    <input 
                        type="search" 
                        id="productSearchInput" 
                        class="form-control menu-search-input" 
                        placeholder="Search products..."
                        value="<?= e($search ?? '') ?>"
                        autocomplete="off"
                    >
                    <button type="button" id="productSearchClear" class="btn btn-sm text-white-50 position-absolute end-0 top-50 translate-middle-y me-2" style="display: <?= !empty($search) ? 'block' : 'none' ?>;">
                        <i class="bi bi-x-circle-fill"></i>
                    </button>
                </div>
            </div>
        </section>

        <?php if (!empty($announcements)): ?>
            <?php
            $mainAnn = $announcements[0];
            $bBg = $mainAnn['bg_color'] ?: '#2C1810';
            $bText = $mainAnn['text_color'] ?: '#FDF8EE';
            $bAccent = $mainAnn['accent_color'] ?: '#C5A059';
            $bDuration = !empty($mainAnn['scroll_duration']) ? (int)$mainAnn['scroll_duration'] : 25;
            ?>
            <div class="kaldis-moving-banner-container" style="background: <?= e($bBg) ?>; color: <?= e($bText) ?>;">
                <div class="kaldis-moving-banner-inner">
                    <div class="kaldis-moving-banner-badge-fixed" style="background: <?= e($bAccent) ?>; color: #2C1810;">
                        <i class="bi bi-megaphone-fill me-1"></i>
                        <span class="d-none d-sm-inline">WHAT'S NEW</span>
                        <span class="d-inline d-sm-none">NEW</span>
                    </div>
                    <div class="kaldis-moving-banner-track-viewport">
                        <div class="kaldis-moving-banner-track" style="animation-duration: <?= $bDuration ?>s;">
                            <?php for ($loop = 0; $loop < 2; $loop++): ?>
                                <div class="kaldis-moving-banner-group" <?= $loop > 0 ? 'aria-hidden="true"' : '' ?>>
                                    <?php foreach ($announcements as $ann): ?>
                                        <div class="kaldis-moving-banner-item" <?= !empty($ann['product_id']) ? 'data-product-id="' . (int)$ann['product_id'] . '"' : '' ?>>
                                            <span class="kaldis-banner-tag" style="background: <?= e($ann['accent_color'] ?: $bAccent) ?>; color: #1c100b;">
                                                <i class="bi bi-stars me-1"></i><?= e($ann['badge_text'] ?: 'NEW PRODUCT') ?>
                                            </span>
                                            <span class="kaldis-banner-cup"><i class="bi bi-cup-hot-fill"></i></span>
                                            <?php if (!empty($ann['product_image']) && file_exists(BASE_PATH . '/public/' . ltrim($ann['product_image'], '/'))): ?>
                                                <img src="<?= base_url($ann['product_image']) ?>" alt="" class="kaldis-banner-prod-img">
                                            <?php endif; ?>
                                            <span class="kaldis-banner-text">
                                                <?= e($ann['message']) ?>
                                            </span>
                                            <span class="kaldis-banner-sep">&bull; ☕ &bull;</span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endfor; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Main Menu Body Container -->
        <div class="container py-4" style="max-width: 820px;">
            
            <!-- Category Cards Grid ("Explore Menu") View (Child Categories) -->
            <div id="categoryCardsSection" style="display: <?= ($currentChild === 0 && empty($search)) ? 'block' : 'none' ?>;">
                <div class="d-flex align-items-center justify-content-between mb-3 pb-2">
                    <div class="explore-header position-relative">
                        <h3 class="explore-title mb-0">Explore Menu</h3>
                        <div class="explore-gold-bar"></div>
                    </div>
                    <span class="explore-count">
                        <?= count($exploreChildCategories) ?> CATEGORIES
                    </span>
                </div>

                <!-- 2-Column Responsive Photography Child Category Grid -->
                <div class="row g-3">
                    <?php foreach ($exploreChildCategories as $childCat): ?>
                        <?php 
                            $catKey = strtolower(trim($childCat['name']));
                            $catPhoto = (!empty($childCat['image_path']) && file_exists(BASE_PATH . '/public/' . ltrim($childCat['image_path'], '/')))
                                ? base_url($childCat['image_path'])
                                : ($categoryPhotos[$catKey] ?? 'https://images.unsplash.com/photo-1509440159596-0249088772ff?q=80&w=700&auto=format&fit=crop');
                        ?>
                        <div class="col-6">
                            <a href="<?= base_url('menu?branch=' . urlencode($branch['code']) . '&child=' . (int)$childCat['id']) ?>" 
                               class="category-photo-card d-block position-relative"
                               data-child-id="<?= (int)$childCat['id'] ?>">
                                
                                <img src="<?= $catPhoto ?>" alt="<?= e($childCat['name']) ?>" class="cat-card-img" loading="lazy">
                                <div class="cat-card-gradient"></div>

                                <div class="cat-card-content d-flex flex-column justify-content-between p-3">
                                    <div class="cat-card-badge-wrap">
                                        <span class="cat-card-count-badge">
                                            <?= $childCat['item_count'] ?> <?= $childCat['item_count'] === 1 ? 'ITEM' : 'ITEMS' ?>
                                        </span>
                                    </div>
                                    <h4 class="cat-card-title mb-0"><?= e($childCat['name']) ?></h4>
                                </div>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Product Items Section (Active when a category is chosen or searching) -->
            <div id="productItemsSection" style="display: <?= ($currentChild > 0 || $currentParent > 0 || !empty($search)) ? 'block' : 'none' ?>;">
                
                <!-- Breadcrumbs & Category Bar -->
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
                    <a href="<?= base_url('menu?branch=' . urlencode($branch['code'])) ?>" class="btn btn-sm btn-outline-gold rounded-pill px-3" id="backToCategoriesBtn">
                        <i class="bi bi-arrow-left me-1"></i> Explore Menu
                    </a>

                    <div class="small text-muted fw-semibold">
                        <?php if ($activeChildObj): ?>
                            Showing: <span class="text-espresso fw-bold"><?= e($activeChildObj['name']) ?></span>
                            <span class="badge bg-light text-muted border ms-1">&rarr; <?= e($activeChildObj['parent_name']) ?></span>
                        <?php elseif (!empty($search)): ?>
                            Search results for: <span class="text-espresso fw-bold">"<?= e($search) ?>"</span>
                        <?php else: ?>
                            Showing: <span class="text-espresso fw-bold">All Products</span>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Product Grid -->
                <div class="row g-3" id="productGrid">
                    <?php if (!empty($products['data'])): ?>
                        <?php foreach ($products['data'] as $p): ?>
                            <?php 
                                // Filter by selected child category if set and not searching
                                if ($currentChild > 0 && empty($search) && (int)($p['child_category_id'] ?? 0) !== $currentChild) {
                                    continue;
                                }
                            ?>
                            <div class="col-12 col-sm-6 product-card-col" 
                                 data-name="<?= e(strtolower($p['name'])) ?>"
                                 data-desc="<?= e(strtolower($p['local_description'] ?? '')) ?>"
                                 data-code="<?= e(strtolower($p['article_code'])) ?>"
                                 data-parent-id="<?= (int)($p['parent_category_id'] ?? 0) ?>"
                                 data-child-id="<?= (int)($p['child_category_id'] ?? 0) ?>">
                                
                                <div class="product-card">
                                    <?php if (!empty($p['image_path']) && file_exists(BASE_PATH . '/public/' . ltrim($p['image_path'], '/'))): ?>
                                        <div class="product-image-container">
                                            <img src="<?= base_url($p['image_path']) ?>" alt="<?= e($p['name']) ?>" class="product-image" loading="lazy">
                                        </div>
                                    <?php endif; ?>
                                    <div class="product-card-body">
                                        <div class="d-flex align-items-center justify-content-between mb-1">
                                            <div class="product-category-breadcrumb mb-0">
                                                <?= e($p['parent_category_name'] ?? 'Menu') ?>
                                                <?php if (!empty($p['child_category_name'])): ?>
                                                    &middot; <?= e($p['child_category_name']) ?>
                                                <?php endif; ?>
                                            </div>
                                            <?php if (!empty($p['consignee_unit'])): ?>
                                                <span class="badge bg-gold-subtle text-gold-dark extra-small px-2 py-1">
                                                    <?= e($p['consignee_unit']) ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>

                                        <h3 class="product-title"><?= e($p['name']) ?></h3>

                                        <p class="product-description">
                                            <?= !empty($p['local_description']) ? e($p['local_description']) : 'Freshly prepared with authentic Kaldis quality.' ?>
                                        </p>

                                        <?php
                                        $vatRate = (float)($settings['vat_rate']['value'] ?? '1.20');
                                        $displayPrice = null;
                                        $isDollar = false;
                                        $priceSuffix = 'ETB';
                                        $showVatLabel = true;

                                        // 1. Airport and Airport 2 branches -> Show airport_price with VAT Multiplier & dollar $ sign
                                        if (!empty($isCurrentAirport)) {
                                            $isDollar = true;
                                            $showVatLabel = true;
                                            $priceSuffix = 'USD';
                                            if (!empty($p['airport_price']) && is_numeric($p['airport_price']) && (float)$p['airport_price'] > 0) {
                                                $displayPrice = round((float)$p['airport_price'] * $vatRate, 2);
                                            } elseif ($p['price'] !== null && (float)$p['price'] > 0) {
                                                $displayPrice = (float)$p['price'];
                                            }
                                        }
                                        // 2. ECA branch -> Show eca_price
                                        elseif (!empty($isCurrentEca)) {
                                            $isDollar = false;
                                            $priceSuffix = 'ETB';
                                            if (!empty($p['eca_price']) && is_numeric($p['eca_price']) && (float)$p['eca_price'] > 0) {
                                                $displayPrice = (float)$p['eca_price'];
                                                $showVatLabel = false;
                                            } elseif ($p['price'] !== null && (float)$p['price'] > 0) {
                                                $displayPrice = (float)$p['price'];
                                                $showVatLabel = true;
                                            } elseif (!empty($p['branches_price']) && is_numeric($p['branches_price']) && (float)$p['branches_price'] > 0) {
                                                $displayPrice = round((float)$p['branches_price'] * $vatRate, 2);
                                                $showVatLabel = true;
                                            } elseif (!empty($p['default_value']) && is_numeric($p['default_value']) && (float)$p['default_value'] > 0) {
                                                $displayPrice = round((float)$p['default_value'] * $vatRate, 2);
                                                $showVatLabel = true;
                                            }
                                        }
                                        // 3. Standard branches
                                        else {
                                            $isDollar = false;
                                            $priceSuffix = e($p['price_currency'] ?? 'ETB');
                                            $showVatLabel = true;
                                            if ($p['price'] !== null && (float)$p['price'] > 0) {
                                                $displayPrice = (float)$p['price'];
                                            } elseif (!empty($p['branches_price']) && is_numeric($p['branches_price']) && (float)$p['branches_price'] > 0) {
                                                $displayPrice = round((float)$p['branches_price'] * $vatRate, 2);
                                            } elseif (!empty($p['default_value']) && is_numeric($p['default_value']) && (float)$p['default_value'] > 0) {
                                                $displayPrice = round((float)$p['default_value'] * $vatRate, 2);
                                            }
                                        }
                                        ?>
                                        <div class="product-footer">
                                            <?php if ($showPrices && $displayPrice !== null): ?>
                                                <div>
                                                    <span class="product-price">
                                                        <?php if ($isDollar): ?>
                                                            <span class="text-gold me-1">$</span><?= number_format($displayPrice, 2) ?>
                                                        <?php else: ?>
                                                            <?= number_format($displayPrice, 2) ?>
                                                        <?php endif; ?>
                                                    </span>
                                                    <span class="product-price-currency"><?= $priceSuffix ?></span>
                                                    <?php if ($showVatLabel): ?>
                                                        <span class="text-muted extra-small d-block" style="font-size: 0.65rem;">incl. VAT</span>
                                                    <?php endif; ?>
                                                </div>
                                            <?php else: ?>
                                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1 extra-small fw-semibold">
                                                    <i class="bi bi-check-circle me-1"></i> Available
                                                </span>
                                            <?php endif; ?>

                                            <span class="badge bg-light text-muted extra-small">
                                                #<?= e($p['article_code']) ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- Empty State for Products -->
                <div id="menuEmptyState" class="text-center py-5 my-4" style="display: <?= empty($products['data']) ? 'block' : 'none' ?>;">
                    <div class="mb-3 text-gold fs-1"><i class="bi bi-cup"></i></div>
                    <h4 class="fw-bold text-espresso mb-1">No Menu Items Found</h4>
                    <p class="text-muted small mb-3">No products matched your search or selected category.</p>
                    <a href="<?= base_url('menu?branch=' . urlencode($branch['code'])) ?>" class="btn btn-gold rounded-pill px-4 btn-sm">
                        View All Categories
                    </a>
                </div>

            </div>
        </div>
    </div>
<?php endif; ?>

<?php if (!empty($popupProduct)): ?>
    <?php
    $pop = $popupProduct;
    $popVatRate = (float)($settings['vat_rate']['value'] ?? '1.20');
    $popDisplayPrice = null;
    $popPriceSuffix = 'ETB';
    $popIsDollar = false;
    $popShowVat = true;

    $popCurrNameLower = !empty($branch['name']) ? strtolower(trim($branch['name'])) : '';
    $popCurrCodeLower = !empty($branch['code']) ? strtolower(trim($branch['code'])) : '';
    $popIsAirport = ($popCurrNameLower === 'airport' || $popCurrNameLower === 'airport 2' || str_starts_with($popCurrNameLower, 'airport') || in_array($popCurrCodeLower, ['43516', '43532', 'airport', 'airport 2', 'airport_2', 'airport-2'], true));
    $popIsEca = ($popCurrNameLower === 'eca' || str_starts_with($popCurrNameLower, 'eca ') || in_array($popCurrCodeLower, ['43492', 'eca'], true));

    if ($popIsAirport) {
        $popIsDollar = true;
        $popShowVat = true;
        $popPriceSuffix = 'USD';
        if (!empty($pop['airport_price']) && is_numeric($pop['airport_price']) && (float)$pop['airport_price'] > 0) {
            $popDisplayPrice = round((float)$pop['airport_price'] * $popVatRate, 2);
        } elseif ($pop['price'] !== null && (float)$pop['price'] > 0) {
            $popDisplayPrice = (float)$pop['price'];
        }
    } elseif ($popIsEca) {
        $popIsDollar = false;
        $popPriceSuffix = 'ETB';
        if (!empty($pop['eca_price']) && is_numeric($pop['eca_price']) && (float)$pop['eca_price'] > 0) {
            $popDisplayPrice = (float)$pop['eca_price'];
            $popShowVat = false;
        } elseif ($pop['price'] !== null && (float)$pop['price'] > 0) {
            $popDisplayPrice = (float)$pop['price'];
            $popShowVat = true;
        } elseif (!empty($pop['branches_price']) && is_numeric($pop['branches_price']) && (float)$pop['branches_price'] > 0) {
            $popDisplayPrice = round((float)$pop['branches_price'] * $popVatRate, 2);
            $popShowVat = true;
        } elseif (!empty($pop['default_value']) && is_numeric($pop['default_value']) && (float)$pop['default_value'] > 0) {
            $popDisplayPrice = round((float)$pop['default_value'] * $popVatRate, 2);
            $popShowVat = true;
        }
    } else {
        $popIsDollar = false;
        $popPriceSuffix = e($pop['price_currency'] ?? 'ETB');
        $popShowVat = true;
        if ($pop['price'] !== null && (float)$pop['price'] > 0) {
            $popDisplayPrice = (float)$pop['price'];
        } elseif (!empty($pop['branches_price']) && is_numeric($pop['branches_price']) && (float)$pop['branches_price'] > 0) {
            $popDisplayPrice = round((float)$pop['branches_price'] * $popVatRate, 2);
        } elseif (!empty($pop['default_value']) && is_numeric($pop['default_value']) && (float)$pop['default_value'] > 0) {
            $popDisplayPrice = round((float)$pop['default_value'] * $popVatRate, 2);
        }
    }

    $popImg = !empty($pop['image_path']) && file_exists(BASE_PATH . '/public/' . ltrim($pop['image_path'], '/'))
        ? base_url($pop['image_path'])
        : null;
    ?>
    <!-- QR Scan Welcome Product Popup Modal -->
    <div class="modal fade" id="qrProductPopupModal" tabindex="-1" aria-labelledby="qrProductPopupTitle" aria-hidden="true" data-product-id="<?= (int)$pop['id'] ?>">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 440px;">
            <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden text-center position-relative">
                <!-- Close Button -->
                <button type="button" class="btn-close position-absolute top-0 end-0 m-3 z-3 bg-white p-2 rounded-circle shadow-sm" data-bs-dismiss="modal" aria-label="Close"></button>

                <!-- TOP HEADER: Product Name prominently displayed at the top -->
                <div class="pt-4 px-4 pb-2 bg-white">
                    <span class="badge bg-gold-subtle text-gold-dark rounded-pill px-3 py-1 extra-small fw-bold text-uppercase tracking-wider mb-2 d-inline-flex align-items-center gap-1">
                        <i class="bi bi-star-fill text-gold"></i> Featured Today
                    </span>
                    <h2 class="fw-bold text-espresso mb-1" id="qrProductPopupTitle" style="font-family: var(--font-serif, serif); font-size: 1.75rem; line-height: 1.25;">
                        <?= e($pop['name']) ?>
                    </h2>
                    <?php if (!empty($pop['child_category_name']) || !empty($pop['parent_category_name'])): ?>
                        <div class="text-muted extra-small">
                            <?= e($pop['child_category_name'] ?? $pop['parent_category_name']) ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Product Image or Food Visual -->
                <?php if ($popImg): ?>
                    <div class="position-relative mx-3 my-2 rounded-3 overflow-hidden shadow-sm" style="max-height: 230px; background: #fdfaf6;">
                        <img src="<?= $popImg ?>" alt="<?= e($pop['name']) ?>" class="w-100 object-fit-cover" style="height: 210px;">
                    </div>
                <?php else: ?>
                    <div class="mx-3 my-2 rounded-3 d-flex align-items-center justify-content-center text-gold shadow-sm" style="height: 160px; background: linear-gradient(135deg, #fdf8ee 0%, #faede0 100%);">
                        <i class="bi bi-cup-hot display-3 opacity-75"></i>
                    </div>
                <?php endif; ?>

                <!-- Modal Body: Description & Price -->
                <div class="modal-body px-4 py-3">
                    <?php if (!empty($pop['local_description'])): ?>
                        <p class="text-secondary small mb-3" style="line-height: 1.5;">
                            <?= nl2br(e($pop['local_description'])) ?>
                        </p>
                    <?php else: ?>
                        <p class="text-secondary small mb-3" style="line-height: 1.5;">
                            Handcrafted fresh to order with authentic Kaldis quality and premium ingredients.
                        </p>
                    <?php endif; ?>

                    <?php if ($showPrices && $popDisplayPrice !== null): ?>
                        <div class="my-2 p-2 rounded-3 bg-light d-inline-block px-4">
                            <span class="extra-small text-muted d-block mb-1 text-uppercase fw-semibold">Price</span>
                            <div class="d-flex align-items-baseline justify-content-center gap-1">
                                <?php if ($popIsDollar): ?>
                                    <span class="text-gold fw-bold fs-4">$</span>
                                <?php endif; ?>
                                <span class="fw-bold text-espresso fs-3" style="font-family: var(--font-serif, serif);">
                                    <?= number_format($popDisplayPrice, 2) ?>
                                </span>
                                <span class="small fw-semibold text-muted"><?= $popPriceSuffix ?></span>
                            </div>
                            <?php if ($popShowVat): ?>
                                <span class="extra-small text-muted d-block" style="font-size: 0.65rem;">inclusive of VAT</span>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Modal Footer: Action Button -->
                <div class="modal-footer border-0 pt-0 pb-4 px-4 justify-content-center">
                    <button type="button" class="btn btn-gold rounded-pill px-5 py-2 fw-bold shadow-sm" data-bs-dismiss="modal">
                        Explore Full Menu <i class="bi bi-arrow-right ms-1"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>
