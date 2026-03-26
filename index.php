<?php
session_start();
require_once 'config.php'; 

$message_status = $_SESSION['message_status'] ?? "";
if (isset($_SESSION['message_status'])) {
    unset($_SESSION['message_status']);
}

if (isset($_POST['inscription'])) {
    
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
if (isset($_POST['connexion'])) {
    
    $nom = trim($_POST['utilisateur'] ?? '');
    $mdp = $_POST['mdp'] ?? '';

    if ($nom === '' || $mdp === '') {
        $message_status = "Veuillez fournir un nom d'utilisateur et un mot de passe.";
    } else {
        try {
            $req = $database->prepare("SELECT mdp FROM utilisateurs WHERE nom = :nom");
            $req->execute(['nom' => $nom]);
            $utilisateur = $req->fetch(PDO::FETCH_ASSOC);

            if ($utilisateur) {
                $pythonPath = "C:\\Users\\loisl\\AppData\\Local\\Programs\\Python\\Python38\\python.exe";
                $commande = "$pythonPath script.py check " . escapeshellarg($mdp) . " " . escapeshellarg($utilisateur['mdp']) . " 2>&1";
                $sortie = shell_exec($commande);
                
                if (trim($sortie) === 'Mot de passe correct') {
                    $_SESSION['message_status'] = htmlspecialchars($nom, ENT_QUOTES, 'UTF-8') . " est connecté";
                    header('Location: index.php');
                    exit;
                } else {
                    $message_status = "Mot de passe incorrect.";
                }
            } else {
                $message_status = "Utilisateur non trouvé.";
            }
        } catch (Exception $e) {
            $message_status = "Erreur BDD : " . $e->getMessage();
        }
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
    <?php if (!empty($message_status)): ?>
        <div style="color: #ff6b6b; margin-bottom: 20px; font-weight: bold;">
            <?php echo $message_status; ?>
        </div>
    <?php endif; ?>
    <div class="formulaire">
    <h1>Inscription</h1>
    <form class="form" method="POST">
        <label for="">Nom d'utilisateur</label>
            <input type="text" name="utilisateur" required>
        <br>
        <label for="">Mot de passe</label>
            <input type="password" name="mdp" required>
        <br>
        <button type="submit" name="inscription">S'inscrire</button>
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
        <button type="submit" name="connexion">Se connecter</button>
    </form>
    </div>
</body>
</html>