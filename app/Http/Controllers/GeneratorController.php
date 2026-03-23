<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Ai\Agents\ContentGeneratorAgent;
use App\Http\Requests\GenerateContentRequest;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;

class GeneratorController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Generator/Index');
    }

    public function generate(GenerateContentRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $tone   = $validated['tone']   ?? 'professional';
        $length = $validated['length'] ?? 'medium';
        $topic  = $validated['topic'];

        $prompt = "Write a {$length} {$tone} article about: {$topic}";

        $response = ContentGeneratorAgent::make()->prompt($prompt);

        return response()->json(['content' => $response->text]);
    }
}
