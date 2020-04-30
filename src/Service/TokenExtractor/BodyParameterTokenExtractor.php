<?php

namespace App\Service\TokenExtractor;

use Symfony\Component\HttpFoundation\Request;

class BodyParameterTokenExtractor implements TokenExtractorInterface
{
    /**
     * @var string
     */
    private $parameterName;

    /**
     * BodyParameterTokenExtractor constructor.
     *
     * @param string $parameterName
     */
    public function __construct(string $parameterName = 'access_token')
    {
        $this->parameterName = $parameterName;
    }

    /**
     * @param Request $request
     *
     * @return string
     */
    public function extract(Request $request): string
    {
        return $request->get($this->parameterName);
    }
}
