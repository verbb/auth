<?php

declare(strict_types=1);

namespace verbb\auth\clients\deezer\provider;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use DateTimeImmutable;
use DateTimeInterface;

use function in_array;

class DeezerResourceOwner implements ResourceOwnerInterface
{
    protected array $data = [];

    public function __construct(array $response)
    {
        $this->data = $response;
    }

    public function getBirthday(): ?DateTimeInterface
    {
        if (null === $this->data['birthday'] || '0000-00-00' === $this->data['birthday']) {
            return null;
        }

        $date = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $this->data['birthday'] . '00:00:00');
        if (false === $date) {
            return null;
        }

        return $date;
    }

    public function getCountry(): ?string
    {
        return $this->data['country'] ?? null;
    }

    public function getEmail(): ?string
    {
        return $this->data['email'] ?? null;
    }

    public function getExplicitContentLevel(): ?string
    {
        return $this->data['explicit_content_level'] ?? null;
    }

    public function getExplicitContentLevelsAvailable(): array
    {
        return $this->data['explicit_content_levels_available'] ?? [];
    }

    public function getFirstname(): ?string
    {
        return $this->data['firstname'] ?? null;
    }

    public function getGender(): ?string
    {
        if (!in_array($this->data['gender'], ['F', 'M'], true)) {
            return null;
        }

        return $this->data['gender'];
    }

    public function getId(): string
    {
        return (string) $this->data['id'];
    }

    public function getInscriptionDate(): ?DateTimeInterface
    {
        if (null === $this->data['inscription_date']) {
            return null;
        }

        $date = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $this->data['inscription_date'] . '00:00:00');
        if (false === $date) {
            return null;
        }

        return $date;
    }

    public function isKid(): bool
    {
        return $this->data['is_kid'];
    }

    public function getLang(): string
    {
        return $this->data['lang'];
    }

    public function getLastname(): ?string
    {
        return $this->data['lastname'] ?? null;
    }

    public function getLink(): ?string
    {
        return $this->data['link'] ?? null;
    }

    public function getName(): ?string
    {
        return $this->data['name'] ?? null;
    }

    public function getPicture(): ?string
    {
        return $this->data['picture'] ?? null;
    }

    public function getPictureSmall(): ?string
    {
        return $this->data['picture_small'] ?? null;
    }

    public function getPictureMedium(): ?string
    {
        return $this->data['picture_medium'] ?? null;
    }

    public function getPictureBig(): ?string
    {
        return $this->data['picture_big'] ?? null;
    }

    public function getPictureXl(): ?string
    {
        return $this->data['picture_xl'] ?? null;
    }

    public function getStatus(): int
    {
        return $this->data['status'];
    }

    public function getTracklist(): string
    {
        return $this->data['tracklist'];
    }

    /**
     * Return all of the owner details available as an array.
     */
    public function toArray(): array
    {
        return $this->data;
    }
}
