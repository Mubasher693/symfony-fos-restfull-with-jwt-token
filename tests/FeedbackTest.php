<?php

namespace App\Tests;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use PHPUnit\Framework\TestCase;

class FeedbackTest extends TestCase
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
    public function testIndexGuestUsers(): void
    {
        $response = $this->http->request(
            'GET',
            'feedback/list/2'
        )->getBody()->getContents();
        $content = json_decode($response, true);
        $this->assertArrayHasKey('id', $content[0]);
    }

    /**
     * @return void
     * @throws GuzzleException
     */
    public function testIndexAuthorizeUsers(): void
    {
        $token    = $this->getToken('mubasher698', '123456');
        $token    = $token['token'];
        $response = $this->http->request(
            'GET',
            'feedback/list/2',
            ['headers' => ['Authorization' => 'Bearer '.$token]]
        )->getBody()->getContents();
        $content = json_decode($response, true);
        $this->assertArrayHasKey('id', $content[0]);
    }

    /**
     * @return void
     * @throws GuzzleException
     */
    public function testIndexAuthorizeUserInvalidCredentials(): void
    {
        try {
            $token = $this->getToken(' ', '123456');
            $token = $token['token'];
            $response = $this->http->request(
                'GET',
                'feedback/list/2',
                ['headers' => ['Authorization' => 'Bearer ' . $token]]
            )->getBody()->getContents();
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            if ($e->hasResponse()) {
                $response      = $e->getResponse();
                $error_message = json_decode((string) $response->getBody(), true);
                $this->assertMatchesRegularExpression('/Invalid credentials/', $error_message['message']);
                $this->assertEquals(401, $error_message['code']);
            }
        }
    }

    /**
     * @return void
     * @throws GuzzleException
     */
    public function testNewAuthorizedUser(): void
    {
        $token    = $this->getToken('mubasher693', '123456');
        $token    = $token['token'];
        $response = $this->http->request(
            'POST',
            'api/feedback',
            [
                'headers' => ['Authorization' => 'Bearer '.$token],
                'form_params' => [
                    'post_id'       => '2',
                    'description'   => 'Dummy Test Description',
                    'logged_in_user'=> 1,
                ]
            ]
        )->getBody()->getContents();
        $this->assertMatchesRegularExpression('/Created new feedback successfully with/', $response);
    }

    /**
     * @return void
     * @throws GuzzleException
     */
    public function testNewUnAuthorizedUser(): void
    {
        $response = $this->http->request(
            'POST',
            'feedback',
            [
                'form_params' => [
                    'post_id'       => '2',
                    'description'   => 'Dummy Test Description',
                ]
            ]
        )->getBody()->getContents();
        $this->assertMatchesRegularExpression('/Created new feedback successfully with/', $response);
    }

    /**
     * @return void
     * @throws GuzzleException
     */
    public function testNewAuthorizedInvalidUser(): void
    {
        try {
            $token = $this->getToken('mubasher693s', '123456');
            $token = $token['token'];
            $response = $this->http->request(
                'POST',
                'api/feedback',
                [
                    'headers' => ['Authorization' => 'Bearer ' . $token],
                    'form_params' => [
                        'post_id' => '2',
                        'description' => 'Dummy Test Description',
                        'logged_in_user' => 1,
                    ]
                ]
            )->getBody()->getContents();
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            if ($e->hasResponse()) {
                $response      = $e->getResponse();
                $error_message = json_decode((string) $response->getBody(), true);
                $this->assertMatchesRegularExpression('/Invalid credentials/', $error_message['message']);
                $this->assertEquals(401, $error_message['code']);
            }
        }
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
