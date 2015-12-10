<?php

class SPM_ShopyMind_Action_GetCategory implements SPM_ShopyMind_Interface_Action
{

    private $categoryId;

    public function __construct(array $arguments = array())
    {
        $this->categoryId = $arguments[0];
    }

    public function process()
    {

    }

}
