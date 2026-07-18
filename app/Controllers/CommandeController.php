<?php
declare(strict_types=1);

class CommandeController extends Controller
{
    private CommandeModel $commandeModel;
    private MenuModel $menuModel;

    public function __construct()
    {
        $this->commandeModel = new CommandeModel();
        $this->menuModel     = new MenuModel();
    }

    public function index(): void
    {
        $this->requireAuth();

        $menuId = isset($_GET['menu_id']) ? (int)$_GET['menu_id'] : 0;
        $menu   = $menuId ? $this->menuModel->getById($menuId) : null;

        $this->render('commande/index', [
            'title' => 'Passer commande',
            'menu'  => $menu,
            'menus' => $this->menuModel->getAll(),
        ]);
    }

    public function store(): void
    {
        $this->requireAuth();

        $userId  = $_SESSION['user']['id'];
        $menuId  = (int)$_POST['menu_id'];
        $menu    = $this->menuModel->getById($menuId);

        if (!$menu) {
            $this->redirect('/menus');
            return;
        }

        $nbPersonnes   = (int)$_POST['nb_personnes'];
        $dateLivraison = $_POST['date_livraison'];
        $heureLivraison= $_POST['heure_livraison'];
        $ville         = trim($_POST['ville']);

        // Calcul prix
        $prixMenu      = (float)$menu['prix_base'];
        $prixLivraison = strtolower($ville) === 'bordeaux' ? 0.00 : 5.00;
        $prixReduction = 0.00;

        // Réduction 10% si 5 personnes de plus que le minimum
        if ($nbPersonnes >= $menu['nb_personnes_min'] + 5) {
            $prixReduction = $prixMenu * 0.10;
        }

        $prixTotal = $prixMenu + $prixLivraison - $prixReduction;

        $commandeId = $this->commandeModel->create([
            ':utilisateur_id' => $userId,
            ':menu_id'        => $menuId,
            ':nb_personnes'   => $nbPersonnes,
            ':date_livraison' => $dateLivraison,
            ':heure_livraison'=> $heureLivraison,
            ':prix_menu'      => $prixMenu,
            ':prix_livraison' => $prixLivraison,
            ':prix_reduction' => $prixReduction,
            ':prix_total'     => $prixTotal,
        ]);

// Sauvegarder dans MongoDB
$this->commandeModel->saveStatToMongo(
    $commandeId,
    $menuId,
    $menu['titre'],
    $prixTotal
);

      $this->redirect('/mon-compte/commandes/' . $commandeId);
    }
}