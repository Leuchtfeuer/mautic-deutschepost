<?php

use Mautic\CoreBundle\Templating\Engine\PhpEngine;
use MauticPlugin\LeuchtfeuerPrintmailingBundle\Controller\TriggerCampaignController;
use MauticPlugin\LeuchtfeuerPrintmailingBundle\Entity\TriggerCampaignRepository;
use MauticPlugin\LeuchtfeuerPrintmailingBundle\Model\TriggerCampaignModel;

/**
 * @var string    $template
 * @var PhpEngine $view
 * @var array     $items
 * @var int       $page
 * @var int       $limit
 * @var array     $permissions
 * @var bool      $configInvalid
 */
if ('index' === $template) {
    $view->extend(TriggerCampaignController::TEMPLATES['index'].'.html.php');
}
?>

<?php if (count($items)): ?>
    <div class="table-responsive page-list">
        <table class="table table-hover table-striped table-bordered triggercampaign-list" id="triggerCampaignTable">
            <thead>
            <tr>
                <?php
                echo $view->render('MauticCoreBundle:Helper:tableheader.html.php', [
                    'checkall'        => 'true',
                    'target'          => '#triggerCampaignTable',
                    'routeBase'       => TriggerCampaignController::MAUTIC_CONTENT,
                    'templateButtons' => [
                        'delete' => $permissions[TriggerCampaignController::PERMISSIONS['delete']],
                    ],
                ]);

                echo $view->render('MauticCoreBundle:Helper:tableheader.html.php', [
                    'sessionVar' => 'plugin.printmailing',
                    'orderBy'    => TriggerCampaignRepository::ALIAS.'.name',
                    'text'       => 'mautic.core.name',
                    'class'      => 'col-triggercampaign-name',
                    'default'    => true,
                ]);

                echo $view->render('MauticCoreBundle:Helper:tableheader.html.php', [
                    'sessionVar' => 'plugin.printmailing',
                    'orderBy'    => TriggerCampaignRepository::ALIAS.'.id',
                    'text'       => 'mautic.core.id',
                    'class'      => 'visible-md visible-lg col-triggercampaign-id',
                ]);
                ?>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($items as $item): ?>
                <tr>
                    <td>
                        <?php
                        if (false === $configInvalid) {
                            echo $view->render('MauticCoreBundle:Helper:list_actions.html.php', [
                                'item'            => $item,
                                'templateButtons' => [
                                    'edit'   => $permissions[TriggerCampaignController::PERMISSIONS['edit']],
                                    'clone'  => $permissions[TriggerCampaignController::PERMISSIONS['create']],
                                    'delete' => $permissions[TriggerCampaignController::PERMISSIONS['delete']],
                                ],
                                'routeBase' => TriggerCampaignController::MAUTIC_CONTENT,
                            ]);
                        }
                        ?>
                    </td>
                    <td>
                        <div>

                            <?php
                            if (false === $configInvalid) {
                                echo $view->render('MauticCoreBundle:Helper:publishstatus_icon.html.php', [
                                    'item'  => $item,
                                    'model' => TriggerCampaignModel::NAME,
                                ]);
                            }
                            ?>
                            <?php if ($permissions[TriggerCampaignController::PERMISSIONS['edit']] && false === $configInvalid): ?>
                                <a href="<?php echo $view['router']->url(TriggerCampaignController::ROUTES['action'], ['objectAction' => 'edit', 'objectId' => $item->getId()]); ?>" data-toggle="ajax">
                                    <?php echo $item->getName(); ?>
                                </a>
                            <?php else: ?>
                                <?php echo $item->getName(); ?>
                            <?php endif; ?>
                        </div>
                        <?php if ($description = $item->getDescription()): ?>
                            <div class="text-muted mt-4">
                                <small><?php echo $description; ?></small>
                            </div>
                        <?php endif; ?>
                    </td>
                    <td class="visible-md visible-lg"><?php echo $item->getId(); ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="panel-footer">
        <?php echo $view->render('MauticCoreBundle:Helper:pagination.html.php', [
            'totalItems' => count($items),
            'page'       => $page,
            'limit'      => $limit,
            'menuLinkId' => TriggerCampaignController::ROUTES['index'],
            'baseUrl'    => $view['router']->url(TriggerCampaignController::ROUTES['index']),
            'sessionVar' => 'plugin.printmailing',
        ]); ?>
    </div>
<?php else: ?>
    <?php echo $view->render('MauticCoreBundle:Helper:noresults.html.php'); ?>
<?php endif; ?>

