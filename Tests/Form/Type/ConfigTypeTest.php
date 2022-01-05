<?php
declare(strict_types=1);
namespace MauticPlugin\MauticTriggerdialogBundle\Tests\Form\Type;

use MauticPlugin\MauticTriggerdialogBundle\Form\Type\ConfigType;
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
            'triggerdialog_masId' => '123',
            'triggerdialog_masClientId' => 'masClientId',
            'triggerdialog_masSecret' => 'masSecret',
            'triggerdialog_rest_user' => 'rest_user',
            'triggerdialog_rest_password' => 'rest_password',
        ];

        $form = $this->factory->create(ConfigType::class, $dataToForm);
        $view = $form->createView();

        self::assertSame('123', $view->children['triggerdialog_masId']->vars['value']);
        self::assertSame('masClientId', $view->children['triggerdialog_masClientId']->vars['value']);
        self::assertSame('', $view->children['triggerdialog_masSecret']->vars['value']);
        self::assertSame('rest_user', $view->children['triggerdialog_rest_user']->vars['value']);
        self::assertSame('', $view->children['triggerdialog_rest_password']->vars['value']);
    }

    public function testFieldsAreSaved(): void
    {
        $dataToForm = [
            'triggerdialog_masId' => '123',
            'triggerdialog_masClientId' => 'masClientId',
            'triggerdialog_masSecret' => 'masSecret',
            'triggerdialog_rest_user' => 'rest_user',
            'triggerdialog_rest_password' => 'rest_password',
        ];

        $form = $this->factory->create(ConfigType::class, $dataToForm);

        $formData = [
            'triggerdialog_masId' => '321',
            'triggerdialog_masClientId' => 'masClientId_1',
            'triggerdialog_masSecret' => 'masSecret_1',
            'triggerdialog_rest_user' => 'rest_user_1',
            'triggerdialog_rest_password' => 'rest_password_1',
        ];

        $form->submit($formData);

        $formData['triggerdialog_masId'] = (int) $formData['triggerdialog_masId'];

        self::assertTrue($form->isSynchronized());

        self::assertSame($formData, $form->getData());
        self::assertTrue($form->isValid());
    }
}
