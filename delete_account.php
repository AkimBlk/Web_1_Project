<?php
session_start();
require_once('db_connection.php');

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit();
}

// Récupérer le login de l'utilisateur connecté
$login = $_SESSION['login'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // Supprimer l'utilisateur de la base de données
        $deleteQuery = "DELETE FROM users WHERE login = :login";
        $stmt = $dbh->prepare($deleteQuery);
        $stmt->bindParam(':login', $login);
        $stmt->execute();

        // Détruire la session
        session_destroy();

        // Rediriger vers la page d'accueil
        header("Location: index.php");
        exit();
    } catch (Exception $e) {
        echo "Erreur lors de la suppression du compte : " . $e->getMessage();
    }
} else {
    // Redirection si l'accès direct au fichier
    header("Location: profil.php");
    exit();
}
?>
