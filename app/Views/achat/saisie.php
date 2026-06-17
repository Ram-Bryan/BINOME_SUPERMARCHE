<?php
/**
 * Vue : achat/saisie.php
 *
 * Page de saisie des achats.
 * Rendu par : Achat::saisie() → templates/layout.php → ici.
 *
 * Variables injectées par le Controller :
 *   $produits       array        — tous les produits (ProduitModel::getAll)
 *   $panier         array        — panier en session (peut être vide [])
 *   $total          float        — somme des montants du panier
 *   $flash_success  string|null  — message flash succès
 *   $flash_error    string|null  — message flash erreur
 *
 * Règles respectées :
 *   - Aucun accès BDD ici (MVC strict)
 *   - Aucun inline style (tout dans public/assets/css/style.css)
 *   - Tout output utilisateur passé par esc()
 *   - CSRF géré par form_open() (champ caché automatique)
 */
?>

<!-- ====================================================
     EN-TÊTE DE PAGE
     ==================================================== -->
<div class="page-header">
    <span class="eyebrow">Saisie</span>
    <h1 class="page-title">Saisie des achats</h1>
</div>

<!-- ====================================================
     MESSAGES FLASH
     Affichés une seule fois puis effacés par CI4.
     ==================================================== -->
<?php if (! empty($flash_success)): ?>
    <div class="alert alert--success" role="status" aria-live="polite">
        <span aria-hidden="true">✓</span>
        <?= $flash_success /* déjà esc() dans le Controller */ ?>
    </div>
<?php endif; ?>

<?php if (! empty($flash_error)): ?>
    <div class="alert alert--error" role="alert" aria-live="assertive">
        <span aria-hidden="true">⚠</span>
        <?= $flash_error /* déjà esc() dans le Controller */ ?>
    </div>
<?php endif; ?>

<!-- ====================================================
     PARTIE HAUTE — Formulaire d'ajout au panier
     POST → Achat::ajouter()
     form_open() génère automatiquement le champ CSRF caché.
     ==================================================== -->
<section aria-labelledby="form-ajout-titre">

    <h2 id="form-ajout-titre" class="section-label">Ajouter un article</h2>

    <?= form_open(site_url('achat/ajouter'), ['id' => 'form-ajout-produit']) ?>

        <div class="form-row">

            <!-- ---- Liste déroulante des produits ---- -->
            <div class="field field--grow">
                <label class="field__label" for="id_produit">Produit</label>

                <select
                    class="select"
                    id="id_produit"
                    name="id_produit"
                    required
                    aria-required="true"
                    aria-describedby="id_produit-hint"
                >
                    <option value="" disabled selected>— Choisir un produit —</option>

                    <?php foreach ($produits as $p): ?>
                        <option
                            value="<?= esc($p['id_produit']) ?>"
                            data-prix="<?= esc($p['prix']) ?>"
                            data-stock="<?= esc($p['quantite_stock']) ?>"
                        >
                            <?= esc($p['designation']) ?>
                            — <?= esc(number_format($p['prix'], 0, ',', ' ')) ?> FCFA
                            (stock : <?= esc($p['quantite_stock']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>

                <span id="id_produit-hint" class="field__hint">
                    <?= count($produits) ?> produit(s) disponible(s)
                </span>
            </div><!-- /.field -->

            <!-- ---- Stepper quantité ---- -->
            <div class="field">
                <label class="field__label" for="quantite-input">Quantité</label>

                <div class="qty-stepper" role="group" aria-label="Saisie de la quantité">
                    <button
                        type="button"
                        class="qty-stepper__btn"
                        id="btn-moins"
                        aria-label="Diminuer la quantité"
                        onclick="stepperChange(-1)"
                    >−</button>

                    <input
                        type="number"
                        class="qty-stepper__input"
                        id="quantite-input"
                        name="quantite"
                        value="1"
                        min="1"
                        max="9999"
                        required
                        aria-required="true"
                        aria-label="Quantité"
                    >

                    <button
                        type="button"
                        class="qty-stepper__btn"
                        id="btn-plus"
                        aria-label="Augmenter la quantité"
                        onclick="stepperChange(1)"
                    >+</button>
                </div><!-- /.qty-stepper -->
            </div><!-- /.field -->

            <!-- ---- Bouton Ajouter ---- -->
            <div class="field field--action">
                <!-- Label invisible pour aligner verticalement avec les autres champs -->
                <span class="field__label" aria-hidden="true"></span>
                <button type="submit" class="btn btn--primary" id="btn-ajouter-produit">
                    ＋ Ajouter
                </button>
            </div>

        </div><!-- /.form-row -->

    <?= form_close() ?>

</section>

<!-- ====================================================
     PARTIE BASSE — Récapitulatif du panier (ticket)
     ==================================================== -->
<section class="panier-section" aria-labelledby="panier-titre">

    <div class="ticket">

        <!-- En-tête du ticket -->
        <div class="ticket__header">
            <h2 id="panier-titre" class="eyebrow eyebrow--flush">Panier en cours</h2>
            <?php if (! empty($panier)): ?>
                <span class="badge badge--neutral"><?= count($panier) ?> article(s)</span>
            <?php endif; ?>
        </div>

        <?php if (empty($panier)): ?>
            <!-- État vide — affiché tant qu'aucun produit n'est ajouté -->
            <div class="ticket-empty" role="status" aria-live="polite">
                <div class="ticket-empty__icon" aria-hidden="true">🛒</div>
                <p>Aucun article pour le moment.</p>
                <p class="ticket-empty__sub">
                    Utilisez le formulaire ci-dessus pour ajouter des produits.
                </p>
            </div>

        <?php else: ?>
            <!-- Tableau des lignes du panier -->
            <div class="table-scroll">
                <table class="ticket-table" aria-label="Détail du panier en cours">
                    <thead>
                        <tr>
                            <th scope="col">Produit</th>
                            <th scope="col" class="is-numeric">Prix unit.</th>
                            <th scope="col" class="is-numeric">Qté</th>
                            <th scope="col" class="is-numeric">Montant</th>
                            <th scope="col"><span class="sr-only">Action</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($panier as $ligne): ?>
                            <tr>
                                <td><?= esc($ligne['designation']) ?></td>
                                <td class="is-numeric num">
                                    <?= esc(number_format($ligne['prix_unitaire'], 0, ',', ' ')) ?> FCFA
                                </td>
                                <td class="is-numeric num"><?= esc($ligne['quantite']) ?></td>
                                <td class="is-numeric num">
                                    <?= esc(number_format($ligne['montant'], 0, ',', ' ')) ?> FCFA
                                </td>
                                <td>
                                    <?= form_open(site_url('achat/supprimer-ligne'), ['class' => 'form-inline']) ?>
                                        <input type="hidden" name="id_produit"
                                               value="<?= esc($ligne['id_produit']) ?>">
                                        <button
                                            type="submit"
                                            class="btn btn--danger btn--sm"
                                            aria-label="Retirer <?= esc($ligne['designation']) ?> du panier"
                                            onclick="return confirm('Retirer cet article du panier ?')"
                                        >✕</button>
                                    <?= form_close() ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr class="ticket__total-row">
                            <td colspan="3">TOTAL</td>
                            <td class="is-numeric ticket__total-value">
                                <?= esc(number_format($total, 0, ',', ' ')) ?> FCFA
                            </td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div><!-- /.table-scroll -->

            <!-- Actions globales du panier -->
            <div class="ticket__footer">
                <?= form_open(site_url('achat/vider-panier'), ['class' => 'form-inline']) ?>
                    <button
                        type="submit"
                        class="btn btn--ghost"
                        id="btn-vider-panier"
                        onclick="return confirm('Vider tout le panier pour ce client ?')"
                    >
                        🗑 Vider le panier
                    </button>
                <?= form_close() ?>

                <!-- Clôturer — désactivé (tâche suivante) -->
                <button
                    type="button"
                    class="btn btn--accent"
                    id="btn-cloturer"
                    disabled
                    aria-disabled="true"
                    title="Disponible à la prochaine tâche"
                >
                    ✔ Clôturer l'achat
                </button>
            </div><!-- /.ticket__footer -->

        <?php endif; ?>

    </div><!-- /.ticket -->

</section>

<!-- ====================================================
     JAVASCRIPT — Stepper quantité
     Vanilla JS uniquement, sans framework.
     ==================================================== -->
<script>
/**
 * stepperChange — modifie la valeur du champ quantité.
 * @param {number} delta  +1 pour augmenter, -1 pour diminuer
 */
function stepperChange(delta) {
    const input = document.getElementById('quantite-input');
    if (!input) return;

    const min = parseInt(input.min, 10) || 1;
    const max = parseInt(input.max, 10) || 9999;
    const val = Math.min(max, Math.max(min, (parseInt(input.value, 10) || 1) + delta));

    input.value = val;
    document.getElementById('btn-moins').disabled = (val <= min);
    document.getElementById('btn-plus').disabled  = (val >= max);
}

// Initialisation au chargement
document.addEventListener('DOMContentLoaded', function () {
    // Recalcule l'état sans changer la valeur
    const input = document.getElementById('quantite-input');
    if (input) {
        const v   = parseInt(input.value, 10) || 1;
        const min = parseInt(input.min, 10)   || 1;
        const max = parseInt(input.max, 10)   || 9999;
        document.getElementById('btn-moins').disabled = (v <= min);
        document.getElementById('btn-plus').disabled  = (v >= max);
    }
});
</script>
