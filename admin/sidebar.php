<?php
// Admin Sidebar Component
$current_page = basename($_SERVER['PHP_SELF']);
?>
<aside class="sidebar">
    <div class="sidebar-logo">
        <span>⚡</span> TechGear <small style="font-size:11px;color:var(--muted);margin-left:4px;">Admin</small>
    </div>
    <nav class="sidebar-nav">
        <div class="nav-label">Main</div>
        <a href="index.php" class="nav-item <?= $current_page === 'index.php' ? 'active' : '' ?>">
            <i class='bx bxs-dashboard'></i> Dashboard
        </a>
        <a href="orders.php" class="nav-item <?= $current_page === 'orders.php' ? 'active' : '' ?>">
            <i class='bx bxs-receipt'></i> Orders
            <?php if(isset($pending_orders) && $pending_orders > 0): ?>
                <span class="badge-nav" style="background:rgba(244,63,94,.2);color:#fb7185;"><?= $pending_orders ?></span>
            <?php endif; ?>
        </a>

        <div class="nav-label">Management</div>
        <a href="products.php" class="nav-item <?= $current_page === 'products.php' ? 'active' : '' ?>">
            <i class='bx bxs-package'></i> Products
        </a>
        <a href="categories.php" class="nav-item <?= $current_page === 'categories.php' ? 'active' : '' ?>">
            <i class='bx bxs-category'></i> Categories
        </a>
        <a href="users.php" class="nav-item <?= $current_page === 'users.php' ? 'active' : '' ?>">
            <i class='bx bxs-group'></i> Users
        </a>

        <div class="nav-label">Other</div>
        <a href="../index.php" class="nav-item">
            <i class='bx bx-store'></i> View Store
        </a>
        <a href="../logout.php" class="nav-item danger">
            <i class='bx bx-log-out'></i> Logout
        </a>
    </nav>
    <div class="sidebar-footer">
        <div class="admin-card">
            <i class='bx bxs-user-circle'></i>
            <div class="info">
                <strong><?= htmlspecialchars($_SESSION['username'] ?? 'Admin') ?></strong>
                <small>Administrator</small>
            </div>
            <div class="dot"></div>
        </div>
    </div>
</aside>
