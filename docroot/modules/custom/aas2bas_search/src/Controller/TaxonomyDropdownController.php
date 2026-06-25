<?php

namespace Drupal\aas2bas_search\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Form\FormBuilderInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class TaxonomyDropdownController extends ControllerBase {

    /**
     * @var \Drupal\Core\Form\FormBuilderInterface
     */
    protected $formBuilder;

    /**
     * Constructs a TaxonomyDropdownController object.
     */
    public function __construct(FormBuilderInterface $formBuilder) {
        $this->formBuilder = $formBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public static function create(ContainerInterface $container) {
        return new static(
            $container->get('form_builder')
        );
    }

    /**
     * Content callback for the dropdown page.
     */
    public function content() {
        // Return the form as a redereable array.
        return $this->formBuilder->getForm('Drupal\aas2bas_search\Form\TaxonomyDropdownForm');
    }
}