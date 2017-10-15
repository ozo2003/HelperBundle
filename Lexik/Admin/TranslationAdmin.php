<?php

namespace Sludio\HelperBundle\Lexik\Admin;

use Sonata\AdminBundle\Route\RouteCollection;
use Lexik\Bundle\TranslationBundle\Manager\TransUnitManagerInterface;
use Sonata\AdminBundle\Model\ModelManagerInterface;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\Extension\Core\Type;

abstract class TranslationAdmin extends AbstractAdmin
{
    /**
     * @var TransUnitManagerInterface
     */
    protected $transUnitManager;
    /**
     * @var array
     */
    protected $editableOptions;

    /**
     * @var array
     */
    protected $defaultSelections = [];

    /**
     * @var array
     */
    protected $emptyFieldPrefixes = [];

    /**
     * @var array
     */
    protected $filterLocales = [];

    /**
     * @var array
     */
    protected $managedLocales = [];

    /**
     * @param array $options
     */
    public function setEditableOptions(array $options)
    {
        $this->editableOptions = $options;
    }

    /**
     * @param TransUnitManagerInterface $translationManager
     */
    public function setTransUnitManager(TransUnitManagerInterface $translationManager)
    {
        $this->transUnitManager = $translationManager;
    }

    /**
     * @param array $managedLocales
     */
    public function setManagedLocales(array $managedLocales)
    {
        $this->managedLocales = $managedLocales;
    }

    /**
     * @return array
     */
    public function getEmptyFieldPrefixes()
    {
        return $this->emptyFieldPrefixes;
    }

    /**
     * @return array
     */
    public function getDefaultSelections()
    {
        return $this->defaultSelections;
    }

    /**
     * @return array
     */
    public function getNonTranslatedOnly()
    {
        return array_key_exists('non_translated_only', $this->getDefaultSelections()) && (bool)$this->defaultSelections['nonTranslatedOnly'];
    }

    /**
     * @param array $selections
     */
    public function setDefaultSelections(array $selections)
    {
        $this->defaultSelections = $selections;
    }

    /**
     * @param array $prefixes
     */
    public function setEmptyPrefixes(array $prefixes)
    {
        $this->emptyFieldPrefixes = $prefixes;
    }

    /**
     * @return array
     */
    public function getFilterParameters()
    {
        if ($this->getDefaultDomain()) {
            $this->datagridValues = array_merge([
                'domain' => [
                    'value' => $this->getDefaultDomain(),
                ],
            ], $this->datagridValues

            );
        }

        return parent::getFilterParameters();
    }

    /**
     * @param string $name
     *
     * @return array|NULL
     */
    public function getTemplate($name)
    {
        if ($name === 'list') {
            return 'SludioHelperBundle:Lexik:CRUD\list.html.twig';
        }

        return parent::getTemplate($name);
    }

    /**
     * @param string $name
     *
     * @return string
     */
    public function getOriginalTemplate($name)
    {
        return parent::getTemplate($name);
    }

    /**
     * @param RouteCollection $collection
     */
    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->add('clear_cache')->add('create_trans_unit');
    }

    /**
     * @param ListMapper $list
     */
    protected function configureListFields(ListMapper $list)
    {
        $list->add('id', Type\IntegerType::class)
            ->add('key', Type\TextType::class)
            ->add('domain', Type\TextType::class)
        ;

        $localesToShow = count($this->filterLocales) > 0 ? $this->filterLocales : $this->managedLocales;

        foreach ($localesToShow as $locale) {
            $fieldDescription = $this->modelManager->getNewFieldDescriptionInstance($this->getClass(), $locale);
            $fieldDescription->setTemplate('SludioHelperBundle:Lexik:CRUD/base_inline_translation_field.html.twig');
            $fieldDescription->setOption('locale', $locale);
            $fieldDescription->setOption('editable', $this->editableOptions);
            $list->add($fieldDescription);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function buildDatagrid()
    {
        if ($this->datagrid) {
            return;
        }

        $filterParameters = $this->getFilterParameters();

        // transform _sort_by from a string to a FieldDescriptionInterface for the datagrid.
        if (isset($filterParameters['locale']) && is_array($filterParameters['locale'])) {
            $this->filterLocales = array_key_exists('value', $filterParameters['locale']) ? $filterParameters['locale']['value'] : $this->managedLocales;
        }

        parent::buildDatagrid();
    }

    /**
     * @param FormMapper $form
     */
    protected function configureFormFields(FormMapper $form)
    {
        $subject = $this->getSubject();

        if (null === $subject->getId()) {
            $subject->setDomain($this->getDefaultDomain());
        }

        $form->add('key', Type\TextareaType::class)->add('domain', Type\TextareaType::class);
    }

    /**
     * @return ContainerInterface
     */
    protected function getContainer()
    {
        return $this->getConfigurationPool()->getContainer();
    }

    /**
     * @return string
     */
    protected function getDefaultDomain()
    {
        return $this->getContainer()->getParameter('sludio_helper.lexik.default_domain');
    }

    /**
     * {@inheritdoc}
     */
    public function getBatchActions()
    {
        $actions = parent::getBatchActions();
        $actions['download'] = [
            'label' => $this->trans($this->getLabelTranslatorStrategy()
                ->getLabel('download', 'batch', 'SludioHelperBundle')),
            'ask_confirmation' => false,
        ];

        return $actions;
    }

    public function initialize()
    {
        parent::initialize();
        $this->managedLocales = $this->getContainer()->getParameter('lexik_translation.managed_locales');
    }
}