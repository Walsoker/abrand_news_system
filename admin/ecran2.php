<?php 
session_start();
include('../config/db_connect.php');

// 1. Vérifier si on a un article spécifique en session
$article = null;
if (isset($_SESSION['last_article_id'])) {
    $stmt = $conn->prepare("SELECT * FROM articles WHERE id = :id");
    $stmt->execute([':id' => $_SESSION['last_article_id']]);
    $article = $stmt->fetch();
    
    // Si pas trouvé, prendre le dernier en audit
    if (!$article) {
        unset($_SESSION['last_article_id']);
    }
}

// 2. Sinon, prendre le dernier article en audit
if (!$article) {
    $stmt = $conn->query("SELECT * FROM articles WHERE statut = 'en_audit' ORDER BY id DESC LIMIT 1");
    $article = $stmt->fetch();
}

// 3. Si toujours pas d'article, données par défaut
if (!$article) {
    $title = "En attente de soumission...";
    $score_initial = 0;
    $sections = [];
    $article = [
        'langue' => 'FR',
        'cible' => 'Général',
        'auteur' => 'Admin',
        'lien_externe' => '#'
    ];
} else {
    $title = htmlspecialchars($article['h1_titre']);
    $score_initial = intval($article['score_aleph']);
    
    // 4. Décoder le JSON des sections
    $sections_json = $article['contenu_json'] ?? '[]';
    $sections = json_decode($sections_json, true);
    if (!is_array($sections)) {
        $sections = [];
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Abrand News | Admin Master Terminal</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Libre+Franklin:wght@300;700;900&family=Source+Serif+4:ital,opsz,wght@0,8..60,400;0,8..60,700;0,8..60,900;1,8..60,400&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        :root { --ink: #121212; --ms-blue: #0067b8; --admin-bg: #f3f5f7; }
        body { font-family: 'Libre Franklin', sans-serif; background-color: var(--admin-bg); color: var(--ink); overflow-x: hidden; }
        .serif { font-family: 'Source Serif 4', serif; }
        
        /* NAVIGATION COPIÉE */
        nav { border-bottom: 1px solid #e0e0e0; background: white; padding: 15px 40px; display: flex; justify-content: space-between; align-items: center; position: sticky; top: 0; z-index: 1000; }

        .admin-wrapper { display: grid; grid-template-columns: 1fr 400px; gap: 30px; padding: 30px; max-width: 1700px; margin: 0 auto; }

        /* ZONE DE PREVIEW INTERACTIVE */
        .preview-container { display: flex; flex-direction: column; align-items: center; }
        .device-toolbar { background: white; padding: 10px 20px; border-radius: 50px; margin-bottom: 20px; display: flex; gap: 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); border: 1px solid #e0e0e0; }
        .device-btn { color: #ccc; cursor: pointer; transition: 0.3s; font-size: 18px; }
        .device-btn.active { color: var(--ms-blue); }

        #article-viewport { 
            background: white; 
            transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1); 
            border: 1px solid #e0e0e0; 
            box-shadow: 0 10px 40px rgba(0,0,0,0.05);
            overflow-y: auto;
            border-radius: 8px;
        }
        .view-desktop { width: 100%; min-height: 800px; padding: 80px; }
        .view-tablet { width: 768px; min-height: 800px; padding: 40px; }
        .view-mobile { width: 375px; min-height: 667px; padding: 20px; }

        /* SIDEBAR MODULES */
        .sidebar-card { background: white; border-radius: 12px; padding: 24px; border: 1px solid #eef0f2; margin-bottom: 20px; box-shadow: 0 4px 12px rgba(0,0,0,0.02); }
        .label-caps { font-size: 10px; font-weight: 900; color: #999; text-transform: uppercase; letter-spacing: 1.5px; margin-bottom: 12px; display: block; }
        
        /* FEEDBACK COMPONENT */
        .feedback-area { width: 100%; background: #fff9e6; border: 1px solid #ffecb3; padding: 12px; border-radius: 6px; font-size: 13px; min-height: 80px; outline: none; margin-bottom: 10px; }
        .btn-reject { width: 100%; border: 1px solid #d83b01; color: #d83b01; padding: 12px; border-radius: 6px; font-weight: 700; font-size: 11px; text-transform: uppercase; transition: 0.3s; }
        .btn-reject:hover { background: #d83b01; color: white; }

        /* SCORE & BUTTONS */
        .score-box { font-size: 42px; font-weight: 900; color: var(--ms-blue); letter-spacing: -2px; }
        .btn-freeze { background: #e0e0e0; color: white; width: 100%; padding: 20px; border-radius: 8px; font-weight: 900; text-transform: uppercase; letter-spacing: 1px; cursor: not-allowed; transition: 0.4s; }
        .btn-freeze.active { background: var(--ink); cursor: pointer; }
    </style>
</head>
<body>

    <nav>
        <div class="serif text-2xl font-black tracking-tighter uppercase">Abrand News.</div>
        <div class="flex items-center gap-6">
            <div class="text-right">
                <p class="text-[11px] font-black uppercase"><?php echo htmlspecialchars($article['auteur'] ?? 'Admin'); ?></p>
                <p class="text-[9px] text-gray-400 font-bold uppercase tracking-widest">Édition Premium</p>
            </div>
            <img class="w-10 h-10 rounded-full grayscale border border-gray-200" src="https://ui-avatars.com/api/?name=Admin&background=121212&color=fff">
        </div>
    </nav>

    <div class="admin-wrapper">
        
        <div class="preview-container">
            <div class="device-toolbar">
                <i class="fas fa-desktop device-btn active" onclick="changeView('desktop', this)"></i>
                <i class="fas fa-tablet-alt device-btn" onclick="changeView('tablet', this)"></i>
                <i class="fas fa-mobile-alt device-btn" onclick="changeView('mobile', this)"></i>
            </div>
            
            <div id="article-viewport" class="view-desktop">
                <span class="text-[10px] font-black text-blue-500 uppercase tracking-widest">Aperçu Certifié - Flux <?php echo htmlspecialchars($article['langue'] ?? 'FR'); ?></span>
                
                <h1 class="serif text-6xl font-black leading-tight mt-6 mb-10 tracking-tighter" id="content-title">
                    <?php echo htmlspecialchars($title); ?>
                </h1>

                <div class="serif text-xl leading-relaxed text-gray-800 space-y-6">
                    <p class="font-bold italic text-gray-400">Parution prévue : Aleph News Portal (Cible : <?php echo htmlspecialchars($article['cible'] ?? 'Général'); ?>)</p>
                    
                    <?php if (!empty($sections)): ?>
    <?php foreach ($sections as $section): ?>
        <div class="section-preview mb-12">
            <h2 class="text-3xl font-bold mb-4">
                <?php echo htmlspecialchars($section['subtitle'] ?? ''); ?>
            </h2>
            
            <!-- AFFICHER L'IMAGE SI ELLE EXISTE -->
            <?php if (!empty($section['imageBase64'])): ?>
                <div class="mb-6">
                    <img src="<?php echo htmlspecialchars($section['imageBase64']); ?>" 
                         alt="<?php echo htmlspecialchars($section['altText'] ?? ''); ?>"
                         class="max-w-full h-auto rounded-lg shadow-lg border border-gray-200">
                </div>
            <?php endif; ?>
            
            <p class="mb-6 text-lg leading-relaxed">
                <?php echo nl2br(htmlspecialchars($section['text'] ?? '')); ?>
            </p>
            
            <?php if(!empty($section['altText'])): ?>
                <p class="text-[10px] uppercase font-bold text-gray-400 border-l-2 border-blue-500 pl-2 mb-4">
                    SEO ALT: <?php echo htmlspecialchars($section['altText']); ?>
                </p>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
<?php else: ?>
    <p class="text-gray-500 italic">Aucun contenu disponible pour le moment.</p>
<?php endif; ?>

                    <div class="p-6 bg-gray-50 rounded-lg border border-dashed border-gray-300 mt-10">
                        <p class="text-sm">Maillage interne de l'article pointe vers : <span class="text-blue-500 underline"><?php echo htmlspecialchars($article['lien_externe'] ?? 'Non défini'); ?></span></p>
                    </div>
                </div>
            </div>
        </div>

        <aside>
            <div class="sidebar-card text-center">
                <span class="label-caps">Certification SEO</span>
                <div class="score-box" id="score-val"><?php echo $score_initial; ?>%</div>
                <div class="w-full bg-gray-100 h-1 rounded-full mt-4">
                    <div id="score-bar" class="bg-blue-600 h-full transition-all duration-700" style="width: <?php echo $score_initial; ?>%"></div>
                </div>
            </div>

            <div class="sidebar-card">
                <span class="label-caps">1. Feedback au Blogueur</span>
                <textarea class="feedback-area" placeholder="Notes pour corrections (ex: Image trop sombre, titre à revoir...)"></textarea>
                <button class="btn-reject">
                    <i class="fas fa-undo-alt mr-2"></i> Renvoyer pour correction
                </button>
            </div>

            <div class="sidebar-card">
                <span class="label-caps">Validation Sémantique</span>
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded border mb-4">
                    <span class="text-[11px] font-bold uppercase">13. Cohérence Zone/Cible</span>
                    <input type="checkbox" id="field-13" onchange="syncAdmin()" class="w-4 h-4 accent-blue-600 cursor-pointer">
                </div>

                <span class="label-caps">14. Maillage Aleph News</span>
                <input type="url" id="field-14" oninput="syncAdmin()" class="w-full border p-3 rounded text-xs outline-none focus:border-blue-500 mb-6" placeholder="Lien cluster Aleph News...">

                <span class="label-caps">15. Gel des Données</span>
                <button id="btn-publish" class="btn-freeze" onclick="gelFinal(<?php echo $article['id'] ?? 0; ?>)">
                    <i class="fas fa-lock mr-2"></i> Publier & Geler
                </button>
            </div>
        </aside>
    </div>

    <script>
        function changeView(type, btn) {
            const viewport = document.getElementById('article-viewport');
            const title = document.getElementById('content-title');
            document.querySelectorAll('.device-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            viewport.className = '';
            if(type === 'desktop') {
                viewport.classList.add('view-desktop');
                title.style.fontSize = "60px";
            } else if(type === 'tablet') {
                viewport.classList.add('view-tablet');
                title.style.fontSize = "40px";
            } else {
                viewport.classList.add('view-mobile');
                title.style.fontSize = "28px";
            }
        }

        // ON RÉCUPÈRE LE SCORE INITIAL DU PHP
        let basePoints = <?php echo $score_initial; ?>;
        
        function syncAdmin() {
            let pts = basePoints;
            const f13 = document.getElementById('field-13');
            const f14 = document.getElementById('field-14');
            
            // Chaque point admin vaut 16.7 environ pour atteindre 100
            if(f13.checked) pts += 16.7;
            if(f14.value.includes('aleph-news.com')) pts += 16.7;

            const final = Math.min(Math.round(pts), 100);
            document.getElementById('score-val').innerText = final + "%";
            document.getElementById('score-bar').style.width = final + "%";

            const btn = document.getElementById('btn-publish');
            if(final >= 99) { // Seuil de certification
                btn.classList.add('active');
                btn.innerHTML = '<i class="fas fa-certificate mr-2"></i> Certifier l\'article';
            } else {
                btn.classList.remove('active');
                btn.innerHTML = '<i class="fas fa-lock mr-2"></i> Publier & Geler';
            }
        }

        function gelFinal(articleId) {
            if(!document.getElementById('btn-publish').classList.contains('active')) return;
            
            // AJAX pour passer le statut en 'publie'
            fetch('process_audit.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'id=' + articleId + '&statut=publie'
            })
            .then(res => res.text())
            .then(data => {
                alert("PROTOCOLE DE GEL ACTIVÉ.\n\nL'article est maintenant sur le Portail Officiel.");
                window.location.reload();
            });
        }
    </script>
</body>
</html>