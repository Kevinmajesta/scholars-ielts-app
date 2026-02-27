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
        responses: [new OA\Response(response: 200, description: 'Berhasil')]
    )]
    public function indexDoc()
    {
    }

    #[OA\Get(
        path: '/api/ielts/questions/{id}',
        operationId: 'getSingleQuestionDetailUnique',
        tags: ['IELTS'],
        summary: 'Ambil 1 soal spesifik beserta judul essay dan pilihannya',
        security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 200, description: 'Berhasil')]
    )]
    public function showquestionDoc()
    {
    }

    #[OA\Get(
        path: '/api/ielts/essays/{id}',
        operationId: 'getIeltsDetail',
        tags: ['IELTS'],
        summary: 'Ambil detail essay (Semua soal di dalamnya)',
        security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 200, description: 'Berhasil')]
    )]
    public function showDoc()
    {
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
                    new OA\Property(property: 'answers', type: 'array', items: new OA\Items(type: 'object'))
                ]
            )
        ),
        responses: [new OA\Response(response: 200, description: 'Skor Berhasil Disimpan')]
    )]
    public function submitDoc()
    {
    }

    #[OA\Post(
        path: '/api/admin/ielts/essays',
        operationId: 'createEssay',
        tags: ['IELTS Admin'],
        summary: 'Tambah Soal Baru (Admin)',
        security: [['bearerAuth' => []]],
        responses: [new OA\Response(response: 201, description: 'Created')]
    )]
    public function storeDoc()
    {
    }

    #[OA\Put(
        path: '/api/admin/ielts/essays/{id}',
        operationId: 'updateEssay',
        tags: ['IELTS Admin'],
        summary: 'Update Essay dan Soal (Admin)',
        security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 200, description: 'Updated')]
    )]
    public function updateDoc()
    {
    }

    #[OA\Delete(
        path: '/api/admin/ielts/essays/{id}',
        operationId: 'deleteEssay',
        tags: ['IELTS Admin'],
        summary: 'Hapus Essay (Admin)',
        security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 200, description: 'Deleted')]
    )]
    public function destroyDoc()
    {
    }

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
    public function getHistoryDoc()
    {
    }

    #[OA\Get(
        path: '/api/admin/ielts/results/{id}',
        operationId: 'adminGetResultDetail',
        tags: ['IELTS Admin'],
        summary: 'Admin: Ambil detail riwayat ujian spesifik berdasarkan ID Result',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: 'ID dari Exam Result', schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(response: 200, description: 'Berhasil'),
            new OA\Response(response: 404, description: 'Data tidak ditemukan')
        ]
    )]
    public function getHistoryByIDDoc()
    {
    }
}