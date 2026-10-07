<?php
// recup le fichier de config 
require_once('config.php');

// PDO 
// dont DSN => chaine de connection a la db
$dsn = "mysql:host={$hostServeur};dbname={$dbNom}";

try {
    $dbh = new PDO($dsn, $userServeur, $userPassword);
    $dbh->exec('SET NAMES utf8');

} catch (PDOException $e) {
    die('Erreur : '.$e->getMessage()); // -> permet d'acceder a l'attribut 
}

//echo 'Tout est OK';
?>