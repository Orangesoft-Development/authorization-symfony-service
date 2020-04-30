<?php

namespace App\Tests\Security\Handler;

use App\Security\Handler\AuthenticationFailureHandler;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class AuthenticationFailureHandlerTest extends TestCase
{
    public function testOnAuthenticationFailure(): void
    {
        $authenticationFailureHandler = new AuthenticationFailureHandler();

        $request = new Request();
        $exception = new AuthenticationException();

        $response = $authenticationFailureHandler->onAuthenticationFailure($request, $exception);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(JsonResponse::HTTP_UNAUTHORIZED, $response->getStatusCode());

        $content = json_decode($response->getContent());
        $this->assertSame($exception->getMessageKey(), $content);

        $this->assertTrue($response->headers->has('WWW-Authenticate'));
        $this->assertSame('Bearer', $response->headers->get('WWW-Authenticate'));
    }
}
