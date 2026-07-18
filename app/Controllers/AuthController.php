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
            $this->verifyCsrf();

            $email    = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $ip       = $this->clientIp();

            // 5 échecs par email ou 20 par IP sur 15 minutes
            $tentatives = $this->userModel->countRecentAttempts($email, $ip, 'login', 900);
            if ($tentatives['par_email'] >= 5 || $tentatives['par_ip'] >= 20) {
                $this->render('auth/login', [
                    'title' => 'Connexion',
                    'error' => 'Trop de tentatives. Réessayez dans quelques minutes.',
                    'csrf'  => $this->csrfField(),
                ]);
                return;
            }

            $user = $this->userModel->findByEmail($email);

            // Hash factice pour garder un temps de réponse identique que l'email existe ou non
            $hash = $user['password'] ?? '$2y$10$56IMWlKRuwxFpELbUN6v5uQCMjuKOyGqgtq0eRk40JSlGQc9MV75S';

            if (password_verify($password, $hash) && $user) {
                $this->userModel->clearAttempts($email, 'login');
                session_regenerate_id(true);
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                $_SESSION['user'] = [
                    'id'     => $user['utilisateur_id'],
                    'nom'    => $user['nom'],
                    'prenom' => $user['prenom'],
                    'email'  => $user['email'],
                    'role'   => $user['role'],
                    'ville'  => $user['ville'] ?? '',
                ];

                $redirect = $_GET['redirect'] ?? $_POST['redirect'] ?? '';
                if ($redirect && str_starts_with($redirect, '/') && !str_contains($redirect, '//') && !str_contains($redirect, '\\')) {
                    $this->redirect($redirect);
                } elseif ($user['role'] === 'administrateur') {
                    $this->redirect('/admin');
                } elseif ($user['role'] === 'employe') {
                    $this->redirect('/employe');
                } else {
                    $this->redirect('/mon-compte');
                }
            } else {
                $this->userModel->recordAttempt($email, $ip, 'login');
                $error = 'Email ou mot de passe incorrect.';
            }
        }

        $this->render('auth/login', [
            'title' => 'Connexion',
            'error' => $error,
            'csrf'  => $this->csrfField(),
        ]);
    }

    public function register(): void
    {
        $error = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->verifyCsrf();
            $data = [
                'email'       => trim($_POST['email'] ?? ''),
                'password'    => $_POST['password'] ?? '',
                'nom'         => trim($_POST['nom'] ?? ''),
                'prenom'      => trim($_POST['prenom'] ?? ''),
                'telephone'   => trim($_POST['telephone'] ?? ''),
                'adresse'     => trim($_POST['adresse'] ?? ''),
                'ville'       => trim($_POST['ville'] ?? ''),
                'code_postal' => trim($_POST['code_postal'] ?? ''),
                'pays'        => trim($_POST['pays'] ?? 'France'),
            ];

            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                $error = 'Adresse email invalide.';
            } elseif (!preg_match('/^(?:0|\+33\s?)[1-9](?:[\s.-]?\d{2}){4}$/', $data['telephone'])) {
                $error = 'Numéro de téléphone invalide (format attendu : 06 00 00 00 00).';
            } elseif (empty($data['nom']) || empty($data['prenom'])) {
                $error = 'Le nom et le prénom sont obligatoires.';
            } elseif (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{10,}$/', $data['password'])) {
                $error = 'Le mot de passe doit contenir au moins 10 caractères, une majuscule, une minuscule, un chiffre et un caractère spécial.';
            } elseif ($data['password'] !== ($_POST['password_confirm'] ?? '')) {
                $error = 'Les mots de passe ne correspondent pas.';
            } elseif ($this->userModel->emailExists($data['email'])) {
                $error = 'Un compte existe déjà avec cet email.';
            } else {
                $this->userModel->create($data);
                $this->redirect('/connexion?success=1');
            }
        }

        $this->render('auth/register', [
            'title' => 'Créer un compte',
            'error' => $error,
            'csrf'  => $this->csrfField(),
        ]);
    }

    public function logout(): void
    {
        $this->verifyCsrf();
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }
        session_destroy();
        $this->redirect('/');
    }


    public function forgotPassword(): void
    {
        $error   = null;
        $success = false;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->verifyCsrf();

            $email = trim($_POST['email'] ?? '');
            $ip    = $this->clientIp();

            // 3 demandes par email ou 10 par IP sur 10 minutes
            $tentatives = $this->userModel->countRecentAttempts($email, $ip, 'reset', 600);
            if ($tentatives['par_email'] >= 3 || $tentatives['par_ip'] >= 10) {
                $this->render('auth/forgot-password', [
                    'title'   => 'Mot de passe oublié',
                    'error'   => 'Trop de demandes. Réessayez dans quelques minutes.',
                    'success' => false,
                    'csrf'    => $this->csrfField(),
                ]);
                return;
            }
            $this->userModel->recordAttempt($email, $ip, 'reset');

            $user = $this->userModel->findByEmail($email);

            if ($user) {
                $token     = bin2hex(random_bytes(32));
                $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));

                $this->userModel->createPasswordReset($user['utilisateur_id'], $token, $expiresAt);

                $link = APP_URL . '/reinitialiser-mot-de-passe?token=' . $token;
                @mail(
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
            'csrf'    => $this->csrfField(),
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
            $this->verifyCsrf();
            $password = $_POST['password'] ?? '';

            if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{10,}$/', $password)) {
                $error = 'Le mot de passe doit contenir au moins 10 caractères, une majuscule, une minuscule, un chiffre et un caractère spécial.';
            } elseif ($password !== ($_POST['password_confirm'] ?? '')) {
                $error = 'Les mots de passe ne correspondent pas.';
            } else {
                $this->userModel->updatePassword($reset['utilisateur_id'], $password);
                $this->userModel->markTokenUsed($reset['token_id']);

                $user = $this->userModel->getById($reset['utilisateur_id']);
                if ($user && !empty($user['email'])) {
                    @mail(
                        $user['email'],
                        'Mot de passe réinitialisé — Vite & Gourmand',
                        "Bonjour " . ($user['prenom'] ?? '') . ",\n\n"
                        . "Votre mot de passe a été réinitialisé avec succès le " . date('d/m/Y à H:i') . ".\n\n"
                        . "Si vous n'êtes pas à l'origine de cette modification, contactez-nous immédiatement à jose@vitegourmand.fr.\n\n"
                        . "L'équipe Vite & Gourmand",
                        "From: noreply@vitegourmand.fr"
                    );
                }

                $this->redirect('/connexion?success=1');
            }
        }

        $this->render('auth/reset-password', [
            'title' => 'Réinitialiser le mot de passe',
            'error' => $error,
            'token' => $token,
            'csrf'  => $this->csrfField(),
        ]);
    }
}

