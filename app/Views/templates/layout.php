<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Caisse supermarché — saisie des achats, gestion de caisse CodeIgniter">
    <title><?= esc($titre ?? 'Caisse') ?> — Caisse Supermarché</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/style.css') ?>">
</head>
<body>

<div class="app-shell">

    <!-- Liseré code-barres — signature de chaque écran -->
    <div class="barcode-strip" aria-hidden="true"></div>

    <!-- ================================================
         TOPBAR — caissier connecté + caisse active
         Les données viennent UNIQUEMENT de la session.
         ================================================ -->
    <header class="app-topbar" role="banner">
        <div class="app-topbar__inner container">

            <!-- Nom de l'application -->
            <span class="app-topbar__brand">🛒 Caisse Supermarché</span>

            <!-- Méta-session : caissier + caisse -->
            <div class="app-topbar__meta">

                <?php if (session()->get('user_id')): ?>
                    <!-- Puce Caissier -->
                    <div class="topbar-chip">
                        <span class="topbar-chip__label">Caissier</span>
                        <span class="topbar-chip__value">
                            <?= esc(session()->get('user_nom') ?? 'Caissier') ?>
                        </span>
                    </div>
                <?php endif; ?>

                <?php if (session()->get('caisse_id')): ?>
                    <!-- Puce Caisse -->
                    <div class="topbar-chip">
                        <span class="topbar-chip__label">Caisse</span>
                        <span class="topbar-chip__value num">
                            N°<?= esc(session()->get('caisse_numero') ?? session()->get('caisse_id')) ?>
                        </span>
                    </div>
                <?php endif; ?>

            </div><!-- /.app-topbar__meta -->
        </div><!-- /.container -->

        <!-- Navigation principale -->
        <nav class="container" aria-label="Navigation principale">
            <ul class="app-nav" role="list">
                <li>
                    <a href="<?= site_url('achat/saisie') ?>"
                       <?= (uri_string() === 'achat/saisie') ? 'aria-current="page"' : '' ?>>
                        Saisie des achats
                    </a>
                </li>
                <?php if (session()->get('user_id')): ?>
                <li>
                    <a href="<?= site_url('auth/deconnexion') ?>">Déconnexion</a>
                </li>
                <?php endif; ?>
            </ul>
        </nav>

    </header><!-- /.app-topbar -->

    <!-- ================================================
         CONTENU PRINCIPAL
         get_defined_vars() transmet toutes les variables
         du Controller à la vue enfant (panier, produits…).
         C'est le pattern standard CI4 pour les layouts.
         ================================================ -->
    <main class="app-main" id="main-content" role="main">
        <div class="container">
            <?= view($vue_contenu ?? 'errors/html/error_404', get_defined_vars()) ?>
        </div>
    </main>

    <!-- Pied de page -->
    <footer class="app-footer" role="contentinfo">
        <div class="container">
            <p class="app-footer__text">
                Caisse Supermarché — Projet CodeIgniter / ITUNIVERSITY 2026
            </p>
        </div>
    </footer>

</div><!-- /.app-shell -->

</body>
</html>
