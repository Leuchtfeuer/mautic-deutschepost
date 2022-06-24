<?php

namespace MauticPlugin\MauticTriggerdialogBundle\Entity;

use Mautic\CoreBundle\Entity\CommonRepository;

class TriggerCampaignRepository extends CommonRepository
{
    public const ALIAS = 'td';

    /**
     * {@inheritdoc}
     */
    public function getTableAlias(): string
    {
        return self::ALIAS;
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefaultOrder(): array
    {
        return [
            [
                self::ALIAS.'.name',
                'ASC',
            ],
        ];
    }
}
