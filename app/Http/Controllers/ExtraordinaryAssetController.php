<?php

namespace App\Http\Controllers;

use App\Models\ExtraordinaryAsset;
use Illuminate\Http\Request;

class ExtraordinaryAssetController extends Controller
{
    public function active(Request $request)
    {
        $query = ExtraordinaryAsset::active()->with('category')->latest();

        if ($request->filled('category')) {
            $query->category($request->category);
        }

        $assets = $query->get();

        return response()->json($assets->map(function ($asset) {
            return [
                'id'                => $asset->id,
                'name'              => $asset->name,
                'short_description' => $asset->short_description,
                'image_url'         => $asset->image_url,
                'link_url'          => $asset->link_url,
                'link_is_external'  => $asset->link_is_external,
                'category'          => $asset->category->name,
                'category_slug'     => $asset->category->slug,
            ];
        }));
    }
}
