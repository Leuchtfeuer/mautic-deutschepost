<?php

/**
 * @var FormView  $form
 * @var PhpEngine $view
 */

use Mautic\CoreBundle\Templating\Engine\PhpEngine;
use Symfony\Component\Form\FormView;

$fields    = $form->children;
// print '<pre>';
// error_log ('<h1>$this->coreParametersHelper->get()</h1>');
// error_log( $this->coreParametersHelper->get('triggerdialog_masSecret') );
// print '</pre>'; 

$arrMsg[] = "Guten Tag";
$arrMsg[] = "";
$arrMsg[] = "Ich interessiere mich für die Nutzung von Ihrer PrintMailing Lösung mit dem Marketing Automation Tool Aivie.";
$arrMsg[] = "";
$arrMsg[] = "Bitte senden Sie mir einen entsprechenden Vertrag.";
$arrMsg[] = "";
$arrMsg[] = sprintf("MAS ID [partnerSystemIdExt]: %s",$_ENV["MAUTIC_TRIGGERDIALOG_MASID"]);
$arrMsg[] = sprintf("MAS Client-ID [Mandanten-ID]: %s", $_ENV["MAUTIC_TRIGGERDIALOG_MASCLIENTID"]);
$arrMsg[] = "";
$arrMsg[] = "Vielen Dank.";
$arrMsg[] = "";
$arrMsg[] = "Mit freundlichen Grüssen";
$message = implode($arrMsg,'\\r\\n');
?>

<div class="panel panel-primary">
	<div class="panel-heading">
		<h3 class="panel-title"><?php echo $view['translator']->trans('mautic.config.tab.triggerdialogconfig'); ?></h3>
	</div>
	<div class="panel-body">
		<div class="row">
			<div class="col-md-6 form-group">
				<p>Um individuelle Print Mailings aus Kampagnen zu versenden benötigst du einen Vertrag mit der Deutschen Post.</p>
				<p>Weitere Details und eine <a href="https://aivie.ch/postkarten-versenden-mit-mautic/" target="_blank">Schritt für Schritt Anleitung</a> findest du im Blog.</p>
				<p><a href="#" class="btn btn-default btn-save btn-copy triggerdialog_mailto">Vertrag anfordern</a></p>
			</div>
		</div>	
		<div class="row">
			<div class="col-md-6">
                <?php echo $view['form']->rowIfExists($fields, 'triggerdialog_masClientId'); ?>
			</div>
			<div class="col-md-6">
                <?php echo $view['form']->rowIfExists($fields, 'triggerdialog_masId'); ?>
			</div>
		</div>
		<hr />
		<div class="row">
			<div class="col-md-12">
                <?php echo $view['form']->rowIfExists($fields, 'triggerdialog_masSecret'); ?>
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
                <?php echo $view['form']->rowIfExists($fields, 'triggerdialog_rest_user'); ?>
			</div>
			<div class="col-md-6">
                <?php echo $view['form']->rowIfExists($fields, 'triggerdialog_rest_password'); ?>
			</div>
		</div>
	</div>
</div>

<script>
  mQuery("a.triggerdialog_mailto").click(
      function(event) {
		event.preventDefault();
        
		var email = 'print-mailing@deutschepost.de';
    	var subject = 'Neuer Vertrag PrintMailing: <?php echo $_ENV["MAUTIC_TRIGGERDIALOG_MASID"] ?>';
    	var emailBody = encodeURIComponent('<?php echo $message; ?>');
    	window.location = 'mailto:' + email + '?subject=' + subject + '&body=' +   emailBody;
      }
    );
  </script>