<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Facture - Supermarché</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/style.css') ?>">
    <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@700&family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body { background: white; padding: 40px; color: #000; }
        .invoice-box { max-width: 800px; margin: auto; padding: 30px; border: 1px solid #eee; box-shadow: 0 0 10px rgba(0, 0, 0, 0.15); font-size: 16px; line-height: 24px; font-family: 'Inter', sans-serif; }
        .invoice-box table { width: 100%; line-height: inherit; text-align: left; }
        .invoice-box table td { padding: 5px; vertical-align: top; }
        .invoice-box table tr td:nth-child(n+2) { text-align: right; }
        .invoice-box table tr.top table td { padding-bottom: 20px; }
        .invoice-box table tr.top table td.title { font-size: 45px; line-height: 45px; color: var(--primary-color); font-family: 'Barlow Condensed', sans-serif; }
        .invoice-box table tr.information table td { padding-bottom: 40px; }
        .invoice-box table tr.heading td { background: #eee; border-bottom: 1px solid #ddd; font-weight: bold; }
        .invoice-box table tr.details td { padding-bottom: 20px; }
        .invoice-box table tr.item td { border-bottom: 1px solid #eee; }
        .invoice-box table tr.item.last td { border-bottom: none; }
        .invoice-box table tr.total td:nth-child(2) { border-top: 2px solid #eee; font-weight: bold; font-size: 20px; color: var(--accent-color); }
        @media print {
            body { padding: 0; background: white; }
            .invoice-box { box-shadow: none; border: none; margin: 0; padding: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="text-align: center; margin-bottom: 20px;">
        <button onclick="window.print()" class="btn btn--primary">🖨 Imprimer la facture</button>
        <a href="<?= site_url('achat/saisie') ?>" class="btn btn--ghost">Retour</a>
    </div>

    <div class="invoice-box">
        <table cellpadding="0" cellspacing="0">
            <tr class="top">
                <td colspan="4">
                    <table>
                        <tr>
                            <td class="title">
                                SUPERMARCHÉ
                            </td>
                            
                            <td>
                                Date : <?= isset($date_achat) ? esc(date('d/m/Y H:i', strtotime($date_achat))) : date('d/m/Y H:i') ?><br>
                                Caisse : <?= esc($caisse_numero) ?><br>
                                Caissier : <?= esc($caissier) ?>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>

            <tr class="heading">
                <td>Produit</td>
                <td>Prix unitaire</td>
                <td>Quantité</td>
                <td>Montant</td>
            </tr>

            <?php if (empty($panier)): ?>
                <tr class="item">
                    <td colspan="4" style="text-align: center;">Aucun achat enregistré.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($panier as $index => $ligne): ?>
                    <tr class="item <?= $index === array_key_last($panier) ? 'last' : '' ?>">
                        <td><?= esc($ligne['designation']) ?></td>
                        <td><?= esc(number_format($ligne['prix_unitaire'], 0, ',', ' ')) ?> Ar</td>
                        <td><?= esc($ligne['quantite']) ?></td>
                        <td><?= esc(number_format($ligne['montant'], 0, ',', ' ')) ?> Ar</td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>

            <tr class="total">
                <td colspan="2"></td>
                <td>TOTAL:</td>
                <td><?= esc(number_format($total, 0, ',', ' ')) ?> Ar</td>
            </tr>
        </table>
    </div>
</body>
</html>
