<?php

namespace Toothpaste\Sugar;

class Rest
{
    protected $user;
    protected $password;
    protected $platform;
    protected $systemUrl;

    protected $accessToken;
    protected $refreshToken;
    protected $expiresIn;
    protected $expirationTime;

    protected $lastRequestData;

    protected $apiUri = '/rest/v11_5';
    protected $oauthUri = '/oauth2/token';

    public function __construct(string $url, string $platform, string $u, string $p)
    {
        $this->systemUrl = $url;
        $this->platform = $platform;
        $this->user = $u;
        $this->password = $p;
    }

    protected function getBaseUrl() : string
    {
        return $this->systemUrl . $this->apiUri;
    }

    protected function getLoginUrl() : string
    {
        return $this->getBaseUrl() . $this->oauthUri;
    }

    protected function getLoginParams() : array
    {
        return [
            'grant_type' => 'password',
            'client_id' => 'sugar',
            'client_secret' => '',
            'platform' => $this->platform,
            'username' => $this->user,
            'password' => $this->password,
        ];
    }

    protected function getRefreshTokenParams() : array
    {
        return [
            'grant_type' => 'refresh_token',
            'client_id' => 'sugar',
            'client_secret' => '',
            'refresh_token' => $this->refreshToken,
        ];
    }

    protected function storeCurrentAction(array $data)
    {
        // store action, so that it can be retried if needs be
        $this->lastRequestData = $data;
    }

    protected function clearLastAction()
    {
        // once you get a correct response, clear the last action
        unset($this->lastRequestData);
    }

    protected function retryLastAction()
    {
        // if there is a failure, re-try if there is a last action
        if (!empty($this->lastRequestData)) {
            $this->completeRestCall($this->lastRequestData['type'], $this->lastRequestData['url'], $this->lastRequestData['params']);
        }
    }

    protected function login()
    {
        // return a valid token if we have it
        if (!empty($this->accessToken) && $this->expirationTime > time()) {
            return $this->accessToken;
        }

        $client = new \GuzzleHttp\Client();
        $response = $client->request('POST', $this->getLoginUrl(), [
            //'debug' => TRUE,
            'json' => $this->getLoginParams(),
            'headers' => [
                'Content-Type' => 'application/json',
            ]
        ]);

        if ($response->getStatusCode() == 200) {
            $resp = json_decode($response->getBody(), true);
            if (!empty($resp['access_token']) && !empty($resp['refresh_token'])) {
                $this->accessToken = $resp['access_token'];
                $this->refreshToken = $resp['refresh_token'];
                $this->expiresIn = $resp['expires_in'];
                $this->expirationTime = time() + $resp['expires_in'];
                return $this->accessToken;
            }
        }

        unset($this->accessToken);
        unset($this->refreshToken);
        unset($this->expiresIn);
        unset($this->expirationTime);
    }

    protected function refreshToken()
    {
        $client = new \GuzzleHttp\Client();
        $response = $client->request('POST', $this->getLoginUrl(), [
            //'debug' => TRUE,
            'json' => $this->getRefreshTokenParams(),
            'headers' => [
                'Content-Type' => 'application/json',
            ]
        ]);

        if ($response->getStatusCode() == 200) {
            $resp = json_decode($response->getBody(), true);
            if (!empty($resp['access_token']) && !empty($resp['refresh_token'])) {
                $this->accessToken = $resp['access_token'];
                $this->refreshToken = $resp['refresh_token'];
                $this->expiresIn = $resp['expires_in'];
                $this->expirationTime = time() + $resp['expires_in'];
                return $this->accessToken;
            }
        }

        unset($this->accessToken);
        unset($this->refreshToken);
        unset($this->expiresIn);
        unset($this->expirationTime);
    }

    protected function handleValidResponse($response)
    {
        $this->clearLastAction();
        return json_decode($response->getBody(), true);
    }

    protected function completeRestCall(string $type, string $uri, array $params)
    {
        $this->login();

        // store action
        $this->storeCurrentAction(['type' => $type, 'url' => $uri, 'params' => $params]);

        $client = new \GuzzleHttp\Client();
        $response = $client->request($type, $this->getBaseUrl() . $uri, [
            //'debug' => TRUE,
            'json' => $params,
            'headers' => [
                'Content-Type' => 'application/json',
                'OAuth-Token' => $this->accessToken,
            ]
        ]);

        // if it is a valid response
        if ($response->getStatusCode() == 200) {
            return $this->handleValidResponse($response);
        } else {
            $this->refreshToken();
            $this->retryLastAction();
            if ($response->getStatusCode() == 200) {
                return $this->handleValidResponse($response);
            } else {
                // re-login
                $this->login();
                $this->retryLastAction();
                if ($response->getStatusCode() == 200) {
                    $this->clearLastAction();
                    return $this->handleValidResponse($response);
                } else {
                    // terminate here
                    return ['error' => 'Request failed multiple times'];
                }
            }
        }
        return [];
    }
}
