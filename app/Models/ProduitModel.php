<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * ProduitModel
 *
 * Toutes les requêtes liées à la table `produit` passent par ici.
 * Le Controller ne touche jamais à la base de données directement.
 */
class ProduitModel extends Model
{
    // Nom de la table en base
    protected $table      = 'produit';

    // Clé primaire
    protected $primaryKey = 'id_produit';

    // Colonnes que l'on peut écrire (whitelist de sécurité)
    protected $allowedFields = ['designation', 'prix', 'quantite_stock'];

    // Retourner les résultats sous forme de tableau associatif
    protected $returnType = 'array';

    // ---------------------------------------------------------------
    // MÉTHODES PUBLIQUES
    // ---------------------------------------------------------------

    /**
     * Retourne tous les produits triés alphabétiquement.
     * Utilisé pour peupler la liste déroulante de saisie.
     *
     * @return array<int, array<string, mixed>>
     */
    public function tousLesProduits(): array
    {
        return $this->orderBy('designation', 'ASC')->findAll();
    }

    /**
     * Retourne un produit par son identifiant.
     * Utilisé lors de l'ajout au panier pour récupérer le prix actuel.
     *
     * @param int $id L'identifiant du produit
     * @return array<string, mixed>|null  null si le produit n'existe pas
     */
    public function trouverParId(int $id): ?array
    {
        return $this->find($id);
    }

    /**
     * Décrémente le stock d'un produit d'une quantité donnée.
     * Utilisé lors de la clôture de l'achat.
     *
     * @param int $idProduit  Identifiant du produit
     * @param int $quantite   Quantité à déduire
     * @return bool
     */
    public function decrementerStock(int $idProduit, int $quantite): bool
    {
        // On s'assure de ne pas passer en stock négatif
        return $this->db->query(
            'UPDATE produit SET quantite_stock = MAX(0, quantite_stock - ?) WHERE id_produit = ?',
            [$quantite, $idProduit]
        );
    }
}
