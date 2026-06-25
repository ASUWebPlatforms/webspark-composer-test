<?php

namespace Drupal\elevate_experience_import\Controller;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Cache\CacheableJsonResponse;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\flag\FlagServiceInterface;
use Drupal\node\NodeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Endpoints powering the Experience Finder "favourite" hearts.
 */
class FavoriteController extends ControllerBase {

  /**
   * The flag id used for Experience favourites.
   */
  const FLAG_ID = 'favorite';

  /**
   * The flag service.
   *
   * @var \Drupal\flag\FlagServiceInterface
   */
  protected $flagService;

  /**
   * Constructs the controller.
   */
  public function __construct(FlagServiceInterface $flag_service) {
    $this->flagService = $flag_service;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('flag'));
  }

  /**
   * Toggles the favourite flag for the given Experience node.
   */
  public function toggle(NodeInterface $node) {
    $flag = $this->flagService->getFlagById(self::FLAG_ID);
    if (!$flag || $node->bundle() !== 'experience') {
      throw new AccessDeniedHttpException();
    }

    $account = $this->currentUser();
    $is_flagged = (bool) $this->flagService->getFlagging($flag, $node, $account);

    if ($is_flagged) {
      $this->flagService->unflag($flag, $node, $account);
      $flagged = FALSE;
    }
    else {
      $this->flagService->flag($flag, $node, $account);
      $flagged = TRUE;
    }

    // Refresh this user's favourites list endpoint and the Favorites view.
    Cache::invalidateTags(['flagging_list:' . $account->id()]);

    return new JsonResponse([
      'nid' => (int) $node->id(),
      'flagged' => $flagged,
    ]);
  }

  /**
   * Returns the node ids the current user has marked as favourite.
   */
  public function list() {
    $flag = $this->flagService->getFlagById(self::FLAG_ID);
    $nids = [];
    if ($flag) {
      $flaggings = $this->entityTypeManager()->getStorage('flagging')->loadByProperties([
        'flag_id' => self::FLAG_ID,
        'uid' => $this->currentUser()->id(),
      ]);
      foreach ($flaggings as $flagging) {
        $nids[] = (int) $flagging->get('entity_id')->value;
      }
    }

    $response = new CacheableJsonResponse(['nids' => array_values($nids)]);
    // Vary per user and invalidate when this user's flaggings change.
    $response->addCacheableDependency((new CacheableMetadata())
      ->setCacheContexts(['user'])
      ->setCacheTags(['flagging_list:' . $this->currentUser()->id()]));
    return $response;
  }

}
