<?php

namespace verbb\auth\clients\vend\provider\vendapi;

class VendCustomer extends VendObject
{
    /**
     * Will create/update the user using the vend api and this object will be updated
     *
     * @return void
     */
    public function save(): void
    {
        // wipe current user and replace with new objects properties
        $this->vendObjectProperties = $this->vend->saveCustomer($this)->toArray();
    }
}
