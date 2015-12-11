<?php

interface SPM_ShopyMind_Interface_Action
{
    public function __construct(array $params = array());
    public function process();
}
