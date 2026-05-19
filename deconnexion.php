<?php
session_start();
session_destroy(); // On détruit toutes les données de session (adieu pseudo et crédits en mémoire)
header("Location: index.php"); // On renvoie vers l'accueil
exit();
