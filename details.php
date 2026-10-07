<?php
session_start();
include 'db_connection.php'; // Inclut la connexion à la base de données
require_once('fonctionPP.php');
$profile_pictureee = isset($_SESSION['login']) ? PP($dbh, $_SESSION['login']) : null;

// Récupère l'ID du PC depuis l'URL
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Requête pour récupérer les détails du PC
    $sql = "SELECT * FROM products WHERE id = :id";
    $stmt = $dbh->prepare($sql);
    $stmt->execute(['id' => $id]);
    $pc = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$pc) {
        die("PC non trouvé.");
    }

    // Récupération des commentaires liés à ce PC
    $sql = "SELECT c.*, u.profile_picture, u.login 
    FROM comments c 
    JOIN users u ON c.user = u.login 
    WHERE c.pc_id = :id 
    ORDER BY c.created_at DESC";

    $stmt = $dbh->prepare($sql);
    $stmt->execute(['id' => $id]); 
    $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

} else {
    die("ID du PC non spécifié.");
}
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
  <link rel="stylesheet" href="css/style.css">
  <link rel="stylesheet" href="css/details.css?v=1">

  <!-- Favicon -->
  <link rel="icon" type="image/png" href="Image/logo.png">

    <!-- CKEditor pour les commentaires -->
    <script src="https://cdn.ckeditor.com/4.20.1/standard/ckeditor.js"></script>

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
      <li class="nav-item"><a class="nav-link" href="panier.php">Votre panier</a></li>
      
      <?php
        if (isset($_SESSION['access_level']) && ($_SESSION['access_level'] == 2 || $_SESSION['access_level'] == 1)) {
          if ($_SESSION['access_level'] == 1) {
              echo '<li class="nav-item"><a class="nav-link" href="Gestion_PC.php">Gestion PC</a></li>';
          }
      
          echo '<li class="nav-item"><a class="nav-link" href="Deconnection.php">Se déconnecter</a></li>';
          echo '<li class="nav-item text-primary fw-bold ms-2">' . htmlspecialchars($_SESSION['login']) . '</li>';

      
          // ✅ Utilisation de l’image récupérée depuis la base de données via la fonction PP()
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


<!-- Icône loupe -->
<li class="nav-item">
  <a class="nav-link" href="#" id="toggleSearch"><i class="fas fa-search fa-lg"></i></a>
</li>
</ul>
</div>
</div>
</nav>

<!-- Bootstrap + JS -->
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

<section class="pc-details">
    <h1><?= htmlspecialchars($pc['name']) ?></h1>
    <img src="<?= htmlspecialchars($pc['url_image']) ?>" alt="<?= htmlspecialchars($pc['name']) ?>">
    <div class="details">

    <p><strong>Boîtier :</strong> <?= htmlspecialchars($pc['boitier']) ?></p>
    <p><strong>Carte mère :</strong> <?= htmlspecialchars($pc['carte_mere']) ?></p>
    <p><strong>Carte graphique (GPU) :</strong> <?= htmlspecialchars($pc['carte_graphique']) ?></p>
    <p><strong>Processeur (CPU) :</strong> <?= htmlspecialchars($pc['processeur']) ?></p>
    <p><strong>Refroidissement du CPU :</strong> <?= htmlspecialchars($pc['refroidissement']) ?></p>
    <p><strong>Mémoire vive :</strong> <?= htmlspecialchars($pc['ram']) ?></p>
    <p><strong>Stockage :</strong> <?= htmlspecialchars($pc['ssd']) ?></p>
    <p><strong>Système d'exploitation :</strong> <?= htmlspecialchars($pc['os']) ?></p>

    <p class="pc-prix"><strong>Prix :</strong> <?= number_format($pc['prix'], 2, ',', ' ') ?>€</p>
    <a href="ajouter_au_panier.php?id=<?= $pc['id'] ?>" class="btn-ajouter-panier">Ajouter au panier</a>
</div>

</section>

<!-- Section Commentaires -->
<section class="comments-section">
    <h2>Commentaires</h2>
    
    <!-- Formulaire d'ajout de commentaire -->
    <?php if (isset($_SESSION['login'])): ?>
        <form action="ajouter_commentaire.php" method="POST">
            <textarea name="comment" id="comment-editor"></textarea>
            <input type="hidden" name="pc_id" value="<?= $id ?>">
            <button type="submit">Envoyer</button>
        </form>
        <script>
            CKEDITOR.replace('comment-editor');
        </script>
    <?php else: ?>
        <p>Vous devez être connecté pour laisser un commentaire.</p>
    <?php endif; ?>

    <!-- Affichage des commentaires -->
    <div class="comments-list">
    <?php if (count($comments) > 0): ?>
        <?php foreach ($comments as $comment): ?>
            <div class="comment" id="comment-<?= $comment['id'] ?>">
                <div class="comment-header">
                    <img class="profile-pic" src="<?= !empty($comment['profile_picture']) ? $comment['profile_picture'] : 'picture_uploads/default.jpg'; ?>" alt="Photo de profil">
                    <strong><?= htmlspecialchars($comment['user']) ?></strong>
                </div>
                <div class="comment-text"><?= $comment['comment'] ?></div> <!-- Affichage brut du contenu -->
                <span class="comment-date"><?= $comment['created_at'] ?></span>

                <?php if (isset($_SESSION['login']) && $_SESSION['login'] == $comment['user']): ?>
                    <!-- Bouton de suppression visible seulement pour l'auteur -->
                    <button class="delete-comment" data-comment-id="<?= $comment['id'] ?>">🗑️ Supprimer</button>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>Aucun commentaire pour l’instant.</p>
    <?php endif; ?>
</div>

</section>


<script>
    document.addEventListener("DOMContentLoaded", function() {
        document.querySelectorAll(".delete-comment").forEach(button => {
            button.addEventListener("click", function() {
                let commentId = this.getAttribute("data-comment-id");

                if (confirm("Voulez-vous vraiment supprimer ce commentaire ?")) {
                    fetch("delete_comment.php", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/x-www-form-urlencoded"
                        },
                        body: "comment_id=" + commentId
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            document.getElementById("comment-" + commentId).remove();
                        } else {
                            alert("Erreur lors de la suppression.");
                        }
                    });
                }
            });
        });
    });
</script>
</body>
</html>