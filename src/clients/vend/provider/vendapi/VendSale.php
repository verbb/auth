<?php

namespace verbb\auth\clients\vend\provider\vendapi;

class VendSale extends VendObject
{
    /**
     * Gets the customer associated with the sale
     * @return VendCustomer
     */
    public function getCustomer()
    {
        $customers = $this->vend->getCustomers(array('id' => $this->customer_id));
        if (empty($customers)) {
            throw new Exception('Unable to find customer ' . $this->customer_id . ' for sale');
        }
        return $customers[0];
    }

    /**
     * Gets the products associated with the sale - return array of [VendProduct]s
     * @return array
     */
    public function getProducts()
    {
        $products = array();
        foreach ($this->register_sale_products as $product) {
            $products[] = $this->vend->getProduct($product->product_id);
        }
        return $products;
    }

    /**
     * will create/update the user using the vend api and this object will be updated
     * @return null
     */
    public function save()
    {
        // wipe current user and replace with new objects properties
        $this->vendObjectProperties = $this->vend->saveSale($this)->toArray();
    }
}
