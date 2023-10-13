<?php
namespace verbb\auth\clients\trello\provider;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;

class TrelloUser implements ResourceOwnerInterface
{
    private mixed $id;
    private mixed $email;
    private mixed $name;
    private mixed $photo;

    public function __construct(array $response)
    {
        $this->id = $response['id'];
        $this->email = $response['email'];
        $this->name = $response['fullName'];
        $this->photo = $response['avatarUrl'];
    }

    public function getId()
    {
        return $this->id;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getPhoto()
    {
        return $this->photo;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'name' => $this->name,
            'photo' => $this->photo,
        ];
    }
}