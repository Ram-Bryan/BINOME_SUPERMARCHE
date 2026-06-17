<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// ---------------------------------------------------------------
// Page d'accueil — redirige vers la saisie des achats
// (en production ce sera le login — géré par l'étudiant A)
// ---------------------------------------------------------------
$routes->get('achats', 'Achat::saisie');

// ---------------------------------------------------------------
// Module Achat (étudiant B)
// ---------------------------------------------------------------
$routes->get('achats/saisie',               'Achat::saisie');
$routes->post('achats/ajouter',             'Achat::ajouter');
$routes->post('achats/supprimer-ligne',     'Achat::supprimerLigne');
$routes->post('achats/vider-panier',        'Achat::viderPanier');

$routes->get('/', 'Auth::login');
$routes->match(['get', 'post'], 'login', 'Auth::login');
$routes->get('logout', 'Auth::logout');
$routes->get('dashboard', 'Dashboard::index');
$routes->post('caisse/valider', 'Dashboard::validerCaisse');