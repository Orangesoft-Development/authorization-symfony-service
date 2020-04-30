<?php

namespace App\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SmsCodeRepository")
 * @ORM\Table(name="sms_codes")
 */
class SmsCode
{
    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @var string|null
     *
     * @ORM\Column(type="string", length=180, unique=true)
     *
     * @Assert\Length(max=180)
     * @Assert\NotBlank
     */
    protected $phone;

    /**
     * The salt to use for hashing.
     *
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    protected $salt;

    /**
     * @var string|null
     *
     * @ORM\Column(type="string", length=180, unique=true, nullable=true)
     *
     * @Assert\Length(max=180)
     */
    protected $smsCode;

    /**
     * @var string|null
     */
    protected $plainSmsCode;

    /**
     * @var DateTime|null
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $smsCodeSentAt;

    /**
     * @var int|null
     *
     * @ORM\Column(type="integer", nullable=false, options={"default": 0})
     */
    protected $smsCodeSentNumber = 0;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string|null
     */
    public function getPhone(): ?string
    {
        return $this->phone;
    }

    /**
     * @param string $phone
     *
     * @return $this
     */
    public function setPhone(string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getSmsCode(): ?string
    {
        return $this->smsCode;
    }

    /**
     * @param string|null $smsCode
     *
     * @return $this
     */
    public function setSmsCode(?string $smsCode): self
    {
        $this->smsCode = $smsCode;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getPlainSmsCode(): ?string
    {
        return $this->plainSmsCode;
    }

    /**
     * @param string|null $smsCode
     *
     * @return $this
     */
    public function setPlainSmsCode(?string $smsCode): self
    {
        $this->plainSmsCode = $smsCode;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getSmsCodeSentNumber(): ?int
    {
        return $this->smsCodeSentNumber;
    }

    /**
     * @return DateTime|null
     */
    public function getSmsCodeSentAt(): ?DateTime
    {
        return $this->smsCodeSentAt;
    }

    /**
     * @param DateTime|null $date
     *
     * @return $this
     */
    public function setSmsCodeSentAt(DateTime $date = null): self
    {
        $this->smsCodeSentAt = $date;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getSalt(): ?string
    {
        return $this->salt;
    }

    /**
     * @param string|null $salt
     *
     * @return $this
     */
    public function setSalt(?string $salt): self
    {
        $this->salt = $salt;

        return $this;
    }

    /**
     * @param int $ttl
     *
     * @return bool
     */
    public function isSmsCodeSendNonExpired(int $ttl): bool
    {
        return $this->getSmsCodeSentAt() instanceof DateTime &&
            $this->getSmsCodeSentAt()->getTimestamp() + $ttl > time();
    }

    /**
     * @param int|null $smsCodeSentNumber
     *
     * @return $this
     */
    public function setSmsCodeSentNumber(?int $smsCodeSentNumber): self
    {
        $this->smsCodeSentNumber = $smsCodeSentNumber;

        return $this;
    }

    /**
     * @return $this
     */
    public function incrementSmsCodeSentNumber(): self
    {
        $this->smsCodeSentNumber++;

        return $this;
    }
}
