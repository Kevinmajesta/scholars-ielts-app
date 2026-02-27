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
        description: 'Jika login sebagai Admin, fitur Search dan Pagination akan aktif. Jika Student, akan mengembalikan semua data dengan opsi jawaban yang diacak.',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'search',
                in: 'query',
                description: 'Cari berdasarkan judul essay (Hanya untuk Admin)',
                required: false,
                schema: new OA\Schema(type: 'string', example: 'Tea')
            ),
            new OA\Parameter(
                name: 'limit',
                in: 'query',
                description: 'Jumlah data per halaman (Hanya untuk Admin)',
                required: false,
                schema: new OA\Schema(type: 'integer', default: 10, example: 5)
            ),
            new OA\Parameter(
                name: 'page',
                in: 'query',
                description: 'Halaman ke-berapa (Hanya untuk Admin)',
                required: false,
                schema: new OA\Schema(type: 'integer', default: 1)
            )
        ],
        responses: [
            new OA\Response(
                response: 200, 
                description: 'Berhasil ambil data',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status_code', type: 'integer', example: 200),
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Daftar essay berhasil diambil'),
                        new OA\Property(property: 'data', type: 'object', description: 'Bisa berupa Array (User) atau Object Pagination (Admin)')
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 404, description: 'Tidak ada essay yang tersedia')
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
        summary: 'Admin: Tambah Soal Baru (Essay Opsional)',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['questions'],
                properties: [
                    new OA\Property(property: 'title', type: 'string', example: 'Judul Essay (Kosongkan jika standalone)', nullable: true),
                    new OA\Property(property: 'content', type: 'string', example: 'Isi Teks Bacaan...', nullable: true),
                    new OA\Property(
                        property: 'questions',
                        type: 'array',
                        items: new OA\Items(
                            properties: [
                                new OA\Property(property: 'question_text', type: 'string', example: 'Pertanyaannya?'),
                                new OA\Property(
                                    property: 'options',
                                    type: 'array',
                                    items: new OA\Items(
                                        properties: [
                                            new OA\Property(property: 'option_text', type: 'string', example: 'Pilihan A'),
                                            new OA\Property(property: 'is_correct', type: 'boolean', example: true)
                                        ]
                                    )
                                )
                            ]
                        )
                    )
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Data Berhasil Dibuat')
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
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['questions'],
                properties: [
                    new OA\Property(property: 'title', type: 'string', nullable: true),
                    new OA\Property(property: 'content', type: 'string', nullable: true),
                    new OA\Property(property: 'questions', type: 'array', items: new OA\Items(type: 'object'))
                ]
            )
        ),
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
            new OA\Response(response: 200, description: 'Berhasil ambil semua data')
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
            new OA\Response(response: 200, description: 'Berhasil')
        ]
    )]
    public function getHistoryByIDDoc() {}
}