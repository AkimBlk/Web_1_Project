<?php
session_start();

// Vérifie si le panier existe dans la session
if (isset($_SESSION['panier'])) {
    // Vider le panier
    unset($_SESSION['panier']);
}

// Redirection vers la page de remerciement
header('Location: remerciement.php');
exit();
?>
