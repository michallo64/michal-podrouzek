<?php

declare(strict_types=1);

namespace Podrouzek\PromoShowcase\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

/**
 * A single career milestone: either the start of a TYPO3 career, or a
 * shipped extension. Dashboard widgets aggregate these into live stats.
 */
class Milestone extends AbstractEntity
{
    protected string $milestoneType = '';

    protected string $title = '';

    protected ?\DateTime $milestoneDate = null;

    protected string $description = '';

    public function getMilestoneType(): string
    {
        return $this->milestoneType;
    }

    public function setMilestoneType(string $milestoneType): void
    {
        $this->milestoneType = $milestoneType;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getMilestoneDate(): ?\DateTime
    {
        return $this->milestoneDate;
    }

    public function setMilestoneDate(?\DateTime $milestoneDate): void
    {
        $this->milestoneDate = $milestoneDate;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }
}
