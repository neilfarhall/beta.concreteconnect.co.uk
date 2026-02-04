<?php

/**
 * @file
 * Contains \Drupal\body_inject\Controller\AutocompleteController.
 */

namespace Drupal\body_inject\Controller;

use Drupal\Component\Utility\Unicode;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\body_inject\ResultManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class AutocompleteController implements ContainerInjectionInterface {

  /**
   * The body_inject profile storage service.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $body_injectProfileStorage;

  /**
   * The result manager.
   *
   * @var \Drupal\body_inject\ResultManager
   */
  protected $resultManager;

  /**
   * The body_inject profile.
   *
   * @var \Drupal\body_inject\ProfileInterface
   */
  protected $body_injectProfile;

  /**
   * Constructs a EntityAutocompleteController object.
   *
   * @param ResultManager $resultManager
   *   The result service.
   * @param \Drupal\Core\Entity\EntityStorageInterface $body_inject_profile_storage
   *   The body_inject profile storage service.
   */
  public function __construct(EntityStorageInterface $body_inject_profile_storage, ResultManager $resultManager) {
    $this->body_injectProfileStorage = $body_inject_profile_storage;
    $this->resultManager = $resultManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.manager')->getStorage('body_inject_profile'),
      $container->get('body_inject.result_manager')
    );
  }

  /**
   * Menu callback for body_inject search autocompletion.
   *
   * Like other autocomplete functions, this function inspects the 'q' query
   * parameter for the string to use to search for suggestions.
   *
   * @param Request $request
   *   The request.
   * @param $body_inject_profile_id
   *   The body_inject profile id.
   * @return JsonResponse
   *   A JSON response containing the autocomplete suggestions.
   */
  public function autocomplete(Request $request, $body_inject_profile_id) {
    $this->body_injectProfile = $this->body_injectProfileStorage->load($body_inject_profile_id);
    $string = mb_strtolower($request->query->get('q'));

    $matches = $this->resultManager->getResults($this->body_injectProfile, $string);

    $json_object = new \stdClass();
    $json_object->matches = $matches;

    return new JsonResponse($json_object);
  }

}
