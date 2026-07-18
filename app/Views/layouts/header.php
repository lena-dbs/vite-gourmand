<?php declare(strict_types=1);
$_csrf_field = '';
if (isset($_SESSION['csrf_token'])) {
    $_csrf_field = '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($_SESSION['csrf_token']) . '">';
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <script nonce="<?= CSP_NONCE ?>">document.documentElement.classList.add('js');</script>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($title) ? htmlspecialchars($title) . ' — ' : '' ?><?= APP_NAME ?></title>
    <meta name="description" content="<?= isset($metaDesc) ? htmlspecialchars($metaDesc) : 'Vite & Gourmand — Traiteur artisanal à Bordeaux depuis 1999. Menus d\'exception pour Noël, Pâques et tous vos événements, livrés chez vous.' ?>">
    <link rel="icon" href="/assets/images/favicon.svg" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Fraunces:ital,opsz,wght@0,9..144,300;0,9..144,500;0,9..144,700;1,9..144,300;1,9..144,500&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/style.css">
</head>

<body>

<nav class="nav" id="nav" role="navigation" aria-label="Navigation principale">
    <a href="/" class="nav-logo">
        <img src="/assets/images/favicon.svg" alt="" class="nav-logo-img">
        Vite <em>&</em> Gourmand
    </a>
    <ul class="nav-links" id="nav-links" role="list">
        <li><a href="/menus">Menus</a></li>
        <li><a href="/#histoire">Histoire</a></li>
        <li><a href="/#avis">Avis</a></li>
        <li><a href="/contact">Contact</a></li>
    </ul>
    <div class="nav-actions">
        <?php if (isset($_SESSION['user'])): ?>
            <?php if ($_SESSION['user']['role'] === 'administrateur'): ?>
                <a href="/admin" class="nav-link-admin">Espace admin</a>
            <?php elseif ($_SESSION['user']['role'] === 'employe'): ?>
                <a href="/employe" class="nav-link-admin">Espace employé</a>
            <?php endif; ?>
            <a href="/mon-compte" class="nav-btn"><span><?= htmlspecialchars($_SESSION['user']['prenom']) ?></span></a>
        <?php else: ?>
            <a href="/connexion" class="nav-btn"><span>Connexion</span></a>
        <?php endif; ?>
    </div>
    <button class="nav-burger" id="nav-burger" aria-label="Ouvrir le menu" aria-expanded="false">
        <span></span><span></span><span></span>
    </button>
</nav>