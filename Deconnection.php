<?php
session_start();
$_SESSION = array();
session_destroy();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>ReadyPC</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.1/css/all.min.css">
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Ton CSS personnalisé -->
  <link rel="stylesheet" href="css/style.css?v=1">
  <link rel="stylesheet" href="css/style_deco.css?v=1">
  <!-- Favicon -->
  <link rel="icon" type="image/png" href="Image/logo.png">


<!-- BARRE DE RECHERCHE -->
<div class="search-dropdown" id="searchDropdown">
  <form class="d-flex justify-content-center" action="recherche.php" method="get">
    <input id="searchInput" class="form-control form-control-lg me-2" type="search" name="q" placeholder="Rechercher un PC..." aria-label="Search">
    <button type="submit" class="search-btn">Rechercher</button>
  </form>
  
  <!-- Suggestions dynamiques -->
  <ul id="suggestionsList" class="list-group position-absolute w-100 mt-1" style="z-index: 1000;"></ul>
</div>
</div>

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
  <div class="container">
    <!-- Logo + nom -->
    <a class="navbar-brand d-flex align-items-center" href="index.php">
      <img src="Image/logo.png" alt="Logo ReadyPC" width="40" height="40" class="me-2">
      <strong>ReadyPC</strong>
    </a>
    
    <!-- Bouton hamburger -->
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent"
    aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
    <span class="navbar-toggler-icon"></span>
  </button>
  
  <!-- Contenu navbar -->
  <div class="collapse navbar-collapse" id="navbarContent">
    <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-lg-center">
      <li class="nav-item"><a class="nav-link" href="index.php">Accueil</a></li>
      <li class="nav-item"><a class="nav-link" href="https://www.youtube.com/watch?v=dQw4w9WgXcQ" target="_blank">À propos</a></li> 
      <?php

            echo '<li class="nav-item"><a class="nav-link" href="login.php">Se connecter</a></li>';
            echo '<li class="nav-item"><a class="nav-link" href="signup.php">S\'inscrire</a></li>';
          
          ?>
            </ul>
          </div>
          </div>
          </nav>     
    </head>
    <body style="margin: 0; background-color: #2b2b34; color: white; font-family: 'LEMONMILK-Regular', sans-serif; min-height: 100vh; display: flex; flex-direction: column;">

<!-- Contenu principal centré -->
<div class="logout-container" style="flex: 1; display: flex; flex-direction: column; justify-content: center; align-items: center; text-align: center;">
  <a href="index.php">
    <img src="Image/logout.jpg" alt="fondEcran" style="width: 400px; height: auto; margin-bottom: 20px;" />
  </a>
  <p class="logout-message" style="font-size: 24px; font-weight: bold;">Vous êtes maintenant déconnecté.</p>
</div>

<!-- Pied de page -->
<footer class="bg-dark text-white text-center py-3">
  <p>&copy; 2025 ReadyPC. Tous droits réservés.</p>
</footer>

<!-- Scripts Bootstrap et JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>