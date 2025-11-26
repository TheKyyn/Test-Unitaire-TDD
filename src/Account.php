<?php

declare(strict_types=1);

namespace MyWeeklyAllowance;

use MyWeeklyAllowance\Exception\InvalidNameException;
use MyWeeklyAllowance\Exception\InvalidAmountException;
use MyWeeklyAllowance\Exception\InsufficientFundsException;
use DateTimeImmutable;

class Account
{
    private readonly string $name;
    private float $balance;
    private array $transactionHistory = [];

    public function __construct(string $name, float $initialBalance = 0.0)
    {
        $trimmedName = trim($name);

        if ($trimmedName === '') {
            throw new InvalidNameException('Le nom ne peut pas être vide');
        }

        if ($initialBalance < 0) {
            throw new InvalidAmountException('Le montant initial ne peut pas être négatif');
        }

        $this->name = $trimmedName;
        $this->balance = $initialBalance;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getBalance(): float
    {
        return $this->balance;
    }

    public function deposit(float $amount, ?string $description = null): float
    {
        if ($amount <= 0) {
            throw new InvalidAmountException('Le montant du dépôt doit être positif');
        }

        $this->balance += $amount;

        $this->recordTransaction(TransactionType::DEPOSIT, $amount, $description);

        return $this->balance;
    }

    public function withdraw(float $amount, ?string $description = null): float
    {
        if ($amount <= 0) {
            throw new InvalidAmountException('Le montant du retrait doit être positif');
        }

        if ($amount > $this->balance) {
            throw new InsufficientFundsException(
                sprintf(
                    'Fonds insuffisants. Solde disponible: %.2f€, montant demandé: %.2f€',
                    $this->balance,
                    $amount
                )
            );
        }

        $this->balance -= $amount;

        $this->recordTransaction(TransactionType::WITHDRAWAL, $amount, $description);

        return $this->balance;
    }

    public function getTransactionHistory(): array
    {
        return $this->transactionHistory;
    }

    private function recordTransaction(TransactionType $type, float $amount, ?string $description): void
    {
        $this->transactionHistory[] = [
            'type' => $type->value,
            'amount' => $amount,
            'date' => new DateTimeImmutable(),
            'balance_after' => $this->balance,
            'description' => $description,
        ];
    }
}
