<?php

namespace Drupal\social_auth_linkedin\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\social_api\Plugin\NetworkManager;
use Drupal\social_auth\SocialAuthDataHandler;
use Drupal\social_auth\SocialAuthUserManager;
use Drupal\social_auth_linkedin\LinkedInAuthManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Routing\TrustedRedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;

/**
 * Returns responses for Simple LinkedIn Connect module routes.
 */
class LinkedInAuthController extends ControllerBase {

  /**
   * The network plugin manager.
   *
   * @var \Drupal\social_api\Plugin\NetworkManager
   */
  private $networkManager;

  /**
   * The user manager.
   *
   * @var \Drupal\social_auth\SocialAuthUserManager
   */
  private $userManager;

  /**
   * The linkedin authentication manager.
   *
   * @var \Drupal\social_auth_linkedin\LinkedInAuthManager
   */
  private $linkedInManager;

  /**
   * Used to access GET parameters.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  private $request;

  /**
   * The Social Auth Data Handler.
   *
   * @var \Drupal\social_auth\SocialAuthDataHandler
   */
  private $dataHandler;

  /**
   * LinkedInAuthController constructor.
   *
   * @param \Drupal\social_api\Plugin\NetworkManager $network_manager
   *   Used to get an instance of social_auth_linkedin network plugin.
   * @param \Drupal\social_auth\SocialAuthUserManager $user_manager
   *   Manages user login/registration.
   * @param \Drupal\social_auth_linkedin\LinkedInAuthManager $linkedin_manager
   *   Used to manage authentication methods.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request
   *   Used to access GET parameters.
   * @param \Drupal\social_auth\SocialAuthDataHandler $data_handler
   *   SocialAuthDataHandler object.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   Used for logging errors.
   */
  public function __construct(NetworkManager $network_manager,
                              SocialAuthUserManager $user_manager,
                              LinkedInAuthManager $linkedin_manager,
                              RequestStack $request,
                              SocialAuthDataHandler $data_handler,
                              LoggerChannelFactoryInterface $logger_factory) {

    $this->networkManager = $network_manager;
    $this->userManager = $user_manager;
    $this->linkedInManager = $linkedin_manager;
    $this->request = $request;
    $this->dataHandler = $data_handler;

    // Sets the plugin id.
    $this->userManager->setPluginId('social_auth_linkedin');

    // Sets the session keys to nullify if user could not logged in.
    $this->userManager->setSessionKeysToNullify(['access_token', 'oauth2state']);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin.network.manager'),
      $container->get('social_auth.user_manager'),
      $container->get('social_auth_linkedin.manager'),
      $container->get('request_stack'),
      $container->get('social_auth.data_handler'),
      $container->get('logger.factory')
    );
  }

  /**
   * Response for path 'user/login/linkedin'.
   *
   * Redirects the user to LinkedIn for authentication.
   */
  public function redirectToLinkedIn() {
    /* @var \League\OAuth2\Client\Provider\LinkedIn|false $linkedin */
    $linkedin = $this->networkManager->createInstance('social_auth_linkedin')->getSdk();

    // If LinkedIn client could not be obtained.
    if (!$linkedin) {
      drupal_set_message($this->t('Social Auth LinkedIn not configured properly. Contact site administrator.'), 'error');
      return $this->redirect('user.login');
    }

    // LinkedIn service was returned, inject it to $linkedinManager.
    $this->linkedInManager->setClient($linkedin);

    // Generates the URL where the user will be redirected for LinkedIn login.
    // If the user did not have email permission granted on previous attempt,
    // we use the re-request URL requesting only the email address.
    $linkedin_login_url = $this->linkedInManager->getAuthorizationUrl();

    $state = $this->linkedInManager->getState();

    $this->dataHandler->set('oauth2state', $state);

    return new TrustedRedirectResponse($linkedin_login_url);
  }

  /**
   * Response for path 'user/login/linkedin/callback'.
   *
   * LinkedIn returns the user here after user has authenticated in LinkedIn.
   */
  public function callback() {
    // Checks if user cancel login via LinkedIn.
    $error = $this->request->getCurrentRequest()->get('error');
    if ($error == 'user_cancelled_login' || $error == 'user_cancelled_authorize') {
      drupal_set_message($this->t('You could not be authenticated.'), 'error');
      return $this->redirect('user.login');
    }

    /* @var \League\OAuth2\Client\Provider\LinkedIn|false $linkedin */
    $linkedin = $this->networkManager->createInstance('social_auth_linkedin')->getSdk();

    // If LinkedIn client could not be obtained.
    if (!$linkedin) {
      drupal_set_message($this->t('Social Auth LinkedIn not configured properly. Contact site administrator.'), 'error');
      return $this->redirect('user.login');
    }

    $state = $this->dataHandler->get('oauth2state');

    // Retreives $_GET['state'].
    $retrievedState = $this->request->getCurrentRequest()->query->get('state');
    if (empty($retrievedState) || ($retrievedState !== $state)) {
      $this->userManager->nullifySessionKeys();
      drupal_set_message($this->t('LinkedIn login failed. Unvalid OAuth2 State.'), 'error');
      return $this->redirect('user.login');
    }

    // Saves access token to session.
    $this->dataHandler->set('access_token', $this->linkedInManager->getAccessToken());

    $this->linkedInManager->setClient($linkedin)->authenticate();

    // Gets user's info from LinkedIn API.
    if (!$profile = $this->linkedInManager->getUserInfo()) {
      drupal_set_message($this->t('LinkedIn login failed, could not load LinkedIn profile. Contact site administrator.'), 'error');
      return $this->redirect('user.login');
    }

    // Gets (or not) extra initial data.
    $data = $this->userManager->checkIfUserExists($profile->getId()) ? NULL : $this->linkedInManager->getExtraDetails();

    // If user information could be retrieved.
    return $this->userManager->authenticateUser($profile->getFirstName() . ' ' . $profile->getLastName(), $profile->getEmail(), $profile->getId(), $this->linkedInManager->getAccessToken(), $profile->getImageurl(), $data);
  }

}
