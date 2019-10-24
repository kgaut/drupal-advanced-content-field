<?php

namespace Drupal\advanced_content_field\Plugin\Field\FieldType;

use Drupal\Component\Utility\Random;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\TypedData\DataDefinition;

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

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties['title'] = DataDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Title'));

    $properties['template'] = DataDefinition::create('string')
      ->setLabel(t('Template'));

    $properties['body'] = DataDefinition::create('string')
      ->setLabel(t('Content'));

    $properties['body_format'] = DataDefinition::create('filter_format')
      ->setLabel(t('Content format'));

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    $schema = [
      'columns' => [],
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
    return ($title === NULL || $title === '') && ($body === NULL || $body === '') ;
  }

}
