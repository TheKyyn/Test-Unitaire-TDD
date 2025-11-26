<?php

declare(strict_types=1);

namespace MyWeeklyAllowance;

enum TransactionType: string
{
    case DEPOSIT = 'deposit';
    case WITHDRAWAL = 'withdrawal';
}
