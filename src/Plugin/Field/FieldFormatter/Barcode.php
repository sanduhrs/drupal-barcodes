<?php

namespace Drupal\barcodes\Plugin\Field\FieldFormatter;

use Drupal\Component\Utility\Html;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;
use Com\Tecnick\Barcode\Barcode as BarcodeGenerator;

/**
 * Plugin implementation of the 'barcode' formatter.
 *
 * @FieldFormatter(
 *   id = "barcode",
 *   label = @Translation("Barcode"),
 *   field_types = {
 *     "email",
 *     "link",
 *     "string",
 *     "telephone",
 *     "text"
 *   }
 * )
 */
class Barcode extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'type' => 'QRCODE',
      'color' => '#000000',
      'height' => 100,
      'width' => 100,
      'padding_top' => 10,
      'padding_right' => 10,
      'padding_bottom' => 10,
      'padding_left' => 10,
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $generator = new BarcodeGenerator();
    $settings['type'] = [
      '#type' => 'select',
      '#title' => $this->t('Barcode Type'),
      '#description' => $this->t('The Barcode type.'),
      '#options' => array_combine($generator->getTypes(), $generator->getTypes()),
      '#default_value' => $this->getSetting('type'),
    ];
    $settings['color'] = array(
      '#type' => 'color',
      '#title' => $this->t('Color'),
      '#default_value' => $this->getSetting('color'),
      '#description' => $this->t('The color code.'),
    );
    $settings['height'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Height'),
      '#size' => 10,
      '#default_value' => $this->getSetting('height'),
      '#description' => $this->t('The height in pixels.'),
    ];
    $settings['width'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Width'),
      '#size' => 10,
      '#default_value' => $this->getSetting('width'),
      '#description' => $this->t('The width in pixels'),
    ];
    $settings['padding_top'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Padding-Top'),
      '#size' => 4,
      '#maxlength' => 4,
      '#default_value' => $this->getSetting('padding_top'),
      '#description' => $this->t('The top padding in pixels'),
    ];
    $settings['padding_right'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Padding-Right'),
      '#size' => 4,
      '#maxlength' => 4,
      '#default_value' => $this->getSetting('padding_right'),
      '#description' => $this->t('The right padding in pixels'),
    ];
    $settings['padding_bottom'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Padding-Bottom'),
      '#size' => 4,
      '#maxlength' => 4,
      '#default_value' => $this->getSetting('padding_bottom'),
      '#description' => $this->t('The bottom padding in pixels'),
    ];
    $settings['padding_left'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Padding-Left'),
      '#size' => 4,
      '#maxlength' => 4,
      '#default_value' => $this->getSetting('padding_left'),
      '#description' => $this->t('The left padding in pixels'),
    ];
    return $settings + parent::settingsForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary[] = t('Type: %type', array('%type' => $this->getSetting('type')));
    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];
    $generator = new BarcodeGenerator();
    foreach ($items as $delta => $item) {
      try {
        $barcode = $generator->getBarcodeObj(
          $this->getSetting('type'),
          $this->viewValue($item),
          $this->getSetting('width'),
          $this->getSetting('height'),
          $this->getSetting('color'),
          [
            $this->getSetting('padding-top'),
            $this->getSetting('padding-right'),
            $this->getSetting('padding-bottom'),
            $this->getSetting('padding-left'),
          ]
        );
        $svg = $barcode->getSvgCode();
        $elements[$delta] = [
          '#type' => 'inline_template',
          '#template' => "{{ svg|raw }}",
          '#context' => [
            'svg' => $svg,
          ],
        ];
      }
      catch (\Exception $e) {
        $elements[$delta] = [
          '#markup' => $this->t('Error: @error, given: @value', [
            '@error' => $e->getMessage(),
            '@value' => $this->viewValue($item),
          ]),
        ];
      }
    }
    return $elements;
  }

  /**
   * Generate the output appropriate for one field item.
   *
   * @param \Drupal\Core\Field\FieldItemInterface $item
   *   One field item.
   *
   * @return string
   *   The textual output generated.
   */
  protected function viewValue(FieldItemInterface $item) {
    return Html::escape($item->value);
  }

}