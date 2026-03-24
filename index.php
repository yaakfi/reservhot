<?php
session_start();

$page = isset($_GET['page']) ? $_GET['page'] : 'home';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $_SESSION['flash_msg'] = "Security Violation: Token perlindungan CSRF Anda kadaluwarsa atau tidak valid. Mutasi ditolak!";
        $_SESSION['flash_type'] = "danger";
        header("Location: index.php?page=" . $page);
        exit;
    }
}

require_once __DIR__ . '/views/layout/header.php';

switch ($page) {
    case 'home':
        require_once __DIR__ . '/views/home.php';
        break;
    case 'login':
        require_once __DIR__ . '/views/auth/login.php';
        break;
    case 'logout':
        require_once __DIR__ . '/views/auth/logout.php';
        break;
    case 'booking':
        require_once __DIR__ . '/views/booking.php';
        break;
    case 'admin_dashboard':
        require_once __DIR__ . '/views/admin_dashboard.php';
        break;
    case 'admin_rooms':
        require_once __DIR__ . '/views/admin_rooms.php';
        break;
    case 'admin_room_types':
        require_once __DIR__ . '/views/admin_room_types.php';
        break;
    case 'my_bookings':
        require_once __DIR__ . '/views/my_bookings.php';
        break;
    case 'admin_bookings':
        require_once __DIR__ . '/views/admin_bookings.php';
        break;
    default:
        echo "<h3>404 Halaman Tidak Ditemukan</h3>";
        break;
}

require_once __DIR__ . '/views/layout/footer.php';
