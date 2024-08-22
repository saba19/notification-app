<?php

namespace App\Tests\Behat;

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\KernelInterface;

class BaseContext implements Context
{
    private KernelInterface $kernel;

    private EntityManagerInterface $entityManager;

    public function __construct(KernelInterface $kernel, EntityManagerInterface $entityManager)
    {
        $this->kernel = $kernel;
        $this->entityManager = $entityManager;
    }

    public function getEntityManager(): EntityManagerInterface
    {
        return $this->entityManager;
    }

    public function getKernel(): KernelInterface
    {
        return $this->kernel;
    }

    public function getConnection(): Connection
    {
        return $this->entityManager->getConnection();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function parseTableNode(TableNode $tableNode): array
    {
        $parsedArray = [];
        $headers = $tableNode->getRow(0);
        foreach ($tableNode->getRows() as $index => $row) {
            if (0 === $index) {
                continue;
            }
            $parsedRow = [];
            foreach ($headers as $columnIndex => $header) {
                $parsedRow[$header] = $row[$columnIndex];
            }
            $parsedArray[] = $parsedRow;
        }

        return $parsedArray;
    }
}
