<?php

namespace verbb\auth\clients\vend\provider\vendapi;

class VendProduct extends VendObject
{
    /**
     * will create/update the product using the vend api and this object will be updated
     *
     * @return void
     */
    public function save(): void
    {
        // wipe current product and replace with new objects properties
        $this->vendObjectProperties = $this->vend->saveProduct($this)->toArray();
    }

    /**
     * get the inventory for the given outlet (default: all outlets)
     *
     * @param string|null $outlet
     * @return int
     */
    public function getInventory(string $outlet = null): int
    {
        $total = 0;
        if (!isset($this->vendObjectProperties['inventory']) || !is_array($this->vendObjectProperties['inventory'])) {
            return $total;
        }
        foreach ($this->vendObjectProperties['inventory'] as $o) {
            if ($o->outlet_name == $outlet) {
                return $o->count;
            }
            $total += $o->count;
        }

        return $total;
    }
    
    /**
     * set the inventory at $outlet to $count .. default outlet is the first found
     *
     * @param int $count
     * @param string|null $outlet
     */
    public function setInventory(int $count, string $outlet = null): void
    {
        foreach ($this->vendObjectProperties['inventory'] as $k => $o) {
            if ($o->outlet_name == $outlet || $outlet === null) {
                $this->vendObjectProperties['inventory'][$k]->count = $count;

                return;
            }
        }
        $this->vendObjectProperties['inventory'] = array(
            array(
                "outlet_name" => (
                    $outlet ?: ($this->vend ? $this->vend->default_outlet : 'Main Outlet')),
                "count" => $count,
            ),
        );
    }
}
