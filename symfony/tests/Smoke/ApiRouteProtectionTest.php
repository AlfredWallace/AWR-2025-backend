<?php

namespace App\Tests\Smoke;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class ApiRouteProtectionTest extends WebTestCase
{
    /**
     * @dataProvider routesNeedingAuthenticationProvider
     */
    public function test_routes_needing_authentication(string $method, string $uri): void
    {
        $client = static::createClient();

        // Make request without authentication
        $client->request($method, $uri);

        // Assert that the response is 401 Unauthorized
        $this->assertEquals(
            Response::HTTP_UNAUTHORIZED, 
            $client->getResponse()->getStatusCode(),
            sprintf('The %s route %s should require authentication', $method, $uri)
        );

        // Optionally check for specific error message in response
        $responseData = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('message', $responseData);
        $this->assertEquals('JWT Token not found', $responseData['message']);
    }

    public function routesNeedingAuthenticationProvider(): array
    {
        return [
            'api_run_simulation' => ['POST', '/api/simulations/run'],
            'api_list_teams' => ['GET', '/api/teams'],
            'api_reset_teams' => ['POST', '/api/teams/reset'],
            'api_list_users' => ['GET', '/api/users'],
        ];
    }
}
