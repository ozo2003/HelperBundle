<?php

namespace Sludio\HelperBundle\Sitemap;

use Sludio\HelperBundle\Sitemap\Dumper\DumperFileInterface;
use Sludio\HelperBundle\Sitemap\Dumper\DumperInterface;
use Sludio\HelperBundle\Sitemap\Entity\Image;
use Sludio\HelperBundle\Sitemap\Entity\SitemapIndex;
use Sludio\HelperBundle\Sitemap\Entity\Url;
use Sludio\HelperBundle\Sitemap\Entity\Video;
use Sludio\HelperBundle\Sitemap\Formatter\FormatterInterface;
use Sludio\HelperBundle\Sitemap\Formatter\SitemapIndexFormatterInterface;
use Sludio\HelperBundle\Sitemap\Provider\ProviderInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class Sitemap
{
    protected $providers = [];
    protected $dumper;
    protected $formatter;
    protected $baseHost;
    protected $limit = 0;
    protected $sitemapIndexes = [];
    protected $originalFilename;

    public function __construct(DumperInterface $dumper, FormatterInterface $formatter, $baseHost = null, $limit = 0, RequestStack $requestStack)
    {
        $this->dumper = $dumper;
        $this->formatter = $formatter;
        $this->baseHost = $baseHost;
        if ($this->baseHost === null && PHP_SAPI !== 'cli') {
            $request = $requestStack->getCurrentRequest();
            $useHttps = $request->server->get('HTTPS') || ($request->server->get('HTTP_X_FORWARDED_PROTO') && $request->server->get('HTTP_X_FORWARDED_PROTO') === 'https');
            $this->baseHost = ($useHttps ? 'https' : 'http').'://'.$request->server->get('HTTP_HOST');
        }
        $this->limit = $limit;
        if ($this->isSitemapIndexable()) {
            if ($dumper instanceof DumperFileInterface) {
                $this->originalFilename = $dumper->getFilename();
            }
        }
    }

    protected function isSitemapIndexable()
    {
        return ($this->limit > 0 && $this->dumper instanceof DumperFileInterface && $this->formatter instanceof SitemapIndexFormatterInterface);
    }

    public function addProvider(ProviderInterface $provider)
    {
        $this->providers[] = $provider;

        return $this;
    }

    public function setDumper(DumperInterface $dumper)
    {
        $this->dumper = $dumper;

        return $this;
    }

    public function build()
    {
        if ($this->isSitemapIndexable()) {
            $this->addSitemapIndex($this->createSitemapIndex());
        }

        $this->dumper->dump($this->formatter->getSitemapStart());

        foreach ($this->providers as $provider) {
            $provider->populate($this);
        }

        $sitemapContent = $this->dumper->dump($this->formatter->getSitemapEnd());

        if (!$this->isSitemapIndexable()) {
            return $sitemapContent;
        }

        if (\count($this->sitemapIndexes)) {
            $this->dumper->setFilename($this->originalFilename);

            $this->dumper->dump($this->formatter->getSitemapIndexStart());
            foreach ($this->sitemapIndexes as $sitemapIndex) {
                $this->dumper->dump($this->formatter->formatSitemapIndex($sitemapIndex));
            }

            $this->dumper->dump($this->formatter->getSitemapIndexEnd());
        }

        return null;
    }

    protected function addSitemapIndex(SitemapIndex $sitemapIndex)
    {
        $nbSitemapIndexs = \count($this->sitemapIndexes);

        if ($nbSitemapIndexs > 0) {
            $this->dumper->dump($this->formatter->getSitemapEnd());
        }
        $sitemapIndexFilename = $this->getSitemapIndexFilename($this->originalFilename);
        $this->dumper->setFilename($sitemapIndexFilename);

        $this->sitemapIndexes[] = $sitemapIndex;
        if ($nbSitemapIndexs > 0) {
            $this->dumper->dump($this->formatter->getSitemapStart());
        }
    }

    protected function getSitemapIndexFilename($filename)
    {
        $sitemapIndexFilename = basename($filename);
        $index = \count($this->sitemapIndexes) + 1;
        $extPosition = strrpos($sitemapIndexFilename, '.');
        if ($extPosition !== false) {
            $sitemapIndexFilename = substr($sitemapIndexFilename, 0, $extPosition).'-'.$index.substr($sitemapIndexFilename, $extPosition);
        } else {
            $sitemapIndexFilename .= '-'.$index;
        }

        $sitemapIndexFilename = \dirname($filename).DIRECTORY_SEPARATOR.$sitemapIndexFilename;

        return $sitemapIndexFilename;
    }

    protected function createSitemapIndex()
    {
        $sitemapIndex = new SitemapIndex();
        $sitemapIndex->setLastmod(new \DateTime());

        return $sitemapIndex;
    }

    public function add(Url $url)
    {
        if ($this->isSitemapIndexable() && $this->getCurrentSitemapIndex()->getUrlCount() >= $this->limit) {
            $this->addSitemapIndex($this->createSitemapIndex());
        }

        $loc = $url->getLoc();
        if (empty($loc)) {
            throw new \InvalidArgumentException('The url MUST have a loc attribute');
        }

        if ($this->baseHost !== null) {
            if ($this->needHost($loc)) {
                $url->setLoc($this->baseHost.$loc);
            }

            $this->parseVideo($url);
            $this->parseImage($url);
        }

        $this->dumper->dump($this->formatter->formatUrl($url));

        if ($this->isSitemapIndexable()) {
            $this->getCurrentSitemapIndex()->incrementUrl();
        }

        return $this;
    }

    protected function parseVideo(Url $url)
    {
        foreach ($url->getVideos() as $video) {
            /** @var Video $video */
            if ($this->needHost($video->getThumbnailLoc())) {
                $video->setThumbnailLoc($this->baseHost.$video->getThumbnailLoc());
            }

            if ($this->needHost($video->getContentLoc())) {
                $video->setContentLoc($this->baseHost.$video->getContentLoc());
            }

            $player = $video->getPlayerLoc();
            if ($player !== null && $this->needHost($player['loc'])) {
                $video->setPlayerLoc($this->baseHost.$player['loc'], $player['allow_embed'], $player['autoplay']);
            }

            $gallery = $video->getGalleryLoc();
            if ($gallery !== null && $this->needHost($gallery['loc'])) {
                $video->setGalleryLoc($this->baseHost.$gallery['loc'], $gallery['title']);
            }
        }
    }

    protected function parseImage(Url $url)
    {
        foreach ($url->getImages() as $image) {
            /** @var Image $image */
            if ($this->needHost($image->getLoc())) {
                $image->setLoc($this->baseHost.$image->getLoc());
            }

            if ($this->needHost($image->getLicense())) {
                $image->setLicense($this->baseHost.$image->getLicense());
            }
        }
    }

    protected function getCurrentSitemapIndex()
    {
        return end($this->sitemapIndexes);
    }

    protected function needHost($url)
    {
        if ($url === null) {
            return false;
        }

        return 0 !== strpos($url, 'http');
    }
}
