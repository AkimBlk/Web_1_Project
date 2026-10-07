<?php
session_start();
require_once('db_connection.php');

// Redirection si déjà connecté
if (isset($_SESSION['login']) && $_SESSION['login'] != '') {
    header("Location: acces_denied.php");
    exit();
}

if (!empty($_POST['login']) && !empty($_POST['password'])) {
  try {
    // Recherche par login
    $sQuery = 'SELECT * FROM users WHERE login = :login';
    $stmt = $dbh->prepare($sQuery);
    $stmt->bindParam(':login', $_POST['login']);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Vérifie si l'utilisateur existe ET si le mot de passe est correct
    if ($user && $user['password'] == $_POST['password']) {
        $_SESSION['access_level'] = $user['access_level'];
        $_SESSION['login'] = $user['login'];
        header("Location: index.php");
        exit();
    } else {
        $erreur = '<p style="color: red;">Login / Password incorrect</p>';
    }
  } catch (PDOException $e) {
    $erreur = '<p style="color: red;">Erreur de base de données</p>';
  }
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Se Connecter - ReadyPC</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.1/css/all.min.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="css/style.css?v=1">
  <link rel="stylesheet" href="css/login.css?v=1">
  <link rel="icon" type="image/png" href="Image/logo.png">

  
</head>
<body>

<!-- Barre de recherche déroulante -->
<div class="search-dropdown" id="searchDropdown">
  <form class="d-flex justify-content-center" action="recherche.php" method="get">
    <input id="searchInput" class="form-control form-control-lg me-2" type="search" name="q" placeholder="Rechercher un PC..." aria-label="Search">
    <button type="submit" class="search-btn">Rechercher</button>
  </form>
  
  <!-- Suggestions dynamiques -->
  <ul id="suggestionsList" class="list-group position-absolute w-100 mt-1" style="z-index: 1000;"></ul>
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
        <li class="nav-item"><a class="nav-link" href="a_propos.php">À propos</a></li>
        <li class="nav-item"><a class="nav-link" href="panier.php">Votre panier</a></li>

        <?php
        if (isset($_SESSION['access_level']) && ($_SESSION['access_level'] == 2 || $_SESSION['access_level'] == 1)) {
            if ($_SESSION['access_level'] == 1) {
                echo '<li class="nav-item"><a class="nav-link" href="Gestion_PC.php">Gestion PC</a></li>';
            }
            echo '<li class="nav-item"><a class="nav-link" href="Deconnection.php">Se déconnecter</a></li>';
            echo '<li class="nav-item text-white fw-bold ms-2">Bienvenue ' . htmlspecialchars($_SESSION['login']) . '</li>';
            $avatar = $profile_pictureee ?? 'picture_uploads/default.jpg';
            echo '<li class="nav-item"><a href="profile.php"><img class="rounded-circle ms-3" src="' . $avatar . '" alt="avatar" width="40" height="40"></a></li>';
        } else {
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

<!-- FORMULAIRE DE CONNEXION -->
<div class="container mt-5">
  <div class="login-container mx-auto p-4 border rounded shadow" style="max-width: 500px;">
    <h1>Se connecter</h1>
    <?php if (!empty($erreur)) echo $erreur; ?>
    <form method="post" action="">
    <input type="text" name="login" placeholder="Nom d'utilisateur" required>
    <input type="password" name="password" placeholder="Mot de passe" required>
    <input type="submit" value="Se connecter">
        <a href="signup.php">Pas de compte ? Inscrivez-vous</a>
      </div>
    </form>
  </div>
</div>

<!-- JS Bootstrap + recherche -->
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
