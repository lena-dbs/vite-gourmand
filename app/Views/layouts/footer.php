<?php
try {
    $horaireLignes = (new HoraireModel())->getLignes();
} catch (Throwable $e) {
    $horaireLignes = [];
}
?>
<footer>
    <div class="footer-grid">
        <div>
            <p class="f-logo">Vite <em>&</em> Gourmand</p>
            <p class="f-tagline">Traiteur artisanal à Bordeaux depuis 1999.<br>Julie et José au service de vos événements.</p>
            <p class="f-tagline" style="margin-top:12px;"> 1 rue Sainte-Catherine, 33000 Bordeaux 
                <br>
                <a href="tel:+33600000001" style="color:rgba(247,241,232,.38);">06 00 00 00 01</a>
        </p>
        </div>
        <div class="f-col">
            <p class="f-col-t">Navigation</p>
            <a href="/menus">Nos menus</a>
            <a href="/contact">Contact</a>
            <?php if (isset($_SESSION['user'])): ?>
                <a href="/mon-compte">Mon compte</a>
            <?php else: ?>
                <a href="/connexion">Connexion</a>
            <?php endif; ?>
        </div>
        <div class="f-col">
            <p class="f-col-t">Légal</p>
            <a href="/mentions-legales">Mentions légales</a>
            <a href="/cgv">CGV</a>
        </div>
        <div class="f-col">
            <p class="f-col-t">Horaires</p>
            <?php if ($horaireLignes): ?>
                <?php foreach ($horaireLignes as $ligne): ?>
                    <p><?= htmlspecialchars($ligne['jours']) ?> : <?= htmlspecialchars($ligne['heures']) ?></p>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Lun–Ven : 9h – 18h</p>
                <p>Samedi : 10h – 20h</p>
                <p>Dimanche : fermé</p>
            <?php endif; ?>
        </div>
    </div>
    <div class="footer-bottom">
        <p>© <?= date('Y') ?> Vite & Gourmand — Tous droits réservés</p>
        <p>Réalisé par FastDev</p>
    </div>
</footer>

<script src="/assets/js/main.js"></script>
</body>
</html>