<?php

namespace App\Traits;

use OpenApi\Attributes as OA;

trait IeltsSwaggerTrait
{
    #[OA\Get(
        path: '/api/ielts/questions',
        operationId: 'getIeltsList',
        tags: ['IELTS'],
        summary: 'Ambil daftar semua essay dan soal',
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Berhasil ambil data')
        ]
    )]
    public function indexDoc() {}

    #[OA\Get(
        path: '/api/ielts/questions/{id}',
        operationId: 'getSingleQuestionDetailUnique',
        tags: ['IELTS'],
        summary: 'Ambil 1 soal spesifik beserta judul essay dan pilihannya',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: 'ID Question', schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(response: 200, description: 'Berhasil')
        ]
    )]
    public function showquestionDoc() {}

    #[OA\Get(
        path: '/api/ielts/essays/{id}',
        operationId: 'getIeltsDetail',
        tags: ['IELTS'],
        summary: 'Ambil detail essay (Semua soal di dalamnya)',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: 'ID Essay', schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(response: 200, description: 'Berhasil')
        ]
    )]
    public function showDoc() {}

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
                                new OA\Property(property: 'option_id', type: 'integer', example: 2)
                            ],
                            type: 'object'
                        )
                    )
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Skor Berhasil Disimpan')
        ]
    )]
    public function submitDoc() {}

    // --- ADMIN ROUTES ---

    #[OA\Post(
        path: '/api/admin/ielts/essays',
        operationId: 'createEssay',
        tags: ['IELTS Admin'],
        summary: 'Admin: Tambah Soal Baru',
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(response: 201, description: 'Created')
        ]
    )]
    public function storeDoc() {}

    #[OA\Put(
        path: '/api/admin/ielts/essays/{id}',
        operationId: 'updateEssay',
        tags: ['IELTS Admin'],
        summary: 'Admin: Update Essay dan Soal',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(response: 200, description: 'Updated')
        ]
    )]
    public function updateDoc() {}

    #[OA\Delete(
        path: '/api/admin/ielts/questions/{id}',
        operationId: 'deleteQuestion',
        tags: ['IELTS Admin'],
        summary: 'Admin: Hapus Soal Spesifik',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(response: 200, description: 'Deleted')
        ]
    )]
    public function destroyDoc() {}

    #[OA\Get(
        path: '/api/admin/ielts/results',
        operationId: 'adminGetAllResults',
        tags: ['IELTS Admin'],
        summary: 'Admin: Ambil semua riwayat ujian seluruh user',
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Berhasil ambil semua data'),
            new OA\Response(response: 403, description: 'Forbidden (Bukan Admin)')
        ]
    )]
    public function getHistoryDoc() {}

    #[OA\Get(
        path: '/api/admin/ielts/results/{id}',
        operationId: 'adminGetResultDetail',
        tags: ['IELTS Admin'],
        summary: 'Admin: Ambil detail riwayat ujian spesifik',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: 'ID Exam Result', schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(response: 200, description: 'Berhasil'),
            new OA\Response(response: 404, description: 'Data tidak ditemukan')
        ]
    )]
    public function getHistoryByIDDoc() {}
}