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
    public function getId()
    {
        return $this->getValueByKey($this->response, 'id');
    }

    /**
     * Return all of the details available as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->response;
    }

    /**
     * Get resource owner name
     * @return string
     */
    public function getName()
    {
        return $this->getValueByKey($this->response, 'name');
    }

    /**
     * Get resource owner username
     * @return string
     */
    public function getUsername()
    {
        return $this->getValueByKey($this->response, 'username');
    }

    /**
     * Get resource owner html url
     * @return string
     */
    public function getHtmlUrl()
    {
        return $this->getValueByKey($this->response, 'html_url');
    }

    /**
     * Get resource owner avatar url
     * @return string
     */
    public function getAvatarUrl()
    {
        return $this->getValueByKey($this->response, 'avatar_url');
    }

    /**
     * Get resource owner bio
     * @return string
     */
    public function getBio()
    {
        return $this->getValueByKey($this->response, 'bio');
    }

    /**
     * Get resource owner location
     * @return string
     */
    public function getLocation()
    {
        return $this->getValueByKey($this->response, 'location');
    }

    /**
     * Get resource owner links
     * @return array
     */
    public function getLinks()
    {
        return $this->getValueByKey($this->response, 'links');
    }

    /**
     * Get resource bucket count
     * @return int
     */
    public function getBucketCount()
    {
        return $this->getValueByKey($this->response, 'buckets_count');
    }

    /**
     * Get resource comments received count
     * @return int
     */
    public function getCommentsReceivedCount()
    {
        return $this->getValueByKey($this->response, 'comments_received_count');
    }

    /**
     * Get resource followers count
     * @return int
     */
    public function getFollowersCount()
    {
        return $this->getValueByKey($this->response, 'followers_count');
    }

    /**
     * Get resource followings count
     * @return int
     */
    public function getFollowingsCount()
    {
        return $this->getValueByKey($this->response, 'followings_count');
    }

    /**
     * Get resource likes count
     * @return int
     */
    public function getLikesCount()
    {
        return $this->getValueByKey($this->response, 'likes_count');
    }

    /**
     * Get resource likes received count
     * @return int
     */
    public function getLikesReceivedCount()
    {
        return $this->getValueByKey($this->response, 'likes_received_count');
    }

    /**
     * Get resource projects count
     * @return int
     */
    public function getProjectsCount()
    {
        return $this->getValueByKey($this->response, 'projects_count');
    }

    /**
     * Get resource rebounds count
     * @return int
     */
    public function getReboundsReceivedCount()
    {
        return $this->getValueByKey($this->response, 'rebounds_received_count');
    }

    /**
     * Get resource shots count
     * @return int
     */
    public function getShotsCount()
    {
        return $this->getValueByKey($this->response, 'shots_count');
    }

    /**
     * Get resource teams count
     * @return int
     */
    public function getTeamsCount()
    {
        return $this->getValueByKey($this->response, 'teams_count');
    }

    /**
     * Can resource owner upload shots
     * @return bool
     */
    public function canUploadShot()
    {
        return ($this->getValueByKey($this->response, 'can_upload_shot') == 'true');
    }

    /**
     * can resource owner type
     * @return string
     */
    public function getType()
    {
        return $this->getValueByKey($this->response, 'type');
    }

    /**
     * Is resource owner a Pro account
     * @return bool
     */
    public function isPro()
    {
        return ($this->getValueByKey($this->response, 'pro') == 'true');
    }

    /**
     * Get resource owner buckets url
     * @return string
     */
    public function getBucketsUrl()
    {
        return $this->getValueByKey($this->response, 'buckets_url');
    }

    /**
     * Get resource owner followers url
     * @return string
     */
    public function getFollowersUrl()
    {
        return $this->getValueByKey($this->response, 'followers_url');
    }

    /**
     * Get resource owners following url
     * @return string
     */
    public function getFollowingUrl()
    {
        return $this->getValueByKey($this->response, 'following_url');
    }

    /**
     * Get resource owners likes url
     * @return string
     */
    public function getLikesUrl()
    {
        return $this->getValueByKey($this->response, 'likes_url');
    }

    /**
     * Get resource owner projects url
     * @return string
     */
    public function getProjectsUrl()
    {
        return $this->getValueByKey($this->response, 'projects_url');
    }

    /**
     * Get resource owner shots url
     * @return string
     */
    public function getShotsUrl()
    {
        return $this->getValueByKey($this->response, 'shots_url');
    }

    /**
     * Get resource owner teams url
     * @return string
     */
    public function getTeamsUrl()
    {
        return $this->getValueByKey($this->response, 'teams_url');
    }

    /**
     * Get resource created date
     * @return string
     */
    public function getCreated()
    {
        return $this->getValueByKey($this->response, 'created_at');
    }

    /**
     * Get resource updated date
     * @return string
     */
    public function getUpdated()
    {
        return $this->getValueByKey($this->response, 'updated_at');
    }
}
