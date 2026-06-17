<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// ---------------------------------------------------------------
// Page d'accueil — redirige vers la saisie des achats
// (en production ce sera le login — géré par l'étudiant A)
// ---------------------------------------------------------------
$routes->get('/', 'Achat::saisie');

// ---------------------------------------------------------------
// Module Achat (étudiant B)
// ---------------------------------------------------------------
$routes->get('achat/saisie',               'Achat::saisie');
$routes->post('achat/ajouter',             'Achat::ajouter');
$routes->post('achat/supprimer-ligne',     'Achat::supprimerLigne');
$routes->post('achat/vider-panier',        'Achat::viderPanier');
