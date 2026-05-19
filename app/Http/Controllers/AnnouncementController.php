<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AnnouncementController extends Controller
{
    public function forPage(Request $request): JsonResponse
    {
        $pageSlug = $request->input('page', 'home');

        $announcements = Announcement::active()
            ->forPage($pageSlug)
            ->byPriority()
            ->with('media')
            ->get();

        if ($announcements->isEmpty()) {
            return response()->json(['success' => false, 'announcements' => []]);
        }

        return response()->json([
            'success'       => true,
            'announcements' => $announcements->map(fn($a) => [
                'id'           => $a->id,
                'title'        => $a->title,
                'description'  => $a->description,
                'image_url'    => $a->media?->url,
                'image_alt'    => $a->media?->alt ?? $a->title,
                'image_width'  => $a->media?->width,
                'image_height' => $a->media?->height,
                'cta_text'     => $a->cta_text,
                'cta_url'      => $a->cta_url,
                'cta_new_tab'  => $a->cta_new_tab,
                'display_mode' => $a->display_mode,
            ])->values()->all(),
        ]);
    }
}
