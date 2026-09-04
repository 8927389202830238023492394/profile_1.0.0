<?php
// ============================================================
// ADMIN CRUD HELPER — handles common AJAX actions for all pages
// Call: AdminCRUD::handle($table, $allowedFields) at top of page
// ============================================================
class AdminCRUD {

    public static function handle(string $table, array $allowedFields, callable $onSuccess = null): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['action'])) return;

        requireCsrf();
        $action = $_POST['action'];
        header('Content-Type: application/json');

        try {
            $result = match ($action) {
                'create'        => self::create($table, $allowedFields),
                'update'        => self::update($table, $allowedFields),
                'delete'        => self::delete($table),
                'bulk_delete'   => self::bulkDelete($table),
                'bulk_status'   => self::bulkStatus($table),
                'toggle_status' => self::toggleStatus(),
                'reorder'       => self::reorder($table),
                'delete_media'  => self::deleteMedia(),
                default         => ['success' => false, 'message' => 'Action không hợp lệ.']
            };
            echo json_encode($result);
        } catch (\PDOException $e) {
            error_log("Database Error in CRUD [$action on $table]: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Đã xảy ra lỗi cơ sở dữ liệu. Vui lòng kiểm tra lại dữ liệu nhập.']);
        } catch (\Throwable $e) {
            error_log("System Error in CRUD [$action on $table]: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Hệ thống đang gặp sự cố. Vui lòng thử lại sau.']);
        }
        exit;
    }

    private static function create(string $table, array $fields): array {
        $data = self::extractFields($fields);
        if (empty($data)) return ['success' => false, 'message' => 'Không có dữ liệu.'];
        $cols = implode(', ', array_map(fn($k) => "`$k`", array_keys($data)));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        db()->prepare("INSERT INTO `$table` ($cols) VALUES ($placeholders)")->execute(array_values($data));
        $id = db()->lastInsertId();
        return ['success' => true, 'message' => 'Thêm thành công!', 'id' => $id];
    }

    private static function update(string $table, array $fields): array {
        $id = (int)($_POST['id'] ?? 0);
        if (!$id) return ['success' => false, 'message' => 'ID không hợp lệ.'];
        $data = self::extractFields($fields);
        if (empty($data)) return ['success' => false, 'message' => 'Không có dữ liệu.'];
        $setParts = implode(', ', array_map(fn($k) => "`$k` = ?", array_keys($data)));
        $stmt = db()->prepare("UPDATE `$table` SET $setParts WHERE id = ?");
        $stmt->execute([...array_values($data), $id]);
        if ($stmt->rowCount() === 0) {
            // It could be that data is the same, so we do a quick check if record exists
            $exists = db()->prepare("SELECT 1 FROM `$table` WHERE id = ?");
            $exists->execute([$id]);
            if (!$exists->fetchColumn()) return ['success' => false, 'message' => 'Bản ghi không tồn tại.'];
        }
        return ['success' => true, 'message' => 'Cập nhật thành công!'];
    }

    private static function delete(string $table): array {
        $id = (int)($_POST['id'] ?? 0);
        if (!$id) return ['success' => false, 'message' => 'ID không hợp lệ.'];
        $stmt = db()->prepare("DELETE FROM `$table` WHERE id = ?");
        $stmt->execute([$id]);
        if ($stmt->rowCount() === 0) return ['success' => false, 'message' => 'Không tìm thấy dữ liệu để xóa.'];
        return ['success' => true, 'message' => 'Đã xóa!'];
    }

    private static function bulkDelete(string $table): array {
        $ids = array_filter(array_map('intval', explode(',', $_POST['ids'] ?? '')));
        if (empty($ids)) return ['success' => false, 'message' => 'Không có mục nào được chọn.'];
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = db()->prepare("DELETE FROM `$table` WHERE id IN ($placeholders)");
        $stmt->execute($ids);
        if ($stmt->rowCount() === 0) return ['success' => false, 'message' => 'Không tìm thấy dữ liệu để xóa.'];
        return ['success' => true, 'message' => 'Đã xóa ' . $stmt->rowCount() . ' mục!'];
    }

    private static function bulkStatus(string $table): array {
        $ids = array_filter(array_map('intval', explode(',', $_POST['ids'] ?? '')));
        $val = (int)($_POST['value'] ?? 0);
        if (empty($ids)) return ['success' => false, 'message' => 'Không có mục nào được chọn.'];
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = db()->prepare("UPDATE `$table` SET status = ? WHERE id IN ($placeholders)");
        $stmt->execute([$val, ...$ids]);
        return ['success' => true, 'message' => 'Đã cập nhật!'];
    }

    private static function toggleStatus(): array {
        // [SEC] Whitelist allowed tables to prevent Table Injection
        $allowedTables = ['services','websites','skills','socials','banks','statistics','achievements','testimonials','page_sections'];
        $table = $_POST['table'] ?? '';
        if (!in_array($table, $allowedTables, true)) {
            return ['success' => false, 'message' => 'Bảng không được phép.'];
        }
        $id    = (int)($_POST['id'] ?? 0);
        $field = preg_replace('/[^a-z_]/', '', $_POST['field'] ?? 'status');
        // [SEC] Whitelist allowed fields to prevent column injection
        $allowedFields = ['status','approved','is_featured','visible'];
        if (!in_array($field, $allowedFields, true)) {
            return ['success' => false, 'message' => 'Trường không được phép.'];
        }
        $val   = (int)($_POST['value'] ?? 0);
        if (!$id) return ['success' => false, 'message' => 'Thiếu tham số.'];
        $stmt = db()->prepare("UPDATE `$table` SET `$field` = ? WHERE id = ?");
        $stmt->execute([$val, $id]);
        if ($stmt->rowCount() === 0) {
            $exists = db()->prepare("SELECT 1 FROM `$table` WHERE id = ?");
            $exists->execute([$id]);
            if (!$exists->fetchColumn()) return ['success' => false, 'message' => 'Bản ghi không tồn tại.'];
        }
        return ['success' => true, 'message' => 'Đã cập nhật!'];
    }

    private static function reorder(string $table): array {
        $ids = array_filter(array_map('intval', explode(',', $_POST['ids'] ?? '')));
        foreach ($ids as $order => $id) {
            db()->prepare("UPDATE `$table` SET sort_order = ? WHERE id = ?")->execute([$order + 1, $id]);
        }
        return ['success' => true, 'message' => 'Thứ tự đã được lưu.'];
    }

    private static function deleteMedia(): array {
        $id = (int)($_POST['id'] ?? 0);
        if (!$id) return ['success' => false, 'message' => 'ID không hợp lệ.'];
        $media = db()->prepare("SELECT path FROM media WHERE id = ?");
        $media->execute([$id]);
        $row = $media->fetch();
        if ($row && file_exists($row['path'])) @unlink($row['path']);
        db()->prepare("DELETE FROM media WHERE id = ?")->execute([$id]);
        return ['success' => true];
    }

    private static function extractFields(array $allowedFields): array {
        $data = [];
        foreach ($allowedFields as $field) {
            if (isset($_POST[$field])) {
                $data[$field] = trim($_POST[$field]);
            }
        }
        return $data;
    }

    // Helper: render table with pagination
    public static function paginate(string $table, int $perPage = 20, string $where = '1=1', array $params = [], string $order = 'id DESC'): array {
        $page = max(1, (int)($_GET['page'] ?? 1));
        $search = trim($_GET['search'] ?? '');

        if ($search && $where === '1=1') {
            // Override where for search — callers should handle their own search
        }

        // [SEC-M3 Fix] Remove duplicate query — only use the prepared statement below
        $countStmt = db()->prepare("SELECT COUNT(*) FROM `$table` WHERE $where");
        $countStmt->execute($params);
        $total = (int)$countStmt->fetchColumn();

        $offset = ($page - 1) * $perPage;
        $stmt = db()->prepare("SELECT * FROM `$table` WHERE $where ORDER BY $order LIMIT $perPage OFFSET $offset");
        $stmt->execute($params);
        $rows = $stmt->fetchAll();

        return [
            'rows'      => $rows,
            'total'     => $total,
            'page'      => $page,
            'per_page'  => $perPage,
            'last_page' => max(1, (int)ceil($total / $perPage)),
            'search'    => $search,
        ];
    }
}
