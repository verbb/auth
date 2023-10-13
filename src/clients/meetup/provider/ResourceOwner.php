<?php

declare(strict_types=1);

namespace verbb\auth\clients\meetup\provider;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;

class ResourceOwner implements ResourceOwnerInterface
{
    private int $id;
    private string $link;
    private string $name;
    private array $birthday;
    private array $photo;
    private string $status;
    private int $joined;
    private int $visited;
    private string $lang;
    private string $country;
    private string $city;
    private float $lat;
    private float $lon;
    private array $topics;
    private array $otherServices;
    private array $self;


    /**
     * Resource owner factory method.
     *
     * $data must be the response of `Meetup::getResourceOwnerDetailsUrl`.
     */
    public static function fromArray(array $data): ResourceOwner
    {
        return new self(
            $data['id'] ?? 0,
            $data['link'] ?? '',
            $data['name'] ?? '',
            $data['birthday'] ?? [],
            $data['photo'] ?? [],
            $data['status'] ?? '',
            $data['joined'] ?? 0,
            $data['visited'] ?? 0,
            $data['lang'] ?? '',
            $data['country'] ?? '',
            $data['city'] ?? '',
            $data['lat'] ?? 0.0,
            $data['lon'] ?? 0.0,
            $data['topics'] ?? [],
            $data['other_services'] ?? $data['otherServices'] ?? [],
            $data['self'] ?? []
        );
    }


    public function __construct(
        int $id,
        string $link,
        string $name,
        array $birthday,
        array $photo,
        string $status,
        int $joined,
        int $visited,
        string $lang,
        string $country,
        string $city,
        float $lat,
        float $lon,
        array $topics,
        array $otherServices,
        array $self
    ) {
        $this->id = $id;
        $this->link = $link;
        $this->name = $name;
        $this->birthday = $birthday;
        $this->photo = $photo;
        $this->status = $status;
        $this->joined = $joined;
        $this->visited = $visited;
        $this->lang = $lang;
        $this->country = $country;
        $this->city = $city;
        $this->lat = $lat;
        $this->lon = $lon;
        $this->topics = $topics;
        $this->otherServices = $otherServices;
        $this->self = $self;
    }


    public function getId(): int
    {
        return $this->id;
    }

    public function getLink(): string
    {
        return $this->link;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getBirthday(): array
    {
        return $this->birthday;
    }

    public function getPhoto(): array
    {
        return $this->photo;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getJoined(): int
    {
        return $this->joined;
    }

    public function getVisited(): int
    {
        return $this->visited;
    }

    public function getLang(): string
    {
        return $this->lang;
    }

    public function getCountry(): string
    {
        return $this->country;
    }

    public function getCity(): string
    {
        return $this->city;
    }

    public function getLat(): float
    {
        return $this->lat;
    }

    public function getLon(): float
    {
        return $this->lon;
    }

    public function getTopics(): array
    {
        return $this->topics;
    }

    public function getOtherServices(): array
    {
        return $this->otherServices;
    }

    public function getSelf(): array
    {
        return $this->self;
    }


    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'link' => $this->link,
            'name' => $this->name,
            'birthday' => $this->birthday,
            'photo' => $this->photo,
            'status' => $this->status,
            'joined' => $this->joined,
            'visited' => $this->visited,
            'lang' => $this->lang,
            'country' => $this->country,
            'city' => $this->city,
            'lat' => $this->lat,
            'lon' => $this->lon,
            'topics' => $this->topics,
            'otherServices' => $this->otherServices,
            'self' => $this->self,
        ];
    }
}
