<?php

namespace verbb\auth\clients\vend\provider\vendapi;

class VendWebhook extends VendObject
{
    /**
     * will create/update the user using the vend api and this object will be updated
     *
     * @return void
     */
    public function create(): void
    {
        // wipe current user and replace with new objects properties
        $this->vendObjectProperties = $this->vend->createWebook($this)->toArray();
    }
}
