<?php
namespace MauticPlugin\MauticTriggerdialogBundle\Form\Type;

use MauticPlugin\MauticTriggerdialogBundle\Entity\TriggerCampaign;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class VariableType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $formModifier = function (FormEvent $event, $eventName) {
            $this->buildFiltersForm($eventName, $event);
        };

        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) use ($formModifier) {
                $formModifier($event, FormEvents::PRE_SET_DATA);
            }
        );

        $builder->addEventListener(
            FormEvents::PRE_SUBMIT,
            function (FormEvent $event) use ($formModifier) {
                $formModifier($event, FormEvents::PRE_SUBMIT);
            }
        );

        $builder->add('field', HiddenType::class);
        $builder->add('object', HiddenType::class);
        $builder->add('type', HiddenType::class);
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setRequired([
            'fields',
        ]);

        $resolver->setDefaults([
            'label' => false,
            'error_bubbling' => false,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['attr'] = $options['attr'];
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'trigger_variable';
    }

    /**
     * @param string    $eventName
     * @param FormEvent $event
     */
    public function buildFiltersForm($eventName, FormEvent $event)
    {
        $data = $event->getData();

        $event->getForm()->add('variable', ChoiceType::class, [
            'label' => false,
            'attr' => [
                'class' => 'form-control',
            ],
            'data' => isset($data['variable']) ? $data['variable'] : '',
            'error_bubbling' => false,
            'choices' => TriggerCampaign::ALLOWED_TYPES,
            'multiple' => false,
        ]);

        if ($eventName == FormEvents::PRE_SUBMIT) {
            $event->setData($data);
        }
    }
}
