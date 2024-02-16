<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TrackTickService
{ 
    protected $apiUrl; 

    public function __construct()
    {
        // Retrieve the API URL from the environment
        $this->apiUrl = env('TRACKTICK_API_URL');
    }

    public function postToTrackTick($data)
    {
        try {
            // Obtain the access token
            $accessToken = $this->getAccessToken();
        
            // Search for the employee
            $employeeJson = $this->searchEmployee($accessToken,$data);
        
           // Check if the employee exists and get id 
           if (count($employeeJson['data']) > 0 && isset($employeeJson['data'][0]['id'])) {
                 // Employee found, update the employee
               return $this->updateEmployee($accessToken, $employeeJson['data'][0]['id'], $data);
            } else {
                // Employee not found , create a new employee
                return $this->createEmployee($accessToken,  $data);
            }
        } catch (\Exception $e) {
            // Log the exception with class name and more details
            Log::error('Exception occurred in TrackTickService: ' . $e->getMessage(), [
                'class' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            // Return error response
            return response()->json(['error' => 'Internal Server Error'], 500);
        }
    }

    protected function getAccessToken()
    {
        try {
            // Retrieve client credentials and token endpoint URL from environment variables
            $clientId = env('OAUTH_CLIENT_ID');
            $clientSecret = env('OAUTH_CLIENT_SECRET');
            $tokenUrl = env('OAUTH_TOKEN_URL');

            //Note: we have to use a mechanism to save these newly refreshed token so that we dont lose it and to create a way 
            //that uses the current token if it has not yet expired. This could be done either by saving the data somewhere in this case tokens.json 
            //or by having a dblog(tablename) and save and retrieve it from there..

            $tokenData = json_decode(file_get_contents('tokens.json'), true);

            // Retrieve tokens from the loaded data
            $accessToken = $tokenData['access_token'] ?? null;
            $refreshToken = $tokenData['refresh_token'] ?? null;
            $timestamp = $tokenData['timestamp'] ?? null;

            // Check if the access token exists and is not expired
            if (!empty($accessToken) && !$this->isAccessTokenExpired($timestamp)) {
                return $accessToken;
            }

            // Access token doesn't exist or is expired, refresh it
            $response = Http::post($tokenUrl, [
                'grant_type' => 'refresh_token',
                'refresh_token' => $refreshToken,
                'client_id' => $clientId,
                'client_secret' => $clientSecret
            ]);

            // Log the response data so to not lose the token in case the method fails somehow
            Log::debug('Token refresh response:', $response->json());

            if ($response->successful()) {
                // Decode the JSON response
                $responseData = $response->json();
            
                // Extract the access token, refresh token, and timestamp from the decoded response
                $accessToken = $responseData['access_token'];
                $newRefreshToken = $responseData['refresh_token'];

                // Update the tokens in the tokenData array
                $tokenData['access_token'] = $accessToken;
                $tokenData['refresh_token'] = $newRefreshToken;
                $tokenData['timestamp'] = time();

                // Wipe everything inside the file
                file_put_contents('tokens.json', '');

                // Save the updated tokens back to tokens.json, overwriting its contents
                file_put_contents('tokens.json', json_encode($tokenData), LOCK_EX);

                // Return the access token
                return $accessToken;
            } else {
                return null;
            }
        } catch (\Exception $e) {
            // Log the exception with class name and more details
            Log::error('Exception occurred in TrackTickService getAccessToken method: ' . $e->getMessage(), [
                'class' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            // Return error response
            return null;
        }
    }

    
    protected function isAccessTokenExpired($timestamp)
    {
        
        // Retrieve the expires_in value
        $expiresIn = env('EXPIRES_IN');
        
        // Calculate the expiration time based on the timestamp and expires_in value
        $expirationTime = $timestamp + $expiresIn;

        // Get the current timestamp
        $currentTime = time();

        // Check if the expiration time is in the past
        return $currentTime >= $expirationTime;
    }


    protected function searchEmployee($accessToken, $data)
    {
        try {
            
            // Prepare the query parameters
            $queryParams = [
                // Filter by some attributes to retrieve one employee id if it exists so that we update, this is considering that the email parameter is unique
                
                'email:contains' => "" . $data['email'] . ""
            ];
            // Send a GET request to the List Employees endpoint with the access token and query parameters
            $response = Http::withToken($accessToken)
                            ->get($this->apiUrl . '/employees', $queryParams);

            if ($response->successful()) {
                // Return the JSON response containing the employee information
                return $response->json();
            } else {
                return response()->json(['error' => 'Failed to list employees'], $response->status());
            }
        } catch (\Exception $e) {
            // Log the exception with class name and more details
            Log::error('Exception occurred in TrackTickService searchEmployee method: ' . $e->getMessage(), [
                'class' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            // Return error response
            return null;
        }
    }
    
    protected function updateEmployee($accessToken, $employeeId, $data)
    {
        try {
            // Send a PUT request to the Update Employee endpoint with the access token and employee ID
            $response = Http::withToken($accessToken)
                            ->put($this->apiUrl . "/employees/{$employeeId}", $data);
        
            if ($response->successful()) {
                // Employee updated successfully
                return $response->json();
            } else {
                return response()->json(['error' => 'Failed to update employee'], $response->status());
            }
        } catch (\Exception $e) {
            // Log the exception with class name and more details
            Log::error('Exception occurred in TrackTickService updateEmployee method: ' . $e->getMessage(), [
                'class' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            // Return error response
            return null;
        }
    }
    
    protected function createEmployee($accessToken, $data)
    {
        try {
            // Send a POST request to the Create Employee endpoint with the access token
            $response = Http::withToken($accessToken)
                            ->post($this->apiUrl . '/employees', $data);
            
            if ($response->successful()) {
                // Employee created successfully
                return $response->json();
            } else {
                return response()->json(['error' => 'Failed to create employee'], $response->status());
            }
        } catch (\Exception $e) {
            // Log the exception with class name and more details
            Log::error('Exception occurred in TrackTickService createEmployee method: ' . $e->getMessage(), [
                'class' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            // Return error response
            return null;
        }
    }
}
