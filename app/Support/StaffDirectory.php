<?php

namespace App\Support;

class StaffDirectory
{
    private const COORDINATORS = [
        ['employee_id' => 'COORD-1001', 'first_name' => 'Maria Lourdes', 'middle_name' => 'R', 'last_name' => 'Santos', 'contact_number' => '09171234567', 'department' => 'Computer Science'],
        ['employee_id' => 'COORD-1002', 'first_name' => 'Allan', 'middle_name' => 'P', 'last_name' => 'Cruz', 'contact_number' => '09281234567', 'department' => 'Computer Science'],
        ['employee_id' => 'COORD-1003', 'first_name' => 'Grace', 'middle_name' => 'D', 'last_name' => 'Villanueva', 'contact_number' => '09391234567', 'department' => 'Computer Science'],
        ['employee_id' => 'COORD-1004', 'first_name' => 'Jerome', 'middle_name' => 'L', 'last_name' => 'Navarro', 'contact_number' => '09451234567', 'department' => 'Computer Science'],
    ];

    private const SUPERVISORS = [
        ['supervisor_id' => 'SUP-3001', 'first_name' => 'Patricia', 'middle_name' => 'S', 'last_name' => 'Uy', 'contact_number' => '09173456789', 'company_name' => 'TechNova Solutions', 'company_address' => 'Cebu IT Park, Lahug, Cebu City'],
        ['supervisor_id' => 'SUP-3002', 'first_name' => 'Michael', 'middle_name' => 'R', 'last_name' => 'Tan', 'contact_number' => '09283456789', 'company_name' => 'BrightWorks Systems', 'company_address' => 'North Reclamation Area, Mandaue City'],
        ['supervisor_id' => 'SUP-3003', 'first_name' => 'Angela', 'middle_name' => 'C', 'last_name' => 'Lim', 'contact_number' => '09393456789', 'company_name' => 'HarborTech Services', 'company_address' => 'Pusok, Lapu-Lapu City'],
        ['supervisor_id' => 'SUP-3004', 'first_name' => 'Noel', 'middle_name' => 'V', 'last_name' => 'Garcia', 'contact_number' => '09473456789', 'company_name' => 'CoreBridge Analytics', 'company_address' => 'Ayala Avenue, Makati City'],
    ];

    public static function findCoordinator(string $employeeId): ?array
    {
        return self::findById(self::COORDINATORS, 'employee_id', $employeeId, 'coordinator');
    }

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
        $id = strtolower((string) preg_replace('/[^a-z0-9]+/i', '', $record['employee_id'] ?? $record['supervisor_id']));

        return $firstName.'.'.$lastName.'.'.$role.'.'.$id.'@dutytrack.test';
    }
}
