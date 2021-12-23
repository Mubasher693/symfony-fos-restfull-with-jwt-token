<?php

namespace App\Tests;

use GuzzleHttp\Exception\GuzzleException;
use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;

class UserTest extends TestCase
{

    /**
     * @var Client
     */
    protected $http;

    /**
     * @return void
     */
    public function setUp() :void
    {
        $this->http = new Client(['base_uri' => 'http://localhost:8000/']);
    }

    /**
     * @throws GuzzleException
     */
    public function testIndex(): void
    {
        $token    = $this->getToken('mubasher698', '123456');
        $token    = $token['token'];
        $response = $this->http->request(
            'GET',
            'api/user',
            ['headers' => ['Authorization' => 'Bearer '.$token]]
        )->getBody()->getContents();
        $content = json_decode($response, true);
        $this->assertArrayHasKey('id', $content[0]);
    }

    /**
     * @throws GuzzleException
     */
    public function testIndexEmptyToken(): void
    {
        try {
            $this->http->request('GET', 'api/user')->getBody()->getContents();;
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            if ($e->hasResponse()) {
                $response      = $e->getResponse();
                $error_message = json_decode((string) $response->getBody(), true);
                $this->assertEquals('JWT Token not found', $error_message['message']);
                $this->assertEquals(401, $error_message['code']);
            }
        }
    }

    /**
     * @throws GuzzleException
     */
    public function testIndexWriterRole(): void
    {
        try {
            $token = $this->getToken('mubasher693', '123456');
            $token = $token['token'];
            $this->http->request(
                'GET',
                'api/user',
                ['headers' => ['Authorization' => 'Bearer ' . $token]]
            )->getBody()->getContents();
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            if ($e->hasResponse()) {
                $response      = $e->getResponse();
                $error_message = json_decode((string) $response->getBody(), true);
                $this->assertMatchesRegularExpression('/No user found or You dont have right to access this URL/', $error_message);
                $this->assertEquals(404, $e->getCode());
            }
        }
    }

    /**
     * @throws GuzzleException
     */
    public function testRegister(): void
    {
        $random   = rand(0,1000);
        $response = $this->http->request('POST', '/register', [
            'form_params' => [
                'username' => 'mubasher'.$random,
                'email'    => 'mubasheriqbal'.$random.'@gmail.com',
                'role'     => '1',
                'password' => '123456',
            ]
        ])->getBody()->getContents();
        $this->assertMatchesRegularExpression('/Created new user successfully/', $response);
    }

    /**
     * @throws GuzzleException
     */
    public function testRegisterEmailEmpty(): void
    {
        $random   = 693;
        $response = $this->http->request('POST', '/register', [
            'form_params' => [
                'username' => 'mubasher'.$random,
                'email'    => '',
                'role'     => '1',
                'password' => '123456',
            ]
            /*,['debug' => false],
            [
                'headers' =>
                    [
                        'Authorization' => "Bearer {$token['token']}"
                    ]
            ]*/
        ])->getBody()->getContents();
        $this->assertMatchesRegularExpression('/Invalid Username or Password or Email/', $response);
    }

    /**
     * @throws GuzzleException
     */
    public function testRegisterUsernameEmpty(): void
    {
        $random   = 693;
        $response = $this->http->request('POST', '/register', [
            'form_params' => [
                'username' => '',
                'email'    => 'mubasheriqbal'.$random.'@gmail.com',
                'role'     => '1',
                'password' => '123456',
            ]
            /*,['debug' => false],
            [
                'headers' =>
                    [
                        'Authorization' => "Bearer {$token['token']}"
                    ]
            ]*/
        ])->getBody()->getContents();
        $this->assertMatchesRegularExpression('/Invalid Username or Password or Email/', $response);
    }

    /**
     * @throws GuzzleException
     */
    public function testRegisterPasswordEmpty(): void
    {
        $random   = 693;
        $response = $this->http->request('POST', '/register', [
            'form_params' => [
                'username' => 'mubasher'.$random,
                'email'    => 'mubasheriqbal'.$random.'@gmail.com',
                'role'     => '1',
                'password' => '',
            ]
        ])->getBody()->getContents();
        $this->assertMatchesRegularExpression('/Invalid Username or Password or Email/', $response);
    }

    /**
     * @throws GuzzleException
     */
    public function testGetTokenUser(): void
    {
        $response = $this->getToken();
        $this->assertArrayHasKey('token', $response);
    }

    /**
     * @param string $username
     * @param string $password
     * @return mixed
     * @throws GuzzleException
     */
    private function getToken(string $username='mubasher693', string $password='123456'){
        $response = $this->http->request('POST', 'api/login_check', [
            'json' => [
                'username' => $username,
                'password' => $password,
            ]
        ])->getBody()->getContents();
        return json_decode($response, true);
    }
}
