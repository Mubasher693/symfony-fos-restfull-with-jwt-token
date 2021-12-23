<?php

namespace App\Tests;


use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use PHPUnit\Framework\TestCase;

class PostTest extends TestCase
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
     * @return void
     * @throws GuzzleException
     */
    public function testIndexGuestUsers(): void
    {
        $response = $this->http->request(
            'GET',
            'post'
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
            'post',
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
                'api/post',
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
    public function testIndexAuthorizeUserInvalidToken(): void
    {
        try {
            $this->http->request(
                'GET',
                'api/post',
                ['headers' => ['Authorization' => 'Bearer forgedToken']]
            )->getBody()->getContents();
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            if ($e->hasResponse()) {
                $response      = $e->getResponse();
                $error_message = json_decode((string) $response->getBody(), true);
                $this->assertMatchesRegularExpression('/Invalid JWT Token/', $error_message['message']);
                $this->assertEquals(401, $error_message['code']);
            }
        }
    }

    /**
     * @return void
     * @throws GuzzleException
     */
    public function testNew(): void
    {
        $token    = $this->getToken('mubasher693', '123456');
        $token    = $token['token'];
        $response = $this->http->request(
            'POST',
            'api/post',
            [
                'headers' => ['Authorization' => 'Bearer '.$token],
                'form_params' => [
                    'title'       => 'Dummy Test Title',
                    'description' => 'Dummy Test Description'
                ]
            ]
        )->getBody()->getContents();
        $this->assertMatchesRegularExpression('/Created new post successfully with id/', $response);
    }

    /**
     * @return void
     * @throws GuzzleException
     */
    public function testNewWithAdmin(): void
    {
        try {
            $token = $this->getToken('mubasher695', '123456');
            $token = $token['token'];
            $response = $this->http->request(
                'POST',
                'api/post',
                [
                    'headers' => ['Authorization' => 'Bearer ' . $token],
                    'form_params' => [
                        'title' => 'Dummy Test Title',
                        'description' => 'Dummy Test Description'
                    ]
                ]
            )->getBody()->getContents();
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            if ($e->hasResponse()) {
                $response      = $e->getResponse();
                $error_message = json_decode((string) $response->getBody(), true);
                $this->assertMatchesRegularExpression('/You dont have right to access this UR/', $error_message);
            }
        }
    }

    /**
     * @return void
     * @throws GuzzleException
     */
    public function testEdit(): void
    {
        $token    = $this->getToken('mubasher693', '123456');
        $token    = $token['token'];
        $response = $this->http->request(
            'PUT',
            'api/post/10',
            [
                'headers' => ['Authorization' => 'Bearer '.$token],
                'form_params' => [
                    'title'       => 'Dummy Test Title',
                    'description' => 'Dummy Test Description'
                ]
            ]
        )->getBody()->getContents();
        $content = json_decode($response, true);
        $this->assertArrayHasKey('id', $content);
    }

    /**
     * @return void
     * @throws GuzzleException
     */
    public function testEditWithAdminOrDifferentUser(): void
    {
        try {
            $token = $this->getToken('mubasher695', '123456');
            $token = $token['token'];
            $response = $this->http->request(
                'PUT',
                'api/post/6',
                [
                    'headers' => ['Authorization' => 'Bearer '.$token],
                    'form_params' => [
                        'title'       => 'Dummy Test Title',
                        'description' => 'Dummy Test Description'
                    ]
                ]
            )->getBody()->getContents();
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            if ($e->hasResponse()) {
                $response      = $e->getResponse();
                $error_message = json_decode((string) $response->getBody(), true);
                $this->assertMatchesRegularExpression('/You dont have right to access this UR/', $error_message);
            }
        }
    }

    /**
     * @return void
     * @throws GuzzleException
     */
    public function testDelete(): void
    {
        $token    = $this->getToken('mubasher693', '123456');
        $token    = $token['token'];
        $response = $this->http->request(
            'DELETE',
            'api/post/10',
            [
                'headers' => ['Authorization' => 'Bearer '.$token],
                'form_params' => [
                    'title'       => 'Dummy Test Title',
                    'description' => 'Dummy Test Description'
                ]
            ]
        )->getBody()->getContents();
        $this->assertMatchesRegularExpression('/Deleted a post successfully with /', $response);
    }

    /**
     * @return void
     * @throws GuzzleException
     */
    public function testDeleteWithAdminOrDifferentUser(): void
    {
        try {
            $token = $this->getToken('mubasher695', '123456');
            $token = $token['token'];
            $response = $this->http->request(
                'DELETE',
                'api/post/5',
                [
                    'headers' => ['Authorization' => 'Bearer '.$token],
                    'form_params' => [
                        'title'       => 'Dummy Test Title',
                        'description' => 'Dummy Test Description'
                    ]
                ]
            )->getBody()->getContents();
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            if ($e->hasResponse()) {
                $response      = $e->getResponse();
                $error_message = json_decode((string) $response->getBody(), true);
                $this->assertMatchesRegularExpression('/You dont have right to access this UR/', $error_message);
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
