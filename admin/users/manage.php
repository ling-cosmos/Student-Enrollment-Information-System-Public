<?php
$pageTitle = 'Manage Users';
require_once dirname(dirname(__DIR__)) . '/includes/header.php';
requireRole('admin');

$pdo = getPDO();
$users = $pdo->query(
    "SELECT id, username, full_name, email, role, is_active, last_login, created_at
     FROM users ORDER BY is_active DESC, created_at DESC"
)->fetchAll();
$activeCount = count(array_filter($users, fn($user) => (int)$user['is_active'] === 1));
$adminCount = count(array_filter($users, fn($user) => $user['role'] === 'admin'));
?>

<nav class="seis-breadcrumb" aria-label="Breadcrumb">
  <a href="<?= BASE_URL ?>/admin/dashboard.php">System</a><i class="bi bi-chevron-right" aria-hidden="true"></i><span aria-current="page">Manage Users</span>
</nav>

<div class="page-header">
  <div class="page-header-left"><h1>Manage Users</h1><p class="page-subtitle"><?= count($users) ?> system account<?= count($users) !== 1 ? 's' : '' ?> · <?= $activeCount ?> active · <?= $adminCount ?> administrator<?= $adminCount !== 1 ? 's' : '' ?></p></div>
  <div class="page-header-actions"><a href="<?= BASE_URL ?>/admin/users/add.php" class="btn-seis"><i class="bi bi-person-plus"></i> Add User</a></div>
</div>

<div class="seis-card">
  <div class="seis-card-header"><i class="bi bi-shield-person"></i> System Users<span class="card-header-note">Manage identity, access, and account status</span></div>
  <div class="user-management-toolbar">
    <div class="toolbar-search"><i class="bi bi-search" aria-hidden="true"></i><input type="search" class="seis-input" placeholder="Search name, username, or email" aria-label="Search users" data-user-search></div>
    <select class="seis-select toolbar-select" aria-label="Filter by role" data-user-role><option value="">All roles</option><option value="admin">Administrator</option><option value="registrar">Registrar</option><option value="viewer">Viewer</option></select>
    <select class="seis-select toolbar-select" aria-label="Filter by status" data-user-status><option value="">All statuses</option><option value="active">Active</option><option value="inactive">Inactive</option></select>
    <select class="seis-select toolbar-select page-size-select" aria-label="Rows per page" data-page-size><option value="10">10 rows</option><option value="25">25 rows</option><option value="50">50 rows</option></select>
    <select class="seis-select toolbar-select mobile-table-control" aria-label="Sort users" data-mobile-sort><option value="name:1">Name A–Z</option><option value="name:-1">Name Z–A</option><option value="role:1">Role</option><option value="status:1">Status</option><option value="login:-1">Newest login</option></select>
  </div>
  <form id="bulkUserForm" class="bulk-action-bar" method="POST" action="<?= BASE_URL ?>/handlers/user_handler.php">
    <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>"><input type="hidden" name="action" value="bulk_status">
    <span><strong data-selected-count>0</strong> selected</span>
    <label class="mobile-table-control mobile-select-all"><input type="checkbox" data-mobile-select-all> Select visible</label>
    <select name="bulk_status" class="seis-select toolbar-select" aria-label="Bulk action"><option value="activate">Activate</option><option value="deactivate">Deactivate</option></select>
    <button type="submit" class="btn-seis-outline btn-compact" data-confirm="Apply this status change to the selected accounts?"><i class="bi bi-check2-square"></i> Apply</button>
  </form>
  <div class="table-scroll">
    <table class="seis-table responsive-table" data-table-tool="users" id="userTable">
      <thead><tr>
        <th class="selection-cell"><input type="checkbox" aria-label="Select all visible users" data-select-all></th>
        <th><button type="button" class="sort-button" data-sort-key="name">User <i class="bi bi-arrow-down-up"></i></button></th>
        <th><button type="button" class="sort-button" data-sort-key="role">Role <i class="bi bi-arrow-down-up"></i></button></th>
        <th><button type="button" class="sort-button" data-sort-key="status">Status <i class="bi bi-arrow-down-up"></i></button></th>
        <th><button type="button" class="sort-button" data-sort-key="login">Last Login <i class="bi bi-arrow-down-up"></i></button></th><th>Actions</th>
      </tr></thead>
      <tbody>
      <?php foreach ($users as $u):
        $isSelf = (int)$u['id'] === (int)$_SESSION['user_id'];
        $words = preg_split('/\s+/', trim($u['full_name']));
        $lastWord = $words ? $words[count($words) - 1] : 'U';
        $userInitials = strtoupper(substr($words[0] ?? 'U', 0, 1) . substr($lastWord, 0, 1));
        $status = $u['is_active'] ? 'active' : 'inactive';
      ?>
        <tr data-row data-search="<?= sanitize(strtolower($u['full_name'] . ' ' . $u['username'] . ' ' . $u['email'])) ?>" data-role="<?= sanitize($u['role']) ?>" data-status="<?= $status ?>" data-sort-name="<?= sanitize(strtolower($u['full_name'])) ?>" data-sort-role="<?= sanitize($u['role']) ?>" data-sort-status="<?= $status ?>" data-sort-login="<?= sanitize($u['last_login'] ?? '') ?>">
          <td class="selection-cell" data-label="Select"><input type="checkbox" name="user_ids[]" value="<?= (int)$u['id'] ?>" form="bulkUserForm" aria-label="Select <?= sanitize($u['full_name']) ?>" data-row-select <?= $isSelf ? 'disabled' : '' ?>></td>
          <td data-label="User"><div class="user-identity"><span class="user-avatar" aria-hidden="true"><?= $userInitials ?></span><span><strong><?= sanitize($u['full_name']) ?></strong><small>@<?= sanitize($u['username']) ?> · <?= sanitize($u['email']) ?></small></span></div></td>
          <td data-label="Role"><span class="role-chip <?= sanitize($u['role']) ?>"><?= $u['role'] === 'admin' ? 'Administrator' : ucfirst(sanitize($u['role'])) ?></span></td>
          <td data-label="Status"><?= statusBadge($status) ?></td>
          <td data-label="Last Login" class="last-login-text"><?= $u['last_login'] ? formatDate($u['last_login'], 'M j, Y g:i A') : '<span class="never-login">Never signed in</span>' ?></td>
          <td data-label="Actions"><details class="row-actions"><summary class="btn-icon" aria-label="Actions for <?= sanitize($u['full_name']) ?>"><i class="bi bi-three-dots"></i></summary><div class="row-actions-menu">
            <a href="<?= BASE_URL ?>/admin/users/edit.php?id=<?= (int)$u['id'] ?>"><i class="bi bi-pencil"></i> Edit details</a>
            <?php if (!$isSelf): ?><a href="<?= BASE_URL ?>/admin/users/reset_password.php?id=<?= (int)$u['id'] ?>"><i class="bi bi-key"></i> Reset password</a><form method="POST" action="<?= BASE_URL ?>/handlers/user_handler.php"><input type="hidden" name="csrf_token" value="<?= csrfToken() ?>"><input type="hidden" name="action" value="toggle"><input type="hidden" name="id" value="<?= (int)$u['id'] ?>"><button type="submit" data-confirm="<?= $u['is_active'] ? 'Deactivate' : 'Activate' ?> this account?"><i class="bi bi-person-<?= $u['is_active'] ? 'dash' : 'check' ?>"></i> <?= $u['is_active'] ? 'Deactivate' : 'Activate' ?></button></form><?php else: ?><span class="current-account"><i class="bi bi-person-check"></i> Current account</span><?php endif; ?>
          </div></details></td>
        </tr>
      <?php endforeach; ?>
        <tr class="table-empty-filter" hidden><td colspan="6"><div class="empty-state empty-state-compact"><div class="empty-icon"><i class="bi bi-search"></i></div><h3>No matching users</h3><p>Try changing the search or filters.</p></div></td></tr>
      </tbody>
    </table>
  </div>
  <div class="table-footer"><span data-table-summary>Showing all users</span><nav class="client-pagination" aria-label="User table pages" data-table-pagination></nav></div>
</div>
<?php require_once dirname(dirname(__DIR__)) . '/includes/footer.php'; ?>
