<?php
session_start();
include 'db_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['comment'], $_POST['pc_id'])) {
    $comment = trim($_POST['comment']);
    $pc_id = intval($_POST['pc_id']); // Sécurise l'ID du PC
    $user = $_SESSION['login'] ?? 'Anonyme';

    if (!empty($comment)) {
        $sql = "INSERT INTO comments (pc_id, user, comment, created_at) VALUES (:pc_id, :user, :comment, NOW())";
        $stmt = $dbh->prepare($sql);
        $stmt->execute(['pc_id' => $pc_id, 'user' => $user, 'comment' => $comment]);
    }
}

header("Location: details.php?id=" . $pc_id);
exit();
?>
