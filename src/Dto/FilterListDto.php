<?php

namespace EscolaLms\Webinar\Dto;

use EscolaLms\Core\Repositories\Criteria\Primitives\WhereCriterion;
use EscolaLms\Core\Repositories\Criteria\Primitives\WhereNotInOrIsNullCriterion;
use EscolaLms\Webinar\Models\Webinar;
use EscolaLms\Webinar\Repositories\Criteria\WebinarIncomingCriterion;
use EscolaLms\Webinar\Repositories\Criteria\WebinarSearch;
use EscolaLms\Core\Repositories\Criteria\Primitives\DateCriterion;
use EscolaLms\Core\Repositories\Criteria\Primitives\EqualCriterion;
use EscolaLms\Core\Repositories\Criteria\Primitives\InCriterion;
use EscolaLms\Webinar\Repositories\Criteria\WebinarTagsCriterion;

class FilterListDto extends BaseDto
{
    private string $name;
    private array $status;
    private array $reminderStatus;
    private string $dateTo;
    private string $dateFrom;
    private string $dateTimeTo;
    private string $dateTimeToLowerThan;
    private string $dateTimeFrom;
    private array $tags;

    private string $onlyIncoming;
    private bool $incomingWithDuration;

    private array $criteria = [];

    public static function prepareFilters(array $search)
    {
        $dto = new self($search);
        if ($dto->getName()) {
            $dto->addToCriteria(new WebinarSearch($dto->getName()));
        }
        if ($dto->getStatus()) {
            $dto->addToCriteria(new InCriterion($dto->model()->getTable() . '.status', $dto->getStatus()));
        }
        if ($dto->getDateFrom()) {
            $dto->addToCriteria(new DateCriterion($dto->model()->getTable() . '.active_from', $dto->getDateFrom(), '>='));
        }
        if ($dto->getDateTo()) {
            $dto->addToCriteria(new DateCriterion($dto->model()->getTable() . '.active_to', $dto->getDateTo(), '<='));
        }
        if ($dto->getDateTimeFrom()) {
            $dto->addToCriteria(new WhereCriterion($dto->model()->getTable() . '.active_from', $dto->getDateTimeFrom(), '>='));
        }
        if ($dto->getDateTimeTo()) {
            $dto->addToCriteria(new WhereCriterion($dto->model()->getTable() . '.active_to', $dto->getDateTimeTo(), '<='));
        }
        if ($dto->getDateTimeToLowerThan()) {
            $dto->addToCriteria(new WhereCriterion($dto->model()->getTable() . '.active_to', $dto->getDateTimeToLowerThan(), '>='));
        }
        if ($dto->getTags()) {
            $dto->addToCriteria(new WebinarTagsCriterion($dto->getTags()));
        }
        if ($dto->getReminderStatus()) {
            $dto->addToCriteria(new WhereNotInOrIsNullCriterion($dto->model()->getTable() . '.reminder_status', $dto->getReminderStatus()));
        }
        if ($dto->getOnlyIncoming()) {
            $dto->addToCriteria(new WebinarIncomingCriterion(now()->format('Y-m-d H:i:s'), $dto->getIncomingWithDuration()));
        }
        return $dto->criteria;
    }

    public function model(): Webinar
    {
        return Webinar::newModelInstance();
    }

    public function getName(): ?string
    {
        return $this->name ?? null;
    }

    public function getStatus(): ?array
    {
        return $this->status ?? null;
    }

    public function getDateFrom(): ?string
    {
        return $this->dateFrom ?? null;
    }

    public function getDateTo(): ?string
    {
        return $this->dateTo ?? null;
    }

    public function getTags(): ?array
    {
        return $this->tags ?? null;
    }

    public function getDateTimeFrom(): ?string
    {
        return $this->dateTimeFrom ?? null;
    }

    public function getDateTimeTo(): ?string
    {
        return $this->dateTimeTo ?? null;
    }

    public function getDateTimeToLowerThan(): ?string
    {
        return $this->dateTimeToLowerThan ?? null;
    }

    public function getReminderStatus(): ?array
    {
        return $this->reminderStatus ?? null;
    }

    protected function setReminderStatus(array $reminderStatus): void
    {
        $this->reminderStatus = $reminderStatus;
    }

    protected function setName(string $name): void
    {
        $this->name = $name;
    }

    protected function setStatus(array $status): void
    {
        $this->status = $status;
    }

    protected function setDateFrom(string $dateFrom): void
    {
        $this->dateFrom = $dateFrom;
    }

    protected function setDateTo(string $dateTo): void
    {
        $this->dateTo = $dateTo;
    }

    protected function setTags(array $tags): void
    {
        $this->tags = $tags;
    }

    protected function setDateTimeFrom(string $dateTimeFrom): void
    {
        $this->dateTimeFrom = $dateTimeFrom;
    }

    protected function setDateTimeTo(string $dateTimeTo): void
    {
        $this->dateTimeTo = $dateTimeTo;
    }

    protected function setDateTimeToLowerThan(string $dateTimeToLowerThan): void
    {
        $this->dateTimeToLowerThan = $dateTimeToLowerThan;
    }

    public function getOnlyIncoming(): ?string
    {
        return $this->onlyIncoming ?? null;
    }

    protected function setOnlyIncoming(string $onlyIncoming): void
    {
        $this->onlyIncoming = $onlyIncoming;
    }

    public function getIncomingWithDuration(): ?bool
    {
        return $this->incomingWithDuration ?? null;
    }

    public function setIncomingWithDuration(bool $incomingWithDuration): void
    {
        $this->incomingWithDuration = $incomingWithDuration;
    }

    private function addToCriteria($value): void
    {
        $this->criteria[] = $value;
    }
}
