<?php
session_start();
require_once 'EcoRideBack/db.php';

// Redirection si non connecté
if (!isset($_SESSION['utilisateur_id'])) {
    header("Location: connexion.php");
    exit();
}

$id_user = $_SESSION['utilisateur_id'];
$message = '';

// --- 1. TRAITEMENT DU FORMULAIRE DE PUBLICATION ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['publier'])) {
    try {
        $sql = "INSERT INTO covoiturage (date_depart, heure_depart, lieu_depart, date_arrivee, heure_arrivee, lieu_arrivee, statut, nb_place, prix_personne, voiture_id) 
                VALUES (:date_depart, :heure_depart, :depart, :date_arrivee, :heure_arrivee, :arrivee, 'Ouvert', :places, :prix, :voiture_id)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':date_depart' => $_POST['date_depart'],
            ':heure_depart' => $_POST['heure_depart'],
            ':depart' => $_POST['lieu_depart'],
            ':date_arrivee' => $_POST['date_arrivee'],
            ':heure_arrivee' => $_POST['heure_arrivee'],
            ':arrivee' => $_POST['lieu_arrivee'],
            ':places' => $_POST['nb_place'],
            ':prix' => $_POST['prix_personne'],
            ':voiture_id' => $_POST['voiture_id']
        ]);
        
        $message = '<div class="alert alert-success">Ton trajet est en ligne ! Prêt pour l\'aventure ? 🌍</div>';
    } catch (Exception $e) {
        $message = '<div class="alert alert-danger">Erreur lors de la publication : ' . $e->getMessage() . '</div>';
    }
}

// --- 2. RÉCUPÉRATION DES VOITURES DU CHAUFFEUR ---
$stmt_voitures = $pdo->prepare("SELECT v.*, m.libelle as marque FROM voiture v JOIN marque m ON v.marque_id = m.marque_id WHERE v.utilisateur_id = :id_user");
$stmt_voitures->execute([':id_user' => $id_user]);
$voitures = $stmt_voitures->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Publier un trajet - EcoRide</title>
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
                <a href="profil.php" class="btn btn-outline-light btn-sm me-2">Mon Espace</a>
                <a href="deconnexion.php" class="btn btn-danger btn-sm">Déconnexion</a>
            </div>
        </div>
    </nav>

    <main class="container my-5 flex-grow-1">
        <div class="row justify-content-center">
            <div class="col-md-8">
                
                <h2 class="text-success mb-4 text-center">Proposer un nouveau trajet</h2>
                
                <?= $message ?>

                <div class="card shadow-sm border-0 p-4">
                    
                    <?php if (empty($voitures)): ?>
                        <div class="alert alert-warning text-center border-0 shadow-sm p-4">
                            <h4 class="alert-heading">Oups ! 🚗💨</h4>
                            <p>Tu n'as pas encore de véhicule enregistré dans ton garage.</p>
                            <hr>
                            <a href="profil.php" class="btn btn-warning fw-bold">Aller dans mon espace pour ajouter une voiture</a>
                        </div>
                    <?php else: ?>
                        <form action="publier.php" method="POST">
                            
                            <h5 class="text-secondary mb-3">📍 L'itinéraire</h5>
                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label class="form-label">Ville de départ</label>
                                    <input type="text" name="lieu_depart" class="form-control" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Ville d'arrivée</label>
                                    <input type="text" name="lieu_arrivee" class="form-control" required>
                                </div>
                            </div>

                            <h5 class="text-secondary mb-3">⏱️ Les horaires</h5>
                            <div class="row g-3 mb-4">
                                <div class="col-md-3">
                                    <label class="form-label">Date départ</label>
                                    <input type="date" name="date_depart" class="form-control" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Heure départ</label>
                                    <input type="time" name="heure_depart" class="form-control" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Date arrivée</label>
                                    <input type="date" name="date_arrivee" class="form-control" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Heure arrivée</label>
                                    <input type="time" name="heure_arrivee" class="form-control" required>
                                </div>
                            </div>

                            <h5 class="text-secondary mb-3">🚗 Le véhicule & Le prix</h5>
                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label class="form-label">Véhicule utilisé</label>
                                    <select name="voiture_id" class="form-select" required>
                                        <?php foreach ($voitures as $v): ?>
                                            <option value="<?= $v['voiture_id'] ?>"><?= htmlspecialchars($v['marque']) ?> <?= htmlspecialchars($v['modele']) ?> (<?= htmlspecialchars($v['immatriculation']) ?>)</option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Places offertes</label>
                                    <input type="number" name="nb_place" class="form-control" min="1" max="6" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Prix (Crédits)</label>
                                    <input type="number" name="prix_personne" class="form-control" min="1" required>
                                </div>
                            </div>

                            <button type="submit" name="publier" class="btn btn-success w-100 py-2 fs-5 fw-bold">Publier mon trajet</button>
                        </form>
                    <?php endif; ?>

                </div>
            </div>
        </div>
    </main>

</body>
</html>
