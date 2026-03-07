<?php

namespace Database\Seeders;

use App\Models\PosterCategory;
use App\Models\PosterSubmission;
use App\Models\PosterTopic;
use App\Models\User;
use Illuminate\Database\Seeder;

class PosterSubmissionSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::whereHas('seminarRegistrations', fn ($query) => $query->where('wants_poster_competition', true))->get();

        if ($users->isEmpty()) {
            $this->command->warn('No users with poster competition registrations found. Skipping poster submission seeding.');

            return;
        }

        $categories = PosterCategory::where('is_active', true)->get();
        $topics = PosterTopic::where('is_active', true)->get();

        $submissions = [
            [
                'title' => 'Clinical Outcomes of Mineral Trioxide Aggregate in Pulpotomy',
                'abstract_text' => 'This study evaluates the long-term clinical outcomes of Mineral Trioxide Aggregate (MTA) in pulpotomy procedures for permanent teeth with cariously exposed pulps. A retrospective analysis of 150 cases over 5 years showed 92% success rate.',
                'author_names' => 'Dr. Sarah Johnson, Dr. Michael Chen',
                'author_emails' => 'sarah.johnson@dental.com, michael.chen@dental.com',
                'affiliation' => 'University of Dental Sciences',
                'presenter_name' => 'Dr. Sarah Johnson',
            ],
            [
                'title' => 'Implant Survival Rates in Diabetic Patients: A 10-Year Follow-up',
                'abstract_text' => 'This prospective study examines dental implant survival rates in controlled diabetic patients compared to non-diabetic controls. Results indicate no significant difference in implant success rates when glycemic control is maintained.',
                'author_names' => 'Dr. Amanda Wong, Dr. Robert Garcia',
                'author_emails' => 'amanda.wong@implant.com, robert.garcia@implant.com',
                'affiliation' => 'Implant Research Center',
                'presenter_name' => 'Dr. Amanda Wong',
            ],
            [
                'title' => 'Effectiveness of Clear Aligner Therapy in Crowding Correction',
                'abstract_text' => 'A comparative study evaluating the effectiveness of clear aligner therapy versus conventional braces in treating mild to moderate crowding. Treatment outcomes and patient satisfaction were assessed.',
                'author_names' => 'Dr. Emily Davis, Dr. James Wilson',
                'author_emails' => 'emily.davis@ortho.com, james.wilson@ortho.com',
                'affiliation' => 'Orthodontic Specialty Clinic',
                'presenter_name' => 'Dr. Emily Davis',
            ],
            [
                'title' => 'Pediatric Dental Anxiety Management Using Virtual Reality',
                'abstract_text' => 'This randomized controlled trial investigates the effectiveness of virtual reality distraction therapy in reducing dental anxiety among pediatric patients aged 6-12 years during restorative procedures.',
                'author_names' => 'Dr. Lisa Brown, Dr. David Lee',
                'author_emails' => 'lisa.brown@pedo.com, david.lee@pedo.com',
                'affiliation' => 'Children\'s Dental Hospital',
                'presenter_name' => 'Dr. Lisa Brown',
            ],
            [
                'title' => 'Regenerative Periodontal Therapy: Clinical Outcomes',
                'abstract_text' => 'A clinical evaluation of regenerative periodontal therapy using enamel matrix derivative in the treatment of intrabony defects. Clinical attachment gain and probing depth reduction were measured over 12 months.',
                'author_names' => 'Dr. Maria Rodriguez, Dr. Thomas Anderson',
                'author_emails' => 'maria.rodriguez@perio.com, thomas.anderson@perio.com',
                'affiliation' => 'Periodontal Institute',
                'presenter_name' => 'Dr. Maria Rodriguez',
            ],
        ];

        foreach ($submissions as $submissionData) {
            $user = $users->random();
            $registration = $user->seminarRegistrations()
                ->where('wants_poster_competition', true)
                ->where('payment_status', 'verified')
                ->first();

            if (! $registration) {
                continue;
            }

            PosterSubmission::create([
                ...$submissionData,
                'user_id' => $user->id,
                'seminar_registration_id' => $registration->id,
                'poster_category_id' => $categories->random()->id,
                'poster_topic_id' => $topics->random()->id,
                'status' => PosterSubmission::STATUS_SUBMITTED,
                'submitted_at' => now()->subDays(random_int(1, 5)),
            ]);
        }
    }
}
