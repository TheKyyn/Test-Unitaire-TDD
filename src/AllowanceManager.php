<?php

declare(strict_types=1);

namespace MyWeeklyAllowance;

use MyWeeklyAllowance\Exception\AccountNotFoundException;
use MyWeeklyAllowance\Exception\DuplicateAccountException;

class AllowanceManager
{
    private array $accounts = [];
    private array $allowances = [];

    public function getAccounts(): array
    {
        return array_values($this->accounts);
    }

    public function getAccountCount(): int
    {
        return count($this->accounts);
    }

    public function addAccount(Account $account): void
    {
        $name = $account->getName();

        if (isset($this->accounts[$name])) {
            throw new DuplicateAccountException(
                sprintf("Un compte avec le nom '%s' existe déjà", $name)
            );
        }

        $this->accounts[$name] = $account;
    }

    public function getAccount(string $name): Account
    {
        if (!isset($this->accounts[$name])) {
            throw new AccountNotFoundException(
                sprintf("Compte '%s' non trouvé", $name)
            );
        }

        return $this->accounts[$name];
    }

    public function removeAccount(string $name): void
    {
        if (!isset($this->accounts[$name])) {
            throw new AccountNotFoundException(
                sprintf("Compte '%s' non trouvé", $name)
            );
        }

        unset($this->accounts[$name]);
        unset($this->allowances[$name]);
    }

    public function hasAccount(string $name): bool
    {
        return isset($this->accounts[$name]);
    }

    public function setAllowance(string $name, float $amount): WeeklyAllowance
    {
        $account = $this->getAccount($name);

        $allowance = new WeeklyAllowance($account, $amount);
        $allowance->activate();

        $this->allowances[$name] = $allowance;

        return $allowance;
    }

    public function getAllowance(string $name): ?WeeklyAllowance
    {
        return $this->allowances[$name] ?? null;
    }

    public function deactivateAllowance(string $name): void
    {
        if (isset($this->allowances[$name])) {
            $this->allowances[$name]->deactivate();
        }
    }

    public function activateAllowance(string $name): void
    {
        if (isset($this->allowances[$name])) {
            $this->allowances[$name]->activate();
        }
    }

    public function processAllDueAllowances(): int
    {
        $processed = 0;

        foreach ($this->allowances as $allowance) {
            if ($allowance->isDue() && $allowance->process()) {
                $processed++;
            }
        }

        return $processed;
    }

    public function processAllowance(string $name): bool
    {
        if (!isset($this->allowances[$name])) {
            return false;
        }

        return $this->allowances[$name]->process();
    }

    public function getTotalBalance(): float
    {
        $total = 0.0;

        foreach ($this->accounts as $account) {
            $total += $account->getBalance();
        }

        return $total;
    }

    public function getTotalWeeklyAllowances(): float
    {
        $total = 0.0;

        foreach ($this->allowances as $allowance) {
            if ($allowance->isActive()) {
                $total += $allowance->getAmount();
            }
        }

        return $total;
    }

    public function createAccount(string $name, float $initialBalance = 0.0): Account
    {
        $account = new Account($name, $initialBalance);
        $this->addAccount($account);

        return $account;
    }

    public function createAccountWithAllowance(
        string $name,
        float $initialBalance,
        float $allowanceAmount
    ): Account {
        $account = $this->createAccount($name, $initialBalance);
        $this->setAllowance($name, $allowanceAmount);

        return $account;
    }
}
