<?php 
// Inclusion silencieuse de la connexion DB
@include('../config/db_connect.php'); 
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Abrand News | Bureau de Rédaction</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Libre+Franklin:wght@300;700;900&family=Source+Serif+4:ital,opsz,wght@0,8..60,400;0,8..60,700;0,8..60,900;1,8..60,400&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.0/Sortable.min.js"></script>
    <style>

        :root { --ink: #121212; --paper: #ffffff; --bg-desk: #f0ede9; }

        body { font-family: 'Libre Franklin', sans-serif; background-color: var(--bg-desk); color: var(--ink); overflow-x: hidden; transition: background 0.5s ease; }

        .serif { font-family: 'Source Serif 4', serif; }

       

        /* MODE IMMERSION */

        body.immersion-mode { background-color: #ffffff; }

        body.immersion-mode nav, body.immersion-mode .seo-floater, body.immersion-mode .floating-info-left, body.immersion-mode .section-label, body.immersion-mode .add-btn-container {

            opacity: 0; pointer-events: none;

        }

        body.immersion-mode .writing-desk { border: none; box-shadow: none; transform: translateY(-50px); }



        nav { border-bottom: 1px solid #d1cfcb; background: white; padding: 15px 40px; display: flex; justify-content: space-between; align-items: center; position: sticky; top: 0; z-index: 1000; }

        .workspace { display: flex; justify-content: center; width: 100%; position: relative; padding: 60px 0; }



        .floating-info-left { position: fixed; left: 60px; top: 200px; width: 120px; pointer-events: none; opacity: 0.4; transition: 0.3s; }

        .workspace:hover .floating-info-left { opacity: 1; }

        .info-tag { font-size: 9px; font-weight: 900; text-transform: uppercase; letter-spacing: 2px; margin-bottom: 40px; border-left: 2px solid var(--ink); padding-left: 15px; }



        /* LA FEUILLE DE REDACTION */

        .writing-desk {

            width: 850px;

            background: var(--paper);

            padding: 100px 80px;

            border-radius: 2px;

            box-shadow: 0 30px 60px rgba(0,0,0,0.1);

            border: 1px solid #dcdad4;

            min-height: 140vh;

            position: relative;

            z-index: 10;

        }

        .writing-desk::before { content: "ABRAND NEWS EDITORIAL"; position: absolute; top: 30px; left: 50%; transform: translateX(-50%); font-size: 9px; font-weight: 900; letter-spacing: 8px; color: #e5e3de; }



        .seo-floater { position: fixed; top: 110px; right: 40px; width: 330px; background: white; border: 1px solid var(--ink); border-radius: 20px; box-shadow: 15px 15px 0px rgba(0,0,0,0.08); padding: 30px; z-index: 500; max-height: 75vh; overflow-y: auto; transition: all 0.4s ease; }

        .seo-floater.focus-mode:not(:hover):not(.minimized) { opacity: var(--idle-opacity, 0.2); filter: blur(var(--idle-blur, 4px)); transform: scale(0.98); }

       

        .seo-floater.minimized { width: 60px; height: 60px; padding: 0; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; border-width: 2px; }

        .seo-floater.minimized #console-content { display: none; }

       

        /* CHAMPS DE TEXTE AVEC LIGNES DE DISTINCTION */

        .headline-input { width: 100%; border: none; outline: none; font-size: 52px; font-weight: 900; line-height: 1.1; margin-bottom: 40px; padding-bottom: 20px; border-bottom: 2px solid #f0f0f0; resize: none; background: transparent; letter-spacing: -1px; text-align: center; transition: 0.3s; }

        .headline-input:focus { border-bottom-color: var(--ink); }



        .section-label { font-size: 10px; font-weight: 900; color: #b0ada8; text-transform: uppercase; letter-spacing: 1.5px; margin-bottom: 8px; display: block; }

        .meta-field { width: 100%; border: none; border-bottom: 1px solid #eee; padding: 10px 0; font-size: 14px; outline: none; margin-bottom: 20px; background: transparent; transition: 0.3s; }

        .meta-field:focus { border-bottom-color: var(--ink); }



        .section-item { position: relative; padding: 30px; background: #fafaf9; border: 1px solid #f0efed; border-radius: 8px; transition: 0.3s; }

        .section-item:hover { border-color: #dcdad4; background: #fdfdfd; }

        .drag-handle { position: absolute; left: -35px; top: 40px; cursor: grab; color: #ccc; font-size: 14px; opacity: 0; transition: 0.2s; }

        .section-item:hover .drag-handle { opacity: 1; }



        .drop-zone { border: 2px dashed #e5e3de; padding: 60px; text-align: center; background: white; border-radius: 4px; margin: 30px 0; transition: 0.3s; cursor: pointer; }

        .drop-zone:hover { border-color: #ccc; background: #fcfcfc; }

       

        .btn-publish { background: #dcdad4; color: #fff; width: 100%; padding: 16px; font-weight: 900; text-transform: uppercase; font-size: 11px; border-radius: 50px; cursor: not-allowed; transition: 0.3s; }

        .btn-publish.active { background: var(--ink); cursor: pointer; }

       

        .immersion-trigger { position: fixed; bottom: 30px; left: 30px; width: 45px; height: 45px; background: black; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; z-index: 2000; box-shadow: 0 10px 20px rgba(0,0,0,0.1); }

        #visibility-tool.hidden-tool { display: none; }

    </style>
</head>
<body>

    <nav>
        <div class="serif text-2xl font-black tracking-tighter uppercase">Abrand News.</div>
        <div class="flex items-center gap-6">
            <div class="text-right">
                <p class="text-[11px] font-black uppercase">Midrash-Hai Martin</p>
                <p class="text-[9px] text-gray-400 font-bold uppercase tracking-widest">Édition Premium</p>
            </div>
            <img class="w-10 h-10 rounded-full grayscale border border-gray-200" src="https://ui-avatars.com/api/?name=Midrash-Hai+Martin&background=121212&color=fff">
        </div>
    </nav>

    <form id="articleForm">
        <div class="floating-info-left">
            <div class="info-tag">Date<br><span class="text-black">30 JAN. 2026</span></div>
            <div class="info-tag">Lecture<br><span class="text-black" id="read-time">0 MIN</span></div>
            <div class="info-tag">Flux<br><span class="text-black">LIVE</span></div>
        </div>

        <div class="workspace">
            <main class="writing-desk">
                <span class="section-label text-center">Titre de l'Article (H1)</span>
                <textarea class="headline-input serif" id="f-h1" name="h1_titre" placeholder="Entrez votre grand titre..." rows="1" oninput="sync()"></textarea>
                
                <div id="editor-flow" class="space-y-12"></div>

                <input type="hidden" id="f-content-json" name="contenu_json">
                <input type="hidden" id="f-score-val" name="score_aleph" value="0">

                <div class="flex justify-center mt-20 add-btn-container">
                    <button type="button" onclick="addSection()" class="text-[10px] font-black uppercase tracking-[4px] border-b-2 border-black pb-2 hover:opacity-40 transition-all">
                        + Ajouter une section
                    </button>
                </div>
            </main>
        </div>

        <aside class="seo-floater focus-mode" id="console">
            <div class="toggle-btn" onclick="toggleConsole()"><i id="main-toggle-icon" class="fas fa-minus text-sm text-gray-300"></i></div>
            <div id="console-content">
                <div class="flex justify-between items-end mb-8 border-b pb-4">
                    <div>
                        <h4 class="font-black text-[10px] uppercase tracking-widest text-gray-400 mb-1">Score SEO (Min. 66%)</h4>
                        <button type="button" onclick="toggleVisibilityTool()" class="text-[10px] text-gray-400 hover:text-black italic underline">Focus visuel</button>
                    </div>
                    <div id="score-val" class="text-4xl font-black serif italic">0%</div>
                </div>

                <div id="visibility-tool" class="hidden-tool bg-gray-50 p-4 rounded-xl mb-6 border border-gray-100">
                    <span class="section-label">Opacité interface</span>
                    <input type="range" min="0" max="100" value="20" class="w-full h-1 accent-black" oninput="updateOpacity(this.value)">
                </div>

                <span class="section-label">1. Titre SEO</span>
                <input type="text" id="f-seo-title" name="seo_titre" class="meta-field" maxlength="60" placeholder="Titre Google..." oninput="sync()">

                <span class="section-label">2. Méta-Description (150-160)</span>
                <textarea id="f-meta" name="meta_description" class="meta-field" rows="2" placeholder="Résumé SEO..." oninput="sync()"></textarea>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <span class="section-label">3. Mot-clé</span>
                        <input type="text" id="f-kw" name="mot_cle" class="meta-field" placeholder="Principal..." oninput="sync()">
                    </div>
                    <div>
                        <span class="section-label">4. Tags</span>
                        <input type="text" id="f-tags" name="tags" class="meta-field" placeholder="Tag1, Tag2..." oninput="sync()">
                    </div>
                </div>

                <span class="section-label">5. Slug URL</span>
                <input type="text" id="f-slug" name="slug" class="meta-field" placeholder="mon-bel-article" oninput="sync()">

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <span class="section-label">9. Ville</span>
                        <input type="text" id="f-loc" name="localisation" class="meta-field" placeholder="Ex: Lyon" oninput="sync()">
                    </div>
                    <div>
                        <span class="section-label">10. Langue</span>
                        <select id="f-lang" name="langue" class="meta-field" onchange="sync()">
                            <option value="">ISO</option>
                            <option value="fr">FR</option>
                            <option value="en">EN</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <span class="section-label">11. Cible</span>
                        <input type="text" id="f-target" name="cible" class="meta-field" placeholder="Public..." oninput="sync()">
                    </div>
                    <div>
                        <span class="section-label">12. Auteur</span>
                        <input type="text" id="f-author" name="auteur" class="meta-field" value="Midrash-Hai Martin" oninput="sync()">
                    </div>
                </div>
                
                <span class="section-label">8. Lien Externe</span>
                <input type="url" id="f-link" name="lien_externe" class="meta-field" placeholder="https://..." oninput="sync()">

                <button type="button" id="pub-btn" onclick="submitArticle()" class="btn-publish mt-4">Soumettre à validation</button>
            </div>
        </aside>
    </form>

    <div class="immersion-trigger" onclick="toggleImmersion()"><i class="fas fa-feather-alt"></i></div>

    <script >
            let sections = 0;
    const flowContainer = document.getElementById('editor-flow');
    new Sortable(flowContainer, { animation: 150, handle: '.drag-handle' });

    function toggleImmersion() { document.body.classList.toggle('immersion-mode'); }
    function toggleVisibilityTool() { document.getElementById('visibility-tool').classList.toggle('hidden-tool'); }
    
    function updateOpacity(val) {
        document.documentElement.style.setProperty('--idle-opacity', val / 100);
        document.documentElement.style.setProperty('--idle-blur', (100 - val) / 20 + 'px');
    }

    function toggleConsole() {
        document.getElementById('console').classList.toggle('minimized');
    }

    // --- MOTEUR DE SCORING ALEPH ---
    function sync() {
        let pts = 0;
        const step = 6.66; // 100 / 15 champs

        // Récupération des éléments
        const h1 = document.getElementById('f-h1').value;
        const seoTitle = document.getElementById('f-seo-title').value;
        const meta = document.getElementById('f-meta').value;
        const kw = document.getElementById('f-kw').value;
        const tags = document.getElementById('f-tags').value;
        const slug = document.getElementById('f-slug').value;
        const loc = document.getElementById('f-loc').value;
        const lang = document.getElementById('f-lang').value;
        const target = document.getElementById('f-target').value;
        const author = document.getElementById('f-author').value;
        const extLink = document.getElementById('f-link').value;

        // 1. Titre H1 présent
        if(h1.trim().length > 10) pts += step;
        
        // 2. Titre SEO (Optimisé pour Google)
        if(seoTitle.length > 5) pts += step;
        
        // 3. Méta-Description (Fenêtre stricte 150-160 selon CDC)
        if(meta.length >= 150 && meta.length <= 160) pts += step;
        
        // 4. Mot-clé principal
        if(kw.trim().length >= 3) pts += step;
        
        // 5. Présence de Tags
        if(tags.trim().length > 2) pts += step;
        
        // 6. Slug URL valide
        if(slug.trim().length > 3 && !slug.includes(' ')) pts += step;
        
        // 7. Localisation (Ville)
        if(loc.trim().length > 2) pts += step;
        
        // 8. Langue ISO
        if(lang !== "") pts += step;
        
        // 9. Public Cible
        if(target.trim().length > 2) pts += step;
        
        // 10. Signature Auteur
        if(author.trim().length > 2) pts += step;

        // 11. Lien Externe (Sourcing)
        if(extLink.includes('http')) pts += step;

        // 12. Présence d'au moins un H2 (Sous-titre dans les sections)
        const h2s = document.querySelectorAll('.section-item input[type="text"]');
        let h2Filled = false;
        h2s.forEach(h => { if(h.value.length > 5) h2Filled = true; });
        if(h2Filled) pts += step;

        // 13. Présence de texte dans les sections (Contenu)
        const texts = document.querySelectorAll('.section-item textarea');
        let textFilled = false;
        texts.forEach(t => { if(t.value.length > 50) textFilled = true; });
        if(textFilled) pts += step;

        // 14. Images & Texte Alt (SEO visuel)
        const alts = document.querySelectorAll('.alt-input');
        let altFilled = false;
        alts.forEach(a => { if(a.value.length > 3) altFilled = true; });
        if(altFilled) pts += step;

        // 15. Densité du mot-clé (Vérifie si le KW est dans le H1)
        if(kw.length > 2 && h1.toLowerCase().includes(kw.toLowerCase())) pts += step;

        // --- CALCUL FINAL ---
        const finalScore = Math.min(Math.round(pts), 100);
        const scoreElement = document.getElementById('score-val');
        scoreElement.innerText = finalScore + "%";

        // Changement de couleur dynamique (Logique CDC)
        if(finalScore < 40) scoreElement.style.color = "#ef4444"; // Rouge
        else if(finalScore < 66) scoreElement.style.color = "#f97316"; // Orange
        else if(finalScore < 80) scoreElement.style.color = "#3b82f6"; // Bleu (Bon)
        else scoreElement.style.color = "#22c55e"; // Vert (Excellent)

        // Activation du bouton Publier (Seuil 66%)
        const btn = document.getElementById('pub-btn');
        if(finalScore >= 66) {
            btn.classList.add('active');
            btn.disabled = false;
            btn.innerText = "Soumettre à l'Audit (Prêt)";
        } else {
            btn.classList.remove('active');
            btn.disabled = true;
            btn.innerText = "Score insuffisant (" + finalScore + "%)";
        }

        // Temps de lecture
        const words = h1.split(' ').length + (sections * 40); 
        document.getElementById('read-time').innerText = Math.ceil(words / 200) + " MIN";
    }

    function addSection() {
        sections++;
        const block = document.createElement('div');
        block.className = "section-item group";
        block.innerHTML = `
            <div class="drag-handle"><i class="fas fa-grip-vertical"></i></div>
            <div class="flex justify-between items-center mb-6">
                <span class="text-[8px] font-black bg-white border border-gray-200 px-2 py-1 uppercase tracking-widest">Section ${sections}</span>
                <button onclick="this.parentElement.parentElement.remove(); sync();" class="text-gray-300 hover:text-red-500 transition-all"><i class="fas fa-times"></i></button>
            </div>
            <input type="text" class="serif text-2xl font-bold w-full mb-6 outline-none bg-transparent border-b border-transparent focus:border-gray-100 transition-all" placeholder="Sous-titre (H2)..." oninput="sync()">
            <textarea class="serif text-lg w-full outline-none mb-6 leading-relaxed bg-transparent text-gray-700" rows="5" placeholder="Votre texte ici..." oninput="sync()"></textarea>
            
            <div class="drop-zone" onclick="document.getElementById('file-${sections}').click()">
                <p class="text-[9px] text-gray-400 font-bold uppercase tracking-widest">+ Ajouter un visuel</p>
                <input type="file" id="file-${sections}" class="hidden" onchange="previewImg(event, ${sections})">
                <img id="prev-${sections}" class="max-w-full hidden mx-auto shadow-lg rounded-sm">
                <div id="alt-box-${sections}" class="hidden mt-6 text-left border-t border-gray-100 pt-4">
                    <span class="section-label">Texte Alt (SEO)</span>
                    <input type="text" class="meta-field text-sm italic alt-input" placeholder="Décrivez l'image..." oninput="sync()">
                </div>
            </div>
        `;
        flowContainer.appendChild(block);
        sync();
    }

function previewImg(e, id) {
    const file = e.target.files[0];
    if(file) {
        // Limite de taille (optionnel)
        if(file.size > 5 * 1024 * 1024) { // 5MB
            alert("Image trop lourde (>5MB). Choisissez une image plus légère.");
            return;
        }
        
        const reader = new FileReader();
        reader.onload = (ev) => {
            const img = document.getElementById('prev-' + id);
            const altBox = document.getElementById('alt-box-' + id);
            
            // 1. Afficher l'image
            img.src = ev.target.result;
            img.classList.remove('hidden');
            
            // 2. Stocker la base64 dans un attribut data
            img.setAttribute('data-base64', ev.target.result);
            
            // 3. Afficher le champ alt
            altBox.classList.remove('hidden');
            img.previousElementSibling.classList.add('hidden');
            
            sync();
        };
        reader.readAsDataURL(file);
    }
}

    // Initialisation
    updateOpacity(20);
    addSection();
    // --- LOGIQUE D'ENVOI AU BACKEND (PHP) ---

// --- LOGIQUE D'ENVOI AU BACKEND (PHP) ---
function submitArticle() {
    const scoreText = document.getElementById('score-val').innerText;
    const scoreVal = parseInt(scoreText);
    
    if (scoreVal < 66) {
        alert("Score insuffisant (" + scoreVal + "%). Atteignez 66% minimum.");
        return;
    }
    
    // 1. Préparer les sections avec les images base64
    const contentSections = [];
    document.querySelectorAll('.section-item').forEach((item, index) => {
        const imgElement = item.querySelector('img[id^="prev-"]');
        const base64Image = imgElement ? imgElement.getAttribute('data-base64') : '';
        
        contentSections.push({
            id: index + 1,
            subtitle: item.querySelector('input[type="text"]').value,
            text: item.querySelector('textarea').value,
            altText: item.querySelector('.alt-input')?.value || "",
            imageBase64: base64Image // ← AJOUT IMPORTANT
        });
    });
    
    // 2. Mettre à jour le champ JSON caché
    document.getElementById('f-content-json').value = JSON.stringify(contentSections);
    document.getElementById('f-score-val').value = scoreVal;
    
    // 3. Préparer FormData
    const form = document.getElementById('articleForm');
    const formData = new FormData(form);
    
    // 4. Indicateur de chargement
    const btn = document.getElementById('pub-btn');
    const originalText = btn.innerText;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Envoi...';
    btn.disabled = true;
    
    // 5. Envoi AJAX
    fetch('process_submit.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(data => {
        if (data.startsWith("SUCCES:")) {
            alert("✅ " + data);
            window.location.href = "ecran2.php";
        } else {
            alert("❌ " + data);
            btn.innerHTML = originalText;
            btn.disabled = false;
        }
    })
    .catch(error => {
        console.error("Erreur réseau:", error);
        alert("Erreur de connexion au serveur. Vérifie ta connexion.");
        btn.innerHTML = originalText;
        btn.disabled = false;
    });
}

    </script>
</body>
</html>