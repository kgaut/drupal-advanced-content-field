<?php

namespace Drupal\advanced_content_field\Plugin\Field\FieldType;

use Drupal\Component\Render\PlainTextOutput;
use Drupal\Component\Utility\Random;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\TypedData\DataDefinition;
use Drupal\Core\TypedData\DataReferenceTargetDefinition;
use Drupal\Core\TypedData\MapDataDefinition;

/**
 * Plugin implementation of the 'advanced_content' field type.
 *
 * @FieldType(
 *   id = "advanced_content",
 *   label = @Translation("Advanced Content"),
 *   description = @Translation("Meta field to replace the classic body field"),
 *   default_widget = "advanced_content_widget",
 *   default_formatter = "advanced_content_formatter"
 * )
 */
class AdvancedContentField extends FieldItemBase {

  public static function defaultFieldSettings() {
    return [
        'file_directory' => 'public://',
        'plugin_ids' => [],
      ] + parent::defaultFieldSettings();
  }

  public static function validateDirectory($element, FormStateInterface $form_state) {
    // Strip slashes from the beginning and end of $element['file_directory'].
    $value = trim($element['#value'], '\\/');
    $form_state->setValueForElement($element, $value);
  }

  public function fieldSettingsForm(array $form, FormStateInterface $form_state) {
    $field = $form_state->getFormObject()->getEntity();
    /** @var \Drupal\advanced_content_field\ACFManager $block_field_manager */
    $block_field_manager = \Drupal::service('advanced_content_field.manager');
    $definitions = $block_field_manager->getBlockDefinitions();
    $options = [];
    foreach ($definitions as $plugin_id => $definition) {
      $options[$plugin_id] = [
        ['category' => (string) $definition['category']],
        ['label' => $definition['admin_label'] . ' (' . $plugin_id . ')'],
        ['provider' => $definition['provider']],
      ];
    }

    $element = [];
    $settings = $this->getSettings();

    $element['file_directory'] = [
      '#type' => 'textfield',
      '#title' => t('File directory'),
      '#default_value' => $settings['file_directory'],
      '#description' => t('Optional subdirectory within the upload destination where files will be stored. Do not include preceding or trailing slashes.'),
      '#element_validate' => [[get_class($this), 'validateDirectory']],
      '#weight' => -1,
    ];

    $element['blocks'] = [
      '#type' => 'details',
      '#title' => $this->t('Blocks'),
      '#description' => $this->t('Please select available blocks.'),
      '#open' => $field->getSetting('plugin_ids') ? TRUE : FALSE,
    ];

    $default_value = $field->getSetting('plugin_ids') ?: array_keys($options);

    $element['blocks']['plugin_ids'] = [
      '#type' => 'tableselect',
      '#header' => [
        'Category',
        'Label/ID',
        'Provider',
      ],
      '#options' => $options,
      '#js_select' => TRUE,
      '#required' => TRUE,
      '#empty' => t('No blocks are available.'),
      '#parents' => ['settings', 'plugin_ids'],
      '#element_validate' => [[get_called_class(), 'validatePluginIds']],
      '#default_value' => array_combine($default_value, $default_value),
    ];
    return $element;
  }

  public function getUploadLocation($data = []) {
    return static::doGetUploadLocation($this->getSettings(), $data);
  }

  public static function doGetUploadLocation(array $settings, $data = []) {
    $destination = trim($settings['file_directory'], '/');
    $destination = PlainTextOutput::renderFromHtml(\Drupal::token()->replace($destination, $data));
    return $destination;
  }

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {

    $properties['type'] = DataDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Type'));

    $properties['title'] = DataDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Title'));

    $properties['image'] = DataReferenceTargetDefinition::create('integer')
      ->setLabel(new TranslatableMarkup('Image file ID'));

    $properties['template'] = DataDefinition::create('string')
      ->setLabel(t('Template'));

    $properties['body'] = DataDefinition::create('string')
      ->setLabel(t('Content'));

    $properties['body_format'] = DataDefinition::create('filter_format')
      ->setLabel(t('Content format'));

    $properties['block_plugin'] = DataDefinition::create('string')
      ->setLabel(t('Plugin ID'));

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    $schema = [
      'columns' => [],
      'indexes' => ['block_plugin' => ['block_plugin']],
    ];

    $schema['columns']['type'] = [
      'type' => 'varchar_ascii',
      'length' => 50,
      'not null' => FALSE,
    ];

    $schema['columns']['title'] = [
      'type' => 'varchar_ascii',
      'length' => 255,
      'not null' => FALSE,
    ];

    $schema['columns']['template'] = [
      'type' => 'varchar_ascii',
      'length' => 55,
      'not null' => FALSE,
    ];

    $schema['columns']['image'] = [
      'description' => 'Image file fid',
      'type' => 'int',
      'unsigned' => TRUE,
      'not null' => FALSE
    ];

    $schema['columns']['body'] = [
      'type' => 'text',
      'size' => 'big',
      'not null' => FALSE,
    ];

    $schema['columns']['body_format'] = [
      'type' => 'varchar_ascii',
      'length' => 255,
      'not null' => FALSE,
    ];

    $schema['columns']['block_plugin'] = [
      'description' => 'The block plugin id',
      'type' => 'varchar',
      'length' => 255,
    ];

    return $schema;
  }

  /**
   * {@inheritdoc}
   */
  public static function generateSampleValue(FieldDefinitionInterface $field_definition) {
    $random = new Random();
    $values['title'] = $random->word(mt_rand(1, $field_definition->getSetting('max_length')));
    return $values;
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    $title = $this->get('title')->getValue();
    $body = $this->get('body')->getValue();
    $image = $this->get('image')->getValue();
    return ($title === NULL || $title === '') && ($body === NULL || $body === '')  && ($image === NULL || !is_numeric($image)) ;
  }

  /**
   * Validates plugin_ids table select element.
   */
  public static function validatePluginIds(array &$element, FormStateInterface $form_state, &$complete_form) {
    $value = array_filter($element['#value']);
    if (array_keys($element['#options']) == array_keys($value)) {
      $form_state->setValueForElement($element, []);
    }
    else {
      $form_state->setValueForElement($element, $value);
    }
    return $element;
  }
}
