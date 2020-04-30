<?php

namespace App\Exception;

use Symfony\Component\Security\Core\Exception\AuthenticationException;

class AccountNotFoundException extends AuthenticationException
{
    /**
     * @var int
     */
    private $id;

    /**
     * @return string
     */
    public function getMessageKey(): string
    {
        return 'Account could not be found.';
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return array
     */
    public function getMessageData(): array
    {
        return ['{{ id }}' => $this->id];
    }

    /**
     * @return array
     */
    public function __serialize(): array
    {
        return [$this->id, parent::__serialize()];
    }

    /**
     * @param array $data
     */
    public function __unserialize(array $data): void
    {
        [$this->id, $parentData] = $data;
        parent::__unserialize($parentData);
    }
}
