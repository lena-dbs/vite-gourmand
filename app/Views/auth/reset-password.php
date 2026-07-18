<main>
<section class="auth-s">
    <div class="auth-card">

        <div class="auth-header">
            <p class="kicker">Sécurité</p>
            <h1 class="auth-titre">Nouveau mot de passe</h1>
        </div>

        <?php if ($error): ?>
            <div class="auth-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if ($token): ?>
            <form method="POST" action="/reinitialiser-mot-de-passe?token=<?= htmlspecialchars($token) ?>">
                <div class="form-group">
                    <label for="password">Nouveau mot de passe</label>
                    <input type="password" id="password" name="password" required
                           placeholder="••••••••••">
                    <span class="form-hint">10 caractères min., une majuscule, une minuscule, un chiffre et un caractère spécial.</span>
                </div>
                <button type="submit" class="hbtn" style="width:100%;justify-content:center;">
                    <span>Réinitialiser le mot de passe</span>
                </button>
            </form>
        <?php else: ?>
            <p class="auth-switch">
                <a href="/mot-de-passe-oublie">Demander un nouveau lien</a>
            </p>
        <?php endif; ?>

        <p class="auth-switch">
            <a href="/connexion">← Retour à la connexion</a>
        </p>

    </div>
</section>
</main>

