<?php

declare(strict_types=1);

namespace MyWeeklyAllowance\Tests;

use PHPUnit\Framework\TestCase;
use MyWeeklyAllowance\Account;
use MyWeeklyAllowance\WeeklyAllowance;
use MyWeeklyAllowance\Exception\InvalidAmountException;
use MyWeeklyAllowance\Exception\AllowanceAlreadyActiveException;

/**
 * Tests unitaires pour la classe WeeklyAllowance
 * Gère les allocations hebdomadaires automatiques
 */
class WeeklyAllowanceTest extends TestCase
{

    /**
     * @test
     * Vérifie qu'on peut créer une allocation hebdomadaire
     */
    public function it_can_create_weekly_allowance(): void
    {
        $account = new Account('Lucas');
        $allowance = new WeeklyAllowance($account, 20.00);

        $this->assertInstanceOf(WeeklyAllowance::class, $allowance);
    }

    /**
     * @test
     * Vérifie qu'on peut obtenir le montant de l'allocation
     */
    public function it_can_get_allowance_amount(): void
    {
        $account = new Account('Lucas');
        $allowance = new WeeklyAllowance($account, 20.00);

        $this->assertEquals(20.00, $allowance->getAmount());
    }

    /**
     * @test
     * Vérifie qu'on peut obtenir le compte associé
     */
    public function it_can_get_associated_account(): void
    {
        $account = new Account('Lucas');
        $allowance = new WeeklyAllowance($account, 20.00);

        $this->assertSame($account, $allowance->getAccount());
    }

    /**
     * @test
     * Vérifie qu'on ne peut pas créer une allocation avec un montant négatif
     */
    public function it_throws_exception_for_negative_allowance(): void
    {
        $account = new Account('Lucas');

        $this->expectException(InvalidAmountException::class);
        $this->expectExceptionMessage("Le montant de l'allocation doit être positif");

        new WeeklyAllowance($account, -10.00);
    }

    /**
     * @test
     * Vérifie qu'on ne peut pas créer une allocation avec un montant nul
     */
    public function it_throws_exception_for_zero_allowance(): void
    {
        $account = new Account('Lucas');

        $this->expectException(InvalidAmountException::class);

        new WeeklyAllowance($account, 0);
    }

    /**
     * @test
     * Vérifie qu'une nouvelle allocation est inactive par défaut
     */
    public function a_new_allowance_is_inactive_by_default(): void
    {
        $account = new Account('Lucas');
        $allowance = new WeeklyAllowance($account, 20.00);

        $this->assertFalse($allowance->isActive());
    }

    /**
     * @test
     * Vérifie qu'on peut activer une allocation
     */
    public function it_can_activate_allowance(): void
    {
        $account = new Account('Lucas');
        $allowance = new WeeklyAllowance($account, 20.00);

        $allowance->activate();

        $this->assertTrue($allowance->isActive());
    }

    /**
     * @test
     * Vérifie qu'on peut désactiver une allocation
     */
    public function it_can_deactivate_allowance(): void
    {
        $account = new Account('Lucas');
        $allowance = new WeeklyAllowance($account, 20.00);
        $allowance->activate();

        $allowance->deactivate();

        $this->assertFalse($allowance->isActive());
    }

    /**
     * @test
     * Vérifie qu'activer une allocation déjà active ne cause pas d'erreur
     */
    public function activating_already_active_allowance_has_no_effect(): void
    {
        $account = new Account('Lucas');
        $allowance = new WeeklyAllowance($account, 20.00);
        $allowance->activate();

        $allowance->activate();

        $this->assertTrue($allowance->isActive());
    }

    /**
     * @test
     * Vérifie que le traitement de l'allocation ajoute l'argent au compte
     */
    public function processing_allowance_adds_money_to_account(): void
    {
        $account = new Account('Lucas', 50.00);
        $allowance = new WeeklyAllowance($account, 20.00);
        $allowance->activate();

        $allowance->process();

        $this->assertEquals(70.00, $account->getBalance());
    }

    /**
     * @test
     * Vérifie que le traitement d'une allocation inactive ne fait rien
     */
    public function processing_inactive_allowance_does_nothing(): void
    {
        $account = new Account('Lucas', 50.00);
        $allowance = new WeeklyAllowance($account, 20.00);

        $allowance->process();

        $this->assertEquals(50.00, $account->getBalance());
    }

    /**
     * @test
     * Vérifie que process retourne true si l'allocation a été traitée
     */
    public function process_returns_true_when_processed(): void
    {
        $account = new Account('Lucas');
        $allowance = new WeeklyAllowance($account, 20.00);
        $allowance->activate();

        $result = $allowance->process();

        $this->assertTrue($result);
    }

    /**
     * @test
     * Vérifie que process retourne false si l'allocation est inactive
     */
    public function process_returns_false_when_inactive(): void
    {
        $account = new Account('Lucas');
        $allowance = new WeeklyAllowance($account, 20.00);

        $result = $allowance->process();

        $this->assertFalse($result);
    }

    /**
     * @test
     * Vérifie que le traitement ajoute une transaction avec description appropriée
     */
    public function processing_adds_transaction_with_allowance_description(): void
    {
        $account = new Account('Lucas');
        $allowance = new WeeklyAllowance($account, 20.00);
        $allowance->activate();

        $allowance->process();

        $history = $account->getTransactionHistory();
        $this->assertCount(1, $history);
        $this->assertEquals('deposit', $history[0]['type']);
        $this->assertStringContainsString('Allocation', $history[0]['description']);
    }

    /**
     * @test
     * Vérifie qu'on peut modifier le montant de l'allocation
     */
    public function it_can_update_allowance_amount(): void
    {
        $account = new Account('Lucas');
        $allowance = new WeeklyAllowance($account, 20.00);

        $allowance->setAmount(30.00);

        $this->assertEquals(30.00, $allowance->getAmount());
    }

    /**
     * @test
     * Vérifie qu'on ne peut pas modifier le montant avec une valeur négative
     */
    public function it_throws_exception_for_negative_amount_update(): void
    {
        $account = new Account('Lucas');
        $allowance = new WeeklyAllowance($account, 20.00);

        $this->expectException(InvalidAmountException::class);

        $allowance->setAmount(-10.00);
    }

    /**
     * @test
     * Vérifie qu'on ne peut pas modifier le montant avec zéro
     */
    public function it_throws_exception_for_zero_amount_update(): void
    {
        $account = new Account('Lucas');
        $allowance = new WeeklyAllowance($account, 20.00);

        $this->expectException(InvalidAmountException::class);

        $allowance->setAmount(0);
    }

    /**
     * @test
     * Vérifie que le jour de versement par défaut est lundi (1)
     */
    public function default_payment_day_is_monday(): void
    {
        $account = new Account('Lucas');
        $allowance = new WeeklyAllowance($account, 20.00);

        $this->assertEquals(1, $allowance->getPaymentDay());
    }

    /**
     * @test
     * Vérifie qu'on peut définir le jour de versement
     */
    public function it_can_set_payment_day(): void
    {
        $account = new Account('Lucas');
        $allowance = new WeeklyAllowance($account, 20.00);

        $allowance->setPaymentDay(5); // Vendredi

        $this->assertEquals(5, $allowance->getPaymentDay());
    }

    /**
     * @test
     * Vérifie qu'on ne peut pas définir un jour invalide (< 1)
     */
    public function it_throws_exception_for_payment_day_less_than_one(): void
    {
        $account = new Account('Lucas');
        $allowance = new WeeklyAllowance($account, 20.00);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Le jour de versement doit être entre 1 (lundi) et 7 (dimanche)');

        $allowance->setPaymentDay(0);
    }

    /**
     * @test
     * Vérifie qu'on ne peut pas définir un jour invalide (> 7)
     */
    public function it_throws_exception_for_payment_day_greater_than_seven(): void
    {
        $account = new Account('Lucas');
        $allowance = new WeeklyAllowance($account, 20.00);

        $this->expectException(\InvalidArgumentException::class);

        $allowance->setPaymentDay(8);
    }

    /**
     * @test
     * Vérifie qu'une nouvelle allocation est due immédiatement
     */
    public function a_new_allowance_is_due(): void
    {
        $account = new Account('Lucas');
        $allowance = new WeeklyAllowance($account, 20.00);
        $allowance->activate();

        $this->assertTrue($allowance->isDue());
    }

    /**
     * @test
     * Vérifie qu'une allocation n'est pas due juste après traitement
     */
    public function allowance_is_not_due_just_after_processing(): void
    {
        $account = new Account('Lucas');
        $allowance = new WeeklyAllowance($account, 20.00);
        $allowance->activate();
        $allowance->process();

        $this->assertFalse($allowance->isDue());
    }

    /**
     * @test
     * Vérifie qu'on peut obtenir la date du dernier versement
     */
    public function it_can_get_last_payment_date(): void
    {
        $account = new Account('Lucas');
        $allowance = new WeeklyAllowance($account, 20.00);
        $allowance->activate();

        $this->assertNull($allowance->getLastPaymentDate());

        $allowance->process();

        $this->assertInstanceOf(\DateTimeInterface::class, $allowance->getLastPaymentDate());
    }

    /**
     * @test
     * Vérifie qu'on peut obtenir la date du prochain versement
     */
    public function it_can_get_next_payment_date(): void
    {
        $account = new Account('Lucas');
        $allowance = new WeeklyAllowance($account, 20.00);
        $allowance->activate();

        $nextPayment = $allowance->getNextPaymentDate();

        $this->assertInstanceOf(\DateTimeInterface::class, $nextPayment);
    }
}
