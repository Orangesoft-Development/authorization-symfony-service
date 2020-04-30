<?php

namespace App\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SessionRepository")
 * @ORM\Table(name="sessions")
 * @UniqueEntity("refreshToken")
 */
class Session
{
    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(type="text", unique=true)
     *
     * @Assert\NotBlank()
     */
    private $accessToken;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=128, unique=true)
     *
     * @Assert\Length(max=128)
     * @Assert\NotBlank()
     */
    private $refreshToken;

    /**
     * @var AuthCredential
     *
     * @ORM\ManyToOne(targetEntity="AuthCredential")
     * @ORM\JoinColumn(name="credential_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $authCredential;

    /**
     * @var DateTime
     *
     * @ORM\Column(type="datetime")
     * @Assert\NotBlank()
     */
    private $valid;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getAccessToken(): string
    {
        return $this->accessToken;
    }

    /**
     * @param string $accessToken
     *
     * @return Session
     */
    public function setAccessToken(string $accessToken): self
    {
        $this->accessToken = $accessToken;

        return $this;
    }

    /**
     * @return string
     */
    public function getRefreshToken(): string
    {
        return $this->refreshToken;
    }

    /**
     * @param string $refreshToken
     *
     * @return Session
     */
    public function setRefreshToken(string $refreshToken): self
    {
        $this->refreshToken = $refreshToken;

        return $this;
    }

    /**
     * @return AuthCredential
     */
    public function getAuthCredential(): AuthCredential
    {
        return $this->authCredential;
    }

    /**
     * @param AuthCredential $authCredential
     *
     * @return Session
     */
    public function setAuthCredential(AuthCredential $authCredential): self
    {
        $this->authCredential = $authCredential;

        return $this;
    }

    /**
     * @return DateTime
     */
    public function getValid(): DateTime
    {
        return $this->valid;
    }

    /**
     * @param DateTime $valid
     *
     * @return Session
     */
    public function setValid(DateTime $valid): self
    {
        $this->valid = $valid;

        return $this;
    }
}
