<?php

declare(strict_types=1);

namespace MyWeeklyAllowance\Tests;

use PHPUnit\Framework\TestCase;
use MyWeeklyAllowance\Account;
use MyWeeklyAllowance\Exception\InvalidAmountException;
use MyWeeklyAllowance\Exception\InsufficientFundsException;
use MyWeeklyAllowance\Exception\InvalidNameException;

/**
 * Tests unitaires pour la classe Account
 * Phase RED du TDD - Les tests sont écrits avant l'implémentation
 */
class AccountTest extends TestCase
{
    /**
     * @test
     * Vérifie qu'on peut créer un compte avec un nom
     */
    public function it_can_create_an_account_with_a_name(): void
    {
        $account = new Account('Lucas');

        $this->assertInstanceOf(Account::class, $account);
        $this->assertEquals('Lucas', $account->getName());
    }

    /**
     * @test
     * Vérifie qu'un nouveau compte a un solde initial de zéro par défaut
     */
    public function a_new_account_has_zero_balance_by_default(): void
    {
        $account = new Account('Emma');

        $this->assertEquals(0.0, $account->getBalance());
    }

    /**
     * @test
     * Vérifie qu'on peut créer un compte avec un solde initial
     */
    public function it_can_create_an_account_with_initial_balance(): void
    {
        $account = new Account('Lucas', 50.00);

        $this->assertEquals(50.00, $account->getBalance());
    }

    /**
     * @test
     * Vérifie qu'on ne peut pas créer un compte avec un nom vide
     */
    public function it_throws_exception_for_empty_name(): void
    {
        $this->expectException(InvalidNameException::class);
        $this->expectExceptionMessage('Le nom ne peut pas être vide');

        new Account('');
    }

    /**
     * @test
     * Vérifie qu'on ne peut pas créer un compte avec un nom contenant uniquement des espaces
     */
    public function it_throws_exception_for_whitespace_only_name(): void
    {
        $this->expectException(InvalidNameException::class);

        new Account('   ');
    }

    /**
     * @test
     * Vérifie qu'on ne peut pas créer un compte avec un solde initial négatif
     */
    public function it_throws_exception_for_negative_initial_balance(): void
    {
        $this->expectException(InvalidAmountException::class);
        $this->expectExceptionMessage('Le montant initial ne peut pas être négatif');

        new Account('Lucas', -10.00);
    }

    /**
     * @test
     * Vérifie que le nom est trimé (espaces supprimés)
     */
    public function it_trims_the_account_name(): void
    {
        $account = new Account('  Lucas  ');

        $this->assertEquals('Lucas', $account->getName());
    }

    /**
     * @test
     * Vérifie qu'on peut déposer de l'argent sur un compte
     */
    public function it_can_deposit_money(): void
    {
        $account = new Account('Lucas');

        $account->deposit(25.00);

        $this->assertEquals(25.00, $account->getBalance());
    }

    /**
     * @test
     * Vérifie que les dépôts s'accumulent
     */
    public function deposits_accumulate(): void
    {
        $account = new Account('Lucas');

        $account->deposit(25.00);
        $account->deposit(15.00);

        $this->assertEquals(40.00, $account->getBalance());
    }

    /**
     * @test
     * Vérifie qu'on peut déposer des centimes
     */
    public function it_can_deposit_cents(): void
    {
        $account = new Account('Lucas');

        $account->deposit(10.50);

        $this->assertEquals(10.50, $account->getBalance());
    }

    /**
     * @test
     * Vérifie qu'on ne peut pas déposer un montant négatif
     */
    public function it_throws_exception_for_negative_deposit(): void
    {
        $account = new Account('Lucas');

        $this->expectException(InvalidAmountException::class);
        $this->expectExceptionMessage('Le montant du dépôt doit être positif');

        $account->deposit(-10.00);
    }

    /**
     * @test
     * Vérifie qu'on ne peut pas déposer zéro
     */
    public function it_throws_exception_for_zero_deposit(): void
    {
        $account = new Account('Lucas');

        $this->expectException(InvalidAmountException::class);
        $this->expectExceptionMessage('Le montant du dépôt doit être positif');

        $account->deposit(0);
    }

    /**
     * @test
     * Vérifie que deposit retourne le nouveau solde
     */
    public function deposit_returns_new_balance(): void
    {
        $account = new Account('Lucas', 20.00);

        $newBalance = $account->deposit(15.00);

        $this->assertEquals(35.00, $newBalance);
    }

    /**
     * @test
     * Vérifie qu'on peut enregistrer une dépense
     */
    public function it_can_record_an_expense(): void
    {
        $account = new Account('Lucas', 50.00);

        $account->withdraw(20.00);

        $this->assertEquals(30.00, $account->getBalance());
    }

    /**
     * @test
     * Vérifie qu'on peut enregistrer plusieurs dépenses
     */
    public function it_can_record_multiple_expenses(): void
    {
        $account = new Account('Lucas', 100.00);

        $account->withdraw(20.00);
        $account->withdraw(15.50);

        $this->assertEquals(64.50, $account->getBalance());
    }

    /**
     * @test
     * Vérifie qu'on ne peut pas retirer plus que le solde disponible
     */
    public function it_throws_exception_for_insufficient_funds(): void
    {
        $account = new Account('Lucas', 30.00);

        $this->expectException(InsufficientFundsException::class);
        $this->expectExceptionMessage('Fonds insuffisants');

        $account->withdraw(50.00);
    }

    /**
     * @test
     * Vérifie qu'on ne peut pas retirer un montant négatif
     */
    public function it_throws_exception_for_negative_withdrawal(): void
    {
        $account = new Account('Lucas', 50.00);

        $this->expectException(InvalidAmountException::class);
        $this->expectExceptionMessage('Le montant du retrait doit être positif');

        $account->withdraw(-10.00);
    }

    /**
     * @test
     * Vérifie qu'on ne peut pas retirer zéro
     */
    public function it_throws_exception_for_zero_withdrawal(): void
    {
        $account = new Account('Lucas', 50.00);

        $this->expectException(InvalidAmountException::class);

        $account->withdraw(0);
    }

    /**
     * @test
     * Vérifie qu'on peut retirer exactement le solde disponible
     */
    public function it_can_withdraw_exact_balance(): void
    {
        $account = new Account('Lucas', 50.00);

        $account->withdraw(50.00);

        $this->assertEquals(0.0, $account->getBalance());
    }

    /**
     * @test
     * Vérifie que withdraw retourne le nouveau solde
     */
    public function withdraw_returns_new_balance(): void
    {
        $account = new Account('Lucas', 50.00);

        $newBalance = $account->withdraw(20.00);

        $this->assertEquals(30.00, $newBalance);
    }

    /**
     * @test
     * Vérifie le message d'erreur pour fonds insuffisants contient les détails
     */
    public function insufficient_funds_exception_contains_details(): void
    {
        $account = new Account('Lucas', 30.00);

        try {
            $account->withdraw(50.00);
            $this->fail('Expected InsufficientFundsException was not thrown');
        } catch (InsufficientFundsException $e) {
            $this->assertStringContainsString('30', $e->getMessage());
            $this->assertStringContainsString('50', $e->getMessage());
        }
    }

    /**
     * @test
     * Vérifie qu'un nouveau compte a un historique vide
     */
    public function a_new_account_has_empty_transaction_history(): void
    {
        $account = new Account('Lucas');

        $this->assertEmpty($account->getTransactionHistory());
    }

    /**
     * @test
     * Vérifie que les dépôts sont enregistrés dans l'historique
     */
    public function deposits_are_recorded_in_history(): void
    {
        $account = new Account('Lucas');
        $account->deposit(25.00);

        $history = $account->getTransactionHistory();

        $this->assertCount(1, $history);
        $this->assertEquals('deposit', $history[0]['type']);
        $this->assertEquals(25.00, $history[0]['amount']);
    }

    /**
     * @test
     * Vérifie que les retraits sont enregistrés dans l'historique
     */
    public function withdrawals_are_recorded_in_history(): void
    {
        $account = new Account('Lucas', 50.00);
        $account->withdraw(20.00);

        $history = $account->getTransactionHistory();

        $this->assertCount(1, $history);
        $this->assertEquals('withdrawal', $history[0]['type']);
        $this->assertEquals(20.00, $history[0]['amount']);
    }

    /**
     * @test
     * Vérifie que l'historique contient la date de la transaction
     */
    public function transaction_history_contains_date(): void
    {
        $account = new Account('Lucas');
        $account->deposit(25.00);

        $history = $account->getTransactionHistory();

        $this->assertArrayHasKey('date', $history[0]);
        $this->assertInstanceOf(\DateTimeInterface::class, $history[0]['date']);
    }

    /**
     * @test
     * Vérifie que l'historique contient le solde après transaction
     */
    public function transaction_history_contains_balance_after(): void
    {
        $account = new Account('Lucas', 100.00);
        $account->withdraw(30.00);

        $history = $account->getTransactionHistory();

        $this->assertArrayHasKey('balance_after', $history[0]);
        $this->assertEquals(70.00, $history[0]['balance_after']);
    }

    /**
     * @test
     * Vérifie qu'on peut ajouter une description à une transaction
     */
    public function it_can_add_description_to_withdrawal(): void
    {
        $account = new Account('Lucas', 100.00);
        $account->withdraw(20.00, 'Achat jeu vidéo');

        $history = $account->getTransactionHistory();

        $this->assertEquals('Achat jeu vidéo', $history[0]['description']);
    }

    /**
     * @test
     * Vérifie qu'on peut ajouter une description à un dépôt
     */
    public function it_can_add_description_to_deposit(): void
    {
        $account = new Account('Lucas');
        $account->deposit(50.00, 'Cadeau anniversaire');

        $history = $account->getTransactionHistory();

        $this->assertEquals('Cadeau anniversaire', $history[0]['description']);
    }
}
