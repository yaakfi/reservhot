<?php
require_once __DIR__ . '/../../classes/Auth.php';

$auth = new Auth();
$auth->logout();

$_SESSION['flash_msg'] = "Anda berhasil keluar (logout).";
$_SESSION['flash_type'] = "info";

header("Location: index.php?page=home");
exit;
