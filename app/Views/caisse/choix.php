<?= $this->extend('layout/default') ?>

<?= $this->section('title') ?>Choix de la Caisse<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="dashboard-container">
    <div class="card form-card">
        <div class="card-header">
            <h2>Choix de la caisse</h2>
            <p>Veuillez sélectionner la caisse sur laquelle vous allez opérer.</p>
        </div>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger">
                <?= session()->getFlashdata('error') ?>
            </div>
        <?php endif; ?>

        <form action="<?= base_url('caisse/valider') ?>" method="post" class="caisse-form">
            <?= csrf_field() ?>
            
            <div class="form-group">
                <label for="caisse_id">Sélectionnez une caisse :</label>
                <select name="caisse_id" id="caisse_id" class="form-control form-select" required>
                    <option value="" disabled selected>-- Choisir une caisse --</option>
                    <?php foreach($caisses as $caisse): ?>
                        <option value="<?= $caisse['id_caisse'] ?>">
                            Caisse n° <?= esc($caisse['numero']) ?> - <?= esc($caisse['libelle']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-actions mt-4">
                <button type="submit" class="btn btn-primary btn-block">Valider la caisse</button>
            </div>
        </form>
    </div>
</div>
<?= $this->endSection() ?>
