<?php

namespace Drupal\advanced_content_field\Plugin\Field\FieldWidget;

use Drupal\advanced_content_field\ACFManager;
use Drupal\Core\Block\BlockManagerInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

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
class AdvancedContentWidget extends WidgetBase implements ContainerFactoryPluginInterface {

  /** @var \Drupal\advanced_content_field\ACFManager */
  protected $manager;

  public function setManager(ACFManager $manager) {
    $this->manager = $manager;
  }

  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $instance = new static(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['third_party_settings']
    );
    $instance->setManager($container->get('advanced_content_field.manager'));

    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $field_name = $this->fieldDefinition->getName();
    $field_type_selector = ':input[name="' . $field_name .'[' . $delta .'][type]"]';
    $element['type'] = [
      '#type' => 'select',
      '#title' => 'Type',
      '#options' => $this->manager->getFieldTypes(),
      '#required' => TRUE,
      '#default_value' => $items[$delta]->type ?? NULL,
    ];

    $element['layout'] = [
      '#type' => 'select',
      '#title' => 'Layout',
      '#options' => $this->manager->getFieldLayouts(),
      '#required' => TRUE,
      '#default_value' => $items[$delta]->layout ?? NULL,
    ];

    $element['title'] = [
      '#type' => 'textfield',
      '#title' => t('Title'),
      '#default_value' => isset($items[$delta]->title) ? $items[$delta]->title : NULL,
      '#size' => 120,
      '#maxlength' => 255,
    ];

    $element['block_plugin'] = $this->getBlockFormElement($items, $delta, $element, $form, $form_state);

    $element['block_plugin']['#states'] = [
      'visible' => [$field_type_selector => ['value' => 'block']],
    ];

    $element['image'] = [
      '#title' => 'Image',
      '#type' => 'managed_file',
      '#default_value' => isset($items[$delta]->image) ? [$items[$delta]->image] : NULL,
      '#upload_location'  => $items[$delta]->getUploadLocation(),
      '#multiple' => FALSE,
      '#description' => t('Allowed extensions: gif png jpg jpeg'),
      '#upload_validators' => [
        'file_validate_is_image' => [],
        'file_validate_extensions' => ['gif png jpg jpeg'],
      ],
      '#states' => [
        //'visible' => [$field_type_selector => ['value' => 'image']],
      ]
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
      '#states' => [
        'visible' => [$field_type_selector => [
          ['value' => 'text'],
          ['value' => 'image_and_text'],
        ]],
      ]
    ];

    return $element;
  }

  public function getBlockFormElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $item =& $items[$delta];
    $plugin_ids = $this->fieldDefinition->getSetting('plugin_ids');
    $options = [];
    $block_field_manager = \Drupal::service('advanced_content_field.manager');
    $definitions = $block_field_manager->getBlockDefinitions();
    foreach ($definitions as $id => $definition) {
      // If allowed plugin ids are set then check that this block should be
      // included.
      if ($plugin_ids && !isset($plugin_ids[$id])) {
        // Remove the definition, so that we have an accurate list of allowed
        // blocks definitions.
        unset($definitions[$id]);
        continue;
      }
      $category = (string) $definition['category'];
      $options[$category][$id] = $definition['admin_label'];
    }

    // Make sure the plugin id is allowed, if not clear all settings.
    if ($item->plugin_id && !isset($definitions[$item->plugin_id])) {
      $item->plugin_id = '';
      $item->setting = [];
    }

    $element['block_plugin'] = [
      '#type' => 'select',
      '#title' => $this->t('Block'),
      '#options' => $options,
      '#empty_option' => $this->t('- None -'),
      '#default_value' => $item->plugin_id,
      '#required' => $element['#required'],
    ];

    return $element['block_plugin'];
  }

  public function  massageFormValues(array $values, array $form, FormStateInterface $form_state) {
    foreach ($values as &$value) {
      $value['image'] = \count($value['image']) === 1 ? array_pop($value['image']) : NULL;
      $body = $value['body'];
      $value['body_format'] = $body['format'];
      $value['body'] = $body['value'];
    }
    return parent::massageFormValues($values, $form, $form_state);
  }

}
