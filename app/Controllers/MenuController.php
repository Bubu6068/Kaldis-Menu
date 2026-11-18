<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\{View, Request, Response};
use App\Repositories\{ProductRepository, CategoryRepository, BranchRepository, SettingsRepository};
use App\Services\AutoSyncService;

class MenuController
{
    public function index(): void
    {
        // Automatically synchronize any changes from CNET ERP staging in the background (throttled)
        AutoSyncService::checkAndSync();

        $settings  = (new SettingsRepository())->getAll();
        $maintenance = ($settings['maintenance_mode']['value'] ?? '0') === '1';

        if ($maintenance) {
            View::render('menu/maintenance', [
                'title' => 'Menu Temporarily Unavailable',
            ], 'base');
            return;
        }

        // Branch resolution:
        // When visiting /menu directly without a ?branch= parameter, open the All Branches selection view.
        // When a branch is specified (?branch=CODE), display that branch's menu.
        $changeBranch = Request::int('change_branch') === 1 || Request::str('branch') === 'select';
        $branchRepo   = new BranchRepository();
        $branch       = null;

        if ($changeBranch) {
            \App\Helpers\Session::remove('current_branch_code');
        } else {
            $rawBranch = Request::str('branch');

            // Support direct branch query without 'branch=' key
            // (e.g. /menu?Betel Bicha Foq, /menu?Betel+Bicha+Foq, /menu?4 Kilo)
            if ($rawBranch === '' && !empty($_SERVER['QUERY_STRING'])) {
                $rawQuery = trim(urldecode($_SERVER['QUERY_STRING']));
                $firstPart = trim(explode('&', $rawQuery)[0]);
                if (!str_contains($firstPart, '=') && $firstPart !== '' && $firstPart !== 'select' && $firstPart !== 'change_branch') {
                    $rawBranch = $firstPart;
                }
            }

            if ($rawBranch !== '' && $rawBranch !== 'select') {
                $branch = $branchRepo->resolveBranch($rawBranch);
                if ($branch) {
                    \App\Helpers\Session::set('current_branch_code', $branch['code']);
                }
            } else {
                // Visiting /menu directly without branch parameter opens All Branches view
                $branch = null;
                \App\Helpers\Session::remove('current_branch_code');
            }
        }

        $branches = $branchRepo->getActive();

        // Categories
        $categoryRepo   = new CategoryRepository();
        $categories     = $categoryRepo->getParentCategoriesWithChildren();
        $hiddenChildIds = !empty($branch['id']) ? $categoryRepo->getHiddenChildCategoryIdsForBranch((int)$branch['id']) : [];

        // Product filters
        $filters = ['is_visible' => 1, 'is_active_source' => 1];
        if (!empty($branch['id'])) {
            $filters['branch_id'] = (int)$branch['id'];
        }
        $search = Request::str('q');
        if ($search !== '') {
            $filters['search'] = $search;
        }
        $parentFilter = Request::int('parent');
        if ($parentFilter > 0) {
            $filters['parent_category_id'] = $parentFilter;
        }
        $childFilter = Request::int('child');
        if ($childFilter > 0) {
            $filters['child_category_id'] = $childFilter;
        }

        $productRepo = new ProductRepository();
        $products    = $productRepo->findAll($filters, 1, 9999);
        $showPrices  = ($settings['show_prices']['value'] ?? '0') === '1';

        // Load active or dynamic new product announcements
        $vatRate = (float)($settings['vat_multiplier']['value'] ?? $settings['vat_rate']['value'] ?? 1.20);
        $announcements = (new \App\Repositories\AnnouncementRepository())->findActiveForDisplay($branch, $vatRate);

        $title = e($settings['menu_title']['value'] ?? 'Kaldis Coffee')
               . ' — '
               . e($settings['menu_subtitle']['value'] ?? 'Digital Menu');

        View::render('menu/index', [
            'title'          => $title,
            'categories'     => $categories,
            'hiddenChildIds' => $hiddenChildIds,
            'products'       => $products,
            'announcements'  => $announcements,
            'branch'         => $branch,
            'branches'       => $branches,
            'settings'       => $settings,
            'showPrices'     => $showPrices,
            'search'         => $search,
            'hideBaseHeader' => true,
        ], 'base');
    }

    public function branch(string $branch): void
    {
        // Validate branch code format
        if (!preg_match('/^[a-zA-Z0-9_\-]{1,50}$/', $branch)) {
            Response::abort(404, '404 — Branch Not Found');
        }
        $b = (new BranchRepository())->findByCode($branch);
        if (!$b || !(bool)$b['is_active']) {
            Response::abort(404, '404 — Branch Not Found');
        }
        // Canonical redirect to /menu?branch=CODE
        Response::redirect(base_url('menu') . '?branch=' . urlencode($branch));
    }
}

