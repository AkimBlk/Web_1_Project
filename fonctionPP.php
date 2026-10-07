<?php
require_once('db_connection.php');


// Fonction pour récupérer l'image de profil en utilisant le login de la session
function PP($dbh, $login) {
    global $dbh; // Utiliser la variable PDO définie en dehors de la fonction

    $query10 = 'SELECT profile_picture FROM users WHERE login = :login';
    $stmt10 = $dbh->prepare($query10);
    $stmt10->bindValue(':login', $login);
    $stmt10->execute();
    
    return $stmt10->fetchColumn(); // Retourner l'image de profil
}

?>