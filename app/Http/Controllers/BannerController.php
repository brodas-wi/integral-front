<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use Illuminate\Http\JsonResponse;

class BannerController extends Controller
{
    public function active(): JsonResponse
    {
        $banners = Banner::active()
            ->get()
            ->map(fn($b) => [
                'id'                     => $b->id,
                'title'                  => $b->title,
                'description'            => $b->description,
                'image_url'              => $b->image_url,
                'image_alt'              => $b->image_alt,
                'category'               => $b->category,
                'btn_primary_text'       => $b->btn_primary_text,
                'btn_primary_url'        => $b->btn_primary_url,
                'btn_primary_style'      => $b->btn_primary_style,
                'btn_primary_external'   => $b->btn_primary_external,
                'btn_secondary_text'     => $b->btn_secondary_text,
                'btn_secondary_url'      => $b->btn_secondary_url,
                'btn_secondary_style'    => $b->btn_secondary_style,
                'btn_secondary_external' => $b->btn_secondary_external,
            ]);

        $response = response()->json($banners);

        if ($banners->isNotEmpty() && $banners->first()['image_url']) {
            $response->header('Link', '<' . $banners->first()['image_url'] . '>; rel=preload; as=image');
        }

        return $response;
    }
}
