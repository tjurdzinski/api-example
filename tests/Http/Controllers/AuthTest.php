<?php

namespace Tests\Http\Controllers;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use DatabaseTransactions;

    public function testLoginInvalidEmail()
    {
        $params = [
            'email' => 'john',
            'password' => '123456',
        ];

        $response = $this->post('/api/login', $params);
        $this->assertEquals(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
    }

    public function testLoginNoPassword()
    {
        $params = [
            'email' => 'john@example.com',
        ];

        $response = $this->post('/api/login', $params);
        $this->assertEquals(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
    }

    public function testLoginNotExistingEmail()
    {
        $params = [
            'email' => 'mark@example.com',
            'password' => '123456',
        ];

        $response = $this->post('/api/login', $params);
        $this->assertEquals(Response::HTTP_FORBIDDEN, $response->getStatusCode());
    }

    public function testLoginInvalidPassword()
    {
        $params = [
            'email' => 'john@example.com',
            'password' => 'invalid',
        ];

        $response = $this->post('/api/login', $params);
        $this->assertEquals(Response::HTTP_FORBIDDEN, $response->getStatusCode());
    }

    public function testLogin()
    {
        $params = [
            'email' => 'john@example.com',
            'password' => '123456',
        ];

        $response = $this->post('/api/login', $params);
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertNotEmpty($response->json('token'));
    }

    public function testLogout()
    {
        $response = $this->post('/api/logout', [], $this->getAuthHeader($this->getDefaultUser()));
        $this->assertEquals(Response::HTTP_NO_CONTENT, $response->getStatusCode());
    }

    public function testLogoutUnauthorized()
    {
        $response = $this->post('/api/logout');
        $this->assertEquals(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
    }
}
