<?php include('../config/db_connect.php'); ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solutions | Abrand News</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Libre+Franklin:wght@300;700;900&family=Source+Serif+4:ital,opsz,wght@0,8..60,400;0,8..60,700;0,8..60,900;1,8..60,400&display=swap" rel="stylesheet">
    <style>
        :root { --ink: #121212; --ms-blue: #0067b8; --paper: #fdfdfd; --border: #e5e7eb; }
        .dark-theme { --ink: #fdfdfd; --paper: #0a0a0a; --border: #2d2d2d; }
        
        body { font-family: 'Libre Franklin', sans-serif; background-color: var(--paper); color: var(--ink); transition: 0.4s; margin: 0; }
        .serif { font-family: 'Source Serif 4', serif; }
        
        header { border-bottom: 1px solid var(--border); background: var(--paper); position: sticky; top: 0; z-index: 1000; }
        nav.user-nav { padding: 10px 40px; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; }
        .main-menu { padding: 15px 40px; display: flex; justify-content: center; gap: 30px; border-bottom: 4px solid var(--ink); }

        /* TOGGLE DARK MODE */
        #theme-toggle { cursor: pointer; width: 44px; height: 22px; background: #ccc; border-radius: 20px; position: relative; }
        #theme-toggle::before { content: ""; position: absolute; width: 18px; height: 18px; background: white; border-radius: 50%; top: 2px; left: 2px; transition: 0.3s; }
        .dark-theme #theme-toggle { background: var(--ms-blue); }
        .dark-theme #theme-toggle::before { left: 24px; }

        /* GRID SYSTEM */
        .layout-grid { display: grid; grid-template-columns: repeat(1, 1fr); gap: 40px; }
        @media (min-width: 1024px) { .layout-grid { grid-template-columns: repeat(3, 1fr); } }

        /* FEATURED CARD (STYLE DOCONOMY) */
        .card-featured { position: relative; grid-column: span 1; min-height: 550px; overflow: hidden; color: white !important; display: flex; flex-direction: column; justify-content: flex-end; cursor: pointer; }
        .card-featured .bg-img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; transition: 0.8s; z-index: 0; }
        .card-featured:hover .bg-img { transform: scale(1.05); }
        .card-featured .overlay { position: absolute; inset: 0; background: linear-gradient(to top, rgba(0,0,0,0.95) 0%, rgba(0,0,0,0.2) 60%); z-index: 1; }
        .card-featured .content { position: relative; z-index: 2; padding: 30px; }

        /* STANDARD CARD (STYLE COOL ROOF / PAPTIC) */
        .card-standard { display: flex; flex-direction: column; gap: 15px; cursor: pointer; }
        .card-standard .img-box { overflow: hidden; aspect-ratio: 4/3; position: relative; }
        .card-standard .article-img { width: 100%; height: 100%; object-fit: cover; transition: 0.8s; }
        .card-standard:hover .article-img { transform: scale(1.05); }

        .tag { font-size: 10px; font-weight: 900; color: var(--ms-blue); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 5px; display: block; }
        .title-featured { font-family: 'Source Serif 4', serif; font-size: 32px; font-weight: 700; line-height: 1.1; margin-bottom: 15px; color: white; }
        .title-standard { font-family: 'Source Serif 4', serif; font-size: 22px; font-weight: 700; line-height: 1.2; color: var(--ink); }
        .card-standard:hover .title-standard { color: var(--ms-blue); }

        .read-more { font-size: 10px; font-weight: 900; text-transform: uppercase; color: var(--ms-blue); margin-top: 10px; display: inline-flex; align-items: center; gap: 5px; transition: 0.3s; }
        .card-standard:hover .read-more { transform: translateX(5px); }

        .aleph-badge { position: absolute; top: 10px; right: 10px; z-index: 10; background: rgba(0, 103, 184, 0.9); color: white; font-size: 9px; font-weight: 800; padding: 4px 8px; border-radius: 2px; }
        
        .section-header { margin-bottom: 40px; padding-bottom: 10px; border-bottom: 4px solid var(--ink); }
        .btn-load-more { margin: 60px auto; display: block; padding: 15px 40px; border: 2px solid var(--ink); font-size: 11px; font-weight: 900; text-transform: uppercase; letter-spacing: 2px; transition: 0.3s; cursor: pointer; background: transparent; color: var(--ink); }
        .btn-load-more:hover { background: var(--ink); color: var(--paper); }

        .reveal { opacity: 0; transform: translateY(20px); transition: 0.6s ease-out; }
        .reveal.active { opacity: 1; transform: translateY(0); }
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
                    <p class="text-[9px] text-blue-500 font-bold uppercase">Rubrique Solutions</p>
                </div>
                <img class="w-10 h-10 rounded-full border border-gray-200" src="https://ui-avatars.com/api/?name=Midrash-Hai+Martin&background=121212&color=fff">
            </div>
        </nav>
        <nav class="main-menu hidden md:flex">
            <a href="index.html" class="text-[10px] font-black uppercase tracking-widest hover:text-blue-600">À la Une</a>
            <a href="economie.html" class="text-[10px] font-black uppercase tracking-widest hover:text-blue-600">Économie</a>
            <a href="techia.html" class="text-[10px] font-black uppercase tracking-widest hover:text-blue-600">Tech & IA</a>
            <a href="solution.html" class="text-[10px] font-black uppercase tracking-widest text-blue-600 border-b-2 border-blue-600">Solutions</a>
            <a href="geopolitique.html" class="text-[10px] font-black uppercase tracking-widest text-blue-600 border-b-2 border-blue-600">Géopolitique</a>

        </nav>
    </header>

    <main class="container mx-auto px-6 py-12">
        
        <div class="section-header">
            <h1 class="serif text-5xl font-black uppercase tracking-tighter">Solutions</h1>
            <p class="text-xs font-bold opacity-50 uppercase tracking-widest mt-2">Réponses concrètes aux défis planétaires</p>
        </div>

        <div class="layout-grid">
            
            <div class="card-featured reveal">
                <div class="aleph-badge">ALEPH 100%</div>
                <img src="https://images.unsplash.com/photo-1559526324-4b87b5e36e44?auto=format&fit=crop&q=80&w=1200" class="bg-img">
                <div class="overlay"></div>
                <div class="content">
                    <span class="tag" style="color: white; border-bottom: 1px solid white; display: inline-block;">Consommation</span>
                    <h2 class="title-featured">DOCONOMY : La carte bancaire qui freine notre carbone.</h2>
                    <p class="text-sm opacity-90 leading-relaxed mb-4">L'application suédoise qui calcule en temps réel l'impact de vos achats pour dépenser moins et mieux.</p>
                    <div class="read-more" style="color: white;">Voir la solution <i class="fas fa-arrow-right"></i></div>
                </div>
            </div>

            <div class="card-standard reveal">
                <div class="img-box">
                    <div class="aleph-badge">ALEPH 98%</div>
                    <img src="https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?auto=format&fit=crop&q=80&w=800" class="article-img">
                </div>
                <div>
                    <span class="tag">Urbanisme</span>
                    <h3 class="title-standard">Cool Roof : Peindre les toits en blanc pour rafraîchir les villes.</h3>
                    <p class="text-sm opacity-70 serif">La start-up brestoise réfléchit 95% des rayons solaires pour réduire la climatisation massive.</p>
                    <span class="read-more">Lire l'article <i class="fas fa-chevron-right text-[8px]"></i></span>
                </div>
            </div>

            <div class="card-standard reveal">
                <div class="img-box">
                    <div class="aleph-badge">ALEPH 96%</div>
                    <img src="https://images.unsplash.com/photo-1530587191325-3db32d826c18?auto=format&fit=crop&q=80&w=800" class="article-img">
                </div>
                <div>
                    <span class="tag">Packaging</span>
                    <h3 class="title-standard">Paptic : L'alternative finlandaise au plastique.</h3>
                    <p class="text-sm opacity-70 serif">Un matériau recyclable et biodégradable qui s'attaque à un marché de 60 milliards de dollars.</p>
                    <span class="read-more">Lire l'article <i class="fas fa-chevron-right text-[8px]"></i></span>
                </div>
            </div>

            <div class="card-standard reveal">
                <div class="img-box">
                    <div class="aleph-badge">ALEPH 93%</div>
                    <img src="https://images.unsplash.com/photo-1546027658-e5359949d28d?auto=format&fit=crop&q=80&w=800" class="article-img">
                </div>
                <div>
                    <span class="tag">Océans</span>
                    <h3 class="title-standard">Tēnaka : Des nurseries connectées pour sauver le corail.</h3>
                    <p class="text-sm opacity-70 serif">Restaurer les récifs du Pacifique grâce à un modèle RSE innovant pour les entreprises.</p>
                    <span class="read-more">Lire l'article <i class="fas fa-chevron-right text-[8px]"></i></span>
                </div>
            </div>

            <div class="card-standard reveal">
                <div class="img-box">
                    <div class="aleph-badge">ALEPH 89%</div>
                    <img src="https://images.unsplash.com/photo-1518133910546-b6c2fb7d79e3?auto=format&fit=crop&q=80&w=800" class="article-img">
                </div>
                <div>
                    <span class="tag">Énergie</span>
                    <h3 class="title-standard">Gramitherm : Isoler les maisons avec de l'herbe.</h3>
                    <p class="text-sm opacity-70 serif">Une alternative 100% écologique aux isolants thermiques classiques produite en circuit court.</p>
                    <span class="read-more">Lire l'article <i class="fas fa-chevron-right text-[8px]"></i></span>
                </div>
            </div>

            <div class="card-standard reveal">
                <div class="img-box">
                    <div class="aleph-badge">ALEPH 95%</div>
                    <img src="https://images.unsplash.com/photo-1449824913935-59a10b8d2000?auto=format&fit=crop&q=80&w=800" class="article-img">
                </div>
                <div>
                    <span class="tag">Smart City</span>
                    <h3 class="title-standard">Kermap : L'IA qui cartographie la verdure urbaine.</h3>
                    <p class="text-sm opacity-70 serif">Utiliser l'imagerie spatiale pour aider les mairies à optimiser la canopée de leurs villes.</p>
                    <span class="read-more">Lire l'article <i class="fas fa-chevron-right text-[8px]"></i></span>
                </div>
            </div>

        </div>

        <button class="btn-load-more reveal">Plus de solutions innovantes</button>

    </main>

    <footer class="bg-zinc-900 text-white py-12">
        <div class="container mx-auto px-6 text-center text-[10px] tracking-widest text-zinc-500 uppercase">
            © 2026 VISION MEDIA GROUP - CERTIFICATION SÉMANTIQUE ALEPH
        </div>
    </footer>

    <script>
        function toggleTheme() {
            document.body.classList.toggle('dark-theme');
            localStorage.setItem('theme', document.body.classList.contains('dark-theme') ? 'dark' : 'light');
        }

        if (localStorage.getItem('theme') === 'dark') {
            document.body.classList.add('dark-theme');
        }

        function reveal() {
            var reveals = document.querySelectorAll(".reveal");
            for (var i = 0; i < reveals.length; i++) {
                var windowHeight = window.innerHeight;
                var elementTop = reveals[i].getBoundingClientRect().top;
                if (elementTop < windowHeight - 50) {
                    reveals[i].classList.add("active");
                }
            }
        }
        window.addEventListener("scroll", reveal);
        reveal();
    </script>
</body>
</html>