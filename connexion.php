<?php
//Pour mémoriser l'utilisateur on démarre la session en premier
session_start();

//On inclut la session à la base de données
require_once 'EcoRideBack/db.php';

$message = ' ';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    //on va chercher l'utilisateur dans la base de données grâce à son email
    $sql = "SELECT * FROM utilisateur WHERE email = :email";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch(); //fetch récupere la ligne s'il la trouve

    //On verifie si l'utilisateur existe et si le mdp est correct
    if ($user && password_verify($password, $user['password'])) {
        //Si mdp correct, on sauvegarde l'utilisateur dans la session
        $_SESSION['utilisateur_id'] = $user['utilisateur_id'];
        $_SESSION['pseudo'] = $user['pseudo'];
        $_SESSION['role'] = $user['role']; //pour savoir plus tard si il est admin ou employé
        $_SESSION['credits'] = $user['credits']; //Pour garder son solde sous la main

        //rediriger l'utilisateur vers l'accueil
        header("Location: index.php");
        exit();
    } else {
        //Si erreur de frappe ou autre problème
        $message = '<div class="alert alert-danger">Adresse email ou mot de passe incorrect.</div>';
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - EcoRide</title>
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
        <div class="card shadow-sm border-0 p-4" style="max-width: 400px; width: 100%;">
            <h3 class="text-center mb-4 text-success">Bon retour parmi nous !</h3>
            
            <?= $message ?>

            <form action="connexion.php" method="POST">
                <div class="mb-3">
                    <label class="form-label">Adresse Email</label>
                    <input type="email" name="email" class="form-control" required>
                </div>

                <div class="mb-4">
                    <label class="form-label">Mot de passe</label>
                    <input type="password" name="password" class="form-control" required>
                </div>

                <button type="submit" class="btn btn-success w-100 mb-3">Me connecter</button>
                
                <div class="text-center mt-3">
                    <span class="text-muted">Pas encore de compte ?</span>
                    <br>
                    <a href="inscription.php" class="text-success text-decoration-none fw-bold">Créer un compte et recevoir 20 crédits</a>
                </div>
            </form>
        </div>
    </main>

</body>
</html>