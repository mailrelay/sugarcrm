<?php

class Mailrelay {

    // Properties

    private $applicationInfo = 'Unknow|?|?';

    private $protocol = 'https';

    private $host = '';

    private $apiKey = '';

    private $endpoint = '/ccm/admin/api/version/2/&type=json';

    private $params = array();

    private $curlInstance = null;

    private $added = 0;

    private $updated = 0;

    private $failed = 0;


    // Constructor

    public function __construct() {
    }

    // Destructor

    public function __destruct() {
        if (!is_null($this->curlInstance)) {
            curl_close($this->curlInstance);
            unset($this->curlInstance);
        }
    }

    // Getters and Setters

    public function setApplicationInfo($applicationName = 'Unknow', $applicationVersion = '?', $pluginVersion = '?') {
        $this->applicationInfo = implode('|', array(trim($applicationName), trim($applicationVersion), trim($pluginVersion)));
    }

    public function setHost($host) {
        $this->host = trim($host);
    }

    public function getHost() {
        return $this->host;
    }

    public function setApiKey($apiKey) {
        $this->apiKey = trim($apiKey);
    }

    public function getApiKey() {
        return $this->apiKey;
    }

    public function setParams($params) {
        if (!is_array($params)) {
            $params = array();
        }
        $this->params = $params;
    }

    // Public generic function

    public function execute($httpBuildQuery = false) {
        if (is_null($this->curlInstance)) {
            $this->init();
        }

        if ($httpBuildQuery) {
            curl_setopt($this->curlInstance, CURLOPT_POSTFIELDS, http_build_query($this->params));
        } else {
            curl_setopt($this->curlInstance, CURLOPT_POSTFIELDS, $this->params);
        }
        $responseString = curl_exec($this->curlInstance);
        $response = json_decode($responseString);
        if (!$response) {
            throw new Exception('Invalid response: ' . $responseString, 4);
        }
        if (!is_object($response)) {
            throw new Exception('Invalid response type: ' . $responseString, 5);
        }
        if (!isset($response->status)) {
            throw new Exception('Invalid response structure: ' . print_r($response, true), 6);
        }
        if ($response->status == 0) {
            if (isset($response->error)) {
                throw new Exception($response->error, 7);
            } else {
                throw new Exception('Unknow error', 8);
            }
        }
        return $response;
    }

    // Public specific functions

    public function getGroups($sortField = 'name', $sortOrder = 'ASC') {
        try {
            $params = array('function' => 'getGroups',
                            'apiKey' => $this->getApiKey(),
                            'sortField' => $sortField,
                            'sortOrder' => $sortOrder);
            $this->setParams($params);
            $response = $this->execute();
            $groups = array();
            foreach ($response->data as $group) {
                $groups[] = get_object_vars($group);
            }
            return $groups;
        } catch(Exception $exception) {
            throw $exception;
        }
    }

    public function getSubscribers($email = '') {
        try {
            $params = array(
                'function' => 'getSubscribers',
                'apiKey' => $this->getApiKey(),
                'email' => $email,
            );
            $this->setParams($params);
            $response = $this->execute();
            return $response->data;
        } catch(Exception $exception) {
            throw $exception;
        }
    }

    public function updateSubscriber($id, $email, $name = '', $groups = array()) {
        try {
            $params = array(
                'function' => 'updateSubscriber',
                'apiKey' => $this->getApiKey(),
                'id' => $id,
                'email' => $email,
                'name' => $name,
                'groups' => $groups
            );
            $this->setParams($params);
            $response = $this->execute(true);
            return $response;
        } catch(Exception $exception) {
            throw $exception;
        }
    }

    public function addSubscriber($email, $name = '', $groups = array()) {
        try {
            $params = array(
                'function' => 'addSubscriber',
                'apiKey' => $this->getApiKey(),
                'email' => $email,
                'name' => $name,
                'groups' => $groups
            );
            $this->setParams($params);
            $response = $this->execute(true);
            return $response;
        } catch(Exception $exception) {
            throw $exception;
        }
    }

    public function syncUsersToGroups($users, $groups) {
        $this->added = 0;
        $this->updated = 0;
        $this->failed = 0;
        foreach ($users as $email => $name) {
            try {
                $subscribers = $this->getSubscribers($email);
                if (!empty($subscribers)) {
                    $this->updateSubscriber($subscribers[0]->id, $email, $name, $groups);
                    $this->updated++;
                } else {
                    $this->addSubscriber($email, $name, $groups);
                    $this->added++;
                }
            } catch(Exception $exception) {
                $this->failed++;
            }
        }
        return array('added' => $this->added, 'updated' => $this->updated, 'failed' => $this->failed);
    }

    public function syncUserToGroups($email, $name, $groups) {
        try {
            $subscribers = $this->getSubscribers($email);
            if (!empty($subscribers)) {
                $this->updateSubscriber($subscribers[0]->id, $email, $name, $groups);
            } else {
                $this->addSubscriber($email, $name, $groups);
            }
        } catch(Exception $exception) {
            throw $exception;
        }
    }

    // Private functions

    private function init() {
        if ($this->applicationInfo == '') {
            throw new Exception('Application information can\'t be empty. Format: {Application Name}|{Application Version}|{Plugin Version}. e.g: Application|2.2|1.1', 1);
        }
        if ($this->host == '') {
            throw new Exception('The Host can\'t be empty', 2);
        }

        $this->curlInstance = curl_init($this->protocol . '://' . $this->host . '/' . $this->endpoint);
        curl_setopt($this->curlInstance, CURLOPT_HTTPHEADER, array('X-Request-Origin: ' . $this->applicationInfo));
        curl_setopt($this->curlInstance, CURLOPT_POST, true);
        curl_setopt($this->curlInstance, CURLOPT_RETURNTRANSFER, true);
        if ($this->protocol == 'https') {
            curl_setopt($this->curlInstance, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($this->curlInstance, CURLOPT_SSLVERSION, 3);
        }
    }

}
