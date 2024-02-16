<?php

namespace App\Http\Controllers;

use App\Services\TrackTickService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log; 
use App\Services\SchemaService;

class TrackTickController extends Controller
{
    protected $trackTickService;
    protected $schemaService;

    public function __construct(TrackTickService $trackTickService, SchemaService $schemaService)
    {
        $this->trackTickService = $trackTickService;
        $this->schemaService = $schemaService;
    }

    public function postEmployee(Request $request)
    {
        try {
            // Retrieve username and password from headers
            $username = $request->header('username');
            $password = $request->header('password');

            // Check if username and password headers are present
            if (empty($username) || empty($password)) {
                return response()->json(['error' => 'Username and password headers are required'], 400);
            }

            // Authenticate user
            if (!Auth::attempt(['username' => $username, 'password' => $password])) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }
            
            // Retrieve the authenticated user
            $user = Auth::user();

            // Get the provider ID from the user
            $providerId = $user->provider_id;

            // Convert the schema based on the provider
            $convertedSchema = $this->schemaService->convertSchema($providerId, $request->all());
            // If conversion fails, return appropriate message
            if (!$convertedSchema) {
                return response()->json(['error' => 'Failed to convert schema'], 400);
            }

            // Post the converted schema to TrackTick
            $response = $this->trackTickService->postToTrackTick($convertedSchema);

            // Return the response from TrackTick
            return $response;
        } catch (\Exception $e) {
            // Log the exception with class name and more details
            Log::error('Exception occurred in TrackTickController: ' . $e->getMessage(), [
                'class' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            // Return error response
            return response()->json(['error' => 'Internal Server Error'], 500);
        }
    }
}
