<?php

namespace Database\Seeders;

use App\Models\PosterCategory;
use App\Models\PosterTopic;
use Illuminate\Database\Seeder;

class PosterCategoryAndTopicSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Case Report', 'slug' => 'case-report'],
            ['name' => 'Research', 'slug' => 'research'],
        ];

        foreach ($categories as $category) {
            PosterCategory::create($category);
        }

        $topics = [
            ['name' => 'Endodontics', 'slug' => 'endodontics'],
            ['name' => 'General Dentistry', 'slug' => 'general-dentistry'],
            ['name' => 'Implantology', 'slug' => 'implantology'],
            ['name' => 'Oral Surgery', 'slug' => 'oral-surgery'],
            ['name' => 'Orthodontics', 'slug' => 'orthodontics'],
            ['name' => 'Pedodontics', 'slug' => 'pedodontics'],
            ['name' => 'Periodontics', 'slug' => 'periodontics'],
            ['name' => 'Prosthodontics', 'slug' => 'prosthodontics'],
            ['name' => 'Public Health', 'slug' => 'public-health'],
            ['name' => 'Others', 'slug' => 'others'],
        ];

        foreach ($topics as $topic) {
            PosterTopic::create($topic);
        }
    }
}
