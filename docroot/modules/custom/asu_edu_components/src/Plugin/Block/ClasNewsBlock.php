<?php

namespace Drupal\asu_edu_components\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Url;

/**
 * Provides a clas news block.
 *
 * @Block(
 *   id = "asu_edu_components_clas_news",
 *   admin_label = @Translation("Clas News"),
 *   category = @Translation("ASU.edu")
 * )
 */
class ClasNewsBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a new ClasNewsBlock instance.
   *
   * @param array $configuration
   *   The plugin configuration, i.e. an array with configuration values keyed
   *   by configuration option name. The special key 'context' may be used to
   *   initialize the defined contexts by setting it to an array of context
   *   values keyed by context names.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'section' => $this->t('&Science&Student life'),
      'view' => $this->t('Carousel'),
      'items' => 9,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form['header_title'] = [
      '#type' => 'textfield',
      '#title' => $this->t(' Header title'),
      '#default_value' => $this->configuration['header_title'],
    ];

    $form['section'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Section'),
      '#description' => $this->t('Sample value: Science'),
      '#default_value' => $this->configuration['section'],
      '#required' => TRUE,
    ];

    $form['view'] = [
      '#type' => 'select',
      '#title' => $this->t('View'),
      '#options' => [
        'Carousel' => $this->t('Carousel'),
        'Cards' => $this->t('Cards'),
        'Horizontal' => $this->t('Horizontal'),
      ],
      '#default_value' => $this->configuration['view'],
      '#required' => TRUE,
    ];

    $form['items'] = [
      '#title' => $this->t('Items'),
      '#type' => 'number',
      '#min' => 3,
      '#max' => 30,
      '#step' => 1,
      '#default_value' => $this->configuration['items'],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->configuration['header_title'] = $form_state->getValue('header_title');
    $this->configuration['section'] = $form_state->getValue('section');
    $this->configuration['view'] = $form_state->getValue('view');
    $this->configuration['items'] = $form_state->getValue('items');
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $url = Url::fromRoute('api_proxy.forwarder', ['api_proxy' => 'api-clas-news'], [
      'query' => [
        '_api_proxy_uri' => ''
      ],
    ])->toString();

    return [
      '#theme' => 'clas_news',
      '#news_feed_url' => $url,
      '#view' => $this->configuration['view'],
      '#items' => $this->configuration['items'],
      '#section' => $this->configuration['section'],
      '#header_title' => $this->configuration['header_title'],
      '#attached' => [
        'library' => [
          'asu_edu_components/clas_news',
          'asu_edu_components/facebook.react',
          'asu_edu_components/facebook.react-dom',
        ],
      ],
    ];

  }

}
