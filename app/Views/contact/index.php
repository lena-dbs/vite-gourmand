<main>
<section class="auth-s">
    <div class="wrap" style="max-width:700px;margin:0 auto;">

        <div class="user-header reveal">
            <p class="kicker">Nous écrire</p>
            <h1 class="sec-h2">Nous <em>contacter</em></h1>
        </div>

        <?php if ($success): ?>
            <div class="auth-success" style="margin-bottom:24px;">
                Votre message a bien été envoyé ! Nous vous répondrons dans les plus brefs délais.
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="auth-error" style="margin-bottom:24px;"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="/contact" class="reveal">
            <div class="form-group">
                <label for="titre">Sujet</label>
                <input type="text" id="titre" name="titre" required
                       placeholder="Demande de devis, renseignement..."
                       value="<?= htmlspecialchars($_POST['titre'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="email">Votre email</label>
                <input type="email" id="email" name="email" required
                       placeholder="votre@email.fr"
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="message">Message</label>
                <textarea id="message" name="message" required
                          placeholder="Votre message..."
                          rows="6" style="padding:12px 16px;border:1px solid #EDE3D5;background:#F7F1E8;font-family:'DM Sans',sans-serif;font-size:14px;color:#261A0D;outline:none;width:100%;resize:vertical;"><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>
            </div>
            <button type="submit" class="hbtn">
                <span>Envoyer le message</span>
            </button>
        </form>

        <!-- Infos contact -->
        <div class="contact-infos reveal" style="margin-top:48px;display:grid;grid-template-columns:1fr 1fr;gap:24px;">
            <div>
                <p class="kicker">Adresse</p>
                <p style="font-size:14px;color:#6B5444;line-height:1.8;">1 rue Sainte-Catherine<br>33000 Bordeaux</p>
            </div>
            <div>
                <p class="kicker">Horaires</p>
                <p style="font-size:14px;color:#6B5444;line-height:1.8;">Lun–Ven : 9h – 18h<br>Samedi : 10h – 20h<br>Dimanche : fermé</p>
            </div>
        </div>

    </div>
</section>
</main>

