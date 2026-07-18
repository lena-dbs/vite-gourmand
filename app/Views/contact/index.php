<main>
<section class="contact-s">

    <div class="user-header reveal">
        <div class="wrap">
            <p class="kicker">Nous écrire</p>
            <h1 class="sec-h2">Nous <em>contacter</em></h1>
        </div>
    </div>

    <div class="wrap">

        <div class="contact-grid">

            <!-- Formulaire -->
            <div class="contact-form-col reveal">

                <?php if ($success): ?>
                    <div class="auth-success" style="margin-bottom:24px;">
                        Votre message a bien été envoyé ! Nous vous répondrons dans les plus brefs délais.
                    </div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="auth-error" style="margin-bottom:24px;"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <form method="POST" action="/contact">
                    <?= $csrf ?>
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
                                  rows="6"><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>
                    </div>
                    <button type="submit" class="hbtn">
                        <span>Envoyer le message</span>
                    </button>
                </form>
            </div>

            <!-- Infos + carte -->
            <div class="contact-info-col reveal-r">

                <div class="contact-info-card">
                    <p class="kicker">Nous trouver</p>
                    <h2 class="contact-info-titre">Vite & Gourmand</h2>

                    <div class="contact-info-item">
                        <div class="contact-info-icon">📍</div>
                        <div>
                            <p class="contact-info-label">Adresse</p>
                            <p class="contact-info-val">1 rue Sainte-Catherine<br>33000 Bordeaux</p>
                        </div>
                    </div>

                    <div class="contact-info-item">
                        <div class="contact-info-icon">📞</div>
                        <div>
                            <p class="contact-info-label">Téléphone</p>
                            <a href="tel:+33600000001" class="contact-info-val contact-info-link">06 00 00 00 01</a>
                        </div>
                    </div>

                    <div class="contact-info-item">
                        <div class="contact-info-icon">✉️</div>
                        <div>
                            <p class="contact-info-label">Email</p>
                            <a href="mailto:jose@vitegourmand.fr" class="contact-info-val contact-info-link">jose@vitegourmand.fr</a>
                        </div>
                    </div>

                    <div class="contact-info-item">
                        <div class="contact-info-icon">🕐</div>
                        <div>
                            <p class="contact-info-label">Horaires</p>
                            <p class="contact-info-val">Lun–Ven : 9h – 18h<br>Samedi : 10h – 20h<br>Dimanche : fermé</p>
                        </div>
                    </div>
                </div>

                <!-- Carte Google Maps -->
                <div class="contact-map">
                    <iframe 
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2826.8!2d-0.5752!3d44.8378!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0xd5527f4639f1d15%3A0x9a6b7b3e3e3e3e3e!2s1+Rue+Sainte-Catherine%2C+33000+Bordeaux!5e0!3m2!1sfr!2sfr!4v1620000000000!5m2!1sfr!2sfr" 
                        width="100%" 
                        height="250" 
                        style="border:0;border-radius:2px;" 
                        allowfullscreen="" 
                        loading="lazy">
                    </iframe>
                </div>

            </div>
        </div>

    </div>
</section>
</main>

