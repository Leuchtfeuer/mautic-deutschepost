<?php

/**
 * @var PhpEngine $view
 * @var FormView  $form
 */
use Mautic\CoreBundle\Templating\Engine\PhpEngine;
use Symfony\Component\Form\FormView;

foreach ($form as $i => $variable) {
    $isPrototype = ($variable->vars['name'] == '__name__');
    $filterType = $variable['field']->vars['value'];

    foreach ($form->parent->vars['fields'] as $object => $objectfields) {
        if ($isPrototype || isset($objectfields[$variable->vars['value']['field']])) {
            echo $view['form']->widget($variable, ['first' => ($i === 0)]);
        }
    }
}
