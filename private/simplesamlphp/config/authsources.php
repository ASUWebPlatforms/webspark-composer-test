<?php

// To see a full list of examples, see https://github.com/simplesamlphp/simplesamlphp/blob/master/config/authsources.php.dist
$config = [
  'admin' => [
    'core:AdminPassword',
  ],
  'acquia-dev-sp' => [
    'saml:SP',
    'entityID' => 'urn:drupal:asu-analytics-acquia-dev-adfs',
    'idp' => 'http://federation.asu.edu/adfs/services/trust',
    'discoURL' => null,
    'redirect.sign' => true,
    'assertion.encryption' => true,
    'sign.logout' => true,
    'NameIDPolicy' => [
      'Format' => 'urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified',
      'AllowCreate' => true
    ],
    'privatekey' => 'asu-analytics-acquia-dev-saml.pem',
    'certificate' => 'asu-analytics-acquia-dev-saml.crt',
  ],
  'acquia-test-sp' => [
    'saml:SP',
    'entityID' => 'urn:drupal:asu-analytics-acquia-test-adfs',
    'idp' => 'http://federation.asu.edu/adfs/services/trust',
    'discoURL' => null,
    'redirect.sign' => true,
    'assertion.encryption' => true,
    'sign.logout' => true,
    'NameIDPolicy' => [
      'Format' => 'urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified',
      'AllowCreate' => true
    ],
    'privatekey' => 'asu-analytics-acquia-test-saml.pem',
    'certificate' => 'asu-analytics-acquia-test-saml.crt',
  ],
  'acquia-sp' => [
    'saml:SP',
    'entityID' => 'urn:drupal:asu-analytics-acquia-adfs',
    'idp' => 'http://federation.asu.edu/adfs/services/trust',
    'discoURL' => null,
    'redirect.sign' => true,
    'assertion.encryption' => true,
    'sign.logout' => true,
    'NameIDPolicy' => [
      'Format' => 'urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified',
      'AllowCreate' => true
    ],
    'privatekey' => 'asu-analytics-acquia-saml.pem',
    'certificate' => 'asu-analytics-acquia-saml.crt',
  ],
];
