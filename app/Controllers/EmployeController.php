<?php
declare(strict_types=1);

class EmployeController extends Controller
{
    private const STATUTS_COMMANDE = ['en_attente', 'en_preparation', 'prete', 'livree', 'retour_materiel', 'terminee', 'annulee'];
    private const STATUTS_AVIS = ['valide', 'refuse'];

    private CommandeModel $commandeModel;
    private MenuModel $menuModel;

    public function __construct()
    {
        $this->commandeModel = new CommandeModel();
        $this->menuModel     = new MenuModel();
    }

    private function requireEmploye(): void
    {
        $this->requireAuth();
        $role = $_SESSION['user']['role'] ?? '';
        if ($role !== 'employe' && $role !== 'administrateur') {
            $this->redirect('/');
        }
    }

    public function index(): void
    {
        $this->requireEmploye();

        $statut    = $_GET['statut'] ?? '';
        $search    = $_GET['search'] ?? '';
        $page      = max(1, (int)($_GET['page'] ?? 1));
        $result    = $this->commandeModel->getAllFiltered($statut, $search, $page);

        $this->render('employe/index', [
            'title'      => 'Espace employé',
            'commandes'  => $result['data'],
            'statut'     => $statut,
            'search'     => $search,
            'pagination' => $result,
            'csrf'       => $this->csrfField(),
        ]);
    }

    public function commande(): void
    {
        $this->requireEmploye();
        $id       = (int)($_GET['id'] ?? 0);
        $commande = $this->commandeModel->getById($id);

        if (!$commande) {
            $this->redirect('/employe');
            return;
        }

        $suivi = $this->commandeModel->getSuivi($id);

        $this->render('employe/commande', [
            'title'    => 'Commande #' . $id,
            'commande' => $commande,
            'suivi'    => $suivi,
            'csrf'     => $this->csrfField(),
        ]);
    }

    public function updateStatut(): void
    {
        $this->requireEmploye();
        $this->verifyCsrf();

        $id          = (int)($_POST['commande_id'] ?? 0);
        $statut      = $_POST['statut'] ?? '';
        $commentaire = trim($_POST['commentaire'] ?? '');

        if (!in_array($statut, self::STATUTS_COMMANDE, true)) {
            $this->redirect('/employe/commande?id=' . $id);
            return;
        }

        $this->commandeModel->addSuivi($id, $statut, $commentaire);

        $this->redirect('/employe/commande?id=' . $id);
    }

    public function menus(): void
    {
        $this->requireEmploye();
        $menus = $this->menuModel->getAllAdmin();

        $this->render('employe/menus', [
            'title' => 'Gestion des menus',
            'menus' => $menus,
            'csrf'  => $this->csrfField(),
        ]);
    }

    public function toggleMenu(): void
    {
        $this->requireEmploye();
        $this->verifyCsrf();
        $id = (int)($_POST['menu_id'] ?? 0);
        $this->menuModel->toggleActif($id);
        $this->redirect('/employe/menus');
    }

    public function avis(): void
    {
        $this->requireEmploye();
        $avis = $this->commandeModel->getAvisEnAttente();

        $this->render('employe/avis', [
            'title' => 'Validation des avis',
            'avis'  => $avis,
            'csrf'  => $this->csrfField(),
        ]);
    }

    public function updateAvis(): void
    {
        $this->requireEmploye();
        $this->verifyCsrf();
        $id     = (int)($_POST['avis_id'] ?? 0);
        $statut = $_POST['statut'] ?? '';
        if (!in_array($statut, self::STATUTS_AVIS, true)) {
            $this->redirect('/employe/avis');
            return;
        }
        $this->commandeModel->updateAvis($id, $statut);
        $this->redirect('/employe/avis');
    }
}