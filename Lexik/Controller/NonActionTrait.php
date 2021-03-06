<?php

namespace Sludio\HelperBundle\Lexik\Controller;

use Doctrine\DBAL\DBALException;
use Lexik\Bundle\TranslationBundle\Entity\TransUnit;
use Lexik\Bundle\TranslationBundle\Manager\TranslationInterface;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Yaml\Dumper;

trait NonActionTrait
{
    /**
     * Execute a batch download
     *
     * @param ProxyQueryInterface $queryProxy
     *
     * @return StreamedResponse
     * @internal param ProxyQueryInterface $query
     * @throws \InvalidArgumentException
     */
    public function batchActionDownload(ProxyQueryInterface $queryProxy)
    {
        $flashType = 'success';

        $dumper = new Dumper();
        $dumper->setIndentation(4);

        $response = new StreamedResponse(function() use ($queryProxy, &$flashType, $dumper) {
            try {
                /**
                 * @var TransUnit $transUnit
                 */
                $iterates = $queryProxy->getQuery()->iterate();
                /** @var $iterates array */
                foreach ($iterates as $pos => $object) {
                    /** @var $object array */
                    foreach ($object as $transUnit) {
                        $chunkPrefix = $transUnit->getDomain().'__'.$transUnit->getKey().'__'.$transUnit->getId().'__';
                        $chunk = [];
                        /** @var TranslationInterface $translation */
                        foreach ($transUnit->getTranslations() as $translation) {
                            $chunk[$chunkPrefix.$translation->getLocale()] = $translation->getContent();
                        }
                        echo $dumper->dump($chunk, 2);
                        flush();
                    }
                }
            } catch (\PDOException $e) {
                $flashType = 'error';
                flush();
            } catch (DBALException $e) {
                $flashType = 'error';
                flush();
            }
        });

        $this->addFlash('sonata_flash_'.$flashType, 'translations.flash_batch_download_'.$flashType);

        $response->headers->set('Content-Type', 'text/x-yaml');
        $response->headers->set('Cache-Control', '');
        $response->headers->set('Transfer-Encoding', 'chunked');
        $response->headers->set('Last-Modified', gmdate('D, d M Y H:i:s'));
        $contentDisposition = $response->headers->makeDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, 'translations.yml');
        $response->headers->set('Content-Disposition', $contentDisposition);

        return $response;
    }

    abstract protected function addFlash($type, $message);

    public function __toString()
    {
        return 'sludio_helper.lexik.crud.controller';
    }

    protected function getManagedLocales()
    {
        return $this->container->getParameter('lexik_translation.managed_locales');
    }
}
