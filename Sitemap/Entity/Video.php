<?php

namespace Sludio\HelperBundle\Sitemap\Entity;

class Video
{
    const RESTRICTION_DENY = 'deny';
    const RESTRICTION_ALLOW = 'allow';

    const PLATFORM_TV = 'tv';
    const PLATFORM_MOBILE = 'mobile';
    const PLATFORM_WEB = 'web';

    protected $thumbnailLoc = null;
    protected $title = null;
    protected $description = null;
    protected $contentLoc = null;
    protected $playerLoc = null;
    protected $duration = null;
    protected $expirationDate = null;
    protected $rating = null;
    protected $viewCount = null;
    protected $publicationDate = null;
    protected $familyFriendly = null;
    protected $tags = [];
    protected $category = null;
    protected $restrictions = null;
    protected $galleryLoc = null;
    protected $requiresSubscription = null;
    protected $uploader = null;
    protected $platforms = null;
    protected $live = null;

    public function setTitle($title)
    {
        if (strlen($title) > 100) {
            throw new \DomainException('The title value must be less than 100 characters');
        }

        $this->title = $title;

        return $this;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setThumbnailLoc($loc)
    {
        $this->thumbnailLoc = $loc;

        return $this;
    }

    public function getThumbnailLoc()
    {
        return $this->thumbnailLoc;
    }

    public function setDescription($description)
    {
        if (strlen($description) > 2048) {
            throw new \DomainException('The description value must be less than 2,048 characters');
        }

        $this->description = $description;

        return $this;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setContentLoc($loc)
    {
        $this->contentLoc = $loc;

        return $this;
    }

    public function getContentLoc()
    {
        return $this->contentLoc;
    }

    public function setPlayerLoc($loc, $allowEmbed = true, $autoplay = null)
    {
        if ($loc === null) {
            $this->playerLoc = null;

            return $this;
        }

        $this->playerLoc = [
            'loc' => $loc,
            'allow_embed' => $allowEmbed,
            'autoplay' => $autoplay !== null ? $autoplay : null,
        ];

        return $this;
    }

    public function getPlayerLoc()
    {
        return $this->playerLoc;
    }

    public function setDuration($duration)
    {
        $duration = (int)$duration;

        if ($duration < 0 || $duration > 28800) {
            throw new \DomainException('The duration must be between 0 and 28800 seconds');
        }

        $this->duration = $duration;

        return $this;
    }

    public function getDuration()
    {
        return $this->duration;
    }

    public function setExpirationDate($date)
    {
        if ($date !== null && !$date instanceof \DateTime) {
            $date = new \DateTime($date);
        }

        $this->expirationDate = $date;

        return $this;
    }

    public function getExpirationDate()
    {
        if ($this->expirationDate === null) {
            return null;
        }

        return $this->expirationDate->format(\DateTime::W3C);
    }

    public function setRating($rating)
    {
        $rating = (float)$rating;

        if ($rating < 0 || $rating > 5) {
            throw new \DomainException('The rating must be between 0 and 5');
        }

        $this->rating = $rating;

        return $this;
    }

    public function getRating()
    {
        return $this->rating;
    }

    public function setViewCount($count)
    {
        $count = (int)$count;

        if ($count < 0) {
            throw new \DomainException('The view count must be positive');
        }

        $this->viewCount = $count;

        return $this;
    }

    public function getViewCount()
    {
        return $this->viewCount;
    }

    public function setPublicationDate($date)
    {
        if ($date !== null && !$date instanceof \DateTime) {
            $date = new \DateTime($date);
        }

        $this->publicationDate = $date;

        return $this;
    }

    public function getPublicationDate()
    {
        if ($this->publicationDate === null) {
            return null;
        }

        return $this->publicationDate->format(\DateTime::W3C);
    }

    public function setFamilyFriendly($friendly)
    {
        $this->familyFriendly = (bool)$friendly;

        return $this;
    }

    public function getFamilyFriendly()
    {
        return $this->familyFriendly;
    }

    public function setTags($tags)
    {
        if ($tags === null) {
            $this->tags = null;

            return $this;
        }

        if (count($tags) > 32) {
            throw new \DomainException('A maximum of 32 tags is allowed.');
        }

        $this->tags = $tags;

        return $this;
    }

    public function getTags()
    {
        return $this->tags;
    }

    public function setCategory($category)
    {
        if (strlen($category) > 256) {
            throw new \DomainException('The category value must be less than 256 characters');
        }

        $this->category = $category;

        return $this;
    }

    public function getCategory()
    {
        return $this->category;
    }

    public function setRestrictions($restrictions, $relationship = self::RESTRICTION_DENY)
    {
        if ($restrictions === null) {
            $this->restrictions = null;

            return $this;
        }

        if ($relationship !== self::RESTRICTION_ALLOW && $relationship !== self::RESTRICTION_DENY) {
            throw new \InvalidArgumentException('The relationship must be deny or allow');
        }

        $this->restrictions = [
            'countries' => $restrictions,
            'relationship' => $relationship,
        ];

        return $this;
    }

    public function getRestrictions()
    {
        return $this->restrictions;
    }

    public function setGalleryLoc($loc, $title = null)
    {
        if ($loc === null) {
            $this->galleryLoc = null;

            return $this;
        }

        $this->galleryLoc = [
            'loc' => $loc,
            'title' => $title,
        ];

        return $this;
    }

    public function getGalleryLoc()
    {
        return $this->galleryLoc;
    }

    public function setRequiresSubscription($requiresSubscription)
    {
        $this->requiresSubscription = (bool)$requiresSubscription;

        return $this;
    }

    public function getRequiresSubscription()
    {
        return $this->requiresSubscription;
    }

    public function setUploader($uploader, $info = null)
    {
        if ($uploader === null) {
            $this->uploader = null;

            return $this;
        }

        $this->uploader = [
            'name' => $uploader,
            'info' => $info,
        ];

        return $this;
    }

    public function getUploader()
    {
        return $this->uploader;
    }

    public function setPlatforms($platforms)
    {
        if ($platforms === null) {
            $this->platforms = null;

            return $this;
        }

        $valid_platforms = [
            self::PLATFORM_TV,
            self::PLATFORM_WEB,
            self::PLATFORM_MOBILE,
        ];
        foreach ($platforms as $platform => $relationship) {
            if (!in_array($platform, $valid_platforms)) {
                throw new \DomainException(sprintf('Invalid platform given. Valid values are: %s', implode(', ', $valid_platforms)));
            }

            if ($relationship !== self::RESTRICTION_ALLOW && $relationship !== self::RESTRICTION_DENY) {
                throw new \InvalidArgumentException('The relationship must be deny or allow');
            }
        }

        $this->platforms = $platforms;

        return $this;
    }

    public function getPlatforms()
    {
        return $this->platforms;
    }

    public function setLive($live)
    {
        $this->live = (bool)$live;

        return $this;
    }

    public function getLive()
    {
        return $this->live;
    }
}