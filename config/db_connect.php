<?php
// config/db_connect.php
// NE METS PAS d'echo ou de HTML ici - uniquement la connexion

$host = 'localhost';
$dbname = 'abrand_news';  // Nom de ta base
$username = 'root';       // Ton utilisateur MySQL
$password = '';           // Ton mot de passe MySQL (vide par défaut sur XAMPP/WAMP)

try {
    // Connexion PDO avec paramètres optimisés
    $conn = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        array(
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        )
    );
    
    // Optionnel: débogage (à enlever en production)
    // error_log("Connexion DB réussie à " . date('Y-m-d H:i:s'));
    
} catch(PDOException $e) {
    // En développement, on affiche l'erreur
    die("<div style='background:#f8d7da;color:#721c24;padding:20px;margin:20px;border-radius:5px;'>
        <strong>ERREUR DE CONNEXION DB:</strong><br>
        " . htmlspecialchars($e->getMessage()) . "<br>
        <small>Vérifie: 1) MySQL est démarré, 2) La base 'abrand_news' existe, 3) Identifiants corrects</small>
    </div>");
}
?>