<?php

use Mautic\CoreBundle\Templating\Engine\PhpEngine;
use Symfony\Component\Form\FormView;

/**
 * @var $view PhpEngine
 * @var $form FormView
 */
foreach ($form as $f) {
    echo $view['form']->row($f);
}
