<?php

/**
 * @var PhpEngine $view
 * @var FormView  $form
 */
use Mautic\CoreBundle\Templating\Engine\PhpEngine;
use Symfony\Component\Form\FormView;

/** @var FormView $fields */
$fields = $form->children['field']->parent->vars['attr'];
$isPrototype = ($form->vars['name'] === '__name__');
$variableType = $form['field']->vars['value'];
$object = (isset($form->vars['data']['object'])) ? $form->vars['data']['object'] : 'variable';
$class = (isset($form->vars['data']['object']) && $form->vars['data']['object'] == 'company') ? 'fa-building' : 'fa-user';

if (!$isPrototype && !isset($fields[$object][$variableType]['label'])) {
    return;
}
?>

<div class="panel">
    <div class="panel-body">
        <div class="col-xs-6 col-sm-3 field-name">
            <i class="object-icon fa <?php echo $class; ?>" aria-hidden="true"></i> <span><?php echo ($isPrototype) ? '__label__' : $fields[$object][$variableType]['label']; ?></span>
        </div>

        <div class="col-xs-6 col-sm-3 padding-none"></div>

        <?php $hasErrors = count($form['filter']->vars['errors']) || count($form['display']->vars['errors']); ?>
        <div class="col-xs-10 col-sm-5 padding-none<?php if ($hasErrors): echo ' has-error'; endif; ?>">
            <?php echo $view['form']->widget($form['variable']); ?>
            <?php echo $view['form']->errors($form['variable']); ?>
        </div>

        <div class="col-xs-2 col-sm-1">
            <a href="javascript: void(0);" class="remove-selected btn btn-default text-danger pull-right"><i class="fa fa-trash-o"></i></a>
        </div>
        <?php echo $view['form']->widget($form['field']); ?>
        <?php echo $view['form']->widget($form['type']); ?>
        <?php echo $view['form']->widget($form['object']); ?>
    </div>
</div>
