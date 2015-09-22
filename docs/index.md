# Documentation

## Conflicting modules

The following modules are known to have, at least in some cases, conflicted with ShopyMind and generating errors when
ShopyMind is being called on the website.

* AW_Mobile

Problems seem to occur when ShopyMind starts Magento App Emulation.

### Fix

You can fix this conflict by deactivating the module on the fly during a ShopyMind call. We implemented an event that is
dispatched just before entering App Emulation. You can subscribe to this event to disable the conflicting module(s). 

Example for AW_Mobile:

```php
<?php

class Vendor_Module_Model_Observer
{
    /**
     * @event shopymind_start_emulation_before
     */
    public function disableConflictingModules(Varien_Event_Observer $observer)
    {
        Mage::app()->getStore($observer->getEvent()->getStoreId())
            ->setConfig('awmobile/settings/enabled', '0');
    }
}
```
