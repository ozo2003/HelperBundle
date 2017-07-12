<?php

namespace Sludio\HelperBundle\Oauth\Client\Provider\Facebook;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use Sludio\HelperBundle\Oauth\Implement\SocialUserInterface;

class FacebookUser implements ResourceOwnerInterface, SocialUserInterface
{
    /**
     * @var array
     */
    protected $response;

    /**
     * @var integer
     */
    protected $id;

    /**
     * @var string
     */
    protected $email;

    /**
     * @var string
     */
    protected $firstName;

    /**
     * @var string
     */
    protected $lastName;

    /**
     * @var string
     */
    protected $username;

    /**
     * @param  array $response
     */
    public function __construct(array $response)
    {
        $this->response = $response;

        if (!empty($response['picture']['data']['url'])) {
            $this->response['picture_url'] = $response['picture']['data']['url'];
        }

        if (isset($response['picture']['data']['is_silhouette'])) {
            $this->response['is_silhouette'] = $response['picture']['data']['is_silhouette'];
        }

        if (!empty($response['cover']['source'])) {
            $this->response['cover_photo_url'] = $response['cover']['source'];
        }

        $this->id = intval($this->getField('id'));

        $this->email = $this->getField('email');

        $this->firstName = $this->getField('first_name');

        $this->lastName = $this->getField('last_name');

        $username = explode('@', $this->email);
        $username = preg_replace('/[^a-z\d]/i', '', $username[0]);
        $this->username = $username;
    }

    /**
     * Returns the name for the user as a string if present.
     *
     * @return string|null
     */
    public function getName()
    {
        return $this->getField('name');
    }

    /**
     * Returns the current location of the user as an array.
     *
     * @return array|null
     */
    public function getHometown()
    {
        return $this->getField('hometown');
    }

    /**
     * Returns the "about me" bio for the user as a string if present.
     *
     * @return string|null
     * @deprecated The bio field was removed in Graph v2.8
     */
    public function getBio()
    {
        return $this->getField('bio');
    }

    /**
     * Returns if user has not defined a specific avatar
     *
     * @return boolean
     */

    public function isDefaultPicture()
    {
        return $this->getField('is_silhouette');
    }

    /**
     * Returns the profile picture of the user as a string if present.
     *
     * @return string|null
     */
    public function getPictureUrl()
    {
        return $this->getField('picture_url');
    }

    /**
     * Returns the cover photo URL of the user as a string if present.
     *
     * @return string|null
     */
    public function getCoverPhotoUrl()
    {
        return $this->getField('cover_photo_url');
    }

    /**
     * Returns the gender for the user as a string if present.
     *
     * @return string|null
     */
    public function getGender()
    {
        return $this->getField('gender');
    }

    /**
     * Returns the locale of the user as a string if available.
     *
     * @return string|null
     */
    public function getLocale()
    {
        return $this->getField('locale');
    }

    /**
     * Returns the Facebook URL for the user as a string if available.
     *
     * @return string|null
     */
    public function getLink()
    {
        return $this->getField('link');
    }

    /**
     * Returns the current timezone offset from UTC (from -24 to 24)
     *
     * @return float|null
     */
    public function getTimezone()
    {
        return $this->getField('timezone');
    }

    /**
     * Returns the lower bound of the user's age range
     *
     * @return integer|null
     */
    public function getMinAge()
    {
        if (isset($this->response['age_range']['min'])) {
            return $this->response['age_range']['min'];
        }
        return null;
    }

    /**
     * Returns the upper bound of the user's age range
     *
     * @return integer|null
     */
    public function getMaxAge()
    {
        if (isset($this->response['age_range']['max'])) {
            return $this->response['age_range']['max'];
        }
        return null;
    }

    /**
     * Returns all the data obtained about the user.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->response;
    }

    /**
     * Returns a field from the Graph node data.
     *
     * @param string $key
     *
     * @return mixed|null
     */
    private function getField($key)
    {
        return isset($this->response[$key]) ? $this->response[$key] : null;
    }

    /**
     * Get the value of Id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get the value of Email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Get the value of First Name
     *
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * Get the value of Last Name
     *
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * Get the value of Username
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

}
