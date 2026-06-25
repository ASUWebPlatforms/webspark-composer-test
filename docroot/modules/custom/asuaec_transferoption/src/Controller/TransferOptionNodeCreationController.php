<?php
/**
 * @file
 * Contains \Drupal\asuaec_transferoption\Controller\TransferOptionNodeCreationController.
 */
namespace Drupal\asuaec_transferoption\Controller;

use Drupal\asuaec_transferoption\NodeCreation\TransferOptionNodeGenerator;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\Url;

class TransferOptionNodeCreationController extends ControllerBase
{
  private $transferoptionNodeGenerator;

  public function __construct(TransferOptionNodeGenerator $transferoptionNodeGenerator)
  {
    $this->transferoptionNodeGenerator = $transferoptionNodeGenerator;
  }

  // Process to create a node
  public function process($plancode, $comm_college) {

    $config = \Drupal::config('asuaec_transferoption.settings');
    $degreelistingpage_nid = $config->get('asuaec_transferoption_degreelistingpage_nid');
//    \Drupal::logger('asuaec_transferoption')->notice("degreelistingpage_nid from Controller:<pre>" . $degreelistingpage_nid . "</pre>");

//    $degreelistingpage_nid = '1928';


    $path = '/bachelors-degrees/majorinfo/' . $plancode . '/undergrad/false/' . $degreelistingpage_nid;
    $is_valid = \Drupal::service('path.validator')->isValid($path);

    // If the path doesn't exist, create the Degree detail page node from the UTO RFI module. If the path already exists, just redirect to the Degree detail page node.
    if($is_valid == false) {
      $newNode = $this->transferoptionNodeGenerator->generateTransferOptionNode($plancode, $path);
    }
    return new RedirectResponse($path . '?comm-college=' . $comm_college);
  }

  public static function create(ContainerInterface $container)
  {
    $nodeGenerator = $container->get('asuaec_transferoption.nodecreation');
    return new static($nodeGenerator);
  }


}