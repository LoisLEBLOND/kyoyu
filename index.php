<?php
require_once 'config.php'; 

$message_status = "";

if (isset($_POST['register'])) {
    
    $nom = $_POST['utilisateur'];
    $mdp = $_POST['mdp'];

    $commande = "py script.py hash " . escapeshellarg($mdp);
    $sortie = shell_exec($commande);
    
    $mdp_hache = trim($sortie);

    if (!empty($mdp_hache)) {
        try {
            $req = $database->prepare("INSERT INTO utilisateurs (nom, mdp) VALUES (:nom, :mdp)");
            $req->execute([
                'nom' => $nom,
                'mdp' => $mdp_hache
            ]);
            $message_status = "$nom est bien enregistré";
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
</body>
</html>