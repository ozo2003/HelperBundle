<?php

namespace Sludio\HelperBundle\Sitemap\Entity;

class Image
{
    /**
     * @var string|null
     */
    protected $loc;

    /**
     * @var string|null
     */
    protected $caption;

    /**
     * @var string|null
     */
    protected $geoLocation;

    /**
     * @var string|null
     */
    protected $title;
    /**
     * @var string|null
     */

    protected $license;

    /**
     * @return null|string
     */
    public function getLoc()
    {
        return $this->loc;
    }

    /**
     * @param null|string $loc
     *
     * @return Image
     */
    public function setLoc($loc)
    {
        $this->loc = $loc;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getCaption()
    {
        return $this->caption;
    }

    /**
     * @param null|string $caption
     *
     * @return Image
     */
    public function setCaption($caption)
    {
        $this->caption = $caption;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getGeoLocation()
    {
        return $this->geoLocation;
    }

    /**
     * @param null|string $geoLocation
     *
     * @return Image
     */
    public function setGeoLocation($geoLocation)
    {
        $this->geoLocation = $geoLocation;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param null|string $title
     *
     * @return Image
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getLicense()
    {
        return $this->license;
    }

    /**
     * @param null|string $license
     *
     * @return Image
     */
    public function setLicense($license)
    {
        $this->license = $license;

        return $this;
    }
}
