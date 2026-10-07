<?php
session_start();
require_once('db_connection.php');
require_once('fonctionPP.php');

$profile_pictureee = null;
if (isset($_SESSION['login'])) {
    $profile_pictureee = PP($dbh, $_SESSION['login']);
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Mon panier</title>
  <link rel="stylesheet" href="css/panier.css">
  <link rel="stylesheet" href="css/style.css">
  <link rel="icon" type="image/png" href="Image/logo.png">

  <!-- Font Awesome & Bootstrap -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.1/css/all.min.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<!-- BARRE DE RECHERCHE -->
<div class="search-dropdown" id="searchDropdown">
  <form class="d-flex justify-content-center" action="recherche.php" method="get">
    <input id="searchInput" class="form-control form-control-lg me-2" type="search" name="q" placeholder="Rechercher un PC..." aria-label="Search">
    <button class="btn btn-primary btn-lg" type="submit">Rechercher</button>
  </form>
  
  <!-- Suggestions dynamiques -->
  <ul id="suggestionsList" class="list-group position-absolute w-100 mt-1" style="z-index: 1000;"></ul>
</div>
</div>


<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm fixed-top">
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

<?php
// Si panier vide
if (!isset($_SESSION['panier']) || empty($_SESSION['panier'])) {
  echo "<div class='empty-cart-message text-center mt-5'>
          <h1>Votre panier est vide.</h1>
          <a href='index.php' class='btn btn-primary mt-3'>Continuer vos achats</a>
        </div>";
  echo '<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>';
  echo '<script>
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

  
  document.addEventListener("click", function (e) {
    if (!document.getElementById("searchDropdown").contains(e.target)) {
      suggestionsList.innerHTML = "";
    }
  });
});
</script>';
  echo '</body></html>';
  exit();
}

// Sinon, panier rempli :
$ids = implode(',', array_keys($_SESSION['panier']));
$sql = "SELECT * FROM products WHERE id IN ($ids)";
$stmt = $dbh->prepare($sql);
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="centered-container">
    <div class="container mt-5">
  <div class="panier-wrapper text-center">
    <h2 class="mb-4">Votre Panier</h2>
    <table class="table table-bordered mx-auto">
      <thead class="table-dark">
        <tr>
          <th>Nom</th>
          <th>Prix</th>
          <th>Quantité</th>
          <th>Total</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php $total_panier = 0; ?>
        <?php foreach ($products as $product) : ?>
          <?php $id = $product['id']; ?>
          <tr>
            <td><?= htmlspecialchars($product['name']) ?></td>
            <td><?= number_format($product['prix'], 2, ',', ' ') ?>€</td>
            <td><?= $_SESSION['panier'][$id] ?></td>
            <td><?= number_format($product['prix'] * $_SESSION['panier'][$id], 2, ',', ' ') ?>€</td>
            <td><a class="btn btn-danger btn-sm" href="supprimer_du_panier.php?id=<?= $id ?>">Supprimer</a></td>
          </tr>
          <?php $total_panier += $product['prix'] * $_SESSION['panier'][$id]; ?>
        <?php endforeach; ?>
        <tr class="table-secondary">
  <td colspan="3"><strong>Total :</strong></td>
  <td><strong><?= number_format($total_panier, 2, ',', ' ') ?>€</strong></td>
  <td>
    <?php if (isset($_SESSION['login'])): ?>
      <a href="paiement.php" class="btn btn-success">Acheter</a>
    <?php else: ?>
      <button type="button" class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#loginModal">
        Acheter
      </button>
    <?php endif; ?>
  </td>
</tr>
<!-- Modal d'avertissement de connexion -->
<div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="loginModalLabel">Connexion requise</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p>Vous devez être connecté pour finaliser votre achat.</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
        <a href="login.php" class="btn btn-primary">Se connecter</a>
        <a href="signup.php" class="btn btn-primary">S'inscrire</a>
      </div>
    </div>
  </div>
</div>

      </tbody>
    </table>
    <a href="index.php" class="btn btn-primary mt-3">Continuer vos achats</a>
  </div>
</div>
</div>



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
