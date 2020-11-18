<?php

use Mautic\CoreBundle\Templating\Engine\PhpEngine;
use MauticPlugin\MauticTriggerdialogBundle\Controller\TriggerCampaignController;

/**
 * @var $view          PhpEngine
 * @var $permissions   array
 * @var $searchValue   string
 * @var $currentRoute  string
 * @var $ssoUrl        string
 * @var $configInvalid bool
 */

if (!empty($ssoUrl)) {
    $ssoButton = sprintf(
        '<a href="%s" target="_blank" class="ml-10 btn btn-primary">%s</a>',
        $ssoUrl,
        $view['translator']->trans('plugin.triggerdialog.open.triggerdialog')
    );
}

$view->extend('MauticCoreBundle:Default:content.html.php');
$view['slots']->set('mauticContent', TriggerCampaignController::MAUTIC_CONTENT);
$view['slots']->set('headerTitle', $view['translator']->trans('plugin.triggerdialog.menu.root'));


if ($configInvalid === false) {
    $view['slots']->set(
        'actions',
        $view->render('MauticCoreBundle:Helper:page_actions.html.php', [
            'templateButtons' => [
                'new' => $permissions[TriggerCampaignController::PERMISSIONS['create']],
            ],
            'routeBase' => TriggerCampaignController::MAUTIC_CONTENT,
        ]) . ($ssoButton ?? '')
    );
} else {
    $view['slots']->set('actions', ($ssoButton ?? ''));
}

?>

<?php if ($configInvalid === true): ?>
<div class="alert alert-warning" role="alert">
	<?php echo $view['translator']->trans('plugin.triggerdialog.config.invalid', ['%link%' => '<a href="' . $view['router']->url('mautic_config_action', ['objectAction' => 'edit']) . '#triggerdialog' . '">Configuration</a>']); ?>
</div>
<?php endif; ?>


<div class="panel panel-default bdr-t-wdh-0 mb-0">
    <?php echo $view->render(
        'MauticCoreBundle:Helper:list_toolbar.html.php',
        [
            'searchValue' => $searchValue,
            'searchHelp' => 'mautic.core.help.searchcommands',
            'action' => $currentRoute,
        ]
    ); ?>
    <div class="page-list">
        <?php $view['slots']->output('_content'); ?>
    </div>
</div>
