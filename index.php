<?php
session_start();
require_once 'config.php'; 

$message_status = $_SESSION['message_status'] ?? "";
if (isset($_SESSION['message_status'])) {
    unset($_SESSION['message_status']);
}

if (isset($_POST['register'])) {
    
    $nom = trim($_POST['utilisateur'] ?? '');
    $mdp = $_POST['mdp'] ?? '';

    $pythonPath = "C:\\Users\\loisl\\AppData\\Local\\Programs\\Python\\Python38\\python.exe";

    $commande = "$pythonPath script.py hash " . escapeshellarg($mdp) . " 2>&1";
    $sortie = shell_exec($commande);
    
    $mdp_hache = trim($sortie ?? '');

    if (!empty($mdp_hache) && stripos($mdp_hache, 'erreur') === false) {
        try {
            $req = $database->prepare("INSERT INTO utilisateurs (nom, mdp) VALUES (:nom, :mdp)");
            $req->execute([
                'nom' => $nom,
                'mdp' => $mdp_hache
            ]);
            header('Location: index.php');
            exit;
        } catch (Exception $e) {
            $message_status = "Erreur BDD : " . $e->getMessage();
        }
    } else {
        $message_status = "Erreur : Le script Python n'a pas répondu.";
    }
}
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
            <input type="text" name="utilisateur" required>
        <br>
        <label for="">Mot de passe</label>
            <input type="password" name="mdp" required>
        <br>
        <button type="submit" name="register">S'inscrire</button>
    </form>
    </div>
    <div class="formulaire">
    <h1>Connexion</h1>
    <form class="form" method="POST">
        <label for="">Nom d'utilisateur</label>
            <input type="text" name="utilisateur" required>
        <br>
        <label for="">Mot de passe</label>
            <input type="password" name="mdp" required>
        <br>
        <button type="submit" name="login">Se connecter</button>
    </form>
    </div>
</body>
</html>