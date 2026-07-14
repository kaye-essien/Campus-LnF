<?php
session_start();
require '../includes/db.php';
require '../includes/auth.php';
requireLogin();

$img_id  = intval($_GET['id'] ?? 0);
$item_id = intval($_GET['item_id'] ?? 0);

$stmt = $conn->prepare("SELECT ii.image_path FROM item_images ii JOIN items i ON ii.item_id = i.id WHERE ii.id = ? AND i.user_id = ?");
$stmt->bind_param("ii", $img_id, $_SESSION['user_id']);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($row) {
    $file = '../uploads/' . $row['image_path'];
    if (file_exists($file)) unlink($file);
    $conn->query("DELETE FROM item_images WHERE id=$img_id");
}
header("Location: /pages/edit.php?id=$item_id");
exit();
