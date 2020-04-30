<?php

namespace App\Service\TokenExtractor;

use Exception;
use Symfony\Component\HttpFoundation\Request;

interface TokenExtractorInterface
{
    /**
     * @param Request $request
     *
     * @return string
     *
     * @throws Exception
     */
    public function extract(Request $request): string;
}
