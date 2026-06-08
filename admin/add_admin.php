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

$totalAdmins = count($admins);
$mainAdmins = 0;
$subAdmins = 0;
foreach ($admins as $admin) {
    if (($admin['role'] ?? 'sub') === 'main') {
        $mainAdmins++;
    } else {
        $subAdmins++;
    }
}

$adminPageExtraHead = <<<HTML
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<style>
    .page-content.admin-users-bg {
        background:
            radial-gradient(circle at top right, rgba(13, 138, 146, 0.12), transparent 28%),
            linear-gradient(180deg, #f8fbfc 0%, #f3f7f8 100%);
    }

    .admin-manager-page {
        --admin-primary: var(--green);
        --admin-primary-dark: var(--green-dark);
        --admin-primary-soft: rgba(1, 106, 112, 0.08);
        --admin-border: #e2e8f0;
        --admin-text: #0f172a;
        --admin-muted: #64748b;
        display: grid;
        gap: 24px;
    }

    .admin-hero {
        position: relative;
        overflow: hidden;
        border: 1px solid rgba(1, 106, 112, 0.14);
        border-radius: 28px;
        background: linear-gradient(135deg, #ffffff 0%, #f6fbfb 58%, #edf8f8 100%);
        box-shadow: 0 18px 42px rgba(15, 23, 42, 0.08);
        padding: 28px;
    }

    .admin-hero::after {
        content: "";
        position: absolute;
        inset: auto -80px -90px auto;
        width: 240px;
        height: 240px;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(13, 138, 146, 0.18), rgba(13, 138, 146, 0));
        pointer-events: none;
    }

    .admin-hero-grid {
        position: relative;
        z-index: 1;
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        gap: 24px;
        align-items: end;
    }

    .admin-topline {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 14px;
        border-radius: 999px;
        background: var(--admin-primary-soft);
        color: var(--admin-primary);
        font-size: 0.9rem;
        font-weight: 700;
    }

    .admin-hero h1 {
        margin: 14px 0 8px;
        color: var(--admin-text);
        font-size: clamp(1.85rem, 3vw, 2.65rem);
        line-height: 1.1;
        font-weight: 800;
    }

    .admin-hero p {
        margin: 0;
        max-width: 62ch;
        color: var(--admin-muted);
        line-height: 1.7;
    }

    .admin-stats {
        display: grid;
        grid-template-columns: repeat(3, minmax(110px, 1fr));
        gap: 12px;
        min-width: 360px;
    }

    .admin-stat {
        padding: 14px;
        border: 1px solid rgba(1, 106, 112, 0.14);
        border-radius: 18px;
        background: rgba(255, 255, 255, 0.78);
    }

    .admin-stat span {
        color: var(--admin-muted);
        font-size: 0.78rem;
        font-weight: 700;
    }

    .admin-stat strong {
        display: block;
        margin-top: 4px;
        color: var(--admin-text);
        font-size: 1.55rem;
        line-height: 1;
    }

    .admin-shell {
        display: grid;
        grid-template-columns: minmax(330px, 0.9fr) minmax(0, 1.35fr);
        gap: 24px;
        align-items: start;
    }

    .admin-panel {
        border: 1px solid rgba(1, 106, 112, 0.14);
        border-radius: 24px;
        background: rgba(255, 255, 255, 0.94);
        box-shadow: 0 18px 42px rgba(15, 23, 42, 0.07);
        overflow: hidden;
    }

    .admin-panel-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        padding: 22px 24px;
        border-bottom: 1px solid var(--admin-border);
        background: linear-gradient(180deg, #ffffff 0%, #f8fcfc 100%);
    }

    .admin-panel-title {
        display: flex;
        align-items: center;
        gap: 12px;
        margin: 0;
        color: var(--admin-text);
        font-size: 1.08rem;
        font-weight: 800;
    }

    .admin-panel-title i {
        display: inline-flex;
        width: 38px;
        height: 38px;
        align-items: center;
        justify-content: center;
        border-radius: 14px;
        background: var(--admin-primary-soft);
        color: var(--admin-primary);
    }

    .admin-panel-body {
        padding: 24px;
    }

    .admin-manager-page .form-label {
        color: var(--admin-text);
        font-size: 0.92rem;
        font-weight: 700;
    }

    .admin-manager-page .form-control,
    .admin-manager-page .form-select {
        min-height: 48px;
        border: 1px solid var(--admin-border);
        border-radius: 14px;
        color: var(--admin-text);
        background-color: #fff;
    }

    .admin-manager-page .form-control:focus,
    .admin-manager-page .form-select:focus {
        border-color: var(--admin-primary);
        box-shadow: 0 0 0 4px rgba(1, 106, 112, 0.12);
    }

    .admin-field-hint {
        color: var(--admin-muted);
        font-size: 0.84rem;
        line-height: 1.55;
    }

    .admin-actions {
        display: flex;
        justify-content: flex-end;
        gap: 12px;
        flex-wrap: wrap;
        margin-top: 24px;
        padding-top: 24px;
        border-top: 1px solid var(--admin-border);
    }

    .admin-btn,
    .admin-action-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        border: 1px solid transparent;
        text-decoration: none;
        font-weight: 800;
        transition: 0.2s ease;
        white-space: nowrap;
    }

    .admin-btn {
        min-height: 44px;
        padding: 10px 18px;
        border-radius: 14px;
    }

    .admin-btn-primary {
        color: #fff;
        background: var(--admin-primary);
        box-shadow: 0 10px 20px rgba(1, 106, 112, 0.16);
    }

    .admin-btn-primary:hover {
        color: #fff;
        background: var(--admin-primary-dark);
        transform: translateY(-1px);
    }

    .admin-btn-secondary {
        color: var(--admin-primary);
        background: #fff;
        border-color: rgba(1, 106, 112, 0.22);
    }

    .admin-btn-secondary:hover {
        color: var(--admin-primary-dark);
        background: #eefafa;
    }

    .admin-table {
        margin: 0;
    }

    .admin-table thead th {
        padding: 14px 18px;
        border-bottom: 1px solid var(--admin-border);
        color: var(--admin-muted);
        font-size: 0.78rem;
        font-weight: 800;
        text-transform: uppercase;
    }

    .admin-table tbody td {
        padding: 16px 18px;
        border-bottom: 1px solid #eef2f7;
        color: var(--admin-text);
        vertical-align: middle;
    }

    .admin-table tbody tr:last-child td {
        border-bottom: 0;
    }

    .admin-user {
        display: flex;
        align-items: center;
        gap: 12px;
        min-width: 0;
    }

    .admin-avatar {
        display: inline-flex;
        width: 40px;
        height: 40px;
        flex: 0 0 40px;
        align-items: center;
        justify-content: center;
        border-radius: 14px;
        background: var(--admin-primary-soft);
        color: var(--admin-primary);
        font-weight: 800;
    }

    .admin-user-name {
        color: var(--admin-text);
        font-weight: 800;
        overflow-wrap: anywhere;
    }

    .admin-user-email {
        color: var(--admin-muted);
        font-size: 0.88rem;
        overflow-wrap: anywhere;
    }

    .admin-action-btn {
        min-height: 36px;
        padding: 8px 13px;
        border-radius: 999px;
        background: rgba(1, 106, 112, 0.08);
        color: var(--admin-primary);
        font-size: 0.84rem;
    }

    .admin-action-btn:hover {
        color: #fff;
        background: var(--admin-primary);
        transform: translateY(-1px);
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

    .admin-empty {
        padding: 46px 24px;
        text-align: center;
        color: var(--admin-muted);
    }

    .admin-empty i {
        display: block;
        margin-bottom: 10px;
        color: var(--admin-primary);
        font-size: 2.3rem;
    }

    @media (max-width: 1180px) {
        .admin-hero-grid,
        .admin-shell {
            grid-template-columns: 1fr;
        }

        .admin-stats {
            min-width: 0;
        }
    }

    @media (max-width: 768px) {
        .admin-hero,
        .admin-panel {
            border-radius: 20px;
        }

        .admin-hero,
        .admin-panel-body,
        .admin-panel-header {
            padding: 20px;
        }

        .admin-stats {
            grid-template-columns: 1fr;
        }

        .admin-actions,
        .admin-btn {
            width: 100%;
        }

        .admin-table thead {
            display: none;
        }

        .admin-table,
        .admin-table tbody,
        .admin-table tr,
        .admin-table td {
            display: block;
            width: 100%;
        }

        .admin-table tbody tr {
            padding: 16px 0;
            border-bottom: 1px solid var(--admin-border);
        }

        .admin-table tbody td {
            padding: 8px 20px;
            border-bottom: 0;
        }

        .admin-table tbody td[data-label]::before {
            content: attr(data-label);
            display: block;
            margin-bottom: 4px;
            color: var(--admin-muted);
            font-size: 0.78rem;
            font-weight: 800;
        }

        .admin-table .text-end {
            text-align: left !important;
        }
    }
</style>
HTML;

adminPageStart($isEditMode ? 'แก้ไขแอดมิน' : 'เพิ่มแอดมิน');
?>

<script>
    document.querySelector('.page-content')?.classList.add('admin-users-bg');
</script>

<div class="admin-manager-page">

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

    <div class="admin-shell">
        <section class="admin-panel">
            <div class="admin-panel-header">
                <h2 class="admin-panel-title">
                    <i class="bi <?= $isEditMode ? 'bi-pencil-square' : 'bi-person-plus-fill' ?>"></i>
                    <?= $isEditMode ? 'ฟอร์มแก้ไขแอดมิน' : 'ฟอร์มเพิ่มแอดมิน' ?>
                </h2>
            </div>

            <div class="admin-panel-body">
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
                        <div class="admin-field-hint mt-2">เฉพาะ main เท่านั้นที่เข้าเมนูเพิ่ม/แก้ไขแอดมินได้</div>
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

                    <div class="admin-actions">
                        <?php if ($isEditMode): ?>
                            <a href="add_admin.php" class="admin-btn admin-btn-secondary">ยกเลิก</a>
                        <?php endif; ?>
                        <button type="submit" class="admin-btn admin-btn-primary">
                            <i class="bi bi-save"></i><?= $isEditMode ? 'บันทึกการแก้ไข' : 'สร้างบัญชี' ?>
                        </button>
                    </div>
                </form>
            </div>
        </section>

        <section class="admin-panel">
            <div class="admin-panel-header">
                <h2 class="admin-panel-title">
                    <i class="bi bi-people-fill"></i>รายชื่อแอดมิน
                </h2>
            </div>

            <div class="admin-panel-body p-0">
                <div class="table-responsive">
                    <table class="table admin-table align-middle">
                        <thead>
                            <tr>
                                <th>Admin</th>
                                <th>Role</th>
                                <th>Created</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($admins as $admin): ?>
                                <tr>
                                    <td>
                                        <div class="admin-user">
                                            <span class="admin-avatar"><?= strtoupper(substr((string) $admin['username'], 0, 1)) ?></span>
                                            <div>
                                                <div class="admin-user-name"><?= htmlspecialchars((string) $admin['username'], ENT_QUOTES, 'UTF-8') ?></div>
                                                <div class="admin-user-email"><?= htmlspecialchars((string) $admin['email'], ENT_QUOTES, 'UTF-8') ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td data-label="Role">
                                        <span class="role-badge role-<?= htmlspecialchars((string) $admin['role'], ENT_QUOTES, 'UTF-8') ?>">
                                            <i class="bi <?= $admin['role'] === 'main' ? 'bi-shield-check' : 'bi-person' ?>"></i>
                                            <?= htmlspecialchars((string) $admin['role'], ENT_QUOTES, 'UTF-8') ?>
                                        </span>
                                    </td>
                                    <td class="text-muted" data-label="Created"><?= htmlspecialchars((string) $admin['created_at'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td class="text-end" data-label="Action">
                                        <a href="add_admin.php?edit_id=<?= (int) $admin['id'] ?>" class="admin-action-btn">
                                            <i class="bi bi-pencil-square"></i>แก้ไข
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>

                            <?php if (empty($admins)): ?>
                                <tr>
                                    <td colspan="4">
                                        <div class="admin-empty">
                                            <i class="bi bi-person-plus"></i>
                                            ยังไม่มีข้อมูลแอดมิน
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
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
