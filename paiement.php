<?php
session_start();
require_once('db_connection.php');
require_once('fonctionPP.php');
// ✅ Récupération de l'image de profil
$profile_pictureee = isset($_SESSION['login']) ? PP($dbh, $_SESSION['login']) : null;
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>ReadyPC</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.1/css/all.min.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="css/style.css">
  <link rel="stylesheet" href="css/paiement.css">
  <link rel="icon" type="image/png" href="Image/logo.png">

</head>
<body>
    

<!-- BARRE DE RECHERCHE -->
<div class="search-dropdown" id="searchDropdown">
  <form class="d-flex justify-content-center" action="recherche.php" method="get">
    <input id="searchInput" class="form-control form-control-lg me-2" type="search" name="q" placeholder="Rechercher un PC..." aria-label="Search">
    <button type="submit" class="search-btn">Rechercher</button>
  </form>
  
  <!-- Suggestions dynamiques -->
  <ul id="suggestionsList" class="list-group position-center w-100 mt-1" style="z-index: 1000;"></ul>
</div>
</div>


<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
  <div class="container">
    <a class="navbar-brand d-flex align-items-center" href="index.php">
      <img src="Image/logo.png" alt="Logo ReadyPC" width="40" height="40" class="me-2">
      <strong>ReadyPC</strong>
    </a>
    
    <!-- Bouton hamburger -->
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent"
    aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
    <span class="navbar-toggler-icon"></span>
  </button>
    
    <div class="collapse navbar-collapse" id="navbarContent">
      <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-lg-center">
        <li class="nav-item"><a class="nav-link" href="index.php">Accueil</a></li>
        <li class="nav-item"><a class="nav-link" href="https://www.youtube.com/watch?v=dQw4w9WgXcQ">À propos</a></li>
        <li class="nav-item"><a class="nav-link" href="panier.php">Votre panier</a></li>

        <?php
        if (isset($_SESSION['access_level']) && ($_SESSION['access_level'] == 2 || $_SESSION['access_level'] == 1)) {
          if ($_SESSION['access_level'] == 1) {
              echo '<li class="nav-item"><a class="nav-link" href="Gestion_PC.php">Gestion PC</a></li>';
          }

          echo '<li class="nav-item"><a class="nav-link" href="Deconnection.php">Se déconnecter</a></li>';
          echo '<li class="nav-item text-primary fw-bold ms-2">Bienvenue ' . htmlspecialchars($_SESSION['login']) . '</li>';


          $avatar = !empty($profile_pictureee) ? $profile_pictureee : 'picture_uploads/default.jpg';

          echo '<li class="nav-item">
                  <a href="profile.php">
                    <img class="rounded-circle ms-3" src="' . htmlspecialchars($avatar) . '" alt="Photo de profil" width="40" height="40">
                  </a>
                </li>';
        } else {
          echo '<li class="nav-item"><a class="nav-link" href="login.php">Se connecter</a></li>';
          echo '<li class="nav-item"><a class="nav-link" href="signup.php">S\'inscrire</a></li>';
        }
        ?>

        <li class="nav-item">
          <a class="nav-link" href="#" id="toggleSearch"><i class="fas fa-search fa-lg"></i></a>
        </li>
      </ul>
    </div>
  </div>
</nav>

<title>Paiement</title>
<div class="container mt-5">
  <h2 class="mb-4">Paiement sécurisé</h2>
  <form id="form-paiement">
    <div class="mb-3">
      <label for="nom" class="form-label">Nom sur la carte</label>
      <input type="text" class="form-control" id="nom" name="nom" required>
    </div>
    <div class="mb-3">
      <label for="numero" class="form-label">Numéro de carte</label>
      <input type="text" class="form-control" id="numero" name="numero" maxlength="16" required>
    </div>
    <div class="mb-3 row">
      <div class="col">
        <label for="expiration" class="form-label">Date d'expiration</label>
        <input type="text" class="form-control" id="expiration" name="expiration" placeholder="MM/AA" required>
      </div>
      <div class="col">
        <label for="cvc" class="form-label">CVC</label>
        <input type="text" class="form-control" id="cvc" name="cvc" maxlength="4" required>
      </div>
    </div>
    <a href="acheter.php" class="btn btn-success" id="btn-payer">Payer</a>
  </form>
</div>

<!-- Modal de champs requis -->
<div class="modal fade" id="fieldsModal" tabindex="-1" aria-labelledby="fieldsModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="fieldsModalLabel">Champs manquants</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
      </div>
      <div class="modal-body">
        <p>Veuillez remplir tous les champs du formulaire avant de continuer.</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
      </div>
    </div>
  </div>
</div>

<!-- JS de validation -->
<script>
  const form = document.getElementById('form-paiement');
  const payerBtn = document.getElementById('btn-payer');

  payerBtn.addEventListener('click', function (e) {
    const inputs = form.querySelectorAll('input');
    let allFilled = true;

    inputs.forEach(input => {
      if (input.value.trim() === '') {
        allFilled = false;
      }
    });

    if (!allFilled) {
      e.preventDefault(); // Empêche le lien de fonctionner
      const modal = new bootstrap.Modal(document.getElementById('fieldsModal'));
      modal.show(); // Affiche le modal Bootstrap
    }
  });
</script>


<!-- Bootstrap JS (nécessaire pour le menu hamburger) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>


<!--script pour la recherche-->
<script>
  document.getElementById("toggleSearch").addEventListener("click", function (e) {
    e.preventDefault();
    document.getElementById("searchDropdown").classList.toggle("active");
  });

  document.addEventListener("keydown", function (e) {
    if (e.key === "Escape") {
      document.getElementById("searchDropdown").classList.remove("active");
    }
  });

  document.addEventListener("DOMContentLoaded", function () {
    const input = document.getElementById("searchInput");
    const suggestionsList = document.getElementById("suggestionsList");

    input.addEventListener("input", function () {
      const query = this.value.trim();
      if (query.length === 0) {
        suggestionsList.innerHTML = "";
        return;
      }

    fetch("search_suggestions.php?query=" + encodeURIComponent(query))
      .then(response => response.json())
      .then(data => {
        suggestionsList.innerHTML = "";
        data.forEach(item => {
          const li = document.createElement("li");
          li.classList.add("list-group-item", "list-group-item-action", "d-flex", "align-items-center", "gap-3");
          li.dataset.id = item.id;

          const img = document.createElement("img");
          img.src = item.url_image;
          img.alt = item.name;
          img.style.width = "50px";
          img.style.height = "50px";
          img.style.objectFit = "cover";

          const span = document.createElement("span");
          span.textContent = item.name;

          li.appendChild(img);
          li.appendChild(span);

          li.addEventListener("click", function () {
            window.location.href = "details.php?id=" + this.dataset.id;
          });

          suggestionsList.appendChild(li);
        });
      });
  });

  // Cacher les suggestions si clic à l'extérieur
  document.addEventListener("click", function (e) {
    if (!document.getElementById("searchDropdown").contains(e.target)) {
      suggestionsList.innerHTML = "";
    }
  });
});
</script>
</body>
</html>
