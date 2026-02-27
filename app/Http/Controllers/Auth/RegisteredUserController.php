<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\Mail;
use App\Mail\WelcomeMail;
use OpenApi\Attributes as OA;

#[OA\Post(
    path: '/api/register',
    operationId: 'register',
    tags: ['Auth'],
    summary: 'Register User Baru',
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'name', type: 'string', example: 'Kevin Majesta'),
                new OA\Property(property: 'email', type: 'string', example: 'kevin@example.com'),
                new OA\Property(property: 'password', type: 'string', example: 'password123'),
                new OA\Property(property: 'password_confirmation', type: 'string', example: 'password123'),
            ]
        )
    ),
    responses: [
        new OA\Response(
            response: 201,
            description: 'User berhasil terdaftar',
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'message', type: 'string', example: 'User registered successfully!'),
                    new OA\Property(
                        property: 'user',
                        type: 'object',
                        properties: [
                            new OA\Property(property: 'id', type: 'integer', example: 1),
                            new OA\Property(property: 'name', type: 'string', example: 'Kevin Majesta'),
                            new OA\Property(property: 'email', type: 'string', example: 'kevin@example.com'),
                            new OA\Property(property: 'role', type: 'string', example: 'user'),
                        ]
                    ),
                ]
            )
        ),
        new OA\Response(
            response: 422,
            description: 'Validation Error',
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'message', type: 'string', example: 'The email has already been taken.'),
                    new OA\Property(
                        property: 'errors',
                        type: 'object',
                        properties: [
                            new OA\Property(
                                property: 'email',
                                type: 'array',
                                items: new OA\Items(type: 'string', example: 'The email has already been taken.')
                            ),
                        ]
                    ),
                ]
            )
        ),
    ]
)]
class RegisteredUserController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->string('password')),
            'role' => 'user',
        ]);

        event(new Registered($user));

        try {
            Mail::to($user->email)->send(new WelcomeMail($user));
        } catch (\Exception $e) {
            // Log error jika email gagal, tapi register tetap lanjut
            \Log::error("Gagal kirim email registrasi: " . $e->getMessage());
        }

        return response()->json([
            'message' => 'User registered successfully!',
            'user' => $user,
        ], 201);
    }
}