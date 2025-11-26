<?php

declare(strict_types=1);

namespace MyWeeklyAllowance\Tests;

use PHPUnit\Framework\TestCase;
use MyWeeklyAllowance\Account;
use MyWeeklyAllowance\WeeklyAllowance;
use MyWeeklyAllowance\AllowanceManager;
use MyWeeklyAllowance\Exception\DuplicateAccountException;
use MyWeeklyAllowance\Exception\AccountNotFoundException;

/**
 * Tests unitaires pour la classe AllowanceManager
 * Gère plusieurs comptes et leurs allocations
 */
class AllowanceManagerTest extends TestCase
{
    /**
     * @test
     * Vérifie qu'on peut créer un gestionnaire d'allocations
     */
    public function it_can_create_allowance_manager(): void
    {
        $manager = new AllowanceManager();

        $this->assertInstanceOf(AllowanceManager::class, $manager);
    }

    /**
     * @test
     * Vérifie qu'un nouveau gestionnaire n'a pas de comptes
     */
    public function a_new_manager_has_no_accounts(): void
    {
        $manager = new AllowanceManager();

        $this->assertEmpty($manager->getAccounts());
        $this->assertEquals(0, $manager->getAccountCount());
    }

    /**
     * @test
     * Vérifie qu'on peut ajouter un compte au gestionnaire
     */
    public function it_can_add_an_account(): void
    {
        $manager = new AllowanceManager();
        $account = new Account('Lucas');

        $manager->addAccount($account);

        $this->assertEquals(1, $manager->getAccountCount());
    }

    /**
     * @test
     * Vérifie qu'on peut ajouter plusieurs comptes
     */
    public function it_can_add_multiple_accounts(): void
    {
        $manager = new AllowanceManager();

        $manager->addAccount(new Account('Lucas'));
        $manager->addAccount(new Account('Emma'));
        $manager->addAccount(new Account('Noah'));

        $this->assertEquals(3, $manager->getAccountCount());
    }

    /**
     * @test
     * Vérifie qu'on peut récupérer un compte par son nom
     */
    public function it_can_get_account_by_name(): void
    {
        $manager = new AllowanceManager();
        $lucas = new Account('Lucas');
        $manager->addAccount($lucas);

        $retrieved = $manager->getAccount('Lucas');

        $this->assertSame($lucas, $retrieved);
    }

    /**
     * @test
     * Vérifie qu'une exception est levée si le compte n'existe pas
     */
    public function it_throws_exception_for_unknown_account(): void
    {
        $manager = new AllowanceManager();

        $this->expectException(AccountNotFoundException::class);
        $this->expectExceptionMessage("Compte 'Lucas' non trouvé");

        $manager->getAccount('Lucas');
    }

    /**
     * @test
     * Vérifie qu'on ne peut pas ajouter deux comptes avec le même nom
     */
    public function it_throws_exception_for_duplicate_account_name(): void
    {
        $manager = new AllowanceManager();
        $manager->addAccount(new Account('Lucas'));

        $this->expectException(DuplicateAccountException::class);
        $this->expectExceptionMessage("Un compte avec le nom 'Lucas' existe déjà");

        $manager->addAccount(new Account('Lucas'));
    }

    /**
     * @test
     * Vérifie qu'on peut supprimer un compte
     */
    public function it_can_remove_an_account(): void
    {
        $manager = new AllowanceManager();
        $manager->addAccount(new Account('Lucas'));

        $manager->removeAccount('Lucas');

        $this->assertEquals(0, $manager->getAccountCount());
    }

    /**
     * @test
     * Vérifie que supprimer un compte inexistant lève une exception
     */
    public function it_throws_exception_when_removing_unknown_account(): void
    {
        $manager = new AllowanceManager();

        $this->expectException(AccountNotFoundException::class);

        $manager->removeAccount('Lucas');
    }

    /**
     * @test
     * Vérifie qu'on peut vérifier si un compte existe
     */
    public function it_can_check_if_account_exists(): void
    {
        $manager = new AllowanceManager();
        $manager->addAccount(new Account('Lucas'));

        $this->assertTrue($manager->hasAccount('Lucas'));
        $this->assertFalse($manager->hasAccount('Emma'));
    }

    /**
     * @test
     * Vérifie qu'on peut configurer une allocation pour un compte
     */
    public function it_can_set_allowance_for_account(): void
    {
        $manager = new AllowanceManager();
        $manager->addAccount(new Account('Lucas'));

        $allowance = $manager->setAllowance('Lucas', 25.00);

        $this->assertInstanceOf(WeeklyAllowance::class, $allowance);
        $this->assertEquals(25.00, $allowance->getAmount());
    }

    /**
     * @test
     * Vérifie qu'on peut récupérer l'allocation d'un compte
     */
    public function it_can_get_allowance_for_account(): void
    {
        $manager = new AllowanceManager();
        $manager->addAccount(new Account('Lucas'));
        $manager->setAllowance('Lucas', 25.00);

        $allowance = $manager->getAllowance('Lucas');

        $this->assertEquals(25.00, $allowance->getAmount());
    }

    /**
     * @test
     * Vérifie que getAllowance retourne null si pas d'allocation configurée
     */
    public function get_allowance_returns_null_when_not_set(): void
    {
        $manager = new AllowanceManager();
        $manager->addAccount(new Account('Lucas'));

        $this->assertNull($manager->getAllowance('Lucas'));
    }

    /**
     * @test
     * Vérifie que configurer une allocation l'active automatiquement
     */
    public function setting_allowance_activates_it(): void
    {
        $manager = new AllowanceManager();
        $manager->addAccount(new Account('Lucas'));

        $allowance = $manager->setAllowance('Lucas', 25.00);

        $this->assertTrue($allowance->isActive());
    }

    /**
     * @test
     * Vérifie qu'on peut désactiver l'allocation d'un compte
     */
    public function it_can_deactivate_allowance(): void
    {
        $manager = new AllowanceManager();
        $manager->addAccount(new Account('Lucas'));
        $manager->setAllowance('Lucas', 25.00);

        $manager->deactivateAllowance('Lucas');

        $this->assertFalse($manager->getAllowance('Lucas')->isActive());
    }

    /**
     * @test
     * Vérifie qu'on peut réactiver l'allocation d'un compte
     */
    public function it_can_reactivate_allowance(): void
    {
        $manager = new AllowanceManager();
        $manager->addAccount(new Account('Lucas'));
        $manager->setAllowance('Lucas', 25.00);
        $manager->deactivateAllowance('Lucas');

        $manager->activateAllowance('Lucas');

        $this->assertTrue($manager->getAllowance('Lucas')->isActive());
    }

    /**
     * @test
     * Vérifie qu'on peut traiter toutes les allocations dues
     */
    public function it_can_process_all_due_allowances(): void
    {
        $manager = new AllowanceManager();

        $manager->addAccount(new Account('Lucas'));
        $manager->addAccount(new Account('Emma'));

        $manager->setAllowance('Lucas', 20.00);
        $manager->setAllowance('Emma', 30.00);

        $processed = $manager->processAllDueAllowances();

        $this->assertEquals(2, $processed);
        $this->assertEquals(20.00, $manager->getAccount('Lucas')->getBalance());
        $this->assertEquals(30.00, $manager->getAccount('Emma')->getBalance());
    }

    /**
     * @test
     * Vérifie que les allocations inactives ne sont pas traitées
     */
    public function inactive_allowances_are_not_processed(): void
    {
        $manager = new AllowanceManager();

        $manager->addAccount(new Account('Lucas'));
        $manager->addAccount(new Account('Emma'));

        $manager->setAllowance('Lucas', 20.00);
        $manager->setAllowance('Emma', 30.00);
        $manager->deactivateAllowance('Emma');

        $processed = $manager->processAllDueAllowances();

        $this->assertEquals(1, $processed);
        $this->assertEquals(20.00, $manager->getAccount('Lucas')->getBalance());
        $this->assertEquals(0.00, $manager->getAccount('Emma')->getBalance());
    }

    /**
     * @test
     * Vérifie qu'on peut traiter l'allocation d'un compte spécifique
     */
    public function it_can_process_single_account_allowance(): void
    {
        $manager = new AllowanceManager();
        $manager->addAccount(new Account('Lucas'));
        $manager->setAllowance('Lucas', 20.00);

        $result = $manager->processAllowance('Lucas');

        $this->assertTrue($result);
        $this->assertEquals(20.00, $manager->getAccount('Lucas')->getBalance());
    }

    /**
     * @test
     * Vérifie qu'on peut obtenir le solde total de tous les comptes
     */
    public function it_can_get_total_balance(): void
    {
        $manager = new AllowanceManager();

        $manager->addAccount(new Account('Lucas', 50.00));
        $manager->addAccount(new Account('Emma', 30.00));

        $this->assertEquals(80.00, $manager->getTotalBalance());
    }

    /**
     * @test
     * Vérifie qu'on peut obtenir le montant total des allocations actives
     */
    public function it_can_get_total_weekly_allowances(): void
    {
        $manager = new AllowanceManager();

        $manager->addAccount(new Account('Lucas'));
        $manager->addAccount(new Account('Emma'));
        $manager->addAccount(new Account('Noah'));

        $manager->setAllowance('Lucas', 20.00);
        $manager->setAllowance('Emma', 30.00);
        // Noah n'a pas d'allocation

        $this->assertEquals(50.00, $manager->getTotalWeeklyAllowances());
    }

    /**
     * @test
     * Vérifie que seules les allocations actives sont comptées
     */
    public function total_weekly_allowances_only_counts_active(): void
    {
        $manager = new AllowanceManager();

        $manager->addAccount(new Account('Lucas'));
        $manager->addAccount(new Account('Emma'));

        $manager->setAllowance('Lucas', 20.00);
        $manager->setAllowance('Emma', 30.00);
        $manager->deactivateAllowance('Emma');

        $this->assertEquals(20.00, $manager->getTotalWeeklyAllowances());
    }

    /**
     * @test
     * Vérifie qu'on peut obtenir tous les comptes sous forme de tableau
     */
    public function it_can_get_all_accounts_as_array(): void
    {
        $manager = new AllowanceManager();

        $lucas = new Account('Lucas');
        $emma = new Account('Emma');

        $manager->addAccount($lucas);
        $manager->addAccount($emma);

        $accounts = $manager->getAccounts();

        $this->assertCount(2, $accounts);
        $this->assertContains($lucas, $accounts);
        $this->assertContains($emma, $accounts);
    }

    /**
     * @test
     * Vérifie qu'on peut créer un compte directement via le manager
     */
    public function it_can_create_account_directly(): void
    {
        $manager = new AllowanceManager();

        $account = $manager->createAccount('Lucas', 50.00);

        $this->assertInstanceOf(Account::class, $account);
        $this->assertEquals('Lucas', $account->getName());
        $this->assertEquals(50.00, $account->getBalance());
        $this->assertTrue($manager->hasAccount('Lucas'));
    }

    /**
     * @test
     * Vérifie qu'on peut créer un compte avec allocation en une fois
     */
    public function it_can_create_account_with_allowance(): void
    {
        $manager = new AllowanceManager();

        $account = $manager->createAccountWithAllowance('Lucas', 50.00, 20.00);

        $this->assertEquals(50.00, $account->getBalance());
        $this->assertNotNull($manager->getAllowance('Lucas'));
        $this->assertEquals(20.00, $manager->getAllowance('Lucas')->getAmount());
    }
}
