<?php
// 🔧 Affichage des erreurs PHP pour le debug
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once('db_connection.php');
require_once('fonctionPP.php');

// Réinitialiser le marqueur de comparaison si nécessaire
if (isset($_SESSION['comparison_viewed']) && $_SESSION['comparison_viewed'] === true) {
  // Supprimer le marqueur
  unset($_SESSION['comparison_viewed']);
  
  // Assurez-vous que la session de comparaison est vide
  $_SESSION['compare_ids'] = [];
}
// Réinitialiser le marqueur de comparaison si nécessaire
if (isset($_SESSION['comparison_viewed']) && $_SESSION['comparison_viewed'] === true) {
  // Supprimer le marqueur
  unset($_SESSION['comparison_viewed']);
  
  // Assurez-vous que la session de comparaison est vide
  $_SESSION['compare_ids'] = [];
}

// ✅ Récupération de l'image de profil
$profile_pictureee = isset($_SESSION['login']) ? PP($dbh, $_SESSION['login']) : null;

// Construction de la requête SQL avec filtres
$query = 'SELECT * FROM products WHERE 1=1'; // 1=1 permet d'ajouter des conditions facilement
$params = [];

// Filtrage par carte graphique
if (isset($_GET['carte_graphique']) && !empty($_GET['carte_graphique'])) {
    $query .= ' AND carte_graphique LIKE ?';
    $params[] = '%' . $_GET['carte_graphique'] . '%';
}

// Filtrage par processeur
if (isset($_GET['processeur']) && !empty($_GET['processeur'])) {
    $query .= ' AND processeur LIKE ?';
    $params[] = '%' . $_GET['processeur'] . '%';
}

// Filtrage par prix maximum
if (isset($_GET['prix_max']) && !empty($_GET['prix_max']) && is_numeric($_GET['prix_max'])) {
    $query .= ' AND prix <= ?';
    $params[] = $_GET['prix_max'];
}

// Filtrage par quantité de RAM
if (isset($_GET['ram']) && !empty($_GET['ram'])) {
    $query .= ' AND ram LIKE ?';
    $params[] = '%' . $_GET['ram'] . '%';
}

// Filtrage par ssd
if (isset($_GET['ssd']) && !empty($_GET['ssd'])) {
    $query .= ' AND ssd LIKE ?';
    $params[] = '%' . $_GET['ssd'] . '%';
}

// Gestion du tri par prix
$sort = isset($_GET['sort']) ? $_GET['sort'] : '';
$orderBy = "id DESC"; // Tri par défaut

// Définir l'ordre de tri en fonction du paramètre
switch ($sort) {
    case 'price_asc':
        $orderBy = "prix ASC";
        break;
    case 'price_desc':
        $orderBy = "prix DESC";
        break;
}

// Ajout de l'ordre de tri à la requête
$query .= ' ORDER BY ' . $orderBy;

// Exécution de la requête
$stmt = $dbh->prepare($query);
$stmt->execute($params);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Conservation du paramètre de tri actuel pour les liens
$current_sort = $sort;
?>





<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>ReadyPC</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.1/css/all.min.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="css/style.css?v=1!important">
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


<!-- carrousel -->
<!-- Section produits en vedette avec design amélioré -->
<div class="featured-products-section">
  <div class="featured-title-container">
    <h2 class="featured-title">Nos meilleures ventes</h2>
    <div class="featured-line"></div>
  </div>
</div>

<div id="productCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="5000">
  <!-- Indicateurs -->
  <div class="carousel-indicators">
    <?php
    $shuffle_products = $products; // Créez une copie pour le mélange;
    shuffle($shuffle_products); // Mélangez les produits
    $featured_products = array_slice($shuffle_products, 0, 3); // Limiter à 3 produits
    foreach($featured_products as $i => $product): 
    ?>
      <button type="button" data-bs-target="#productCarousel" data-bs-slide-to="<?= $i ?>" 
        <?= $i === 0 ? 'class="active"' : '' ?>></button>
    <?php endforeach; ?>
  </div>
  
  <!-- Slides -->
  <div class="carousel-inner">
    <?php foreach($featured_products as $index => $product): ?>
      <!-- Dans votre boucle de carrousel -->
<div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
  <div class="carousel-content">
    <div class="carousel-image">
      <img src="<?= htmlspecialchars($product['url_image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
    </div>
    <div class="carousel-details">
      <h3><?= htmlspecialchars($product['name']) ?></h3>
      <p><strong>Carte Graphique:</strong> <?= htmlspecialchars($product['carte_graphique']) ?></p>
      <p><strong>Processeur:</strong> <?= htmlspecialchars($product['processeur']) ?></p>
      <p><strong>RAM:</strong> <?= htmlspecialchars($product['ram']) ?></p>
      <p><strong>SSD:</strong> <?= htmlspecialchars($product['ssd']) ?></p>
      <strong><span class="price"><?= number_format($product['prix'], 2, ',', ' ') ?> €</span></strong>
      <div class="carousel-buttons">
        <a href="details.php?id=<?= $product['id'] ?>" class="btn btn-outline-primary">Voir détails</a>
        <a href="ajouter_au_panier.php?id=<?= $product['id'] ?>" class="btn-ajouter-panier-index">Ajouter au panier</a>
      </div>
    </div>
  </div>
</div>

    <?php endforeach; ?>
  </div>
  
  <!-- Contrôles -->
  <button class="carousel-control-prev" type="button" data-bs-target="#productCarousel" data-bs-slide="prev">
    <span class="carousel-control-prev-icon"></span>
  </button>
  <button class="carousel-control-next" type="button" data-bs-target="#productCarousel" data-bs-slide="next">
    <span class="carousel-control-next-icon"></span>
  </button>
</div>
<!-- /carrousel -->




<!-- Services -->
<section class="services">
  <div class="service_item">
    <i class="fas fa-store delivery-icon"></i>
    <p class="text-service">Commande prête en 4min6</p>
  </div>
  <div class="service_item">
    <i class="fas fa-truck delivery-icon"></i>
    <p class="text-service">Livraison en moins de 3min47</p>
  </div>
  <div class="service_item">
    <i class="fas fa-people-carry delivery-icon"></i>
    <p class="text-service">Installation du PC en moins de 1min93</p>
  </div>
</section>


<!-- Affiche un message si des filtres sont appliqués -->
<?php if (isset($_GET['carte_graphique']) || isset($_GET['processeur']) || isset($_GET['prix_max']) || isset($_GET['ram'])): ?>
  <div class="alert alert-info">
    Filtres appliqués: 
    <?php
    $filtresAppliques = [];
    if (!empty($_GET['carte_graphique'])) $filtresAppliques[] = "Carte Graphique: " . htmlspecialchars($_GET['carte_graphique']);
    if (!empty($_GET['processeur'])) $filtresAppliques[] = "Processeur: " . htmlspecialchars($_GET['processeur']);
    if (!empty($_GET['prix_max'])) $filtresAppliques[] = "Prix max: " . htmlspecialchars($_GET['prix_max']) . " €";
    if (!empty($_GET['ram'])) $filtresAppliques[] = "RAM: " . htmlspecialchars($_GET['ram']);
    echo implode(' | ', $filtresAppliques);
    ?>
    <a href="index.php" class="btn btn-sm btn-outline-secondary ms-2">Effacer les filtres</a>
  </div>
<?php endif; ?>

<!-- Filtres -->
<div class="filter-container">
  <input type="checkbox" id="toggle-filters" hidden>
  <label for="toggle-filters" class="filter-hamburger">
    <i class="fas fa-filter"></i> Filtres
  </label>
  
  <div class="filter-menu">
    <h3>Filtrer les produits</h3>
    <form method="GET" action="">
      <!-- Préserver le paramètre de tri actuel -->
      <?php if (isset($_GET['sort'])): ?>
      <input type="hidden" name="sort" value="<?= htmlspecialchars($_GET['sort']) ?>">
      <?php endif; ?>
      
      <div class="filter-group">
        <label>Carte Graphique</label>
        <input type="text" name="carte_graphique" placeholder="RTX 3080, RX 6800..." 
               value="<?= isset($_GET['carte_graphique']) ? htmlspecialchars($_GET['carte_graphique']) : '' ?>">
      </div>
      <div class="filter-group">
        <label>Processeur</label>
        <input type="text" name="processeur" placeholder="i7-12700K, Ryzen 7..." 
               value="<?= isset($_GET['processeur']) ? htmlspecialchars($_GET['processeur']) : '' ?>">
      </div>
      <div class="filter-group">
        <label>Prix max (€)</label>
        <div class="price-input">
          <input type="number" name="prix_max" min="0" placeholder="2000" 
                 value="<?= isset($_GET['prix_max']) ? htmlspecialchars($_GET['prix_max']) : '' ?>">
        </div>
      </div>
      <div class="filter-group">
        <label>RAM (GO)</label>
        <input type="text" name="ram" placeholder="16, 32..." 
               value="<?= isset($_GET['ram']) ? htmlspecialchars($_GET['ram']) : '' ?>">
      </div>
      <div class="filter-group">
        <label>SSD (GO)</label>
        <input type="text" name="ssd" placeholder="500G0, 1T..." 
               value="<?= isset($_GET['ssd']) ? htmlspecialchars($_GET['ssd']) : '' ?>">
      </div>
      <div class="filter-buttons">
        <button type="submit" class="filter-btn apply">Appliquer</button>
        <?php 
        $reset_url = isset($_GET['sort']) ? "?sort=" . htmlspecialchars($_GET['sort']) : "index.php";
        ?>
        <a href="<?= $reset_url ?>" class="filter-btn reset">Réinitialiser</a>
      </div>
    </form>
  </div>
</div>



<!-- Liste des PC -->
<section class="pc-products" id="products">
  <div class="section-header">
    <h2 class="section-title">PC en vente</h2>
    <div class="sort-buttons">
      <?php
      // Préserver tous les paramètres de filtrage actuels
      $current_params = $_GET;
      
      // Paramètres pour le tri croissant
      $asc_params = $current_params;
      $asc_params['sort'] = 'price_asc';
      $asc_url = '?' . http_build_query($asc_params);
      
      // Paramètres pour le tri décroissant
      $desc_params = $current_params;
      $desc_params['sort'] = 'price_desc';
      $desc_url = '?' . http_build_query($desc_params);
      
      // URL pour réinitialiser le tri des prix tout en conservant les filtres
      $reset_sort_params = $current_params;
      if (isset($reset_sort_params['sort'])) {
          unset($reset_sort_params['sort']);
      }
      $reset_sort_url = empty($reset_sort_params) ? 'index.php' : '?' . http_build_query($reset_sort_params);
      
      // Déterminer quel bouton est actif
      $active_asc = (isset($_GET['sort']) && $_GET['sort'] == 'price_asc') ? 'active' : '';
      $active_desc = (isset($_GET['sort']) && $_GET['sort'] == 'price_desc') ? 'active' : '';
      ?>
      <a href="<?= $asc_url ?>" class="btn btn-sm btn-outline-primary sort-btn <?= $active_asc ?>">
        <i class="fas fa-sort-amount-down-alt"></i> Prix croissant
      </a>
      <a href="<?= $desc_url ?>" class="btn btn-sm btn-outline-primary sort-btn <?= $active_desc ?>">
        <i class="fas fa-sort-amount-down"></i> Prix décroissant
      </a>
      <a href="<?= $reset_sort_url ?>" class="btn btn-sm btn-outline-primary sort-btn reset-sort" title="Réinitialiser le tri">
        <i class="fas fa-sync"></i>
      </a>
    </div>
  </div>



  <?php
  if (empty($products)) {
      echo "<p class='text-center text-danger'>Aucun produit trouvé.</p>";
  }
  ?>




<div class="row">
  <?php
  if (!isset($_SESSION['compare_ids'])) {
    $_SESSION['compare_ids'] = [];
  }
  
  foreach ($products as $product): 
    // Vérifie si ce produit est dans la liste de comparaison
    $isSelected = in_array($product['id'], $_SESSION['compare_ids']);
  ?>
    <div class="col-md-3 mb-4">
      <div class="pc-box card h-100">
        <img src="<?= htmlspecialchars($product['url_image']) ?>" class="card-img-top" alt="...">
        <div class="card-body d-flex flex-column">
          <h5 class="card-title"><?= htmlspecialchars($product['name']) ?></h5>
          <p class="card-text">
            Carte graphique : <?= htmlspecialchars($product['carte_graphique']) ?><br>
            Processeur: <?= htmlspecialchars($product['processeur']) ?><br>
            RAM : <?= htmlspecialchars($product['ram']) ?><br>
            Stockage : <?= htmlspecialchars($product['ssd']) ?><br>
          </p>
          <strong><span class="price"><?= number_format($product['prix'], 2, ',', ' ') ?> €</span></strong>
          
          <!-- Bouton détails sur sa propre ligne -->
          <div class="text-center mb-2 mt-auto">
            <a href="details.php?id=<?= $product['id'] ?>" class="btn btn-outline-primary w-100">Voir détails</a>
          </div>
          
          <!-- Boutons panier et comparaison sur une ligne avec espace maximal entre eux -->
          <div class="d-flex justify-content-between w-100">
            <a href="ajouter_au_panier.php?id=<?= $product['id'] ?>" class="btn-ajouter-panier-index">Ajouter au panier</a>
            <a href="comparaison.php?id=<?= $product['id'] ?>" 
              class="btn-compare ml-auto <?= (isset($_SESSION['compare_ids']) && in_array($product['id'], $_SESSION['compare_ids'])) ? 'active' : '' ?>"
              style="position: relative; top: 10px;">
              <i class="fas fa-exchange-alt"></i>
            </a>
          </div>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
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

<!-- carrousel script -->
<!-- Assurez-vous que ces scripts Bootstrap sont inclus dans votre page -->
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.min.js"></script>

<script>
  // Code d'initialisation du carrousel
  document.addEventListener('DOMContentLoaded', function() {
    // Initialise le carrousel avec Bootstrap
    var carousel = new bootstrap.Carousel(document.getElementById('productCarousel'), {
      interval: 5000,
      wrap: true,
      keyboard: true
    });
    
    // Vérifie que les contrôles fonctionnent
    document.querySelector('.carousel-control-prev').addEventListener('click', function() {
      carousel.prev();
    });
    
    document.querySelector('.carousel-control-next').addEventListener('click', function() {
      carousel.next();
    });
  });
</script>
<!-- /carrousel script -->



<!-- Bootstrap JS (nécessaire pour le menu hamburger) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>


</section>
    <!-- Barre supérieure avec hotline -->
    <div class="top-bar">
        <div class="containerr">
            <div class="hotline">
                Hotline d'experts: <strong>+32 (0) 493 93 19 45 (c'est david)</strong> <small> <br> (lun-ven : 9h à 20h)<br>
                Assistance francophone et anglais</small>
            </div>
            <div class="social-icons">
                <a href="https://www.youtube.com/watch?v=dQw4w9WgXcQ" class="social-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                </a>
                <a href="https://www.youtube.com/watch?v=dQw4w9WgXcQ" class="social-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z"/></svg>
                </a>
                <a href="https://www.youtube.com/watch?v=dQw4w9WgXcQ" class="social-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M9 8h-3v4h3v12h5v-12h3.642l.358-4h-4v-1.667c0-.955.192-1.333 1.115-1.333h2.885v-5h-3.808c-3.596 0-5.192 1.583-5.192 4.615v3.385z"/></svg>
                </a>
                <a href="https://www.youtube.com/watch?v=JmVZduM_IlU&ab_channel=TheKenny78" class="social-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M19.615 3.184c-3.604-.246-11.631-.245-15.23 0-3.897.266-4.356 2.62-4.385 8.816.029 6.185.484 8.549 4.385 8.816 3.6.245 11.626.246 15.23 0 3.897-.266 4.356-2.62 4.385-8.816-.029-6.185-.484-8.549-4.385-8.816zm-10.615 12.816v-8l8 3.993-8 4.007z"/></svg>
                </a>
                <a href="https://www.youtube.com/watch?v=dQw4w9WgXcQ" class="social-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M12.53.02C13.84 0 15.14.01 16.44.02c1.3 0 2.61.01 3.91.02.82.01 1.54.15 2.2.47.55.26 1.03.62 1.43 1.03.41.42.74.91 1 1.46.32.67.48 1.39.49 2.21.01.74.02 1.48.02 2.23.01 1.31.01 2.61.01 3.91 0 1.31-.01 2.61-.02 3.91 0 .75-.01 1.49-.01 2.24-.01.82-.15 1.54-.47 2.21-.26.55-.62 1.03-1.03 1.43-.42.41-.91.74-1.46 1-.67.32-1.39.48-2.21.49-.74.01-1.48.02-2.23.02-1.31.01-2.61.01-3.91.01-1.31 0-2.61-.01-3.91-.02-.75 0-1.49-.01-2.24-.01-.82-.01-1.54-.15-2.21-.47-.55-.26-1.03-.62-1.43-1.03-.41-.42-.74-.91-1-1.46-.32-.67-.48-1.39-.49-2.21-.01-.74-.02-1.48-.02-2.23-.01-1.31-.01-2.61-.01-3.91 0-1.31.01-2.61.02-3.91 0-.75.01-1.49.01-2.24.01-.82.15-1.54.47-2.21.26-.55.62-1.03 1.03-1.43.42-.41.91-.74 1.46-1 .67-.32 1.39-.48 2.21-.49.74-.01 1.48-.02 2.23-.02M12 6.87c-2.83 0-5.13 2.3-5.13 5.13 0 2.83 2.3 5.13 5.13 5.13s5.13-2.3 5.13-5.13c0-2.83-2.3-5.13-5.13-5.13M12 15c-1.66 0-3-1.34-3-3s1.34-3 3-3 3 1.34 3 3-1.34 3-3 3m5.87-9.13c0-.66-.54-1.2-1.2-1.2-.66 0-1.2.54-1.2 1.2 0 .66.54 1.2 1.2 1.2.66 0 1.2-.54 1.2-1.2zM18.25 4h-12.5c-.97 0-1.75.78-1.75 1.75v12.5c0 .97.78 1.75 1.75 1.75h12.5c.97 0 1.75-.78 1.75-1.75v-12.5c0-.97-.78-1.75-1.75-1.75zm.75 14.25c0 .41-.34.75-.75.75h-12.5c-.41 0-.75-.34-.75-.75v-12.5c0-.41.34-.75.75-.75h12.5c.41 0 .75.34.75.75v12.5z"/></svg>
                </a>
                <a href="https://www.youtube.com/watch?v=dQw4w9WgXcQ" class="social-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M12.042 23.648c-7.813 0-12.042-4.876-12.042-11.171 0-6.727 4.762-12.125 13.276-12.125 6.214 0 10.724 4.038 10.724 9.601 0 8.712-10.33 11.012-9.812 6.052-.71 1.108-1.854 2.354-4.053 2.354-2.516 0-4.08-1.842-4.08-4.807 0-4.444 2.921-8.199 6.379-8.199 1.659 0 2.8.876 3.277 2.221l.464-1.632h2.338c-.244.832-2.321 8.527-2.321 8.527-.648 2.666 1.35 2.713 3.122 1.297 3.329-2.58 3.501-9.327-.998-12.141-4.821-2.891-15.795-1.102-15.795 8.693 0 5.611 3.95 9.381 9.829 9.381 3.436 0 5.542-.93 7.295-1.948l1.177 1.698c-1.711.966-4.461 2.209-8.78 2.209zm-2.344-14.305c-.715 1.34-1.177 3.076-1.177 4.424 0 3.61 3.522 3.633 5.252.239.712-1.394 1.171-3.171 1.171-4.529 0-2.917-3.495-3.434-5.246-.134z"/></svg>
                </a>
            </div>
        </div>
    </div>



    <!-- Footer -->
    <footer class="footer">
        <div class="container footer-container">
            <div class="footer-column">
                <h3 class="footer-title">ReadyPC</h3>
                <a href="https://www.youtube.com/watch?v=dQw4w9WgXcQ" class="footer-link">Pourquoi ReadyPC</a>
                <a href="https://www.youtube.com/watch?v=dQw4w9WgXcQ" class="footer-link">Formulaire de contact</a>
                <a href="https://www.youtube.com/watch?v=dQw4w9WgXcQ" class="footer-link">Blog</a>
                <a href="https://www.youtube.com/watch?v=dQw4w9WgXcQ" class="footer-link">Programme d'affiliation / de partenariat</a>
                
            </div>

            <div class="footer-column">
                <h3 class="footer-title">Informations</h3>
                <a href="https://www.youtube.com/watch?v=dQw4w9WgXcQ" class="footer-link">La garantie ReadyPC</a>
                <a href="https://www.youtube.com/watch?v=dQw4w9WgXcQ" class="footer-link">Service / Téléchargements</a>
                <a href="https://www.youtube.com/watch?v=dQw4w9WgXcQ" class="footer-link">FAQ : Foire aux questions</a>
                <a href="https://www.youtube.com/watch?v=dQw4w9WgXcQ" class="footer-link">Paiement et envoi</a>
            </div>


            <div class="footer-column">
              <h3 class="footer-title">Payer en toute simplicité</h3>
              <div class="payment-methods">
                  <!-- PayPal -->
                  <div class="payment-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                    <title>PayPal</title>
                      <path fill="#253B80" d="M7.076 21.337H2.47a.641.641 0 0 1-.633-.755l2.88-18.292a.641.641 0 0 1 .634-.526h9.103c4.115 0 6.143 3.018 5.634 6.542-.521 3.524-3.521 6.162-7.604 6.162h-2.79a.641.641 0 0 0-.634.526l-.984 6.343z"/>
                      <path fill="#179BD7" d="M20.357 7.358C20.884 4.089 18.853 1.5 15.082 1.5H6.265a.745.745 0 0 0-.735.62L2.618 20.851a.447.447 0 0 0 .442.526h4.28l1.075-6.821-.033.212a.744.744 0 0 1 .735-.62h2.631c3.453 0 6.059-1.835 6.59-4.826.025-.138.046-.273.064-.406.236-1.582-.001-2.652-.718-3.558H20.357z"/>  
                      <path fill="#222D65" d="M8,17.5 L7,22.5 L11.6,22.5 L12.7,15.7 L16,15.7 C19.1,15.7 22,13.8 22.5,10.2 C23.1,6.1 19.9,4.5 16.9,4.5 L8.9,4.5 L6.3,22.5 L8,17.5 Z"/>
                    </svg>
                 </div>
                 
                 <!-- lol -->
                 <div class="payment-icon">
                   <img src="Image/lol.png" alt="League of Legends" width="40" height="20" title="League of Legends">
                 </div>

                 <!-- Mastercard -->
                 <div class="payment-icon">
                   <svg xmlns="http://www.w3.org/2000/svg" width="27" height="27" viewBox="0 0 23 23">
                     <title>Mastercard</title>
                     <path fill="#FF5F00" d="M9.5 5.5h5v13h-5z"/>
                     <path fill="#EB001B" d="M9.5 12c0-2.5 1.2-4.7 3-6.1-1.3-1.1-3.1-1.7-5-1.7-4.4 0-8 3.6-8 8s3.6 8 8 8c1.9 0 3.6-.7 5-1.7-1.8-1.4-3-3.6-3-6.5z"/>
                     <path fill="#F79E1B" d="M24.5 12c0 4.4-3.6 8-8 8-1.9 0-3.6-.7-5-1.7 1.8-1.4 3-3.6 3-6.1s-1.2-4.7-3-6.1c1.3-1.1 3.1-1.7 5-1.7 4.4-.2 8 3.4 8 7.6z"/>
                    </svg>
                  </div>
                  
                  <!-- vbuck -->
                  <div class="payment-icon">
                    <img src="Image/icons8-v-bucks-96.png" alt="V-Bucks" width="27" height="27" title="V-Bucks">
                  </div>
                </div>
                
                
            <h3 class="footer-title">Nous expédions avec</h3>
              <div class="shipping-methods">
                  <!-- Première icône (DHL) -->
                  <div class="shipping-icon dhl-icon">
                      <svg role="img" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" width="24" height="24">
                          <title>DHL</title>
                          <rect width="24" height="24" fill="#FFCC00" rx="2"/>
                          <path d="M4.22 10.303l-.767 1.043h4.18c.21 0 .208.078.105.218-.105.142-.28.39-.386.534-.054.073-.154.207.171.207h1.71l.505-.69c.314-.426.028-1.312-1.095-1.312H4.22zm7.204 0l-1.475 2.002h5.39l1.473-2.002H14.61l-.843 1.146h-.985l.846-1.146h-2.203zm6.105 0l-1.474 2.002h2.334l1.472-2.002H17.53zm-12.845 1.3l-1.54 2.094h3.754c1.24 0 1.932-.844 2.145-1.136h-2.56c-.326 0-.226-.133-.172-.207.107-.143.283-.388.388-.53.104-.14.107-.22-.105-.22h-1.91zM0 12.562v.242h3.398l.176-.242H0zm9.762 0l-.836 1.136h2.203l.836-1.136H9.762zm3.185 0l-.836 1.136h2.203l.836-1.136h-2.203zm2.918 0s-.159.22-.238.326c-.276.374-.033.81.87.81h3.538l.834-1.136h-5.004zm5.408 0l-.177.242H24v-.242h-2.727zM0 13.01v.24h3.068l.178-.24H0zm20.943 0l-.175.24H24v-.24h-3.057zM0 13.457v.24h2.74l.176-.24H0zm20.615 0l-.177.24H24v-.24h-3.385z" fill="#D40511"/>
                      </svg>
                  </div>

                   <!-- RL -->
                 <div class="payment-icon">
                  <img src="Image/rocketleague.png" alt="Rocket League" width="25" height="25" title="Rocket League">
                 </div>
                  
                  <!-- Deuxième icône (DPD) -->
                  <div class="shipping-icon dpd-icon">
                      <svg role="img" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" width="24" height="24">
                          <title>DPD</title>
                          <rect width="24" height="24" fill="#ffffff" rx="2"/>
                          <path d="M16.01 10.71a.364.364 0 01-.343-.006l-.558-.331a.43.43 0 01-.182-.312l-.014-.65a.363.363 0 01.165-.3l6.7-3.902L12.377.085A.799.799 0 0012 0a.798.798 0 00-.377.085l-9.4 5.124 10.53 6.13c.098.054.172.181.172.295v8.944c0 .112-.08.241-.178.294l-.567.315c-.171.062-.256.043-.361 0l-.569-.315a.362.362 0 01-.175-.294v-7.973a.223.223 0 00-.095-.156L1.702 7.048v10.579c0 .236.167.528.371.648l9.556 5.636c.102.06.237.09.371.089a.745.745 0 00.371-.09l9.557-5.635a.835.835 0 00.37-.648V7.047Z" fill="#DC0032"/>
                      </svg>
                  </div>
              </div>
            </div>
        </div>
    </footer>
</body>
</html>

