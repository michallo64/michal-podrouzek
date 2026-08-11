<?php

declare(strict_types=1);

namespace Podrouzek\PromoShowcase\Dashboard;

use Podrouzek\PromoShowcase\Domain\Repository\MilestoneRepository;
use TYPO3\CMS\Dashboard\Widgets\NumberWithIconDataProviderInterface;

/**
 * Count of "extension_shipped" milestones.
 */
class ExtensionsShippedDataProvider implements NumberWithIconDataProviderInterface
{
    public function __construct(
        private readonly MilestoneRepository $milestoneRepository,
    ) {}

    public function getNumber(): int
    {
        return $this->milestoneRepository->countExtensionsShipped();
    }
}
