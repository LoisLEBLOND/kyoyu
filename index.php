<?php
require_once 'config.php';
if (isset($_POST['register'])) {
    $username = $_POST['utilisateur']; 
    $password = $_POST['mdp'];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kyōyū</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="formulaire">
    <h1>Inscription</h1>
    <form class="form" method="POST">
        <label for="">Nom d'utilisateur</label>
            <input type="text" name="username" required>
        <br>
        <label for="">Mot de passe</label>
            <input type="password" name="password" required>
        <br>
        <button type="submit">S'inscrire</button>
    </form>
    </div>
</body>
</html>