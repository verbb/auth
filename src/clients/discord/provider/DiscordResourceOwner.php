<?php
/**
 * This file is part of the wohali/oauth2-discord-new library
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @copyright Copyright (c) Joan Touzet <code@atypical.net>
 * @license http://opensource.org/licenses/MIT MIT
 * @link https://packagist.org/packages/wohali/oauth2-discord-new Packagist
 * @link https://github.com/wohali/oauth2-discord-new GitHub
 */

namespace verbb\auth\clients\discord\provider;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use League\OAuth2\Client\Tool\ArrayAccessorTrait;

class DiscordResourceOwner implements ResourceOwnerInterface
{
    use ArrayAccessorTrait;

    /**
     * Raw response
     *
     * @var array
     */
    protected array $response = [];

    /**
     * Creates new resource owner.
     *
     * @param array  $response
     */
    public function __construct(array $response = array())
    {
        $this->response = $response;
    }

    /**
     * Get resource owner ID
     *
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->getValueByKey($this->response, 'id');
    }

    /**
     * Get resource owner username
     *
     * @return string|null
     */
    public function getUsername(): ?string
    {
        return $this->getValueByKey($this->response, 'username');
    }

    /**
     * Get resource owner discriminator
     *
     * @return string|null
     */
    public function getDiscriminator(): ?string
    {
        return $this->getValueByKey($this->response, 'discriminator');
    }

    /**
     * Get resource owner avatar hash
     *
     * @return string|null
     */
    public function getAvatarHash(): ?string
    {
        return $this->getValueByKey($this->response, 'avatar');
    }

    /**
     * Get resource owner verified flag
     *
     * @return bool
     */
    public function getVerified(): bool
    {
        return $this->getValueByKey($this->response, 'verified', false);
    }

    /**
     * Get resource owner email
     *
     * @return string|null
     */
    public function getEmail(): ?string
    {
        return $this->getValueByKey($this->response, 'email');
    }

    /**
     * Returns the raw resource owner response.
     *
     * @return array
     */
    public function toArray(): array
    {
        return $this->response;
    }
}
