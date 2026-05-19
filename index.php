<?php
session_start();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EcoRide - Covoiturage Écologique</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .bg-ecolo { background-color: #2e7d32; }
        .text-ecolo { color: #2e7d32; }
    </style>
</head>
<body class="d-flex flex-column min-vh-100">

    <nav class="navbar navbar-expand-lg navbar-dark bg-ecolo">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">🌱 EcoRide</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link active" href="index.php">Accueil</a></li>
                    <li class="nav-item"><a class="nav-link" href="#">Covoiturages</a></li>
                    <li class="nav-item"><a class="nav-link" href="#">Connexion</a></li>
                    <li class="nav-item"><a class="nav-link" href="#">Contact</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <main class="container my-5 flex-grow-1">
        
        <div class="row align-items-center mb-5">
            <div class="col-md-6">
                <h1 class="text-ecolo display-4 fw-bold">Voyagez vert, voyagez moins cher.</h1>
                <p class="lead">EcoRide est la nouvelle plateforme de covoiturage dédiée aux voyageurs soucieux de l'environnement. Privilégiez les véhicules électriques et réduisez votre empreinte carbone tout en faisant des économies !</p>
            </div>
            <div class="col-md-6 text-center">
                <img src="./image/imageAccueil.jpg" class="img-fluid rounded shadow" alt="voiture avec plusieurs personnes">
            </div>
        </div>

        <div class="card shadow-sm border-0 bg-light p-4">
            <h3 class="mb-4 text-center">Trouvez votre prochain itinéraire</h3>
            <form action="#" method="GET" class="row g-3 justify-content-center">
                <div class="col-md-4">
                    <input type="text" class="form-control form-control-lg" name="depart" placeholder="Ville de départ" required>
                </div>
                <div class="col-md-4">
                    <input type="text" class="form-control form-control-lg" name="arrivee" placeholder="Ville d'arrivée" required>
                </div>
                <div class="col-md-3">
                    <input type="date" class="form-control form-control-lg" name="date" required>
                </div>
                <div class="col-md-1">
                    <button type="submit" class="btn btn-success btn-lg w-100">🔍</button>
                </div>
            </form>
        </div>

    </main>

    <footer class="bg-dark text-light text-center py-4 mt-auto">
        <div class="container">
            <p class="mb-1">Contactez-nous : <a href="mailto:contact@ecoride.fr" class="text-light">contact@ecoride.fr</a></p>
            <p class="mb-0"><a href="#" class="text-secondary text-decoration-none">Mentions légales</a> | &copy; 2026 EcoRide</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
