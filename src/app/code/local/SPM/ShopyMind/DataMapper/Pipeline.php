<?php

class SPM_ShopyMind_DataMapper_Pipeline
{
    private $transformations;

    public function __construct($transformations)
    {
        $this->transformations = $transformations;
    }

    public function format($data)
    {
        return array_reduce($this->transformations, function($results, $formatter) {
            return array_map($formatter, $results);
        }, $data);
    }

}
