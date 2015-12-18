<?php

require dirname($_SERVER['SCRIPT_FILENAME']) . DIRECTORY_SEPARATOR . 'abstract.php';

class SPM_Shell_TroubleshootSPMActions extends Mage_Shell_Abstract
{

    public function run()
    {
        $actionName = $this->getArg('name');
        $internalMethodName = 'run' . ucfirst($actionName);
        if (!$actionName || !method_exists($this, $internalMethodName)) {
            echo $this->usageHelp();
        } else {
            $this->$internalMethodName();
        }
    }

    public function usageHelp()
    {
        return <<<USAGE
This shell is aimed at calling internal methods to make troubleshooting easier.
/!\ USE WITH CARE /!\

Usage:  php -f runAction.php -- [options]
-name         Action name (must be implemented)
-h            Short alias for help
help          This help

Examples:
- php -f runAction.php -- -name mapProduct -id 42
USAGE;
    }

    private function runMapProduct()
    {
        $productId = $this->getArg('id');

        $mapper = new SPM_ShopyMind_DataMapper_Product();
        $product = Mage::getModel('catalog/product')->load($productId);
        print_r($mapper->format($product));
    }

    protected function _error($msg)
    {
        exit(PHP_EOL . $msg . PHP_EOL);
    }
}

$shell = new SPM_Shell_TroubleshootSPMActions();
$shell->run();
