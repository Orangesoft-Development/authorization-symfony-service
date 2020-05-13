<?php

namespace App\Tests\Service\TokenExtractor;

use App\Service\TokenExtractor\BodyParameterTokenExtractor;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

class BodyParameterTokenExtractorTest extends TestCase
{
    public function testExtract(): void
    {
        $parameterName = 'access_token';
        $token = 'access_token';

        $bodyParameterTokenExtractor = new BodyParameterTokenExtractor($parameterName);
        $request = new Request([], [$parameterName => $token]);

        $this->assertSame($token, $bodyParameterTokenExtractor->extract($request));
    }
}
