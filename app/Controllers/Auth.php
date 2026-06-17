<?php

namespace App\Controllers;

use App\Models\CaissierModel;

class Auth extends BaseController
{
    public function login()
    {
        if (session()->get('isLoggedIn')) {
            return redirect()->to('/dashboard');
        }

        if (strtolower($this->request->getMethod()) === 'post') {
            $rules = [
                'email' => 'required|valid_email',
                'mot_de_passe' => 'required'
            ];

            if ($this->validate($rules)) {
                $model = new CaissierModel();
                $email = $this->request->getPost('email');
                $password = $this->request->getPost('mot_de_passe');
                
                $caissier = $model->where('email', $email)->first();

                if ($caissier) {
                    if (password_verify((string)$password, $caissier['mot_de_passe'])) {
                        $sessionData = [
                            'id_caissier' => $caissier['id_caissier'],
                            'email'       => $caissier['email'],
                            'isLoggedIn'  => true
                        ];
                        session()->set($sessionData);
                        return redirect()->to('/dashboard');
                    } else {
                        session()->setFlashdata('error', 'Mot de passe incorrect.');
                    }
                } else {
                    session()->setFlashdata('error', 'Email introuvable.');
                }
            } else {
                $data['validation'] = $this->validator;
                return view('auth/login', $data);
            }
        }

        return view('auth/login');
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to('/login');
    }
}
