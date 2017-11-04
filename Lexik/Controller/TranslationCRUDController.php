<?php

namespace Sludio\HelperBundle\Lexik\Controller;

use Doctrine\DBAL\DBALException;
use Sludio\HelperBundle\Lexik\Event\RemoveLocaleCacheEvent;
use Lexik\Bundle\TranslationBundle\Entity\TransUnit;
use Lexik\Bundle\TranslationBundle\Manager\TranslationInterface;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Symfony\Component\HttpFoundation\Response;
use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Yaml\Dumper;

class TranslationCRUDController extends CRUDController
{
    /**
     * Edit action
     *
     * @param int|string|null $id
     *
     * @return Response|RedirectResponse
     * @throws NotFoundHttpException If the object does not exist
     * @throws NotFoundHttpException If the object does not exist
     * @throws AccessDeniedException If access is not granted
     * @throws AccessDeniedException If access is not granted
     */
    public function editAction($id = null, Request $request = null)
    {
        if (!$request) {
            $request = $this->getRequest();
        }
        if (!$request->isMethod('POST')) {
            return $this->redirect($this->admin->generateUrl('list'));
        }

        /* @var $transUnit \Lexik\Bundle\TranslationBundle\Model\TransUnit */
        $transUnit = $this->get('lexik_translation.translation_storage')->getTransUnitById($id);
        if (!$transUnit) {
            throw new NotFoundHttpException(sprintf('unable to find the object with id : %s', $id));
        }

        if (false === $this->admin->isGranted('EDIT', $transUnit)) {
            return $this->renderJson([
                'message' => 'access denied',
            ], 403);
        }

        $this->admin->setSubject($transUnit);

        /* @var $transUnitManager \Lexik\Bundle\TranslationBundle\Manager\TransUnitManager */
        $transUnitManager = $this->get('lexik_translation.trans_unit.manager');

        $parameters = $this->getRequest()->request;

        $locale = $parameters->get('locale');
        $content = $parameters->get('value');

        if (!$locale) {
            return $this->renderJson([
                'message' => 'locale missing',
            ], 422);
        }

        /* @var $translation \Lexik\Bundle\TranslationBundle\Entity\Translation */
        if ($parameters->get('pk')) {
            $translation = $transUnitManager->updateTranslation($transUnit, $locale, $content, true);
        } else {
            $translation = $transUnitManager->addTranslation($transUnit, $locale, $content, null, true);
        }

        if ($request->query->get('clear_cache')) {
            $this->get('translator')->removeLocalesCacheFiles([$locale]);
        }

        return $this->renderJson([
            'key' => $transUnit->getKey(),
            'domain' => $transUnit->getDomain(),
            'pk' => $translation->getId(),
            'locale' => $translation->getLocale(),
            'value' => $translation->getContent(),
        ]);
    }

    /**
     * @return RedirectResponse|Response
     */
    public function createTransUnitAction()
    {
        $request = $this->getRequest();
        $parameters = $this->getRequest()->request;
        if (!$request->isMethod('POST')) {
            return $this->renderJson([
                'message' => 'method not allowed',
            ], 403);
        }
        $admin = $this->admin;
        if (false === $admin->isGranted('EDIT')) {
            return $this->renderJson([
                'message' => 'access denied',
            ], 403);
        }
        $keyName = $parameters->get('key');
        $domainName = $parameters->get('domain');
        if (!$keyName || !$domainName) {
            return $this->renderJson([
                'message' => 'missing key or domain',
            ], 422);
        }

        /* @var $transUnitManager \Lexik\Bundle\TranslationBundle\Manager\TransUnitManager */
        $transUnitManager = $this->get('lexik_translation.trans_unit.manager');
        $transUnit = $transUnitManager->create($keyName, $domainName, true);

        return $this->editAction($transUnit->getId());
    }

    /**
     * @return RedirectResponse
     */
    public function clearCacheAction()
    {
        $this->get('event_dispatcher')
            ->dispatch(RemoveLocaleCacheEvent::PRE_REMOVE_LOCAL_CACHE, new RemoveLocaleCacheEvent($this->getManagedLocales()))
        ;
        $this->get('translator')->removeLocalesCacheFiles($this->getManagedLocales());
        $this->get('event_dispatcher')
            ->dispatch(RemoveLocaleCacheEvent::POST_REMOVE_LOCAL_CACHE, new RemoveLocaleCacheEvent($this->getManagedLocales()))
        ;

        /** @var $session Session */
        $session = $this->get('session');
        $session->getFlashBag()->set('sonata_flash_success', 'translations.cache_removed');

        return $this->redirect($this->admin->generateUrl('list'));
    }

    /**
     * Execute a batch download
     *
     * @param ProxyQueryInterface $query
     *
     * @return RedirectResponse
     */
    public function batchActionDownload(ProxyQueryInterface $queryProxy)
    {
        $flashType = 'success';

        $dumper = new Dumper(4);

        $response = new StreamedResponse(function() use ($queryProxy, &$flashType, $dumper) {
            try {
                /**
                 * @var TransUnit $transUnit
                 */
                foreach ($queryProxy->getQuery()->iterate() as $pos => $object) {
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

    protected function getManagedLocales()
    {
        return $this->container->getParameter('lexik_translation.managed_locales');
    }

    public function __toString()
    {
        return 'sludio_helper.lexik.crud.controller';
    }
}
