<?php
/**
 * AnnouncementRepository — Manages New Product Announcements & Banner Ticker
 *
 * @package App\Repositories
 */

declare(strict_types=1);

namespace App\Repositories;

use App\Helpers\Database;
use App\Helpers\Logger;
use PDO;
use Throwable;

class AnnouncementRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::local();
        $this->ensureSchemaExists();
    }

    /**
     * Self-healing schema: automatically creates announcements table and settings if missing.
     */
    private function ensureSchemaExists(): void
    {
        static $checked = false;
        if ($checked) {
            return;
        }
        $checked = true;

        try {
            $this->db->exec("
                CREATE TABLE IF NOT EXISTS `announcements` (
                    `id`              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    `title`           VARCHAR(255) NOT NULL,
                    `badge_text`      VARCHAR(50)  NOT NULL DEFAULT 'NEW PRODUCT',
                    `message`         TEXT         NOT NULL,
                    `product_id`      INT UNSIGNED NULL,
                    `branch_id`       INT UNSIGNED NULL,
                    `start_date`      DATE         NULL,
                    `end_date`        DATE         NULL,
                    `scroll_duration` INT UNSIGNED NOT NULL DEFAULT 25 COMMENT 'Seconds per scroll cycle',
                    `bg_color`        VARCHAR(50)  NOT NULL DEFAULT '#2C1810',
                    `text_color`      VARCHAR(50)  NOT NULL DEFAULT '#FDF8EE',
                    `accent_color`    VARCHAR(50)  NOT NULL DEFAULT '#C5A059',
                    `is_active`       TINYINT(1)   NOT NULL DEFAULT 1,
                    `sort_order`      SMALLINT     NOT NULL DEFAULT 0,
                    `created_at`      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    `updated_at`      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    KEY `idx_ann_active` (`is_active`),
                    KEY `idx_ann_dates` (`start_date`, `end_date`),
                    KEY `idx_ann_product` (`product_id`),
                    KEY `idx_ann_branch` (`branch_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");

            $cols = $this->db->query("SHOW COLUMNS FROM `announcements` LIKE 'branch_id'")->fetchAll();
            if (empty($cols)) {
                @$this->db->exec("ALTER TABLE `announcements` ADD COLUMN `branch_id` INT UNSIGNED NULL AFTER `product_id`, ADD KEY `idx_ann_branch` (`branch_id`)");
            }
        } catch (Throwable $e) {
            // Silently continue if permissions are restricted
        }
    }

    /**
     * Returns all announcements for the admin listing.
     *
     * @return array<int, array<string, mixed>>
     */
    public function findAll(): array
    {
        $sql = "
            SELECT 
                a.*,
                b.`name` AS `branch_name`,
                b.`code` AS `branch_code`,
                p.`name` AS `product_name`,
                p.`article_code` AS `product_code`,
                p.`image_path` AS `product_image`,
                p.`default_value`,
                p.`branches_price`,
                p.`currency`,
                p.`airport_price`,
                p.`eca_price`,
                p.`price` AS `price_override`
            FROM `announcements` a
            LEFT JOIN `branches` b ON b.`id` = a.`branch_id`
            LEFT JOIN `products` p ON p.`id` = a.`product_id`
            ORDER BY a.`sort_order` ASC, a.`id` DESC
        ";

        try {
            return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            Logger::error("AnnouncementRepository::findAll failed: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Returns a single announcement by ID.
     *
     * @param int $id
     * @return array<string, mixed>|null
     */
    public function findById(int $id): ?array
    {
        $sql = "
            SELECT 
                a.*,
                b.`name` AS `branch_name`,
                b.`code` AS `branch_code`,
                p.`name` AS `product_name`,
                p.`article_code` AS `product_code`,
                p.`image_path` AS `product_image`,
                p.`default_value`,
                p.`branches_price`,
                p.`currency`,
                p.`airport_price`,
                p.`eca_price`,
                p.`price` AS `price_override`
            FROM `announcements` a
            LEFT JOIN `branches` b ON b.`id` = a.`branch_id`
            LEFT JOIN `products` p ON p.`id` = a.`product_id`
            WHERE a.`id` = :id
            LIMIT 1
        ";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id' => $id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row !== false ? $row : null;
        } catch (Throwable $e) {
            Logger::error("AnnouncementRepository::findById failed: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Finds active announcements eligible to be displayed on the customer menu.
     * If no active custom announcements exist and auto-products is enabled,
     * it generates dynamic announcements from recently added or activated products.
     *
     * @param array<string, mixed>|null $branch Current branch context
     * @param float $vatRate VAT multiplier (default 1.20)
     * @return array<int, array<string, mixed>>
     */
    public function findActiveForDisplay(?array $branch = null, float $vatRate = 1.20): array
    {
        $settings = (new SettingsRepository())->getAll();
        $enabled = ($settings['announcements_enabled']['value'] ?? '1') === '1';

        if (!$enabled) {
            return [];
        }

        // Hide announcement banner when no branch is selected (unselected state)
        if (empty($branch) || empty($branch['id'])) {
            return [];
        }

        $branchId = (int)$branch['id'];
        $branchJoin = 'LEFT JOIN `branch_products` bp ON (bp.`product_id` = a.`product_id` AND bp.`branch_id` = :branch_id)';
        $branchCondition = 'AND (a.`product_id` IS NULL OR bp.`is_available` IS NULL OR bp.`is_available` = 1)
                            AND (a.`branch_id` IS NULL OR a.`branch_id` = :selected_branch_id)';

        $sql = "
            SELECT 
                a.*,
                p.`name` AS `product_name`,
                p.`article_code` AS `product_code`,
                p.`image_path` AS `product_image`,
                p.`default_value`,
                p.`branches_price`,
                p.`currency`,
                p.`airport_price`,
                p.`eca_price`,
                p.`price` AS `price_override`,
                p.`is_visible` AS `product_visible`
            FROM `announcements` a
            LEFT JOIN `products` p ON p.`id` = a.`product_id`
            {$branchJoin}
            WHERE a.`is_active` = 1
              AND (a.`start_date` IS NULL OR a.`start_date` <= CURDATE())
              AND (a.`end_date` IS NULL OR a.`end_date` >= CURDATE())
              AND (a.`product_id` IS NULL OR (p.`is_visible` = 1 AND p.`is_active_source` = 1))
              {$branchCondition}
            ORDER BY a.`sort_order` ASC, a.`id` DESC
        ";

        $items = [];
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':branch_id', $branchId, PDO::PARAM_INT);
            $stmt->bindValue(':selected_branch_id', $branchId, PDO::PARAM_INT);
            $stmt->execute();
            $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            Logger::error("AnnouncementRepository::findActiveForDisplay failed: " . $e->getMessage());
        }

        // Format prices for each announcement
        foreach ($items as &$item) {
            $item['formatted_price'] = $this->formatProductPrice($item, $branch, $vatRate);
        }
        unset($item);

        // Dynamic Product Integration fallback: If no custom announcements or auto-products is on
        $autoProducts = ($settings['announcements_auto_products']['value'] ?? '1') === '1';
        if (empty($items) && $autoProducts) {
            $items = $this->getDynamicRecentProductAnnouncements($branch, $vatRate);
        }

        return $items;
    }

    /**
     * Retrieves recent active products and generates dynamic announcements.
     *
     * @param array<string, mixed>|null $branch
     * @param float $vatRate
     * @return array<int, array<string, mixed>>
     */
    public function getDynamicRecentProductAnnouncements(?array $branch = null, float $vatRate = 1.20): array
    {
        $branchId = !empty($branch['id']) ? (int)$branch['id'] : null;
        $branchJoin = $branchId !== null
            ? 'LEFT JOIN `branch_products` bp ON (bp.`product_id` = p.`id` AND bp.`branch_id` = :branch_id)'
            : '';
        $branchCondition = $branchId !== null
            ? 'AND (bp.`is_available` IS NULL OR bp.`is_available` = 1)'
            : '';

        $sql = "
            SELECT 
                p.`id` AS `product_id`,
                p.`name` AS `product_name`,
                p.`article_code` AS `product_code`,
                p.`image_path` AS `product_image`,
                p.`default_value`,
                p.`branches_price`,
                p.`currency`,
                p.`airport_price`,
                p.`eca_price`,
                p.`price` AS `price_override`,
                p.`local_description`
            FROM `products` p
            {$branchJoin}
            WHERE p.`is_visible` = 1
              AND p.`is_active_source` = 1
              {$branchCondition}
            ORDER BY p.`created_at` DESC, p.`id` DESC
            LIMIT 4
        ";

        $items = [];
        try {
            $stmt = $this->db->prepare($sql);
            if ($branchId !== null) {
                $stmt->bindValue(':branch_id', $branchId, PDO::PARAM_INT);
            }
            $stmt->execute();
            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($products as $idx => $p) {
                $formattedPrice = $this->formatProductPrice($p, $branch, $vatRate);
                $priceStr = $formattedPrice ? " – {$formattedPrice}" : "";
                
                $items[] = [
                    'id'              => 0,
                    'title'           => $p['product_name'],
                    'badge_text'      => 'NEW PRODUCT ALERT',
                    'message'         => "Try our NEW {$p['product_name']}{$priceStr}. Available Now at Kaldis Coffee!",
                    'product_id'      => $p['product_id'],
                    'product_name'    => $p['product_name'],
                    'product_code'    => $p['product_code'],
                    'product_image'   => $p['product_image'],
                    'formatted_price' => $formattedPrice,
                    'scroll_duration' => 25,
                    'bg_color'        => '#2C1810',
                    'text_color'      => '#FDF8EE',
                    'accent_color'    => '#C5A059',
                    'is_active'       => 1,
                    'sort_order'      => $idx,
                ];
            }
        } catch (Throwable $e) {
            Logger::error("AnnouncementRepository::getDynamicRecentProductAnnouncements failed: " . $e->getMessage());
        }

        return $items;
    }

    /**
     * Formats price string adhering to branch logic (Airport USD vs ECA vs Standard ETB).
     */
    private function formatProductPrice(array $row, ?array $branch, float $vatRate): ?string
    {
        $currNameLower = !empty($branch['name']) ? strtolower(trim($branch['name'])) : '';
        $currCodeLower = !empty($branch['code']) ? strtolower(trim($branch['code'])) : '';
        $isAirport = ($currNameLower === 'airport' || $currNameLower === 'airport 2' || str_starts_with($currNameLower, 'airport') || in_array($currCodeLower, ['43516', '43532', 'airport', 'airport 2', 'airport_2', 'airport-2'], true));
        $isEca = ($currNameLower === 'eca' || str_starts_with($currNameLower, 'eca ') || in_array($currCodeLower, ['43492', 'eca'], true));

        if ($isAirport) {
            if (!empty($row['airport_price']) && is_numeric($row['airport_price']) && (float)$row['airport_price'] > 0) {
                $p = round((float)$row['airport_price'] * $vatRate, 2);
                return '$' . number_format($p, 2) . ' USD';
            } elseif (!empty($row['price_override']) && (float)$row['price_override'] > 0) {
                return '$' . number_format((float)$row['price_override'], 2) . ' USD';
            }
        } elseif ($isEca) {
            if (!empty($row['eca_price']) && is_numeric($row['eca_price']) && (float)$row['eca_price'] > 0) {
                return number_format((float)$row['eca_price'], 2) . ' ETB';
            }
        }

        // Standard
        if (!empty($row['price_override']) && (float)$row['price_override'] > 0) {
            return number_format((float)$row['price_override'], 2) . ' ' . ($row['currency'] ?? 'ETB');
        } elseif (!empty($row['branches_price']) && is_numeric($row['branches_price']) && (float)$row['branches_price'] > 0) {
            $p = round((float)$row['branches_price'] * $vatRate, 2);
            return number_format($p, 2) . ' ' . ($row['currency'] ?? 'ETB');
        } elseif (!empty($row['default_value']) && is_numeric($row['default_value']) && (float)$row['default_value'] > 0) {
            $p = round((float)$row['default_value'] * $vatRate, 2);
            return number_format($p, 2) . ' ' . ($row['currency'] ?? 'ETB');
        }

        return null;
    }

    /**
     * Creates a new announcement.
     *
     * @param array<string, mixed> $data
     * @return int Inserted ID or 0 on failure
     */
    public function create(array $data): int
    {
        $sql = "
            INSERT INTO `announcements` (
                `title`, `badge_text`, `message`, `product_id`, `branch_id`,
                `start_date`, `end_date`, `scroll_duration`,
                `bg_color`, `text_color`, `accent_color`,
                `is_active`, `sort_order`
            ) VALUES (
                :title, :badge_text, :message, :product_id, :branch_id,
                :start_date, :end_date, :scroll_duration,
                :bg_color, :text_color, :accent_color,
                :is_active, :sort_order
            )
        ";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':title'           => trim((string)$data['title']),
                ':badge_text'      => trim((string)($data['badge_text'] ?? 'NEW PRODUCT')),
                ':message'         => trim((string)$data['message']),
                ':product_id'      => !empty($data['product_id']) ? (int)$data['product_id'] : null,
                ':branch_id'       => !empty($data['branch_id']) ? (int)$data['branch_id'] : null,
                ':start_date'      => !empty($data['start_date']) ? $data['start_date'] : null,
                ':end_date'        => !empty($data['end_date']) ? $data['end_date'] : null,
                ':scroll_duration' => max(10, min(120, (int)($data['scroll_duration'] ?? 25))),
                ':bg_color'        => !empty($data['bg_color']) ? trim((string)$data['bg_color']) : '#2C1810',
                ':text_color'      => !empty($data['text_color']) ? trim((string)$data['text_color']) : '#FDF8EE',
                ':accent_color'    => !empty($data['accent_color']) ? trim((string)$data['accent_color']) : '#C5A059',
                ':is_active'       => !empty($data['is_active']) ? 1 : 0,
                ':sort_order'      => (int)($data['sort_order'] ?? 0),
            ]);

            return (int)$this->db->lastInsertId();
        } catch (Throwable $e) {
            Logger::error("AnnouncementRepository::create failed: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Updates an existing announcement.
     *
     * @param int $id
     * @param array<string, mixed> $data
     * @return bool
     */
    public function update(int $id, array $data): bool
    {
        $sql = "
            UPDATE `announcements` SET
                `title`           = :title,
                `badge_text`      = :badge_text,
                `message`         = :message,
                `product_id`      = :product_id,
                `branch_id`       = :branch_id,
                `start_date`      = :start_date,
                `end_date`        = :end_date,
                `scroll_duration` = :scroll_duration,
                `bg_color`        = :bg_color,
                `text_color`      = :text_color,
                `accent_color`    = :accent_color,
                `is_active`       = :is_active,
                `sort_order`      = :sort_order
            WHERE `id` = :id
        ";

        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':id'              => $id,
                ':title'           => trim((string)$data['title']),
                ':badge_text'      => trim((string)($data['badge_text'] ?? 'NEW PRODUCT')),
                ':message'         => trim((string)$data['message']),
                ':product_id'      => !empty($data['product_id']) ? (int)$data['product_id'] : null,
                ':branch_id'       => !empty($data['branch_id']) ? (int)$data['branch_id'] : null,
                ':start_date'      => !empty($data['start_date']) ? $data['start_date'] : null,
                ':end_date'        => !empty($data['end_date']) ? $data['end_date'] : null,
                ':scroll_duration' => max(10, min(120, (int)($data['scroll_duration'] ?? 25))),
                ':bg_color'        => !empty($data['bg_color']) ? trim((string)$data['bg_color']) : '#2C1810',
                ':text_color'      => !empty($data['text_color']) ? trim((string)$data['text_color']) : '#FDF8EE',
                ':accent_color'    => !empty($data['accent_color']) ? trim((string)$data['accent_color']) : '#C5A059',
                ':is_active'       => !empty($data['is_active']) ? 1 : 0,
                ':sort_order'      => (int)($data['sort_order'] ?? 0),
            ]);
        } catch (Throwable $e) {
            Logger::error("AnnouncementRepository::update failed for id={$id}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Deletes an announcement.
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM `announcements` WHERE `id` = :id");
            return $stmt->execute([':id' => $id]);
        } catch (Throwable $e) {
            Logger::error("AnnouncementRepository::delete failed for id={$id}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Toggles an announcement's active status.
     *
     * @param int $id
     * @return bool
     */
    public function toggleActive(int $id): bool
    {
        try {
            $stmt = $this->db->prepare("UPDATE `announcements` SET `is_active` = 1 - `is_active` WHERE `id` = :id");
            return $stmt->execute([':id' => $id]);
        } catch (Throwable $e) {
            Logger::error("AnnouncementRepository::toggleActive failed for id={$id}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Returns statistics for admin overview cards.
     *
     * @return array{total: int, active: int, scheduled: int, expired: int}
     */
    public function getStats(): array
    {
        $today = date('Y-m-d');
        $sql = "
            SELECT
                COUNT(*) AS `total`,
                SUM(CASE 
                    WHEN `is_active` = 1 
                     AND (`start_date` IS NULL OR `start_date` <= :today1) 
                     AND (`end_date` IS NULL OR `end_date` >= :today2) 
                    THEN 1 ELSE 0 
                END) AS `active`,
                SUM(CASE 
                    WHEN `is_active` = 1 AND `start_date` > :today3 
                    THEN 1 ELSE 0 
                END) AS `scheduled`,
                SUM(CASE 
                    WHEN `is_active` = 1 AND `end_date` < :today4 
                    THEN 1 ELSE 0 
                END) AS `expired`
            FROM `announcements`
        ";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':today1' => $today,
                ':today2' => $today,
                ':today3' => $today,
                ':today4' => $today,
            ]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return [
                'total'     => (int)($row['total'] ?? 0),
                'active'    => (int)($row['active'] ?? 0),
                'scheduled' => (int)($row['scheduled'] ?? 0),
                'expired'   => (int)($row['expired'] ?? 0),
            ];
        } catch (Throwable $e) {
            return ['total' => 0, 'active' => 0, 'scheduled' => 0, 'expired' => 0];
        }
    }
}
