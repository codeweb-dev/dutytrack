<?php

namespace Database\Seeders;

use App\Models\Announcement;
use Illuminate\Database\Seeder;

class AnnouncementSeeder extends Seeder
{
    public function run(): void
    {
        $announcements = collect(range(1, 40))->map(function (int $index) {
            $type = match (true) {
                $index % 10 === 0 => 'urgent',
                $index % 6 === 0 => 'maintenance',
                $index % 4 === 0 => 'warning',
                default => 'info',
            };

            return [
                'title' => $this->titleFor($index, $type),
                'message' => $this->messageFor($index, $type),
                'type' => $type,
            ];
        });

        foreach ($announcements as $announcement) {
            Announcement::query()->updateOrCreate(
                ['title' => $announcement['title']],
                $announcement,
            );
        }
    }

    protected function titleFor(int $index, string $type): string
    {
        return match ($type) {
            'urgent' => "Urgent Update {$index}: Immediate OJT Action Required",
            'maintenance' => "Maintenance Notice {$index}: Platform Service Window",
            'warning' => "Reminder {$index}: Submission and Attendance Review",
            default => "Campus Update {$index}: OJT Coordination Notice",
        };
    }

    protected function messageFor(int $index, string $type): string
    {
        return match ($type) {
            'urgent' => 'Please review pending attendance, duty hour approvals, and assigned evaluations as soon as possible. This seeded urgent notice is provided for dashboard and announcement page previews.',
            'maintenance' => 'DutyTrack services may be temporarily limited during the scheduled maintenance window. Use this seeded maintenance notice to preview system-wide updates for all users.',
            'warning' => 'Students, professors, coordinators, and company supervisors are advised to check incomplete logs and outstanding reports. This seeded reminder helps populate announcement timelines.',
            default => 'This is a seeded informational announcement for the shared announcement page. It helps demonstrate timeline layouts, filters, badges, and callout styling across the application.',
        };
    }
}
