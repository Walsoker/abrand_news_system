<?php
include('../config/db_connect.php');

// Vérifier si un ID d'article est passé
$article_id = $_GET['id'] ?? 0;

try {
    $query = $conn->prepare("
        SELECT * FROM articles 
        WHERE id = :id AND statut = 'publie'
    ");
    $query->execute([':id' => $article_id]);
    $article = $query->fetch(PDO::FETCH_ASSOC);
    
    if (!$article) {
        header('Location: index.php');
        exit();
    }
    
    // Décoder le JSON des sections
    $sections = json_decode($article['contenu_json'] ?? '[]', true);
    if (!is_array($sections)) {
        $sections = [];
    }
    
} catch (Exception $e) {
    header('Location: index.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($article['h1_titre']); ?> | Abrand News</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Libre+Franklin:wght@300;700;900&family=Source+Serif+4:ital,opsz,wght@0,8..60,400;0,8..60,700;0,8..60,900;1,8..60,400&display=swap" rel="stylesheet">
    <style>
        :root { --ink: #121212; --paper: #ffffff; }
        body { font-family: 'Libre Franklin', sans-serif; background-color: var(--paper); color: var(--ink); }
        .serif { font-family: 'Source Serif 4', serif; }
        
        .article-header { 
            border-bottom: 1px solid #e5e7eb; 
            padding: 20px 40px; 
            display: flex; 
            justify-content: space-between; 
            align-items: center;
            background: white;
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        
        .btn-back {
            background: transparent;
            border: 1px solid #0067b8;
            color: #0067b8;
            padding: 8px 16px;
            font-size: 11px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 1px;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
        }
        .btn-back:hover {
            background: #0067b8;
            color: white;
        }
        
        .article-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 60px 20px;
        }
        
        .article-meta {
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 20px;
            margin-bottom: 40px;
        }
        
        .meta-item {
            font-size: 12px;
            color: #666;
            margin-right: 20px;
            display: inline-flex;
            align-items: center;
        }
        
        .article-img {
            width: 100%;
            max-height: 500px;
            object-fit: cover;
            border-radius: 4px;
            margin: 30px 0;
        }
        
        .section-content {
            margin-bottom: 40px;
        }
        
        .aleph-score-badge {
            background: #0067b8;
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 10px;
            font-weight: 900;
            display: inline-block;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <header class="article-header">
        <a href="index.php" class="serif text-2xl font-black tracking-tighter uppercase">Abrand News.</a>
        <a href="index.php" class="btn-back">
            <i class="fas fa-arrow-left mr-2"></i>Retour aux articles
        </a>
    </header>

    <main class="article-container">
        <div class="aleph-score-badge">
            CERTIFICATION ALEPH <?php echo $article['score_aleph']; ?>%
        </div>
        
        <h1 class="serif text-5xl font-black leading-tight mb-6">
            <?php echo htmlspecialchars($article['h1_titre']); ?>
        </h1>
        
        <div class="article-meta">
            <span class="meta-item">
                <i class="fas fa-user mr-2"></i><?php echo htmlspecialchars($article['auteur']); ?>
            </span>
            <span class="meta-item">
                <i class="fas fa-calendar mr-2"></i><?php echo date('d/m/Y', strtotime($article['date_creation'])); ?>
            </span>
            <span class="meta-item">
                <i class="fas fa-tag mr-2"></i><?php echo htmlspecialchars($article['tags']); ?>
            </span>
            <?php if (!empty($article['localisation'])): ?>
                <span class="meta-item">
                    <i class="fas fa-map-marker-alt mr-2"></i><?php echo htmlspecialchars($article['localisation']); ?>
                </span>
            <?php endif; ?>
        </div>
        
        <p class="serif text-xl text-gray-700 mb-10 leading-relaxed">
            <?php echo htmlspecialchars($article['meta_description']); ?>
        </p>
        
        <?php if (!empty($sections)): ?>
            <?php foreach ($sections as $index => $section): ?>
                <div class="section-content">
                    <?php if (!empty($section['subtitle'])): ?>
                        <h2 class="serif text-3xl font-bold mb-6">
                            <?php echo htmlspecialchars($section['subtitle']); ?>
                        </h2>
                    <?php endif; ?>
                    
                    <?php if (!empty($section['imageBase64'])): ?>
                        <img src="<?php echo htmlspecialchars($section['imageBase64']); ?>" 
                             alt="<?php echo htmlspecialchars($section['altText'] ?? ''); ?>"
                             class="article-img">
                    <?php endif; ?>
                    
                    <?php if (!empty($section['text'])): ?>
                        <div class="serif text-lg leading-relaxed text-gray-800">
                            <?php echo nl2br(htmlspecialchars($section['text'])); ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($section['altText'])): ?>
                        <p class="text-xs text-gray-500 italic mt-2">
                            <i class="fas fa-info-circle mr-1"></i>SEO Alt: <?php echo htmlspecialchars($section['altText']); ?>
                        </p>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="text-gray-500 italic">Contenu non disponible.</p>
        <?php endif; ?>
        
        <?php if (!empty($article['lien_externe'])): ?>
            <div class="mt-12 p-6 bg-gray-50 rounded-lg border border-gray-200">
                <h3 class="font-bold text-sm uppercase tracking-widest mb-4">Sources et références</h3>
                <a href="<?php echo htmlspecialchars($article['lien_externe']); ?>" 
                   target="_blank" 
                   class="text-blue-600 hover:underline">
                    <i class="fas fa-external-link-alt mr-2"></i>Consulter la source
                </a>
            </div>
        <?php endif; ?>
        
        <div class="mt-12 pt-8 border-t border-gray-200">
            <a href="index.php" class="btn-back">
                <i class="fas fa-arrow-left mr-2"></i>Retour aux articles
            </a>
        </div>
    </main>

    <footer class="bg-zinc-900 text-white py-12 mt-20">
        <div class="container mx-auto px-6 text-center">
            <div class="serif text-2xl font-black uppercase mb-4">Abrand News.</div>
            <p class="text-[10px] tracking-widest text-zinc-500">© 2026 VISION MEDIA GROUP - CERTIFICATION SÉMANTIQUE ALEPH</p>
        </div>
    </footer>
</body>
</html>