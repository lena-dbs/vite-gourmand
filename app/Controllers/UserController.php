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

        if (!$commande || $commande['utilisateur_id'] != $_SESSION['user']['id']) {
            $this->redirect('/mon-compte');
            return;
        }

        $suivi = $this->commandeModel->getSuivi($id);

        $this->render('user/commande', [
            'title'    => 'Détail commande',
            'commande' => $commande,
            'suivi'    => $suivi,
            'csrf'     => $this->csrfField(),
        ]);
    }

    public function cancelCommande(): void
    {
        $this->requireAuth();
        $this->verifyCsrf();
        $id = (int)($_POST['commande_id'] ?? 0);

        $commande = $this->commandeModel->getById($id);

        if (!$commande || $commande['utilisateur_id'] != $_SESSION['user']['id']) {
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

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->verifyCsrf();
            $data = [
                'nom'         => trim($_POST['nom'] ?? ''),
                'prenom'      => trim($_POST['prenom'] ?? ''),
                'telephone'   => trim($_POST['telephone'] ?? ''),
                'adresse'     => trim($_POST['adresse'] ?? ''),
                'ville'       => trim($_POST['ville'] ?? ''),
                'code_postal' => trim($_POST['code_postal'] ?? ''),
            ];

            $this->userModel->update($_SESSION['user']['id'], $data);
            $_SESSION['user']['nom']    = $data['nom'];
            $_SESSION['user']['prenom'] = $data['prenom'];
            $success = true;
            $user    = $this->userModel->getById($_SESSION['user']['id']);
        }

        $this->render('user/profil', [
            'title'   => 'Mon profil',
            'user'    => $user,
            'error'   => $error,
            'success' => $success,
            'csrf'    => $this->csrfField(),
        ]);
    }
}