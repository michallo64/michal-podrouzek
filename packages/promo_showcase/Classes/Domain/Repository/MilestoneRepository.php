<?php

declare(strict_types=1);

namespace Podrouzek\PromoShowcase\Domain\Repository;

use Podrouzek\PromoShowcase\Domain\Model\Milestone;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\Repository;

/**
 * @extends Repository<Milestone>
 */
class MilestoneRepository extends Repository
{
    /**
     * Milestone records are administrative data, not tied to a page tree
     * position, so they must be readable regardless of which pid they live on.
     * Set explicitly per query rather than via initializeObject(): dashboard
     * widgets construct this repository through the core DI container, which
     * does not reliably invoke Extbase's initializeObject() lifecycle hook.
     */
    private function createUnrestrictedQuery(): \TYPO3\CMS\Extbase\Persistence\QueryInterface
    {
        $query = $this->createQuery();
        $querySettings = $query->getQuerySettings();
        $querySettings->setRespectStoragePage(false);
        $query->setQuerySettings($querySettings);

        return $query;
    }

    public function findCareerStart(): ?Milestone
    {
        $query = $this->createUnrestrictedQuery();
        $query->matching($query->equals('milestoneType', 'career_start'));
        $query->setOrderings(['milestoneDate' => QueryInterface::ORDER_ASCENDING]);
        $query->setLimit(1);

        return $query->execute()->getFirst();
    }

    public function countExtensionsShipped(): int
    {
        $query = $this->createUnrestrictedQuery();
        $query->matching($query->equals('milestoneType', 'extension_shipped'));

        return $query->execute()->count();
    }
}
