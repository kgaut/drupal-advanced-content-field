<?php

namespace Drupal\advanced_content_field;
use Drupal\Core\Block\BlockManagerInterface;
use Drupal\Core\Plugin\Context\ContextRepositoryInterface;

/**
 * Class ACFManager.
 */
class ACFManager {

  /**
   * Drupal\Core\Block\BlockManagerInterface definition.
   *
   * @var \Drupal\Core\Block\BlockManagerInterface
   */
  protected $blockManager;

  /**
   * Drupal\Core\Plugin\Context\ContextRepositoryInterface definition.
   *
   * @var \Drupal\Core\Plugin\Context\ContextRepositoryInterface
   */
  protected $contextRepository;

  /**
   * Constructs a new BlockManager object.
   */
  public function __construct(BlockManagerInterface $plugin_manager_block, ContextRepositoryInterface $context_repository) {
    $this->blockManager = $plugin_manager_block;
    $this->contextRepository = $context_repository;
  }

  public function getFieldTypes() {
    return [
      'block' => 'Block',
      'image' => 'Image',
      'text' => 'Text',
      'image_and_text' => 'Image and text',
    ];
  }

  public function getFieldLayouts() {
    return [
      'image_above_text' => 'Image above text',
      'image_below_text' => 'Image below text',
      'image_left_text_right' => 'Image left, text right',
      'image_right_text_left' => 'Image right, text left',
    ];
  }

  public function getBlockDefinitions() {
    $definitions = $this->blockManager->getDefinitionsForContexts($this->contextRepository->getAvailableContexts());
    return $this->blockManager->getSortedDefinitions($definitions);
  }

}
