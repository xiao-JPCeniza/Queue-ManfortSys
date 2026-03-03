<?php

namespace App\Services;

use Illuminate\Support\Str;

class OfficeAdminCredentialService
{
    /**
     * Generate a secure password meeting common policy requirements:
     * minimum 12 characters, at least one uppercase, one lowercase, one digit, one symbol.
     */
    public static function generateSecurePassword(int $length = 16): string
    {
        $uppers = 'ABCDEFGHJKLMNPQRSTUVWXYZ'; // exclude I,O
        $lowers = 'abcdefghjkmnpqrstuvwxyz';   // exclude i,l,o
        $digits = '23456789';                   // exclude 0,1
        $symbols = '!@#$%&*+-=?';

        $password = '';
        $password .= $uppers[random_int(0, strlen($uppers) - 1)];
        $password .= $lowers[random_int(0, strlen($lowers) - 1)];
        $password .= $digits[random_int(0, strlen($digits) - 1)];
        $password .= $symbols[random_int(0, strlen($symbols) - 1)];

        $all = $uppers . $lowers . $digits . $symbols;
        for ($i = strlen($password); $i < $length; $i++) {
            $password .= $all[random_int(0, strlen($all) - 1)];
        }

        return str_shuffle($password);
    }

    /**
     * Generate a unique email for an office admin (login username).
     */
    public static function emailForOfficeSlug(string $slug): string
    {
        $safe = preg_replace('/[^a-z0-9-]/', '', strtolower($slug));
        $domain = config('app.office_email_domain', 'manolofortich.gov.ph');
        return $safe . '@' . $domain;
    }
}
