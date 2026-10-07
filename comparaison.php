<?php
require_once('db_connection.php');
require_once('fonctionPP.php');
session_start();
// Crée un cookie spécifique pour la page de comparaison
// Ce cookie expirera dès que l'utilisateur fermera la page
setcookie('comparison_active', 'true', 0, '/');

// Vérifie si le cookie de comparaison existait déjà
// S'il n'existait pas, cela signifie que l'utilisateur vient d'une autre page
if (!isset($_COOKIE['comparison_active'])) {
    // Réinitialise la session de comparaison
    $_SESSION['compare_ids'] = [];
}
// Initialiser le tableau des produits à comparer si nécessaire
if (!isset($_SESSION['compare_ids'])) {
    $_SESSION['compare_ids'] = [];
}

// Si un ID est fourni dans l'URL, l'ajouter au tableau de comparaison
if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    // Si l'ID est déjà dans la liste, le retirer
    if (in_array($id, $_SESSION['compare_ids'])) {
        $key = array_search($id, $_SESSION['compare_ids']);
        unset($_SESSION['compare_ids'][$key]);
        $_SESSION['compare_ids'] = array_values($_SESSION['compare_ids']);
    } 
    // Sinon l'ajouter s'il n'y a pas déjà 2 produits
    elseif (count($_SESSION['compare_ids']) < 2) {
        $_SESSION['compare_ids'][] = $id;
    }
    
    // Rediriger vers l'index si on n'a pas encore 2 produits à comparer
    if (count($_SESSION['compare_ids']) < 2) {
        header('Location: index.php');
        exit;
    }
}

// S'il y a exactement 2 produits à comparer, les récupérer de la base de données
if (count($_SESSION['compare_ids']) == 2) {
    $id1 = $_SESSION['compare_ids'][0];
    $id2 = $_SESSION['compare_ids'][1];
    
    try {
        $stmt = $dbh->prepare("SELECT * FROM products WHERE id IN (?, ?)");
        $stmt->execute([$id1, $id2]);
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Organiser les produits pour l'affichage
        $pc1 = null;
        $pc2 = null;
        
        foreach ($products as $product) {
            if ($product['id'] == $id1) {
                $pc1 = $product;
            } else {
                $pc2 = $product;
            }
        }
    } catch (PDOException $e) {
        die("Erreur lors de la récupération des produits: " . $e->getMessage());
    }
} else {
    // Pas assez de produits pour comparer, rediriger
    header('Location: index.php');
    exit;
}
// Marquer que la comparaison a été vue, ce qui réinitialisera les boutons sur l'index
$_SESSION['comparison_viewed'] = true;

// Pour éviter que la comparaison ne soit conservée après l'affichage
$_SESSION['compare_ids'] = [];

// ✅ Récupération de l'image de profil
$profile_pictureee = isset($_SESSION['login']) ? PP($dbh, $_SESSION['login']) : null;

?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>ReadyPC - Comparaison</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.1/css/all.min.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="css/style.css">
  <link rel="stylesheet" href="css/comparaison.css?v=1!important">
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
          echo '<li class="nav-item text-primary fw-bold ms-2">' . htmlspecialchars($_SESSION['login']) . '</li>';


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

<!-- Section de comparaison de PC avec des classes uniques -->
<section class="compare-section">
    <h1 class="compare-title">Comparaison de PC</h1>
    
    <div class="compare-grid">
        <div class="compare-vs-badge">VS</div>
        
        <div class="compare-item-col">
            <h2 class="compare-item-title"><?= htmlspecialchars($pc1['name']) ?></h2>
            <img src="<?= htmlspecialchars($pc1['url_image']) ?>" alt="<?= htmlspecialchars($pc1['name']) ?>" class="compare-img">
            
            <div class="compare-item-details">
                <div class="compare-specs-list">
                    <p class="compare-spec"><strong>Boîtier :</strong> <?= htmlspecialchars($pc1['boitier']) ?></p>
                    <p class="compare-spec"><strong>Carte mère :</strong> <?= htmlspecialchars($pc1['carte_mere']) ?></p>
                    <p class="compare-spec"><strong>Carte graphique (GPU) :</strong> <?= htmlspecialchars($pc1['carte_graphique']) ?></p>
                    <p class="compare-spec"><strong>Processeur (CPU) :</strong> <?= htmlspecialchars($pc1['processeur']) ?></p>
                    <p class="compare-spec"><strong>Refroidissement du CPU :</strong> <?= htmlspecialchars($pc1['refroidissement']) ?></p>
                    <p class="compare-spec"><strong>Mémoire vive :</strong> <?= htmlspecialchars($pc1['ram']) ?></p>
                    <p class="compare-spec"><strong>Stockage :</strong> <?= htmlspecialchars($pc1['ssd']) ?></p>
                    <p class="compare-spec"><strong>Système d'exploitation :</strong> <?= htmlspecialchars($pc1['os']) ?></p>
                </div>
                
                <div class="compare-price-cart">
                    <p class="compare-price"><strong>Prix :</strong> <?= number_format($pc1['prix'], 2, ',', ' ') ?>€</p><br>
                    <a href="details.php?id=<?= $pc1['id'] ?>" class="btn btn-outline-primary">Voir détails</a><br>
                    <a href="ajouter_au_panier.php?id=<?= $pc1['id'] ?>" class="btn-ajouter-panier-index">Ajouter au panier</a>
                </div>
            </div>
        </div>
        
        <div class="compare-item-col">
            <h2 class="compare-item-title"><?= htmlspecialchars($pc2['name']) ?></h2>
            <img src="<?= htmlspecialchars($pc2['url_image']) ?>" alt="<?= htmlspecialchars($pc2['name']) ?>" class="compare-img">
            
            <div class="compare-item-details">
                <div class="compare-specs-list">
                    <p class="compare-spec"><strong>Boîtier :</strong> <?= htmlspecialchars($pc2['boitier']) ?></p>
                    <p class="compare-spec"><strong>Carte mère :</strong> <?= htmlspecialchars($pc2['carte_mere']) ?></p>
                    <p class="compare-spec"><strong>Carte graphique (GPU) :</strong> <?= htmlspecialchars($pc2['carte_graphique']) ?></p>
                    <p class="compare-spec"><strong>Processeur (CPU) :</strong> <?= htmlspecialchars($pc2['processeur']) ?></p>
                    <p class="compare-spec"><strong>Refroidissement du CPU :</strong> <?= htmlspecialchars($pc2['refroidissement']) ?></p>
                    <p class="compare-spec"><strong>Mémoire vive :</strong> <?= htmlspecialchars($pc2['ram']) ?></p>
                    <p class="compare-spec"><strong>Stockage :</strong> <?= htmlspecialchars($pc2['ssd']) ?></p>
                    <p class="compare-spec"><strong>Système d'exploitation :</strong> <?= htmlspecialchars($pc2['os']) ?></p>
                </div>
                
                <div class="compare-price-cart">
                    <p class="compare-price"><strong>Prix :</strong> <?= number_format($pc2['prix'], 2, ',', ' ') ?>€</p><br>
                    <a href="details.php?id=<?= $pc2['id'] ?>" class="btn btn-outline-primary">Voir détails</a><br>
                    <a href="ajouter_au_panier.php?id=<?= $pc2['id'] ?>" class="btn-ajouter-panier-index">Ajouter au panier</a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="compare-actions">
        <a href="index.php" class="compare-btn-return">Retour aux produits</a>
    </div>
</section>
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

<!-- Bootstrap JS (nécessaire pour le menu hamburger) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>