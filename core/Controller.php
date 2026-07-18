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

        require_once VIEWS_PATH . '/layouts/header.php';
        require_once $viewPath;
        require_once VIEWS_PATH . '/layouts/footer.php';
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

    protected function verifyCsrf(): void
    {
        $token = $_POST['csrf_token'] ?? '';
        if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
            http_response_code(403);
            die('Jeton de sécurité invalide. Veuillez rafraîchir la page et réessayer.');
        }
    }
}
