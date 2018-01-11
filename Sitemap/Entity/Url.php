<?php

namespace Sludio\HelperBundle\Sitemap\Entity;

class Url
{
    const CHANGEFREQ_ALWAYS = 'always';
    const CHANGEFREQ_HOURLY = 'hourly';
    const CHANGEFREQ_DAILY = 'daily';
    const CHANGEFREQ_WEEKLY = 'weekly';
    const CHANGEFREQ_MONTHLY = 'monthly';
    const CHANGEFREQ_YEARLY = 'yearly';
    const CHANGEFREQ_NEVER = 'never';

    protected $loc;
    protected $lastmod;
    protected $changefreq;
    protected $priority;
    protected $videos = [];
    protected $images = [];

    public function getLoc()
    {
        return $this->loc;
    }

    public function setLoc($loc)
    {
        if (\strlen($loc) > 2048) {
            throw new \DomainException('The loc value must be less than 2,048 characters');
        }

        $this->loc = $loc;

        return $this;
    }

    public function getLastmod()
    {
        if ($this->lastmod === null) {
            return null;
        }

        if ($this->getChangefreq() === null || \in_array($this->getChangefreq(), [
                self::CHANGEFREQ_ALWAYS,
                self::CHANGEFREQ_HOURLY,
            ], true)) {
            return $this->lastmod->format(\DateTime::W3C);
        }

        return $this->lastmod->format('Y-m-d');
    }

    public function setLastmod($lastmod)
    {
        if ($lastmod !== null && !$lastmod instanceof \DateTime) {
            $lastmod = new \DateTime($lastmod);
        }

        $this->lastmod = $lastmod;

        return $this;
    }

    public function getChangefreq()
    {
        return $this->changefreq;
    }

    public function setChangefreq($changefreq)
    {
        $validFreqs = [
            self::CHANGEFREQ_ALWAYS,
            self::CHANGEFREQ_HOURLY,
            self::CHANGEFREQ_DAILY,
            self::CHANGEFREQ_WEEKLY,
            self::CHANGEFREQ_MONTHLY,
            self::CHANGEFREQ_YEARLY,
            self::CHANGEFREQ_NEVER,
            null,
        ];

        if (!\in_array($changefreq, $validFreqs, true)) {
            throw new \DomainException(sprintf('Invalid changefreq given. Valid values are: %s', implode(', ', $validFreqs)));
        }

        $this->changefreq = $changefreq;

        return $this;
    }

    public function getPriority()
    {
        return $this->priority;
    }

    public function setPriority($priority)
    {
        $priority = (float)$priority;

        if ($priority < 0 || $priority > 1) {
            throw new \DomainException('The priority must be between 0 and 1');
        }

        $this->priority = $priority;

        return $this;
    }

    public function addVideo(Video $video)
    {
        $this->videos[] = $video;

        return $this;
    }

    public function getVideos()
    {
        return $this->videos;
    }

    public function setVideos($videos)
    {
        $this->videos = $videos;

        return $this;
    }

    public function addImage(Image $image)
    {
        $this->images[] = $image;

        return $this;
    }

    public function getImages()
    {
        return $this->images;
    }

    public function setImages($images)
    {
        $this->images = $images;

        return $this;
    }
}
