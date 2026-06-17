<?= $this->extend('layout/default') ?>

<?= $this->section('title') ?>Liste des achats<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="page-header">
    <span class="eyebrow">Historique</span>
    <h1 class="page-title">Liste des achats</h1>
</div>

<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert--error"><?= session()->getFlashdata('error') ?></div>
<?php endif; ?>

<div class="ticket">
    <div class="table-scroll">
        <table class="ticket-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Date</th>
                    <th>Caisse</th>
                    <th>Caissier</th>
                    <th>Statut</th>
                    <th class="is-numeric">Total</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($achats)): ?>
                    <tr>
                        <td colspan="7" style="text-align: center;">Aucun achat enregistré.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($achats as $achat): ?>
                        <tr>
                            <td>#<?= esc($achat['id_achat']) ?></td>
                            <td><?= esc(date('d/m/Y H:i', strtotime($achat['date_achat']))) ?></td>
                            <td><?= esc($achat['caisse_numero']) ?></td>
                            <td><?= esc($achat['caissier_email']) ?></td>
                            <td>
                                <?php if ($achat['statut'] === 'cloture'): ?>
                                    <span style="color: #10b981; font-weight: 600;">Clôturé</span>
                                <?php else: ?>
                                    <span style="color: #6b7280;"><?= esc($achat['statut']) ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="is-numeric num"><?= esc(number_format($achat['total'], 0, ',', ' ')) ?> Ar</td>
                            <td>
                                <a href="<?= site_url('achat/export/' . $achat['id_achat']) ?>" target="_blank" class="btn btn--sm btn--secondary">
                                    🖨 Exporter
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?= $this->endSection() ?>
