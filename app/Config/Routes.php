<?php

use CodeIgniter\Router\RouteCollection;

$routes->get('achats', 'Achat::saisie');

$routes->get('achat/saisie',               'Achat::saisie');
$routes->post('achat/ajouter',             'Achat::ajouter');
$routes->post('achat/supprimer-ligne',     'Achat::supprimerLigne');
$routes->post('achat/vider-panier',        'Achat::viderPanier');
$routes->post('achat/cloturer',            'Achat::cloturer');
$routes->get('achat/liste',                'Achat::liste');
$routes->get('achat/export/(:num)',        'Achat::exportFacture/$1');

$routes->get('/', 'Auth::login');
$routes->match(['get', 'post'], 'login', 'Auth::login');
$routes->get('logout', 'Auth::logout');
$routes->get('dashboard', 'Dashboard::index');
$routes->post('caisse/valider', 'Dashboard::validerCaisse');