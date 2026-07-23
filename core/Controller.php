<?php 
declare(strict_types=1);

abstract class Controller
{
    protected function render(string $view, array $data = []): void
    {
        extract($data);

        $viewPath = VIEWS_PATH . '/' . $view . '.php';

        if (!file_exists($viewPath)) {
            die('Vue introuvable : ' . $view);
        }

        require VIEWS_PATH . '/layouts/header.php';
        require $viewPath;
        require VIEWS_PATH . '/layouts/footer.php';
    }

    protected function redirect(string $url): void
    {
        header('Location: ' . $url);
        exit;
    }

    protected function isLogged(): bool
    {
        return isset($_SESSION['user']);
    }

    protected function requireAuth():void
    {
        if (!$this->isLogged()) {
            $this->redirect('/connexion');
        }

        $db = Database::getInstance();
        $stmt = $db->prepare('SELECT actif FROM utilisateur WHERE utilisateur_id = :id');
        $stmt->execute([':id' => $_SESSION['user']['id']]);
        $user = $stmt->fetch();
        if (!$user || !$user['actif']) {
            session_destroy();
            $this->redirect('/connexion');
        }
    }

    protected function generateCsrfToken(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    protected function csrfField(): string
    {
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($this->generateCsrfToken()) . '">';
    }

    protected function clientIp(): string
    {
        $ip = $_SERVER['HTTP_FLY_CLIENT_IP'] ?? $_SERVER['REMOTE_ADDR'] ?? '';
        return substr($ip, 0, 45);
    }

    protected function verifyCsrf(): void
    {
        $token = $_POST['csrf_token'] ?? '';
        if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
            http_response_code(403);
            die('Jeton de sécurité invalide. Veuillez rafraîchir la page et réessayer.');
        }
    }

    // Notifie le client par e-mail sur certains changements de statut :
    // - retour_materiel : rappel de restitution sous 10 jours ouvrés, sinon 600 € de frais (cf. CGV)
    // - terminee : invitation à se connecter pour laisser un avis
    protected function notifierChangementStatut(array $commande, string $statut): void
    {
        $email = $commande['email'] ?? '';
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return;
        }

        $prenom = $commande['prenom'] ?? '';
        $menu   = $commande['menu_titre'] ?? '';
        $numero = $commande['commande_id'] ?? '';
        $from   = 'From: noreply@vitegourmand.fr';

        if ($statut === 'retour_materiel') {
            $sujet = 'Vite & Gourmand — Retour du matériel prêté';
            $corps = "Bonjour $prenom,\n\n"
                . "Votre commande #$numero ($menu) est terminée et du matériel vous a été prêté.\n"
                . "Merci de nous le restituer sous 10 jours ouvrés. Passé ce délai, des frais de 600 € "
                . "seront facturés, conformément à nos conditions générales de vente.\n\n"
                . "Pour organiser le retour, contactez-nous par retour de mail ou par téléphone.\n\n"
                . "L'équipe Vite & Gourmand";
            @mail($email, $sujet, $corps, $from);
        } elseif ($statut === 'terminee') {
            $sujet = 'Vite & Gourmand — Votre avis nous intéresse';
            $corps = "Bonjour $prenom,\n\n"
                . "Votre commande #$numero ($menu) est terminée. Nous espérons que tout s'est bien passé !\n"
                . "Connectez-vous à votre espace pour laisser un avis sur cette commande.\n\n"
                . "Merci de votre confiance,\nL'équipe Vite & Gourmand";
            @mail($email, $sujet, $corps, $from);
        }
    }
}
