<?php
session_start();
require_once 'EcoRideBack/db.php';

// On récupère les mots tapés dans la barre de recherche (s'ils existent)
$depart = $_GET['depart'] ?? '';
$arrivee = $_GET['arrivee'] ?? '';
$date = $_GET['date'] ?? '';

$resultats = []; // On prépare un tableau vide pour stocker les trajets trouvés

// Si l'utilisateur a bien rempli les 3 champs
if (!empty($depart) && !empty($arrivee) && !empty($date)) {
    
    // La fameuse requête avec des JOIN pour lier Covoiturage, Voiture et Utilisateur
    $sql = "SELECT c.*, u.pseudo, v.energie 
            FROM covoiturage c
            INNER JOIN voiture v ON c.voiture_id = v.voiture_id
            INNER JOIN utilisateur u ON v.utilisateur_id = u.utilisateur_id
            WHERE c.lieu_depart = :depart 
              AND c.lieu_arrivee = :arrivee 
              AND c.date_depart = :date
              AND c.nb_place > 0 
              AND c.statut = 'Ouvert'";
              
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':depart' => $depart,
        ':arrivee' => $arrivee,
        ':date' => $date
    ]);
    
    // fetchAll() permet de récupérer TOUS les trajets qui correspondent
    $resultats = $stmt->fetchAll();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Résultats de recherche - EcoRide</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .bg-ecolo { background-color: #2e7d32; }
        .text-ecolo { color: #2e7d32; }
    </style>
</head>
<body class="d-flex flex-column min-vh-100 bg-light">

    <nav class="navbar navbar-expand-lg navbar-dark bg-ecolo">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">🌱 EcoRide</a>
            <a href="index.php" class="btn btn-outline-light btn-sm">Retour à l'accueil</a>
        </div>
    </nav>

    <main class="container my-5 flex-grow-1">
        <h2 class="mb-4">Résultats pour : <?= htmlspecialchars($depart) ?> ➡️ <?= htmlspecialchars($arrivee) ?></h2>

        <?php if (empty($resultats)): ?>
            <div class="alert alert-warning">
                Aucun covoiturage disponible pour ce trajet à cette date. Essayez de modifier votre recherche !
            </div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($resultats as $trajet): ?>
                    <div class="col-md-6 mb-4">
                        <div class="card shadow-sm border-0">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h5 class="card-title mb-0">👤 <?= htmlspecialchars($trajet['pseudo']) ?></h5>
                                    <span class="badge bg-success fs-6"><?= htmlspecialchars($trajet['prix_personne']) ?> Crédits</span>
                                </div>
                                
                                <p class="mb-1"><strong>Départ :</strong> <?= htmlspecialchars($trajet['heure_depart']) ?> de <?= htmlspecialchars($trajet['lieu_depart']) ?></p>
                                <p class="mb-2"><strong>Arrivée :</strong> <?= htmlspecialchars($trajet['heure_arrivee']) ?> à <?= htmlspecialchars($trajet['lieu_arrivee']) ?></p>
                                
                                <div class="d-flex justify-content-between align-items-center mt-3">
                                    <span class="text-muted">💺 <?= htmlspecialchars($trajet['nb_place']) ?> places restantes</span>
                                    
                                    <?php if ($trajet['energie'] === 'Electrique'): ?>
                                        <span class="badge bg-success bg-opacity-75 text-white">🌱 Voyage Écologique</span>
                                    <?php endif; ?>
                                </div>

                                <hr>
                                <a href="detail.php?id=<?= $trajet['covoiturage_id'] ?>" class="btn btn-outline-success w-100">Voir le détail</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>

</body>
</html>
