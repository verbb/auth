<?php

namespace verbb\auth\providers\vend\provider\vendapi;

class VendWebhook extends VendObject
{
    /**
     * will create/update the user using the vend api and this object will be updated
     * @return null
     */
    public function create()
    {
        // wipe current user and replace with new objects properties
        $this->vendObjectProperties = $this->vend->createWebook($this)->toArray();
    }
}
