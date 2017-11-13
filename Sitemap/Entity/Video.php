<?php

namespace Sludio\HelperBundle\Sitemap\Entity;

class Video
{
    use VideoTrait;

    public const RESTRICTION_DENY = 'deny';
    public const RESTRICTION_ALLOW = 'allow';

    public const PLATFORM_TV = 'tv';
    public const PLATFORM_MOBILE = 'mobile';
    public const PLATFORM_WEB = 'web';

    public function setTitle($title)
    {
        if (\strlen($title) > 100) {
            throw new \DomainException('The title value must be less than 100 characters');
        }

        $this->title = $title;

        return $this;
    }

    public function setDescription($description)
    {
        if (\strlen($description) > 2048) {
            throw new \DomainException('The description value must be less than 2,048 characters');
        }

        $this->description = $description;

        return $this;
    }

    public function setPlayerLoc($loc, $allowEmbed = true, $autoplay = null)
    {
        $this->playerLoc = null;
        if ($loc !== null) {
            $this->playerLoc = [
                'loc' => $loc,
                'allow_embed' => $allowEmbed,
                'autoplay' => $autoplay,
            ];
        }

        return $this;
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

    public function setViewCount($count)
    {
        if ((int)$count < 0) {
            throw new \DomainException('The view count must be positive');
        }

        $this->viewCount = $count;

        return $this;
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

    public function setTags($tags)
    {
        if ($tags === null) {
            $this->tags = null;
        } else {
            if (\count($tags) > 32) {
                throw new \DomainException('A maximum of 32 tags is allowed.');
            }

            $this->tags = $tags;
        }

        return $this;
    }

    public function setCategory($category)
    {
        if (\strlen($category) > 256) {
            throw new \DomainException('The category value must be less than 256 characters');
        }

        $this->category = $category;

        return $this;
    }

    public function setRestrictions($restrictions, $relationship = self::RESTRICTION_DENY)
    {
        if ($restrictions === null) {
            $this->restrictions = null;
        } else {
            if ($relationship !== self::RESTRICTION_ALLOW && $relationship !== self::RESTRICTION_DENY) {
                throw new \InvalidArgumentException('The relationship must be deny or allow');
            }

            $this->restrictions = [
                'countries' => $restrictions,
                'relationship' => $relationship,
            ];
        }

        return $this;
    }

    public function setGalleryLoc($loc, $title = null)
    {
        $this->galleryLoc = null;
        if ($loc !== null) {
            $this->galleryLoc = [
                'loc' => $loc,
                'title' => $title,
            ];
        }

        return $this;
    }

    public function setUploader($uploader, $info = null)
    {
        $this->uploader = null;
        if ($uploader !== null) {
            $this->uploader = [
                'name' => $uploader,
                'info' => $info,
            ];
        }

        return $this;
    }

    public function setPlatforms($platforms)
    {
        if ($platforms === null) {
            $this->platforms = null;
        } else {
            $valid_platforms = [
                self::PLATFORM_TV,
                self::PLATFORM_WEB,
                self::PLATFORM_MOBILE,
            ];
            /** @var $platforms array */
            foreach ($platforms as $platform => $relationship) {
                if (!\in_array($platform, $valid_platforms, true)) {
                    throw new \DomainException(sprintf('Invalid platform given. Valid values are: %s', implode(', ', $valid_platforms)));
                }

                if ($relationship !== self::RESTRICTION_ALLOW && $relationship !== self::RESTRICTION_DENY) {
                    throw new \InvalidArgumentException('The relationship must be deny or allow');
                }
            }

            $this->platforms = $platforms;
        }

        return $this;
    }
}
