<?php

namespace App\DTO;

use App\Entity\AuthCredential;

class AuthCredentialDTO
{
    private $method;

    private $name;

    /**
     * AuthCredentialDTO constructor.
     *
     * @param AuthCredential $credential
     */
    public function __construct(AuthCredential $credential)
    {
        $this->method = $credential->getMethod();
        $this->name = $credential->getName();
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'method' => $this->method,
            'name' => $this->name,
        ];
    }
}
