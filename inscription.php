<?php
// Inclur le fichier de connexion à la base de données
require_once 'EcoRideBack/db.php';

$message = ''; //Afficher un message d'erreur ou succès

//Vérifier si le formulaire à été soumis
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nom = $_POST["nom"];
    $prenom = $_POST["prenom"];
    $pseudo = $_POST["pseudo"];
    $email = $_POST["email"];
    $password_clair = $_POST["password"];

    //sécurité, on le mdp avant de l'enregistrer
    $password_hache = password_hash($password_clair, PASSWORD_DEFAULT);

    try {
        //Préparation de la requette SQL pour éviter les injection SQL
        //Note : On ne précise pas les crédits, car mySQL les mettra par defaut comme on l'a configuré
        $sql = "INSERT INTO utilisateur (nom, prenom, pseudo, email, password)
        VALUES (:nom, :prenom, :pseudo, :email, :password)";

        $stmt = $pdo->prepare($sql);

        //Éxecution de la requête en lian les variable
        $stmt->execute([
            ':nom' => $nom,
            ':prenom' => $prenom,
            ':pseudo' => $pseudo,
            ':email' => $email,
            ':password' => $password_hache
        ]);

        $message = '<div class="alert alert-success">Inscription réussie ! Vous avez reçu 20 crédits.</div>';
    } catch (PDOException $e) {
        // Si l'email existe déjà, ça va déclencher une erreur car on a mis "UNIQUE" dans la base
        $message = '<div class="alert alert-danger">Erreur: cet email est déjà utilisé.</div>';
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - EcoRide</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .bg-ecolo { background-color: #2e7d32; }
    </style>
</head>
<body class="d-flex flex-column min-vh-100 bg-light">

    <nav class="navbar navbar-expand-lg navbar-dark bg-ecolo">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">🌱 EcoRide</a>
            <a href="index.php" class="btn btn-outline-light btn-sm">Retour à l'accueil</a>
        </div>
    </nav>

    <main class="container my-5 flex-grow-1 d-flex justify-content-center align-items-center">
        <div class="card shadow-sm border-0 p-4" style="max-width: 500px; width: 100%;">
            <h3 class="text-center mb-4 text-success">Rejoignez l'aventure</h3>
            
            <?= $message ?>

            <form action="inscription.php" method="POST">
                <div class="row g-2 mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Prénom</label>
                        <input type="text" name="prenom" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Nom</label>
                        <input type="text" name="nom" class="form-control" required>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Pseudo</label>
                    <input type="text" name="pseudo" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Adresse Email</label>
                    <input type="email" name="email" class="form-control" required>
                </div>

                <div class="mb-4">
                    <label class="form-label">Mot de passe</label>
                    <input type="password" name="password" class="form-control" required minlength="6">
                </div>

                <button type="submit" class="btn btn-success w-100">Créer mon compte (et recevoir 20 crédits)</button>
            </form>
        </div>
    </main>

</body>
</html>