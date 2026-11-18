<?php
/**
 * CategoryRepository — Parent and Child Category Data Access
 *
 * Provides read and visibility-update operations for `parent_categories`
 * and `child_categories`.  Used by menu navigation and admin panels.
 *
 * @package App\Repositories
 */

declare(strict_types=1);

namespace App\Repositories;

use App\Helpers\Database;
use App\Helpers\Logger;
use PDO;
use Throwable;

class CategoryRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::local();
        $this->ensureImagePathColumn();
        $this->ensureBranchChildCategoriesTable();
    }

    private function ensureBranchChildCategoriesTable(): void
    {
        static $checked = false;
        if ($checked) {
            return;
        }
        $checked = true;
        try {
            $this->db->exec("
                CREATE TABLE IF NOT EXISTS `branch_child_categories` (
                    `id` INT AUTO_INCREMENT PRIMARY KEY,
                    `branch_id` INT NOT NULL,
                    `child_category_id` INT NOT NULL,
                    `is_available` TINYINT(1) NOT NULL DEFAULT 1,
                    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    UNIQUE KEY `uq_branch_child` (`branch_id`, `child_category_id`),
                    INDEX `idx_branch` (`branch_id`),
                    INDEX `idx_child` (`child_category_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
        } catch (\Throwable $e) {
            // Silently fall through
        }
    }

    private function ensureImagePathColumn(): void
    {
        static $checked = false;
        if ($checked) {
            return;
        }
        $checked = true;
        try {
            $cols = $this->db->query("SHOW COLUMNS FROM `child_categories` LIKE 'image_path'")->fetchAll();
            if (empty($cols)) {
                $this->db->exec("ALTER TABLE `child_categories` ADD COLUMN `image_path` VARCHAR(255) NULL AFTER `is_visible`");
            }
        } catch (\Throwable $e) {
            // Silently fall through if database user lacks ALTER permission
        }
    }

    // =========================================================================
    // Read operations
    // =========================================================================

    /**
     * Returns all parent categories, each with their children nested under
     * a `children` key.  Suitable for building full menu navigation trees.
     *
     * Only categories where at least one linked product exists are included in
     * the children array; the parent is still returned even if it has no
     * visible children (e.g. for admin use).
     *
     * @return array<int,array<string,mixed>>  Indexed array of parent rows,
     *                                         each with a `children` sub-array.
     */
    public function getParentCategoriesWithChildren(): array
    {
        // Fetch all parents ordered by sort_order then name.
        $stmtParents = $this->db->query(
            'SELECT `id`, `source_id`, `name`, `name_normalized`, `is_visible`, `sort_order`
             FROM `parent_categories`
             ORDER BY `sort_order` ASC, `name` ASC'
        );
        $parents = $stmtParents->fetchAll(PDO::FETCH_ASSOC);

        if (empty($parents)) {
            return [];
        }

        // Fetch all children in one query to avoid N+1.
        $stmtChildren = $this->db->query(
            'SELECT `id`, `parent_category_id`, `source_id`, `name`, `name_normalized`,
                    `is_visible`, `image_path`, `sort_order`
             FROM `child_categories`
             ORDER BY `sort_order` ASC, `name` ASC'
        );
        $allChildren = $stmtChildren->fetchAll(PDO::FETCH_ASSOC);

        // Index children by parent_category_id.
        $childrenByParent = [];
        foreach ($allChildren as $child) {
            $childrenByParent[(int) $child['parent_category_id']][] = $child;
        }

        // Attach children to each parent.
        foreach ($parents as &$parent) {
            $parent['children'] = $childrenByParent[(int) $parent['id']] ?? [];
        }
        unset($parent);

        return $parents;
    }

    /**
     * Returns only visible parent categories (is_visible = 1), ordered by
     * sort_order then name.  Used for public-facing menu navigation.
     *
     * @return array<int,array<string,mixed>>
     */
    public function getVisibleParentCategories(): array
    {
        $stmt = $this->db->prepare(
            'SELECT `id`, `source_id`, `name`, `name_normalized`, `is_visible`, `sort_order`
             FROM `parent_categories`
             WHERE `is_visible` = 1
             ORDER BY `sort_order` ASC, `name` ASC'
        );
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Returns all child categories belonging to a given parent, ordered by
     * sort_order then name.
     *
     * @param int $parentId Local `parent_categories.id`.
     *
     * @return array<int,array<string,mixed>>
     */
    public function getChildrenByParent(int $parentId): array
    {
        $stmt = $this->db->prepare(
            'SELECT `id`, `parent_category_id`, `source_id`, `name`, `name_normalized`,
                    `is_visible`, `sort_order`
             FROM `child_categories`
             WHERE `parent_category_id` = :pid
             ORDER BY `sort_order` ASC, `name` ASC'
        );
        $stmt->execute([':pid' => $parentId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Returns a single parent category by its local ID.
     *
     * @param int $id Local `parent_categories.id`.
     *
     * @return array<string,mixed>|null  Row or null if not found.
     */
    public function getParentById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT `id`, `source_id`, `name`, `name_normalized`, `is_visible`, `sort_order`
             FROM `parent_categories`
             WHERE `id` = :id
             LIMIT 1'
        );
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row !== false ? $row : null;
    }

    /**
     * Returns a single child category by its local ID.
     *
     * @param int $id Local `child_categories.id`.
     *
     * @return array<string,mixed>|null  Row or null if not found.
     */
    public function getChildById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT `id`, `parent_category_id`, `source_id`, `name`, `name_normalized`,
                    `is_visible`, `sort_order`
             FROM `child_categories`
             WHERE `id` = :id
             LIMIT 1'
        );
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row !== false ? $row : null;
    }

    // =========================================================================
    // Write operations
    // =========================================================================

    /**
     * Sets the `is_visible` flag on a parent category.
     *
     * @param int  $id      Local `parent_categories.id`.
     * @param bool $visible New visibility state.
     *
     * @return bool True on success (row exists and was updated or already set).
     */
    public function updateParentVisibility(int $id, bool $visible): bool
    {
        try {
            $stmt = $this->db->prepare(
                'UPDATE `parent_categories` SET `is_visible` = :visible WHERE `id` = :id'
            );
            $stmt->execute([':visible' => (int) $visible, ':id' => $id]);
            return $stmt->rowCount() >= 0; // 0 rowCount is OK if value unchanged.
        } catch (Throwable $e) {
            Logger::error("CategoryRepository::updateParentVisibility failed for id={$id}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Sets the `is_visible` flag on a child category.
     *
     * @param int  $id      Local `child_categories.id`.
     * @param bool $visible New visibility state.
     *
     * @return bool True on success.
     */
    public function updateChildVisibility(int $id, bool $visible): bool
    {
        try {
            $stmt = $this->db->prepare(
                'UPDATE `child_categories` SET `is_visible` = :visible WHERE `id` = :id'
            );
            $stmt->execute([':visible' => (int) $visible, ':id' => $id]);
            return $stmt->rowCount() >= 0;
        } catch (Throwable $e) {
            Logger::error("CategoryRepository::updateChildVisibility failed for id={$id}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Updates the image path for a child category.
     *
     * @param int         $id        Local `child_categories.id`.
     * @param string|null $imagePath Relative path from public/ or null to clear.
     *
     * @return bool True on success.
     */
    public function updateChildImage(int $id, ?string $imagePath): bool
    {
        try {
            $stmt = $this->db->prepare(
                'UPDATE `child_categories` SET `image_path` = :img, `updated_at` = NOW() WHERE `id` = :id'
            );
            $stmt->execute([':img' => $imagePath, ':id' => $id]);
            return true;
        } catch (Throwable $e) {
            Logger::error("CategoryRepository::updateChildImage failed for id={$id}: " . $e->getMessage());
            return false;
        }
    }

    // =========================================================================
    // Branch Visibility (Hide by Branch)
    // =========================================================================

    /**
     * Retrieves branch availability map grouped by child category ID.
     *
     * @return array<int, array<int, int>> [child_id => [branch_id => is_available]]
     */
    public function getBranchChildVisibilityMap(): array
    {
        try {
            $rows = $this->db->query("
                SELECT `child_category_id`, `branch_id`, `is_available`
                FROM `branch_child_categories`
            ")->fetchAll(PDO::FETCH_ASSOC);

            $map = [];
            foreach ($rows as $row) {
                $cId = (int)$row['child_category_id'];
                $bId = (int)$row['branch_id'];
                $map[$cId][$bId] = (int)$row['is_available'];
            }
            return $map;
        } catch (Throwable $e) {
            Logger::error("CategoryRepository::getBranchChildVisibilityMap failed: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Returns count of hidden branches for each child category.
     *
     * @return array<int, int> [child_id => hidden_branch_count]
     */
    public function getHiddenBranchCounts(): array
    {
        try {
            $rows = $this->db->query("
                SELECT `child_category_id`, COUNT(*) as `hidden_count`
                FROM `branch_child_categories`
                WHERE `is_available` = 0
                GROUP BY `child_category_id`
            ")->fetchAll(PDO::FETCH_KEY_PAIR);

            return array_map('intval', $rows);
        } catch (Throwable $e) {
            Logger::error("CategoryRepository::getHiddenBranchCounts failed: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Saves branch visibility settings for a single child category across branches.
     *
     * @param int                 $childId          Local child_categories.id
     * @param array<int, int|bool> $branchVisibility Map of [branch_id => 1 (visible) | 0 (hidden)]
     * @return bool
     */
    public function saveBranchChildVisibility(int $childId, array $branchVisibility): bool
    {
        if ($childId <= 0 || empty($branchVisibility)) {
            return false;
        }

        try {
            $stmt = $this->db->prepare("
                INSERT INTO `branch_child_categories` (`branch_id`, `child_category_id`, `is_available`, `updated_at`)
                VALUES (:branch_id, :child_id, :is_available, NOW())
                ON DUPLICATE KEY UPDATE `is_available` = VALUES(`is_available`), `updated_at` = NOW()
            ");

            $this->db->beginTransaction();
            foreach ($branchVisibility as $branchId => $available) {
                $stmt->execute([
                    ':branch_id'     => (int)$branchId,
                    ':child_id'      => $childId,
                    ':is_available'  => (int)$available,
                ]);
            }
            $this->db->commit();
            return true;
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            Logger::error("CategoryRepository::saveBranchChildVisibility failed for childId={$childId}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Returns list of child category IDs that are marked as hidden (is_available = 0)
     * for a given branch ID.
     *
     * @param int|null $branchId
     * @return array<int>
     */
    public function getHiddenChildCategoryIdsForBranch(?int $branchId): array
    {
        if (empty($branchId)) {
            return [];
        }

        try {
            $stmt = $this->db->prepare("
                SELECT `child_category_id`
                FROM `branch_child_categories`
                WHERE `branch_id` = :branch_id AND `is_available` = 0
            ");
            $stmt->execute([':branch_id' => $branchId]);
            return array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
        } catch (Throwable $e) {
            Logger::error("CategoryRepository::getHiddenChildCategoryIdsForBranch failed: " . $e->getMessage());
            return [];
        }
    }
}
