<?php
// Get Current Page Name
$current_page = basename($_SERVER['PHP_SELF']);

// For submenu active check
$is_acctive_zones = ['list_zones.php', 'list_subzones.php'];
$is_acctive_routers = ['list_routers.php', 'list_servers.php'];
$package_pages = ['list_packages.php', 'list_profiles.php'];
$is_package_active = in_array($current_page, $package_pages);
$is_active_routers = in_array($current_page, $is_acctive_routers);
$is_active_zones = in_array($current_page, $is_acctive_zones);

?>

<!-- ✅ Sidebar Start -->
<!-- <nav id="sidebarMenu" class="bg-light border" style="width: 250px; height: 100vh; position: fixed; top: 22; left: 0; overflow-y: auto;"> -->
<nav id="sidebarMenu" class="bg-light d-none d-lg-block border" style="width: 250px; position: fixed; top: 56px; bottom: 0; left: 0; overflow-y: auto; z-index: 1030;">
    <div class="list-group list-group-flush">
        <a href="/admin/dashboard.php" class="list-group-item list-group-item-action <?= ($current_page == 'dashboard.php') ? 'active' : '' ?>">Dashboard</a>
        <a href="#" class="list-group-item list-group-item-action">Configuration</a>

        <a href="/clients/add_client.php" class="list-group-item list-group-item-action <?= ($current_page == 'add_client.php') ? 'active' : '' ?>">Add Client</a>
        <a href="/clients/list_clients.php" class="list-group-item list-group-item-action <?= ($current_page == 'list_clients.php') ? 'active' : '' ?>">Client List<a>
        <!-- ✅ Packages Menu with Submenu -->
        <div class="list-group-item p-0">
            <button class="btn btn-toggle align-items-center rounded w-100 text-start ps-3 <?= ($is_active_routers) ? '' : 'collapsed' ?>" 
                data-bs-toggle="collapse" data-bs-target="#routers" 
                aria-expanded="<?= ($is_active_routers) ? 'true' : 'false' ?>">Routers & Servers</button>
            <div class="collapse <?= ($is_active_routers) ? 'show' : '' ?>" id="routers">
                <a href="/routers/list_routers.php" class="list-group-item list-group-item-action ps-5 <?= ($current_page == 'list_routers.php') ? 'active' : '' ?>">Routers</a>
                <a href="/servers/list_servers.php" class="list-group-item list-group-item-action ps-5 <?= ($current_page == 'list_servers.php') ? 'active' : '' ?>">Servers</a>
            </div>
        </div>

        <!-- ✅ Packages Menu with Submenu -->
        <div class="list-group-item p-0">
            <button class="btn btn-toggle align-items-center rounded w-100 text-start ps-3 <?= ($is_package_active) ? '' : 'collapsed' ?>" 
                data-bs-toggle="collapse" data-bs-target="#packagesSubmenu" 
                aria-expanded="<?= ($is_package_active) ? 'true' : 'false' ?>">Packages</button>
            <div class="collapse <?= ($is_package_active) ? 'show' : '' ?>" id="packagesSubmenu">
                <a href="/packages/list_packages.php" class="list-group-item list-group-item-action ps-5 <?= ($current_page == 'list_packages.php') ? 'active' : '' ?>">Package List</a>
                <a href="/profile/list_profiles.php" class="list-group-item list-group-item-action ps-5 <?= ($current_page == 'list_profiles.php') ? 'active' : '' ?>">Profiles</a>
            </div>
        </div>
<!-- ✅ Packages Menu with Submenu -->
<div class="list-group-item p-0">
            <button class="btn btn-toggle align-items-center rounded w-100 text-start ps-3 <?= ($is_active_zones) ? '' : 'collapsed' ?>" 
                data-bs-toggle="collapse" data-bs-target="#zones" 
                aria-expanded="<?= ($is_active_zones) ? 'true' : 'false' ?>">Zone & Sub Zone</button>
            <div class="collapse <?= ($is_active_zones) ? 'show' : '' ?>" id="zones">
                <a href="/network/list_zones.php" class="list-group-item list-group-item-action ps-5 <?= ($current_page == 'list_zones.php') ? 'active' : '' ?>">
                    Zone</a>
                <a href="/network/list_subzones.php" class="list-group-item list-group-item-action ps-5 <?= ($current_page == 'list_subzones.php') ? 'active' : '' ?>">
                    Sub Zone</a>
            </div>
        </div>
<!-- ✅ Packages Menu with Submenu -->
        <a href="/employees/list_employees.php" class="list-group-item list-group-item-action <?= ($current_page == 'list_employees.php') ? 'active' : '' ?>">
            Employees List</a>
        <a href="/clients/online_users.php" class="list-group-item list-group-item-action <?= ($current_page == 'online_users.php') ? 'active' : '' ?>">
            Online Users</a>
        <a href="/users/transfer_users.php" class="list-group-item list-group-item-action <?= ($current_page == 'transfer_users.php') ? 'active' : '' ?>">
            Transfer Users</a>
        <a href="/clients/import_pppoe_users.php" class="list-group-item list-group-item-action <?= ($current_page == 'import_pppoe_users.php') ? 'active' : '' ?>">Users Import Database</a>
        <a href="/clients/billing_summary.php" class="list-group-item list-group-item-action <?= ($current_page == 'billing_summary.php') ? 'active' : '' ?>">Collected Bills</a>
        <a href="#" class="list-group-item list-group-item-action">Inventory</a>
        <a href="#" class="list-group-item list-group-item-action">Assets</a>
        <a href="#" class="list-group-item list-group-item-action">Accounting</a>
        <a href="#" class="list-group-item list-group-item-action">Report</a>
        <a href="#" class="list-group-item list-group-item-action">SMS Service</a>
        <a href="/admin/logout.php" class="list-group-item list-group-item-action text-danger">
            Logout
        </a>
    </div>
</nav>



<nav id="mobileSidebar" class="bg-light d-lg-none position-fixed top-0 start-0 vh-100 p-3 border" style="width: 250px; z-index: 1050; display: none;">
    <button class="btn btn-danger mb-3" onclick="toggleSidebar()">Close</button>
    <!-- duplicate sidebar links -->
    <div class="list-group list-group-flush">

<a href="/admin/dashboard.php" class="list-group-item list-group-item-action <?= ($current_page == 'dashboard.php') ? 'active' : '' ?>">
    Dashboard
</a>

<a href="#" class="list-group-item list-group-item-action">
    Configuration
</a>

<a href="/clients/add_client.php" class="list-group-item list-group-item-action <?= ($current_page == 'add_client.php') ? 'active' : '' ?>">
    Add Client
</a>

<a href="/clients/list_clients.php" class="list-group-item list-group-item-action <?= ($current_page == 'list_clients.php') ? 'active' : '' ?>">
    Client List
</a>

<!-- ✅ Packages Menu with Submenu -->
<div class="list-group-item p-0">
    <button class="btn btn-toggle align-items-center rounded w-100 text-start ps-3 <?= ($is_active_routers) ? '' : 'collapsed' ?>" 
        data-bs-toggle="collapse" data-bs-target="#routers" 
        aria-expanded="<?= ($is_active_routers) ? 'true' : 'false' ?>">
        Routers & Servers
    </button>
    <div class="collapse <?= ($is_active_routers) ? 'show' : '' ?>" id="routers">
        <a href="/routers/list_routers.php" class="list-group-item list-group-item-action ps-5 <?= ($current_page == 'list_routers.php') ? 'active' : '' ?>">
            Routers
        </a>
        <a href="/servers/list_servers.php" class="list-group-item list-group-item-action ps-5 <?= ($current_page == 'list_servers.php') ? 'active' : '' ?>">
            Servers
        </a>
    </div>
</div>

<!-- ✅ Packages Menu with Submenu -->
<div class="list-group-item p-0">
    <button class="btn btn-toggle align-items-center rounded w-100 text-start ps-3 <?= ($is_package_active) ? '' : 'collapsed' ?>" 
        data-bs-toggle="collapse" data-bs-target="#packagesSubmenu" 
        aria-expanded="<?= ($is_package_active) ? 'true' : 'false' ?>">
        Packages
    </button>
    <div class="collapse <?= ($is_package_active) ? 'show' : '' ?>" id="packagesSubmenu">
        <a href="/packages/list_packages.php" class="list-group-item list-group-item-action ps-5 <?= ($current_page == 'list_packages.php') ? 'active' : '' ?>">
            Package List
        </a>
        <a href="/profile/list_profiles.php" class="list-group-item list-group-item-action ps-5 <?= ($current_page == 'list_profiles.php') ? 'active' : '' ?>">
            Profiles
        </a>
    </div>
</div>
<!-- ✅ Packages Menu with Submenu -->
<div class="list-group-item p-0">
    <button class="btn btn-toggle align-items-center rounded w-100 text-start ps-3 <?= ($is_active_zones) ? '' : 'collapsed' ?>" 
        data-bs-toggle="collapse" data-bs-target="#zones" 
        aria-expanded="<?= ($is_active_zones) ? 'true' : 'false' ?>">
        Zone & Sub Zone
    </button>
    <div class="collapse <?= ($is_active_zones) ? 'show' : '' ?>" id="zones">
        <a href="/network/list_zones.php" class="list-group-item list-group-item-action ps-5 <?= ($current_page == 'list_zones.php') ? 'active' : '' ?>">
            Zone
        </a>
        <a href="/network/list_subzones.php" class="list-group-item list-group-item-action ps-5 <?= ($current_page == 'list_subzones.php') ? 'active' : '' ?>">
            Sub Zone
        </a>
    </div>
</div>
<!-- ✅ Packages Menu with Submenu -->
<a href="/employees/list_employees.php" class="list-group-item list-group-item-action <?= ($current_page == 'list_employees.php') ? 'active' : '' ?>">
    Employees List
</a>

<a href="/clients/online_users.php" class="list-group-item list-group-item-action <?= ($current_page == 'online_users.php') ? 'active' : '' ?>">
    Online Users
</a>

<a href="/users/transfer_users.php" class="list-group-item list-group-item-action <?= ($current_page == 'transfer_users.php') ? 'active' : '' ?>">
    Transfer Users
</a>
<a href="/clients/import_pppoe_users.php" class="list-group-item list-group-item-action <?= ($current_page == 'import_pppoe_users.php') ? 'active' : '' ?>">Users Import Database</a>

<a href="/clients/billing_summary.php" class="list-group-item list-group-item-action <?= ($current_page == 'billing_summary.php') ? 'active' : '' ?>">Collected Bills</a>
<a href="#" class="list-group-item list-group-item-action">Inventory</a>
<a href="#" class="list-group-item list-group-item-action">Assets</a>
<a href="#" class="list-group-item list-group-item-action">Accounting</a>
<a href="#" class="list-group-item list-group-item-action">Report</a>
<a href="#" class="list-group-item list-group-item-action">SMS Service</a>

<a href="/admin/logout.php" class="list-group-item list-group-item-action text-danger">
    Logout
</a>

</div>
</nav>

<!-- ✅ Sidebar End -->

<!-- ✅ Main Content Start -->
<!-- <main class="flex-grow-1 p-4" style="margin-left: 250px;"> -->
<main class="flex-grow-1 p-4" id="mainContent" style="margin-left: 250px;">
