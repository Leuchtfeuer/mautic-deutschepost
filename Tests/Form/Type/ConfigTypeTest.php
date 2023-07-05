<?php

declare(strict_types=1);

namespace MauticPlugin\LeuchtfeuerPrintmailingBundle\Tests\Form\Type;

use MauticPlugin\LeuchtfeuerPrintmailingBundle\Form\Type\ConfigType;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\TypeTestCase;

class ConfigTypeTest extends TypeTestCase
{
    /**
     * @return PreloadedExtension[]
     */
    protected function getExtensions(): array
    {
        $type = new ConfigType();

        return [
            new PreloadedExtension([$type], []),
        ];
    }

    public function testFieldsAreNotFilledIn(): void
    {
        $dataToForm = [
            'printmailing_masId'         => '123',
            'printmailing_masClientId'   => 'masClientId',
            'printmailing_masSecret'     => 'masSecret',
            'printmailing_rest_user'     => 'rest_user',
            'printmailing_rest_password' => 'rest_password',
        ];

        $form = $this->factory->create(ConfigType::class, $dataToForm);
        $view = $form->createView();

        self::assertSame('123', $view->children['printmailing_masId']->vars['value']);
        self::assertSame('masClientId', $view->children['printmailing_masClientId']->vars['value']);
        self::assertSame('', $view->children['printmailing_masSecret']->vars['value']);
        self::assertSame('rest_user', $view->children['printmailing_rest_user']->vars['value']);
        self::assertSame('', $view->children['printmailing_rest_password']->vars['value']);
    }

    public function testFieldsAreSaved(): void
    {
        $dataToForm = [
            'printmailing_masId'         => '123',
            'printmailing_masClientId'   => 'masClientId',
            'printmailing_masSecret'     => 'masSecret',
            'printmailing_rest_user'     => 'rest_user',
            'printmailing_rest_password' => 'rest_password',
        ];

        $form = $this->factory->create(ConfigType::class, $dataToForm);

        $formData = [
            'printmailing_masId'         => '321',
            'printmailing_masClientId'   => 'masClientId_1',
            'printmailing_masSecret'     => 'masSecret_1',
            'printmailing_rest_user'     => 'rest_user_1',
            'printmailing_rest_password' => 'rest_password_1',
        ];

        $form->submit($formData);

        $formData['printmailing_masId'] = (int) $formData['printmailing_masId'];

        self::assertTrue($form->isSynchronized());

        self::assertSame($formData, $form->getData());
        self::assertTrue($form->isValid());
    }
}
