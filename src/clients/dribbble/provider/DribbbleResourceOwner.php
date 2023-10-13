<?php
namespace verbb\auth\clients\dribbble\provider;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use League\OAuth2\Client\Tool\ArrayAccessorTrait;

class DribbbleResourceOwner implements ResourceOwnerInterface
{
    use ArrayAccessorTrait;

    /**
     * Raw response
     *
     * @var
     */
    protected $response;

    /**
     * Creates new resource owner.
     *
     * @param $response
     */
    public function __construct($response)
    {
        $this->response = $response;
    }

    /**
     * Get resource owner id
     *
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->getValueByKey($this->response, 'id');
    }

    /**
     * Return all of the details available as an array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return $this->response;
    }

    /**
     * Get resource owner name
     * @return string
     */
    public function getName(): string
    {
        return $this->getValueByKey($this->response, 'name');
    }

    /**
     * Get resource owner username
     * @return string
     */
    public function getUsername(): string
    {
        return $this->getValueByKey($this->response, 'username');
    }

    /**
     * Get resource owner html url
     * @return string
     */
    public function getHtmlUrl(): string
    {
        return $this->getValueByKey($this->response, 'html_url');
    }

    /**
     * Get resource owner avatar url
     * @return string
     */
    public function getAvatarUrl(): string
    {
        return $this->getValueByKey($this->response, 'avatar_url');
    }

    /**
     * Get resource owner bio
     * @return string
     */
    public function getBio(): string
    {
        return $this->getValueByKey($this->response, 'bio');
    }

    /**
     * Get resource owner location
     * @return string
     */
    public function getLocation(): string
    {
        return $this->getValueByKey($this->response, 'location');
    }

    /**
     * Get resource owner links
     * @return array
     */
    public function getLinks(): array
    {
        return $this->getValueByKey($this->response, 'links');
    }

    /**
     * Get resource bucket count
     * @return int
     */
    public function getBucketCount(): int
    {
        return $this->getValueByKey($this->response, 'buckets_count');
    }

    /**
     * Get resource comments received count
     * @return int
     */
    public function getCommentsReceivedCount(): int
    {
        return $this->getValueByKey($this->response, 'comments_received_count');
    }

    /**
     * Get resource followers count
     * @return int
     */
    public function getFollowersCount(): int
    {
        return $this->getValueByKey($this->response, 'followers_count');
    }

    /**
     * Get resource followings count
     * @return int
     */
    public function getFollowingsCount(): int
    {
        return $this->getValueByKey($this->response, 'followings_count');
    }

    /**
     * Get resource likes count
     * @return int
     */
    public function getLikesCount(): int
    {
        return $this->getValueByKey($this->response, 'likes_count');
    }

    /**
     * Get resource likes received count
     * @return int
     */
    public function getLikesReceivedCount(): int
    {
        return $this->getValueByKey($this->response, 'likes_received_count');
    }

    /**
     * Get resource projects count
     * @return int
     */
    public function getProjectsCount(): int
    {
        return $this->getValueByKey($this->response, 'projects_count');
    }

    /**
     * Get resource rebounds count
     * @return int
     */
    public function getReboundsReceivedCount(): int
    {
        return $this->getValueByKey($this->response, 'rebounds_received_count');
    }

    /**
     * Get resource shots count
     * @return int
     */
    public function getShotsCount(): int
    {
        return $this->getValueByKey($this->response, 'shots_count');
    }

    /**
     * Get resource teams count
     * @return int
     */
    public function getTeamsCount(): int
    {
        return $this->getValueByKey($this->response, 'teams_count');
    }

    /**
     * Can resource owner upload shots
     * @return bool
     */
    public function canUploadShot(): bool
    {
        return ($this->getValueByKey($this->response, 'can_upload_shot') == 'true');
    }

    /**
     * can resource owner type
     * @return string
     */
    public function getType(): string
    {
        return $this->getValueByKey($this->response, 'type');
    }

    /**
     * Is resource owner a Pro account
     * @return bool
     */
    public function isPro(): bool
    {
        return ($this->getValueByKey($this->response, 'pro') == 'true');
    }

    /**
     * Get resource owner buckets url
     * @return string
     */
    public function getBucketsUrl(): string
    {
        return $this->getValueByKey($this->response, 'buckets_url');
    }

    /**
     * Get resource owner followers url
     * @return string
     */
    public function getFollowersUrl(): string
    {
        return $this->getValueByKey($this->response, 'followers_url');
    }

    /**
     * Get resource owners following url
     * @return string
     */
    public function getFollowingUrl(): string
    {
        return $this->getValueByKey($this->response, 'following_url');
    }

    /**
     * Get resource owners likes url
     * @return string
     */
    public function getLikesUrl(): string
    {
        return $this->getValueByKey($this->response, 'likes_url');
    }

    /**
     * Get resource owner projects url
     * @return string
     */
    public function getProjectsUrl(): string
    {
        return $this->getValueByKey($this->response, 'projects_url');
    }

    /**
     * Get resource owner shots url
     * @return string
     */
    public function getShotsUrl(): string
    {
        return $this->getValueByKey($this->response, 'shots_url');
    }

    /**
     * Get resource owner teams url
     * @return string
     */
    public function getTeamsUrl(): string
    {
        return $this->getValueByKey($this->response, 'teams_url');
    }

    /**
     * Get resource created date
     * @return string
     */
    public function getCreated(): string
    {
        return $this->getValueByKey($this->response, 'created_at');
    }

    /**
     * Get resource updated date
     * @return string
     */
    public function getUpdated(): string
    {
        return $this->getValueByKey($this->response, 'updated_at');
    }
}
