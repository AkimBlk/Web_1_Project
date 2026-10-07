<?php
session_start();
include 'db_connection.php'; 

if (!isset($_SESSION['login'])) {
    die("Vous devez être connecté pour commenter.");
}

if (isset($_POST['comment'], $_POST['pc_id']) && !empty(trim($_POST['comment']))) {
    $comment = trim($_POST['comment']);
    $pc_id = intval($_POST['pc_id']);
    $user = $_SESSION['login'];

    $sql = "INSERT INTO comments (pc_id, user, comment) VALUES (:pc_id, :user, :comment)";
    $stmt = $dbh->prepare($sql);
    $stmt->execute(['pc_id' => $pc_id, 'user' => $user, 'comment' => $comment]);

    // Redirige vers la page détails après l'ajout du commentaire
    header("Location: details.php?id=$pc_id");
    exit;
} else {
    die("Commentaire invalide.");
}
?>
