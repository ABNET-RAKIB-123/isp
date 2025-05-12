<?php
session_start();
$role = $_SESSION['role'] ?? '';
$employee_id = $_SESSION['employee_id'] ?? 0;

require_once '../includes/db.php';
require_once '../includes/header.php';
require_once '../includes/sidebar.php';
?>

<div class="container-fluid p-4">
    <h2 class="mb-4">Clients List</h2>

    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success"><?= htmlspecialchars($_GET['success']) ?></div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <table class="table table-bordered table-striped align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Mobile</th>
                        <th>Username (PPPoE)</th>
                        <th>Billing Status</th>
                        <th>Paybil</th>
                        <th>Status</th>
                        <th>Enable & Disable</th>
                        <th>Delete</th>
                        <th>View</th>
                        <th>Edit</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $clients = $conn->query("
                        SELECT 
                            c.id AS client_id, 
                            c.customer_name, 
                            ci.mobile_number, 
                            si.username, 
                            si.status, 
                            si.billing_status
                        FROM clients c
                        JOIN contact_information ci ON c.id = ci.client_id
                        JOIN service_information si ON c.id = si.client_id
                        ORDER BY c.id DESC
                    ");

                    if ($clients->num_rows > 0):
                        $i = 1;
                        while ($client = $clients->fetch_assoc()):
                    ?>
                        <tr>
                            <td><?= $i++ ?></td>
                            <td><?= htmlspecialchars($client['customer_name'] ?? '') ?></td>
                            <td><?= htmlspecialchars($client['mobile_number'] ?? '') ?></td>
                            <td><?= htmlspecialchars($client['username'] ?? '') ?></td>

                            <td>
                                <?php if ($client['billing_status'] === 'paid'): ?>
                                    <span class="badge bg-success">Paid</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Unpaid</span>
                                <?php endif; ?>
                            </td>
                            <td>
                            <a href="pay_bill.php?id=<?= $client['client_id'] ?>" 
                                class="btn btn-sm btn-success d-inline-flex align-items-center">
                                    <i class="bi bi-currency-dollar me-1"></i> Pay Bill
                                </a>
                                <a href="bill_receive.php?id=<?= $client['client_id'] ?>" class="btn btn-primary btn-sm">Pay</a>

                                </td>

                                <td>
                                <?php if ($client['status'] === 'active'): ?>
                                    <span class="badge bg-success">Active</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Inactive</span>
                                <?php endif; ?>
                                </td>

                                <td>
                                <?php if ($client['status'] === 'active'): ?>
                                    <a href="toggle_user_status.php?id=<?= $client['client_id'] ?>&action=disable"
                                       onclick="return confirm('Are you sure you want to disable this user?');"
                                       class="btn btn-sm btn-warning">
                                        <i class="bi bi-x-circle me-1"></i> Disable
                                    </a>
                                    
                                        <?php else: ?>
                                        <a href="toggle_user_status.php?id=<?= $client['client_id'] ?>&action=enable"
                                        onclick="return confirm('Are you sure you want to enable this user?');"
                                        class="btn btn-sm btn-success">
                                            <i class="bi bi-check-circle me-1"></i> Enable
                                        </a>
                                        <?php endif; ?>
                                    </td>

                                    
                                    <td>
                                

                                <a href="delete_client.php?id=<?= $client['client_id'] ?>"
                                   onclick="return confirm('Are you sure you want to delete this client?');"
                                   class="btn btn-sm btn-danger">
                                    <i class="bi bi-trash me-1"></i> Delete
                                </a>
                                </td>
                                <td>
                                <a href="view_client.php?id=<?= $client['client_id'] ?>" class="btn btn-sm btn-info">
                                    <i class="bi bi-eye me-1"></i> View
                                </a>
                            </td>

                            
                            <td class="d-flex gap-2 flex-wrap">
                                <a href="edit_client.php?id=<?= $client['client_id'] ?>" class="btn btn-sm btn-primary">
                                    <i class="bi bi-pencil-square me-1"></i> Edit
                                </a>
                                </td>
                        </tr>
                    <?php endwhile; else: ?>
                        <tr>
                            <td colspan="7" class="text-center">No clients found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    
</div>

<?php require_once '../includes/footer.php'; ?>
