<?php

namespace App\Http\Controllers;

use App\Ai\Agents\DevTestAssistant;
use Illuminate\Http\Request;

class DevTestController extends Controller
{
   public function executePrompt(Request $request)
   {
    $validated = $request->validate([
        'prompt' => 'required|string',
    ]);

    $assistant = new DevTestAssistant();
    $response = $assistant->prompt($validated['prompt']);
    
    return response()->json([
        'prompt' => $validated['prompt'],
        'response' => $response,
    ]);
   }
}
