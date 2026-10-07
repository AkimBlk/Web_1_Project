<?php
session_start();
require_once('db_connection.php');

if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit();
}

$login = $_SESSION['login'];
$successMessage = "";
$errors = [];

try {
    $query = "SELECT profile_picture FROM users WHERE login = :login";
    $stmt = $dbh->prepare($query);
    $stmt->bindParam(':login', $login);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    $profilePicture = !empty($user['profile_picture']) ? $user['profile_picture'] : "picture_uploads/default.jpg";
} catch (PDOException $e) {
    $errors['database'] = "Erreur lors de la récupération des données: " . $e->getMessage();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Traitement de la suppression de photo
    if (isset($_POST['delete_photo'])) {
        try {
            $query = "UPDATE users SET profile_picture = NULL WHERE login = :login";
            $stmt = $dbh->prepare($query);
            $stmt->bindParam(':login', $login);
            $stmt->execute();
            $profilePicture = "picture_uploads/default.jpg";
            $successMessage = "Photo de profil supprimée avec succès !";
        } catch (PDOException $e) {
            $errors['database'] = "Erreur lors de la suppression de la photo: " . $e->getMessage();
        }
    } 
    // Traitement de l'upload de photo (sans vérifier le mot de passe)
    elseif (isset($_FILES["profile_picture"]) && $_FILES["profile_picture"]["error"] == 0) {
        try {
            $targetDir = "picture_uploads/";
            $fileName = basename($_FILES["profile_picture"]["name"]);
            $fileType = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            $newFileName = time() . '_' . $fileName;
            
            while (file_exists($targetDir . $newFileName)) {
                $newFileName = time() . '_' . uniqid() . '.' . $fileType;
            }
            
            $targetFilePath = $targetDir . $newFileName;
            $allowedTypes = ["jpg", "jpeg", "png", "gif", "webp"];
            
            if (in_array($fileType, $allowedTypes)) {
                if (move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $targetFilePath)) {
                    $stmt = $dbh->prepare("UPDATE users SET profile_picture = :profile_picture WHERE login = :login");
                    $stmt->bindParam(':profile_picture', $targetFilePath);
                    $stmt->bindParam(':login', $login);
                    $stmt->execute();
                    $successMessage = "Photo de profil mise à jour avec succès !";
                    
                    // Mettre à jour la variable $profilePicture pour l'affichage
                    $profilePicture = $targetFilePath;
                } else {
                    $errors['profile_picture'] = "Erreur lors du téléchargement.";
                }
            } else {
                $errors['profile_picture'] = "Format non valide (JPG, PNG, GIF, WEBP uniquement).";
            }
        } catch (Exception $e) {
            $errors['profile_picture'] = "Erreur lors du traitement de l'image: " . $e->getMessage();
        }
    }
    // Traitement des autres modifications de profil
    else {
        $new_login = $login;
        $updateLogin = false;
        $updatePassword = false;
        
        // Vérification du mot de passe actuel pour toute modification
        $currentPassword = trim($_POST['password'] ?? '');
        
        if (empty($currentPassword)) {
            $errors['password'] = "Veuillez entrer votre mot de passe actuel pour confirmer les modifications.";
        } else {
            try {
                $checkQuery = "SELECT password FROM users WHERE login = :login";
                $stmt = $dbh->prepare($checkQuery);
                $stmt->bindParam(':login', $login);
                $stmt->execute();
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                
                // Vérification du mot de passe
                if (!$user || $user['password'] !== $currentPassword) {
                    $errors['password'] = "Mot de passe incorrect.";
                } else {
                    // Mise à jour du login si demandé
                    if (!empty($_POST['new_login'])) {
                        $new_login = trim($_POST['new_login']);
                        $updateLogin = true;
                    }
                    
                    // Mise à jour du mot de passe si demandé
                    if (!empty($_POST['new_password'])) {
                        $new_password = trim($_POST['new_password']);
                        $confirm_password = trim($_POST['confirm_password'] ?? '');
                        
                        if ($new_password !== $confirm_password) {
                            $errors['confirm_password'] = "Les mots de passe ne correspondent pas.";
                        } else {
                            $updatePassword = true;
                        }
                    }
                    
                    // Si aucune erreur, procéder aux mises à jour
                    if (empty($errors)) {
                        if ($updateLogin) {
                            $stmt = $dbh->prepare("UPDATE users SET login = :new_login WHERE login = :login");
                            $stmt->bindParam(':new_login', $new_login);
                            $stmt->bindParam(':login', $login);
                            $stmt->execute();
                            $_SESSION['login'] = $new_login;
                            $login = $new_login;
                        }
                        
                        if ($updatePassword) {
                            $stmt = $dbh->prepare("UPDATE users SET password = :new_password WHERE login = :login");
                            $stmt->bindParam(':new_password', $new_password);
                            $stmt->bindParam(':login', $login);
                            $stmt->execute();
                        }
                        
                        if ($updateLogin && $updatePassword) {
                            $successMessage = "Nom d'utilisateur et mot de passe mis à jour avec succès !";
                        } elseif ($updateLogin) {
                            $successMessage = "Nom d'utilisateur mis à jour avec succès !";
                        } elseif ($updatePassword) {
                            $successMessage = "Mot de passe mis à jour avec succès !";
                        }
                    }
                }
            } catch (PDOException) {
                $errors['database'] = "Nom d'utilsateur déjà existant";
            }
        }
    }
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
  <link rel="stylesheet" href="css/style.css?v=1">
  <link rel="stylesheet" href="css/profile.css?v=1">
  <!-- Favicon -->
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
  <ul id="suggestionsList" class="list-group position-absolute w-100 mt-1" style="z-index: 1000;"></ul>
</div>
</div>

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm fixed-top">
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
      <li class="nav-item"><a class="nav-link" href="https://www.youtube.com/watch?v=dQw4w9WgXcQ">À propos</a></li>
      <li class="nav-item"><a class="nav-link" href="panier.php">Votre panier</a></li>
      
      <?php
        if (isset($_SESSION['access_level']) && ($_SESSION['access_level'] == 2 || $_SESSION['access_level'] == 1)) {
          if ($_SESSION['access_level'] == 1) {
              echo '<li class="nav-item"><a class="nav-link" href="Gestion_PC.php">Gestion PC</a></li>';
          }
          echo '<li class="nav-item"><a class="nav-link" href="Deconnection.php">Se déconnecter</a></li>';
          echo '<li class="nav-item text-primary fw-bold ms-2">' . htmlspecialchars($_SESSION['login']) . '</li>';

          // ✅ Utilisation de l'image récupérée depuis la base de données via la fonction PP()
          $avatar = !empty($profile_pictureee) ? $profile_pictureee : 'picture_uploads/default.jpg';
          
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

<div class="profile-container">
  <h1>Mon Profil</h1>

  <?php if (!empty($successMessage)): ?>
    <p class='success-message'><?php echo htmlspecialchars($successMessage); ?></p>
  <?php endif; ?>
  
  <?php if (!empty($errors['database'])): ?>
    <p class='error-message'><?php echo htmlspecialchars($errors['database']); ?></p>
  <?php endif; ?>

  <!-- Formulaire pour changer la photo -->
  <form method="post" enctype="multipart/form-data" id="photoForm">
    <div class="profile-picture-container">
      <img id="profileImg" src="<?php echo htmlspecialchars($profilePicture); ?>?t=<?php echo time(); ?>" alt="Photo de profil" class="profile-picture">
    </div>

    <input type="file" name="profile_picture" id="fileInput" accept="image/*" style="display: none;" onchange="submitPhotoForm()">
    <div class="photo-btns">
      <button type="button" id="changePhotoBtn" class="photo-btn">Changer la photo</button>
    </div>
    
    <?php if (!empty($errors['profile_picture'])): ?>
      <p class='error-text'><?php echo $errors['profile_picture']; ?></p>
    <?php endif; ?>
  </form>

  <!-- Formulaire pour supprimer la photo -->
  <form method="post">
    <button type="submit" name="delete_photo" class="photo-btn red">Supprimer la photo</button>
  </form>

  <!-- Formulaire principal de mise à jour -->
  <form method="post">
    <div class="input-container">
      <input type="text" name="current_login" id="current_login"
        value="<?php echo htmlspecialchars($login); ?>"
        readonly class="readonly-field"
        placeholder="Nom d'utilisateur actuel">
    </div>

    <div class="input-container">
      <input type="text" name="new_login" id="new_login"
        placeholder="Nouveau nom d'utilisateur"
        style="border-color: <?php echo !empty($errors['new_login']) ? 'red' : ''; ?>">
      <?php if (!empty($errors['new_login'])): ?>
        <p class='error-text'><?php echo $errors['new_login']; ?></p>
      <?php endif; ?>
    </div>

    <div class="input-container">
      <input type="password" name="password" id="password"
        placeholder="Mot de passe actuel*"
        required style="border-color: <?php echo !empty($errors['password']) ? 'red' : ''; ?>">
      <?php if (!empty($errors['password'])): ?>
        <p class='error-text'><?php echo $errors['password']; ?></p>
      <?php endif; ?>
    </div>

    <div class="input-container">
      <input type="password" name="new_password" id="new_password"
        placeholder="Nouveau mot de passe"
        style="border-color: <?php echo !empty($errors['new_password']) ? 'red' : ''; ?>">
      <?php if (!empty($errors['new_password'])): ?>
        <p class='error-text'><?php echo $errors['new_password']; ?></p>
      <?php endif; ?>
    </div>

    <div class="input-container">
      <input type="password" name="confirm_password" id="confirm_password"
        placeholder="Confirmer le nouveau mot de passe"
        style="border-color: <?php echo !empty($errors['confirm_password']) ? 'red' : ''; ?>">
      <?php if (!empty($errors['confirm_password'])): ?>
        <p class='error-text'><?php echo $errors['confirm_password']; ?></p>
      <?php endif; ?>
    </div>

    <input type="submit" value="Mettre à jour">
  </form>

  <!-- Suppression du compte -->
  <form method="post" action="delete_account.php" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer votre compte ? Cette action est irréversible.');">
    <input type="submit" value="Supprimer le compte" class="delete-btn delete-btn-account">
  </form>
</div>

<script>
  document.getElementById("changePhotoBtn").addEventListener("click", function() {
    document.getElementById("fileInput").click();
  });

  function submitPhotoForm() {
    if (document.getElementById("fileInput").files.length > 0) {
      // Prévisualisation de l'image
      const file = document.getElementById("fileInput").files[0];
      const reader = new FileReader();
      reader.onload = function(e) {
        document.getElementById("profileImg").src = e.target.result;
      };
      reader.readAsDataURL(file);
      
      // Soumission automatique du formulaire
      document.getElementById("photoForm").submit();
    }
  }

  document.addEventListener("DOMContentLoaded", function () {
    // Le timestamp est déjà ajouté dans l'URL de l'image via PHP
  });
</script>


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
