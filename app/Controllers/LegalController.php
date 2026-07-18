<?php
declare(strict_types=1);

class LegalController extends Controller
{
    public function mentions(): void
    {
        $this->render('legal/mentions', [
            'title' => 'Mentions légales',
        ]);
    }

    public function cgv(): void
    {
        $this->render('legal/cgv', [
            'title' => 'Conditions Générales de Vente',
        ]);
    }
}
