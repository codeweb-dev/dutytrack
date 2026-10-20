<?php

namespace App\Enums;

enum Role: string
{
    case ADMIN = 'admin';
    case OJTCOORDINATOR = 'ojt_coordinator';
    case SUPERVISOR = 'supervisor';
    case STUDENT = 'student';
}
