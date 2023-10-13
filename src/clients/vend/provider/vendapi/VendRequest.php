<?php

namespace verbb\auth\clients\vend\provider\vendapi;

class VendRequest
{
    /**
     * @var mixed
     */
    private mixed $curl;
    /**
     * @var mixed
     */
    private mixed $debug;
    /**
     * @var mixed
     */
    private mixed $cookie;

    /**
     * @var mixed
     */
    public mixed $http_code;

    /**
     * @param $url
     * @param $username
     * @param $password
     */
    public function __construct($url, $username, $password)
    {
        $this->curl = curl_init();

        $this->url = $url;

        // setup default curl options
        $options = array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_TIMEOUT => 120,
            CURLOPT_FAILONERROR => 0, // 0 allows us to process the 400 responses (e.g. rate limits)
            CURLOPT_HTTPAUTH => CURLAUTH_ANY,
            CURLOPT_HTTPHEADER => array(
                'Accept: application/json',
                'Content-Type: application/json',
                'Authorization: ' . $username . ' ' . $password,
            ),
            CURLOPT_HEADER => 1,
        );

        $this->setOpt($options);
    }

    public function __destruct()
    {
        // close curl nicely
        if (is_resource($this->curl)) {
            curl_close($this->curl);
        }
    }

    /**
     * set option for request, also accepts an array of key/value pairs for the first param
     *
     * @param string $name  option name to set
     * @param false|misc $value value
     */
    public function setOpt(string $name, misc|false $value = false): void
    {
        if (is_array($name)) {
            curl_setopt_array($this->curl, $name);
            return;
        }
        if ($name == 'debug') {
            curl_setopt($this->curl, CURLINFO_HEADER_OUT, (int) $value);
            curl_setopt($this->curl, CURLOPT_VERBOSE, (boolean) $value);
            $this->debug = $value;
        } else {
            curl_setopt($this->curl, $name, $value);
        }
    }

    /**
     * @param $path
     * @param $rawdata
     * @return mixed
     */
    public function post($path, $rawdata): mixed
    {
        $this->setOpt(
            array(
                CURLOPT_POST => 1,
                CURLOPT_POSTFIELDS => $rawdata,
                CURLOPT_CUSTOMREQUEST => 'POST',
            )
        );
        $this->posted = $rawdata;
        return $this->request($path, 'post');
    }

    /**
     * @param $path
     * @return mixed
     */
    public function get($path): mixed
    {
        $this->setOpt(
            array(
                CURLOPT_HTTPGET => 1,
                CURLOPT_POSTFIELDS => null,
                CURLOPT_CUSTOMREQUEST => 'GET',
            )
        );
        $this->posted = '';
        return $this->request($path, 'get');
    }

    /**
     * @param $path
     * @return mixed
     */
    public function delete($path): mixed
    {
        $this->setOpt(
            array(
                CURLOPT_POSTFIELDS => null,
                CURLOPT_CUSTOMREQUEST => 'DELETE',
            )
        );
        $this->posted = '';
        return $this->request($path, 'delete');
    }

    /**
     * @param $path
     * @param $type
     * @return mixed
     */
    private function request($path, $type): mixed
    {
        $this->setOpt(CURLOPT_URL, $this->url . $path);

        $this->response = $response = curl_exec($this->curl);
        $curl_status = curl_getinfo($this->curl);
        $this->http_code = $curl_status['http_code'];
        $header_size = $curl_status['header_size'];

        $http_header = substr($response, 0, $header_size);
        $http_body = substr($response, $header_size);

        if ($this->debug) {
            $curl_debug = $curl_status;
            $head = $foot = "\n";
            if (PHP_SAPI !== 'cli') {
                $head = '<pre>';
                $foot = '</pre>';
            }
            echo $head . $curl_debug['request_header'] . $foot .
            ($this->posted ? $head . $this->posted . $foot : '') .
            $head . $http_header . $foot .
            $head . $http_body . $foot;
        }
        return $http_body;
    }
}
