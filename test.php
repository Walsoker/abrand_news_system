<?php
// test.php - Pour tester la connexion
echo "<h1>Test Connexion DB</h1>";

@include('config/db_connect.php');

if (isset($conn)) {
    echo "<p style='color:green;'>✅ Connexion DB OK</p>";
    
    // Test table
    try {
        $result = $conn->query("SELECT COUNT(*) as count FROM articles");
        $data = $result->fetch();
        echo "<p>📊 Table 'articles' : " . $data['count'] . " article(s)</p>";
        
        // Afficher les articles
        $articles = $conn->query("SELECT id, h1_titre, score_aleph, statut FROM articles ORDER BY id DESC LIMIT 5");
        echo "<h3>Derniers articles :</h3>";
        echo "<ul>";
        foreach ($articles as $article) {
            echo "<li>#" . $article['id'] . " - " . htmlspecialchars($article['h1_titre']) . 
                 " (" . $article['score_aleph'] . "%) - Statut: " . $article['statut'] . "</li>";
        }
        echo "</ul>";
        
    } catch (Exception $e) {
        echo "<p style='color:red;'>❌ Erreur table: " . $e->getMessage() . "</p>";
    }
} else {
    echo "<p style='color:red;'>❌ Connexion DB échouée</p>";
}
?>