<?php

namespace App\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\PersistentCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass="App\Repository\AccountRepository")
 * @ORM\Table(name="accounts")
 */
class Account implements UserInterface
{
    const ROLE_DEFAULT = 'ROLE_USER';

    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    protected $enabled = false;

    /**
     * @var DateTime|null
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $lastLogin;

    /**
     * @var string|null
     *
     * @ORM\Column(type="string", length=180, nullable=true)
     *
     * @Assert\Length(max=180)
     */
    protected $name;

    /**
     * @var DateTime
     *
     * @ORM\Column(type="date", nullable=true)
     */
    protected $birthday;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=2, nullable=true)
     *
     * @Assert\Length(min=2, max=2)
     */
    protected $countryId;

    /**
     * @var int
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $cityId;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     *
     * @Assert\Length(max=255)
     */
    protected $avatarUrl;

    /**
     * @var AuthCredential[]|ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="AuthCredential", mappedBy="account", orphanRemoval=true)
     */
    protected $authCredentials;

    /**
     * Account constructor.
     */
    public function __construct()
    {
        $this->authCredentials = new ArrayCollection();
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getAccountId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->getUsername();
    }

    /**
     * @return string
     */
    public function getUsername(): string
    {
        return '';
    }

    /**
     * @return string|null
     */
    public function getPassword(): ?string
    {
        return null;
    }

    public function eraseCredentials(): void
    {
    }

    /**
     * @return string[]
     */
    public function getRoles(): array
    {
        return [static::ROLE_DEFAULT];
    }

    /**
     * @return string|null
     */
    public function getSalt(): ?string
    {
        return null;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string|null $name
     *
     * @return $this
     */
    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return DateTime|null
     */
    public function getLastLogin(): ?DateTime
    {
        return $this->lastLogin;
    }

    /**
     * @param DateTime|null $time
     *
     * @return $this
     */
    public function setLastLogin(DateTime $time = null): self
    {
        $this->lastLogin = $time;

        return $this;
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * @param bool $enabled
     *
     * @return $this
     */
    public function setEnabled(bool $enabled): self
    {
        $this->enabled = $enabled;

        return $this;
    }

    /**
     * @return DateTime|null
     */
    public function getBirthday(): ?DateTime
    {
        return $this->birthday;
    }

    /**
     * @param DateTime $birthday
     *
     * @return $this
     */
    public function setBirthday(DateTime $birthday): self
    {
        $this->birthday = $birthday;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getCountryId(): ?string
    {
        return $this->countryId;
    }

    /**
     * @param string $countryId
     *
     * @return Account
     */
    public function setCountryId(string $countryId): self
    {
        if ($this->countryId != $countryId) {
            $this->countryId = strtoupper($countryId);
            $this->cityId = null;
        }

        return $this;
    }

    /**
     * @return int|null
     */
    public function getCityId(): ?int
    {
        return $this->cityId;
    }

    /**
     * @param int $cityId
     *
     * @return Account
     */
    public function setCityId(int $cityId): self
    {
        $this->cityId = $cityId;

        return $this;
    }

    /**
     * @return AuthCredential[]|ArrayCollection|PersistentCollection
     */
    public function getAuthCredentials(): PersistentCollection
    {
        return $this->authCredentials;
    }

    /**
     * @param AuthCredential $credential
     *
     * @return $this
     */
    public function addAuthCredential(AuthCredential $credential): self
    {
        if (!$this->authCredentials->contains($credential)) {
            $this->authCredentials[] = $credential;
            $credential->setAccount($this);
        }

        return $this;
    }

    /**
     * @param AuthCredential $credential
     *
     * @return $this
     */
    public function removeAuthCredential(AuthCredential $credential): self
    {
        if ($this->authCredentials->contains($credential)) {
            $this->authCredentials->removeElement($credential);
        }

        return $this;
    }

    /**
     * @return string|null
     */
    public function getAvatarUrl(): ?string
    {
        return $this->avatarUrl;
    }

    /**
     * @param string|null $avatarUrl
     *
     * @return $this
     */
    public function setAvatarUrl(?string $avatarUrl): self
    {
        $this->avatarUrl = $avatarUrl;

        return $this;
    }
}
