<?php
session_start();
require_once('db_connection.php');
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>403 - Accès refusé</title>
  <link rel="stylesheet" href="css/403.css">
</head>
<body>
  <center>
    <h1>403 Accès refusé</h1>
    <a href="index.php"><img src="Image/logout.jpg" alt="Déconnexion réussie" class="logout-icon"></a>
  </center>
  
  <script>
      document.addEventListener("DOMContentLoaded", function() {
    const emojis = ["🚫", "⛔", "🚷", "🔒", "👮‍♂️", "🛑", "❌", "🚨"];
    
    // Créer 20 emojis qui tombent
    for (let i = 0; i < 20; i++) {
      const emoji = document.createElement("div");
      emoji.className = "emoji";
      emoji.textContent = emojis[Math.floor(Math.random() * emojis.length)];
      emoji.style.left = Math.random() * 100 + "vw";
      emoji.style.animationDuration = 5 + Math.random() * 10 + "s";
      emoji.style.animationDelay = Math.random() * 5 + "s";
      document.body.appendChild(emoji);
    }
  });
  </script>
</body>
</html>
