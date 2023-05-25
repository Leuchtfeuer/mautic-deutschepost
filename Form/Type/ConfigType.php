<?php

namespace MauticPlugin\LeuchtfeuerPrintmailingBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class ConfigType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('triggerdialog_masId', IntegerType::class, [
            'label'      => 'plugin.triggerdialog.form.masId',
            'label_attr' => [
                'class' => 'control-label',
            ],
            'required' => true,
            'attr'     => [
                'class' => 'form-control',
                'rows'  => '6',
            ],
        ]);

        $builder->add('triggerdialog_masClientId', TextType::class, [
            'label'      => 'plugin.triggerdialog.form.masClientId',
            'label_attr' => [
                'class' => 'control-label',
            ],
            'required' => true,
            'attr'     => [
                'class'    => 'form-control',
                'rows'     => '6',
                'readonly' => 'readonly',
            ],
        ]);

        $builder->add('triggerdialog_masSecret', PasswordType::class, [
            'label'      => 'plugin.triggerdialog.form.masSecret',
            'label_attr' => [
                'class' => 'control-label',
            ],
            'required' => true,
            'attr'     => [
                'class' => 'form-control',
                'rows'  => '6',
            ],
        ]);

        $builder->add('triggerdialog_rest_user', TextType::class, [
            'label'      => 'plugin.triggerdialog.form.rest.user',
            'label_attr' => [
                'class' => 'control-label',
            ],
            'required' => true,
            'attr'     => [
                'class' => 'form-control',
                'rows'  => '6',
            ],
        ]);

        $builder->add('triggerdialog_rest_password', PasswordType::class, [
            'label'      => 'plugin.triggerdialog.form.rest.password',
            'label_attr' => [
                'class' => 'control-label',
            ],
            'required' => true,
            'attr'     => [
                'class'       => 'form-control',
                'rows'        => '6',
                'placeholder' => '********',
            ],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'triggerdialogconfig';
    }
}
