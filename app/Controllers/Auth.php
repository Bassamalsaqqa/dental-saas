<?php
namespace App\Controllers;

use App\Libraries\ApiRuntimeContext;
use App\Models\ClinicUserModel;
use App\Models\ClinicNotificationChannelModel;
use App\Models\ActivityLogModel;
use App\Models\SettingsModel;

/**
 * Class Auth
 *
 * @property Ion_auth|Ion_auth_model $ion_auth      The ION Auth spark
 * @package  CodeIgniter-Ion-Auth
 * @author   Ben Edmunds <ben.edmunds@gmail.com>
 * @author   Benoit VRIGNAUD <benoit.vrignaud@zaclys.net>
 * @license  https://opensource.org/licenses/MIT	MIT License
 */
class Auth extends BaseController
{

	/**
	 *
	 * @var array
	 */
	public $data = [];

	/**
	 * Configuration
	 *
	 * @var \App\Config\IonAuth
	 */
	protected $configIonAuth;

	/**
	 * Validation library
	 *
	 * @var \CodeIgniter\Validation\Validation
	 */
	protected $validation;

	/**
	 * Validation list template.
	 *
	 * @var string
	 * @see https://bcit-ci.github.io/CodeIgniter4/libraries/validation.html#configuration
	 */
	protected $validationListTemplate = 'list';

	/**
	 * Views folder
	 * Set it to 'auth' if your views files are in the standard application/Views/auth
	 *
	 * @var string
	 */
	protected $viewsFolder = 'auth';

	/**
	 * Constructor
	 *
	 * @return void
	 */
	public function __construct()
	{
		$this->ionAuth    = new \App\Libraries\IonAuth();
		$this->validation = \Config\Services::validation();
		helper(['form', 'url']);
		$this->configIonAuth = config('IonAuth');
		$this->session       = \Config\Services::session();

		if (! empty($this->configIonAuth->templates['errors']['list']))
		{
			$this->validationListTemplate = $this->configIonAuth->templates['errors']['list'];
		}
	}

	/**
	 * Require genuine platform Super Admin in global mode.
	 */
	protected function requirePlatformSuperAdmin(): ?\CodeIgniter\HTTP\RedirectResponse
	{
		if (!$this->ionAuth->loggedIn()) {
			return redirect()->to('/auth/login');
		}

		$session = service('session');
		$isGlobal = $session && $session->get('global_mode') === true;
		$userId = (int) $this->ionAuth->getUserId();
		$permService = service('permission');
		if (!$permService) {
			$permService = new \App\Services\PermissionService();
		}

		if (!$isGlobal || !$permService->isSuperAdmin($userId)) {
			return redirect()->to('/controlplane')->with('error', 'Platform administrator access required.');
		}

		return null;
	}

	/**
	 * Redirect if needed, otherwise display the user list
	 *
	 * @return string|\CodeIgniter\HTTP\RedirectResponse
	 */
	public function index()
	{
		$redirect = $this->requirePlatformSuperAdmin();
		if ($redirect) {
			return $redirect;
		}

		return redirect()->to('/controlplane/users');
	}

	/**
	 * Log the user in
	 *
	 * @return string|\CodeIgniter\HTTP\RedirectResponse
	 */
	public function login()
	{
		// Debug session data
		$session = \Config\Services::session();
		$identity = $session->get('identity');
		$userId = $session->get('user_id');
		$loggedIn = $this->ionAuth->loggedIn();
		
		// Log debug info
		log_message('debug', 'Login check - Identity: ' . ($identity ?: 'null') . ', User ID: ' . ($userId ?: 'null') . ', Logged In: ' . ($loggedIn ? 'true' : 'false'));
		
		// If user is already logged in, redirect based on current context
		if ($loggedIn) {
			if ($session->get('global_mode')) {
				return redirect()->to('/controlplane/dashboard');
			}
			if (!$session->get('active_clinic_id')) {
				return redirect()->to('/clinic/select');
			}
			return redirect()->to('/dashboard');
		}

		$this->data['title'] = lang('Auth.login_heading');
		
		// Check if demo mode is enabled
		$appConfig = config('App');
		$this->data['demo'] = $appConfig->demo;

		// validate form input
		$this->validation->setRule('identity', str_replace(':', '', lang('Auth.login_identity_label')), 'required');
		$this->validation->setRule('password', str_replace(':', '', lang('Auth.login_password_label')), 'required');

		if ($this->request->getPost() && $this->validation->withRequest($this->request)->run())
		{
			// check to see if the user is logging in
			// check for "remember me"
			$remember = (bool)$this->request->getVar('remember');

			if ($this->ionAuth->login($this->request->getVar('identity'), $this->request->getVar('password'), $remember))
			{
				// S2-02: Tenant Context Contract
				$userId = $this->ionAuth->getUserId();
				$clinicService = new \App\Services\ClinicService();

				// 1. Clear global mode and tenant keys
				$clinicService->clearContext();

				// 2. Query memberships
				$memberships = $clinicService->getMemberships($userId);

				$count = count($memberships);

				//if the login is successful
				$this->session->setFlashdata('success', 'Welcome back! You have been logged in successfully.');

				if ($count === 1) {
					// Auto-select
					$clinicService->setContext($memberships[0]);
					session_write_close();
					return redirect()->to('/dashboard')->withCookies();
				} elseif ($count > 1) {
					// Selection Wall
					return redirect()->to('/clinic/select')->withCookies();
				} else {
					// No Clinic
					return redirect()->to('/clinic/no-clinic')->withCookies();
				}
			}
			else
			{
				// if the login was un-successful
				// redirect them back to the login page
				$this->session->setFlashdata('message', $this->ionAuth->errors($this->validationListTemplate));
				// use redirects instead of loading views for compatibility with MY_Controller libraries
				return redirect()->back()->withInput();
			}
		}
		else
		{
			// the user is not logging in so display the login page
			// set the flash data error message if there is one
			$this->data['message'] = $this->validation->getErrors() ? $this->validation->listErrors($this->validationListTemplate) : $this->session->getFlashdata('message');

			$this->data['identity'] = [
				'name'  => 'identity',
				'id'    => 'identity',
				'type'  => 'text',
				'value' => set_value('identity'),
			];

			$this->data['password'] = [
				'name' => 'password',
				'id'   => 'password',
				'type' => 'password',
				'value' => $this->data['demo'] ? 'password' : '',
			];

			return $this->renderPage($this->viewsFolder . DIRECTORY_SEPARATOR . 'login', $this->data);
		}
	}

	public function switchLanguage(?string $requestedLocale = null)
	{
		$locale = strtolower(trim((string) ($requestedLocale ?? $this->request->getGet('ui_language') ?? $this->request->getPost('ui_language'))));
		if (!in_array($locale, ['en', 'ar'], true)) {
			return redirect()->back()->with('message', lang('Auth.error_security'));
		}

		$this->session->set('ui_locale', $locale);

		$returnTo = trim((string) ($this->request->getGet('return_to') ?? $this->request->getPost('return_to')));
		if ($returnTo !== '') {
			return redirect()->to($returnTo);
		}

		return redirect()->back();
	}

	/**
	 * Log the user out
	 *
	 * @return \CodeIgniter\HTTP\RedirectResponse
	 */
	public function logout()
	{
		$this->data['title'] = 'Logout';

		// log the user out
		$this->ionAuth->logout();

		// redirect them to the login page with success message
		$this->session->setFlashdata('success', 'You have been logged out successfully.');
		return redirect()->to('/auth/login')->withCookies();
	}

	/**
	 * Change password
	 *
	 * @return string|\CodeIgniter\HTTP\RedirectResponse
	 */
	public function change_password()
	{
		if (! $this->ionAuth->loggedIn())
		{
			return redirect()->to('/auth/login');
		}
		
		$this->validation->setRule('old', lang('Auth.change_password_validation_old_password_label'), 'required');
		$this->validation->setRule('new', lang('Auth.change_password_validation_new_password_label'), 'required|min_length[' . $this->configIonAuth->minPasswordLength . ']|matches[new_confirm]');
		$this->validation->setRule('new_confirm', lang('Auth.change_password_validation_new_password_confirm_label'), 'required');

		$user = $this->ionAuth->user()->row();

		if (! $this->request->getPost() || $this->validation->withRequest($this->request)->run() === false)
		{
			// display the form
			// set the flash data error message if there is one
			$this->data['message'] = ($this->validation->getErrors()) ? $this->validation->listErrors($this->validationListTemplate) : $this->session->getFlashdata('message');

			$this->data['minPasswordLength'] = $this->configIonAuth->minPasswordLength;
			$this->data['old_password'] = [
				'name' => 'old',
				'id'   => 'old',
				'type' => 'password',
			];
			$this->data['new_password'] = [
				'name'    => 'new',
				'id'      => 'new',
				'type'    => 'password',
				'pattern' => '^.{' . $this->data['minPasswordLength'] . '}.*$',
			];
			$this->data['new_password_confirm'] = [
				'name'    => 'new_confirm',
				'id'      => 'new_confirm',
				'type'    => 'password',
				'pattern' => '^.{' . $this->data['minPasswordLength'] . '}.*$',
			];
			$this->data['user_id'] = [
				'name'  => 'user_id',
				'id'    => 'user_id',
				'type'  => 'hidden',
				'value' => $user->id,
			];

			// render
			return $this->renderPage($this->viewsFolder . DIRECTORY_SEPARATOR . 'change_password', $this->data);
		}
		else
		{
			$identity = $this->session->get('identity');

			$change = $this->ionAuth->changePassword($identity, $this->request->getPost('old'), $this->request->getPost('new'));

			if ($change)
			{
				//if the password was successfully changed
				$this->session->setFlashdata('message', $this->ionAuth->messages());
				return $this->logout();
			}
			else
			{
				$this->session->setFlashdata('message', $this->ionAuth->errors($this->validationListTemplate));
				return redirect()->to('/auth/change_password');
			}
		}
	}

	/**
	 * Forgot password
	 *
	 * @return string|\CodeIgniter\HTTP\RedirectResponse
	 */
	public function forgot_password()
	{
		// If user is already logged in, redirect to dashboard
		if ($this->ionAuth->loggedIn()) {
			return redirect()->to('/dashboard');
		}

		$this->data['title'] = lang('Auth.forgot_password_heading');

		// setting validation rules by checking whether identity is username or email
		if ($this->configIonAuth->identity !== 'email')
		{
			$this->validation->setRule('identity', lang('Auth.forgot_password_identity_label'), 'required');
		}
		else
		{
			$this->validation->setRule('identity', lang('Auth.forgot_password_validation_email_label'), 'required|valid_email');
		}

		if (! ($this->request->getPost() && $this->validation->withRequest($this->request)->run()))
		{
			$this->data['type'] = $this->configIonAuth->identity;
			// setup the input
			$this->data['identity'] = [
				'name' => 'identity',
				'id'   => 'identity',
			];

			if ($this->configIonAuth->identity !== 'email')
			{
				$this->data['identity_label'] = lang('Auth.forgot_password_identity_label');
			}
			else
			{
				$this->data['identity_label'] = lang('Auth.forgot_password_email_identity_label');
			}

			// set any errors and display the form
			$this->data['message'] = $this->validation->getErrors() ? $this->validation->listErrors($this->validationListTemplate) : $this->session->getFlashdata('message');
			$this->data['clinic_admin_email'] = $this->session->getFlashdata('clinic_admin_email');
			return $this->renderPage($this->viewsFolder . DIRECTORY_SEPARATOR . 'forgot_password', $this->data);
		}
		else
		{
			$identityColumn = $this->configIonAuth->identity;
			$identity = $this->ionAuth->where($identityColumn, $this->request->getPost('identity'))->users()->row();

			if (empty($identity))
			{
				if ($this->configIonAuth->identity !== 'email')
				{
					$this->ionAuth->setError('Auth.forgot_password_identity_not_found');
				}
				else
				{
					$this->ionAuth->setError('Auth.forgot_password_email_not_found');
				}

				$this->session->setFlashdata('message', $this->ionAuth->errors($this->validationListTemplate));
				return redirect()->to('/auth/forgot-password');
			}

			// generate reset code (email dispatch handled per clinic SMTP)
			$forgotten = $this->ionAuth->forgottenPassword($identity->{$this->configIonAuth->identity});

			if (! $forgotten)
			{
				$this->session->setFlashdata('message', $this->ionAuth->errors($this->validationListTemplate));
				return redirect()->to('/auth/forgot-password');
			}

			$sendResult = $this->sendForgotPasswordEmail((int) $identity->id, (string) $identity->email, $forgotten);
			if (! $sendResult['success'])
			{
				if (!empty($sendResult['clinic_admin_email'])) {
					$this->session->setFlashdata('clinic_admin_email', $sendResult['clinic_admin_email']);
				}
				$this->session->setFlashdata('message', $sendResult['message']);
				return redirect()->to('/auth/forgot-password');
			}

			$this->session->setFlashdata('message', $this->ionAuth->messages());
			return redirect()->to('/auth/login'); //we should display a confirmation page here instead of the login page
		}
	}

	/**
	 * Reset password - final step for forgotten password
	 *
	 * @param string|null $code The reset code
	 *
	 * @return string|\CodeIgniter\HTTP\RedirectResponse
	 */
	public function reset_password($code = null)
	{
		if (! $code)
		{
			throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
		}

		$this->data['title'] = lang('Auth.reset_password_heading');

		$user = $this->ionAuth->forgottenPasswordCheck($code);

		if ($user)
		{
			// if the code is valid then display the password reset form

			$this->validation->setRule('new', lang('Auth.reset_password_validation_new_password_label'), 'required|min_length[' . $this->configIonAuth->minPasswordLength . ']|matches[new_confirm]');
			$this->validation->setRule('new_confirm', lang('Auth.reset_password_validation_new_password_confirm_label'), 'required');

			if (! $this->request->getPost() || $this->validation->withRequest($this->request)->run() === false)
			{
				// display the form

				// set the flash data error message if there is one
				$this->data['message'] = $this->validation->getErrors() ? $this->validation->listErrors($this->validationListTemplate) : $this->session->getFlashdata('message');

				$this->data['minPasswordLength'] = $this->configIonAuth->minPasswordLength;
				$this->data['new_password'] = [
					'name'    => 'new',
					'id'      => 'new',
					'type'    => 'password',
					'pattern' => '^.{' . $this->data['minPasswordLength'] . '}.*$',
				];
				$this->data['new_password_confirm'] = [
					'name'    => 'new_confirm',
					'id'      => 'new_confirm',
					'type'    => 'password',
					'pattern' => '^.{' . $this->data['minPasswordLength'] . '}.*$',
				];
				$this->data['user_id'] = [
					'name'  => 'user_id',
					'id'    => 'user_id',
					'type'  => 'hidden',
					'value' => $user->id,
				];
				$this->data['code'] = $code;

				// render
				return $this->renderPage($this->viewsFolder . DIRECTORY_SEPARATOR . 'reset_password', $this->data);
			}
			else
			{
				$identity = $user->{$this->configIonAuth->identity};

				// do we have a valid request?
				if ($user->id != $this->request->getPost('user_id'))
				{
					// something fishy might be up
					$this->ionAuth->clearForgottenPasswordCode($identity);

					throw new \Exception(lang('Auth.error_security'));
				}
				else
				{
					// finally change the password
					$change = $this->ionAuth->resetPassword($identity, $this->request->getPost('new'));

					if ($change)
					{
						// if the password was successfully changed
						$this->session->setFlashdata('message', $this->ionAuth->messages());
						return redirect()->to('/auth/login');
					}
					else
					{
						$this->session->setFlashdata('message', $this->ionAuth->errors($this->validationListTemplate));
						return redirect()->to('/auth/reset-password/' . $code);
					}
				}
			}
		}
		else
		{
			// if the code is invalid then send them back to the forgot password page
			$this->session->setFlashdata('message', $this->ionAuth->errors($this->validationListTemplate));
			return redirect()->to('/auth/forgot-password');
		}
	}

	/**
	 * Activate the user
	 *
	 * @param integer $id   The user ID
	 * @param string  $code The activation code
	 *
	 * @return \CodeIgniter\HTTP\RedirectResponse
	 */
	public function activate(int $id, string $code = ''): \CodeIgniter\HTTP\RedirectResponse
	{
		$activation = false;

		if ($code)
		{
			$activation = $this->ionAuth->activate($id, $code);
		}
		else
		{
			$redirect = $this->requirePlatformSuperAdmin();
			if ($redirect) {
				return $redirect;
			}
			$activation = $this->ionAuth->activate($id);
		}

		if ($activation)
		{
			// redirect them to the auth page
			$this->session->setFlashdata('message', $this->ionAuth->messages());
			return redirect()->to('/auth');
		}
		else
		{
			// redirect them to the forgot password page
			$this->session->setFlashdata('message', $this->ionAuth->errors($this->validationListTemplate));
			return redirect()->to('/auth/forgot-password');
		}
	}

	/**
	 * Deactivate the user
	 *
	 * @param integer $id The user ID
	 *
	 * @throw Exception
	 *
	 * @return string|\CodeIgniter\HTTP\RedirectResponse
	 */
	public function deactivate(int $id = 0)
	{
		$redirect = $this->requirePlatformSuperAdmin();
		if ($redirect) {
			return $redirect;
		}

		$this->validation->setRule('confirm', lang('Auth.deactivate_validation_confirm_label'), 'required');
		$this->validation->setRule('id', lang('Auth.deactivate_validation_user_id_label'), 'required|integer');

		if (! $this->validation->withRequest($this->request)->run())
		{
			$this->data['user'] = $this->ionAuth->user($id)->row();
			return $this->renderPage($this->viewsFolder . DIRECTORY_SEPARATOR . 'deactivate_user', $this->data);
		}
		else
		{
			// do we really want to deactivate?
			if ($this->request->getPost('confirm') === 'yes')
			{
				// do we have a valid request?
				if ($id !== (int) $this->request->getPost('id'))
				{
					$this->session->setFlashdata('message', lang('Auth.error_security'));
					return redirect()->to('/auth');
				}

				$message = $this->ionAuth->deactivate($id) ? $this->ionAuth->messages() : $this->ionAuth->errors($this->validationListTemplate);
				$this->session->setFlashdata('message', $message);
			}

			// redirect them back to the auth page
			return redirect()->to('/auth');
		}
	}

	/**
	 * Create a new user
	 *
	 * @return string|\CodeIgniter\HTTP\RedirectResponse
	 */
	public function create_user()
	{
		$redirect = $this->requirePlatformSuperAdmin();
		if ($redirect) {
			return $redirect;
		}

		return redirect()->to('/controlplane/onboarding/clinic/create');
	}


	/**
	 * Redirect a user checking if is admin
	 *
	 * @return \CodeIgniter\HTTP\RedirectResponse
	 */
	public function redirectUser()
	{
		if ($this->ionAuth->loggedIn() && (new \App\Services\PermissionService())->isSuperAdmin((int) $this->ionAuth->getUserId()))
		{
			return redirect()->to('/auth');
		}
		return redirect()->to('/dashboard');
	}

	/**
	 * Edit a user
	 *
	 * @param integer $id User id
	 *
	 * @return string string|\CodeIgniter\HTTP\RedirectResponse
	 */
	public function edit_user(int $id)
	{
		$redirect = $this->requirePlatformSuperAdmin();
		if ($redirect) {
			return $redirect;
		}

		return redirect()->to('/controlplane/users/' . (int) $id);
	}


	/**
	 * Create a new group
	 *
	 * @return string string|\CodeIgniter\HTTP\RedirectResponse
	 */
	public function create_group()
	{
		$redirect = $this->requirePlatformSuperAdmin();
		if ($redirect) {
			return $redirect;
		}

		return redirect()->to('/controlplane/roles');
	}


	/**
	 * Edit a group
	 *
	 * @param integer $id Group id
	 *
	 * @return string|CodeIgniter\Http\Response
	 */
	public function edit_group(int $id = 0)
	{
		$redirect = $this->requirePlatformSuperAdmin();
		if ($redirect) {
			return $redirect;
		}

		return redirect()->to('/controlplane/roles');
	}


	private function sendForgotPasswordEmail(int $userId, string $email, $forgottenData): array
	{
		if (!is_array($forgottenData)) {
			$forgottenData = [
				'identity' => $email,
				'forgottenPasswordCode' => (string) $forgottenData
			];
		}
		$clinicUserModel = new ClinicUserModel();
		$memberships = $clinicUserModel->getMemberships($userId);

		if (empty($memberships)) {
			return [
				'success' => false,
				'message' => 'No active clinic found for this account. Please contact your clinic administrator.'
			];
		}

		if (count($memberships) > 1) {
			foreach ($memberships as $membership) {
				$this->logPasswordResetIssue((int) $membership['clinic_id'], $userId, 'MULTIPLE_CLINICS');
			}
			return [
				'success' => false,
				'message' => 'Multiple clinics found for this account. Please contact your clinic administrator to reset your password.'
			];
		}

		$clinicId = (int) $memberships[0]['clinic_id'];
		$channelModel = (new ClinicNotificationChannelModel())->withoutTenantScope();
		$config = $channelModel->getConfig($clinicId, 'email');
		$channel = $channelModel->where('clinic_id', $clinicId)
			->where('channel_type', 'email')
			->first();

		$clinicAdminEmail = $this->getClinicAdminEmail($clinicId);
		$supportEmail = $this->getPlatformSupportEmail();

		$isReady = $config
			&& ($channel['enabled_by_superadmin'] ?? 0)
			&& ($channel['configured_by_clinic'] ?? 0)
			&& ($channel['validated'] ?? 0);

		if (! $isReady) {
			$this->logPasswordResetIssue($clinicId, $userId, 'SMTP_NOT_CONFIGURED');
			return [
				'success' => false,
				'message' => 'Email delivery is not configured for this clinic. Please contact platform support at ' . $supportEmail . '.',
				'clinic_admin_email' => $clinicAdminEmail
			];
		}

		$message = view($this->configIonAuth->emailTemplates . $this->configIonAuth->emailForgotPassword, $forgottenData);
		$emailService = \Config\Services::email();
		$emailService->initialize([
			'protocol' => 'smtp',
			'SMTPHost' => $config['smtp_host'] ?? '',
			'SMTPUser' => $config['smtp_user'] ?? '',
			'SMTPPass' => $config['smtp_pass'] ?? '',
			'SMTPPort' => (int)($config['smtp_port'] ?? 587),
			'SMTPCrypto' => $config['smtp_crypto'] ?? 'tls',
			'mailType' => 'html',
			'charset'  => 'utf-8',
			'newline'  => "\r\n"
		]);

		$fromEmail = $config['smtp_from_email'] ?? $config['smtp_user'];
		$fromName = $config['smtp_from_name'] ?? 'Clinic Notification';

		if (empty($fromEmail)) {
			$this->logPasswordResetIssue($clinicId, $userId, 'SMTP_FROM_MISSING');
			return [
				'success' => false,
				'message' => 'Email delivery is not configured for this clinic. Please contact platform support at ' . $supportEmail . '.',
				'clinic_admin_email' => $clinicAdminEmail
			];
		}

		$emailService->setFrom($fromEmail, $fromName);
		$emailService->setTo($email);
		$emailService->setSubject($this->configIonAuth->siteTitle . ' - ' . lang('IonAuth.email_forgotten_password_subject'));
		$emailService->setMessage($message);

		if (! $emailService->send()) {
			$this->logPasswordResetIssue($clinicId, $userId, 'SMTP_SEND_FAILED');
			return [
				'success' => false,
				'message' => 'We could not send the reset email. Please contact your clinic administrator.'
			];
		}

		return ['success' => true];
	}

	private function getClinicAdminEmail(int $clinicId): ?string
	{
		$admin = (new ClinicUserModel())->findClinicAdminEmailForClinic($clinicId);

		if (!empty($admin['email'])) {
			return $admin['email'];
		}

		$settings = (new SettingsModel())->withoutTenantScope();
		$clinicEmail = $settings->where('clinic_id', $clinicId)
			->where('setting_key', 'clinic_email')
			->first();

		return $clinicEmail['setting_value'] ?? null;
	}

	private function getPlatformSupportEmail(): string
	{
		$settings = new SettingsModel();
		$email = $settings->getGlobalSetting('platform_support_email', $this->configIonAuth->adminEmail);
		return $email ?: $this->configIonAuth->adminEmail;
	}

	private function logPasswordResetIssue(int $clinicId, int $userId, string $reason): void
	{
		try {
			ApiRuntimeContext::setClinicId($clinicId);
			$activityLog = new ActivityLogModel();
			$activityLog->logActivity(
				$userId,
				'password_reset_failed',
				'auth',
				$userId,
				'Password reset email blocked: ' . $reason,
				['reason' => $reason]
			);
		} catch (\Throwable $e) {
			log_message('error', 'Failed to log password reset issue: ' . $e->getMessage());
		} finally {
			ApiRuntimeContext::clear();
		}
	}

	/**
	 * Render the specified view
	 *
	 * @param string     $view       The name of the file to load
	 * @param array|null $data       An array of key/value pairs to make available within the view.
	 * @param boolean    $returnHtml If true return html string
	 *
	 * @return string|void
	 */
	protected function renderPage(string $view, $data = null, bool $returnHtml = true): string
	{
		$viewdata = $data ?: $this->data;

		$viewHtml = view($view, $viewdata);

		if ($returnHtml)
		{
			return $viewHtml;
		}
		else
		{
			echo $viewHtml;
		}
	}
}
