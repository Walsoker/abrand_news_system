<?php 
include('../config/db_connect.php');

// Récupérer les articles PUBLIÉS (statut = 'publie')
try {
    $query = $conn->prepare("
        SELECT * FROM articles 
        WHERE statut = 'publie' 
        ORDER BY date_creation DESC 
        LIMIT 12
    ");
    $query->execute();
    $articles = $query->fetchAll(PDO::FETCH_ASSOC);
    
    // Pour chaque article, décoder le JSON
    foreach ($articles as &$article) {
        $article['sections'] = json_decode($article['contenu_json'] ?? '[]', true);
        if (!is_array($article['sections'])) {
            $article['sections'] = [];
        }
        // Extraire la première image pour l'aperçu
        $article['preview_image'] = '';
        foreach ($article['sections'] as $section) {
            if (!empty($section['imageBase64'])) {
                $article['preview_image'] = $section['imageBase64'];
                break;
            }
        }
        // Si pas d'image, utiliser une image par défaut
        if (empty($article['preview_image'])) {
            $article['preview_image'] = 'https://images.unsplash.com/photo-1451187580459-43490279c0fa?auto=format&fit=crop&q=80&w=1200';
        }
    }
    
} catch (Exception $e) {
    $articles = [];
    error_log("Erreur récupération articles: " . $e->getMessage());
}

// Récupérer les 3 articles les plus récents pour la sidebar
try {
    $queryTrending = $conn->prepare("
        SELECT id, h1_titre, slug, score_aleph 
        FROM articles 
        WHERE statut = 'publie' 
        ORDER BY date_creation DESC 
        LIMIT 3
    ");
    $queryTrending->execute();
    $trending_articles = $queryTrending->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $trending_articles = [];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Abrand News | Portail Officiel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Libre+Franklin:wght@300;700;900&family=Source+Serif+4:ital,opsz,wght@0,8..60,400;0,8..60,700;0,8..60,900;1,8..60,400&display=swap" rel="stylesheet">
    <style>
        :root { 
            --ink: #121212; 
            --ms-blue: #0067b8; 
            --paper: #fdfdfd; 
            --border: #e5e7eb; 
            --sidebar-bg: #f9fafb;
        }
        
        .dark-theme { 
            --ink: #fdfdfd; 
            --paper: #121212; 
            --border: #2d2d2d; 
            --sidebar-bg: #1a1a1a;
        }
        
        body { font-family: 'Libre Franklin', sans-serif; background-color: var(--paper); color: var(--ink); transition: 0.3s; margin: 0; }
        .serif { font-family: 'Source Serif 4', serif; }
        
        header { border-bottom: 1px solid var(--border); background: var(--paper); position: sticky; top: 0; z-index: 1000; }
        nav.user-nav { padding: 10px 40px; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; }
        .main-menu { padding: 15px 40px; display: flex; justify-content: center; gap: 30px; border-bottom: 4px solid var(--ink); }

        .hero-section { display: grid; grid-template-columns: 2fr 1fr; gap: 30px; border-bottom: 1px solid var(--border); padding-bottom: 40px; }
        .news-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 40px; margin-top: 40px; }

        .article-card { cursor: pointer; transition: transform 0.3s ease; }
        .article-card:hover { transform: translateY(-5px); }
        .img-box { overflow: hidden; position: relative; aspect-ratio: 16/9; background: #eee; margin-bottom: 15px; }
        .article-img { width: 100%; height: 100%; object-fit: cover; transition: 0.8s; }
        .article-card:hover .article-img { transform: scale(1.04); }
        
        .tag { font-size: 10px; font-weight: 900; color: var(--ms-blue); text-transform: uppercase; letter-spacing: 2px; margin-bottom: 8px; display: block; }
        .title { font-size: 22px; line-height: 1.1; font-weight: 900; margin-bottom: 10px; }
        .article-card:hover .title { color: var(--ms-blue); }

        .sidebar-container { background-color: var(--sidebar-bg); padding: 24px; border-radius: 4px; border: 1px solid var(--border); }
        .trending-number { font-family: 'Source Serif 4', serif; font-size: 30px; font-weight: 900; color: var(--ms-blue); opacity: 0.4; min-width: 35px; }
        
        #theme-toggle { cursor: pointer; width: 44px; height: 22px; background: #ccc; border-radius: 20px; position: relative; }
        #theme-toggle::before { content: ""; position: absolute; width: 18px; height: 18px; background: white; border-radius: 50%; top: 2px; left: 2px; transition: 0.3s; }
        .dark-theme #theme-toggle { background: var(--ms-blue); }
        .dark-theme #theme-toggle::before { left: 24px; }

        .aleph-badge {
            position: absolute; top: 10px; right: 10px; z-index: 10;
            background: rgba(0, 103, 184, 0.85); color: white;
            font-size: 8px; font-weight: 900; padding: 3px 7px;
            border-radius: 2px; backdrop-filter: blur(2px);
            opacity: 1; transition: 0.4s;
        }

        .reveal { opacity: 0; transform: translateY(30px); transition: all 0.8s ease-out; }
        .reveal.active { opacity: 1; transform: translateY(0); }

        .btn-see-more {
            background: transparent;
            border: 1px solid var(--ms-blue);
            color: var(--ms-blue);
            padding: 8px 16px;
            font-size: 11px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 1px;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }
        .btn-see-more:hover {
            background: var(--ms-blue);
            color: white;
        }
        
        .trending-article {
            display: flex;
            align-items: flex-start;
            gap: 15px;
            padding: 15px 0;
            border-bottom: 1px solid var(--border);
        }
        .trending-article:last-child {
            border-bottom: none;
        }
        .trending-img {
            width: 80px;
            height: 60px;
            object-fit: cover;
            border-radius: 4px;
        }
        
        .empty-state {
            text-align: center;
            padding: 80px 20px;
            color: #666;
        }
        .empty-state i {
            font-size: 48px;
            margin-bottom: 20px;
            opacity: 0.3;
        }
    </style>
</head>
<body>

    <header>
        <nav class="user-nav">
            <div class="serif text-3xl font-black tracking-tighter uppercase">Abrand News.</div>
            <div class="flex items-center gap-6">                
                <div class="flex items-center gap-2">
                    <i class="fas fa-sun text-[10px]"></i>
                    <div id="theme-toggle" onclick="toggleTheme()"></div>
                    <i class="fas fa-moon text-[10px]"></i>
                </div>
                <div class="text-right border-l pl-6 border-gray-200">
                    <p class="text-[11px] font-black uppercase">Midrash-Hai Martin</p>
                    <p class="text-[9px] text-blue-500 font-bold uppercase">Édition Premium</p>
                </div>
                <img class="w-10 h-10 rounded-full border border-gray-200" src="https://ui-avatars.com/api/?name=Midrash-Hai+Martin&background=121212&color=fff">
            </div>
        </nav>
        <nav class="main-menu hidden md:flex">
            <a href="index.php" class="text-[10px] font-black uppercase tracking-widest text-blue-600">À la Une</a>
            <a href="economie.php" class="text-[10px] font-black uppercase tracking-widest hover:text-blue-600">Économie</a>
            <a href="techia.php" class="text-[10px] font-black uppercase tracking-widest hover:text-blue-600">Tech & IA</a>
            <a href="solution.php" class="text-[10px] font-black uppercase tracking-widest hover:text-blue-600">Solutions</a>
            <a href="geopolitique.php" class="text-[10px] font-black uppercase tracking-widest hover:text-blue-600">Géopolitique</a>
        </nav>
    </header>

    <main class="container mx-auto px-6 py-10">
        
        <div class="hero-section reveal">
            <!-- Article vedette (le plus récent) -->
            <?php if (!empty($articles)): 
                $featured = $articles[0];
            ?>
                <div class="article-card">
                    <div class="img-box !aspect-[21/9]">
                        <div class="aleph-badge">ALEPH CERTIFIED <?php echo $featured['score_aleph']; ?>%</div>
                        <img src="<?php echo htmlspecialchars($featured['preview_image']); ?>" class="article-img" alt="<?php echo htmlspecialchars($featured['h1_titre']); ?>">
                    </div>
                    <span class="tag">
                        <?php 
                        $tags = explode(',', $featured['tags'] ?? '');
                        echo htmlspecialchars(trim($tags[0] ?? 'À la Une')); 
                        ?>
                    </span>
                    <h1 class="serif text-5xl font-black leading-none mb-4 tracking-tighter">
                        <?php echo htmlspecialchars($featured['h1_titre']); ?>
                    </h1>
                    <p class="serif text-xl opacity-80 leading-relaxed mb-6">
                        <?php echo htmlspecialchars($featured['meta_description'] ?? 'Article certifié par le système Aleph.'); ?>
                    </p>
                    <a href="article.php?id=<?php echo $featured['id']; ?>" class="btn-see-more">
                        Lire l'article complet
                    </a>
                </div>
            <?php else: ?>
                <div class="article-card">
                    <div class="img-box !aspect-[21/9]">
                        <div class="aleph-badge">ALEPH CERTIFIED 100%</div>
                        <img src="https://images.unsplash.com/photo-1451187580459-43490279c0fa?auto=format&fit=crop&q=80&w=1200" class="article-img">
                    </div>
                    <span class="tag">Édito du Jour</span>
                    <h1 class="serif text-5xl font-black leading-none mb-4 tracking-tighter">Aucun article publié pour le moment</h1>
                    <p class="serif text-xl opacity-80 leading-relaxed mb-6">Les articles validés par l'équipe éditoriale apparaîtront ici.</p>
                </div>
            <?php endif; ?>

            <div class="sidebar-container">
                <h3 class="text-[11px] font-black uppercase tracking-widest mb-6 border-b-2 border-current pb-2">Les plus récents</h3>
                <div class="space-y-6">
                    <?php if (!empty($trending_articles)): ?>
                        <?php foreach ($trending_articles as $index => $trending): ?>
                            <div class="trending-article">
                                <span class="trending-number">0<?php echo $index + 1; ?></span>
                                <div>
                                    <a href="article.php?id=<?php echo $trending['id']; ?>" class="text-xs font-bold leading-tight hover:text-blue-600 block mb-1">
                                        <?php echo htmlspecialchars($trending['h1_titre']); ?>
                                    </a>
                                    <span class="text-[9px] text-gray-500 font-bold">Score: <?php echo $trending['score_aleph']; ?>%</span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-sm text-gray-500 italic">Aucun article récent</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <?php if (!empty($articles)): ?>
            <div class="news-grid">
                <?php 
                // Afficher tous les articles sauf le premier (déjà affiché en vedette)
                $grid_articles = array_slice($articles, 1);
                ?>
                <?php foreach ($grid_articles as $article): ?>
                    <div class="article-card reveal">
                        <div class="img-box">
                            <div class="aleph-badge">ALEPH CERTIFIED <?php echo $article['score_aleph']; ?>%</div>
                            <img src="<?php echo htmlspecialchars($article['preview_image']); ?>" class="article-img" alt="<?php echo htmlspecialchars($article['h1_titre']); ?>">
                        </div>
                        <span class="tag">
                            <?php 
                            $tags = explode(',', $article['tags'] ?? '');
                            echo htmlspecialchars(trim($tags[0] ?? 'Actualité')); 
                            ?>
                        </span>
                        <h3 class="serif title">
                            <?php echo htmlspecialchars($article['h1_titre']); ?>
                        </h3>
                        <p class="text-sm opacity-70 serif mb-3">
                            <?php 
                            $description = $article['meta_description'] ?? '';
                            if (strlen($description) > 120) {
                                echo htmlspecialchars(substr($description, 0, 120) . '...');
                            } else {
                                echo htmlspecialchars($description);
                            }
                            ?>
                        </p>
                        <a href="article.php?id=<?php echo $article['id']; ?>" class="btn-see-more">
                            Lire la suite
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state reveal">
                <i class="fas fa-newspaper"></i>
                <h3 class="serif text-2xl font-bold mb-4">Portail en attente de contenu</h3>
                <p class="max-w-md mx-auto mb-8">
                    Les articles sont actuellement en cours de validation par l'équipe éditoriale.<br>
                    Revenez bientôt pour découvrir nos premières publications certifiées Aleph.
                </p>
                <div class="flex justify-center gap-4">
                    <a href="../admin/ecran1.php" class="btn-see-more">
                        <i class="fas fa-edit mr-2"></i>Rédiger un article
                    </a>
                    <a href="../admin/ecran2.php" class="btn-see-more bg-blue-600 text-white">
                        <i class="fas fa-check-circle mr-2"></i>Valider des articles
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </main>

    <footer class="bg-zinc-900 text-white py-12">
        <div class="container mx-auto px-6 text-center">
            <div class="serif text-2xl font-black uppercase mb-4">Abrand News.</div>
            <p class="text-[10px] tracking-widest text-zinc-500">© 2026 VISION MEDIA GROUP - CERTIFICATION SÉMANTIQUE ALEPH</p>
            <p class="text-[9px] text-zinc-600 mt-2">
                <?php if (!empty($articles)): ?>
                    Dernière publication : <?php echo date('d/m/Y H:i', strtotime($articles[0]['date_creation'])); ?>
                <?php else: ?>
                    Système en cours d'initialisation
                <?php endif; ?>
            </p>
        </div>
    </footer>

    <script>
        function toggleTheme() {
            document.body.classList.toggle('dark-theme');
            localStorage.setItem('abrand-theme', document.body.classList.contains('dark-theme') ? 'dark' : 'light');
        }

        if (localStorage.getItem('abrand-theme') === 'dark') {
            document.body.classList.add('dark-theme');
        }

        function reveal() {
            var reveals = document.querySelectorAll(".reveal");
            for (var i = 0; i < reveals.length; i++) {
                var windowHeight = window.innerHeight;
                var elementTop = reveals[i].getBoundingClientRect().top;
                if (elementTop < windowHeight - 100) {
                    reveals[i].classList.add("active");
                }
            }
        }
        window.addEventListener("scroll", reveal);
        reveal();
    </script>
</body>
</html>