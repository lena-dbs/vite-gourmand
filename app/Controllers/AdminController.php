<?php
declare(strict_types=1);

class AdminController extends Controller
{
    private const STATUTS_COMMANDE = ['en_attente', 'acceptee', 'en_preparation', 'en_livraison', 'livree', 'retour_materiel', 'terminee', 'annulee'];
    private const STATUTS_AVIS = ['valide', 'refuse'];

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
        $page      = max(1, (int)($_GET['page'] ?? 1));
        $result    = $this->commandeModel->getAllFiltered($statut, $search, $page);

        $this->render('admin/index', [
            'title'      => 'Espace administrateur',
            'commandes'  => $result['data'],
            'statut'     => $statut,
            'search'     => $search,
            'pagination' => $result,
            'csrf'       => $this->csrfField(),
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
            'csrf'     => $this->csrfField(),
        ]);
    }

    public function updateStatut(): void
    {
        $this->requireAdmin();
        $this->verifyCsrf();
        $id          = (int)($_POST['commande_id'] ?? 0);
        $statut      = $_POST['statut'] ?? '';
        $commentaire = trim($_POST['commentaire'] ?? '');
        if (!in_array($statut, self::STATUTS_COMMANDE, true)) {
            $this->redirect('/admin/commande?id=' . $id);
            return;
        }

        // Annulation : le client doit avoir été contacté (appel GSM ou e-mail) + un motif est obligatoire.
        if ($statut === 'annulee') {
            $modes = ['appel' => 'appel GSM', 'mail' => 'e-mail'];
            $modeContact = $_POST['mode_contact'] ?? '';
            $motif       = trim($_POST['motif_annulation'] ?? '');
            if (!isset($modes[$modeContact]) || $motif === '') {
                $_SESSION['flash_error'] = "Pour annuler une commande, indiquez le mode de contact du client (appel GSM ou e-mail) et le motif.";
                $this->redirect('/admin/commande?id=' . $id);
                return;
            }
            $this->commandeModel->cancel($id, 'Contact : ' . $modes[$modeContact] . ' — Motif : ' . $motif);
            $this->redirect('/admin/commande?id=' . $id);
            return;
        }

        $this->commandeModel->addSuivi($id, $statut, $commentaire);
        $this->redirect('/admin/commande?id=' . $id);
    }

    public function menus(): void
    {
        $this->requireAdmin();
        $menus = $this->menuModel->getAllAdmin();
        $this->render('admin/menus', [
            'title' => 'Gestion des menus',
            'menus' => $menus,
            'csrf'  => $this->csrfField(),
        ]);
    }

    public function toggleMenu(): void
    {
        $this->requireAdmin();
        $this->verifyCsrf();
        $id = (int)($_POST['menu_id'] ?? 0);
        $this->menuModel->toggleActif($id);
        $this->redirect('/admin/menus');
    }

    public function updateStock(): void
    {
        $this->requireAdmin();
        $this->verifyCsrf();
        $id    = (int)($_POST['menu_id'] ?? 0);
        $stock = max(0, (int)($_POST['stock'] ?? 0));
        $this->menuModel->updateStock($id, $stock);
        $this->redirect('/admin/menus');
    }

    public function avis(): void
    {
        $this->requireAdmin();
        $avis = $this->commandeModel->getAvisEnAttente();
        $this->render('admin/avis', [
            'title' => 'Validation des avis',
            'avis'  => $avis,
            'csrf'  => $this->csrfField(),
        ]);
    }

    public function updateAvis(): void
    {
        $this->requireAdmin();
        $this->verifyCsrf();
        $id     = (int)($_POST['avis_id'] ?? 0);
        $statut = $_POST['statut'] ?? '';
        if (!in_array($statut, self::STATUTS_AVIS, true)) {
            $this->redirect('/admin/avis');
            return;
        }
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
            'csrf'     => $this->csrfField(),
        ]);
    }

    public function createEmploye(): void
    {
        $this->requireAdmin();
        $error = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->verifyCsrf();
            $email    = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = 'Adresse email invalide.';
            } elseif (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{10,}$/', $password)) {
                $error = 'Le mot de passe doit contenir au moins 10 caractères, une majuscule, une minuscule, un chiffre et un caractère spécial.';
            } elseif ($this->userModel->emailExists($email)) {
                $error = 'Un compte existe déjà avec cet email.';
            } else {
                $this->userModel->createEmploye($email, $password);
                $this->redirect('/admin/employes');
            }
        }

        $this->render('admin/create-employe', [
            'title' => 'Créer un employé',
            'error' => $error,
            'csrf'  => $this->csrfField(),
        ]);
    }

    public function toggleEmploye(): void
    {
        $this->requireAdmin();
        $this->verifyCsrf();
        $id = (int)($_POST['employe_id'] ?? 0);
        $this->userModel->toggleActif($id);
        $this->redirect('/admin/employes');
    }

    public function stats(): void
    {
        $this->requireAdmin();

        $menuId = isset($_GET['menu_id']) && $_GET['menu_id'] !== '' ? (int)$_GET['menu_id'] : null;
        $from   = $_GET['from'] ?? null;
        $to     = $_GET['to'] ?? null;

        // Indicateurs et évolution dans le temps : commandes honorées (MySQL).
        $stats     = $this->commandeModel->getStatsByMenu($menuId, $from, $to);
        // Répartition par menu : lue depuis MongoDB (base non relationnelle), repli SQL si Mongo indisponible.
        $statsMongo = $this->commandeModel->getStatsByMenuMongo($menuId, $from, $to);
        $statsMenu  = $statsMongo !== [] ? $statsMongo : $stats;

        $this->render('admin/stats', [
            'title'       => 'Statistiques',
            'stats'       => $stats,
            'statsMenu'   => $statsMenu,
            'statsSource' => $statsMongo !== [] ? 'MongoDB' : 'MySQL (repli)',
            'statsMois'   => $this->commandeModel->getStatsByMonth($menuId, $from, $to),
            'menus'       => $this->menuModel->getAllAdmin(),
            'fMenu'       => $menuId,
            'fFrom'       => $from,
            'fTo'         => $to,
        ]);
    }
}
