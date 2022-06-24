<?php

namespace MauticPlugin\MauticTriggerdialogBundle\Form\Type;

use Mautic\CoreBundle\Factory\MauticFactory;
use Mautic\CoreBundle\Form\EventListener\CleanFormSubscriber;
use Mautic\CoreBundle\Form\EventListener\FormExitSubscriber;
use Mautic\CoreBundle\Form\Type\FormButtonsType;
use Mautic\CoreBundle\Form\Type\YesNoButtonGroupType;
use Mautic\CoreBundle\Form\Validator\Constraints\CircularDependency;
use Mautic\CoreBundle\Security\Permissions\CorePermissions;
use Mautic\CoreBundle\Translation\Translator;
use Mautic\LeadBundle\Form\DataTransformer\FieldFilterTransformer;
use Mautic\LeadBundle\Model\ListModel;
use MauticPlugin\MauticTriggerdialogBundle\Controller\TriggerCampaignController;
use MauticPlugin\MauticTriggerdialogBundle\Entity\TriggerCampaign;
use MauticPlugin\MauticTriggerdialogBundle\Model\TriggerCampaignModel;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class TriggerCampaignType extends AbstractType
{
    /**
     * @var Translator
     */
    protected $translator;

    /**
     * @var CorePermissions
     */
    protected $security;

    /**
     * @var MauticFactory
     */
    protected $factory;

    /**
     * @var array
     */
    protected $fieldChoices = [];

    /**
     * TriggerCampaignType constructor.
     */
    public function __construct(MauticFactory $factory, ListModel $listModel)
    {
        $this->translator   = $factory->getTranslator();
        $this->security     = $factory->getSecurity();
        $this->factory      = $factory;
        $this->fieldChoices = $listModel->getChoiceFields();
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventSubscriber(new CleanFormSubscriber(['description' => 'html']));
        $builder->addEventSubscriber(new FormExitSubscriber(TriggerCampaignModel::NAME, $options));

        $builder->add('buttons', FormButtonsType::class);

        if (!empty($options['action'])) {
            $builder->setAction($options['action']);
        }

        $builder->add('name', TextType::class, [
            'label'      => 'mautic.core.name',
            'label_attr' => [
                'class' => 'control-label',
            ],
            'attr' => [
                'class'    => 'form-control',
                'required' => 'required',
            ],
        ]);

        $builder->add('description', TextareaType::class, [
            'label'      => 'mautic.core.description',
            'label_attr' => [
                'class' => 'control-label',
            ],
            'attr' => [
                'class' => 'form-control editor',
            ],
            'required' => false,
        ]);

        $builder->add('startDate', DateType::class, [
            'widget'     => 'single_text',
            'label'      => 'mautic.core.form.publishup',
            'label_attr' => [
                'class' => 'control-label',
            ],
            'attr' => [
                'class'       => 'form-control',
                'data-toggle' => 'date',
            ],
            'format'   => 'yyyy-MM-dd',
            'required' => true,
        ]);

        $builder->add('endDate', DateType::class, [
            'widget'     => 'single_text',
            'label'      => 'mautic.core.form.publishdown',
            'label_attr' => [
                'class' => 'control-label',
            ],
            'attr' => [
                'class'       => 'form-control',
                'data-toggle' => 'date',
            ],
            'format'   => 'yyyy-MM-dd',
            'required' => false,
        ]);

        if (!empty($options['data']) && $options['data']->getId()) {
            $readonly = !$this->security->hasEntityAccess(
                TriggerCampaignController::PERMISSIONS['publish'],
                TriggerCampaignController::PERMISSIONS['publish'],
                $options['data']->getCreatedBy()
            );

            $data = $options['data']->isPublished(false);
        } elseif (!$this->security->isGranted(TriggerCampaignController::PERMISSIONS['publish'])) {
            $readonly = true;
            $data     = false;
        } else {
            $readonly = false;
            $data     = true;
        }

        $builder->add('isPublished', YesNoButtonGroupType::class, [
            'attr' => [
                'readonly' => $readonly,
            ],
            'data' => $data,
        ]);

        $builder->add(
            $builder->create('variables', CollectionType::class, [
                'entry_type'    => VariableType::class,
                'entry_options' => [
                    'label' => false,
                    'attr'  => $this->fieldChoices,
                ],
                'error_bubbling' => false,
                'mapped'         => true,
                'allow_add'      => true,
                'allow_delete'   => true,
                'label'          => false,
                'constraints'    => [
                    new CircularDependency([
                        'message' => 'mautic.core.segment.circular_dependency_exists',
                    ]),
                ],
            ])->addModelTransformer(new FieldFilterTransformer($this->translator))
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'trigger_campaign';
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => TriggerCampaign::class,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['fields'] = $this->fieldChoices;
    }
}
