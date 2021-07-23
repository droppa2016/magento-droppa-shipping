<?php

namespace Droppa\DroppaShipping\Model;

use function curl_setopt;
use function json_encode;
use const CURLOPT_CUSTOMREQUEST;
use const CURLOPT_HEADER;
use const CURLOPT_HTTPHEADER;
use const CURLOPT_POST;
use const CURLOPT_RETURNTRANSFER;
use const CURLOPT_SSL_VERIFYHOST;
use const CURLOPT_SSL_VERIFYPEER;
use const CURLOPT_POSTFIELDS;

class Curl
{
    protected $adminStoreAPIKey;
    protected $adminStoreServiceKey;

    public function __construct($adminStoreAPIKey, $adminStoreServiceKey)
    {
        $this->adminStoreAPIKey       = $adminStoreAPIKey;
        $this->adminStoreServiceKey   = $adminStoreServiceKey;
    }

    public function curlEndpoint($endpoint, $body, $method = 'GET')
    {
        $ch = curl_init($endpoint);

        if (!$this->adminStoreAPIKey && !$this->adminStoreServiceKey) {
            return false;
        } else {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                "Content-Type: application/json",
                "Connection: Keep-Alive",
                "Accept: application/json",
                "Authorization: Bearer " . $this->adminStoreAPIKey . ":" . $this->adminStoreServiceKey
            ]);

            $response_json = curl_exec($ch);

            $curl_error = curl_error($ch);

            $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            if ($status >= 200 && $status <= 308) {
                return $response_json;
            } else {
                return trigger_error(
                    "Status Error: " . $status . ", " . $curl_error . " => " . $response_json,
                    E_USER_ERROR
                );
            }

            curl_close($ch);
        }
    }
}