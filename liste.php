<?php

$configsDir = __DIR__ . '/configs';
$faqs = [];

if (is_dir($configsDir)) {
    $files = glob($configsDir . '/*.json');
    if ($files) {
        foreach ($files as $file) {
            $slug = basename($file, '.json');
            $content = file_get_contents($file);
            $data = json_decode($content, true);
            if ($data) {
                $data['slug'] = $slug;
                $data['mtime'] = filemtime($file);
                $faqs[] = $data;
            }
        }
    }
}


usort($faqs, function($a, $b) {
    return $b['mtime'] - $a['mtime'];
});


$totalFaqs = count($faqs);
$totalAmount = 0;
$allProducts = [];

foreach ($faqs as $faq) {
    if (isset($faq['amount']) && is_numeric($faq['amount'])) {
        $totalAmount += floatval($faq['amount']);
    }
    if (isset($faq['products']) && is_array($faq['products'])) {
        foreach ($faq['products'] as $prod) {
            if (isset($prod['name'])) {
                $allProducts[] = $prod['name'];
            }
        }
    }
}
$uniqueProductsCount = count(array_unique($allProducts));
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Portail FAQs Partenaires – Kreancia</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Montserrat:wght@600;700;800&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --bleu-marine: #00276A;
            --bleu-header: #0050C8;
            --bleu-moyen: #0060E0;
            --bleu-clair: #00AEEF;
            --orange: #F5821F;
            --bg: #F4F6F9;
            --white: #ffffff;
            --text-main: #1E293B;
            --text-muted: #64748B;
            --border: #E2E8F0;
            --shadow-sm: 0 1px 3px rgba(0,0,0,0.05);
            --shadow-md: 0 4px 20px rgba(0, 39, 106, 0.08);
            --shadow-lg: 0 10px 25px -5px rgba(0, 39, 106, 0.12);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Topbar decoration line */

        .top-decor {
            height: 5px;
            background: linear-gradient(90deg, var(--bleu-marine) 0%, var(--bleu-clair) 50%, var(--orange) 100%);
        }


        header {
            background: var(--white);
            border-bottom: 1px solid var(--border);
            box-shadow: var(--shadow-sm);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .header-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 16px 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .brand-logo {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
        }

        .brand-logo img {
            height: 40px;
            width: auto;
        }

        .brand-title {
            font-family: 'Montserrat', sans-serif;
            font-size: 18px;
            font-weight: 800;
            color: var(--bleu-marine);
            letter-spacing: -0.5px;
        }

        .brand-title span {
            color: var(--orange);
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 10px 20px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            border: none;
            text-decoration: none;
        }

        .btn-primary {
            background-color: var(--bleu-header);
            color: var(--white);
            box-shadow: 0 4px 12px rgba(0, 80, 200, 0.2);
        }

        .btn-primary:hover {
            background-color: var(--bleu-moyen);
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(0, 80, 200, 0.3);
        }

        .btn-primary:active {
            transform: translateY(0);
        }

        .btn-secondary {
            background-color: #EEF2F6;
            color: var(--bleu-header);
        }

        .btn-secondary:hover {
            background-color: #E2E8F0;
            color: var(--bleu-marine);
        }

        .btn-accent {
            background-color: var(--orange);
            color: var(--white);
            box-shadow: 0 4px 12px rgba(245, 130, 31, 0.2);
        }

        .btn-accent:hover {
            background-color: #e06b0a;
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(245, 130, 31, 0.3);
        }


        .container {
            max-width: 1200px;
            margin: 32px auto;
            padding: 0 24px;
            width: 100%;
            flex: 1;
        }


        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
            margin-bottom: 32px;
        }

        .stat-card {
            background: var(--white);
            border-radius: 10px;
            padding: 24px;
            box-shadow: var(--shadow-md);
            border-left: 4px solid var(--bleu-clair);
            display: flex;
            flex-direction: column;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        .stat-card.amount-card {
            border-left-color: var(--orange);
        }

        .stat-card.prod-card {
            border-left-color: var(--bleu-marine);
        }

        .stat-label {
            font-size: 13px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--text-muted);
            margin-bottom: 8px;
        }

        .stat-value {
            font-family: 'Montserrat', sans-serif;
            font-size: 28px;
            font-weight: 700;
            color: var(--bleu-marine);
        }


        .control-bar {
            background: var(--white);
            border-radius: 8px;
            padding: 18px 24px;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
            margin-bottom: 24px;
            flex-wrap: wrap;
        }

        .search-wrapper {
            position: relative;
            flex: 1;
            min-width: 280px;
        }

        .search-input {
            width: 100%;
            padding: 12px 16px 12px 42px;
            border: 1px solid var(--border);
            border-radius: 6px;
            font-size: 14px;
            color: var(--text-main);
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .search-input:focus {
            border-color: var(--bleu-header);
            box-shadow: 0 0 0 3px rgba(0, 80, 200, 0.15);
        }

        .search-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            pointer-events: none;
            width: 16px;
            height: 16px;
        }

        .results-count {
            font-size: 14px;
            color: var(--text-muted);
            font-weight: 500;
        }


        .faq-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 24px;
        }


        .faq-card {
            background: var(--white);
            border-radius: 12px;
            box-shadow: var(--shadow-md);
            border: 1px solid var(--border);
            display: flex;
            flex-direction: column;
            overflow: hidden;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
        }

        .faq-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-lg);
            border-color: rgba(0, 80, 200, 0.2);
        }

        .faq-card-header {
            padding: 24px;
            background: #FAFBFD;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .logo-container {
            width: 60px;
            height: 60px;
            background: #fff;
            border-radius: 8px;
            border: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 6px;
            flex-shrink: 0;
            box-shadow: var(--shadow-sm);
        }

        .logo-container img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }

        .partner-info {
            flex: 1;
            min-width: 0;
        }

        .partner-name {
            font-family: 'Montserrat', sans-serif;
            font-size: 16px;
            font-weight: 700;
            color: var(--bleu-marine);
            margin-bottom: 4px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .partner-link {
            font-size: 12px;
            color: var(--bleu-clair);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-weight: 600;
        }

        .partner-link:hover {
            text-decoration: underline;
        }

        .faq-card-body {
            padding: 24px;
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .partner-desc {
            font-size: 13.5px;
            color: var(--text-muted);
            line-height: 1.6;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
            flex: 1;
        }

        .dette-details {
            background: #F8FAFC;
            border-radius: 8px;
            padding: 12px 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border: 1px solid #F1F5F9;
        }

        .detail-item {
            display: flex;
            flex-direction: column;
        }

        .detail-lbl {
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            color: var(--text-muted);
            margin-bottom: 2px;
        }

        .detail-val {
            font-size: 13.5px;
            font-weight: 700;
            color: var(--bleu-marine);
        }

        .faq-card-footer {
            padding: 16px 24px 24px;
            border-top: 1px solid var(--border);
            display: flex;
            gap: 12px;
        }

        .faq-card-footer .btn {
            flex: 1;
            padding: 8px 12px;
            font-size: 13px;
        }


        .empty-state {
            background: var(--white);
            border-radius: 12px;
            box-shadow: var(--shadow-md);
            padding: 60px 40px;
            text-align: center;
            max-width: 600px;
            margin: 40px auto;
            border: 1px solid var(--border);
        }

        .empty-icon {
            font-size: 48px;
            margin-bottom: 20px;
            display: block;
        }

        .empty-state h3 {
            font-family: 'Montserrat', sans-serif;
            font-size: 20px;
            color: var(--bleu-marine);
            margin-bottom: 12px;
        }

        .empty-state p {
            color: var(--text-muted);
            font-size: 14.5px;
            line-height: 1.6;
            margin-bottom: 24px;
        }


        footer {
            background: var(--white);
            border-top: 1px solid var(--border);
            padding: 24px;
            text-align: center;
            font-size: 13px;
            color: var(--text-muted);
            margin-top: auto;
        }
    </style>
</head>
<body>

    <div class="top-decor"></div>

    <header>
        <div class="header-container">
            <a href="liste.php" class="brand-logo">
                <img src="https://kreancia.com/wp-content/uploads/2024/08/kreanciabaselinecouleur.png" alt="Kreancia">
                <div class="brand-title">Espace <span>FAQs</span></div>
            </a>
            
            <a href="generateur.html" class="btn btn-primary">
                ➕ Nouveau Partenaire
            </a>
        </div>
    </header>

    <div class="container">

        <div class="stats-grid">

            <div class="stat-card">
                <span class="stat-label">Partenaires Actifs</span>
                <span class="stat-value"><?= $totalFaqs ?></span>
            </div>
            <div class="stat-card amount-card">
                <span class="stat-label">Dette Globale Sous Gestion</span>
                <span class="stat-value"><?= number_format($totalAmount, 0, ',', ' ') ?> €</span>
            </div>
            <div class="stat-card prod-card">
                <span class="stat-label">Total Produits Référencés</span>
                <span class="stat-value"><?= $uniqueProductsCount ?></span>
            </div>
        </div>

        <div class="control-bar">

            <div class="search-wrapper">
                <svg class="search-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
                <input type="text" id="search-bar" class="search-input" placeholder="Rechercher un partenaire, un produit, une description..." oninput="filterFaqs()">
            </div>
            <div class="results-count" id="results-count">
                Affichage de <?= $totalFaqs ?> partenaire<?= $totalFaqs > 1 ? 's' : '' ?>
            </div>
        </div>

        <?php if ($totalFaqs > 0): ?>

            <div class="faq-grid" id="faq-grid">
                <?php foreach ($faqs as $faq): ?>
                    <div class="faq-card" 
                         data-name="<?= htmlspecialchars(strtolower($faq['partnerName'] ?? '')) ?>"
                         data-desc="<?= htmlspecialchars(strtolower($faq['description'] ?? '')) ?>"
                         data-products="<?= htmlspecialchars(strtolower(implode(' ', array_column($faq['products'] ?? [], 'name')))) ?>">
                        
                        <div class="faq-card-header">
                            <div class="logo-container">
                                <img src="<?= htmlspecialchars($faq['logoUrl'] ?? 'https://via.placeholder.com/60') ?>" 
                                     alt="<?= htmlspecialchars($faq['partnerName'] ?? 'Logo') ?>" 
                                     onerror="this.src='https://via.placeholder.com/60?text=Logo';">
                            </div>
                            <div class="partner-info">
                                <h3 class="partner-name"><?= htmlspecialchars($faq['partnerName'] ?? 'Sans Nom') ?></h3>
                                <?php if (!empty($faq['websiteUrl'])): ?>
                                    <a href="<?= htmlspecialchars($faq['websiteUrl']) ?>" target="_blank" class="partner-link">
                                        Site Web ↗
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="faq-card-body">
                            <p class="partner-desc">
                                <?= htmlspecialchars($faq['description'] ?? 'Aucune description disponible.') ?>
                            </p>
                            
                            <div class="dette-details">
                                <div class="detail-item">
                                    <span class="detail-lbl">Créance</span>
                                    <span class="detail-val"><?= isset($faq['amount']) ? htmlspecialchars($faq['amount']) : '-' ?> €</span>
                                </div>
                                <div class="detail-item" style="text-align: right;">
                                    <span class="detail-lbl">Engagement</span>
                                    <span class="detail-val"><?= isset($faq['duration']) ? htmlspecialchars($faq['duration']) : '-' ?></span>
                                </div>
                            </div>
                        </div>

                        <div class="faq-card-footer">
                            <a href="faq.html?partner=<?= htmlspecialchars($faq['slug']) ?>" target="_blank" class="btn btn-accent">
                                👁️ Voir FAQ
                            </a>
                            <a href="generateur.html?partner=<?= htmlspecialchars($faq['slug']) ?>" class="btn btn-secondary">
                                ✏️ Modifier
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <span class="empty-icon">📂</span>
                <h3>Aucune page débiteur créée</h3>
                <p>Pour le moment, aucun partenaire n'a été enregistré sur ce serveur. Vous pouvez créer votre première FAQ en quelques clics via notre générateur.</p>
                <a href="generateur.html" class="btn btn-primary">
                    Créer la première FAQ
                </a>
            </div>
        <?php endif; ?>

    </div>

    <footer>
        &copy; <?= date('Y') ?> KREANCIA. Tous droits réservés.
    </footer>

    <script>
        function filterFaqs() {
            const query = document.getElementById('search-bar').value.toLowerCase().trim();
            const cards = document.querySelectorAll('.faq-card');
            let visibleCount = 0;

            cards.forEach(card => {
                const name = card.getAttribute('data-name');
                const desc = card.getAttribute('data-desc');
                const products = card.getAttribute('data-products');

                if (name.includes(query) || desc.includes(query) || products.includes(query)) {
                    card.style.display = 'flex';
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                }
            });

            const counter = document.getElementById('results-count');

            if (query === '') {
                counter.textContent = `Affichage de ${cards.length} partenaire${cards.length > 1 ? 's' : ''}`;
            } else {
                counter.textContent = `${visibleCount} résultat${visibleCount > 1 ? 's' : ''} trouvé${visibleCount > 1 ? 's' : ''}`;
            }
        }
    </script>
</body>
</html>
