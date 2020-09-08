<?php
namespace MauticPlugin\MauticTriggerdialogBundle\Entity;

use Mautic\CoreBundle\Entity\CommonRepository;

class TriggerCampaignRepository extends CommonRepository
{
    const ALIAS = 'td';

    /**
     * {@inheritdoc}
     */
    public function getTableAlias()
    {
        return self::ALIAS;
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefaultOrder()
    {
        return [
            [
                self::ALIAS . '.name',
                'ASC',
            ],
        ];
    }
}
