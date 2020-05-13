<?php

namespace App\Tests\Functional;

use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Schema\Sequence;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class AbstractWebTestCase extends WebTestCase
{
    /**
     * @var KernelBrowser
     */
    protected static $client;

    /**
     * @var EntityManagerInterface
     */
    protected static $entityManager;

    /**
     * @throws DBALException
     */
    public static function setUpBeforeClass(): void
    {
        self::ensureKernelShutdown();

        self::$client = self::createClient();

        self::tearDownAfterClass();
    }

    /**
     * @throws DBALException
     */
    public static function tearDownAfterClass(): void
    {
        $testCase = new static();

        $testCase->truncateEntities($testCase->getUsedEntities());

        if (self::$entityManager !== null) {
            $testCase->closeEntityManager();
        }
    }

    protected function setUp(): void
    {
        static::ensureKernelShutdown();
    }

    protected function tearDown(): void
    {
        $this->closeEntityManager();
    }

    /**
     * @param array $entities
     *
     * @throws DBALException
     */
    protected function truncateEntities(array $entities): void
    {
        $connection = $this->getEntityManager()->getConnection();
        $databasePlatform = $connection->getDatabasePlatform();

        foreach ($entities as $entity) {
            $table = $this->getEntityManager()->getClassMetadata($entity)->getTableName();

            $queryTruncate = $databasePlatform->getTruncateTableSQL($table, true);
            $connection->executeUpdate($queryTruncate);
        }

        $this->restartSequences($entities);
    }

    /**
     * @param array $entities
     *
     * @throws DBALException
     */
    protected function restartSequences(array $entities)
    {
        $connection = $this->getEntityManager()->getConnection();
        $databasePlatform = $connection->getDatabasePlatform();

        foreach ($entities as $entity) {
            $table = $this->getEntityManager()->getClassMetadata($entity)->getTableName();
            $identifiers = $this->getEntityManager()->getClassMetadata($entity)->getIdentifierColumnNames();
            foreach ($identifiers as $identifier) {
                $sequenceName = $databasePlatform->getIdentitySequenceName($table, $identifier);
                $sequence = new Sequence($sequenceName);
                $querySequence = $databasePlatform->getAlterSequenceSQL($sequence).' RESTART WITH 1';
                $connection->executeUpdate($querySequence);
            }
        }
    }

    /**
     * @return ContainerInterface
     */
    protected function getContainer(): ContainerInterface
    {
        if (self::$container === null) {
            self::bootKernel();
        }

        return self::$container;
    }

    protected function getEntityManager(): EntityManagerInterface
    {
        if (self::$entityManager === null) {
            self::$entityManager = $this->getContainer()->get(EntityManagerInterface::class);
        }

        return self::$entityManager;
    }

    protected function closeEntityManager(): void
    {
        $this->getEntityManager()->close();
        self::$entityManager = null;
    }

    /**
     * @return array
     */
    abstract protected function getUsedEntities(): array;
}
