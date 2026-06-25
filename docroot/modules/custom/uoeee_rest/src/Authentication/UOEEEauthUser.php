<?php


namespace Drupal\uoeee_rest\Authentication;

use Drupal\Core\Authentication\AuthenticationProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Session\UserSession;
use Drupal\Core\Entity\EntityManagerInterface;

/**
 * Authentication provider to validate requests anonymous user
 * @package Drupal\uoeee_rest\Authentication\Provider
 */

class UOEEEauthUser implements AuthenticationProviderInterface {

    /**
     * {@inheritdoc}
     */

    public function applies(Request $request) {
        // If Authentication Provider is enabled always apply
        
        $page = \Drupal::request()->getRequestUri();
        $po = str_contains($page, 'api/publicoutcomes');
        $ce = str_contains($page, 'api/cesections');
        $db = str_contains($page, 'api/db');
        $tst = false;
        if ($ce) {
            $tst = true;
        }

        return $tst;
    }

     /**
     * {@inheritdoc}
     */

    public function authenticate(Request $request) {
        //debug('auth provider');
        //return $this->entityManager->getStorage('user')->load(0);
        return new UserSession();
    }

    /**
     * {@inheritdoc}
     */
    public function cleanup(Request $request) {}
}


