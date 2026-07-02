<?php

namespace App\Http\Controllers;

use App\Models\Script;
use Illuminate\Http\Request;

class ScriptController extends Controller
{
    public function active(Request $request)
    {
        $query = Script::active();

        if ($request->filled('page_slug')) {
            $query->forPage($request->page_slug);
        }

        $scripts = $query->get(['id', 'name', 'scope', 'page_slugs']);

        return response()->json($scripts);
    }
}
