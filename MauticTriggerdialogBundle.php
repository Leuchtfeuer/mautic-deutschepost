<?php

namespace MauticPlugin\MauticTriggerdialogBundle;

use Doctrine\DBAL\Schema\Schema;
use Mautic\CoreBundle\Factory\MauticFactory;
use Mautic\PluginBundle\Bundle\PluginBundleBase;
use Mautic\PluginBundle\Entity\Plugin;

class MauticTriggerdialogBundle extends PluginBundleBase
{
    public static function onPluginUpdate(Plugin $plugin, MauticFactory $factory, $metadata = null, Schema $installedSchema = null)
    {
        $database    = $factory->getDatabase();
        $platform    = $database->getDatabasePlatform()->getName();
        $queries     = [];
        $fromVersion = $plugin->getVersion();

        switch ($fromVersion) {
            case '1.0.0':
                switch ($platform) {
                    case 'mysql':
                        $queries[] = sprintf(
                            'ALTER TABLE %strigger_campaigns CHANGE end_date end_date datetime DEFAULT NULL COMMENT \'(DC2Type:datetime)\'',
                            MAUTIC_TABLE_PREFIX
                        );
                        break;
                }
                break;
        }

        if (!empty($queries)) {
            $database->beginTransaction();
            try {
                foreach ($queries as $query) {
                    $database->query($query);
                }
                $database->commit();
            } catch (\Exception $exception) {
                $database->rollBack();

                throw $exception;
            }
        }

        parent::updatePluginSchema($metadata, $installedSchema, $factory);
    }
}
