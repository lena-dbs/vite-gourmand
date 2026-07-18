<?php
declare(strict_types=1);

class UserController extends Controller
{
    private UserModel $userModel;
    private CommandeModel $commandeModel;

    public function __construct()
    {
        $this->userModel     = new UserModel();
        $this->commandeModel = new CommandeModel();
    }

    public function index(): void
    {
        $this->requireAuth();
        $commandes = $this->commandeModel->getByUser($_SESSION['user']['id']);

        $this->render('user/index', [
            'title'     => 'Mon compte',
            'commandes' => $commandes,
            'csrf'      => $this->csrfField(),
        ]);
    }

    public function commande(): void
    {
        $this->requireAuth();
        $id       = (int)($_GET['id'] ?? 0);
        $commande = $this->commandeModel->getById($id);

        if (!$commande || (int)$commande['utilisateur_id'] !== $_SESSION['user']['id']) {
            $this->redirect('/mon-compte');
            return;
        }

        $suivi = $this->commandeModel->getSuivi($id);
        $avis  = $this->commandeModel->getAvisForCommande($id);

        $dernierStatut = !empty($suivi) ? end($suivi)['statut'] : 'en_attente';
        $peutNoter = !$avis && in_array($dernierStatut, ['livree', 'retour_materiel', 'terminee'], true);

        $this->render('user/commande', [
            'title'     => 'Détail commande',
            'commande'  => $commande,
            'suivi'     => $suivi,
            'avis'      => $avis,
            'peutNoter' => $peutNoter,
            'csrf'      => $this->csrfField(),
        ]);
    }

    public function createAvis(): void
    {
        $this->requireAuth();
        $this->verifyCsrf();

        $id          = (int)($_POST['commande_id'] ?? 0);
        $note        = (int)($_POST['note'] ?? 0);
        $commentaire = trim($_POST['commentaire'] ?? '');

        $commande = $this->commandeModel->getById($id);
        if (!$commande || (int)$commande['utilisateur_id'] !== $_SESSION['user']['id']) {
            $this->redirect('/mon-compte');
            return;
        }

        $statutOk = in_array($this->commandeModel->getDernierStatut($id), ['livree', 'retour_materiel', 'terminee'], true);
        $dejaNote = (bool)$this->commandeModel->getAvisForCommande($id);

        if ($statutOk && !$dejaNote && $note >= 1 && $note <= 5 && $commentaire !== '' && mb_strlen($commentaire) <= 1000) {
            $this->commandeModel->createAvis($_SESSION['user']['id'], $id, $note, $commentaire);
        }

        $this->redirect('/mon-compte/commandes/' . $id);
    }

    public function cancelCommande(): void
    {
        $this->requireAuth();
        $this->verifyCsrf();
        $id = (int)($_POST['commande_id'] ?? 0);

        $commande = $this->commandeModel->getById($id);

        if (!$commande || (int)$commande['utilisateur_id'] !== $_SESSION['user']['id']) {
            $this->redirect('/mon-compte');
            return;
        }

        if ($this->commandeModel->canCancel($id)) {
            $motif = trim($_POST['motif'] ?? 'Annulation demandée par le client');
            $this->commandeModel->cancel($id, $motif);
        }

        $this->redirect('/mon-compte/commandes/' . $id);
    }

    public function profil(): void
    {
        $this->requireAuth();
        $user  = $this->userModel->getById($_SESSION['user']['id']);
        $error = null;
        $success = false;
        $passwordError = null;
        $passwordSuccess = false;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->verifyCsrf();
            $action = $_POST['action'] ?? 'profile';

            if ($action === 'password') {
                $currentPassword = $_POST['current_password'] ?? '';
                $newPassword     = $_POST['new_password'] ?? '';

                if (!password_verify($currentPassword, $user['password'])) {
                    $passwordError = 'Le mot de passe actuel est incorrect.';
                } elseif (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{10,}$/', $newPassword)) {
                    $passwordError = 'Le nouveau mot de passe doit contenir au moins 10 caractères, une majuscule, une minuscule, un chiffre et un caractère spécial.';
                } elseif ($newPassword !== ($_POST['new_password_confirm'] ?? '')) {
                    $passwordError = 'Les mots de passe ne correspondent pas.';
                } else {
                    $this->userModel->updatePassword($_SESSION['user']['id'], $newPassword);
                    $passwordSuccess = true;

                    $userEmail = $_SESSION['user']['email'] ?? '';
                    if ($userEmail) {
                        @mail(
                            $userEmail,
                            'Mot de passe modifié — Vite & Gourmand',
                            "Bonjour " . ($_SESSION['user']['prenom'] ?? '') . ",\n\n"
                            . "Votre mot de passe a été modifié avec succès le " . date('d/m/Y à H:i') . ".\n\n"
                            . "Si vous n'êtes pas à l'origine de cette modification, contactez-nous immédiatement à jose@vitegourmand.fr ou changez votre mot de passe.\n\n"
                            . "L'équipe Vite & Gourmand",
                            "From: noreply@vitegourmand.fr"
                        );
                    }
                }
            } else {
                $data = [
                    'nom'         => trim($_POST['nom'] ?? ''),
                    'prenom'      => trim($_POST['prenom'] ?? ''),
                    'telephone'   => trim($_POST['telephone'] ?? ''),
                    'adresse'     => trim($_POST['adresse'] ?? ''),
                    'ville'       => trim($_POST['ville'] ?? ''),
                    'code_postal' => trim($_POST['code_postal'] ?? ''),
                ];

                if (!preg_match('/^(?:0|\+33\s?)[1-9](?:[\s.-]?\d{2}){4}$/', $data['telephone'])) {
                    $error = 'Numéro de téléphone invalide (format attendu : 06 00 00 00 00).';
                } else {
                    $this->userModel->update($_SESSION['user']['id'], $data);
                    $_SESSION['user']['nom']    = $data['nom'];
                    $_SESSION['user']['prenom'] = $data['prenom'];
                    $_SESSION['user']['ville']  = $data['ville'];
                    $success = true;
                }
            }

            $user = $this->userModel->getById($_SESSION['user']['id']);
        }

        $this->render('user/profil', [
            'title'           => 'Mon profil',
            'user'            => $user,
            'error'           => $error,
            'success'         => $success,
            'passwordError'   => $passwordError,
            'passwordSuccess' => $passwordSuccess,
            'csrf'            => $this->csrfField(),
        ]);
    }
}