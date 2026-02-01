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
            const reader = new FileReader();
            reader.onload = (ev) => {
                const img = document.getElementById('prev-' + id);
                const altBox = document.getElementById('alt-box-' + id);
                img.src = ev.target.result;
                img.classList.remove('hidden');
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

function submitArticle() {
    // 1. On vérifie le score une dernière fois par sécurité
    const scoreVal = parseInt(document.getElementById('score-val').innerText);
    
    if (scoreVal < 66) {
        alert("Action impossible : Votre score Aleph doit être d'au moins 66%.");
        return;
    }

    // 2. On récupère le contenu dynamique (H2, Textes, Alts)
    const contentSections = [];
    document.querySelectorAll('.section-item').forEach((item, index) => {
        contentSections.push({
            subtitle: item.querySelector('input[type="text"]').value,
            text: item.querySelector('textarea').value,
            altText: item.querySelector('.alt-input')?.value || ""
            // Note : Pour les images réelles (fichiers), on traitera cela en Phase 2
        });
    });

    // 3. On prépare le "paquet" de données (FormData)
    const form = document.getElementById('articleForm');
    const formData = new FormData(form);
    
    // On ajoute manuellement les données calculées par le JS
    formData.append('contenu_json', JSON.stringify(contentSections));
    formData.append('score_aleph', scoreVal);

    // 4. L'appel AJAX (Le pont vers le PHP)
    fetch('process_submit.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(data => {
        console.log("Réponse serveur:", data);
        alert("Article transmis avec succès au Terminal d'Audit !");
        window.location.href = "ecran2.php"; // On envoie le blogueur vers l'étape suivante
    })
    .catch(error => {
        console.error("Erreur d'envoi:", error);
        alert("Erreur de connexion au serveur.");
    });
}
