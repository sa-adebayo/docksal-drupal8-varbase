<?php

namespace Drupal\l10n_client_test\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Response;

/**
 * Returns responses for comment module administrative routes.
 */
class TestController extends ControllerBase {

  /**
   * Mock page for returning a mock XML-RPC response as if this was a server.
   *
   * Stores the request value for testing purposes.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   */
  public function mockPage() {
    // @todo using state may not be good for testing from the test itself.
    $this->state()->set('l10n_client_test_mock_request', file_get_contents('php://input'));

    $response = Response::create('<?xml version="1.0"?>
<methodResponse>
  <params>
  <param>
    <value><struct>
  <member><name>status</name><value><boolean>1</boolean></value></member>
  <member><name>sid</name><value><string>387</string></value></member>
</struct></value>
  </param>
  </params>
</methodResponse>', 200)->setSharedMaxAge(300);
    $response->headers->set('Content-Type', 'application/xml');
    return $response;
  }

}
