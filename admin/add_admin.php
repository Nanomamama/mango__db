<?php
require_once 'auth.php';
require_once __DIR__ . '/../db/db.php';
require_once 'sidebar.php';

if (($_SESSION['admin_role'] ?? 'sub') !== 'main') {
    header('Location: index.php');
    exit;
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$editId = isset($_GET['edit_id']) ? (int) $_GET['edit_id'] : 0;
$isEditMode = false;
$editAdmin = [
    'id' => 0,
    'username' => '',
    'email' => '',
    'role' => 'sub',
];

if ($editId > 0) {
    $stmt = $conn->prepare("SELECT id, username, email, role FROM system_administrator WHERE id = ? LIMIT 1");
    if ($stmt) {
        $stmt->bind_param('i', $editId);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $editAdmin = $row;
            $isEditMode = true;
        }
        $stmt->close();
    }
}

$admins = [];
$adminResult = $conn->query("SELECT id, username, email, role, created_at FROM system_administrator ORDER BY id DESC");
if ($adminResult instanceof mysqli_result) {
    while ($row = $adminResult->fetch_assoc()) {
        $admins[] = $row;
    }
    $adminResult->close();
}

$adminPageExtraHead = <<<HTML
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<style>
    .admin-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 18px;
        box-shadow: 0 12px 30px rgba(15, 23, 42, 0.06);
    }
    .role-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        border-radius: 999px;
        padding: 4px 10px;
        font-size: 12px;
        font-weight: 700;
    }
    .role-main {
        background: #ecfdf5;
        color: #047857;
    }
    .role-sub {
        background: #eff6ff;
        color: #1d4ed8;
    }
</style>
HTML;

adminPageStart($isEditMode ? 'แก้ไขแอดมิน' : 'เพิ่มแอดมิน');
?>

<div class="container-fluid">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h2 class="fw-bold mb-1"><?= $isEditMode ? 'แก้ไขข้อมูลแอดมิน' : 'เพิ่มแอดมิน' ?></h2>
            <div class="text-muted">กำหนดบัญชีผู้ดูแลระบบและบทบาทการใช้งาน</div>
        </div>
        <?php if ($isEditMode): ?>
            <a href="add_admin.php" class="btn btn-outline-success">
                <i class="bi bi-plus-circle me-1"></i> เพิ่มแอดมินใหม่
            </a>
        <?php endif; ?>
    </div>

    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>สร้างบัญชีแอดมินสำเร็จแล้ว
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['updated'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>อัปเดตข้อมูลแอดมินสำเร็จแล้ว
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <?php
            $error = $_GET['error'];
            echo $error === 'duplicate'
                ? 'Username หรือ Email นี้มีอยู่ในระบบแล้ว'
                : ($error === 'password_mismatch'
                    ? 'รหัสผ่านและยืนยันรหัสผ่านไม่ตรงกัน'
                    : 'กรุณาตรวจสอบข้อมูลให้ถูกต้อง');
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="row g-4">
        <div class="col-xl-5">
            <div class="admin-card p-4">
                <h5 class="fw-bold mb-3">
                    <i class="bi <?= $isEditMode ? 'bi-pencil-square' : 'bi-person-plus-fill' ?> me-2 text-success"></i>
                    <?= $isEditMode ? 'ฟอร์มแก้ไขแอดมิน' : 'ฟอร์มเพิ่มแอดมิน' ?>
                </h5>

                <form action="save_admin.php" method="POST" id="addAdminForm" class="needs-validation" novalidate>
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="admin_id" value="<?= (int) $editAdmin['id'] ?>">

                    <div class="mb-3">
                        <label for="username" class="form-label">Username <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="username" name="username" value="<?= htmlspecialchars((string) $editAdmin['username'], ENT_QUOTES, 'UTF-8') ?>" required>
                        <div class="invalid-feedback">กรุณากรอก username</div>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars((string) $editAdmin['email'], ENT_QUOTES, 'UTF-8') ?>" required>
                        <div class="invalid-feedback">กรุณากรอกอีเมลให้ถูกต้อง</div>
                    </div>

                    <div class="mb-3">
                        <label for="role" class="form-label">Role <span class="text-danger">*</span></label>
                        <select class="form-select" id="role" name="role" required>
                            <option value="sub" <?= ($editAdmin['role'] ?? 'sub') === 'sub' ? 'selected' : '' ?>>sub - ผู้ดูแลทั่วไป</option>
                            <option value="main" <?= ($editAdmin['role'] ?? 'sub') === 'main' ? 'selected' : '' ?>>main - ผู้ดูแลหลัก</option>
                        </select>
                        <div class="form-text">เฉพาะ main เท่านั้นที่เข้าเมนูเพิ่ม/แก้ไขแอดมินได้</div>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">
                            Password <?= $isEditMode ? '<span class="text-muted">(เว้นว่างถ้าไม่เปลี่ยน)</span>' : '<span class="text-danger">*</span>' ?>
                        </label>
                        <input type="password" class="form-control" id="password" name="password" <?= $isEditMode ? '' : 'required' ?>>
                        <div class="invalid-feedback">กรุณากรอกรหัสผ่าน</div>
                    </div>

                    <div class="mb-4">
                        <label for="confirm_password" class="form-label">
                            Confirm Password <?= $isEditMode ? '<span class="text-muted">(กรอกเมื่อเปลี่ยนรหัสผ่าน)</span>' : '<span class="text-danger">*</span>' ?>
                        </label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" <?= $isEditMode ? '' : 'required' ?>>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <?php if ($isEditMode): ?>
                            <a href="add_admin.php" class="btn btn-light">ยกเลิก</a>
                        <?php endif; ?>
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-save me-1"></i><?= $isEditMode ? 'บันทึกการแก้ไข' : 'สร้างบัญชี' ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="col-xl-7">
            <div class="admin-card p-4">
                <h5 class="fw-bold mb-3">
                    <i class="bi bi-people-fill me-2 text-success"></i>รายชื่อแอดมิน
                </h5>

                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Created</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($admins as $admin): ?>
                                <tr>
                                    <td class="fw-semibold"><?= htmlspecialchars((string) $admin['username'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars((string) $admin['email'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td>
                                        <span class="role-badge role-<?= htmlspecialchars((string) $admin['role'], ENT_QUOTES, 'UTF-8') ?>">
                                            <i class="bi <?= $admin['role'] === 'main' ? 'bi-shield-check' : 'bi-person' ?>"></i>
                                            <?= htmlspecialchars((string) $admin['role'], ENT_QUOTES, 'UTF-8') ?>
                                        </span>
                                    </td>
                                    <td class="text-muted"><?= htmlspecialchars((string) $admin['created_at'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td class="text-end">
                                        <a href="add_admin.php?edit_id=<?= (int) $admin['id'] ?>" class="btn btn-sm btn-outline-success">
                                            <i class="bi bi-pencil-square"></i> แก้ไข
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>

                            <?php if (empty($admins)): ?>
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">ยังไม่มีข้อมูลแอดมิน</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    (() => {
        const form = document.getElementById('addAdminForm');
        const password = document.getElementById('password');
        const confirmPassword = document.getElementById('confirm_password');

        form.addEventListener('submit', (event) => {
            if (password.value !== confirmPassword.value) {
                confirmPassword.setCustomValidity('Passwords do not match');
            } else {
                confirmPassword.setCustomValidity('');
            }

            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }

            form.classList.add('was-validated');
        });
    })();
</script>

<?php adminPageEnd(); ?>
