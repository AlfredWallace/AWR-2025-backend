<?php

namespace App\Tests\Smoke;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class BasicGetTest extends WebTestCase
{
    private KernelBrowser $client;
    private string $token;
    private string $username;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->registerUser();
        $this->getToken();
    }

    /**
     * @dataProvider getRoutesProvider
     */
    public function test_authenticated_get_routes(string $uri): void
    {
        // Make request with authentication
        $this->client->request(
            'GET',
            $uri,
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Bearer ' . $this->token
            ]
        );

        // Assert that the response is 200 OK
        $this->assertEquals(
            Response::HTTP_OK, 
            $this->client->getResponse()->getStatusCode(),
            sprintf('The GET route %s should return 200 OK for authenticated users', $uri)
        );

        // Verify that the response contains valid JSON
        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertIsArray($responseData, 'Response should be a valid JSON array');
    }

    public function getRoutesProvider(): array
    {
        return [
            'api_list_users' => ['/api/users'],
            'api_list_teams' => ['/api/teams'],
        ];
    }

    private function registerUser(): void
    {
        $this->username = 'testuser' . uniqid();

        // Register a test user
        $this->client->request(
            'POST',
            '/api/users/register',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'username' => $this->username,
                'password' => 'test_password'
            ])
        );

        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());
    }

    private function getToken(): void
    {
        // Request JWT token for the test user
        $this->client->request(
            'POST',
            '/api/login_check',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'username' => $this->username,
                'password' => 'test_password'
            ])
        );

        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('token', $responseData);

        $this->token = $responseData['token'];
    }
}
