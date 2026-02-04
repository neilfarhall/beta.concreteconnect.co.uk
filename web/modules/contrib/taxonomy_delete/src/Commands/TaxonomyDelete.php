<?php

namespace Drupal\taxonomy_delete\Commands;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drush\Commands\DrushCommands;
use Drush\Exceptions\UserAbortException;

/**
 * Class TaxonomyDelete. The base class for Drush commands.
 */
class TaxonomyDelete extends DrushCommands {

  /**
   * Returns the entity_type.manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Returns the logger.factory service.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $loggerChannel;

  /**
   * Constructs a TaxonomyDelete object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Provides an interface for entity type managers.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_channel
   *   Logger channel factory interface.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, LoggerChannelFactoryInterface $logger_channel) {
    parent::__construct();
    $this->entityTypeManager = $entity_type_manager;
    $this->loggerChannel = $logger_channel;
  }

  /**
   * Remove taxonomy terms from provided vocabulary.
   *
   * @param string $vid
   *   Vocabulary machine name.
   *
   * @throws \Exception
   *
   * @usage drush taxonomy-delete:term-delete tags
   *   Delete all taxonomy terms from the 'tags' vocabulary.
   *
   * @command taxonomy-delete:term-delete
   * @aliases tdel
   */
  public function deleteTerms($vid = '') {
    if (empty($vid)) {
      throw new \Exception('Vocabulary name is not specified.');
    }

    if (!$this->io()->confirm(dt('Are you sure you want to delete all taxonomy terms from @vid?', ['@vid' => $vid]))) {
      throw new UserAbortException();
    }

    $query = $this->entityTypeManager->getStorage('taxonomy_term')->getQuery();
    $query->condition('vid', explode(',', $vid), 'IN');
    $query->accessCheck(FALSE);
    $query->sort('tid');

    if ($tids = $query->execute()) {
      $batch = ['operations' => []];

      foreach ($tids as $tid) {
        $batch['operations'][] = ['\Drupal\taxonomy_delete\BatchService::deleteTerm', [$tid]];
      }

      batch_set($batch);
      drush_backend_batch_process();

      $this->output()->writeln(dt('All selected taxonomy terms have been removed.'));
      $this->loggerChannel->get('taxonomy_delete')->info('All selected taxonomy terms have been removed from @vocabularies.', [
        '@vocabularies' => $vid,
      ]);
    }
    else {
      $this->output()->writeln(dt('No taxonomy terms found.'));
    }
  }

}
