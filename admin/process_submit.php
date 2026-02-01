<?php
// admin/process_submit.php
// Ce fichier reçoit les données de l'écran 1

// 1. Démarrer la session et inclure la connexion DB
session_start();
require_once('../config/db_connect.php');

// 2. Vérifier que c'est bien une requête POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die("ERREUR: Méthode non autorisée");
}

// 3. Récupérer toutes les données du formulaire
$donnees = [
    'h1_titre' => trim($_POST['h1_titre'] ?? ''),
    'seo_titre' => trim($_POST['seo_titre'] ?? ''),
    'meta_description' => trim($_POST['meta_description'] ?? ''),
    'mot_cle' => trim($_POST['mot_cle'] ?? ''),
    'tags' => trim($_POST['tags'] ?? ''),
    'slug' => trim($_POST['slug'] ?? ''),
    'localisation' => trim($_POST['localisation'] ?? ''),
    'langue' => trim($_POST['langue'] ?? 'fr'),
    'cible' => trim($_POST['cible'] ?? ''),
    'auteur' => trim($_POST['auteur'] ?? 'Midrash-Hai Martin'),
    'lien_externe' => trim($_POST['lien_externe'] ?? ''),
    'contenu_json' => $_POST['contenu_json'] ?? '[]',
    'score_aleph' => intval($_POST['score_aleph'] ?? 0)
];

// 4. Validation basique
if (empty($donnees['h1_titre'])) {
    die("ERREUR: Le titre H1 est obligatoire");
}

if (empty($donnees['slug'])) {
    die("ERREUR: Le slug est obligatoire");
}

// 5. Nettoyer et formater le slug
$donnees['slug'] = strtolower($donnees['slug']);
$donnees['slug'] = preg_replace('/[^a-z0-9-]/', '-', $donnees['slug']);
$donnees['slug'] = preg_replace('/-+/', '-', $donnees['slug']);
$donnees['slug'] = trim($donnees['slug'], '-');

// 6. Préparer la requête d'insertion
try {
    $sql = "INSERT INTO articles (
        h1_titre, seo_titre, meta_description, mot_cle, tags,
        slug, localisation, langue, cible, auteur,
        lien_externe, contenu_json, score_aleph, statut
    ) VALUES (
        :h1_titre, :seo_titre, :meta_description, :mot_cle, :tags,
        :slug, :localisation, :langue, :cible, :auteur,
        :lien_externe, :contenu_json, :score_aleph, 'en_audit'
    )";
    
    $stmt = $conn->prepare($sql);
    
    // 7. Exécuter avec les paramètres
    $stmt->execute([
        ':h1_titre' => $donnees['h1_titre'],
        ':seo_titre' => $donnees['seo_titre'],
        ':meta_description' => $donnees['meta_description'],
        ':mot_cle' => $donnees['mot_cle'],
        ':tags' => $donnees['tags'],
        ':slug' => $donnees['slug'],
        ':localisation' => $donnees['localisation'],
        ':langue' => $donnees['langue'],
        ':cible' => $donnees['cible'],
        ':auteur' => $donnees['auteur'],
        ':lien_externe' => $donnees['lien_externe'],
        ':contenu_json' => $donnees['contenu_json'],
        ':score_aleph' => $donnees['score_aleph']
    ]);
    
    // 8. Récupérer l'ID de l'article créé
    $article_id = $conn->lastInsertId();
    
    // 9. Stocker en session pour l'écran 2
    $_SESSION['last_article_id'] = $article_id;
    $_SESSION['last_article_score'] = $donnees['score_aleph'];
    
    // 10. Réponse de succès (JavaScript attend un texte)
    echo "SUCCES: Article transmis avec score " . $donnees['score_aleph'] . "% (ID: $article_id)";
    
} catch (PDOException $e) {
    // 11. Gestion des erreurs spécifiques
    if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
        die("ERREUR: Ce slug existe déjà. Choisis un autre slug.");
    }
    die("ERREUR DB: " . $e->getMessage());
}
?>