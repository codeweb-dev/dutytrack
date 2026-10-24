<?php

namespace App\Support;

class StaffDirectory
{
    private const SUPERVISORS = [
        ['supervisor_id' => 'SUP-3001', 'first_name' => 'Patricia', 'middle_name' => 'S', 'last_name' => 'Uy', 'contact_number' => '09173456789', 'company_name' => 'TechNova Solutions', 'company_address' => 'Cebu IT Park, Lahug, Cebu City'],
        ['supervisor_id' => 'SUP-3002', 'first_name' => 'Michael', 'middle_name' => 'R', 'last_name' => 'Tan', 'contact_number' => '09283456789', 'company_name' => 'BrightWorks Systems', 'company_address' => 'North Reclamation Area, Mandaue City'],
        ['supervisor_id' => 'SUP-3003', 'first_name' => 'Angela', 'middle_name' => 'C', 'last_name' => 'Lim', 'contact_number' => '09393456789', 'company_name' => 'HarborTech Services', 'company_address' => 'Pusok, Lapu-Lapu City'],
        ['supervisor_id' => 'SUP-3004', 'first_name' => 'Noel', 'middle_name' => 'V', 'last_name' => 'Garcia', 'contact_number' => '09473456789', 'company_name' => 'CoreBridge Analytics', 'company_address' => 'Ayala Avenue, Makati City'],
    ];

    public static function findSupervisor(string $supervisorId): ?array
    {
        return self::findById(self::SUPERVISORS, 'supervisor_id', $supervisorId, 'supervisor');
    }

    private static function findById(array $records, string $idKey, string $id, string $role): ?array
    {
        $id = strtoupper(trim($id));

        foreach ($records as $record) {
            if ($record[$idKey] === $id) {
                return self::withAccount($record, $role);
            }
        }

        return null;
    }

    private static function withAccount(array $record, string $role): array
    {
        $record['email'] = self::emailFor($record, $role);
        $record['password'] = $record['last_name'];

        return $record;
    }

    private static function emailFor(array $record, string $role): string
    {
        $firstName = strtolower((string) preg_replace('/[^a-z0-9]+/i', '', $record['first_name']));
        $lastName = strtolower((string) preg_replace('/[^a-z0-9]+/i', '', $record['last_name']));
        $id = strtolower((string) preg_replace('/[^a-z0-9]+/i', '', $record['supervisor_id']));

        return $firstName.'.'.$lastName.'.'.$role.'.'.$id.'@dutytrack.test';
    }
}
