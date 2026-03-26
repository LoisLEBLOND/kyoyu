<?php
session_start();
require_once "config.php";

$pythonPath = "C:\\Python314\\python.exe";
#mon chemin python est cassé donc je force

if (isset($_GET["logout"])) {
    session_destroy();
    header("Location: index.php");
    exit;
} 

$message_status = $_SESSION["message_status"] ?? "";
if (isset($_SESSION["message_status"])) {
    unset($_SESSION["message_status"]);
}

if (isset($_POST["inscription"])) {
    $nom = trim($_POST["utilisateur"] ?? "");
    $mdp = $_POST["mdp"] ?? "";

    $commande = "$pythonPath script.py hash " . escapeshellarg($mdp) . " 2>&1";
    $sortie = shell_exec($commande);
    
    $infos = explode("|", trim($sortie));
    
    if (count($infos) === 2) {
        $mdp_hache = trim($infos[0]);
        $user_uuid = trim($infos[1]);
        
        try {
            $req = $database->prepare("INSERT INTO utilisateurs (nom, mdp, uuid) VALUES (:nom, :mdp, :uuid)");
            $req->execute([
                "nom" => $nom,
                "mdp" => $mdp_hache,
                "uuid" => $user_uuid
            ]);
            $_SESSION["message_status"] = htmlspecialchars($nom) . " est bien enregistré";
            header("Location: index.php");
            exit;
        } catch (Exception $e) {
            $message_status = "Erreur BDD : " . $e->getMessage();
        }
    } else {
        $message_status = "Erreur Python : " . htmlspecialchars($sortie);
    }
}
if (isset($_POST["connexion"])) {
    
    $nom = trim($_POST["utilisateur"] ?? "");
    $mdp = $_POST["mdp"] ?? "";

    if ($nom === "" || $mdp === "") {
        $message_status = "Veuillez fournir un nom d'utilisateur et un mot de passe.";
    } else {
        try {
            $req = $database->prepare("SELECT mdp, uuid FROM utilisateurs WHERE nom = :nom");
            $req->execute(["nom" => $nom]);
            $utilisateur = $req->fetch(PDO::FETCH_ASSOC);

            if ($utilisateur) {
                $commande = "$pythonPath script.py check " . escapeshellarg($mdp) . " " . escapeshellarg($utilisateur["mdp"]) . " 2>&1";
                $sortie = shell_exec($commande);
                
                if (trim($sortie) === "Mot de passe correct") {
                    $_SESSION["user_uuid"] = $utilisateur["uuid"];
                    $_SESSION["user_nom"] = htmlspecialchars($nom, ENT_QUOTES, "UTF-8");
                    $_SESSION["message_status"] = $_SESSION["user_nom"] . " est connecté";
                    header("Location: index.php");
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

if (isset($_POST["send_message"]) && isset($_SESSION["user_uuid"])) {
    $contenu = trim($_POST["contenu"] ?? "");
    $stmt = $database->prepare("SELECT cle_chiffrement FROM groupes WHERE id = 1");
    $stmt->execute();
    $groupe = $stmt->fetch();
    $cle = $groupe['cle_chiffrement'];
    $commande = "$pythonPath script.py encrypt " . escapeshellarg($contenu) . " " . escapeshellarg($cle) . " 2>&1";
    $resultat_python = shell_exec($commande);
$contenu_chiffre = ($resultat_python !== null) ? trim($resultat_python) : ""; 

if (empty($contenu_chiffre)) {
    $message_status = "Erreur : Le chiffrement du message a échoué.";
} else {
    $req = $database->prepare("INSERT INTO messages (uuid, groupe_id, contenu, `date/heure`) VALUES (:uuid, :groupe_id, :contenu, NOW())");
    $req->execute([
        "uuid" => $_SESSION["user_uuid"],
        "groupe_id" => 1,
        "contenu" => $contenu_chiffre
    ]);
}
}

$message_sent = $_SESSION["message_sent"] ?? "";
if (isset($_SESSION["message_sent"])) {
    unset($_SESSION["message_sent"]);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kyōyū</title>
    <link rel="stylesheet" href="style.css">
    <link rel="icon" type="image/vnd.icon" href="images/japon.ico">
</head>
<body>
    <img src="images/japon.jpg" alt="Kyōyū" style="display: block; margin: 20px auto; width: 150px; height: auto; border-radius: 50%; border: 3px solid #ff0000;">
    <h1>OHAYŌ, BIENVENUE SUR KYŌYŪ(共有) - DESU</h1>
    <?php if (!empty($message_status)): ?>
        <div style="color: #ff6b6b; margin-bottom: 20px; font-weight: bold;">
            <?php echo $message_status; ?>
        </div>
    <?php endif; ?>
    
    <?php if (!empty($message_sent)): ?>
        <div style="color: #51cf66; margin-bottom: 20px; font-weight: bold;">
            <?php echo $message_sent; ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION["user_uuid"])): ?>
        <div class="formulaire">
            <h1>Déconnexion</h1>
            <p>Connecté en tant que : <strong><?php echo $_SESSION["user_nom"]; ?></strong></p>
            <a href="?logout=1" style="color: #007bff; text-decoration: none;">Se déconnecter</a>
        </div>

        <div class="formulaire">
            <h1>Envoyer un message</h1>
            <form class="form" method="POST">
                <label for="contenu">Votre message</label>
                <textarea name="contenu" required style="padding: 10px; border: none; border-radius: 5px; min-height: 80px; font-family: Arial, sans-serif;"></textarea>
                <br>
                <button type="submit" name="send_message">Envoyer</button>
            </form>
        </div>

        <div class="formulaire">
            <h1>Messages</h1>
            <div style="min-height: 200px; max-height: 400px; overflow-y: auto; border: 1px solid #555; border-radius: 5px; padding: 10px;">
                <?php
                try {
                    $req = $database->prepare("SELECT m.contenu, m.`date/heure`, u.nom FROM messages m JOIN utilisateurs u ON m.uuid = u.uuid WHERE m.groupe_id = 1 ORDER BY m.`date/heure` ASC");
                    $req->execute();
                    $messages = $req->fetchAll(PDO::FETCH_ASSOC);
                    
                    if (empty($messages)) {
                        echo "<p style=\"color: #aaa; text-align: center;\">Aucun message pour le moment...</p>";
                    } else {
                        foreach ($messages as $msg) {
                            $commande_dec = "$pythonPath script.py decrypt " . escapeshellarg($msg["contenu"]) . " " . escapeshellarg($cle) . " 2>&1";
                            $message_clair = trim(shell_exec($commande_dec));
                            echo "<div style=\"margin-bottom: 15px; padding: 10px; background-color: #444; border-radius: 5px; text-align: left;\">";
                            echo "<strong style=\"color: #007bff;\">" . htmlspecialchars($msg["nom"], ENT_QUOTES, "UTF-8") . "</strong>";
                            echo "<span style=\"color: #aaa; font-size: 0.9em; margin-left: 10px;\">" . $msg["date/heure"] . "</span>";
                            echo "<p style=\"margin-top: 5px; margin-bottom: 0;\">" . nl2br(htmlspecialchars($message_clair, ENT_QUOTES, "UTF-8")) . "</p>";
                            echo "</div>";
                    }
                }}catch (Exception $e) {
                    echo "<p style=\"color: #ff6b6b;\">Erreur : " . $e->getMessage() . "</p>";
                }
                ?>
            </div>
        </div>
    <?php else: ?>
        <div class="formulaire">
        <h1>Inscription</h1>
        <form class="form" method="POST">
            <label for="">Nom d"utilisateur</label>
                <input type="text" name="utilisateur" required>
            <br>
            <label for="">Mot de passe</label>
                <input type="password" name="mdp" required>
            <br>
            <button type="submit" name="inscription">S"inscrire</button>
        </form>
        </div>
        <div class="formulaire">
        <h1>Connexion</h1>
        <form class="form" method="POST">
            <label for="">Nom d"utilisateur</label>
                <input type="text" name="utilisateur" required>
            <br>
            <label for="">Mot de passe</label>
                <input type="password" name="mdp" required>
            <br>
            <button type="submit" name="connexion">Se connecter</button>
        </form>
        </div>
    <?php endif; ?>
</body>
</html>