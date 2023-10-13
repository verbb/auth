<?php namespace verbb\auth\clients\linkedin\provider;

use League\OAuth2\Client\Provider\GenericResourceOwner;
use League\OAuth2\Client\Tool\ArrayAccessorTrait;

/**
 * @property array $response
 * @property string $uid
 */
class LinkedInResourceOwner extends GenericResourceOwner
{

    use ArrayAccessorTrait;

    /**
     * Raw response
     *
     * @var array
     */
    protected $response = [];

    /**
     * Sorted profile pictures
     *
     * @var array
     */
    protected array $sortedProfilePictures = [];

    /**
     * @var string|null
     */
    private ?string $email;

    /**
     * Creates new resource owner.
     *
     * @param array  $response
     */
    public function __construct(array $response = array())
    {
        $this->response = $response;
        $this->setSortedProfilePictures();
    }

    /**
     * Gets resource owner attribute by key. The key supports dot notation.
     *
     */
    public function getAttribute($key): mixed
    {
        return $this->getValueByKey($this->response, (string) $key);
    }

    /**
     * Get user first name
     *
     * @return string|null
     */
    public function getFirstName(): ?string
    {
        return $this->getAttribute('localizedFirstName');
    }

    /**
     * Get user user id
     *
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->getAttribute('id');
    }

    /**
     * Get specific image by size
     *
     * @param integer $size
     * @return array|null
     */
    public function getImageBySize(int $size): ?array
    {
        $pictures = array_filter($this->sortedProfilePictures, function ($picture) use ($size) {
            return isset($picture['width']) && $picture['width'] == $size;
        });

        return count($pictures) ? $pictures[0] : null;
    }

    /**
     * Get available user image sizes
     *
     * @return array
     */
    public function getImageSizes(): array
    {
        return array_map(function ($picture) {
            return $this->getValueByKey($picture, 'width');
        }, $this->sortedProfilePictures);
    }

    /**
     * Get user image url
     *
     * @return string|null
     */
    public function getImageUrl(): ?string
    {
        $pictures = $this->getSortedProfilePictures();
        $picture = array_pop($pictures);

        return $picture ? $this->getValueByKey($picture, 'url') : null;
    }

    /**
     * Get user last name
     *
     * @return string|null
     */
    public function getLastName(): ?string
    {
        return $this->getAttribute('localizedLastName');
    }

    /**
     * Returns the sorted collection of profile pictures.
     *
     * @return array
     */
    public function getSortedProfilePictures(): array
    {
        return $this->sortedProfilePictures;
    }

    /**
     * Get user url
     *
     * @return string|null
     */
    public function getUrl(): ?string
    {
        $vanityName = $this->getAttribute('vanityName');

        return $vanityName ? sprintf('https://www.linkedin.com/in/%s', $vanityName) : null;
    }

    /**
     * Get user email, if available
     *
     * @return string|null
     */
    public function getEmail(): ?string
    {
        return $this->getAttribute('email');
    }

    /**
     * Attempts to sort the collection of profile pictures included in the profile
     * before caching them in the resource owner instance.
     *
     * @return void
     */
    private function setSortedProfilePictures(): void
    {
        $pictures = $this->getAttribute('profilePicture.displayImage~.elements');
        if (is_array($pictures)) {
            $pictures = array_filter($pictures, function ($element) {
                // filter to public images only
                return
                    isset($element['data']['com.linkedin.digitalmedia.mediaartifact.StillImage'])
                    && strtoupper($element['authorizationMethod']) === 'PUBLIC'
                    && isset($element['identifiers'][0]['identifier'])
                ;
            });
            // order images by width, LinkedIn profile pictures are always squares, so that should be good enough
            usort($pictures, function ($elementA, $elementB) {
                $wA = $elementA['data']['com.linkedin.digitalmedia.mediaartifact.StillImage']['storageSize']['width'];
                $wB = $elementB['data']['com.linkedin.digitalmedia.mediaartifact.StillImage']['storageSize']['width'];
                return $wA - $wB;
            });
            $pictures = array_map(function ($element) {
                // this is an URL, no idea how many of identifiers there can be, so take the first one.
                $url = $element['identifiers'][0]['identifier'];
                $type = $element['identifiers'][0]['mediaType'];
                $width = $element['data']['com.linkedin.digitalmedia.mediaartifact.StillImage']['storageSize']['width'];
                return [
                    'width' => $width,
                    'url' => $url,
                    'contentType' => $type,
                ];
            }, $pictures);
        } else {
            $pictures = [];
        }

        $this->sortedProfilePictures = $pictures;
    }

}
