<?php
/**
 * ProductRepository — Local Product Data Access
 *
 * Provides paginated product listings with optional filters, single-record
 * lookups, and admin field updates.  Never exposes credentials or internal
 * fields from unrelated tables.
 *
 * @package App\Repositories
 */

declare(strict_types=1);

namespace App\Repositories;

use App\Helpers\Database;
use App\Helpers\Logger;
use PDO;
use Throwable;

class ProductRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::local();
        $this->ensurePriceColumnsExist();
    }

    /**
     * Self-healing schema: automatically adds new price columns if not yet migrated.
     */
    private function ensurePriceColumnsExist(): void
    {
        static $ensured = false;
        if ($ensured) {
            return;
        }
        $ensured = true;

        try {
            $cols = $this->db->query("SHOW COLUMNS FROM `products` LIKE 'branches_price'")->fetchAll();
            if (empty($cols)) {
                $this->db->exec("
                    ALTER TABLE `products`
                      ADD COLUMN `branches_price` DECIMAL(10,2) NULL AFTER `default_value`,
                      ADD COLUMN `currency` VARCHAR(50) NULL DEFAULT 'ETB' AFTER `branches_price`,
                      ADD COLUMN `airport_price` DECIMAL(10,2) NULL AFTER `currency`,
                      ADD COLUMN `eca_price` DECIMAL(10,2) NULL AFTER `airport_price`
                ");
                @$this->db->exec("
                    UPDATE `products`
                      SET `branches_price` = CAST(`default_value` AS DECIMAL(10,2))
                      WHERE `branches_price` IS NULL 
                        AND `default_value` IS NOT NULL 
                        AND `default_value` REGEXP '^[0-9]+(\\.[0-9]+)?$'
                ");
            }

            $popupCol = $this->db->query("SHOW COLUMNS FROM `products` LIKE 'is_popup'")->fetchAll();
            if (empty($popupCol)) {
                $this->db->exec("
                    ALTER TABLE `products`
                      ADD COLUMN `is_popup` TINYINT(1) NOT NULL DEFAULT 0 AFTER `is_visible`
                ");
            }
        } catch (\Throwable $e) {
            // Silently fall through if database user lacks ALTER permission
        }
    }

    // =========================================================================
    // Public API
    // =========================================================================

    /**
     * Returns a paginated list of products with joined category names.
     *
     * Supported filters (all optional):
     *   - `search`             (string)  — partial match on name or article_code
     *   - `parent_category_id` (int)     — exact match
     *   - `child_category_id`  (int)     — exact match
     *   - `is_visible`         (int 0|1) — visibility flag
     *   - `is_active_source`   (int 0|1) — active flag from source
     *
     * @param array<string,mixed> $filters
     * @param int                 $page    1-based page number.
     * @param int                 $perPage Rows per page.
     *
     * @return array{
     *     data: array<int,array<string,mixed>>,
     *     total: int,
     *     page: int,
     *     per_page: int,
     *     last_page: int
     * }
     */
    public function findAll(array $filters = [], int $page = 1, int $perPage = 24): array
    {
        $page    = max(1, $page);
        $perPage = max(1, min(200, $perPage));
        $offset  = ($page - 1) * $perPage;

        [$whereClause, $params] = $this->buildWhereClause($filters);

        $branchJoin = !empty($filters['branch_id'])
            ? 'LEFT JOIN `branch_products` bp ON (bp.`product_id` = p.`id` AND bp.`branch_id` = :bp_branch_id)'
            : '';

        $sql = "
            SELECT
                p.`id`,
                p.`article_code`,
                p.`name`,
                p.`gsl_type_id`,
                p.`gsl_type`,
                p.`consignee_unit`,
                p.`default_value`,
                p.`branches_price`,
                p.`currency`,
                p.`airport_price`,
                p.`eca_price`,
                p.`is_active_source`,
                p.`src_parent_pref_id`,
                p.`src_parent_pref`,
                p.`src_child_pref_id`,
                p.`src_child_pref`,
                p.`parent_category_id`,
                p.`child_category_id`,
                p.`local_description`,
                p.`image_path`,
                p.`is_visible`,
                p.`is_popup`,
                p.`price`,
                p.`price_currency`,
                p.`synced_at`,
                p.`created_at`,
                p.`updated_at`,
                pc.`name`  AS `parent_category_name`,
                cc.`name`  AS `child_category_name`
            FROM `products` p
            LEFT JOIN `parent_categories` pc ON pc.`id` = p.`parent_category_id`
            LEFT JOIN `child_categories`  cc ON cc.`id` = p.`child_category_id`
            {$branchJoin}
            {$whereClause}
            ORDER BY p.`name` ASC
            LIMIT :limit OFFSET :offset
        ";

        $total = $this->countAll($filters);

        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':limit',  $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset,  PDO::PARAM_INT);
        $stmt->execute();

        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'data'      => $data,
            'total'     => $total,
            'page'      => $page,
            'per_page'  => $perPage,
            'last_page' => max(1, (int) ceil($total / $perPage)),
        ];
    }

    /**
     * Finds a single product by its article code.
     *
     * @param string $code External article code.
     *
     * @return array<string,mixed>|null  Associative row or null if not found.
     */
    public function findByArticleCode(string $code): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT
                p.*,
                pc.`name` AS `parent_category_name`,
                cc.`name` AS `child_category_name`
             FROM `products` p
             LEFT JOIN `parent_categories` pc ON pc.`id` = p.`parent_category_id`
             LEFT JOIN `child_categories`  cc ON cc.`id` = p.`child_category_id`
             WHERE p.`article_code` = :code
             LIMIT 1'
        );
        $stmt->execute([':code' => $code]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row !== false ? $row : null;
    }

    /**
     * Finds a single product by its local primary key.
     *
     * @param int $id Local `products.id`.
     *
     * @return array<string,mixed>|null  Associative row or null if not found.
     */
    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT
                p.*,
                pc.`name` AS `parent_category_name`,
                cc.`name` AS `child_category_name`
             FROM `products` p
             LEFT JOIN `parent_categories` pc ON pc.`id` = p.`parent_category_id`
             LEFT JOIN `child_categories`  cc ON cc.`id` = p.`child_category_id`
             WHERE p.`id` = :id
             LIMIT 1'
        );
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row !== false ? $row : null;
    }

    /**
     * Updates admin-managed fields on a product.
     *
     * Only the following fields are permitted; all others are silently ignored:
     *   - `local_description`
     *   - `image_path`
     *   - `is_visible`
     *   - `price`
     *
     * @param int                 $id     Local product ID.
     * @param array<string,mixed> $fields Map of field => value.
     *
     * @return bool True on success, false on failure or if no valid fields supplied.
     */
    public function updateAdminFields(int $id, array $fields): bool
    {
        $allowed = ['local_description', 'image_path', 'is_visible', 'is_popup', 'price'];
        $setClauses = [];
        $params     = [':id' => $id, ':updated_at' => date('Y-m-d H:i:s')];

        foreach ($allowed as $field) {
            if (array_key_exists($field, $fields)) {
                $setClauses[] = "`{$field}` = :{$field}";
                $params[":{$field}"] = $fields[$field];
            }
        }

        if (empty($setClauses)) {
            Logger::warning("ProductRepository::updateAdminFields called with no valid fields for id={$id}.");
            return false;
        }

        $setClauses[] = '`updated_at` = :updated_at';
        $sql = 'UPDATE `products` SET ' . implode(', ', $setClauses) . ' WHERE `id` = :id';

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->rowCount() > 0;
        } catch (Throwable $e) {
            Logger::error("ProductRepository::updateAdminFields failed for id={$id}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Counts the total number of products matching the given filters.
     *
     * Shares the same filter logic as {@see findAll()}.
     *
     * @param array<string,mixed> $filters
     *
     * @return int Total matching row count.
     */
    public function countAll(array $filters = []): int
    {
        [$whereClause, $params] = $this->buildWhereClause($filters);

        $branchJoin = !empty($filters['branch_id'])
            ? 'LEFT JOIN `branch_products` bp ON (bp.`product_id` = p.`id` AND bp.`branch_id` = :bp_branch_id)'
            : '';

        $sql = "SELECT COUNT(*) FROM `products` p
                LEFT JOIN `parent_categories` pc ON pc.`id` = p.`parent_category_id`
                LEFT JOIN `child_categories`  cc ON cc.`id` = p.`child_category_id`
                {$branchJoin}
                {$whereClause}";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Retrieves branch availability status for a product across all active branches.
     *
     * @param int $productId
     * @return array<int, array<string, mixed>>
     */
    public function getBranchVisibility(int $productId): array
    {
        $sql = "
            SELECT
                b.`id` AS `branch_id`,
                b.`code`,
                b.`name`,
                b.`address`,
                COALESCE(bp.`is_available`, 1) AS `is_available`,
                bp.`price_override`
            FROM `branches` b
            LEFT JOIN `branch_products` bp ON (bp.`branch_id` = b.`id` AND bp.`product_id` = :productId)
            WHERE b.`is_active` = 1
            ORDER BY b.`name` ASC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':productId' => $productId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Saves branch visibility overrides for a product.
     *
     * @param int $productId
     * @param array<int, int|bool> $branchStates Map of branch_id => 1|0
     */
    public function saveBranchVisibility(int $productId, array $branchStates): void
    {
        if (empty($branchStates)) {
            return;
        }

        $sql = "
            INSERT INTO `branch_products` (`branch_id`, `product_id`, `is_available`)
            VALUES (:branch_id, :product_id, :is_available)
            ON DUPLICATE KEY UPDATE `is_available` = VALUES(`is_available`)
        ";

        $stmt = $this->db->prepare($sql);

        foreach ($branchStates as $branchId => $isVisible) {
            $stmt->execute([
                ':branch_id'    => (int) $branchId,
                ':product_id'   => $productId,
                ':is_available' => $isVisible ? 1 : 0,
            ]);
        }
    }

    /**
     * Retrieves the active product marked for QR scan popup.
     *
     * @param int|null $branchId Optional branch ID to check availability
     * @return array<string, mixed>|null
     */
    public function getPopupProduct(?int $branchId = null): ?array
    {
        $branchJoin = $branchId !== null
            ? 'LEFT JOIN `branch_products` bp ON (bp.`product_id` = p.`id` AND bp.`branch_id` = :branch_id)'
            : '';
        $branchCondition = $branchId !== null
            ? 'AND (bp.`is_available` IS NULL OR bp.`is_available` = 1)'
            : '';

        $sql = "
            SELECT
                p.*,
                pc.`name` AS `parent_category_name`,
                cc.`name` AS `child_category_name`
            FROM `products` p
            LEFT JOIN `parent_categories` pc ON pc.`id` = p.`parent_category_id`
            LEFT JOIN `child_categories`  cc ON cc.`id` = p.`child_category_id`
            {$branchJoin}
            WHERE p.`is_popup` = 1
              AND p.`is_visible` = 1
              AND p.`is_active_source` = 1
              {$branchCondition}
            ORDER BY p.`updated_at` DESC
            LIMIT 1
        ";

        try {
            $stmt = $this->db->prepare($sql);
            if ($branchId !== null) {
                $stmt->bindValue(':branch_id', $branchId, PDO::PARAM_INT);
            }
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row !== false ? $row : null;
        } catch (Throwable $e) {
            Logger::error("ProductRepository::getPopupProduct failed: " . $e->getMessage());
            return null;
        }
    }

    // =========================================================================
    // Private helpers
    // =========================================================================

    /**
     * Builds a parameterised WHERE clause from a filters array.
     *
     * @param array<string,mixed> $filters
     *
     * @return array{0: string, 1: array<string,mixed>}  [whereClause, params]
     */
    private function buildWhereClause(array $filters): array
    {
        $conditions = [];
        $params     = [];

        if (!empty($filters['search'])) {
            $conditions[] = '(p.`name` LIKE :search_name OR p.`article_code` LIKE :search_code)';
            $searchVal = '%' . $filters['search'] . '%';
            $params[':search_name'] = $searchVal;
            $params[':search_code'] = $searchVal;
        }

        if (isset($filters['parent_category_id']) && $filters['parent_category_id'] !== '') {
            $conditions[] = 'p.`parent_category_id` = :parent_category_id';
            $params[':parent_category_id'] = (int) $filters['parent_category_id'];
        }

        if (isset($filters['child_category_id']) && $filters['child_category_id'] !== '') {
            $conditions[] = 'p.`child_category_id` = :child_category_id';
            $params[':child_category_id'] = (int) $filters['child_category_id'];
        }

        if (isset($filters['is_visible']) && $filters['is_visible'] !== '') {
            $conditions[] = 'p.`is_visible` = :is_visible';
            $params[':is_visible'] = (int) $filters['is_visible'];
        }

        if (isset($filters['is_active_source']) && $filters['is_active_source'] !== '') {
            $conditions[] = 'p.`is_active_source` = :is_active_source';
            $params[':is_active_source'] = (int) $filters['is_active_source'];
        }

        if (!empty($filters['branch_id'])) {
            $conditions[] = '(bp.`is_available` IS NULL OR bp.`is_available` = 1)';
            $params[':bp_branch_id'] = (int) $filters['branch_id'];
        }

        $whereClause = $conditions
            ? 'WHERE ' . implode(' AND ', $conditions)
            : '';

        return [$whereClause, $params];
    }
}
