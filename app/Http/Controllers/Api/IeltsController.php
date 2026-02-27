<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Essay;
use App\Models\Option;
use App\Models\Question;
use App\Models\ExamResult;
use App\Traits\ApiResponse;
use App\Traits\IeltsSwaggerTrait;
use App\Mail\ScoreNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

/**
 * @OA\Info(title="IELTS API", version="1.0.0")
 * @OA\Server(url="http://127.0.0.1:8000", description="Local Server")
 * @OA\SecurityScheme(securityScheme="bearerAuth", type="http", scheme="bearer", bearerFormat="JWT")
 */
class IeltsController extends Controller
{
    use IeltsSwaggerTrait, ApiResponse;

    public function index(Request $request)
    {
        $user = auth('api')->user();

        $query = Essay::with([
            'questions.options' => function ($q) {
                $q->inRandomOrder();
            }
        ]);

        if ($user->role === 'admin') {
            if ($request->has('search')) {
                $query->where('title', 'LIKE', '%' . $request->search . '%');
            }

            $limit = $request->input('limit', 10);
            $data = $query->paginate($limit);
        } else {
            $data = $query->get();
        }

        $isEmpty = ($user->role === 'admin') ? ($data->total() === 0) : $data->isEmpty();

        if ($isEmpty) {
            return $this->sendError('Tidak ada essay yang tersedia', 404);
        }

        return $this->sendResponse($data, 'Daftar essay berhasil diambil');
    }

    public function showquestion($id)
    {
        $question = Question::with(['essay', 'options'])->find($id);

        if (!$question) {
            return $this->sendError('Soal tidak ditemukan', 404);
        }

        return $this->sendResponse($question, 'Detail soal ditemukan');
    }

    public function show($id)
    {
        $essay = Essay::with('questions.options')->find($id);

        if (!$essay) {
            return $this->sendError('Essay tidak ditemukan', 404);
        }

        return $this->sendResponse($essay, 'Detail essay ditemukan');
    }
    public function submit(Request $request)
    {
        $request->validate([
            'essay_id' => 'required|exists:essays,id',
            'answers' => 'required|array'
        ]);

        $userAnswers = $request->input('answers', []);
        if (empty($userAnswers)) {
            return $this->sendError('Jawaban kosong', 400);
        }

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

        $score = ($totalQuestions > 0) ? ($correctCount / $totalQuestions) * 100 : 0;

        $result = ExamResult::create([
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
            \Log::error('Gagal mengirim email: ' . $e->getMessage());
        }

        return $this->sendResponse($result, 'Skor berhasil dihitung, disimpan, dan email terkirim');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'nullable|string',
            'content' => 'nullable|string',
            'questions' => 'required|array|min:1',
            'questions.*.question_text' => 'required|string',
            'questions.*.options' => 'required|array|min:2',
            'questions.*.options.*.option_text' => 'required|string',
            'questions.*.options.*.is_correct' => 'required|boolean',
        ]);

        $essay = DB::transaction(function () use ($validated) {
            $essay = Essay::create([
                'title' => $validated['title'] ?? 'Standalone Question ' . now()->format('d/m/Y H:i'),
                'content' => $validated['content'] ?? '-'
            ]);

            foreach ($validated['questions'] as $qData) {
                $question = $essay->questions()->create([
                    'question_text' => $qData['question_text']
                ]);

                foreach ($qData['options'] as $oData) {
                    $question->options()->create($oData);
                }
            }
            return $essay;
        });

        return $this->sendResponse(
            $essay->load('questions.options'),
            'Data berhasil dibuat' . ($request->title ? '' : ' (Tanpa Essay)'),
            201
        );
    }

    public function update(Request $request, $id)
    {
        $essay = Essay::find($id);
        if (!$essay) {
            return $this->sendError('Data tidak ditemukan', 404);
        }

        $validated = $request->validate([
            'title' => 'nullable|string',
            'content' => 'nullable|string',
            'questions' => 'required|array|min:1',
            'questions.*.question_text' => 'required|string',
            'questions.*.options' => 'required|array|min:2',
            'questions.*.options.*.option_text' => 'required|string',
            'questions.*.options.*.is_correct' => 'required|boolean',
        ]);

        DB::transaction(function () use ($essay, $validated) {
            $essay->update([
                'title' => $validated['title'] ?? $essay->title,
                'content' => $validated['content'] ?? '-'
            ]);

            $essay->questions()->delete();

            foreach ($validated['questions'] as $qData) {
                $question = $essay->questions()->create([
                    'question_text' => $qData['question_text']
                ]);

                foreach ($qData['options'] as $oData) {
                    $question->options()->create($oData);
                }
            }
        });

        return $this->sendResponse(
            $essay->load('questions.options'),
            'Data berhasil diperbarui'
        );
    }

    public function destroy($id)
    {
        $question = Question::find($id);
        if (!$question)
            return $this->sendError('Tidak dapat mendapatkan soal', 404);

        $question->delete();
        return $this->sendResponse(null, 'Soal berhasil dihapus');
    }

    public function getHistory()
    {
        $results = ExamResult::with('essay')->where('user_id', auth('api')->id())->get();
        if ($results->isEmpty()) {
            return $this->sendError('Riwayat nilai masih kosong', 404);
        }
        return $this->sendResponse($results, 'Riwayat nilai berhasil diambil');
    }

    public function getHistoryByID($id)
    {
        $user = auth('api')->user();
        $query = ExamResult::with(['essay', 'user']);

        if ($user->role !== 'admin') {
            $query->where('user_id', $user->id);
        }

        $result = $query->find($id);

        if (!$result) {
            return $this->sendError('Riwayat tidak ditemukan, ID salah, atau Anda tidak memiliki akses', 404);
        }

        return $this->sendResponse($result, 'Detail riwayat ditemukan');
    }
}