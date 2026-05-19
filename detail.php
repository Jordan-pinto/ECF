<?php
session_start();
require_once 'EcoRideBack/db.php';

$id_covoiturage = $_GET['id'] ?? 0;
$message = '';

// 1. GESTION DE LA RÉSERVATION (Si l'utilisateur a cliqué sur "Participer")
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['participer'])) {
    
    // Vérifier s'il est bien connecté
    if (!isset($_SESSION['utilisateur_id'])) {
        $message = '<div class="alert alert-warning">Vous devez être connecté pour participer. <a href="connexion.php">Se connecter</a></div>';
    } else {
        $id_user = $_SESSION['utilisateur_id'];
        
        // On vérifie s'il y a de la place et si l'utilisateur a assez d'argent
        $stmt = $pdo->prepare("SELECT c.nb_place, c.prix_personne, u.credits 
                               FROM covoiturage c 
                               JOIN utilisateur u ON u.utilisateur_id = :id_user 
                               WHERE c.covoiturage_id = :id_cov");
        $stmt->execute([':id_user' => $id_user, ':id_cov' => $id_covoiturage]);
        $check = $stmt->fetch();

        if ($check && $check['nb_place'] > 0 && $check['credits'] >= $check['prix_personne']) {
            
            // LA TRANSACTION : C'est ultra sécurisé, soit tout s'exécute, soit rien !
            $pdo->beginTransaction();
            try {
                // A. On retire l'argent à l'utilisateur
                $pdo->prepare("UPDATE utilisateur SET credits = credits - :prix WHERE utilisateur_id = :id_user")
                    ->execute([':prix' => $check['prix_personne'], ':id_user' => $id_user]);
                
                // B. On enlève 1 place dans la voiture
                $pdo->prepare("UPDATE covoiturage SET nb_place = nb_place - 1 WHERE covoiturage_id = :id_cov")
                    ->execute([':id_cov' => $id_covoiturage]);
                
                // C. On ajoute l'utilisateur dans la table "participe"
                $pdo->prepare("INSERT INTO participe (utilisateur_id, covoiturage_id) VALUES (:id_user, :id_cov)")
                    ->execute([':id_user' => $id_user, ':id_cov' => $id_covoiturage]);
                
                $pdo->commit(); // On valide tout !
                
                // On met à jour la session pour que l'affichage en haut change direct
                $_SESSION['credits'] -= $check['prix_personne'];
                $message = '<div class="alert alert-success">Réservation confirmée ! Bon voyage ! 🚗</div>';
                
            } catch (Exception $e) {
                $pdo->rollBack(); // En cas de plantage, on annule tout
                $message = '<div class="alert alert-danger">Une erreur est survenue lors de la réservation.</div>';
            }
        } else {
            $message = '<div class="alert alert-danger">Réservation impossible : fonds insuffisants ou trajet complet.</div>';
        }
    }
}

// 2. RÉCUPÉRATION DES INFOS DU TRAJET POUR L'AFFICHAGE
$sql = "SELECT c.*, u.pseudo, v.modele, v.energie, m.libelle as marque 
        FROM covoiturage c
        INNER JOIN voiture v ON c.voiture_id = v.voiture_id
        INNER JOIN marque m ON v.marque_id = m.marque_id
        INNER JOIN utilisateur u ON v.utilisateur_id = u.utilisateur_id
        WHERE c.covoiturage_id = :id";
$stmt = $pdo->prepare($sql);
$stmt->execute([':id' => $id_covoiturage]);
$trajet = $stmt->fetch();

if (!$trajet) {
    die("Trajet introuvable !");
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détail du trajet - EcoRide</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .bg-ecolo { background-color: #2e7d32; }
    </style>
</head>
<body class="d-flex flex-column min-vh-100 bg-light">

    <nav class="navbar navbar-expand-lg navbar-dark bg-ecolo">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">🌱 EcoRide</a>
            <div class="ms-auto text-light">
                <?php if (isset($_SESSION['pseudo'])): ?>
                    <span>👋 <?= htmlspecialchars($_SESSION['pseudo']) ?> (<?= $_SESSION['credits'] ?> crédits)</span>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <main class="container my-5 flex-grow-1">
        
        <div class="row justify-content-center">
            <div class="col-md-8">
                
                <?= $message ?>

                <div class="card shadow-sm border-0 p-4">
                    <h2 class="mb-4 text-center">Voyage avec <?= htmlspecialchars($trajet['pseudo']) ?></h2>
                    
                    <div class="row mb-4 text-center">
                        <div class="col-6">
                            <h5 class="text-muted">Départ</h5>
                            <p class="fs-4 fw-bold text-success"><?= htmlspecialchars($trajet['lieu_depart']) ?></p>
                            <p><?= htmlspecialchars($trajet['heure_depart']) ?></p>
                        </div>
                        <div class="col-6">
                            <h5 class="text-muted">Arrivée</h5>
                            <p class="fs-4 fw-bold text-success"><?= htmlspecialchars($trajet['lieu_arrivee']) ?></p>
                            <p><?= htmlspecialchars($trajet['heure_arrivee']) ?></p>
                        </div>
                    </div>

                    <hr>

                    <div class="mb-4">
                        <h5>Détails du véhicule :</h5>
                        <ul>
                            <li><strong>Véhicule :</strong> <?= htmlspecialchars($trajet['marque']) ?> <?= htmlspecialchars($trajet['modele']) ?></li>
                            <li><strong>Énergie :</strong> <?= htmlspecialchars($trajet['energie']) ?></li>
                        </ul>
                    </div>

                    <div class="d-flex justify-content-between align-items-center bg-light p-3 rounded">
                        <span class="fs-5">Places restantes : <strong><?= htmlspecialchars($trajet['nb_place']) ?></strong></span>
                        <span class="fs-4 fw-bold text-success"><?= htmlspecialchars($trajet['prix_personne']) ?> Crédits</span>
                    </div>

                    <form action="detail.php?id=<?= $trajet['covoiturage_id'] ?>" method="POST" class="mt-4">
                        <?php if ($trajet['nb_place'] > 0): ?>
                            <button type="submit" name="participer" class="btn btn-success w-100 py-3 fs-5 fw-bold" onclick="return confirm('Confirmer la réservation et débiter les crédits ?');">
                                Je participe !
                            </button>
                        <?php else: ?>
                            <button class="btn btn-secondary w-100 py-3 fs-5" disabled>Complet</button>
                        <?php endif; ?>
                    </form>

                </div>
            </div>
        </div>
    </main>

</body>
</html>
