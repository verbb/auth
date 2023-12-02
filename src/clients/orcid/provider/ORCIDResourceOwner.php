<?php

/**
 * This file is part of the cilogon/oauth2-orcid library.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author    Terry Fleury <tfleury@cilogon.org>
 * @copyright 2016 University of Illinois
 * @license   https://opensource.org/licenses/NCSA NCSA
 * @link      https://github.com/cilogon/oauth2-orcid GitHub
 */

namespace verbb\auth\clients\orcid\provider;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;

class ORCIDResourceOwner implements ResourceOwnerInterface
{
    /**
     * Raw response
     *
     * @var array
     */
    protected array $response = [];

    /**
     * Creates new resource owner.
     *
     * @param array $response
     */
    public function __construct(array $response = array())
    {
        $this->response = $response;
    }

    /**
     * Get resource owner id. This corresponds to the "full" ORCID identifier (with
     * the http://orcid.org/ prefix).
     *
     * @return string|null
     */
    public function getId(): ?string
    {
        return @$this->response['orcid-identifier']['uri'] ?: null;
    }

    /**
     * Get resource owner display name. This corresponds to the
     * "Published Name", * a.k.a. "credit-name".
     *
     * @return string|null
     */
    public function getName(): ?string
    {
        return @$this->response['person']['name']['credit-name']['value'] ?: null;
    }

    /**
     * Get resource owner given (first) name.
     *
     * @return string|null
     */
    public function getGivenName(): ?string
    {
        return @$this->response['person']['name']['given-names']['value'] ?: null;
    }

    /**
     * Get resource owner given (first) name. Alias for getGivenName().
     *
     * @return string|null
     */
    public function getFirstName(): ?string
    {
        return $this->getGivenName();
    }

    /**
     * Get resource owner family (last) name.
     *
     * @return string|null
     */
    public function getFamilyName(): ?string
    {
        return @$this->response['person']['name']['family-name']['value'] ?: null;
    }

    /**
     * Get resource owner family (last) name. Alias for getFamilyName();
     *
     * @return string|null
     */
    public function getLastName(): ?string
    {
        return $this->getFamilyName();
    }

    /**
     * Get other resource owner names, a.k.a. "Also Known As" names.
     *
     * @return array
     */
    public function getOtherNames(): array
    {
        $retval = array();
        foreach (@$this->response['person']['other-names']['other-name'] as $name) {
            if (isset($name['content'])) {
                $retval[] = @$name['content'];
            }
        }

        return $retval;
    }

    /**
     * Get resource owner email address. As there can be multiple email
     * addresses for a user, loop through all of them with preference to the
     * one marked as 'primary'. If no primary email address exists, then use
     * the last one listed.
     *
     * @return string|null
     */
    public function getEmail(): ?string
    {
        $retval = null;
        foreach (@$this->response['person']['emails']['email'] as $email) {
            if (@$email['primary']) {
                $retval = @$email['email'];
                break;
            }

            if (is_null($retval)) {
                $retval = @$email['email'];
            }
        }

        return $retval;
    }

    /**
     * Get resource owner PRIMARY email address. As there can be multiple email
     * addresses for a user, loop through all of them looking for the one
     * marked as 'primary'.
     *
     * @return string|null
     */
    public function getPrimaryEmail(): ?string
    {
        $retval = null;
        foreach (@$this->response['person']['emails']['email'] as $email) {
            if (@$email['primary']) {
                $retval = @$email['email'];
                break;
            }
        }

        return $retval;
    }

    /**
     * Get all resource owner email addresses.
     *
     * @return array
     */
    public function getEmails(): array
    {
        $retval = array();
        foreach (@$this->response['person']['emails']['email'] as $email) {
            if (isset($email['email'])) {
                $retval[] = @$email['email'];
            }
        }

        return $retval;
    }

    /**
     * Get AMR (AuthnMethodRef) used during authentication.
     * This value is available only with the Member API.
     * Can be one of 'mfa', 'pwd', or null.
     *
     * @return string|null
     */
    public function getAmr(): ?string
    {
        return @$this->response['amr'] ?: null;
    }

    /**
     * Return all of the owner details available as an array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return $this->response;
    }
}
