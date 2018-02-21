<?php

namespace Sludio\HelperBundle\Lexik\Admin;

use Doctrine\ORM\Query;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery;

class ORMTranslationAdmin extends TranslationAdmin
{
    private function getLocaleOptions()
    {
        return [
            'callback' => function (ProxyQuery $queryBuilder, $alias, $field, $options) {
                if (!isset($options['value']) || empty($options['value'])) {
                    return;
                }
                // use on to filter locales
                $this->joinTranslations($queryBuilder, $alias, $options['value']);
            },
            'field_options' => [
                'choices' => $this->formatLocales($this->managedLocales),
                'required' => false,
                'multiple' => true,
                'expanded' => false,
            ],
            'field_type' => 'choice',
        ];
    }

    private function getNonOptions()
    {
        return [
            'callback' => function (ProxyQuery $queryBuilder, $alias, $field, $options) {
                if (!isset($options['value']) || empty($options['value']) || false === $options['value']) {
                    return;
                }
                $this->joinTranslations($queryBuilder, $alias);

                foreach ($this->getEmptyFieldPrefixes() as $prefix) {
                    if (empty($prefix)) {
                        $queryBuilder->orWhere('translations.content IS NULL');
                    } else {
                        $queryBuilder->orWhere('translations.content LIKE :content')->setParameter('content', $prefix.'%');
                    }
                }
            },
            'field_options' => [
                'required' => true,
                'value' => $this->getNonTranslatedOnly(),
            ],
            'field_type' => 'checkbox',
        ];
    }

    private function getKeyOptions($domains)
    {
        return [
            'field_options' => [
                'choices' => $domains,
                'required' => true,
                'multiple' => false,
                'expanded' => false,
                'empty_data' => 'all',
            ],
            'field_type' => 'choice',
        ];
    }

    private function getContentOptions()
    {
        return [
            'callback' => function (ProxyQuery $queryBuilder, $alias, $field, $options) {
                if (!isset($options['value']) || empty($options['value'])) {
                    return;
                }
                $this->joinTranslations($queryBuilder, $alias);
                $queryBuilder->andWhere('translations.content LIKE :content')->setParameter('content', '%'.$options['value'].'%');
            },
            'field_type' => 'text',
            'label' => 'content',
        ];
    }

    protected function configureDatagridFilters(DatagridMapper $filter)
    {
        /** @var \Doctrine\ORM\EntityManager $entityManager */
        $entityManager = $this->getContainer()->get('doctrine')->getManagerForClass('Lexik\Bundle\TranslationBundle\Entity\File');

        $domains = [];
        $domainsQueryResult = $entityManager->createQueryBuilder()->select('DISTINCT t.domain')->from('\Lexik\Bundle\TranslationBundle\Entity\File', 't')->getQuery()->getResult(Query::HYDRATE_ARRAY);

        array_walk_recursive($domainsQueryResult, function ($domain) use (&$domains) {
            $domains[$domain] = $domain;
        });
        ksort($domains);

        // @formatter:off
        $filter
            ->add('locale', 'doctrine_orm_callback', $this->getLocaleOptions())
            ->add('show_non_translated_only', 'doctrine_orm_callback', $this->getNonOptions())
            ->add('key', 'doctrine_orm_string')->add('domain', 'doctrine_orm_choice', $this->getKeyOptions($domains))
            ->add('content', 'doctrine_orm_callback', $this->getContentOptions());
        // @formatter:on
    }

    /**
     * @param ProxyQuery $queryBuilder
     * @param String     $alias
     * @param array|null $locales
     */
    private function joinTranslations(ProxyQuery $queryBuilder, $alias, array $locales = null)
    {
        $alreadyJoined = false;
        $joins = $queryBuilder->getDQLPart('join');
        if (array_key_exists($alias, $joins)) {
            $joins = $joins[$alias];
            /** @var $joins array */
            foreach ($joins as $join) {
                if (strpos($join->__toString(), "$alias.translations ")) {
                    $alreadyJoined = true;
                }
            }
        }
        if (!$alreadyJoined) {
            if ($locales) {
                $queryBuilder->leftJoin(sprintf('%s.translations', $alias), 'translations', 'WITH', 'translations.locale in (:locales)');
                $queryBuilder->setParameter('locales', $locales);
            } else {
                $queryBuilder->leftJoin(sprintf('%s.translations', $alias), 'translations');
            }
        }
    }

    /**
     * @param array $locales
     *
     * @return array
     */
    private function formatLocales(array $locales)
    {
        $formattedLocales = [];
        array_walk_recursive($locales, function ($language) use (&$formattedLocales) {
            $formattedLocales[$language] = $language;
        });

        return $formattedLocales;
    }
}
