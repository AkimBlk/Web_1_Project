<?php
session_start();
require_once('db_connection.php');
require_once('fonctionPP.php');
$search = $_GET['q'] ?? '';

if (empty($search)) {
  header('Location: index.php');
  exit;
}

// ✅ Récupération de l'image de profil
$profile_pictureee = isset($_SESSION['login']) ? PP($dbh, $_SESSION['login']) : null;

$sql = "SELECT * FROM products 
        WHERE name LIKE :search 
           OR carte_graphique LIKE :search 
           OR processeur LIKE :search 
           OR ram LIKE :search 
           OR ssd LIKE :search 
           OR CAST(prix AS CHAR) LIKE :search";
$stmt = $dbh->prepare($sql);
$stmt->execute(['search' => "%$search%"]);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>ReadyPC</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.1/css/all.min.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="css/style.css?v=1">
  <link rel="stylesheet" href="css/recherche.css?v=1">
  <link rel="icon" type="image/png" href="Image/logo.png">

  <style>
  <?php
  foreach ($products as $product) {
      $id = htmlspecialchars($product['id']);
      $imagePath = htmlspecialchars($product['url_image']);
      echo ".pc-box.pc$id::before {
          background-image: url('$imagePath');
      }\n";
  }
  ?>
  </style>
</head>
<body>


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


  <!-- Liste de suggestions dynamiques -->
  <ul class="list-group position-absolute w-50 mx-auto mt-2 z-3" id="suggestionsList" style="left: 25%; max-height: 300px; overflow-y: auto;"></ul>
</div>


<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
  <div class="container">
    <a class="navbar-brand d-flex align-items-center" href="index.php">
      <img src="Image/logo.png" alt="Logo ReadyPC" width="40" height="40" class="me-2">
      <strong>ReadyPC</strong>
    </a>
    
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent"
      aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    
    <div class="collapse navbar-collapse" id="navbarContent">
      <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-lg-center">
        <li class="nav-item"><a class="nav-link" href="index.php">Accueil</a></li>
        <li class="nav-item"><a class="nav-link" href="https://www.youtube.com/watch?v=dQw4w9WgXcQ" target="_blank">À propos</a></li>
        <li class="nav-item"><a class="nav-link" href="panier.php">Votre panier</a></li>

        <?php
        if (isset($_SESSION['access_level']) && ($_SESSION['access_level'] == 2 || $_SESSION['access_level'] == 1)) {
          if ($_SESSION['access_level'] == 1) {
              echo '<li class="nav-item"><a class="nav-link" href="Gestion_PC.php">Gestion PC</a></li>';
          }

          echo '<li class="nav-item"><a class="nav-link" href="Deconnection.php">Se déconnecter</a></li>';
          echo '<li class="nav-item text-primary fw-bold ms-2">'.htmlspecialchars($_SESSION['login']) . '</li>';


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

<div class="container py-4">
  <h2>Résultats pour « <?= htmlspecialchars($search) ?> » :</h2>

  <?php if (empty($results)): ?>
    <p class="text-danger">Aucun produit trouvé.</p>
  <?php else: ?>
    <div class="row">
      <?php foreach ($results as $product): ?>
        <div class="col-md-4 mb-4">
          <div class="pc-box card h-100">
            <img src="<?= htmlspecialchars($product['url_image']) ?>" class="card-img-top" alt="...">
            <div class="card-body">
              <h5 class="card-title"><?= htmlspecialchars($product['name']) ?></h5>
              <p class="card-text">
                Carte graphique : <?= htmlspecialchars($product['carte_graphique']) ?><br>
                Processeur : <?= htmlspecialchars($product['processeur']) ?><br>
                RAM : <?= htmlspecialchars($product['ram']) ?><br>
                Stockage : <?= htmlspecialchars($product['ssd']) ?><br>
                <strong><span class="price"><?= number_format($product['prix'], 2, ',', ' ') ?> €</span></strong>
              </p>
              <a href="details.php?id=<?= $product['id'] ?>" class="btn btn-outline-primary">Voir détails</a>
              <a href="ajouter_panier.php?id=<?= $product['id'] ?>" class="btn-ajouter-panier-index">Ajouter au panier</a>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

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
