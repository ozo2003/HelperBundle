<?php

namespace Sludio\HelperBundle\Sitemap\Entity;

trait VideoTrait
{
    protected $thumbnailLoc;
    protected $title;
    protected $description;
    protected $contentLoc;
    protected $playerLoc;
    protected $duration;
    protected $expirationDate;
    protected $rating;
    protected $viewCount;
    protected $publicationDate;
    protected $familyFriendly;
    protected $tags = [];
    protected $category;
    protected $restrictions;
    protected $galleryLoc;
    protected $requiresSubscription;
    protected $uploader;
    protected $platforms;
    protected $live;

    public function getTitle()
    {
        return $this->title;
    }

    public function getThumbnailLoc()
    {
        return $this->thumbnailLoc;
    }

    public function setThumbnailLoc($loc)
    {
        $this->thumbnailLoc = $loc;

        return $this;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function getContentLoc()
    {
        return $this->contentLoc;
    }

    public function setContentLoc($loc)
    {
        $this->contentLoc = $loc;

        return $this;
    }

    public function getPlayerLoc()
    {
        return $this->playerLoc;
    }

    public function getDuration()
    {
        return $this->duration;
    }

    public function getRating()
    {
        return $this->rating;
    }

    public function getViewCount()
    {
        return $this->viewCount;
    }

    public function getFamilyFriendly()
    {
        return $this->familyFriendly;
    }

    public function setFamilyFriendly($friendly)
    {
        $this->familyFriendly = (bool)$friendly;

        return $this;
    }

    public function getTags()
    {
        return $this->tags;
    }

    public function getCategory()
    {
        return $this->category;
    }

    public function getRestrictions()
    {
        return $this->restrictions;
    }

    public function getGalleryLoc()
    {
        return $this->galleryLoc;
    }

    public function getRequiresSubscription()
    {
        return $this->requiresSubscription;
    }

    public function setRequiresSubscription($requiresSubscription)
    {
        $this->requiresSubscription = (bool)$requiresSubscription;

        return $this;
    }

    public function getUploader()
    {
        return $this->uploader;
    }

    public function getPlatforms()
    {
        return $this->platforms;
    }

    public function getLive()
    {
        return $this->live;
    }

    public function setLive($live)
    {
        $this->live = (bool)$live;

        return $this;
    }
}
