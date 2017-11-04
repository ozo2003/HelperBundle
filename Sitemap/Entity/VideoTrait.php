<?php

namespace Sludio\HelperBundle\Sitemap\Entity;

trait VideoTrait
{
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

    public function setFamilyFriendly($friendly)
    {
        $this->familyFriendly = (bool)$friendly;

        return $this;
    }

    public function getFamilyFriendly()
    {
        return $this->familyFriendly;
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

    public function setRequiresSubscription($requiresSubscription)
    {
        $this->requiresSubscription = (bool)$requiresSubscription;

        return $this;
    }

    public function getRequiresSubscription()
    {
        return $this->requiresSubscription;
    }

    public function getUploader()
    {
        return $this->uploader;
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
