<?php

namespace App\DataFixtures;

class PlayerAction
{
    static string $Register = 'register';
    static string $Update = 'update';
    static string $Delete = 'delete';
    static string $CheckIn = 'checkin';
    static string $MakePurchase = 'purchase';

    static string $ApproveCheckin = 'approve_checkin';
    static string $DenyCheckin = 'deny_checkin';

    static string $ApprovePurchase = 'approve_purchase';
    static string $DenyPurchase = 'deny_purchase';
}