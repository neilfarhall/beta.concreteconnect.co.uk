<?php

namespace Drupal\ad\Size;

/**
 * An AD size.
 *
 * @internal
 */
class Size implements SizeInterface {

  /**
   * The AD size ID.
   *
   * @var string
   */
  protected string $id;

  /**
   * The AD size label.
   *
   * @var string
   */
  protected string $label;

  /**
   * Size constructor.
   *
   * @param array $values
   *   The initial values.
   */
  public function __construct(array $values) {
    $this->id = $values['id'];
    $this->label = $values['label'];
  }

  /**
   * {@inheritdoc}
   */
  public function getId(): string {
    return $this->id;
  }

  /**
   * {@inheritdoc}
   */
  public function getLabel(): string {
    return $this->label;
  }

}
