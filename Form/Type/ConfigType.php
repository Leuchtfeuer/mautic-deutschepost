<?php
namespace MauticPlugin\MauticTriggerdialogBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class ConfigType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('triggerdialog_masId', IntegerType::class, [
            'label' => 'plugin.triggerdialog.form.masId',
            'label_attr' => [
                'class' => 'control-label',
            ],
            'required' => true,
            'attr' => [
                'class' => 'form-control',
                'rows' => '6',
            ],
        ]);

        $builder->add('triggerdialog_masClientId', TextType::class, [
            'label' => 'plugin.triggerdialog.form.masClientId',
            'label_attr' => [
                'class' => 'control-label',
            ],
            'required' => true,
            'attr' => [
                'class' => 'form-control',
                'rows' => '6',
                'readonly' => 'readonly',
            ],
        ]);

        $builder->add('triggerdialog_masSecret', TextType::class, [
            'label' => 'plugin.triggerdialog.form.masSecret',
            'label_attr' => [
                'class' => 'control-label',
            ],
            'required' => true,
            'attr' => [
                'class' => 'form-control',
                'rows' => '6',
            ],
        ]);

        $builder->add('triggerdialog_email', TextType::class, [
            'label' => 'plugin.triggerdialog.form.email',
            'label_attr' => [
                'class' => 'control-label',
            ],
            'required' => true,
            'attr' => [
                'class' => 'form-control',
                'rows' => '6',
            ],
        ]);

        $builder->add('triggerdialog_username', TextType::class, [
            'label' => 'plugin.triggerdialog.form.username',
            'label_attr' => [
                'class' => 'control-label',
            ],
            'required' => true,
            'attr' => [
                'class' => 'form-control',
                'rows' => '6',
            ],
        ]);

        $builder->add('triggerdialog_firstName', TextType::class, [
            'label' => 'plugin.triggerdialog.form.firstName',
            'label_attr' => [
                'class' => 'control-label',
            ],
            'attr' => [
                'class' => 'form-control',
                'rows' => '6',
            ],
        ]);

        $builder->add('triggerdialog_lastName', TextType::class, [
            'label' => 'plugin.triggerdialog.form.lastName',
            'label_attr' => [
                'class' => 'control-label',
            ],
            'attr' => [
                'class' => 'form-control',
                'rows' => '6',
            ],
        ]);

        $builder->add('triggerdialog_rest_user', TextType::class, [
            'label' => 'plugin.triggerdialog.form.rest.user',
            'label_attr' => [
                'class' => 'control-label',
            ],
            'required' => true,
            'attr' => [
                'class' => 'form-control',
                'rows' => '6',
            ],
        ]);

        $builder->add('triggerdialog_rest_password', TextType::class, [
            'label' => 'plugin.triggerdialog.form.rest.password',
            'label_attr' => [
                'class' => 'control-label',
            ],
            'required' => true,
            'attr' => [
                'class' => 'form-control',
                'rows' => '6',
                'placeholder' => '********',
            ],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'triggerdialogconfig';
    }
}
