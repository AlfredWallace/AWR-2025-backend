<?php

namespace App\Tests\Controller;

use App\Repository\UserRepository; // Added import
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class UserControllerTest extends WebTestCase
{
    // public function test_user_registration(): void
    // {
    //     $client = static::createClient();
    //     $username = 'user'.uniqid();

    //     // First registration should succeed
    //     $client->request(
    //         'POST',
    //         '/api/users/register',
    //         [],
    //         [],
    //         ['CONTENT_TYPE' => 'application/json'],
    //         json_encode([
    //             'username' => $username,
    //             'password' => 'test_password'
    //         ])
    //     );

    //     // Assert first registration was successful
    //     $this->assertEquals(Response::HTTP_CREATED, $client->getResponse()->getStatusCode());

    //     // Assert response content contains success message
    //     $responseData = json_decode($client->getResponse()->getContent(), true);
    //     $this->assertArrayHasKey('message', $responseData);
    //     $this->assertEquals('User registered successfully', $responseData['message']);

    //     // Try to register the same user again
    //     $client->request(
    //         'POST',
    //         '/api/users/register',
    //         [],
    //         [],
    //         ['CONTENT_TYPE' => 'application/json'],
    //         json_encode([
    //             'username' => $username,
    //             'password' => 'other_password'
    //         ])
    //     );

    //     // Assert response status code is 409 (Conflict)
    //     $this->assertEquals(Response::HTTP_CONFLICT, $client->getResponse()->getStatusCode());

    //     // Assert response content contains error message
    //     $responseData = json_decode($client->getResponse()->getContent(), true);
    //     $this->assertArrayHasKey('message', $responseData);
    //     $this->assertEquals('User already exists', $responseData['message']);
    // }

    public function testRegisterPageLoads(): void
    {
        $client = static::createClient();
        $client->request('GET', '/register');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Register');
        self::assertSelectorExists('form input[name="registration_form[username]"]');
        self::assertSelectorExists('form input[name="registration_form[password]"]');
        self::assertSelectorExists('form button[type="submit"]');
    }

    public function testRegisterSuccessful(): void
    {
        $client = static::createClient();
        /** @var UserRepository $userRepository */
        $userRepository = static::getContainer()->get(UserRepository::class);
        $testUsername = 'testuser_' . uniqid();

        $crawler = $client->request('GET', '/register');
        $csrfToken = $crawler->filter('input[name="registration_form[_token]"]')->attr('value');

        $client->request('POST', '/register', [
            'registration_form' => [
                'username' => $testUsername,
                'password' => 'testpassword',
                '_token' => $csrfToken,
            ]
        ]);

        self::assertResponseRedirects('/users'); // Assuming redirection to app_list_users which is /users
        $client->followRedirect();
        self::assertSelectorTextContains('h1', 'Users'); // Example check for the list page

        $user = $userRepository->findOneBy(['username' => $testUsername]);
        self::assertNotNull($user);
        self::assertEquals($testUsername, $user->getUsername());
        // Optionally, check roles
        self::assertContains('ROLE_USER', $user->getRoles());
    }

    public function testRegisterValidationErrors(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/register');
        $csrfToken = $crawler->filter('input[name="registration_form[_token]"]')->attr('value');

        // Submit with empty username
        $client->request('POST', '/register', [
            'registration_form' => [
                'username' => '', // Invalid
                'password' => 'testpassword',
                '_token' => $csrfToken,
            ]
        ]);

        self::assertFalse($client->getResponse()->isRedirect());
        // Depending on server-side validation rendering, status might be 200 or 422 (if using Turbo Frame for validation messages)
        // For now, let's assume it re-renders the form with errors, so it's a successful response code but contains error messages.
        // A more specific check for Symfony forms is to check for 'form-error-message' or similar classes.
        // The NotBlank constraint message "Please enter a username" is not directly in the HTML source by default with simple form_widget.
        // It's usually attached to the form field itself.
        // Let's check if the page still contains the form and potentially an error indicator.
        // A more robust check would be to look for specific error messages if they are rendered.
        // For now, we check that we are still on the registration page (e.g. h1 is Register)
        // and that there's an indicator of an invalid field if possible.
        // Symfony typically adds 'is-invalid' class to fields with errors if using Bootstrap form themes.
        // Or the message "This value should not be blank." might appear.
        self::assertSelectorTextContains('h1', 'Register'); // Still on the registration page
        // This assertion might be too generic or might fail if no specific error message is rendered or if form theming is different.
        // Looking for the "is-invalid" class is more common with Bootstrap theming.
        // self::assertSelectorExists('form .is-invalid'); // This would be a good check with Bootstrap
        // Let's check for the standard HTML5 validation message if it's rendered, or a generic error.
        // Symfony's default NotBlank message is "This value should not be blank."
        // This message is usually rendered next to the field or in a summary.
        // We'll assume it's rendered somewhere in the form.
        $responseContent = $client->getResponse()->getContent();
        self::assertStringContainsString('Please enter a username', $responseContent);


        // Submit with empty password
        $crawler = $client->request('GET', '/register'); // Re-fetch CSRF for next attempt
        $csrfToken = $crawler->filter('input[name="registration_form[_token]"]')->attr('value');
        $client->request('POST', '/register', [
            'registration_form' => [
                'username' => 'testuser_validation',
                'password' => '', // Invalid
                '_token' => $csrfToken,
            ]
        ]);
        self::assertFalse($client->getResponse()->isRedirect());
        $responseContent = $client->getResponse()->getContent();
        self::assertStringContainsString('Please enter a password', $responseContent);
    }
}
