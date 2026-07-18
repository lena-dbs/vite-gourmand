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
            'csrf'  => $this->csrfField(),
        ]);
    }

    public function store(): void
    {
        $this->requireAuth();
        $this->verifyCsrf();

        $userId  = $_SESSION['user']['id'];
        $menuId  = (int)($_POST['menu_id'] ?? 0);
        $menu    = $this->menuModel->getById($menuId);

        if (!$menu) {
            $this->redirect('/menus');
            return;
        }

        if ($menu['stock'] <= 0) {
            $_SESSION['flash_error'] = 'Ce menu n\'est plus disponible (stock épuisé).';
            $this->redirect('/menus/' . $menuId);
            return;
        }

        $nbPersonnes = (int)($_POST['nb_personnes'] ?? 0);

        if ($nbPersonnes < (int)$menu['nb_personnes_min']) {
            $_SESSION['flash_error'] = 'Le nombre de personnes minimum pour ce menu est de ' . $menu['nb_personnes_min'] . '.';
            $this->redirect('/commande?menu_id=' . $menuId);
            return;
        }

        $dateLivraison  = $_POST['date_livraison'] ?? '';
        $heureLivraison = $_POST['heure_livraison'] ?? '';
        $ville          = trim($_POST['ville'] ?? '');

        if ($ville === '') {
            $_SESSION['flash_error'] = 'La ville de livraison est obligatoire.';
            $this->redirect('/commande?menu_id=' . $menuId);
            return;
        }

        $date = \DateTimeImmutable::createFromFormat('!Y-m-d', $dateLivraison);
        $dateErrors = \DateTimeImmutable::getLastErrors();
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateLivraison)
            || !$date
            || ($dateErrors !== false && ($dateErrors['warning_count'] > 0 || $dateErrors['error_count'] > 0))) {
            $_SESSION['flash_error'] = 'Date de livraison invalide.';
            $this->redirect('/commande?menu_id=' . $menuId);
            return;
        }

        if (!preg_match('/^\d{2}:\d{2}$/', $heureLivraison)) {
            $_SESSION['flash_error'] = 'Heure de livraison invalide.';
            $this->redirect('/commande?menu_id=' . $menuId);
            return;
        }

        $heureInt = (int)str_replace(':', '', $heureLivraison);
        if ($heureInt < 900 || $heureInt > 2000) {
            $_SESSION['flash_error'] = 'L\'heure de livraison doit être entre 09:00 et 20:00.';
            $this->redirect('/commande?menu_id=' . $menuId);
            return;
        }

        $minDate = date('Y-m-d', strtotime('+3 days'));
        if ($dateLivraison < $minDate) {
            $_SESSION['flash_error'] = 'La date de livraison doit être au minimum dans 3 jours (à partir du ' . date('d/m/Y', strtotime('+3 days')) . ').';
            $this->redirect('/commande?menu_id=' . $menuId);
            return;
        }

        $prixMenu      = (float)$menu['prix_base'];
        $prixLivraison = strtolower($ville) === 'bordeaux' ? 0.00 : 5.00;
        $prixReduction = 0.00;

        // Réduction 10% si 5 personnes de plus que le minimum
        if ($nbPersonnes >= $menu['nb_personnes_min'] + 5) {
            $prixReduction = $prixMenu * 0.10;
        }

        $prixTotal = $prixMenu + $prixLivraison - $prixReduction;

        $db = Database::getInstance();
        try {
            $db->beginTransaction();

            if (!$this->menuModel->decrementStock($menuId)) {
                $db->rollBack();
                $_SESSION['flash_error'] = 'Ce menu n\'est plus disponible (stock épuisé).';
                $this->redirect('/menus/' . $menuId);
                return;
            }

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
            $db->commit();
        } catch (\Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            error_log('Order creation error: ' . $e->getMessage());
            $_SESSION['flash_error'] = 'Votre commande n\'a pas pu être enregistrée. Veuillez réessayer.';
            $this->redirect('/commande?menu_id=' . $menuId);
            return;
        }

        $this->commandeModel->saveStatToMongo($commandeId, $menuId, $menu['titre'], $prixTotal);

        $userEmail = $_SESSION['user']['email'] ?? '';
        if ($userEmail) {
            $sujet = 'Confirmation de commande #' . $commandeId . ' — Vite & Gourmand';
            $corps = "Bonjour " . ($_SESSION['user']['prenom'] ?? '') . ",\n\n"
                   . "Votre commande #$commandeId a bien été enregistrée.\n\n"
                   . "Menu : " . $menu['titre'] . "\n"
                   . "Date de livraison : " . date('d/m/Y', strtotime($dateLivraison)) . " à $heureLivraison\n"
                   . "Personnes : $nbPersonnes\n"
                   . "Total : " . number_format($prixTotal, 2, ',', ' ') . " €\n"
                   . "Paiement : à la livraison (espèces ou CB)\n\n"
                   . "Vous pouvez suivre votre commande depuis votre espace personnel.\n\n"
                   . "Merci pour votre confiance !\nL'équipe Vite & Gourmand";
            @mail($userEmail, $sujet, $corps, "From: noreply@vitegourmand.fr");
        }

        $this->redirect('/mon-compte/commandes/' . $commandeId);
    }
}
