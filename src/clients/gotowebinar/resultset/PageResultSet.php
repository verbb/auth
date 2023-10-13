<?php

namespace verbb\auth\clients\gotowebinar\resultset;

use ArrayObject;
use InvalidArgumentException;
use Traversable;

/**
 * Class PageResultSet
 */
class PageResultSet implements ResultSetInterface
{
    /**
     * Data stored in the page.
     *
     * @var ArrayObject
     */
    private ArrayObject $data;

    /**
     * Page information
     *
     * @var ArrayObject
     */
    private ArrayObject $page;

    /**
     * @param array|null $response
     * @param string $type
     * @throws InvalidArgumentException
     */
    public function __construct(?array $response, string $type)
    {
        $response = is_array($response) ? $response : [];
        $this->data = new ArrayObject($response['_embedded'][$type] ?? []);
        $this->page = new ArrayObject($response['page'] ?? []);
    }

    /**
     * {@inheritDoc}
     * @see \JsonSerializable::jsonSerialize()
     */
    public function jsonSerialize(): mixed
    {
        return $this->data->getArrayCopy();
    }
    
    /**
     * @return ArrayObject
     */
    public function getData(): ArrayObject
    {
        return $this->data;
    }

    /**
     * @return ArrayObject
     */
    public function getPage(): ArrayObject
    {
        return $this->page;
    }

    /**
     * {@inheritDoc}
     * @see \Countable::count()
     */
    public function count(): int
    {
        return $this->data->count();
    }

    /**
     * {@inheritDoc}
     * @see \ArrayAccess::offsetExists()
     */
    public function offsetExists($offset): bool
    {
        return $this->data->offsetExists($offset);
    }

    /**
     * {@inheritDoc}
     * @see \ArrayAccess::offsetGet()
     */
    public function offsetGet($offset): mixed
    {
        return $this->data->offsetGet($offset);
    }

    /**
     * {@inheritDoc}
     * @see \ArrayAccess::offsetSet()
     */
    public function offsetSet($offset, $value): void
    {
        $this->data->offsetSet($offset, $value);
    }

    /**
     * {@inheritDoc}
     * @see \ArrayAccess::offsetUnset()
     */
    public function offsetUnset($offset): void
    {
        $this->data->offsetUnset($offset);
    }

    /**
     * {@inheritDoc}
     * @see \Serializable::serialize()
     */
    public function serialize(): ?string
    {
        return (new ArrayObject([
            'data' => $this->data->getArrayCopy(),
            'page' => $this->page->getArrayCopy()
        ]))->serialize();
    }

    /**
     * {@inheritDoc}
     * @see \Serializable::unserialize()
     */
    public function unserialize($serialized): void
    {
        $response = new ArrayObject();
        $response->unserialize($serialized);
        $this->data = new ArrayObject($response['data']);
        $this->page = new ArrayObject($response['page']);
    }

    /**
     * {@inheritDoc}
     * @see \IteratorAggregate::getIterator()
     */
    public function getIterator(): Traversable
    {
        return $this->data->getIterator();
    }
}
