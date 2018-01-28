<?php

require("libs/services/b2bcoin.php");

class Service {
    public static $services = [
        "b2bcoin" => [
            "name" => "B2BCoin.xyz",
            "currencies" => [
                "B2B"
            ]
        ]
    ];

    protected $service;
    protected $api_key;
    protected $service_instance;
    protected $currency;
    public $communication_error = false;
    public $curl_warning = false;

    public $options = array(
        /* if disable_curl is set to true, it'll use PHP's fopen instead of
         * curl for connection */
        'disable_curl' => false,

        /* do not use these options unless you know what you're doing */
        'local_cafile' => false,
        'force_ipv4' => false,
        'verify_peer' => true
    );

    public function __construct($service, $api_key, $currency = "B2B", $connection_options = null) {
        $this->service = $service;
        $this->api_key = $api_key;
        $this->currency = $currency;
        
        if($connection_options)
            $this->options = array_merge($this->options, $connection_options);

        switch($this->service) {
        case "b2bcoin":
            $this->service_instance = new B2BFaucet($api_key, $currency, $connection_options);
            break;        
        default:
            trigger_error("Invalid service $service");
        }
    }

    public function getServices($currency = null) {
        if(!$currency) {
            $all_services = [];
            foreach(self::$services as $service => $details) {
                $all_services[$service] = $details["name"];
            }
            return $all_services;
        }

        $services = [];
        foreach(self::$services as $service => $details) {
            if(in_array($service, $details["currencies"])) {
                $services[$service] = $details["name"];
            }
        }

        return $services;
    }

    public function send($to, $amount, $userip, $referral = "false") {
        /* if($this->currency === "B2B") {
            $amount /= 1000000000000;
        } */
        
        error_log("Amount to send", $amount);
        
        switch($this->service) {
        case "b2bcoin":
            $r = $this->service_instance->send($to, $amount, $referral);
            $check_url = "https://wallet.b2bcoin.xyz/#/pages/faucet";
            $success = $r['success'];
            $balance = $r["balance"];
            $error = $r["message"];
            $this->communication_error = $this->service_instance->communication_error;
            $this->curl_warning = $this->service_instance->curl_warning;
            break;        
        }

        $sname = self::$services[$this->service]["name"];
        $result = [];
        $result['success'] = $success;
        $result['response'] = json_encode($r);
        if($success) {
            $result['message'] = 'Payment sent to you using '.$sname;
            $result['html'] = '<div class="alert alert-success">' . htmlspecialchars(rtrim(rtrim(sprintf("%.12f", $amount/1000000000000), '0'), '.')) . " " . $this->currency . " was sent to you <a target=\"_blank\" href=\"$check_url\">on $sname</a>.</div>";
            $result['html_coin'] = '<div class="alert alert-success">' . htmlspecialchars(rtrim(rtrim(sprintf("%.8f", $amount/1000000000000), '0'), '.')) . " " . $this->currency . " was sent to you <a target=\"_blank\" href=\"$check_url\">on $sname</a>.</div>";
            $result['balance'] = $balance;
            if($balance) {
                $result['balance_bitcoin'] = sprintf("%.8f", $balance/100000000);
            } else {
                $result['balance_bitcoin'] = null;
            }
        } else {
            $result['message'] = $error;
            $result['html'] = '<div class="alert alert-danger">'.htmlspecialchars($error).'</div>';
        }
        return $result;
    }

    public function sendReferralEarnings($to, $amount, $userip) {
        return $this->send($to, $amount, $userip, "true");
    }

    public function getPayouts($count) {
        switch($this->service) {
        case "b2bcoin":
            return $this->service_instance->getPayouts($count);
            break;
        }
        return [];
    }

    public function getCurrencies() {
        switch($this->service) {
        case "b2bcoin":
            return $this->service_instance->getCurrencies();
            break;
        }
        return self::$services[$this->service]["currencies"];
    }

    public function getBalance() {
        switch($this->service) {
            case "b2bcoin":
                $balance = $this->service_instance->getBalance();
                $this->communication_error = $this->service_instance->communication_error;
                $this->curl_warning = $this->service_instance->curl_warning;
                return $balance;        
        }

        die("Database is broken. Please reinstall the script.");
    }

    public function fiabVersionCheck() {
        if($this->service == "faucetbox") {
            $fbox = $this->service_instance;
        } else {
            $fbox = new B2BFaucet("", "B2B", $this->options);
        }
        return $fbox->fiabVersionCheck();
    }
}
