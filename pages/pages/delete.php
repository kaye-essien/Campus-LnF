<?php
session_start();
require '../includes/db.php';
require '../includes/auth.php';
requireLogin();

$id = intval($_GET['id'] ?? 0);
if (!$id) { header("Location: /pages/dashboard.php"); exit(); }

$stmt = $conn->prepare("SELECT * FROM items WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $id, $_SESSION['user_id']);
$stmt->execute();
$item = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$item && !isAdmin()) {
    header("Location: /pages/dashboard.php");
    exit();
}

// delete image file if exists
if ($item['image_path'] && file_exists('../uploads/' . $item['image_path'])) {
    unlink('../uploads/' . $item['image_path']);
}

$conn->query("DELETE FROM items WHERE id=$id");
header("Location: /pages/dashboard.php");
exit();
