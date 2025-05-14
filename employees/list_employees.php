<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/header.php';
require_once '../includes/sidebar.php';

// Fetch the current role and user ID from the session
$role_filter = $_POST['role'] ?? ''; // Role filter from POST request
$current_user_id = $_SESSION['id'] ?? 0; // Current logged-in user's ID
$current_role = $_SESSION['role'] ?? ''; // Current logged-in user's role

// Initialize the WHERE clause for the SQL query
$where = [];

// Add role filter if specified
if ($role_filter) {
    $where[] = "role = '" . $conn->real_escape_string($role_filter) . "'";
}

// If the current user is not an admin, filter by their own ID
if ($current_role !== 'admin') {
    $where[] = "id = $current_user_id";
}

// Combine WHERE conditions for the SQL query
$where_sql = count($where) ? 'WHERE ' . implode(' AND ', $where) : '';

// SQL query to fetch employees based on the filters
$sql = "SELECT * FROM employees $where_sql ORDER BY id DESC";
$result = $conn->query($sql);
?>
<style>
    /* Optional scrollbar visibility enhancement */
.table-responsive {
    scrollbar-width: thin; /* Firefox */
}

.table-responsive::-webkit-scrollbar {
    height: 8px;
}

.table-responsive::-webkit-scrollbar-thumb {
    background: #aaa;
    border-radius: 4px;
}

</style>
<div class="container-fluid p-4">
    <h3>Employees</h3>

    <!-- Swipe hint on small screens -->
    <div class="d-block d-md-none text-muted small mb-2">
        👉 Swipe left/right to view the full table.
    </div>

    <!-- Role filter form -->
    <form method="POST">
        <div class="row">
            <div class="col-12 col-sm-6 col-md-3 mb-3">
                <label for="role" class="form-label">Filter by Role</label>
                <select name="role" class="form-select" onchange="this.form.submit()">
                    <option value="">-- All Roles --</option>
                    <option value="admin" <?= $role_filter === 'admin' ? 'selected' : '' ?>>Admin</option>
                    <option value="editor" <?= $role_filter === 'editor' ? 'selected' : '' ?>>Editor</option>
                    <option value="support" <?= $role_filter === 'support' ? 'selected' : '' ?>>Support</option>
                </select>
            </div>
        </div>
    </form>

    <!-- Table with horizontal scroll -->

<!-- Table with horizontal scroll and visible scrollbar -->
<div class="table-responsive rounded-lg shadow ">
    <table class="table table-bordered align-middle text-nowrap w-full">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Phone</th>
                <th>Email</th>
                <th>Role</th>
                <th class="d-none d-md-table-cell">Created At</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <!-- your PHP loop here -->
            <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
    <td><?= $row['id'] ?></td>
    <td><?= htmlspecialchars($row['name']) ?></td>
    <td><?= htmlspecialchars($row['phone']) ?></td>
    <td><?= htmlspecialchars($row['email']) ?></td>
    <td><span class="badge bg-info text-dark"><?= ucfirst($row['role']) ?></span></td>
    <td class="d-none d-md-table-cell"><?= $row['created_at'] ?></td>
    <td>
        <a href="edit_employee.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-info">Edit</a>
        <?php if ($current_role === 'admin'): ?>
            <a href="delete_employee.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</a>
        <?php endif; ?>
    </td>
</tr>

                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="7" class="text-center">No employees found.</td></tr>
                <?php endif; ?>
        </tbody>
    </table>
</div>



<?php require_once '../includes/footer.php'; ?>
