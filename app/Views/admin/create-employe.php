<main>
<section class="user-s">
    <div class="wrap">

        <a href="/admin/employes" class="menu-detail-back">← Retour aux employés</a>

        <div class="user-header reveal">
            <p class="kicker">Espace administrateur</p>
            <h1 class="sec-h2">Créer un <em>employé</em></h1>
        </div>

        <?php if ($error): ?>
            <div class="auth-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="/admin/employes/create" style="max-width:480px;">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required
                       placeholder="employe@vitegourmand.fr"
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="password">Mot de passe temporaire</label>
                <input type="password" id="password" name="password" required
                       placeholder="••••••••••">
                <span class="form-hint">L'employé devra se rapprocher de vous pour obtenir ce mot de passe.</span>
            </div>
            <button type="submit" class="hbtn">
                <span>Créer le compte employé</span>
            </button>
        </form>

    </div>
</section>
</main>