<?php

/**
 * @var FormView  $form
 * @var PhpEngine $view
 */

use Mautic\CoreBundle\Templating\Engine\PhpEngine;
use Symfony\Component\Form\FormView;

?>

<div class="panel panel-primary">
	<div class="panel-heading">
		<h3 class="panel-title"><?php echo $view['translator']->trans('mautic.config.tab.triggerdialogconfig'); ?></h3>
	</div>
	<div class="panel-body">
		<div class="row">
			<div class="col-md-6">
                <?php echo $view['form']->row($form->children['triggerdialog_masId']); ?>
			</div>
			<div class="col-md-6">
                <?php echo $view['form']->row($form->children['triggerdialog_masClientId']); ?>
			</div>
		</div>
		<hr />
		<div class="row">
			<div class="col-md-12">
                <?php echo $view['form']->row($form->children['triggerdialog_masSecret']); ?>
			</div>
		</div>
		<div class="row">
			<div class="col-md-6">
                <?php echo $view['form']->row($form->children['triggerdialog_username']); ?>
			</div>
			<div class="col-md-6">
                <?php echo $view['form']->row($form->children['triggerdialog_email']); ?>
			</div>
		</div>
		<div class="row">
			<div class="col-md-6">
                <?php echo $view['form']->row($form->children['triggerdialog_firstName']); ?>
			</div>
			<div class="col-md-6">
                <?php echo $view['form']->row($form->children['triggerdialog_lastName']); ?>
			</div>
		</div>
	</div>
</div>

<div class="panel panel-primary">
	<div class="panel-heading">
		<h3 class="panel-title"><?php echo $view['translator']->trans('mautic.config.tab.triggerdialogconfig.rest'); ?></h3>
	</div>
	<div class="panel-body">
		<div class="row">
			<div class="col-md-6">
                <?php echo $view['form']->row($form->children['triggerdialog_rest_user']); ?>
			</div>
			<div class="col-md-6">
                <?php echo $view['form']->row($form->children['triggerdialog_authenticationSecret']); ?>
			</div>
		</div>
	</div>
</div>
