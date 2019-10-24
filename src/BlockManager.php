<?php

namespace Drupal\advanced_content_field;
use Drupal\Core\Block\BlockManagerInterface;
use Drupal\Core\Plugin\Context\ContextRepositoryInterface;

/**
 * Class BlockManager.
 */
class BlockManager {

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

  public function getBlockDefinitions() {
    $definitions = $this->blockManager->getDefinitionsForContexts($this->contextRepository->getAvailableContexts());
    return $this->blockManager->getSortedDefinitions($definitions);
  }

}
