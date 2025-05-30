<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class UserControllerTest extends WebTestCase
{
    public function test_user_registration(): void
    {
        $client = static::createClient();
        $username = 'user'.uniqid();

        // First registration should succeed
        $client->request(
            'POST',
            '/api/users/register',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'username' => $username,
                'password' => 'test_password'
            ])
        );

        // Assert first registration was successful
        $this->assertEquals(Response::HTTP_CREATED, $client->getResponse()->getStatusCode());

        // Assert response content contains success message
        $responseData = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('message', $responseData);
        $this->assertEquals('User registered successfully', $responseData['message']);

        // Try to register the same user again
        $client->request(
            'POST',
            '/api/users/register',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'username' => $username,
                'password' => 'other_password'
            ])
        );

        // Assert response status code is 409 (Conflict)
        $this->assertEquals(Response::HTTP_CONFLICT, $client->getResponse()->getStatusCode());

        // Assert response content contains error message
        $responseData = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('message', $responseData);
        $this->assertEquals('User already exists', $responseData['message']);
    }
}
