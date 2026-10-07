<?php
session_start();
require_once('db_connection.php');
require_once('fonctionPP.php');

// Vérification de l'accès administrateur
if (!isset($_SESSION['access_level']) || $_SESSION['access_level'] != 1) {
  header("Location: acces_denied.php");
  exit;
}

$profile_pictureee = isset($_SESSION['login']) ? PP($dbh, $_SESSION['login']) : null;
$success_message = isset($_SESSION['success_message']) ? $_SESSION['success_message'] : '';
$error_message = isset($_SESSION['error_message']) ? $_SESSION['error_message'] : '';
unset($_SESSION['success_message'], $_SESSION['error_message']);

// Initialisation des données du formulaire
$edit_mode = false;
$pc_data = [
    'id' => '', 'name' => '', 'ssd' => '', 'prix' => '', 'carte_graphique' => '',
    'processeur' => '', 'ram' => '', 'boitier' => '', 'carte_mere' => '', 
    'refroidissement' => '', 'os' => ''
];

// Fonction pour gérer les uploads d'images
function handleImageUpload() {
    $uploadDir = 'Image/';
    $default = 'Image/default.png';
    
    if (!isset($_FILES['image']) || $_FILES['image']['error'] != 0) {
        return $default;
    }
    
    $uploadFile = $uploadDir . time() . '_' . basename($_FILES['image']['name']);
    $imageFileType = strtolower(pathinfo($uploadFile, PATHINFO_EXTENSION));
    $allowedFormats = ['jpg', 'jpeg', 'png', 'webp'];
    
    if (!in_array($imageFileType, $allowedFormats) || $_FILES['image']['size'] > 4000000) {
        $_SESSION['error_message'] = 'Format ou taille d\'image invalide';
        return false;
    }
    
    if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadFile)) {
        return $uploadFile;
    }
    
    return $default;
}

// Mode edition
if (isset($_POST['edit_id'])) {
    $edit_mode = true;
    $stmt = $dbh->prepare("SELECT * FROM products WHERE id = :id");
    $stmt->bindValue(':id', $_POST['edit_id'], PDO::PARAM_INT);
    $stmt->execute();
    $pc_data = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Traitement des formulaires
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Suppression d'un PC
    if (isset($_POST['supprimer_pc'])) {
        try {
            // Récupération et suppression de l'image
            $stmtGetImage = $dbh->prepare("SELECT url_image FROM products WHERE id = :id");
            $stmtGetImage->bindValue(':id', $_POST['supprimer_pc']);
            $stmtGetImage->execute();
            $imageUrl = $stmtGetImage->fetchColumn();
            
            if ($imageUrl && $imageUrl !== 'Image/default.png' && file_exists($imageUrl)) {
                unlink($imageUrl);
            }
            
            // Suppression du PC
            $stmt = $dbh->prepare('DELETE FROM products WHERE id = :id');
            $stmt->bindValue(':id', $_POST['supprimer_pc'], PDO::PARAM_INT);
            $stmt->execute();
            $_SESSION['success_message'] = 'PC supprimé avec succès';
            
        } catch (PDOException $e) {
            $_SESSION['error_message'] = 'Erreur: ' . $e->getMessage();
        }
        
        header("Location: Gestion_PC.php");
        exit;
    }
    
    // Ajout d'un PC
    else if (isset($_POST['ajouter_pc'])) {
        $imageUrl = handleImageUpload();
        
        if ($imageUrl !== false) {
            try {
                $query = 'INSERT INTO products (name, ssd, prix, carte_graphique, processeur, ram, url_image, 
                          boitier, carte_mere, refroidissement, os) 
                          VALUES (:name, :ssd, :prix, :carte_graphique, :processeur, :ram, :url_image, 
                          :boitier, :carte_mere, :refroidissement, :os)';
                $stmt = $dbh->prepare($query);
                
                $stmt->bindValue(':name', $_POST['namepc']);
                $stmt->bindValue(':ssd', $_POST['ssd']);
                $stmt->bindValue(':prix', $_POST['prix']);
                $stmt->bindValue(':carte_graphique', $_POST['carte_graphique']);
                $stmt->bindValue(':processeur', $_POST['processeur']);
                $stmt->bindValue(':ram', $_POST['ram']);
                $stmt->bindValue(':url_image', $imageUrl);
                $stmt->bindValue(':boitier', $_POST['boitier']);
                $stmt->bindValue(':carte_mere', $_POST['carte_mere']);
                $stmt->bindValue(':refroidissement', $_POST['refroidissement']);
                $stmt->bindValue(':os', $_POST['os']);
                
                $stmt->execute();
                $_SESSION['success_message'] = 'PC ajouté avec succès';
                
            } catch (PDOException $e) {
                $_SESSION['error_message'] = 'Erreur: ' . $e->getMessage();
            }
            
            header("Location: Gestion_PC.php");
            exit;
        }
    }
    
    // Modification d'un PC
    else if (isset($_POST['modifier_pc'])) {
        try {
            $queryUpdate = 'UPDATE products SET 
                name = :name, ssd = :ssd, prix = :prix, 
                carte_graphique = :carte_graphique, processeur = :processeur, 
                ram = :ram, boitier = :boitier, carte_mere = :carte_mere,
                refroidissement = :refroidissement, os = :os';
            
            $imageUrl = handleImageUpload();
            if ($imageUrl !== false && $imageUrl !== 'Image/default.png') {
                $queryUpdate .= ', url_image = :image';
            }
            
            $queryUpdate .= ' WHERE id = :id';
            $stmt = $dbh->prepare($queryUpdate);
            
            // Bind des valeurs
            $stmt->bindValue(':id', $_POST['modifier_pc'], PDO::PARAM_INT);
            $stmt->bindValue(':name', $_POST['namepc']);
            $stmt->bindValue(':ssd', $_POST['ssd']);
            $stmt->bindValue(':prix', $_POST['prix']);
            $stmt->bindValue(':carte_graphique', $_POST['carte_graphique']);
            $stmt->bindValue(':processeur', $_POST['processeur']);
            $stmt->bindValue(':ram', $_POST['ram']);
            $stmt->bindValue(':boitier', $_POST['boitier']);
            $stmt->bindValue(':carte_mere', $_POST['carte_mere']);
            $stmt->bindValue(':refroidissement', $_POST['refroidissement']);
            $stmt->bindValue(':os', $_POST['os']);
            
            if ($imageUrl !== false && $imageUrl !== 'Image/default.png') {
                $stmt->bindValue(':image', $imageUrl);
            }
            
            $stmt->execute();
            $_SESSION['success_message'] = 'PC modifié avec succès';
            
        } catch (PDOException $e) {
            $_SESSION['error_message'] = 'Erreur: ' . $e->getMessage();
        }
        
        header("Location: Gestion_PC.php");
        exit;
    }
}

// Construction du tableau de produits
$stmt = $dbh->prepare('SELECT * FROM products');
$stmt->execute();

$table = '<tr>
    <th>Id</th>
    <th>Nom du pc</th>
    <th>SSD</th>
    <th>Prix</th>
    <th>Carte Graphique</th>
    <th>Processeur</th>
    <th>RAM</th>
    <th>Boîtier</th>
    <th>Carte Mère</th>
    <th>Refroidissement</th>
    <th>OS</th>
    <th>Image</th>
    <th>Modifier</th>
    <th>Supprimer</th>
</tr>';

if ($stmt->rowCount() > 0) {
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $table .= '<tr>';
        $table .= '<td>'.$row['id'].'</td>';
        $table .= '<td>'.$row['name'].'</td>';
        $table .= '<td>'.$row['ssd'].'</td>';
        $table .= '<td>'.$row['prix'].' €</td>';
        $table .= '<td>'.$row['carte_graphique'].'</td>';
        $table .= '<td>'.$row['processeur'].'</td>';
        $table .= '<td>'.$row['ram'].'</td>';
        $table .= '<td>'.$row['boitier'].'</td>';
        $table .= '<td>'.$row['carte_mere'].'</td>';
        $table .= '<td>'.$row['refroidissement'].'</td>';
        $table .= '<td>'.$row['os'].'</td>';
        $table .= '<td><img src="'.$row['url_image'].'" class="miniature"></td>';
        $table .= '<td>
            <form method="POST">
                <input type="hidden" name="edit_id" value="'.$row['id'].'">
                <button type="submit" class="btn-edit"><i class="fas fa-edit"></i></button>
            </form>
        </td>';
        $table .= '<td>
            <form method="POST" onsubmit="return confirmDeletion();">
                <input type="hidden" name="supprimer_pc" value="'.$row['id'].'">
                <button type="submit" class="btn-delete"><i class="fas fa-trash"></i></button>
            </form>
        </td>';
        $table .= '</tr>';
    }
} else {
    $table .= '<tr><td colspan="14">Aucun produit trouvé.</td></tr>';
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/gestion_pc.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="icon" type="image/png" href="Image/logo.png">
    <title>GESTION PC</title>
</head>
<body>

<header class="header">
    <div class="header-content">
        <a href="index.php" class="logo-link">
            <img src="Image/logo.png" alt="Logo ReadyPC" class="logo">
        </a>

        <!-- Menu de navigation classique -->
        <nav class="main-nav">
            <ul>
                <li><a href="index.php">Accueil</a></li>
                <?php
                if (isset($_SESSION['access_level']) && ($_SESSION['access_level'] == 2 || $_SESSION['access_level'] == 1)) {
                    echo '<li><a href="Deconnection.php">Se déconnecter</a></li>';
                } else {
                    echo '<li><a href="login.php">Se connecter</a></li>';
                    echo '<li><a href="signup.php">S\'inscrire</a></li>';
                }
                ?>
            </ul>
        </nav>
    </div>
</header>

<main>
    <div class="form-container">
        <center>
            <?php if ($success_message): ?>
                <p class="success-message"><?= $success_message ?></p>
            <?php endif; ?>
            
            <?php if ($error_message): ?>
                <p class="error-message"><?= $error_message ?></p>
            <?php endif; ?>
            
            <h1 class="page-title"><?= $edit_mode ? 'Modifier le PC' : 'Ajouter un PC' ?></h1>
        </center>    
        
        <section class="form-section">
            <form method="post" action="Gestion_PC.php" enctype="multipart/form-data">
                <?php if ($edit_mode): ?>
                    <input type="hidden" name="modifier_pc" value="<?= $pc_data['id'] ?>">
                <?php else: ?>
                    <input type="hidden" name="ajouter_pc" value="1">
                <?php endif; ?>
                
                <div class="form-wrapper">
                    <div class="form-group">
                        <label>Nom du PC</label>
                        <input type="text" name="namepc" value="<?= htmlspecialchars($pc_data['name']) ?>" required>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>SSD</label>
                            <input type="text" name="ssd" value="<?= htmlspecialchars($pc_data['ssd']) ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Prix (€)</label>
                            <input type="number" name="prix" value="<?= htmlspecialchars($pc_data['prix']) ?>" min="0" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Carte Graphique</label>
                            <input type="text" name="carte_graphique" value="<?= htmlspecialchars($pc_data['carte_graphique']) ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Processeur</label>
                            <input type="text" name="processeur" value="<?= htmlspecialchars($pc_data['processeur']) ?>" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>RAM</label>
                            <input type="text" name="ram" value="<?= htmlspecialchars($pc_data['ram']) ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Boîtier</label>
                            <input type="text" name="boitier" value="<?= htmlspecialchars($pc_data['boitier']) ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Carte Mère</label>
                            <input type="text" name="carte_mere" value="<?= htmlspecialchars($pc_data['carte_mere']) ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Refroidissement CPU</label>
                            <input type="text" name="refroidissement" value="<?= htmlspecialchars($pc_data['refroidissement']) ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Système d'exploitation</label>
                            <input type="text" name="os" value="<?= htmlspecialchars($pc_data['os']) ?>" required>
                        </div>
                        
                        <div class="form-group full-width">
                            <label>Image</label>
                            <input type="file" name="image" accept="image/jpeg, image/png, image/jpg, image/webp"> 
                        </div>
                    </div>

                    <div class="form-submit">
                        <input type="submit" value="<?= $edit_mode ? 'Modifier le PC' : 'Ajouter le PC' ?>">
                    </div>
                </div>
            </form>
        </section>
    </div>

    <div class="table-container">
        <section class="table-section">
            <h2>Produits dans la db</h2>
            <div class="custom-table-container">
                <table class="custom-table">
                    <?= $table ?>
                </table>
            </div>
        </section>
    </div>
</main>
<script>
    function confirmDeletion() {
        return confirm("Êtes-vous sûr de vouloir supprimer ce PC ?");
    }
</script>
</body>
</html>
