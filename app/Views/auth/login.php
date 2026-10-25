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

<?= view('auth/partials/access_head',['title'=>$title,'locale'=>$activeLocale,'access_heading'=>$welcomeHeading,'access_help'=>lang('Auth.login_tagline')]) ?><nav class="v4-languages" aria-label="<?= esc(lang('UI.settingsLanguage')) ?>" dir="ltr"><?php foreach(['en'=>'EN','ar'=>'AR'] as $code=>$label): ?><a href="<?= base_url('auth/language/'.$code.'?return_to='.urlencode($returnTo)) ?>" aria-current="<?= $activeLocale===$code?'page':'false' ?>" lang="<?= $code ?>"><?= $label ?></a><?php endforeach; ?></nav><header class="v4-access-heading"><h2 id="login-title"><?= esc(lang('Auth.login_heading')) ?></h2><p><?= esc(lang('Auth.login_subheading')) ?></p></header><?php if($flashSuccess||$flashMessage): ?><div class="v4-access-alert <?= $flashSuccess?'is-success':'is-error' ?>" role="status"><?= $flashSuccess?:$flashMessage ?></div><?php endif; ?><?= form_open('auth/login', ['class' => 'v4-form v4-access-form', 'novalidate' => true]) ?>
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
                                <button type="button" id="togglePassword" class="login-password-toggle" aria-label="<?= esc(lang('Auth.login_show_password')) ?>">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>

                        <div class="login-options">
                            <label class="login-remember">
                                <span class="login-check-wrap">
                                    <input type="checkbox" id="remember" name="remember" value="1" class="login-check">

                                </span>
                                <span><?= esc(lang('Auth.login_remember_label')) ?></span>
                            </label>
                            <a href="<?= base_url('auth/forgot-password') ?>" class="login-link"><?= esc(lang('Auth.login_forgot_short')) ?></a>
                        </div>

                        <button type="submit" class="login-submit">
                            <span><?= esc(lang('Auth.login_submit_cta')) ?></span>
                            <i class="fas <?= $arrowIcon ?>"></i>
                        </button>
                    <?= form_close() ?><footer class="v4-access-support"><span><?= esc(lang('V4.needHelp')) ?></span><a href="https://wa.me/970599350450" target="_blank" rel="noopener">+970 599 350 450</a><a href="https://wa.me/972569885000" target="_blank" rel="noopener">+972 569 885 000</a></footer>    <script>
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
<?= view('auth/partials/access_footer') ?>