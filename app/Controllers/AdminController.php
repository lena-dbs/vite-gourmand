<?php
declare(strict_types=1);

class AdminController extends Controller
{
    private UserModel $userModel;
    private CommandeModel $commandeModel;
    private MenuModel $menuModel;

    public function __construct()
    {
        $this->userModel     = new UserModel();
        $this->commandeModel = new CommandeModel();
        $this->menuModel     = new MenuModel();
    }

    private function requireAdmin(): void
    {
        $this->requireAuth();
        if (($_SESSION['user']['role'] ?? '') !== 'administrateur') {
            $this->redirect('/');
        }
    }

    public function index(): void
    {
        $this->requireAdmin();

        $statut    = $_GET['statut'] ?? '';
        $search    = $_GET['search'] ?? '';
        $commandes = $this->commandeModel->getAllFiltered($statut, $search);

        $this->render('admin/index', [
            'title'     => 'Espace administrateur',
            'commandes' => $commandes,
            'statut'    => $statut,
            'search'    => $search,
        ]);
    }

    public function commande(): void
    {
        $this->requireAdmin();
        $id       = (int)($_GET['id'] ?? 0);
        $commande = $this->commandeModel->getById($id);

        if (!$commande) {
            $this->redirect('/admin');
            return;
        }

        $suivi = $this->commandeModel->getSuivi($id);

        $this->render('admin/commande', [
            'title'    => 'Commande #' . $id,
            'commande' => $commande,
            'suivi'    => $suivi,
        ]);
    }

    public function updateStatut(): void
    {
        $this->requireAdmin();
        $id          = (int)($_POST['commande_id'] ?? 0);
        $statut      = $_POST['statut'] ?? '';
        $commentaire = trim($_POST['commentaire'] ?? '');
        $this->commandeModel->addSuivi($id, $statut, $commentaire);
        $this->redirect('/admin/commande?id=' . $id);
    }

    public function menus(): void
    {
        $this->requireAdmin();
        $menus = $this->menuModel->getAll();
        $this->render('admin/menus', [
            'title' => 'Gestion des menus',
            'menus' => $menus,
        ]);
    }

    public function toggleMenu(): void
    {
        $this->requireAdmin();
        $id = (int)($_POST['menu_id'] ?? 0);
        $this->menuModel->toggleActif($id);
        $this->redirect('/admin/menus');
    }

    public function avis(): void
    {
        $this->requireAdmin();
        $avis = $this->commandeModel->getAvisEnAttente();
        $this->render('admin/avis', [
            'title' => 'Validation des avis',
            'avis'  => $avis,
        ]);
    }

    public function updateAvis(): void
    {
        $this->requireAdmin();
        $id     = (int)($_POST['avis_id'] ?? 0);
        $statut = $_POST['statut'] ?? '';
        $this->commandeModel->updateAvis($id, $statut);
        $this->redirect('/admin/avis');
    }

    public function employes(): void
    {
        $this->requireAdmin();
        $employes = $this->userModel->getEmployes();
        $this->render('admin/employes', [
            'title'    => 'Gestion des employés',
            'employes' => $employes,
        ]);
    }

    public function createEmploye(): void
    {
        $this->requireAdmin();
        $error = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email    = trim($_POST['email'] ?? '');
            $password = trim($_POST['password'] ?? '');

            if ($this->userModel->findByEmail($email)) {
                $error = 'Un compte existe déjà avec cet email.';
            } else {
                $this->userModel->createEmploye($email, $password);
                $this->redirect('/admin/employes');
            }
        }

        $this->render('admin/create-employe', [
            'title' => 'Créer un employé',
            'error' => $error,
        ]);
    }

    public function toggleEmploye(): void
    {
        $this->requireAdmin();
        $id = (int)($_POST['employe_id'] ?? 0);
        $this->userModel->toggleActif($id);
        $this->redirect('/admin/employes');
    }

    public function stats(): void
    {
        $this->requireAdmin();

        $statsMongoRaw = $this->commandeModel->getStatsFromMongo();

        $stats = !empty($statsMongoRaw) ? $statsMongoRaw : $this->commandeModel->getStatsByMenu();

        $stats = $this->commandeModel->getStatsByMenu();


        $this->render('admin/stats', [
            'title' => 'Statistiques',
            'stats' => $stats,
        ]);
    }
}
