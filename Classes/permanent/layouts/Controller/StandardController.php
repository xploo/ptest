<?php
namespace permanent\layouts\Controller;

/*
 * This file is part of the permanent.layouts package.
 */

use TYPO3\Flow\Annotations as Flow;

class StandardController extends \TYPO3\Flow\Mvc\Controller\ActionController
{

    /**
     * @return void
     */
    public function indexAction()
    {
        $this->view->assign('foos', array(
            'test', 'HAHHAHAHHAHAHA'
        ));
    }

}
