<?php
session_start();
require_once 'EcoRideBack/db.php';

if (!isset($_SESSION['utilisateur_id'])) {
    header("Location: connexion.php");
    exit();
}

$id_user = $_SESSION['utilisateur_id'];
$message_voiture = '';

// --- 1. TRAITEMENT DU FORMULAIRE : AJOUT D'UNE VOITURE ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajouter_voiture'])) {
    try {
        $sql = "INSERT INTO voiture (modele, immatriculation, energie, couleur, date_premiere_immatriculation, utilisateur_id, marque_id) 
                VALUES (:modele, :immatriculation, :energie, :couleur, :date_immat, :id_user, :marque_id)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':modele' => $_POST['modele'],
            ':immatriculation' => $_POST['immatriculation'],
            ':energie' => $_POST['energie'],
            ':couleur' => $_POST['couleur'],
            ':date_immat' => $_POST['date_immat'],
            ':id_user' => $id_user,
            ':marque_id' => $_POST['marque_id']
        ]);
        $message_voiture = '<div class="alert alert-success">Super ! Ta voiture a bien été ajoutée au garage. 🚗</div>';
    } catch (Exception $e) {
        $message_voiture = '<div class="alert alert-danger">Erreur lors de l\'ajout de la voiture.</div>';
    }
}

// --- 2. RÉCUPÉRATION DES DONNÉES POUR L'AFFICHAGE ---

// Infos de l'utilisateur
$stmt = $pdo->prepare("SELECT * FROM utilisateur WHERE utilisateur_id = :id");
$stmt->execute([':id' => $id_user]);
$user = $stmt->fetch();

// Historique des trajets (Passager)
$sql_historique = "SELECT c.*, u_chauffeur.pseudo as nom_chauffeur, v.modele, m.libelle as marque
                   FROM participe p
                   JOIN covoiturage c ON p.covoiturage_id = c.covoiturage_id
                   JOIN voiture v ON c.voiture_id = v.voiture_id
                   JOIN marque m ON v.marque_id = m.marque_id
                   JOIN utilisateur u_chauffeur ON v.utilisateur_id = u_chauffeur.utilisateur_id
                   WHERE p.utilisateur_id = :id_user ORDER BY c.date_depart DESC";
$stmt_histo = $pdo->prepare($sql_historique);
$stmt_histo->execute([':id_user' => $id_user]);
$reservations = $stmt_histo->fetchAll();

// Liste des marques pour le menu déroulant du formulaire
$stmt_marques = $pdo->query("SELECT * FROM marque ORDER BY libelle ASC");
$marques = $stmt_marques->fetchAll();

// Les voitures de l'utilisateur (Son garage)
$stmt_mes_voitures = $pdo->prepare("SELECT v.*, m.libelle as marque FROM voiture v JOIN marque m ON v.marque_id = m.marque_id WHERE v.utilisateur_id = :id_user");
$stmt_mes_voitures->execute([':id_user' => $id_user]);
$mes_voitures = $stmt_mes_voitures->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Espace - EcoRide</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .bg-ecolo { background-color: #2e7d32; }
    </style>
</head>
<body class="d-flex flex-column min-vh-100 bg-light">

    <nav class="navbar navbar-expand-lg navbar-dark bg-ecolo">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">🌱 EcoRide</a>
            <div class="ms-auto">
                <a href="index.php" class="btn btn-outline-light btn-sm me-2">Retour à l'accueil</a>
                <a href="deconnexion.php" class="btn btn-danger btn-sm">Déconnexion</a>
            </div>
        </div>
    </nav>

    <main class="container my-5 flex-grow-1">
        
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card shadow-sm border-0 bg-success text-white p-4 text-center rounded-3">
                    <h2>Bienvenue dans ton espace, <?= htmlspecialchars($user['pseudo']) ?> ! 🚀</h2>
                    <p class="fs-4 mt-2 mb-0">Solde actuel : <strong><?= $user['credits'] ?> Crédits</strong></p>
                </div>
            </div>
        </div>

        <div class="row">
                        <div class="col-md-6 mb-4">
                <h3 class="mb-3">Mes voyages à venir</h3>
                <?php if (empty($reservations)): ?>
                    <div class="alert alert-info">Tu n'as pas encore réservé de covoiturage.</div>
                <?php else: ?>
                    <?php foreach ($reservations as $trajet): ?>
                        <div class="card shadow-sm border-0 mb-3">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <h5 class="text-success fw-bold mb-0"><?= htmlspecialchars($trajet['lieu_depart']) ?> ➡️ <?= htmlspecialchars($trajet['lieu_arrivee']) ?></h5>
                                    <span class="badge bg-primary"><?= htmlspecialchars($trajet['statut']) ?></span>
                                </div>
                                <hr class="my-2">
                                <p class="mb-1"><strong>📅</strong> <?= htmlspecialchars($trajet['date_depart']) ?> à <?= htmlspecialchars($trajet['heure_depart']) ?></p>
                                <p class="mb-0"><strong>🚗</strong> <?= htmlspecialchars($trajet['marque']) ?> <?= htmlspecialchars($trajet['modele']) ?> avec <?= htmlspecialchars($trajet['nom_chauffeur']) ?></p>
                            </div>
                            <div class="card-footer bg-white border-0 text-end pt-0">
                                <button class="btn btn-outline-danger btn-sm">Annuler ma place</button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            </div>

            <div class="col-md-6 mb-4">
                <h3 class="mb-3">Mon Garage (Chauffeur)</h3>
                
                <?= $message_voiture ?>

                <div class="card shadow-sm border-0 mb-4 bg-white">
                    <div class="card-body">
                        <h5 class="card-title text-secondary mb-3">Ajouter un nouveau véhicule</h5>
                        <form action="profil.php" method="POST">
                            <div class="row g-2 mb-2">
                                <div class="col-md-6">
                                    <label class="form-label small">Marque</label>
                                    <select name="marque_id" class="form-select form-select-sm" required>
                                        <option value="">Choisir...</option>
                                        <?php foreach ($marques as $m): ?>
                                            <option value="<?= $m['marque_id'] ?>"><?= htmlspecialchars($m['libelle']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small">Modèle</label>
                                    <input type="text" name="modele" class="form-control form-control-sm" required>
                                </div>
                            </div>
                            <div class="row g-2 mb-2">
                                <div class="col-md-4">
                                    <label class="form-label small">Plaque</label>
                                    <input type="text" name="immatriculation" class="form-control form-control-sm" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small">Énergie</label>
                                    <select name="energie" class="form-select form-select-sm" required>
                                        <option value="Essence">Essence</option>
                                        <option value="Diesel">Diesel</option>
                                        <option value="Electrique">Electrique</option>
                                        <option value="Hybride">Hybride</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small">Couleur</label>
                                    <input type="text" name="couleur" class="form-control form-control-sm">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small">Date de première immatriculation</label>
                                <input type="date" name="date_immat" class="form-control form-control-sm" required>
                            </div>
                            <button type="submit" name="ajouter_voiture" class="btn btn-success btn-sm w-100">Enregistrer ma voiture</button>
                        </form>
                    </div>
                </div>

                <?php if (!empty($mes_voitures)): ?>
                    <h5 class="text-secondary">Mes véhicules enregistrés :</h5>
                    <ul class="list-group shadow-sm">
                        <?php foreach ($mes_voitures as $v): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <strong><?= htmlspecialchars($v['marque']) ?> <?= htmlspecialchars($v['modele']) ?></strong>
                                    <br>
                                    <small class="text-muted"><?= htmlspecialchars($v['immatriculation']) ?> - <?= htmlspecialchars($v['energie']) ?></small>
                                </div>
                                <?php if ($v['energie'] === 'Electrique'): ?>
                                    <span class="badge bg-success rounded-pill">🌱 Écolo</span>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>

            </div>
        </div>

    </main>

</body>
</html>
