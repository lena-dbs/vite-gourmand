<?php
declare(strict_types=1);

class AuthController extends Controller
{
    private UserModel $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    public function login(): void
    {
        $error = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email    = trim($_POST['email'] ?? '');
            $password = trim($_POST['password'] ?? '');

            $user = $this->userModel->findByEmail($email);

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user'] = [
                    'id'     => $user['utilisateur_id'],
                    'nom'    => $user['nom'],
                    'prenom' => $user['prenom'],
                    'email'  => $user['email'],
                    'role'   => $user['role'],
                ];

                if ($user['role'] === 'administrateur') {
                    $this->redirect('/admin');
                } elseif ($user['role'] === 'employe') {
                    $this->redirect('/employe');
                } else {
                    $this->redirect('/mon-compte');
                }
            } else {
                $error = 'Email ou mot de passe incorrect.';
            }
        }

        $this->render('auth/login', [
            'title' => 'Connexion',
            'error' => $error,
        ]);
    }

    public function register(): void
    {
        $error = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'email'       => trim($_POST['email'] ?? ''),
                'password'    => trim($_POST['password'] ?? ''),
                'nom'         => trim($_POST['nom'] ?? ''),
                'prenom'      => trim($_POST['prenom'] ?? ''),
                'telephone'   => trim($_POST['telephone'] ?? ''),
                'adresse'     => trim($_POST['adresse'] ?? ''),
                'ville'       => trim($_POST['ville'] ?? ''),
                'code_postal' => trim($_POST['code_postal'] ?? ''),
                'pays'        => trim($_POST['pays'] ?? 'France'),
            ];

            if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{10,}$/', $data['password'])) {
                $error = 'Le mot de passe doit contenir au moins 10 caractères, une majuscule, une minuscule, un chiffre et un caractère spécial.';
            } elseif ($this->userModel->findByEmail($data['email'])) {
                $error = 'Un compte existe déjà avec cet email.';
            } else {
                $this->userModel->create($data);
                $this->redirect('/connexion?success=1');
            }
        }

        $this->render('auth/register', [
            'title' => 'Créer un compte',
            'error' => $error,
        ]);
    }

    public function logout(): void
    {
        session_destroy();
        $this->redirect('/');
    }


    public function forgotPassword(): void
{
    $error   = null;
    $success = false;

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $email = trim($_POST['email'] ?? '');
        $user  = $this->userModel->findByEmail($email);

        if ($user) {
            $token     = bin2hex(random_bytes(32));
            $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));

            $this->userModel->createPasswordReset($user['utilisateur_id'], $token, $expiresAt);

            $link = APP_URL . '/reinitialiser-mot-de-passe?token=' . $token;
            mail(
                $email,
                'Réinitialisation de votre mot de passe — Vite & Gourmand',
                "Bonjour,\n\nCliquez sur ce lien pour réinitialiser votre mot de passe :\n$link\n\nCe lien expire dans 1 heure.",
                'From: jose@vitegourmand.fr'
            );
        }

        $success = true;
    }

    $this->render('auth/forgot-password', [
        'title'   => 'Mot de passe oublié',
        'error'   => $error,
        'success' => $success,
    ]);
}

public function resetPassword(): void
{
    $token = $_GET['token'] ?? '';
    $error = null;

    $reset = $this->userModel->findValidToken($token);

    if (!$reset) {
        $this->render('auth/reset-password', [
            'title' => 'Lien invalide',
            'error' => 'Ce lien est invalide ou a expiré.',
            'token' => '',
        ]);
        return;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $password = trim($_POST['password'] ?? '');

        if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{10,}$/', $password)) {
            $error = 'Le mot de passe doit contenir au moins 10 caractères, une majuscule, une minuscule, un chiffre et un caractère spécial.';
        } else {
            $this->userModel->updatePassword($reset['utilisateur_id'], $password);
            $this->userModel->markTokenUsed($reset['token_id']);
            $this->redirect('/connexion?success=1');
        }
    }

    $this->render('auth/reset-password', [
        'title' => 'Réinitialiser le mot de passe',
        'error' => $error,
        'token' => $token,
    ]);
}

}

