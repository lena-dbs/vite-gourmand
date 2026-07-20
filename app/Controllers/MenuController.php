<?php
declare(strict_types=1);

class MenuController extends Controller
{
    private MenuModel $menuModel;

    public function __construct()
    {
        $this->menuModel = new MenuModel();
    }

    public function index(): void
    {
        $menus = $this->menuModel->getAll();

        $this->render('menus/index', [
            'title' => 'Nos menus',
            'menus' => $menus,
        ]);
    }

    public function detail(): void
    {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        $menu = $this->menuModel->getById($id);

        if (!$menu) {
            http_response_code(404);
            $this->render('errors/404', ['title' => 'Menu introuvable']);
            return;
        }

        $plats     = $this->menuModel->getPlats($id);
        $conditions = $this->menuModel->getConditions($id);

        $allergenes = $this->menuModel->getAllergenesParPlat(array_column($plats, 'plat_id'));
        foreach ($plats as &$plat) {
            $plat['allergenes'] = $allergenes[$plat['plat_id']] ?? [];
        }

        $this->render('menus/detail', [
            'title'      => $menu['titre'],
            'menu'       => $menu,
            'plats'      => $plats,
            'conditions' => $conditions,
            'avis'       => $this->menuModel->getAvisForMenu($id),
        ]);
    }
}