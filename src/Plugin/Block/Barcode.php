<?php

namespace Drupal\barcodes\Plugin\Block;

use Com\Tecnick\Barcode\Barcode as BarcodeGenerator;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Logger\LoggerChannelFactory;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'Barcode' block.
 *
 * @Block(
 *  id = "barcode",
 *  admin_label = @Translation("Barcode"),
 * )
 */
class Barcode extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Drupal\Core\Logger\LoggerChannelFactory definition.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactory
   */
  protected $loggerFactory;

  /**
   * Construct.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param string $plugin_definition
   *   The plugin implementation definition.
   * @param LoggerChannelFactory $logger_factory
   *   The logger factory.
   */
  public function __construct(
      array $configuration,
      $plugin_id,
      $plugin_definition,
      LoggerChannelFactory $logger_factory
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->loggerFactory = $logger_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(
      ContainerInterface $container,
      array $configuration,
      $plugin_id,
      $plugin_definition
  ) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('logger.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'type' => 'QRCODE',
      'value' => '',
      'color' => '#000000',
      'height' => 100,
      'width' => 100,
      'padding_top' => 0,
      'padding_right' => 0,
      'padding_bottom' => 0,
      'padding_left' => 0,
      'show_value' => FALSE,
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $generator = new BarcodeGenerator();
    $form['value'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Value'),
      '#description' => $this->t('The Barcode value.'),
      '#default_value' => $this->configuration['value'],
    ];
    $form['type'] = [
      '#type' => 'select',
      '#title' => $this->t('Barcode Type'),
      '#description' => $this->t('The Barcode type.'),
      '#options' => array_combine($generator->getTypes(), $generator->getTypes()),
      '#default_value' => $this->configuration['type'],
    ];
    $form['color'] = array(
      '#type' => 'color',
      '#title' => $this->t('Color'),
      '#default_value' => $this->configuration['color'],
      '#description' => $this->t('The color code.'),
    );
    $form['height'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Height'),
      '#size' => 10,
      '#default_value' => $this->configuration['height'],
      '#description' => $this->t('The height in pixels.'),
    ];
    $form['width'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Width'),
      '#size' => 10,
      '#default_value' => $this->configuration['width'],
      '#description' => $this->t('The width in pixels'),
    ];
    $form['padding_top'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Padding-Top'),
      '#size' => 4,
      '#maxlength' => 4,
      '#default_value' => $this->configuration['padding_top'],
      '#description' => $this->t('The top padding in pixels'),
    ];
    $form['padding_right'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Padding-Right'),
      '#size' => 4,
      '#maxlength' => 4,
      '#default_value' => $this->configuration['padding_right'],
      '#description' => $this->t('The right padding in pixels'),
    ];
    $form['padding_bottom'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Padding-Bottom'),
      '#size' => 4,
      '#maxlength' => 4,
      '#default_value' => $this->configuration['padding_bottom'],
      '#description' => $this->t('The bottom padding in pixels'),
    ];
    $form['padding_left'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Padding-Left'),
      '#size' => 4,
      '#maxlength' => 4,
      '#default_value' => $this->configuration['padding_left'],
      '#description' => $this->t('The left padding in pixels'),
    ];
    $form['show_value'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Show value'),
      '#default_value' => $this->configuration['show_value'],
      '#description' => $this->t('Show the actual value in addition to the barcode'),
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    $this->setConfiguration($values);
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];
    $generator = new BarcodeGenerator();
    $suffix = str_replace(
      '+', 'plus', strtolower($this->configuration['type'])
    );
    $build['barcode'] = [
      '#theme' => 'barcode__' . $suffix,
      '#attached' => [
        'library' => [
          'barcodes/' . $suffix,
        ],
      ],
      '#type' => $this->configuration['type'],
      '#value' => $this->configuration['value'],
      '#width' => $this->configuration['width'],
      '#height' => $this->configuration['height'],
      '#color' => $this->configuration['color'],
      '#padding_top' => $this->configuration['padding_top'],
      '#padding_right' => $this->configuration['padding_right'],
      '#padding_bottom' => $this->configuration['padding_bottom'],
      '#padding_left' => $this->configuration['padding_left'],
      '#show_value' => $this->configuration['show_value'],
    ];

    try {
      $barcode = $generator->getBarcodeObj(
        $this->configuration['type'],
        $this->configuration['value'],
        $this->configuration['width'],
        $this->configuration['height'],
        $this->configuration['color'],
        [
          $this->configuration['padding_top'],
          $this->configuration['padding_right'],
          $this->configuration['padding_bottom'],
          $this->configuration['padding_left'],
        ]
      );
      $build['barcode']['#svg'] = $barcode->getSvgCode();
    }
    catch (\Exception $e) {
      /** @var \Drupal\Core\Logger\LoggerChannelInterface $logger */
      $logger = $this->loggerFactory->get('barcodes');
      $logger->error(
        'Error: @error, given: @value',
        [
          '@error' => $e->getMessage(),
          '@value' => $this->configuration['value'],
        ]
      );
    }
    return $build;
  }

}
