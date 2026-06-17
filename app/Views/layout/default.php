<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $this->renderSection('title') ?> - Supermarché</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/style.css') ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="app-body">
    <?php if(session()->get('isLoggedIn')): ?>
        <header class="top-header">
            <div class="header-container">
                <div class="session-info">
                    <div class="info-badge caissier-badge">
                        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                        <span>Caissier : <strong><?= esc(session()->get('email')) ?></strong></span>
                    </div>
                    
                    <?php if(session()->get('caisse_id')): ?>
                        <div class="info-badge caisse-badge">
                            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                            <span>Caisse : <strong><?= esc(session()->get('caisse_numero')) ?> (<?= esc(session()->get('caisse_libelle')) ?>)</strong></span>
                        </div>
                    <?php else: ?>
                        <div class="info-badge caisse-badge pending">
                            <span>Aucune caisse sélectionnée</span>
                        </div>
                    <?php endif; ?>
                </div>
                
                <a href="<?= base_url('logout') ?>" class="btn-logout">Déconnexion</a>
            </div>
        </header>

        <nav class="main-nav">
            <div class="nav-container">
                <a href="<?= base_url('dashboard') ?>" class="nav-link">Changer de caisse</a>
                <?php if(session()->get('caisse_id')): ?>
                    <a href="<?= base_url('achats') ?>" class="nav-link">Saisie des achats</a>
                <?php endif; ?>
            </div>
        </nav>
    <?php endif; ?>

    <main class="main-content">
        <?= $this->renderSection('content') ?>
    </main>
</body>
</html>
