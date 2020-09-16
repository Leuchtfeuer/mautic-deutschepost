<?php
namespace MauticPlugin\MauticTriggerdialogBundle\Form\Type;

use MauticPlugin\MauticTriggerdialogBundle\Entity\TriggerCampaign;
use MauticPlugin\MauticTriggerdialogBundle\Model\TriggerCampaignModel;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class ActionType extends AbstractType
{
    /**
     * @var array
     */
    protected $fieldChoices;

    /**
     * ActionType constructor.
     *
     * @param TriggerCampaignModel $triggerCampaignModel
     */
    public function __construct(TriggerCampaignModel $triggerCampaignModel)
    {
        $triggerCampaigns = $triggerCampaignModel->getEntities();
        $fieldChoices = [];

        /** @var TriggerCampaign $triggerCampaign */
        foreach ($triggerCampaigns as $triggerCampaign) {
            $fieldChoices[$triggerCampaign->getId()] = $triggerCampaign->getName();
        }

        $this->fieldChoices = $fieldChoices;
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
    public function getName()
    {
        return 'trigger_action';
    }
}
