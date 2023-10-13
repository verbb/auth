<?php

namespace verbb\auth\clients\vend\provider\vendapi;

use Exception;

class VendAPI
{
    /**
     * @var mixed
     */
    private mixed $url;

    /**
     * @var mixed
     */
    private mixed $last_result_raw;
    /**
     * @var mixed
     */
    private mixed $last_result;

    /**
     * @var mixed
     */
    private mixed $requestr;

    /**
     * @var mixed
     */
    private mixed $debug = false;

    /**
     * Request all pages of the results, looping through returning as a single result set
     * @var boolean
     */
    public bool $automatic_depage = false;
    /**
     * Default outlet to use for inventory, this shouldn't need to be changed
     * @var string
     */
    public string $default_outlet = 'Main Outlet';

    /**
     * If rate limiting kicks in and the retry-after date is earlier than the current system time
     * then this API will sleep for 60 seconds, otherwise an exception will be thrown.
     * The right thing to do is ensure that your system has its time synchronised with a time server.
     */
    public bool $allow_time_slip = true;

    private string $tokenType;
    private string $token;

    /**
     * @param string $url url of your shop eg https://shopname.vendhq.com
     * @param string $tokenType tokenType for api
     * @param string $accessToken accessToken for api
     */
    public function __construct(string $url, string $tokenType, string $accessToken)
    {
        // trim trailing slash for niceness
        $this->url = rtrim($url, '/');

        $this->tokenType = $tokenType;
        $this->token = $accessToken;

        $this->requestr = new VendRequest($url, $tokenType, $accessToken);
    }

    /**
     * turn on debuging for this class and requester class
     *
     * @param boolean $status
     */
    public function debug(bool $status = true): void
    {
        $this->requestr->setOpt('debug', $status);
        $this->debug = true;
    }

    public function __destruct()
    {
        //
    }

    /**
     * Update customer
     *
     * @param object $customer
     * @return VendCustomer
     * @throws Exception
     */
    public function updateCustomer(object $customer): VendCustomer
    {
        $result = $this->apiRequest('/api/customers', $customer);
        return new VendCustomer($result->customer, $this);
    }

    /**
     * Update config
     * @return object
     */
    public function getConfig(): object
    {
        return $this->apiRequest('/api/config');
    }

    /**
     * Get a single webhook by id
     *
     * @param string $id id of the webhook to get
     *
     * @return VendWebhook
     */
    public function getWebhook(string $id): VendWebhook
    {
        $result = $this->getWebhooks(array('id' => $id));
        return isset($result[0]) ? $result[0] : new VendWebhook(null, $this);
    }

    /**
     * Get all webhooks
     *
     * @param array $options
     * @return array
     */
    public function getWebhooks(array $options = array()): array
    {
        $path = '';
        if (count($options)) {
            foreach ($options as $k => $v) {
                $v = urlencode($v); // ensure values with spaces etc are encoded properly
                $path .= '/' . $k . '/' . $v;
            }
        }

        return $this->apiGetWebhooks($path);
    }

    /**
     * Get a single webhook by id
     *
     * @param string $id id of the webhook to get
     *
     * @return object
     */
    public function deleteWebhook(string $id): object
    {
        $path = '/' . urlencode($id);

        return $this->apiDeleteWebhook($path);
    }

    /**
     * @param array $options
     * @return array
     */
    public function getOutlets(array $options = array()): array
    {
        $path = '';
        if (count($options)) {
            foreach ($options as $k => $v) {
                $path .= '/' . $k . '/' . $v;
            }
        }

        return $this->apiGetOutlets($path);
    }

    /**
     * @param array $options
     * @return array
     */
    public function getCustomers(array $options = array()): array
    {
        $path = '';
        if (count($options)) {
            foreach ($options as $k => $v) {
                $path .= '/' . $k . '/' . $v;
            }
        }

        return $this->apiGetCustomers($path);
    }

    /**
     * Get all products
     *
     * @param array $options .. optional
     * @return array
     */
    public function getProducts(array $options = array()): array
    {
        $path = '';
        if (count($options)) {
            foreach ($options as $k => $v) {
                $path .= '/' . $k . '/' . $v;
            }
        }

        return $this->apiGetProducts($path);
    }

    /**
     * Get a single register by id
     *
     * @param string $id id of the register to get
     *
     * @return VendSale
     */
    public function getRegister(string $id): VendSale
    {
        $result = $this->getRegisters(array('id' => $id));
        return isset($result[0]) ? $result[0] : new VendSale(null, $this);
    }

    /**
     * Get all active registers
     *
     * @param array $options .. optional
     * @return array
     */
    public function getRegisters(array $options = array()): array
    {
        $path = '';
        if (count($options)) {
            foreach ($options as $k => $v) {
                $v = urlencode($v); // ensure values with spaces etc are encoded properly
                $path .= '/' . $k . '/' . $v;
            }
        }

        return $this->apiGetRegisters($path);
    }

    /**
     * Get all sales
     *
     * @param array $options .. optional
     * @return array
     */
    public function getSales(array $options = array()): array
    {
        $path = '';
        if (count($options)) {
            foreach ($options as $k => $v) {
                $v = urlencode($v); // ensure values with spaces etc are encoded properly
                $path .= '/' . $k . '/' . $v;
            }
        }

        return $this->apiGetSales($path);
    }

    /**
     * Get a single product by id
     *
     * @param string $id id of the product to get
     *
     * @return VendProduct
     */
    public function getProduct(string $id): VendProduct
    {
        $result = $this->getProducts(array('id' => $id));
        return isset($result[0]) ? $result[0] : new VendProduct(null, $this);
    }

    /**
     * Get a single customer by id
     *
     * @param string $id id of the customer to get
     *
     * @return VendCustomer
     */
    public function getCustomer(string $id): VendCustomer
    {
        $result = $this->getCustomers(array('id' => $id));
        return isset($result[0]) ? $result[0] : new VendCustomer(null, $this);
    }

    /**
     * Get a single sale by id
     *
     * @param string $id id of the sale to get
     *
     * @return VendSale
     * @throws Exception
     */
    public function getSale(string $id): VendSale
    {
        $result = $this->apiGetSales('/' . $id);
        return isset($result[0]) ? $result[0] : new VendSale(null, $this);
    }

    /**
     * @param $date
     * @return array
     */
    public function getProductsSince($date): array
    {
        return $this->getProducts(array('since' => $date));
    }

    /**
     * @param $date
     * @return array
     */
    public function getSalesSince($date): array
    {
        return $this->getSales(array('since' => $date));
    }

    /**
     * request a specific path from vend
     *
     * @param string $path the absolute path of the requested item (ie /api/products )
     *
     * @return object returned from vend
     */
    public function request(string $path): object
    {
        return $this->apiRequest($path);
    }

    /**
     * @param $path
     * @return array
     */
    private function apiGetOutlets($path): array
    {
        $result = $this->apiRequest('/api/outlets' . $path);
        if (!isset($result->outlets) || !is_array($result->outlets)) {
            throw new Exception("Error: Unexpected result for request");
        }
        $outlets = array();
        foreach ($result->outlets as $outlet) {
            $outlets[] = new VendOutlet($outlet, $this);
        }

        return $outlets;
    }

    /**
     * @param $path
     * @return array
     */
    private function apiGetProducts($path): array
    {
        $result = $this->apiRequest('/api/products' . $path);
        if (!isset($result->products) || !is_array($result->products)) {
            throw new Exception("Error: Unexpected result for request");
        }
        $products = array();
        foreach ($result->products as $product) {
            $products[] = new VendProduct($product, $this);
        }

        return $products;
    }

    /**
     * @param $path
     * @return array
     */
    private function apiGetCustomers($path): array
    {
        $result = $this->apiRequest('/api/customers' . $path);
        if (!isset($result->customers) || !is_array($result->customers)) {
            throw new Exception("Error: Unexpected result for request");
        }
        $customers = array();
        foreach ($result->customers as $cust) {
            $customers[] = new VendCustomer($cust, $this);
        }

        return $customers;
    }

    /**
     * @param $path
     * @return array
     */
    private function apiGetSales($path): array
    {
        $result = $this->apiRequest('/api/register_sales' . $path);
        if (!isset($result->register_sales) || !is_array($result->register_sales)) {
            throw new Exception("Error: Unexpected result for request");
        }
        $sales = array();
        foreach ($result->register_sales as $s) {
            $sales[] = new VendSale($s, $this);
        }

        return $sales;
    }

    /**
     * @param $path
     * @return array
     * @throws Exception
     */
    private function apiGetRegisters($path): array
    {
        $result = $this->apiRequest('/api/registers' . $path);
        if (!isset($result->registers) || !is_array($result->registers)) {
            throw new Exception("Error: Unexpected result for request");
        }
        $sales = array();
        foreach ($result->registers as $r) {
            $sales[] = new VendRegister($r, $this);
        }

        return $sales;
    }

    /**
     * @param $path
     * @return array
     * @throws Exception
     */
    private function apiGetWebhooks($path): array
    {
        $result = $this->apiRequest('/api/webhooks' . $path);
        if (!is_array($result)) {
            throw new Exception("Error: Unexpected result for request");
        }
        $webhooks = array();
        foreach ($result as $r) {
            $webhooks[] = new VendWebhook($r, $this);
        }

        return $webhooks;
    }

    /**
     * Create new Webhook
     *
     * @param $webhookData
     * @return object
     * @throws Exception
     */
    public function createWebook($webhookData): object
    {
        $postData = 'data=' . json_encode($webhookData);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->url . '/api/webhooks');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/x-www-form-urlencoded',
            'Authorization: ' . $this->tokenType . ' ' . $this->token,
        ));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $curlResult = curl_exec($ch);
        $curlStatus = curl_getinfo($ch);
        $httpCode = $curlStatus['http_code'];
        $result = json_decode($curlResult);

        if ($httpCode >= 400) {
            if (isset($result->error)) {
                $apiError = 'Error: ' . json_encode($result->error) . ' path: /api/webhooks';
            } else {
                $apiError = "Error: Unexpected HTTP " . $httpCode . " result from API";
            }

            throw new Exception($apiError, (int) $httpCode);
        }

        return $result;
    }

    /**
     * @param $path
     * @return object
     * @throws Exception
     */
    private function apiDeleteWebhook($path): object
    {
        return $this->apiRequest('/api/webhooks' . $path, null, null, $type = 'delete');
    }

    /**
     * Save vendproduct object to vend
     *
     * @param object $product
     * @return VendProduct
     * @throws Exception
     */
    public function saveProduct(object $product): VendProduct
    {
        $result = $this->apiRequest('/api/products', $product->toArray());

        return new VendProduct($result->product, $this);
    }

    /**
     * Save customer object to vend
     *
     * @param object $cust
     * @return VendCustomer
     * @throws Exception
     */
    public function saveCustomer(object $cust): VendCustomer
    {
        $result = $this->apiRequest('/api/customers', $cust->saveArray());

        return new VendCustomer($result->customer, $this);
    }

    /**
     * Save sale object to vend
     *
     * @param object $sale
     * @return VendSale
     * @throws Exception
     */
    public function saveSale(object $sale): VendSale
    {
        $result = $this->apiRequest('/api/register_sales', $sale->toArray());

        return new VendSale($result->register_sale, $this);
    }

    /**
     * make request to the vend api
     *
     * @param string $path the url to request
     * @param array|null $data optional - if sending a post request, send fields through here
     * @param boolean|null $depage do you want to grab and merge page results? .. will only depage on first page
     *
     * @return object variable result based on request
     */
    private function apiRequest(string $path, array $data = null, bool $depage = null, $type = null): object
    {
        $depage = $depage ?? $this->automatic_depage;
        if ($type == 'delete') {
            $rawresult = $this->requestr->delete($path);
        } elseif ($data !== null) {
            // setup for a post
            $rawresult = $this->requestr->post($path, json_encode($data));
        } else {
            // reset to a get
            $rawresult = $this->requestr->get($path);
        }

        $result = json_decode($rawresult);
        if ($result === null) {
            throw new Exception("Error: Recieved null result from API");
        }

        // Check for 400+ error:
        if ($this->requestr->http_code >= 400) {
            if ($this->requestr->http_code == 429) {
                // Too Many Requests
                $retry_after = strtotime($result->{'retry-after'});
                if ($retry_after < time()) {
                    if ($this->allow_time_slip) {
                        // The date on the current machine must be out of sync ...
                        // sleep for a minute to give the API time to cool down
                        sleep(60);
                    } else {
                        $exMessage = "Rate limit hit on API yet retry-after time given is in the past. ";
                        $exMessage .= "Please check time of local system. ";
                        $exMessage .= "Set \$allow-time-slip to true to work around this problem";
                        throw new Exception($exMessage);
                    }
                }

                if ($this->debug) {
                    echo "Vend API rate limit hit\n";
                    echo "Time now on local system is " . date('r', time()) . "\n";
                    echo "Sleeping until " . date('r', $retry_after) . " (as advised by Vend API) ";
                }

                while (time() < $retry_after) {
                    sleep(1);
                    if ($this->debug) {
                        echo ".";
                    }
                }

                // We've given the Vend API time to cool down - retry the original request:
                return $this->apiRequest($path, $data, $depage);
            }

            if (isset($result->error)) {
                $apiError = 'Error: ' . json_encode($result->error) . ' path: ' . $path;
            } else {
                $apiError = "Error: Unexpected HTTP " . $this->requestr->http_code . " result from API";
            }

            throw new Exception($apiError, (int) $this->requestr->http_code);
        }

        if ($depage && isset($result->pagination) && $result->pagination->page == 1) {
            for ($i = 2; $i <= $result->pagination->pages; $i++) {
                $paged_result = $this->apiRequest(rtrim($path, '/') . '/page/' . $i, $data, false);
                $result = $this->mergeObjects($paged_result, $result);
            }
        }

        if ($result && isset($result->error)) {
            throw new Exception($result->error . ' : ' . $result->details);
        }

        if ($this->debug) {
            $this->last_result_raw = $rawresult;
            $this->last_result = $result;
        }

        return $result;
    }

    /**
     * merge two objects when depaginating results
     *
     * @param object $obj1 original object to overwrite / merge
     * @param object $obj2 secondary object
     *
     * @return object       merged object
     */
    private function mergeObjects(object $obj1, object $obj2): object
    {
        $obj3 = $obj1;
        foreach ($obj2 as $k => $v) {
            if (is_array($v) && isset($obj3->$k) && is_array($obj3->$k)) {
                $obj3->$k = array_merge($obj3->$k, $v);
            } else {
                $obj3->$k = $v;
            }
        }
        return $obj3;
    }
}
