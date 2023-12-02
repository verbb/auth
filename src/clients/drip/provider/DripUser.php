<?php

namespace verbb\auth\clients\drip\provider;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use League\OAuth2\Client\Tool\ArrayAccessorTrait;

class DripUser implements ResourceOwnerInterface
{
  use ArrayAccessorTrait;

  /**
   * @var array
   */
  protected array $response = [];

  /**
   * @param array $response
   */
  public function __construct(array $response)
  {
    $this->response = $response;
  }

  public function getId()
  {
    return $this->getValueByKey($this->response, '');
  }

  public function getEmail()
  {
    return $this->getValueByKey($this->response, 'users.email');
  }

  public function getName()
  {
    return $this->getValueByKey($this->response, 'users.name');
  }

  public function toArray(): array
  {
    return $this->response;
  }
}
