<?php

namespace Drupal\Tests\l10n_client_contributor\Functional;

use Drupal\Component\Gettext\PoHeader;
use Drupal\Core\Url;
use Drupal\language\Entity\ConfigurableLanguage;
use Drupal\Tests\BrowserTestBase;

/**
 * Tests translation sending.
 *
 * @group l10n_client
 */
class L10nSubmitTranslationTest extends BrowserTestBase {

  /**
   * Modules to install.
   *
   * @var array
   */
  public static $modules = ['node', 'l10n_client_contributor', 'l10n_client_test'];

  protected $adminUser;

  public function setUp() {
    parent::setUp();
    $this->adminUser = $this->drupalCreateUser(array(
      'access content',
      'contribute translations to localization server',
    ));
  }

  public function testTranslationSubmission() {
    global $base_url;

    $url = URL::fromRoute('l10n_client_test.xmlrpc');
    $url_path = str_replace('/xmlrpc.php', '', $url->getInternalPath());
    $config = \Drupal::configFactory()->getEditable('l10n_client_contributor.settings');
    $config->set('server', $base_url . '/' . $url_path);
    $config->set('use_server', TRUE);
    $config->save();

    // Add the german language.
    ConfigurableLanguage::createFromLangcode('de')->save();
    $formula = 'nplurals=2; plural=(n!=1);';
    $header = new PoHeader();
    list($nplurals, $formula) = $header->parsePluralForms($formula);
    \Drupal::service('locale.plural.formula')->setPluralFormula('de', $nplurals, $formula);

    $language_config = \Drupal::configFactory()->getEditable('language.types');
    $language_config->set('negotiation.language_interface.enabled', array(
      'language-url' => 1,
    ));
    $language_config->set('negotiation.language_content.enabled', array(
      'language-interface' => 1,
    ));
    $language_config->set('negotiation.language_url.enabled', array(
      'language-url' => 1,
      'language-url-fallback' => 1,
    ));

    $language_config->save();

    // Create user that is authorized to use the l10n contributor.
    $this->drupalLogin($this->adminUser);
    $token_generator = \Drupal::csrfToken();
    $token = $token_generator->get('l10n-client-test');
    $this->adminUser->set('l10n_client_contributor_key', $token);
    $this->adminUser->save();

    // Parse the form token.
    $this->drupalGet('de/user/' . $this->adminUser->id() . '/edit');
    // Post a fake translation.
    $translation_source = 'Password';
    $translation_target = 'Translation suggestion for Password';

    $storage = \Drupal::service('locale.storage');
    $conditions = array(
      'context' => '',
      'source' => $translation_source,
    );

    $source_object = $storage->getStrings($conditions);
    // Get the last lid in the table.
    $lid = (int) $source_object[0]->getId();
    $test_translation_object = array(
      $lid => array(
        'translations' => array($translation_target),
      ),
    );
    $response = l10n_client_contributor_save_translation('en', array($lid), $test_translation_object);
    // Check response of the server.
    $this->assertTrue(strpos($response->render(), 'Translation sent and accepted by') !== FALSE, 'Translation sent and accepted by the server.');

    // Get returned data that is mocked in the l10n_client_test module.
    $saved_xml = \Drupal::state()->get('l10n_client_test_mock_request');
    $saved_xml = new \SimpleXMLElement($saved_xml);

    // Assert basic structure of the saved data.
    $this->assertEquals((string) $saved_xml->methodName, 'l10n.submit.translation', 'Right methodname was returned.');
    $this->assertEquals(count($saved_xml->params->param), 6, 'Response XML contains right amount of parameters.');

    // Assert values in saved data.
    $this->assertEquals((string) $saved_xml->params[0]->param[0]->value->string, 'en', 'Source language parameter is correct.');
    $this->assertEquals((string) $saved_xml->params[0]->param[1]->value->string, $translation_source, 'Source string parameter is correct.');
    $this->assertEquals((string) $saved_xml->params[0]->param[2]->value->string, $translation_target, 'Suggestion string parameter is correct.');
  }

}
