<?php helper('asset'); ?>
<!DOCTYPE html>
<html lang="<?= esc($locale ?? 'en') ?>" dir="<?= !empty($is_rtl) ? 'rtl' : 'ltr' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title) ?></title>
    <link rel="icon" type="image/png" href="<?= base_url('assets/images/favicon.png') ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Noto+Sans+Arabic:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('assets/css/tailwind.css') ?>?v=<?= esc(asset_version('assets/css/tailwind.css')) ?>">
    <link rel="stylesheet" media="print" href="<?= base_url('assets/vendor/fontawesome/css/all.min.css') ?>?v=<?= esc(asset_version('assets/vendor/fontawesome/css/all.min.css')) ?>">
    <meta name="description" content="<?= esc(lang('Auth.login_meta_description')) ?>">
    <meta name="theme-color" content="#162421">
    <link rel="stylesheet" media="screen" href="<?= base_url('assets/css/warm-clinical.css') ?>">
<style>
        :root {
            --app-font-sans: "Inter", ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Roboto", "Noto Sans", sans-serif;
            --app-font-ar: "Noto Sans Arabic", "Inter", ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Roboto", "Noto Sans", sans-serif;
            --login-panel: rgba(15, 23, 42, 0.74);
            --login-panel-soft: rgba(255, 255, 255, 0.095);
            --login-line: rgba(255, 255, 255, 0.16);
            --login-text: var(--denta-ink);
            --login-muted: var(--denta-muted);
            --login-faint: var(--denta-muted);
        }

        html,
        body,
        body *,
        button,
        input,
        select,
        textarea {
            font-family: var(--app-font-sans) !important;
        }

        html[lang^="ar"],
        html[lang^="ar"] body,
        html[lang^="ar"] body *,
        html[lang^="ar"] button,
        html[lang^="ar"] input,
        html[lang^="ar"] select,
        html[lang^="ar"] textarea {
            font-family: var(--app-font-ar) !important;
        }

        .fa,
        .fas,
        .fa::before,
        .fas::before {
            font-family: "Font Awesome 6 Free" !important;
            font-weight: 900 !important;
        }

        .far,
        .far::before {
            font-family: "Font Awesome 6 Free" !important;
            font-weight: 400 !important;
        }

        .fab,
        .fab::before {
            font-family: "Font Awesome 6 Brands" !important;
            font-weight: 400 !important;
        }

        body.login-page {
            min-height: 100dvh;
            overflow: hidden;
            background: var(--denta-alabaster);
            color: var(--denta-ink);
        }

        .login-bg {
            position: fixed;
            inset: 0;
            z-index: 0;
            overflow: hidden;
        }

        .login-bg img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transform: scale(1.02);
        }

        .login-bg::before {
            content: "";
            position: absolute;
            inset: 0;
            background: var(--denta-alabaster);
        }

        .login-bg::after {
            content: "";
            position: absolute;
            inset: 0;
            backdrop-filter: none;
            -webkit-backdrop-filter: none;
            background-image: none;
            background-size: 42px 42px;
            mask-image: none;
        }

        .login-stage {
            position: relative;
            z-index: 1;
            display: flex;
            min-height: 100dvh;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }

        .login-card {
            width: min(980px, 100%);
            max-height: calc(100dvh - 2rem);
            display: grid;
            grid-template-columns: minmax(0, 0.92fr) minmax(360px, 0.78fr);
            overflow: hidden;
            border:1px solid var(--denta-taupe-border);
            border-radius: 5px;
            background: var(--denta-alabaster);
            box-shadow: none;
            backdrop-filter: none;
            -webkit-backdrop-filter: none;
        }

        .login-info {
            min-height: 520px;
            padding: 2rem;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            border-inline-end: 1px solid var(--login-line);
            background: var(--denta-alabaster);
        }

        .login-mark {
            width: 3.25rem;
            height: 3.25rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 5px;
            background: var(--denta-alabaster);
            border:1px solid var(--denta-taupe-border);
            box-shadow: none;
        }

        .login-kicker,
        .login-footer-label {
            color: var(--denta-ink);
            font-size: 0.72rem;
            font-weight: 900;
            letter-spacing: 0.16em;
            text-transform: uppercase;
        }

        .login-title {
            margin-top: 1.25rem;
            max-width: 26rem;
            color: var(--denta-ink);
            font-size: clamp(2rem, 4vw, 3.1rem);
            font-weight: 900;
            line-height: 1.04;
            letter-spacing: -0.01em;
        }

        html[lang^="ar"] .login-title {
            letter-spacing: 0;
            line-height: 1.22;
        }

        .login-copy {
            margin-top: 1rem;
            max-width: 26rem;
            color: var(--denta-ink);
            font-size: 0.96rem;
            font-weight: 650;
            line-height: 1.8;
        }

        .login-feature-grid {
            margin-top: 1.5rem;
            display: grid;
            gap: 0.75rem;
            max-width: 26rem;
        }

        .login-feature {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 0.85rem;
            border-radius: 5px;
            background: var(--denta-alabaster);
            border:1px solid var(--denta-taupe-border);
            color: var(--denta-ink);
            font-size: 0.86rem;
            font-weight: 800;
        }

        .login-form-side {
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .login-form-card {
            border-radius: 5px;
            padding: 0.35rem;
        }

        .login-lang {
            display: inline-flex;
            align-self: flex-end;
            gap: 0.25rem;
            padding: 0.25rem;
            border-radius: 5px;
            background: var(--denta-alabaster);
            border:1px solid var(--denta-taupe-border);
        }

        .login-lang a {
            min-width: 2.6rem;
            padding: 0.46rem 0.65rem;
            border-radius: 5px;
            color: var(--denta-ink);
            font-size: 0.76rem;
            font-weight: 900;
            text-align: center;
            transition: background-color 140ms ease, color 140ms ease;
        }

        .login-lang a.is-active {
            background: var(--denta-alabaster);
            color: var(--denta-ink);
        }

        .login-form-heading {
            margin-top: 1rem;
        }

        .login-form-heading h2 {
            color: var(--denta-ink);
            font-size: 1.65rem;
            font-weight: 900;
            line-height: 1.15;
        }

        .login-form-heading p {
            margin-top: 0.45rem;
            color: var(--denta-ink);
            font-size: 0.88rem;
            font-weight: 650;
            line-height: 1.6;
        }

        .login-alert {
            margin-top: 0.95rem;
            display: flex;
            gap: 0.75rem;
            border-radius: 5px;
            padding: 0.85rem;
            font-size: 0.84rem;
            font-weight: 750;
            line-height: 1.6;
        }

        .login-alert.is-success {
            background: var(--denta-alabaster);
            color: var(--denta-ink);
            border:1px solid var(--denta-taupe-border);
        }

        .login-alert.is-error {
            background: var(--denta-alabaster);
            color: var(--denta-ink);
            border:1px solid var(--denta-taupe-border);
        }

        .login-form {
            margin-top: 1rem;
            display: grid;
            gap: 0.85rem;
        }

        .login-field label {
            display: block;
            margin-bottom: 0.42rem;
            color: var(--denta-ink);
            font-size: 0.82rem;
            font-weight: 850;
        }

        .login-control {
            position: relative;
        }

        .login-control-icon {
            position: absolute;
            inset-block: 0;
            inset-inline-start: 0.9rem;
            display: flex;
            align-items: center;
            color: var(--denta-ink);
            pointer-events: none;
        }

        .login-input {
            width: 100%;
            min-height: 3.05rem;
            border-radius: 5px;
            border:1px solid var(--denta-taupe-border);
            background: var(--denta-alabaster);
            color: var(--denta-ink);
            outline: none;
            padding-block: 0.8rem;
            padding-inline-start: 2.7rem;
            padding-inline-end: 1rem;
            font-size: 0.92rem;
            font-weight: 760;
            transition: border-color 140ms ease, background-color 140ms ease, box-shadow 140ms ease;
        }

        .login-input::placeholder {
            color: var(--denta-ink);
        }

        .login-input:focus {
            border-color: var(--denta-taupe-border);
            background: var(--denta-alabaster);
            box-shadow: none;
        }

        .login-input-password {
            padding-inline-end: 3rem;
        }

        .login-password-toggle {
            position: absolute;
            inset-block: 0;
            inset-inline-end: 0.65rem;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 2rem;
            color: var(--denta-ink);
            transition: color 140ms ease;
        }

        .login-password-toggle:hover,
        .login-password-toggle:focus {
            color: var(--denta-ink);
            outline: none;
        }

        .login-options {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.8rem;
            border-radius: 5px;
            border:1px solid var(--denta-taupe-border);
            background: var(--denta-alabaster);
            padding: 0.72rem;
        }

        .login-remember {
            display: inline-flex;
            align-items: center;
            gap: 0.65rem;
            color: var(--denta-ink);
            cursor: pointer;
            font-size: 0.82rem;
            font-weight: 800;
        }

        .login-check-wrap {
            position: relative;
            display: inline-flex;
            width: 1.15rem;
            height: 1.15rem;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .login-check {
            width: 1.15rem;
            height: 1.15rem;
            appearance: none;
            border-radius: 5px;
            border: 2px solid rgba(226, 232, 240, 0.42);
            background: var(--denta-alabaster);
            cursor: pointer;
        }

        .login-check:checked {
            border-color: var(--denta-taupe-border);
            background: var(--denta-alabaster);
        }

        .login-check-icon {
            position: absolute;
            opacity: 0;
            transform: scale(0.75);
            color: var(--denta-ink);
            font-size: 0.62rem;
            pointer-events: none;
            transition: opacity 120ms ease, transform 120ms ease;
        }

        .login-check:checked ~ .login-check-icon {
            opacity: 1;
            transform: scale(1);
        }

        .login-link {
            color: var(--denta-ink);
            font-size: 0.82rem;
            font-weight: 900;
            text-decoration: none;
        }

        .login-link:hover {
            color: var(--denta-ink);
        }

        .login-submit {
            min-height: 3.1rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.65rem;
            border-radius: 5px;
            background: var(--denta-alabaster);
            color: var(--denta-ink);
            font-size: 0.86rem;
            font-weight: 900;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            box-shadow: none;
            transition: transform 140ms ease, filter 140ms ease;
        }

        .login-submit:hover {
            transform: translateY(-1px);
            filter: brightness(1.06);
        }

        .login-footer {
            margin-top: 1rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            color: var(--denta-ink);
            font-size: 0.78rem;
            font-weight: 750;
        }

        .login-support {
            display: flex;
            align-items: center;
            gap: 0.45rem;
        }

        .login-support a {
            display: inline-flex;
            width: 2.15rem;
            height: 2.15rem;
            align-items: center;
            justify-content: center;
            border-radius: 5px;
            background: var(--denta-alabaster);
            color: var(--denta-ink);
            border:1px solid var(--denta-taupe-border);
            transition: background-color 140ms ease, color 140ms ease;
        }

        .login-support a:hover {
            background: var(--denta-alabaster);
            color: var(--denta-ink);
        }

        @media (max-width: 767px) {
            body.login-page {
                overflow: auto;
            }

            .login-stage {
                min-height: 100dvh;
                align-items: flex-start;
                padding-block: 1rem;
            }

            .login-card {
                max-height: none;
                grid-template-columns: 1fr;
                border-radius: 5px;
            }

            .login-form-side {
                padding: 1rem;
            }

            .login-options,
            .login-footer {
                align-items: flex-start;
                flex-direction: column;
            }
        }

body.login-page{background:var(--denta-travertine);color:var(--denta-ink)}
.login-page .login-card{border:1.5px solid var(--denta-taupe-border);border-radius:9px;background:var(--denta-alabaster)}
.login-page .login-input{padding-inline-start:2.7rem}
.login-page .login-input-password{padding-inline-end:3rem}
</style>
<link rel="stylesheet" media="screen" href="<?= base_url('assets/css/v3-components.css') ?>?v=<?= @filemtime(FCPATH . 'assets/css/v3-components.css') ?: '1' ?>">
<link rel="stylesheet" media="screen" href="<?= base_url('assets/css/v3-icons.css') ?>?v=<?= @filemtime(FCPATH . 'assets/css/v3-icons.css') ?: '1' ?>">
</head>
<body class="denta-v3 login-page">
    <?php
        $isRtl = !empty($is_rtl);
        $clinicName = (string) ($clinic['name'] ?? 'Denta+');
        $welcomeHeading = strtr(lang('Auth.login_welcome'), ['{clinic}' => $clinicName]);
        $returnTo = current_url() . (!empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : '');
        $activeLocale = strtolower((string) ($locale ?? service('request')->getLocale() ?? 'en'));
        $arrowIcon = $isRtl ? 'fa-arrow-left' : 'fa-arrow-right';
        $flashSuccess = session()->getFlashdata('success');
        $flashMessage = session()->getFlashdata('message');
    ?>



    <main class="login-stage">
        <section class="login-card" aria-labelledby="login-title">
            <aside class="login-info">
                <div>
                    <span class="login-mark"><i class="fas fa-tooth text-2xl"></i></span>
                    <p class="login-kicker mt-6">DentaCare</p>
                    <h1 class="login-title"><?= esc($welcomeHeading) ?></h1>
                    <p class="login-copy"><?= esc(lang('Auth.login_tagline')) ?></p>
                    <div class="login-feature-grid">
                        <div class="login-feature">
                            <i class="fas fa-calendar-check v3-text-primary"></i>
                            <span><?= esc(lang('Auth.login_feature_scheduling')) ?></span>
                        </div>
                        <div class="login-feature">
                            <i class="fas fa-file-medical v3-text-primary"></i>
                            <span><?= esc(lang('Auth.login_feature_records')) ?></span>
                        </div>
                    </div>
                </div>
                <div>
                    <p class="login-footer-label"><?= esc(lang('Auth.login_system_secure')) ?></p>
                    <p class="login-copy mt-2 text-sm"><?= esc(lang('Auth.login_meta_description')) ?></p>
                </div>
            </aside>

            <section class="login-form-side">
                <div class="login-form-card">
                    <nav class="login-lang" aria-label="Language">
                        <a href="<?= base_url('auth/language/en?return_to=' . urlencode($returnTo)) ?>"
                           lang="en"
                           hreflang="en"
                           class="<?= $activeLocale === 'en' ? 'is-active' : '' ?>">EN</a>
                        <a href="<?= base_url('auth/language/ar?return_to=' . urlencode($returnTo)) ?>"
                           lang="ar"
                           hreflang="ar"
                           class="<?= $activeLocale === 'ar' ? 'is-active' : '' ?>">AR</a>
                    </nav>

                    <div class="login-form-heading">
                        <p class="login-kicker"><?= esc(lang('Auth.login_enter_credentials')) ?></p>
                        <h2 id="login-title"><?= esc(lang('Auth.login_heading')) ?></h2>
                        <p><?= esc(lang('Auth.login_subheading')) ?></p>
                    </div>

                    <?php if ($flashSuccess || $flashMessage): ?>
                        <div class="login-alert <?= $flashSuccess ? 'is-success' : 'is-error' ?>">
                            <i class="fas <?= $flashSuccess ? 'fa-check-circle' : 'fa-exclamation-circle' ?> mt-1"></i>
                            <div><?= $flashSuccess ?: $flashMessage ?></div>
                        </div>
                    <?php endif; ?>

                    <?= form_open('auth/login', ['class' => 'login-form', 'novalidate' => true]) ?>
                        <div class="login-field">
                            <label for="identity"><?= esc(lang('Auth.login_identity_label')) ?></label>
                            <div class="login-control">
                                <span class="login-control-icon"><i class="fas fa-user"></i></span>
                                <?= form_input($identity + [
                                    'class' => 'login-input',
                                    'placeholder' => lang('Auth.login_identity_placeholder'),
                                    'autocomplete' => 'username',
                                    'autocapitalize' => 'off',
                                    'spellcheck' => 'false',
                                    'dir' => 'ltr',
                                    'inputmode' => 'email',
                                ]) ?>
                            </div>
                        </div>

                        <div class="login-field">
                            <label for="password"><?= esc(lang('Auth.login_password_label')) ?></label>
                            <div class="login-control">
                                <span class="login-control-icon"><i class="fas fa-lock"></i></span>
                                <?= form_input($password + [
                                    'class' => 'login-input login-input-password',
                                    'placeholder' => lang('Auth.login_password_placeholder'),
                                    'autocomplete' => 'current-password',
                                    'spellcheck' => 'false',
                                    'autocapitalize' => 'off',
                                    'autocorrect' => 'off',
                                    'dir' => 'ltr',
                                ]) ?>
                                <button type="button" id="togglePassword" class="login-password-toggle v3-btn v3-btn--secondary" aria-label="<?= esc(lang('Auth.login_show_password')) ?>">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>

                        <div class="login-options">
                            <label class="login-remember">
                                <span class="login-check-wrap">
                                    <input type="checkbox" id="remember" name="remember" value="1" class="login-check v3-checkbox">
                                    <i class="login-check-icon fas fa-check"></i>
                                </span>
                                <span><?= esc(lang('Auth.login_remember_label')) ?></span>
                            </label>
                            <a href="<?= base_url('auth/forgot-password') ?>" class="login-link"><?= esc(lang('Auth.login_forgot_short')) ?></a>
                        </div>

                        <button type="submit" class="login-submit v3-btn v3-btn--primary">
                            <span><?= esc(lang('Auth.login_submit_cta')) ?></span>
                            <i class="fas <?= $arrowIcon ?>"></i>
                        </button>
                    <?= form_close() ?>

                    <footer class="login-footer">
                        <div>
                            <strong>Palsync Dental</strong>
                            <span class="ms-2">&copy; <?= date('Y') ?></span>
                        </div>
                        <div class="login-support">
                            <span class="hidden sm:inline"><?= esc(lang('Auth.login_live_concierge')) ?></span>
                            <a href="https://wa.me/970599350450" target="_blank" rel="noopener" aria-label="WhatsApp support 1"><i class="fab fa-whatsapp"></i></a>
                            <a href="https://wa.me/972569885000" target="_blank" rel="noopener" aria-label="WhatsApp support 2"><i class="fab fa-whatsapp"></i></a>
                        </div>
                    </footer>
                </div>
            </section>
        </section>
    </main>

    <script>
    (function() {
        var toggle = document.getElementById('togglePassword');
        var password = document.getElementById('password');

        if (toggle && password) {
            toggle.addEventListener('click', function() {
                var shouldShow = password.type === 'password';
                password.type = shouldShow ? 'text' : 'password';

                var icon = toggle.querySelector('i');
                if (icon) {
                    icon.className = shouldShow ? 'fas fa-eye-slash' : 'fas fa-eye';
                }

                toggle.setAttribute('aria-label', shouldShow ? '<?= esc(lang('Auth.login_hide_password'), 'js') ?>' : '<?= esc(lang('Auth.login_show_password'), 'js') ?>');
                password.focus();
            });
        }
    })();
    </script>
</body>
</html>
