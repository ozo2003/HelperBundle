<?php

namespace Sludio\HelperBundle\Sitemap\Formatter;

use Sludio\HelperBundle\Sitemap\Entity\Image;
use Sludio\HelperBundle\Sitemap\Entity\Url;
use Sludio\HelperBundle\Sitemap\Entity\Video;
use Sludio\HelperBundle\Sitemap\Entity\SitemapIndex;

class XmlFormatter extends BaseFormatter implements SitemapIndexFormatterInterface
{
    public function getSitemapStart()
    {
        return '<?xml version="1.0" encoding="UTF-8"?>'."\n".'<urlset '.'xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" '.'xmlns:video="http://www.google.com/schemas/sitemap-video/1.1" '.'xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">'."\n";
    }

    public function getSitemapEnd()
    {
        return '</urlset>';
    }

    public function getSitemapIndexStart()
    {
        return '<?xml version="1.0" encoding="UTF-8"?>'."\n".'<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";
    }

    public function getSitemapIndexEnd()
    {
        return '</sitemapindex>';
    }

    public function formatUrl(Url $url)
    {
        return '<url>'."\n".$this->formatBody($url).'</url>'."\n";
    }

    protected function formatBody(Url $url)
    {
        $buffer = "\t".'<loc>'.$this->escape($url->getLoc()).'</loc>'."\n";

        $checks = [
            'getLastmod' => "\t".'<lastmod>'.$this->escape($url->getLastmod()).'</lastmod>'."\n",
            'getChangefreq' => "\t".'<changefreq>'.$this->escape($url->getChangefreq()).'</changefreq>'."\n",
            'getPriority' => "\t".'<priority>'.$this->escape($url->getPriority()).'</priority>'."\n",
        ];

        foreach ($checks as $check => $text) {
            if ($url->{$check}() !== null) {
                $buffer .= $text;
            }
        }

        foreach ($url->getVideos() as $video) {
            $buffer .= $this->formatVideo($video);
        }

        foreach ($url->getImages() as $image) {
            $buffer .= $this->formatImage($image);
        }

        return $buffer;
    }

    public function formatSitemapIndex(SitemapIndex $sitemapIndex)
    {
        return '<sitemap>'."\n".$this->formatSitemapIndexBody($sitemapIndex).'</sitemap>'."\n";
    }

    protected function formatSitemapIndexBody(SitemapIndex $sitemapIndex)
    {
        $buffer = "\t".'<loc>'.$this->escape($sitemapIndex->getLoc()).'</loc>'."\n";

        if ($sitemapIndex->getLastmod() !== null) {
            $buffer .= "\t".'<lastmod>'.$this->escape($sitemapIndex->getLastmod()).'</lastmod>'."\n";
        }

        return $buffer;
    }

    protected function checkVideoPlayerLoc($video)
    {
        $playerLoc = $video->getPlayerLoc();
        $allowEmbed = $playerLoc['allow_embed'] ? 'yes' : 'no';
        $autoplay = $playerLoc['autoplay'] !== null ? sprintf(' autoplay="%s"', $this->escape($playerLoc['autoplay'])) : '';

        return "\t\t".sprintf('<video:player_loc allow_embed="%s"%s>', $allowEmbed, $autoplay).$this->escape($playerLoc['loc']).'</video:player_loc>'."\n";
    }

    protected function checkVideoTags($video)
    {
        $text = '';
        $tags = $video->getTags();
        /** @var $tags array */
        foreach ($tags as $tag) {
            $text .= "\t\t".'<video:tag>'.$this->escape($tag).'</video:tag>'."\n";
        }

        return $text;
    }

    protected function checkVideoRestrictions($video)
    {
        $restrictions = $video->getRestrictions();
        $relationship = $this->escape($restrictions['relationship']);

        return "\t\t".'<video:restriction relationship="'.$relationship.'">'.$this->escape(implode(' ', $restrictions['countries'])).'</video:restriction>'."\n";
    }

    protected function checkVideoGalleryLoc($video)
    {
        $galleryLoc = $video->getGalleryLoc();
        $title = $galleryLoc['title'] !== null ? sprintf(' title="%s"', $this->escape($galleryLoc['title'])) : '';

        return "\t\t".sprintf('<video:gallery_loc%s>', $title).$this->escape($galleryLoc['loc']).'</video:gallery_loc>'."\n";
    }

    protected function checkVideoUploader($video)
    {
        $uploader = $video->getUploader();
        $info = $uploader['info'] !== null ? sprintf(' info="%s"', $this->escape($uploader['info'])) : '';

        return "\t\t".sprintf('<video:uploader%s>', $info).$this->escape($uploader['name']).'</video:uploader>'."\n";
    }

    protected function checkVideoPlatforms($video)
    {
        $text = '';
        $platforms = $video->getPlatforms();
        /** @var $platforms array */
        foreach ($platforms as $platform => $relationship) {
            $text .= "\t\t".'<video:platform relationship="'.$this->escape($relationship).'">'.$this->escape($platform).'</video:platform>'."\n";
        }

        return $text;
    }

    protected function videoHeader($video)
    {
        $buffer = "\t".'<video:video>'."\n";

        $buffer .= "\t\t".'<video:title>'.$this->escape($video->getTitle()).'</video:title>'."\n";
        $buffer .= "\t\t".'<video:description>'.$this->escape($video->getDescription()).'</video:description>'."\n";
        $buffer .= "\t\t".'<video:thumbnail_loc>'.$this->escape($video->getThumbnailLoc()).'</video:thumbnail_loc>'."\n";

        return $buffer;
    }

    protected function formatVideo(Video $video)
    {
        $buffer = $this->videoHeader($video);

        if ($video->getFamilyFriendly() === false) {
            $buffer .= "\t\t".'<video:family_friendly>no</video:family_friendly>'."\n";
        }

        $checks = [
            'getContentLoc' => "\t\t".'<video:content_loc>'.$this->escape($video->getContentLoc()).'</video:content_loc>'."\n",
            'getDuration' => "\t\t".'<video:duration>'.$this->escape($video->getDuration()).'</video:duration>'."\n",
            'getExpirationDate' => "\t\t".'<video:expiration_date>'.$this->escape($video->getExpirationDate()).'</video:expiration_date>'."\n",
            'getRating' => "\t\t".'<video:rating>'.$this->escape($video->getRating()).'</video:rating>'."\n",
            'getViewCount' => "\t\t".'<video:view_count>'.$this->escape($video->getViewCount()).'</video:view_count>'."\n",
            'getPublicationDate' => "\t\t".'<video:publication_date>'.$this->escape($video->getPublicationDate()).'</video:publication_date>'."\n",
            'getCategory' => "\t\t".'<video:category>'.$this->escape($video->getCategory()).'</video:category>'."\n",
            'getRequiresSubscription' => "\t\t".'<video:requires_subscription>'.($video->getRequiresSubscription() ? 'yes' : 'no').'</video:requires_subscription>'."\n",
            'getLive' => "\t\t".'<video:live>'.($video->getLive() ? 'yes' : 'no').'</video:live>'."\n",
        ];

        $extended = [
            'getPlayerLoc' => 'checkVideoPlayerLoc',
            'getTags' => 'checkVideoTags',
            'getRestrictions' => 'checkVideoRestrictions',
            'getGalleryLoc' => 'checkVideoGalleryLoc',
            'getUploader' => 'checkVideoUploader',
            'getPlatforms' => 'checkVideoPlatforms',
        ];

        $keys = array_merge(array_keys($checks), array_keys($extended));
        foreach ($keys as $key) {
            if ($video->{$key}() !== null) {
                if (isset($checks[$key])) {
                    $buffer .= $checks[$key];
                } elseif (isset($extended[$key])) {
                    $buffer .= $this->{$extended[$key]}($video);
                }
            }
        }

        return $buffer."\t".'</video:video>'."\n";
    }

    protected function formatImage(Image $image)
    {
        $buffer = "\t".'<image:image>'."\n\t\t".'<image:loc>'.$this->escape($image->getLoc()).'</image:loc>'."\n";

        $checks = [
            'getCaption' => "\t\t".'<image:caption>'.$this->escape($image->getCaption()).'</image:caption>'."\n",
            'getGeoLocation' => "\t\t".'<image:geo_location>'.$this->escape($image->getGeoLocation()).'</image:geo_location>'."\n",
            'getTitle' => "\t\t".'<image:title>'.$this->escape($image->getTitle()).'</image:title>'."\n",
            'getLicense' => "\t\t".'<image:license>'.$this->escape($image->getLicense()).'</image:license>'."\n",
        ];

        foreach ($checks as $check => $text) {
            if ($image->{$check}() !== null) {
                $buffer .= $text;
            }
        }

        return $buffer."\t".'</image:image>'."\n";
    }
}
