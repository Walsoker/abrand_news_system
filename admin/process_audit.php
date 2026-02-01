<?php
// admin/process_audit.php
session_start();
require_once('../config/db_connect.php');

// Vérifier la méthode
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("ERREUR: Méthode non autorisée");
}

// Récupérer l'ID et le statut
$article_id = intval($_POST['id'] ?? 0);
$statut = $_POST['statut'] ?? 'publie';

// Validation
if ($article_id <= 0) {
    die("ERREUR: ID article invalide");
}

// Mise à jour du statut
try {
    $sql = "UPDATE articles SET statut = :statut WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ':statut' => $statut,
        ':id' => $article_id
    ]);
    
    // Vérifier si une ligne a été mise à jour
    if ($stmt->rowCount() > 0) {
        echo "SUCCES: Article #$article_id publié avec succès!";
        
        // Option: Rediriger vers la page publique
        // header('Location: ../public/index.php');
    } else {
        echo "ERREUR: Aucun article trouvé avec cet ID";
    }
    
} catch (PDOException $e) {
    die("ERREUR DB: " . $e->getMessage());
}
?>