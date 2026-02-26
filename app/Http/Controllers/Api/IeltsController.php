<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Essay;
use App\Models\Option;
use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use OpenApi\Attributes as OA;

class IeltsController extends Controller
{
    #[OA\Get(
        path: '/api/ielts/questions',
        operationId: 'getIeltsList',
        tags: ['IELTS'],
        summary: 'Ambil daftar semua essay dan soal',
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Berhasil ambil data'),
        ]
    )]
    public function index()
    {
        $essays = Essay::with('questions.options')->get();
        return response()->json(['success' => true, 'data' => $essays]);
    }

    #[OA\Get(
        path: '/api/ielts/questions/{id}',
        operationId: 'getIeltsDetail',
        tags: ['IELTS'],
        summary: 'Ambil detail soal berdasarkan ID',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: 'ID Essay', schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Berhasil ambil data'),
            new OA\Response(response: 404, description: 'Essay tidak ditemukan'),
        ]
    )]
    public function show($id)
    {
        $essay = Essay::with('questions.options')->find($id);
        if (!$essay)
            return response()->json(['message' => 'Essay not found'], 404);
        return response()->json(['success' => true, 'data' => $essay]);
    }

    #[OA\Post(
        path: '/api/ielts/submit',
        operationId: 'submitAnswers',
        tags: ['IELTS'],
        summary: 'Submit Jawaban & Hitung Skor',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    
                    new OA\Property(property: 'essay_id', type: 'integer', example: 1),
                    new OA\Property(
                        property: 'answers',
                        type: 'array',
                        items: new OA\Items(
                            properties: [
                                new OA\Property(property: 'question_id', type: 'integer', example: 1),
                                new OA\Property(property: 'option_id', type: 'integer', example: 2),
                            ],
                            type: 'object'
                        )
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Skor dihitung dan disimpan'),
            new OA\Response(response: 400, description: 'Data tidak lengkap atau ID salah'),
        ]
    )]
    public function submit(Request $request)
    {
        $userAnswers = $request->input('answers', []);
        if (empty($userAnswers))
            return response()->json(['message' => 'Jawaban kosong'], 400);

        $totalQuestions = count($userAnswers);
        $optionIds = collect($userAnswers)->pluck('option_id');
        $options = Option::whereIn('id', $optionIds)->get()->keyBy('id');

        $correctCount = 0;
        $details = [];

        foreach ($userAnswers as $answer) {
            $option = $options->get($answer['option_id']);
            $isCorrect = $option && $option->is_correct && $option->question_id == $answer['question_id'];
            if ($isCorrect)
                $correctCount++;
            $details[] = ['question_id' => $answer['question_id'], 'is_correct' => $isCorrect];
        }

        $score = ($correctCount / $totalQuestions) * 100;

        $result = \App\Models\ExamResult::create([
            'user_id' => auth('api')->id(),
            'essay_id' => $request->essay_id,
            'total_questions' => $totalQuestions,
            'correct_answers' => $correctCount,
            'score' => round($score, 2),
            'details' => $details
        ]);

        return response()->json(['success' => true, 'results' => $result]);
    }

    #[OA\Post(
        path: '/api/admin/ielts/essays',
        operationId: 'createEssay',
        tags: ['IELTS Admin'],
        summary: 'Tambah Soal Baru (Admin)',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['title', 'content', 'questions'],
                properties: [
                    new OA\Property(property: 'title', type: 'string', example: 'IELTS Reading Test 1'),
                    new OA\Property(property: 'content', type: 'string', example: 'Read the following passage and answer the questions...'),
                    new OA\Property(
                        property: 'questions',
                        type: 'array',
                        items: new OA\Items(
                            required: ['question_text', 'options'],
                            properties: [
                                new OA\Property(property: 'question_text', type: 'string', example: 'What is the main idea of the passage?'),
                                new OA\Property(
                                    property: 'options',
                                    type: 'array',
                                    items: new OA\Items(
                                        required: ['option_text', 'is_correct'],
                                        properties: [
                                            new OA\Property(property: 'option_text', type: 'string', example: 'Climate change impacts'),
                                            new OA\Property(property: 'is_correct', type: 'boolean', example: true),
                                        ],
                                        type: 'object'
                                    )
                                ),
                            ],
                            type: 'object'
                        ),
                        example: [
                            [
                                'question_text' => 'What is the main idea of the passage?',
                                'options' => [
                                    ['option_text' => 'Climate change impacts', 'is_correct' => true],
                                    ['option_text' => 'Global warming myths', 'is_correct' => false],
                                    ['option_text' => 'Environmental policies', 'is_correct' => false],
                                    ['option_text' => 'Renewable energy sources', 'is_correct' => false],
                                ],
                            ],
                            [
                                'question_text' => 'According to the text, what year was mentioned?',
                                'options' => [
                                    ['option_text' => '2020', 'is_correct' => false],
                                    ['option_text' => '2021', 'is_correct' => true],
                                    ['option_text' => '2022', 'is_correct' => false],
                                    ['option_text' => '2023', 'is_correct' => false],
                                ],
                            ],
                        ]
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Soal Dibuat',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'data', type: 'object'),
                    ]
                )
            ),
            new OA\Response(
                response: 422,
                description: 'Validation Error',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'The questions field is required.'),
                        new OA\Property(property: 'errors', type: 'object'),
                    ]
                )
            ),
        ]
    )]
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string',
            'content' => 'required|string',
            'questions' => 'required|array',
            'questions.*.question_text' => 'required|string',
            'questions.*.options' => 'required|array|min:2',
            'questions.*.options.*.option_text' => 'required|string',
            'questions.*.options.*.is_correct' => 'required|boolean',
        ]);

        $essay = DB::transaction(function () use ($validated) {
            $essay = Essay::create(['title' => $validated['title'], 'content' => $validated['content']]);
            foreach ($validated['questions'] as $qData) {
                $question = $essay->questions()->create(['question_text' => $qData['question_text']]);
                foreach ($qData['options'] as $oData) {
                    $question->options()->create($oData);
                }
            }
            return $essay;
        });

        return response()->json(['success' => true, 'data' => $essay->load('questions.options')], 201);
    }

    #[OA\Put(
        path: '/api/admin/ielts/essays/{id}',
        operationId: 'updateEssay',
        tags: ['IELTS Admin'],
        summary: 'Update Essay dan Soal (Admin)',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: 'ID Essay', schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['title', 'content', 'questions'],
                properties: [
                    new OA\Property(property: 'title', type: 'string', example: 'IELTS Reading Test 1 - Updated'),
                    new OA\Property(property: 'content', type: 'string', example: 'Updated passage text about environmental issues...'),
                    new OA\Property(
                        property: 'questions',
                        type: 'array',
                        example: [
                            [
                                'question_text' => 'What is the updated main idea of the passage?',
                                'options' => [
                                    ['option_text' => 'Climate change impacts', 'is_correct' => true],
                                    ['option_text' => 'Economic benefits', 'is_correct' => false],
                                    ['option_text' => 'Political changes', 'is_correct' => false],
                                    ['option_text' => 'Social movements', 'is_correct' => false],
                                ]
                            ],
                            [
                                'question_text' => 'According to the updated passage, what is the author\'s perspective?',
                                'options' => [
                                    ['option_text' => 'Supportive of change', 'is_correct' => false],
                                    ['option_text' => 'Critical of current policies', 'is_correct' => true],
                                    ['option_text' => 'Neutral observer', 'is_correct' => false],
                                    ['option_text' => 'Pessimistic view', 'is_correct' => false],
                                ]
                            ]
                        ],
                        items: new OA\Items(
                            required: ['question_text', 'options'],
                            properties: [
                                new OA\Property(property: 'question_text', type: 'string', example: 'What is the main idea?'),
                                new OA\Property(
                                    property: 'options',
                                    type: 'array',
                                    example: [
                                        ['option_text' => 'Option A', 'is_correct' => true],
                                        ['option_text' => 'Option B', 'is_correct' => false]
                                    ],
                                    items: new OA\Items(
                                        required: ['option_text', 'is_correct'],
                                        properties: [
                                            new OA\Property(property: 'option_text', type: 'string', example: 'Answer option text'),
                                            new OA\Property(property: 'is_correct', type: 'boolean', example: false),
                                        ],
                                        type: 'object'
                                    )
                                ),
                            ],
                            type: 'object'
                        )
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Essay berhasil diperbarui',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Essay updated successfully'),
                        new OA\Property(
                            property: 'essay',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'id', type: 'integer', example: 1),
                                new OA\Property(property: 'title', type: 'string', example: 'IELTS Reading Test 1 - Updated'),
                                new OA\Property(property: 'content', type: 'string', example: 'Updated passage text...'),
                            ]
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Essay tidak ditemukan',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Essay not found')
                    ]
                )
            ),
            new OA\Response(
                response: 422,
                description: 'Validation Error',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'The title field is required. (and 1 more error)'),
                        new OA\Property(
                            property: 'errors',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'title', type: 'array', items: new OA\Items(type: 'string', example: 'The title field is required.')),
                                new OA\Property(property: 'questions', type: 'array', items: new OA\Items(type: 'string', example: 'The questions field is required.')),
                            ]
                        )
                    ]
                )
            ),
        ]
    )]
    public function update(Request $request, $id)
    {
        $essay = Essay::find($id);
        if (!$essay)
            return response()->json(['message' => 'Essay not found'], 404);

        $validated = $request->validate([
            'title' => 'required|string',
            'content' => 'required|string',
            'questions' => 'required|array',
            'questions.*.question_text' => 'required|string',
            'questions.*.options' => 'required|array|min:2',
            'questions.*.options.*.option_text' => 'required|string',
            'questions.*.options.*.is_correct' => 'required|boolean',
        ]);

        DB::transaction(function () use ($essay, $validated) {
            $essay->update(['title' => $validated['title'], 'content' => $validated['content']]);
            $essay->questions()->delete();
            foreach ($validated['questions'] as $qData) {
                $question = $essay->questions()->create(['question_text' => $qData['question_text']]);
                foreach ($qData['options'] as $oData) {
                    $question->options()->create($oData);
                }
            }
        });

        return response()->json(['success' => true, 'data' => $essay->load('questions.options')], 200);
    }

    #[OA\Delete(
        path: '/api/admin/ielts/essays/{id}',
        operationId: 'deleteEssay',
        tags: ['IELTS Admin'],
        summary: 'Hapus Essay',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: 'ID Essay', schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Berhasil Hapus'),
        ]
    )]
    public function destroy($id)
    {
        $question = Question::find($id);
        if (!$question)
            return response()->json(['message' => 'Question not found'], 404);
        $question->delete();
        return response()->json(['success' => true, 'message' => 'Deleted successfully']);
    }
}