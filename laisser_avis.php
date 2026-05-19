<?php
session_start();
require_once 'EcoRideBack/db.php';

// Si on n'est pas connecté, dehors !
if (!isset($_SESSION['utilisateur_id'])) {
    header("Location: connexion.php");
    exit();
}

$id_chauffeur = $_GET['chauffeur_id'] ?? 0;
$message = '';

// --- TRAITEMENT DU FORMULAIRE ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['soumettre_avis'])) {
    $note = (int)$_POST['note'];
    $commentaire = $_POST['commentaire'];
    $auteur_id = $_SESSION['utilisateur_id'];
    $chauffeur_post = $_POST['chauffeur_id']; // Récupéré depuis le champ caché

    try {
        $sql = "INSERT INTO avis (commentaire, note, statut, auteur_id, chauffeur_id) 
                VALUES (:commentaire, :note, 'Validé', :auteur_id, :chauffeur_id)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':commentaire' => $commentaire,
            ':note' => $note,
            ':auteur_id' => $auteur_id,
            ':chauffeur_id' => $chauffeur_post
        ]);
        $message = '<div class="alert alert-success fw-bold text-center">Merci pour ton retour ! Ton avis a bien été publié. ⭐</div>';
    } catch (Exception $e) {
        $message = '<div class="alert alert-danger">Erreur lors de l\'envoi de l\'avis.</div>';
    }
}

// --- RÉCUPÉRATION DU PSEUDO DU CHAUFFEUR ---
$stmt_chauffeur = $pdo->prepare("SELECT pseudo FROM utilisateur WHERE utilisateur_id = :id");
$stmt_chauffeur->execute([':id' => $id_chauffeur]);
$chauffeur = $stmt_chauffeur->fetch();

// Si le chauffeur n'existe pas et qu'on n'a pas posté de formulaire, on bloque
if (!$chauffeur && empty($_POST)) {
    die("Chauffeur introuvable !");
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laisser un avis - EcoRide</title>
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
                <a href="profil.php" class="btn btn-outline-light btn-sm">Retour à mon espace</a>
            </div>
        </div>
    </nav>

    <main class="container my-5 flex-grow-1 d-flex justify-content-center align-items-center">
        <div class="card shadow-sm border-0 p-4" style="max-width: 500px; width: 100%;">
            
            <?= $message ?>

            <?php if (empty($message)): ?>
                <h3 class="text-center text-success mb-4">Évaluer <?= htmlspecialchars($chauffeur['pseudo']) ?></h3>
                
                <form action="laisser_avis.php" method="POST">
                    
                    <input type="hidden" name="chauffeur_id" value="<?= htmlspecialchars($id_chauffeur) ?>">

                    <div class="mb-4 text-center">
                        <label class="form-label fw-bold">Note sur 5 étoiles</label>
                        <select name="note" class="form-select form-select-lg text-center mx-auto" style="max-width: 200px;" required>
                            <option value="5">⭐⭐⭐⭐⭐ (Parfait)</option>
                            <option value="4">⭐⭐⭐⭐ (Très bien)</option>
                            <option value="3">⭐⭐⭐ (Correct)</option>
                            <option value="2">⭐⭐ (Moyen)</option>
                            <option value="1">⭐ (À éviter)</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">Ton commentaire</label>
                        <textarea name="commentaire" class="form-control" rows="4" placeholder="Comment s'est passé le voyage ? La conduite était-elle écologique ?" required></textarea>
                    </div>

                    <button type="submit" name="soumettre_avis" class="btn btn-success w-100 fw-bold fs-5 py-2">Envoyer mon avis</button>
                </form>
            <?php endif; ?>
        </div>
    </main>

</body>
</html>