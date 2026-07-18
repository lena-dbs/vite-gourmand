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
}
