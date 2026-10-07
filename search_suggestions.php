<?php
require_once('db_connection.php');

if (isset($_GET['query'])) {
    $query = trim($_GET['query']);
    $stmt = $dbh->prepare("SELECT id, name, url_image FROM products WHERE name LIKE :query LIMIT 5");
    $stmt->execute([':query' => '%' . $query . '%']);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($results);
}
?>
