<?php
/**
 * Application Routes
 */

declare(strict_types=1);

use App\Helpers\Router;
use App\Helpers\Response;
use App\Controllers\AuthController;
use App\Controllers\MenuController;
use App\Controllers\AdminController;
use App\Controllers\FeedbackController;

// ── Public ──────────────────────────────────────────────────────────────────
Router::get('/', function() {
    $branch = \App\Helpers\Request::get('branch', '');
    if ($branch !== '') {
        $resolved = (new \App\Repositories\BranchRepository())->resolveBranch($branch);
        $code = $resolved ? $resolved['code'] : $branch;
        \App\Helpers\Session::set('current_branch_code', $code);
    }
    $qs = !empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : '';
    \App\Helpers\Response::redirect(base_url('menu') . $qs);
});
Router::get('/menu',                [MenuController::class, 'index']);
Router::get('/menu/{branch}',       [MenuController::class, 'branch']);
Router::get('/feedback',            [FeedbackController::class, 'form']);
Router::post('/feedback',           [FeedbackController::class, 'submit']);
Router::get('/feedback/thank-you',  [FeedbackController::class, 'thankYou']);

// ── Authentication ──────────────────────────────────────────────────────────
Router::get('/admin/login',  [AuthController::class, 'loginForm']);
Router::post('/admin/login', [AuthController::class, 'login']);
Router::get('/admin/logout', [AuthController::class, 'logout']);

// ── Admin root redirect ─────────────────────────────────────────────────────
Router::get('/admin',        fn() => Response::redirect(base_url('admin/dashboard')));

// ── Admin — Dashboard ───────────────────────────────────────────────────────
Router::get('/admin/dashboard', [AdminController::class, 'dashboard']);

// ── Admin — Products ────────────────────────────────────────────────────────
Router::get('/admin/products',         [AdminController::class, 'products']);
Router::get('/admin/products/{id}',    [AdminController::class, 'productDetail']);
Router::post('/admin/products/{id}',   [AdminController::class, 'productUpdate']);

// ── Admin — Announcements & Moving Banner ──────────────────────────────────
Router::get('/admin/announcements',                  [AdminController::class, 'announcements']);
Router::post('/admin/announcements',                 [AdminController::class, 'announcementSave']);
Router::post('/admin/announcements/{id}/toggle',     [AdminController::class, 'announcementToggle']);
Router::post('/admin/announcements/{id}/delete',     [AdminController::class, 'announcementDelete']);
Router::post('/admin/announcements/settings',        [AdminController::class, 'announcementSettingsSave']);

// ── Admin — Categories ──────────────────────────────────────────────────────
Router::get('/admin/categories',                   [AdminController::class, 'categories']);
Router::post('/admin/categories/toggle',            [AdminController::class, 'categoryToggle']);
Router::post('/admin/categories/branch-visibility', [AdminController::class, 'categoryBranchVisibility']);
Router::post('/admin/categories/image',             [AdminController::class, 'categoryImageUpload']);

// ── Admin — Branches ────────────────────────────────────────────────────────
Router::get('/admin/branches',             [AdminController::class, 'branches']);
Router::post('/admin/branches',            [AdminController::class, 'branchSave']);
Router::post('/admin/branches/{id}/delete',[AdminController::class, 'branchDelete']);

// ── Admin — QR Codes ────────────────────────────────────────────────────────
Router::get('/admin/qrcodes',                     [AdminController::class, 'qrcodes']);
Router::post('/admin/qrcodes/generate',           [AdminController::class, 'qrcodeGenerate']);
Router::post('/admin/qrcodes/{id}/edit',          [AdminController::class, 'qrcodeUpdate']);
Router::get('/admin/qrcodes/{id}/download',       [AdminController::class, 'qrcodeDownload']);
Router::get('/admin/qrcodes/{id}/print',          [AdminController::class, 'qrcodePrint']);
Router::post('/admin/qrcodes/{id}/deactivate',    [AdminController::class, 'qrcodeDeactivate']);
Router::post('/admin/qrcodes/{id}/delete',        [AdminController::class, 'qrcodeDelete']);

// ── Admin — Import ──────────────────────────────────────────────────────────
Router::get('/admin/import',       [AdminController::class, 'importPage']);
Router::post('/admin/import/run',  [AdminController::class, 'importRun']);
Router::get('/admin/import/logs',  [AdminController::class, 'importLogs']);

// ── Admin — Settings ────────────────────────────────────────────────────────
Router::get('/admin/settings',              [AdminController::class, 'settings']);
Router::post('/admin/settings',             [AdminController::class, 'settingsSave']);
Router::post('/admin/settings/telegram-test',[AdminController::class, 'settingsTelegramTest']);

// ── Admin — Customer Feedbacks ───────────────────────────────────────────────
Router::get('/admin/feedbacks',                         [AdminController::class, 'feedbacks']);
Router::post('/admin/feedbacks/{id}/toggle-reviewed',   [AdminController::class, 'feedbackToggleReviewed']);

// ── Admin — Users (administrator role only) ─────────────────────────────────
Router::get('/admin/users',  [AdminController::class, 'users']);
Router::post('/admin/users', [AdminController::class, 'userSave']);

