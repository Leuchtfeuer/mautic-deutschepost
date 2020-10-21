<?php

use Mautic\CoreBundle\Templating\Engine\PhpEngine;
use Mautic\LeadBundle\Entity\LeadField;
use MauticPlugin\MauticTriggerdialogBundle\Controller\TriggerCampaignController;
use MauticPlugin\MauticTriggerdialogBundle\Entity\TriggerCampaign;
use Symfony\Component\Form\FormView;

/**
 * @var PhpEngine       $view
 * @var TriggerCampaign $entity
 * @var FormView        $form
 * @var LeadField[]     $fields
 * @var array           $formFields
 */

echo $view['assets']->includeScript('plugins/MauticTriggerdialogBundle/Assets/js/triggerdialog.js', 'td', 'td');
$view->extend('MauticCoreBundle:Default:content.html.php');
$view['slots']->set('mauticContent', TriggerCampaignController::MAUTIC_CONTENT);
$fields = $form->vars['fields'];
$id = $form->vars['data']->getId();
$index = count($form['variables']->vars['value']) ? max(array_keys($form['variables']->vars['value'])) : 0;
$mainErrors = ($view['form']->containsErrors($form, ['variables'])) ? 'class="text-danger"' : '';
$variableErrors = ($view['form']->containsErrors($form['variables'])) ? 'class="text-danger"' : '';

if (!empty($id)) {
    $header = $view['translator']->trans('plugin.triggerdialog.menu.edit', ['%name%' => $form->vars['data']->getName()]);
} else {
    $header = $view['translator']->trans('plugin.triggerdialog.menu.new');
}

$view['slots']->set('headerTitle', $header);

echo $view['form']->start($form);
?>
<div class="box-layout">
	<div class="col-md-9 bg-white height-auto">
		<div class="row">
			<div class="col-xs-12">
				<ul class="bg-auto nav nav-tabs pr-md pl-md">
					<li class="active">
						<a href="#details" role="tab" data-toggle="tab"<?php echo $mainErrors; ?>>
                            <?php echo $view['translator']->trans('plugin.triggerdialog.form.tab.details'); ?>
                            <?php if ($mainErrors): ?>
								<i class="fa fa-warning"></i>
                            <?php endif; ?>
						</a>
					</li>
					<li data-toggle="tooltip" title="" data-placement="top">
						<a href="#variables" role="tab" data-toggle="tab"<?php echo $variableErrors; ?>>
                            <?php echo $view['translator']->trans('plugin.triggerdialog.form.tab.variables'); ?>
                            <?php if ($variableErrors): ?>
								<i class="fa fa-warning"></i>
                            <?php endif; ?>
						</a>
					</li>
				</ul>
				<div class="tab-content pa-md">
					<div class="tab-pane fade in active bdr-w-0" id="details">
						<div class="row">
							<div class="col-md-6">
                                <?php echo $view['form']->row($form['name']); ?>
							</div>
						</div>
						<div class="row">
							<div class="col-xs-12">
                                <?php echo $view['form']->row($form['description']); ?>
							</div>
						</div>
					</div>
					<div class="tab-pane fade bdr-w-0" id="variables">
						<div class="alert alert-info" role="alert">
                            <?php echo $view['translator']->trans('plugin.triggerdialog.form.tab.variables.hint'); ?>
						</div>

						<div class="form-group">
							<div class="available-variables mb-md pl-0 col-md-4" data-prototype="<?php echo $view->escape($view['form']->widget($form['variables']->vars['prototype'])); ?>" data-index="<?php echo $index + 1; ?>">
								<select class="chosen form-control" id="available_variables">
									<option value=""></option>
                                    <?php
                                    foreach ($fields as $object => $field):
                                        $header = $object;
                                        $icon = ($object === 'company') ? 'building' : 'user';
                                        ?>

										<optgroup label="<?php echo $view['translator']->trans('mautic.lead.' . $header); ?>">
                                            <?php foreach ($field as $value => $params):
                                                $list = (!empty($params['properties']['list'])) ? $params['properties']['list'] : [];
                                                $choices = \Mautic\LeadBundle\Helper\FormFieldHelper::parseList($list, true, ('boolean' === $params['properties']['type']));
                                                $list = json_encode($choices);
                                                $callback = (!empty($params['properties']['callback'])) ? $params['properties']['callback'] : '';
                                                $operators = (!empty($params['operators'])) ? $view->escape(json_encode($params['operators'])) : '{}';
                                                ?>
												<option value="<?php echo $view->escape($value); ?>"
												        id="available_<?php echo $object . '_' . $value; ?>"
												        data-field-object="<?php echo $object; ?>"
												        data-field-type="<?php echo $params['properties']['type']; ?>"
												        data-field-list="<?php echo $view->escape($list); ?>"
												        data-field-callback="<?php echo $callback; ?>"
												        data-field-operators="<?php echo $operators; ?>"
												        class="segment-filter <?php echo $icon; ?>">
                                                    <?php echo $view['translator']->trans($params['label']); ?>
												</option>
                                            <?php endforeach; ?>
										</optgroup>
                                    <?php endforeach; ?>
								</select>
							</div>
							<div class="clearfix"></div>
						</div>

						<div class="selected-variables" id="trigger_campaign_variables">
                            <?php if ($variableErrors): ?>
								<div class="alert alert-danger has-error">
                                    <?php echo $view['form']->errors($form['variables']); ?>
								</div>
                            <?php endif ?>
                            <?php echo $view['form']->widget($form['variables']); ?>
						</div>
					</div>

				</div>
			</div>
		</div>
	</div>
	<div class="col-md-3 bg-white height-auto bdr-l">
		<div class="pr-lg pl-lg pt-md pb-md">
            <?php
            echo $view['form']->row($form['isPublished']);
            echo $view['form']->row($form['startDate']);
            echo $view['form']->row($form['endDate']);
            ?>
		</div>
	</div>
</div>
<?php echo $view['form']->end($form); ?>