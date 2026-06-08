<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\TagService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TagSuggestionController extends Controller
{
    public function __invoke(Request $request, TagService $tagService): JsonResponse
    {
        $query = $request->string('q', '')->toString();

        $tags = $tagService->suggest($query);

        return response()->json($tags->map(fn ($tag) => [
            'id'          => $tag->id,
            'name'        => $tag->name,
            'slug'        => $tag->slug,
            'color'       => $tag->color,
            'posts_count' => $tag->posts_count,
        ])->values()->all());
    }
}
