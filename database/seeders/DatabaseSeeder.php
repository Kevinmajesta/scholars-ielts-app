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
        // 1. Seed Users
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

        // 2. Data Essay, Pertanyaan, dan Pilihan
        $essaysData = [
            [
                'title' => 'The History of Tea',
                'content' => 'Tea is the most popular beverage in the world after water. Its history spans thousands of years, starting from ancient China. Legend says Emperor Shen Nung discovered it when leaves from a wild tree drifted into his pot of boiling water.',
                'questions' => [
                    [
                        'text' => 'What is the most popular beverage in the world after water?',
                        'options' => [
                            ['text' => 'Coffee', 'correct' => false],
                            ['text' => 'Tea', 'correct' => true],
                            ['text' => 'Juice', 'correct' => false],
                            ['text' => 'Soda', 'correct' => false],
                        ]
                    ],
                    [
                        'text' => 'Who is credited with discovering tea according to the legend?',
                        'options' => [
                            ['text' => 'Emperor Shen Nung', 'correct' => true],
                            ['text' => 'Ancient Farmers', 'correct' => false],
                            ['text' => 'Marco Polo', 'correct' => false],
                        ]
                    ]
                ]
            ],
            [
                'title' => 'Climate Change Impacts',
                'content' => 'Climate change refers to long-term shifts in temperatures and weather patterns. These shifts may be natural, but since the 1800s, human activities have been the main driver of climate change, primarily due to burning fossil fuels like coal, oil and gas.',
                'questions' => [
                    [
                        'text' => 'What has been the main driver of climate change since the 1800s?',
                        'options' => [
                            ['text' => 'Volcanic eruptions', 'correct' => false],
                            ['text' => 'Human activities', 'correct' => true],
                            ['text' => 'Solar cycles', 'correct' => false],
                        ]
                    ],
                    [
                        'text' => 'Which fossil fuel is mentioned in the text?',
                        'options' => [
                            ['text' => 'Coal', 'correct' => true],
                            ['text' => 'Wood', 'correct' => false],
                            ['text' => 'Nuclear', 'correct' => false],
                        ]
                    ]
                ]
            ],
            [
                'title' => 'The Rise of Artificial Intelligence',
                'content' => 'Artificial Intelligence (AI) is the simulation of human intelligence processes by machines, especially computer systems. These processes include learning, reasoning, and self-correction. AI is now used in healthcare, finance, and transportation.',
                'questions' => [
                    [
                        'text' => 'What does AI simulate?',
                        'options' => [
                            ['text' => 'Human emotions', 'correct' => false],
                            ['text' => 'Human intelligence processes', 'correct' => true],
                            ['text' => 'Animal behavior', 'correct' => false],
                        ]
                    ],
                    [
                        'text' => 'In which field is AI NOT explicitly mentioned as being used?',
                        'options' => [
                            ['text' => 'Healthcare', 'correct' => false],
                            ['text' => 'Cooking', 'correct' => true],
                            ['text' => 'Transportation', 'correct' => false],
                        ]
                    ]
                ]
            ]
        ];

        // 3. Proses Loop untuk Insert ke Database
        foreach ($essaysData as $data) {
            $essay = Essay::create([
                'title' => $data['title'],
                'content' => $data['content']
            ]);

            foreach ($data['questions'] as $qData) {
                $question = Question::create([
                    'essay_id' => $essay->id,
                    'question_text' => $qData['text']
                ]);

                foreach ($qData['options'] as $oData) {
                    Option::create([
                        'question_id' => $question->id,
                        'option_text' => $oData['text'],
                        'is_correct' => $oData['correct']
                    ]);
                }
            }
        }
    }
}