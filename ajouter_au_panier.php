<?php
session_start();

if (!isset($_SESSION['panier'])) {
    $_SESSION['panier'] = []; // Initialise le panier
}

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Vérifie si l'ID est bien un nombre
    if (!is_numeric($id)) {
        header("Location: index.php");
        exit();
    }

    // Ajoute le produit avec quantité
    if (isset($_SESSION['panier'][$id])) {
        $_SESSION['panier'][$id]++; // Augmente la quantité
    } else {
        $_SESSION['panier'][$id] = 1; // Ajoute avec quantité 1
    }
}

header("Location: panier.php");
exit();
?>
