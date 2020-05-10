<?php
namespace Als;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;

class GoogleAuth
{
    static $authorizationEndpoint = 'https://accounts.google.com/o/oauth2/v2/auth';
    static $tokenEndpoint         = 'https://oauth2.googleapis.com/token';
    static $userInfoEndpoint      = 'https://www.googleapis.com/oauth2/v3/userinfo';
    static $deviceEndpoint        = 'https://oauth2.googleapis.com/device/code';

    static $redirect_uri    = 'urn:ietf:wg:oauth:2.0:oob';
    static $response_type   = 'code';

    static $scope           = 'openid email';
    static $access_type     = 'offline';

    protected $client_id     = null;
    protected $client_secret = null;

    public $errors;

    function __construct($client_id = null, $client_secret = null) {
        if($client_id != null)
            $this->client_id = $client_id;
        if($client_secret != null)
            $this->client_secret = $client_secret;
    }

    function getUrl($scope = null)
    {
        if($this->client_id == null)
            return false;

        return self::Url($this->client_id, $scope);
    }

    function doAuth($code)
    {
        if($this->client_id == null || $this->client_secret == null)
            return false;

        return self::Auth($code, $this->client_id, $this->client_secret);
    }

    function doRefresh($code)
    {
        if($this->client_id == null || $this->client_secret == null)
            return false;

        return self::Refresh($code, $this->client_id, $this->client_secret);
    }

    function getDevice($scope = null)
    {
        if($this->client_id == null)
            return false;

        return self::Device_code($this->client_id, $scope);
    }

    function authDevice($code)
    {
        if($this->client_id == null || $this->client_secret == null)
            return false;

        return self::Device($code, $this->client_id, $this->client_secret);
    }

    static function Device_code($client_id = null, $scope = null)
    {
        if($client_id == null)
            return false;

        $scope = is_null($scope) ? self::$scope : self::$scope . ' ' . $scope;

        $client = new Client;

        try 
        {
            $res = $client->post(self::$deviceEndpoint,[
                'form_params' => [
                    'client_id'     => $client_id,
                    'scope'         => $scope
                ]
            ]);
        }
        catch (BadResponseException $e) 
        {
            $res = $e->getResponse();
            $body = $res->getBody()->getContents();
            return  ['code' => $res->getStatusCode(), 'error' => 'Get device_code falied', 'body' => $body];
        }
        if($res->getStatusCode() == '200')
        {
            $authdata = json_decode($res->getBody());
            $user_code = $authdata->user_code;

            if($user_code != '')
            {
                $data['code']= '200';
                $data['auth'] = $authdata;

                return $data;
            }
            return ['code' => '1001', 'error' => 'Get device code falied', 'auth'=> $authdata];
        }
    }

    static function Device($code, $client_id, $client_secret)
    {
        $client = new Client;

        try
        {
            $res = $client->post(self::$tokenEndpoint, [
                'form_params' => [
                    'device_code'   => $code,
                    'client_id'     => $client_id,
                    'client_secret' => $client_secret,
                    'grant_type'    => 'urn:ietf:params:oauth:grant-type:device_code'
                ]
            ]);
        }
        catch (BadResponseException $e) 
        {
            $res = $e->getResponse();
            $body = $res->getBody()->getContents();
            return  ['code' => $res->getStatusCode(), 'error' => 'Authorization device falied', 'body' => $body];
        }

        if($res->getStatusCode() == '200')
        {
            $authdata = json_decode($res->getBody());
            $access_token = $authdata->access_token;

            if($access_token != '')
            {
                $res = self::userinfo($access_token);
                $res['auth'] = $authdata;

                return $res;
            }
            return ['code' => '1002', 'error' => 'Authorization device error', 'auth'=> $authdata];
        }
        return  ['code' => '1002', 'error' => "Authorization device error"];
    }

    static function userinfo($code)
    {
        $client = new Client;
        try
        {
            $res = $client->get(self::$userInfoEndpoint. '?access_token=' . $code);
        }
        catch (BadResponseException $e) 
        {
            $res = $e->getResponse();
            $body = $res->getBody()->getContents();
            return  ['code' => $res->getStatusCode(), "error" => 'Get Userinfo falied', 'body' => $body];
        }

        if($res->getStatusCode() == '200')
        {
            $data = json_decode($res->getBody());
            if( $data->sub != '' && $data->email != '')
                return [ 'code' => '200', 'sub' => $data->sub, 'email' => $data->email];

            return ['code' => '1003', 'error' => 'Get Userinfo falied', 'data' => $data];
        }

        return ['code' => '1003', 'error' => 'Get Userinfo falied'];
    }

    static function Url($client_id = null, $scope = null)
    {
        if($client_id == null)
            return false;

        $scope = is_null($scope) ? self::$scope : self::$scope . ' ' . $scope;

        $http_query = http_build_query(['client_id'     => $client_id,
                                        'access_type'   => self::$access_type,
                                        'redirect_uri'  => self::$redirect_uri, 
                                        'response_type' => self::$response_type, 
                                        'scope'         => $scope
                                    ]);

        return self::$authorizationEndpoint . '?' . $http_query;
    }

    static function Refresh($code, $client_id, $client_secret)
    {
        $client = new Client;
        try
        {
            $res = $client->post(self::$tokenEndpoint,[
                'form_params' => [
                    'refresh_token' => $code,
                    'client_id'     => $client_id,
                    'client_secret' => $client_secret,
                    'grant_type'    => 'refresh_token'
                ]
            ]);
        }
        catch (BadResponseException $e) 
        {
            $res = $e->getResponse();
            $body = $res->getBody()->getContents();
            return  ['code' => $res->getStatusCode(), 'error' => 'Refresh token failed', 'body' => $body];
        }

        if($res->getStatusCode() == 200)
        {
            $authdata = json_decode($res->getBody());
            $access_token = $authdata->access_token;

            if($access_token != '')
            {
                return ['code' => '200', 'auth' => $authdata ];
            }
            return ['code' => '1004', "msg" => 'Refresh token failed', 'auth'=> $authdata];
        }

        return  ['code' => '1004', 'error' => 'Refresh token falied'];
    }

    static function Auth($auth_code, $client_id, $client_secret)
    {
        $client = new Client;
        try
        {
            $res = $client->post(self::$tokenEndpoint, [
                'form_params' => [
                    'code'          => $auth_code,
                    'client_id'     => $client_id,
                    'client_secret' => $client_secret,
                    'redirect_uri'  => self::$redirect_uri,
                    'grant_type'    => 'authorization_code'
                ]
            ]);
        }
        catch (BadResponseException $e) 
        {
            $res = $e->getResponse();
            $body = $res->getBody()->getContents();
            return  ['code' => $res->getStatusCode(), 'error' => 'Authorization falied', 'body' => $body];
        }

        if($res->getStatusCode() == 200)
        {
            $authdata = json_decode($res->getBody());
            $access_token = $authdata->access_token;

            if($access_token != '')
            {
                $res = self::userinfo($access_token);
                $res['auth'] = $authdata;

                return $res;
            }
            return ['code' => '1005', 'error' => 'Authorization falied', 'auth'=> $authdata];
        }
        return  ['code' => '1005', "error" => "Authorization falied"];
    }
}

