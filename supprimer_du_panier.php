<?php
session_start();

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    if (isset($_SESSION['panier'][$id])) {
        unset($_SESSION['panier'][$id]); // Supprime l'élément
    }
}

// Redirige vers le panier
header("Location: panier.php");
exit();
?>
