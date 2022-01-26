<?php
namespace MauticPlugin\MauticTriggerdialogBundle\Form\Type;

use MauticPlugin\MauticTriggerdialogBundle\Model\TriggerCampaignModel;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class ActionType extends AbstractType
{
    protected $fieldChoices = [];

    /**
     * ActionType constructor.
     */
    public function __construct(TriggerCampaignModel $triggerCampaignModel)
    {
        $triggerCampaigns = $triggerCampaignModel->getEntities();

        foreach ($triggerCampaigns as $triggerCampaign) {
            $this->fieldChoices[$triggerCampaign->getId()] = $triggerCampaign->getName();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('trigger_campaign', ChoiceType::class, [
            'choices' => array_flip($this->fieldChoices),
            'label' => 'plugin.triggerdialog.campaign.formlabel',
            'label_attr' => ['class' => 'control-label'],
            'required' => true,
            'multiple' => false,
            'expanded' => false,
            'attr' => [
                'class' => 'form-control',
                'data-sortable' => 'true',
            ],
            'constraints' => [
                new NotBlank(
                    ['message' => 'todo.add.me']
                ),
            ],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'trigger_action';
    }
}
