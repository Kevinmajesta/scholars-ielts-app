<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Essay;
use App\Models\Option;
use App\Models\Question;
use finfo;
use Illuminate\Http\Request;
use App\Models\ExamResult;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use OpenApi\Attributes as OA;
use App\Traits\IeltsSwaggerTrait;
use App\Mail\ScoreNotification;

/**
 * @OA\Info(title="IELTS API", version="1.0.0")
 * @OA\Server(url="http://127.0.0.1:8000", description="Local Server")
 * @OA\SecurityScheme(securityScheme="bearerAuth", type="http", scheme="bearer", bearerFormat="JWT")
 */
class IeltsController extends Controller
{
    use IeltsSwaggerTrait;
    public function index()
    {
        $essays = Essay::with('questions.options')->get();
        return response()->json(['success' => true, 'data' => $essays]);
    }

    public function showquestion($id)
    {
        $question = Question::with(['essay', 'options'])->find($id);

        if (!$question) {
            return response()->json(['message' => 'Soal tidak ditemukan'], 404);
        }
        return response()->json([
            'success' => true,
            'data' => $question
        ]);
    }

    public function show($id)
    {
        $essay = Essay::with('questions.options')->find($id);
        if (!$essay)
            return response()->json(['message' => 'Essay not found'], 404);
        return response()->json(['success' => true, 'data' => $essay]);
    }

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

        try {
            $user = auth('api')->user();
            Mail::to($user->email)->send(new ScoreNotification($result));
        } catch (\Exception $e) {
                \Log::error('Failed to send score email: ' . $e->getMessage());
        }

        return response()->json(['success' => true, 'results' => $result]);
    }

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

    public function destroy($id)
    {
        $question = Question::find($id);
        if (!$question)
            return response()->json(['message' => 'Question not found'], 404);
        $question->delete();
        return response()->json(['success' => true, 'message' => 'Deleted successfully']);
    }

    public function getHistory()
    {
        $results = ExamResult::with('essay')->where('user_id', auth('api')->id())->get();
        return response()->json(['success' => true, 'data' => $results]);
    }

    public function getHistoryByID($id)
    {
        $result = ExamResult::with('essay')->where('user_id', auth('api')->id())->find($id);
        if (!$result)
            return response()->json(['message' => 'Result / User not found'], 404);
        return response()->json(['success' => true, 'data' => $result]);
    }
}