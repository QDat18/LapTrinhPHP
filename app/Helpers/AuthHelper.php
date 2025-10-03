<?php
namespace App\Helpers;

class AuthHelper
{
    /**
     * Check if password meets requirements
     */
    public static function isStrongPassword($password)
    {
        // At least 8 characters, 1 uppercase, 1 lowercase, 1 number
        return strlen($password) >= 8 
            && preg_match('/[A-Z]/', $password)
            && preg_match('/[a-z]/', $password)
            && preg_match('/[0-9]/', $password);
    }

    /**
     * Generate random username
     */
    public static function generateUsername($firstName, $lastName)
    {
        $base = strtolower($firstName . $lastName);
        $base = preg_replace('/[^a-z0-9]/', '', $base);
        
        $username = $base;
        $counter = 1;
        
        while (\App\Models\User::where('username', $username)->exists()) {
            $username = $base . $counter;
            $counter++;
        }
        
        return $username;
    }

    /**
     * Sanitize phone number
     */
    public static function sanitizePhone($phone)
    {
        // Remove all non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // Format Vietnamese phone number
        if (strlen($phone) == 10 && substr($phone, 0, 1) == '0') {
            return $phone;
        }
        
        return $phone;
    }

    /**
     * Format full name
     */
    public static function formatFullName($firstName, $lastName)
    {
        return trim(ucwords(strtolower($firstName)) . ' ' . ucwords(strtolower($lastName)));
    }
}