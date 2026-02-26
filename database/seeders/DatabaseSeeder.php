<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Essay;
use App\Models\Question;
use App\Models\Option;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Admin IELTS',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin', 
        ]);

        User::create([
            'name' => 'Kevin Student',
            'email' => 'student@example.com',
            'password' => Hash::make('password'),
            'role' => 'user',
        ]);

        $essay = Essay::create([
            'title' => 'The History of Tea',
            'content' => 'Tea is the most popular beverage in the world after water. Its history spans thousands of years, starting from ancient China. Legend says Emperor Shen Nung discovered it when leaves from a wild tree drifted into his pot of boiling water.'
        ]);

        $q1 = Question::create([
            'essay_id' => $essay->id,
            'question_text' => 'What is the most popular beverage in the world after water?'
        ]);

        Option::create(['question_id' => $q1->id, 'option_text' => 'Coffee', 'is_correct' => false]);
        Option::create(['question_id' => $q1->id, 'option_text' => 'Tea', 'is_correct' => true]);
        Option::create(['question_id' => $q1->id, 'option_text' => 'Juice', 'is_correct' => false]);
        Option::create(['question_id' => $q1->id, 'option_text' => 'Soda', 'is_correct' => false]);

        $q2 = Question::create([
            'essay_id' => $essay->id,
            'question_text' => 'Who is credited with discovering tea according to the legend?'
        ]);

        Option::create(['question_id' => $q2->id, 'option_text' => 'Emperor Shen Nung', 'is_correct' => true]);
        Option::create(['question_id' => $q2->id, 'option_text' => 'Ancient Farmers', 'is_correct' => false]);
        Option::create(['question_id' => $q2->id, 'option_text' => 'Marco Polo', 'is_correct' => false]);
    }
}