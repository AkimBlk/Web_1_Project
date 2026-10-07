<?php
session_start();
include 'db_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['comment_id'])) {
    $comment_id = intval($_POST['comment_id']);

    // Vérifier que l'utilisateur est connecté
    if (!isset($_SESSION['login'])) {
        echo json_encode(["success" => false, "error" => "Non autorisé"]);
        exit();
    }

    // Vérifier que l'utilisateur est l'auteur du commentaire
    $sql = "SELECT user FROM comments WHERE id = :comment_id";
    $stmt = $dbh->prepare($sql);
    $stmt->execute(['comment_id' => $comment_id]);
    $comment = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$comment || $comment['user'] !== $_SESSION['login']) {
        echo json_encode(["success" => false, "error" => "Action non autorisée"]);
        exit();
    }

    // Supprimer le commentaire
    $sql = "DELETE FROM comments WHERE id = :comment_id";
    $stmt = $dbh->prepare($sql);
    $stmt->execute(['comment_id' => $comment_id]);

    echo json_encode(["success" => true]);
    exit();
}
?>
