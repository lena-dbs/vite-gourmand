<?php 
declare(strict_types=1);

class HomeController extends Controller
{
    public function index(): void
    {
        $commandeModel = new CommandeModel();

        $this->render('home/index', [
            'title' => 'Accueil',
            'avisValides' => $commandeModel->getAvisValides(3),
        ]);
    }
}