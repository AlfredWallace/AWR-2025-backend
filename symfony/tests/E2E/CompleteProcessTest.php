<?php

namespace App\Tests\E2E;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\HttpFoundation\Response;

class CompleteProcessTest extends WebTestCase
{
    private KernelBrowser $client;
    private string $adminToken;
    private string $userToken;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    public function testCompleteProcess(): void
    {
        // Step 1: Create an admin user with the Symfony command
        $this->createAdminUser();

        // Step 2: Get JWT token for admin user
        $this->getAdminToken();

        // Step 3: Reset teams via the API
        $this->resetTeams();

        // Step 4: Register a normal user
        $this->registerNormalUser();

        // Step 5: Get JWT token for normal user
        $this->getUserToken();

        // Step 6: Get team IDs
        $teamIds = $this->getTeamIds();

        // Step 7: Run a simulation for the normal user
        $this->runSimulation($teamIds);
    }

    private function createAdminUser(): void
    {
        $kernel = self::bootKernel();
        $application = new Application($kernel);

        $command = $application->find('app:create-admin');
        $commandTester = new CommandTester($command);
        
        // Execute the command with username and password arguments
        $commandTester->execute([
            'username' => 'admin_user',
            'password' => 'admin_password'
        ]);

        // Assert the command was successful
        $this->assertStringContainsString('Admin user "admin_user" has been created successfully', $commandTester->getDisplay());
    }

    private function getAdminToken(): void
    {
        // Request JWT token for admin
        $this->client->request(
            'POST',
            '/api/login_check',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'username' => 'admin_user',
                'password' => 'admin_password'
            ])
        );

        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('token', $responseData);
        
        $this->adminToken = $responseData['token'];
    }

    private function resetTeams(): void
    {
        // Reset teams using admin token
        $this->client->request(
            'POST',
            '/api/teams/reset',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Bearer ' . $this->adminToken
            ]
        );

        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('message', $responseData);
        $this->assertEquals('Teams have been successfully reset', $responseData['message']);
    }

    private function registerNormalUser(): void
    {
        // Register a normal user
        $this->client->request(
            'POST',
            '/api/register',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'username' => 'normal_user',
                'password' => 'normal_password'
            ])
        );

        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('message', $responseData);
        $this->assertEquals('User registered successfully', $responseData['message']);
    }

    private function getUserToken(): void
    {
        // Request JWT token for normal user
        $this->client->request(
            'POST',
            '/api/login_check',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'username' => 'normal_user',
                'password' => 'normal_password'
            ])
        );

        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('token', $responseData);
        
        $this->userToken = $responseData['token'];
    }

    private function getTeamIds(): array
    {
        // Get team IDs using user token
        $this->client->request(
            'GET',
            '/api/teams',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Bearer ' . $this->userToken
            ]
        );

        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('teams', $responseData);
        $this->assertNotEmpty($responseData['teams']);

        // Get the first two team IDs for the simulation
        $teams = $responseData['teams'];
        return [
            'homeTeamId' => $teams[0]['id'],
            'awayTeamId' => $teams[1]['id']
        ];
    }

    private function runSimulation(array $teamIds): void
    {
        // Run a simulation using user token
        $this->client->request(
            'POST',
            '/api/simulations/run',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Bearer ' . $this->userToken
            ],
            json_encode([
                'name' => 'Test Simulation',
                'matches' => [
                    [
                        'homeTeamId' => $teamIds['homeTeamId'],
                        'awayTeamId' => $teamIds['awayTeamId'],
                        'homeScore' => 21,
                        'awayScore' => 14
                    ]
                ]
            ])
        );

        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('message', $responseData);
        $this->assertEquals('Simulation created and run successfully', $responseData['message']);
        $this->assertArrayHasKey('simulationId', $responseData);
    }
}