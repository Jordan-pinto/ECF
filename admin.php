<?php
session_start();
require_once 'EcoRideBack/db.php';

// Si l'utilisateur n'est pas connecté du tout
if (!isset($_SESSION['utilisateur_id'])) {
    header("Location: index.php");
    exit();
}

// 🛑 LE VIGILE : On vérifie les rôles de l'utilisateur dans les bonnes tables
$stmt_role = $pdo->prepare("
    SELECT r.libelle 
    FROM utilisateur_role ur
    JOIN role r ON ur.role_id = r.role_id
    WHERE ur.utilisateur_id = :id_user
");
$stmt_role->execute([':id_user' => $_SESSION['utilisateur_id']]);
$roles_user = $stmt_role->fetchAll(PDO::FETCH_COLUMN);

// On parcourt ses rôles pour voir s'il a le droit d'entrer
$est_autorise = false;
foreach ($roles_user as $role) {
    if (in_array(strtolower($role), ['employe', 'admin', 'administrateur'])) {
        $est_autorise = true;
        // On sauvegarde son rôle exact dans la session pour l'afficher en haut
        $_SESSION['role_actif'] = $role;
        break;
    }
}

// MODE DÉBOGAGE : On désactive la redirection pour voir ce qui cloche
if (!$est_autorise) {
    echo "<h3>🚨 Mode Débogage Activé</h3>";
    echo "Ton ID de session est : " . (isset($_SESSION['utilisateur_id']) ? $_SESSION['utilisateur_id'] : "NON CONNECTÉ") . "<br>";
    echo "Les rôles que la base de données te trouve sont : <pre>";
    print_r($roles_user);
    echo "</pre>";
    die("Le code s'arrête ici. Qu'est-ce qui s'affiche juste au-dessus ?");
}


$message = '';

// --- TRAITEMENT : SUPPRESSION D'UN AVIS ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['supprimer_avis'])) {
    $id_avis_a_supprimer = $_POST['avis_id'];
    
    try {
        $stmt = $pdo->prepare("DELETE FROM avis WHERE avis_id = :id");
        $stmt->execute([':id' => $id_avis_a_supprimer]);
        $message = '<div class="alert alert-success">Le coup de balai a été donné ! L\'avis a été supprimé. 🧹</div>';
    } catch (Exception $e) {
        $message = '<div class="alert alert-danger">Erreur lors de la suppression.</div>';
    }
}

// --- RÉCUPÉRATION DE TOUS LES AVIS DE LA PLATEFORME ---
$sql = "SELECT a.*, u_auteur.pseudo as nom_auteur, u_chauffeur.pseudo as nom_chauffeur 
        FROM avis a
        JOIN utilisateur u_auteur ON a.auteur_id = u_auteur.utilisateur_id
        JOIN utilisateur u_chauffeur ON a.chauffeur_id = u_chauffeur.utilisateur_id
        ORDER BY a.avis_id DESC";
$stmt_avis = $pdo->query($sql);
$tous_les_avis = $stmt_avis->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administration - EcoRide</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .bg-admin { background-color: #1a237e; } /* Bleu foncé pour marquer la différence avec le vert public */
    </style>
</head>
<body class="d-flex flex-column min-vh-100 bg-light">

    <nav class="navbar navbar-expand-lg navbar-dark bg-admin shadow">
        <div class="container">
            <a class="navbar-brand fw-bold" href="admin.php">⚙️ EcoRide - Panel Employé</a>
            <div class="ms-auto">
                <span class="text-light me-3">Badge actif : <strong class="text-warning"><?= htmlspecialchars($_SESSION['role_actif']) ?></strong></span>
                <a href="index.php" class="btn btn-outline-light btn-sm me-2">Retour au site public</a>
            </div>
        </div>
    </nav>

    <main class="container my-5 flex-grow-1">
        
        <h2 class="mb-4">Modération des avis</h2>
        
        <?= $message ?>

        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Auteur</th>
                                <th>Chauffeur évalué</th>
                                <th>Note</th>
                                <th>Commentaire</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($tous_les_avis)): ?>
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">Aucun avis sur la plateforme pour le moment.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($tous_les_avis as $avis): ?>
                                    <tr>
                                        <td><?= $avis['avis_id'] ?></td>
                                        <td><span class="badge bg-secondary"><?= htmlspecialchars($avis['nom_auteur']) ?></span></td>
                                        <td><span class="badge bg-primary"><?= htmlspecialchars($avis['nom_chauffeur']) ?></span></td>
                                        <td>
                                            <span class="text-warning"><?= str_repeat('⭐', $avis['note']) ?></span>
                                        </td>
                                        <td><em>"<?= htmlspecialchars($avis['commentaire']) ?>"</em></td>
                                        <td class="text-end">
                                            <form action="admin.php" method="POST" class="d-inline">
                                                <input type="hidden" name="avis_id" value="<?= $avis['avis_id'] ?>">
                                                <button type="submit" name="supprimer_avis" class="btn btn-danger btn-sm" onclick="return confirm('Attention, cette suppression est définitive. Confirmer ?');">
                                                    🗑️ Supprimer
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </main>

</body>
</html>
