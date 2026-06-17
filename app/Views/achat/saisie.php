<?= $this->extend('layout/default') ?>

<?= $this->section('title') ?><?= esc($titre) ?><?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="page-header">
    <span class="eyebrow">Saisie</span>
    <h1 class="page-title">Saisie des achats</h1>
</div>

<div id="alerts-container">
<?php if (! empty($flash_success)): ?>
    <div class="alert alert--success" role="status" aria-live="polite">
        <span aria-hidden="true">✓</span>
        <?= $flash_success ?>
    </div>
<?php endif; ?>

<?php if (! empty($flash_error)): ?>
    <div class="alert alert--error" role="alert" aria-live="assertive">
        <span aria-hidden="true">⚠</span>
        <?= $flash_error ?>
    </div>
<?php endif; ?>
</div>

<section aria-labelledby="client-titre">
    <h2 id="client-titre" class="section-label">Informations Client</h2>
    <div class="client-selection">
        <label class="field__label" for="id_client">Sélectionner un client</label>
        <select class="select" id="id_client" name="id_client" form="form-cloture" onchange="toggleNouveauClient()" required>
            <option value="" disabled selected>— Choisir un client —</option>
            <option value="NEW">-- Nouveau Client --</option>
            <?php foreach ($clients as $c): ?>
                <option value="<?= esc($c['id_client']) ?>">
                    <?= esc($c['nom']) ?> <?= $c['telephone'] ? '('.esc($c['telephone']).')' : '' ?>
                </option>
            <?php endforeach; ?>
        </select>
        
        <div id="nouveau-client-fields" style="display:none; margin-top:1rem;">
            <div class="form-row">
                <div class="field">
                    <label class="field__label" for="nouveau_client_nom">Nom du client</label>
                    <input type="text" id="nouveau_client_nom" name="nouveau_client_nom" class="input" form="form-cloture">
                </div>
                <div class="field">
                    <label class="field__label" for="nouveau_client_telephone">Téléphone</label>
                    <input type="text" id="nouveau_client_telephone" name="nouveau_client_telephone" class="input" form="form-cloture">
                </div>
                <div class="field">
                    <label class="field__label" for="nouveau_client_email">Email</label>
                    <input type="email" id="nouveau_client_email" name="nouveau_client_email" class="input" form="form-cloture">
                </div>
            </div>
        </div>
    </div>
</section>

<section aria-labelledby="form-ajout-titre">
    <h2 id="form-ajout-titre" class="section-label">Ajouter un article</h2>
    <?= form_open(site_url('achat/ajouter'), ['id' => 'form-ajout-produit']) ?>
        <div class="form-row">
            <div class="field field--grow">
                <label class="field__label" for="id_produit">Produit</label>
                <select class="select" id="id_produit" name="id_produit" required aria-required="true">
                    <option value="" disabled selected>— Choisir un produit —</option>
                    <?php foreach ($produits as $p): ?>
                        <option value="<?= esc($p['id_produit']) ?>">
                            <?= esc($p['designation']) ?> — <?= esc(number_format($p['prix'], 0, ',', ' ')) ?> Ar (stock : <?= esc($p['quantite_stock']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field">
                <label class="field__label" for="quantite-input">Quantité</label>
                <div class="qty-stepper">
                    <button type="button" class="qty-stepper__btn" id="btn-moins" onclick="stepperChange(-1)">−</button>
                    <input type="number" class="qty-stepper__input" id="quantite-input" name="quantite" value="1" min="1" max="9999" required>
                    <button type="button" class="qty-stepper__btn" id="btn-plus" onclick="stepperChange(1)">+</button>
                </div>
            </div>
            <div class="field field--action">
                <span class="field__label" aria-hidden="true"></span>
                <button type="submit" class="btn btn--primary" id="btn-ajouter-produit">＋ Ajouter</button>
            </div>
        </div>
    <?= form_close() ?>
</section>

<section class="panier-section" aria-labelledby="panier-titre">
    <div class="ticket">
        <div class="ticket__header">
            <h2 id="panier-titre" class="eyebrow eyebrow--flush">Panier en cours</h2>
            <?php if (! empty($panier)): ?>
                <span class="badge badge--neutral"><?= count($panier) ?> article(s)</span>
            <?php endif; ?>
        </div>

        <?php if (empty($panier)): ?>
            <div class="ticket-empty">
                <div class="ticket-empty__icon" aria-hidden="true">🛒</div>
                <p>Aucun article pour le moment.</p>
            </div>
        <?php else: ?>
            <div class="table-scroll">
                <table class="ticket-table">
                    <thead>
                        <tr>
                            <th scope="col">Produit</th>
                            <th scope="col" class="is-numeric">Prix unit.</th>
                            <th scope="col" class="is-numeric">Qté</th>
                            <th scope="col" class="is-numeric">Montant</th>
                            <th scope="col"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($panier as $ligne): ?>
                            <tr>
                                <td><?= esc($ligne['designation']) ?></td>
                                <td class="is-numeric num"><?= esc(number_format($ligne['prix_unitaire'], 0, ',', ' ')) ?> Ar</td>
                                <td class="is-numeric num"><?= esc($ligne['quantite']) ?></td>
                                <td class="is-numeric num"><?= esc(number_format($ligne['montant'], 0, ',', ' ')) ?> Ar</td>
                                <td>
                                    <?= form_open(site_url('achat/supprimer-ligne'), ['class' => 'form-inline']) ?>
                                        <input type="hidden" name="id_produit" value="<?= esc($ligne['id_produit']) ?>">
                                        <button type="submit" class="btn btn--danger btn--sm" onclick="return confirm('Retirer ?')">✕</button>
                                    <?= form_close() ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr class="ticket__total-row">
                            <td colspan="3">TOTAL</td>
                            <td class="is-numeric ticket__total-value"><?= esc(number_format($total, 0, ',', ' ')) ?> Ar</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="ticket__footer">
                <?= form_open(site_url('achat/vider-panier'), ['class' => 'form-inline', 'id' => 'form-vider']) ?>
                    <button type="submit" class="btn btn--ghost" id="btn-vider-panier" onclick="return confirm('Vider tout le panier ?')">
                        🗑 Vider le panier
                    </button>
                <?= form_close() ?>

                <?= form_open(site_url('achat/cloturer'), ['class' => 'form-cloture', 'id' => 'form-cloture']) ?>
                    <button type="submit" class="btn btn--accent" id="btn-cloturer">
                        ✔ Clôturer l'achat
                    </button>
                <?= form_close() ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<script>
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

function toggleNouveauClient() {
    const select = document.getElementById('id_client');
    const fields = document.getElementById('nouveau-client-fields');
    const nomInput = document.getElementById('nouveau_client_nom');
    if (select.value === 'NEW') {
        fields.style.display = 'block';
        nomInput.required = true;
    } else {
        fields.style.display = 'none';
        nomInput.required = false;
    }
}

document.addEventListener('DOMContentLoaded', function () {
    const input = document.getElementById('quantite-input');
    if (input) {
        const v   = parseInt(input.value, 10) || 1;
        const min = parseInt(input.min, 10)   || 1;
        const max = parseInt(input.max, 10)   || 9999;
        document.getElementById('btn-moins').disabled = (v <= min);
        document.getElementById('btn-plus').disabled  = (v >= max);
    }
});

document.addEventListener('submit', async function(e) {
    const form = e.target;
    if (form.id === 'form-ajout-produit') {
        e.preventDefault();
        
        const submitBtn = document.getElementById('btn-ajouter-produit');
        const originalText = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = 'Ajout...';

        try {
            const response = await fetch(form.action, {
                method: form.method,
                body: new FormData(form),
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });

            if (response.ok) {
                const html = await response.text();
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');

                const alertsContainer = document.getElementById('alerts-container');
                if (alertsContainer && doc.getElementById('alerts-container')) {
                    alertsContainer.innerHTML = doc.getElementById('alerts-container').innerHTML;
                }

                const idProduit = document.getElementById('id_produit');
                if (idProduit && doc.getElementById('id_produit')) {
                    idProduit.innerHTML = doc.getElementById('id_produit').innerHTML;
                }

                const panierSection = document.querySelector('.panier-section');
                if (panierSection && doc.querySelector('.panier-section')) {
                    panierSection.innerHTML = doc.querySelector('.panier-section').innerHTML;
                }

                const quantiteInput = document.getElementById('quantite-input');
                if (quantiteInput) {
                    quantiteInput.value = 1;
                    stepperChange(0);
                }
            }
        } catch (error) {
            console.error('Erreur AJAX:', error);
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    }
});
</script>

<?= $this->endSection() ?>
