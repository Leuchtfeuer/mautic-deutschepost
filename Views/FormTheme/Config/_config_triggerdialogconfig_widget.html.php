<?php

/**
 * @var FormView  $form
 * @var PhpEngine $view
 */

use Mautic\CoreBundle\Templating\Engine\PhpEngine;
use Symfony\Component\Form\FormView;

$fields    = $form->children;

$message = $view['translator']->trans('plugin.printmailing.form.contract.email', [
    '%masId%'       => $_ENV['MAUTIC_TRIGGERDIALOG_MASID'],
    '%masClientId%' => $_ENV['MAUTIC_TRIGGERDIALOG_MASCLIENTID'],
]);
$subject = $view['translator']->trans('plugin.printmailing.form.contract.subject', ['%masId%' => $_ENV['MAUTIC_TRIGGERDIALOG_MASID']]);
?>

<div class="panel panel-primary">
	<div class="panel-heading">
		<h3 class="panel-title"><?php echo $view['translator']->trans('mautic.config.tab.printmailingconfig'); ?></h3>
	</div>
	<div class="panel-body">
		<div class="row">
			<div class="col-md-6 form-group">
				<p><?php echo $view['translator']->trans('plugin.printmailing.form.contract.help'); ?></p>
				<p><a href="<?php echo $view['translator']->trans('plugin.printmailing.form.details.link'); ?>" target="_blank"><?php echo $view['translator']->trans('plugin.printmailing.form.tab.details'); ?> > </a</p>
				<p><a href="#" class="btn btn-default btn-save btn-copy printmailing_mailto"><?php echo $view['translator']->trans('plugin.printmailing.form.contract.request'); ?></a></p>
			</div>
		</div>
		<div class="row">
			<div class="col-md-6">
                <?php echo $view['form']->rowIfExists($fields, 'printmailing_masClientId'); ?>
			</div>
			<div class="col-md-6">
                <?php echo $view['form']->rowIfExists($fields, 'printmailing_masId'); ?>
			</div>
		</div>
		<hr />
		<div class="row">
			<div class="col-md-12">
                <?php echo $view['form']->rowIfExists($fields, 'printmailing_masSecret'); ?>
			</div>
		</div>
	</div>
</div>

<div class="panel panel-primary">
	<div class="panel-heading">
		<h3 class="panel-title"><?php echo $view['translator']->trans('mautic.config.tab.printmailingconfig.rest'); ?></h3>
	</div>
	<div class="panel-body">
		<div class="row">
			<div class="col-md-6">
                <?php echo $view['form']->rowIfExists($fields, 'printmailing_rest_user'); ?>
			</div>
			<div class="col-md-6">
                <?php echo $view['form']->rowIfExists($fields, 'printmailing_rest_password'); ?>
			</div>
		</div>
	</div>
</div>

<script>
  mQuery("a.printmailing_mailto").click(
      function(event) {
		event.preventDefault();

		const email = '<?php echo $formConfig['parameters']['printmailing_contract_email']; ?>';
    	const subject = '<?php echo $subject; ?>';
    	const emailBody = encodeURIComponent('<?php echo $message; ?>');
    	window.location = 'mailto:' + email + '?subject=' + subject + '&body=' +   emailBody;
      }
    );
</script>
