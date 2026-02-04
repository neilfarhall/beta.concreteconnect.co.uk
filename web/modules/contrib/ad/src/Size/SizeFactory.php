<?php

namespace Drupal\ad\Size;

use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * The AD size factory.
 *
 * @internal
 */
class SizeFactory {

  /**
   * All the available AD sizes.
   *
   * @var \Drupal\ad\Size\SizeInterface[]
   */
  protected array $sizes;

  /**
   * Returns the specified AD size.
   *
   * @param string $id
   *   The AD size machine name.
   *
   * @return \Drupal\ad\Size\SizeInterface
   *   An AD size.
   */
  public function get(string $id): SizeInterface {
    $sizes = $this->getAll();
    if (isset($sizes[$id])) {
      return $sizes[$id];
    }
    throw new \InvalidArgumentException($id);
  }

  /**
   * Returns all available AD sizes.
   *
   * @return \Drupal\ad\Size\SizeInterface[]
   *   An array of AD sizes.
   */
  public function getAll(): array {
    if (!isset($this->sizes)) {
      $sizes = [
        'rectangle' => new TranslatableMarkup('Rectangle'),
        'skyscraper' => new TranslatableMarkup('Skyscraper'),
        'leaderboard' => new TranslatableMarkup('Leaderboard'),
        'large_leaderboard' => new TranslatableMarkup('Large Leaderboard'),
        'medium_rectangle' => new TranslatableMarkup('Medium Rectangle'),
        'billboard' => new TranslatableMarkup('Billboard'),
      ];
      foreach ($sizes as $id => $label) {
        $this->sizes[$id] = new Size([
          'id' => $id,
          'label' => $label,
        ]);
      }
    }
    return $this->sizes;
  }

}
