<?php

namespace Sludio\HelperBundle\Translatable\Form\Type;

use Sludio\HelperBundle\Translatable\Helper\Manager;
use Sonata\AdminBundle\Admin\AdminInterface;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatorInterface;

class TranslatorType extends AbstractType
{
    const DEFAULT_CLASS = '';
    const DEFAULT_TYPE = 'text';
    protected $manager;
    protected $container;
    /**
     * @var array
     */
    private $locales;
    private $userLocale;

    public function __construct($locales, Manager $manager, TranslatorInterface $translator, $container)
    {
        $this->manager = $manager;
        $this->locales = $locales;
        $this->userLocale = $translator->getLocale();
        $this->container = $container;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     *
     * @throws InvalidConfigurationException
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var AdminInterface $admin */
        $admin = $options['sonata_field_description']->getAdmin();

        $entities = $this->container->getParameter('sludio_helper.translatable.entities');
        $entity = null;
        $className = $admin->getClass();
        /** @var $entities array */
        foreach ($entities as $key => &$entity) {
            $entity['name'] = $key;
            if ($entity['entity'] === $className) {
                break;
            }
        }

        $under = 'sludio_helper.extensions.translatable.entities';
        if ($entity === null || $entity['entity'] !== $className) {
            throw new InvalidConfigurationException(sprintf('Entity %s not defined under %s', $className, $under));
        }

        $id = $admin->getSubject()->getId();
        $fieldName = $builder->getName();

        if (!$this->checkOptions($entity, $fieldName)) {
            throw new InvalidConfigurationException(sprintf('No fields defined or fields missing for %s under %s.%s', $className, $under, $entity['name']));
        }

        $fieldType = $entity['fields'][$fieldName]['type'];
        $class = $entity['fields'][$fieldName]['class'];
        $required = $options['required'];

        if (!$id) {
            $translations = $this->manager->getNewTranslatedFields($fieldName, $this->locales);
        } else {
            $translations = $this->manager->getTranslatedFields($className, $fieldName, $id, $this->locales);
        }

        $this->addPreSetDataListener($builder, $fieldName, $translations, $fieldType, $class, $required, $className, $id);
        $this->addPostSubmitListener($builder, $fieldName, $className, $id);
    }

    private function addPreSetDataListener(FormBuilderInterface $builder, $fieldName, $translations, $fieldType, $class, $required, $className, $id)
    {
        // 'populate' fields by *hook on form generation
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($fieldName, $translations, $fieldType, $class, $required, $className, $id) {
            $form = $event->getForm();
            foreach ($this->locales as $locale) {
                $data = (array_key_exists($locale, $translations) && array_key_exists($fieldName, $translations[$locale])) ? $translations[$locale][$fieldName] : null;
                $form->add($locale, $fieldType, [
                    'label' => false,
                    'data' => $data,
                    'required' => $required,
                    'attr' => [
                        'class' => $class,
                        'data-locale' => $locale,
                        'data-class' => $className,
                        'data-id' => $id,
                    ],
                ]);
            }

            // extra field for twig rendering
            $form->add('currentFieldName', 'hidden', ['data' => $fieldName]);
        });
    }

    private function addPostSubmitListener(FormBuilderInterface $builder, $fieldName, $className, $id)
    {
        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) use ($fieldName, $className, $id) {
            $form = $event->getForm();
            $this->manager->persistTranslations($form, $className, $fieldName, $id, $this->locales);
        });
    }

    private function checkOptions(array $object, $field)
    {
        if (!isset($object['fields'][$field])) {
            return false;
        }

        $fields = [
            'class',
            'type',
        ];
        foreach ($fields as $type) {
            if (!isset($object['fields'][$field][$type])) {
                $object['fields'][$field][$type] = \constant('self::DEFAULT_'.strtoupper($type));
            }
        }

        return true;
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        // pass some variables for field rendering
        $view->vars['locales'] = $this->locales;
        $view->vars['currentlocale'] = $this->userLocale;
        $view->vars['translatedtablocales'] = $this->getTabTranslations();
    }

    private function getTabTranslations()
    {
        $translatedLocaleCodes = [];
        foreach ($this->locales as $locale) {
            $translatedLocaleCodes[$locale] = $this->getTranslatedLocalCode($locale);
        }

        return $translatedLocaleCodes;
    }

    private function getTranslatedLocalCode($locale)
    {
        return \Locale::getDisplayLanguage($locale, $this->userLocale);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->getBlockPrefix();
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'translations';
    }

    /**
     * {@inheritdoc}
     * @throws \Symfony\Component\OptionsResolver\Exception\AccessException
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $defaults = [
            'mapped' => false,
            'required' => false,
            'by_reference' => false,
        ];
        $resolver->setDefaults($defaults);
    }
}
