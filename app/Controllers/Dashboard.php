<?php

namespace App\Controllers;

use App\Models\CaisseModel;

class Dashboard extends BaseController
{
    public function index()
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $caisseModel = new CaisseModel();
        $data['caisses'] = $caisseModel->findAll();

        return view('caisse/choix', $data);
    }

    public function validerCaisse()
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $caisseId = $this->request->getPost('caisse_id');
        $caisseModel = new CaisseModel();
        $caisse = $caisseModel->find($caisseId);

        if ($caisse) {
            session()->set([
                'caisse_id' => $caisse['id_caisse'],
                'caisse_numero' => $caisse['numero'],
                'caisse_libelle' => $caisse['libelle']
            ]);
            return redirect()->to('/achats');
        }

        return redirect()->back()->with('error', 'Caisse invalide.');
    }
}
