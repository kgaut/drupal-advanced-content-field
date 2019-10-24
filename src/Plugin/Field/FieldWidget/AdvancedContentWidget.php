<?php

namespace Drupal\advanced_content_field\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'advanced_content_widget' widget.
 *
 * @FieldWidget(
 *   id = "advanced_content_widget",
 *   module = "advanced_content_field",
 *   label = @Translation("AdvancedContent"),
 *   field_types = {
 *     "advanced_content"
 *   }
 * )
 */
class AdvancedContentWidget extends WidgetBase {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element['title'] = [
      '#type' => 'textfield',
      '#title' => t('Title'),
      '#default_value' => isset($items[$delta]->title) ? $items[$delta]->title : NULL,
      '#size' => 120,
      '#maxlength' => 255,
    ];

    $element['body'] = [
      '#type' => 'text_format',
      '#format' => $items[$delta]->body_format ?? 'full_html',
      '#default_value' => $items[$delta]->body ?? '',
      '#title' => t('Text'),
      '#rows' => 5,
      '#attached' => [
        'library' => ['text/drupal.text'],
      ],
    ];

    return $element;
  }

  public function  massageFormValues(array $values, array $form, FormStateInterface $form_state) {
    foreach ($values as &$value) {
      $body = $value['body'];
      $value['body_format'] = $body['format'];
      $value['body'] = $body['value'];
    }
    return parent::massageFormValues($values, $form, $form_state);
  }

}
