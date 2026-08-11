<?php

declare(strict_types=1);

namespace Podrouzek\PromoShowcase\Dashboard;

use Podrouzek\PromoShowcase\Domain\Repository\MilestoneRepository;
use TYPO3\CMS\Dashboard\Widgets\NumberWithIconDataProviderInterface;

/**
 * Years since the "career_start" milestone, calculated on every request
 * from the milestone table rather than hardcoded.
 */
class ExperienceYearsDataProvider implements NumberWithIconDataProviderInterface
{
    public function __construct(
        private readonly MilestoneRepository $milestoneRepository,
    ) {}

    public function getNumber(): int
    {
        $careerStart = $this->milestoneRepository->findCareerStart();
        if ($careerStart === null || $careerStart->getMilestoneDate() === null) {
            return 0;
        }

        return (int)$careerStart->getMilestoneDate()->diff(new \DateTime())->y;
    }
}
