<?php


namespace App\Storage;


use App\Entity\AuthCredential;

class AuthCredentialStorage
{
    /**
     * @var AuthCredential
     */
    private $credential;

    /**
     * @return AuthCredential
     */
    public function getCredential(): AuthCredential
    {
        return $this->credential;
    }

    /**
     * @param AuthCredential $credential
     */
    public function setCredential(AuthCredential $credential): void
    {
        $this->credential = $credential;
    }
}
