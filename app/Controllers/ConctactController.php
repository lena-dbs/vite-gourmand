<?php
declare(strict_types=1);

class ContactController extends Controller
{
    public function index(): void
    {
        $error   = null;
        $success = false;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $titre   = trim($_POST['titre'] ?? '');
            $message = trim($_POST['message'] ?? '');
            $email   = trim($_POST['email'] ?? '');

            if (empty($titre) || empty($message) || empty($email)) {
                $error = 'Tous les champs sont obligatoires.';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = 'Email invalide.';
            } else {
                mail(
                    'jose@vitegourmand.fr',
                    '[Contact] ' . $titre,
                    "De : $email\n\n$message",
                    "From: $email\r\nReply-To: $email"
                );
                $success = true;
            }
        }

        $this->render('contact/index', [
            'title'   => 'Contact',
            'error'   => $error,
            'success' => $success,
        ]);
    }
}
