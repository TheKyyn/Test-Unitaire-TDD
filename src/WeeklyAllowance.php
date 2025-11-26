<?php

declare(strict_types=1);

namespace MyWeeklyAllowance;

use MyWeeklyAllowance\Exception\InvalidAmountException;
use DateTimeImmutable;
use DateTimeInterface;
use InvalidArgumentException;

class WeeklyAllowance
{
    private readonly Account $account;
    private float $amount;
    private bool $active = false;
    private int $paymentDay = 1;
    private ?DateTimeInterface $lastPaymentDate = null;

    public function __construct(Account $account, float $amount)
    {
        if ($amount <= 0) {
            throw new InvalidAmountException("Le montant de l'allocation doit être positif");
        }

        $this->account = $account;
        $this->amount = $amount;
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function getAccount(): Account
    {
        return $this->account;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function activate(): void
    {
        $this->active = true;
    }

    public function deactivate(): void
    {
        $this->active = false;
    }

    public function setAmount(float $amount): void
    {
        if ($amount <= 0) {
            throw new InvalidAmountException("Le montant de l'allocation doit être positif");
        }

        $this->amount = $amount;
    }

    public function getPaymentDay(): int
    {
        return $this->paymentDay;
    }

    public function setPaymentDay(int $day): void
    {
        if ($day < 1 || $day > 7) {
            throw new InvalidArgumentException(
                'Le jour de versement doit être entre 1 (lundi) et 7 (dimanche)'
            );
        }

        $this->paymentDay = $day;
    }

    public function isDue(): bool
    {
        if (!$this->active) {
            return false;
        }

        if ($this->lastPaymentDate === null) {
            return true;
        }

        $now = new DateTimeImmutable();
        $daysSinceLastPayment = $now->diff($this->lastPaymentDate)->days;

        return $daysSinceLastPayment >= 7;
    }

    public function process(): bool
    {
        if (!$this->active) {
            return false;
        }

        $this->account->deposit($this->amount, 'Allocation hebdomadaire');
        $this->lastPaymentDate = new DateTimeImmutable();

        return true;
    }

    public function getLastPaymentDate(): ?DateTimeInterface
    {
        return $this->lastPaymentDate;
    }

    public function getNextPaymentDate(): ?DateTimeInterface
    {
        if (!$this->active) {
            return null;
        }

        $now = new DateTimeImmutable();

        if ($this->lastPaymentDate === null) {
            $currentDayOfWeek = (int) $now->format('N');
            $daysUntilPayment = ($this->paymentDay - $currentDayOfWeek + 7) % 7;

            if ($daysUntilPayment === 0) {
                return $now;
            }

            return $now->modify("+{$daysUntilPayment} days");
        }

        return $this->lastPaymentDate->modify('+7 days');
    }
}
