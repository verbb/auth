<?php
namespace verbb\auth\clients\stackexchange\provider;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use League\OAuth2\Client\Tool\ArrayAccessorTrait;

class StackExchangeResourceOwner implements ResourceOwnerInterface
{
    use ArrayAccessorTrait;

    public function __construct(array $response = [])
    {
        $this->items = $this->getValueByKey($response, 'items', []);
    }

    /**
     * @return array
     */
    public function getId(): array
    {
        $items = $this->items;

        $ids = [];
        foreach ($items as $item) {
            $ids[] = $this->getValueByKey($item, 'user_id');
        }

        return $ids;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return $this->items;
    }

    /**
     * @var array
     */
    protected mixed $items = [];
}
