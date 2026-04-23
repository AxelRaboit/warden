<?php

declare(strict_types=1);

namespace App\Enum;

enum VaultRecordTypeEnum: string
{
    case Login = 'login';
    case PaymentCard = 'payment_card';
    case Contact = 'contact';
    case Address = 'address';
    case BankAccount = 'bank_account';
    case DriverLicense = 'driver_license';
    case BirthCertificate = 'birth_certificate';
    case Database = 'database';
    case Server = 'server';
    case HealthInsurance = 'health_insurance';
    case Membership = 'membership';
    case SecureNote = 'secure_note';
    case Passport = 'passport';
    case IdentityCard = 'identity_card';
    case SoftwareLicense = 'software_license';
    case SshKey = 'ssh_key';
}
