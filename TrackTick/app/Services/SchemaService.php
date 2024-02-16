<?php

namespace App\Services;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log; 

/**
 * SchemaService
 * 
 * This service class is responsible for converting incoming data into a standardized schema
 * based on the provider ID.
 * 
 * It provides methods to convert data for different providers and validate the converted schema.
 */
class SchemaService
{
    /**
     * convertSchema
     * 
     * Converts the incoming data into a standardized schema based on the provider ID.
     * 
     * @param int $providerId The ID of the provider
     * @param array $data The incoming data to be converted
     * @return array|null The converted schema or null if conversion fails
     */
    public function convertSchema($providerId, $data)
    {
        try {
            // Initialize converted schema
            $convertedSchema = [];

            // Schema mapping logic for Provider 1
            if ($providerId === 1) {
                $convertedSchema = [
                    'jobTitle' => $data['position'] ?? null,
                    'firstName' => $data['first_name'] ?? null,
                    'lastName' => $data['last_name'] ?? null,
                    'email' => $data['email_address'] ?? null,
                    'address' => [
                        'addressLine1' => $data['address_line_1'] ?? null,
                        'city' => $data['city'] ?? null,
                        'country' => $data['country'] ?? null,
                        'state' => $data['state'] ?? null,
                        'postalCode' => $data['postal_code'] ?? null,
                    ],
                    'primaryPhone' => $data['phone'] ?? null,
                    'username' => $data['username'] ?? null,
                    'password' => $data['password'] ?? null,
                ];
            }

            // Schema mapping logic for Provider 2
            elseif ($providerId === 2) {
                $convertedSchema = [
                    'jobTitle' => $data['title'] ?? null,
                    'firstName' => $data['fname'] ?? null,
                    'lastName' => $data['lname'] ?? null,
                    'email' => $data['email'] ?? null,
                    'address' => [
                        'addressLine1' => $data['street'] ?? null,
                        'city' => $data['city'] ?? null,
                        'country' => $data['country'] ?? null,
                        'state' => $data['province'] ?? null,
                        'postalCode' => $data['postal'] ?? null,
                    ],
                    'primaryPhone' => $data['phone'] ?? null,
                    'username' => $data['username'] ?? null,
                    'password' => $data['password'] ?? null,
                ];
            } else {
                // No provider
                return null;
            }

            // Validate the converted schema 
            $validator = Validator::make($convertedSchema, [
                'jobTitle' => 'required|string|max:255',
                'firstName' => 'required|string|max:255',
                'lastName' => 'required|string|max:255',
                'email' => 'required|email',
                'address.addressLine1' => 'required|string|max:255',
                'address.city' => 'required|string|max:255',
                'address.country' => 'required|string|size:2',
                'address.state' => 'required|string|size:2',
                'address.postalCode' => 'required|string|max:255',
                'primaryPhone' => 'nullable|string|max:20',
                'username' => 'nullable|string|max:255',
                'password' => [
                    'required',
                    'string',
                    'min:8',
                    'regex:/^(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]+$/'
                ]           
             ]);

            // Check if validation fails
            if ($validator->fails()) {
                return null;
            }

            // Return the validated converted schema
            return $convertedSchema;
        } catch (\Exception $e) {
            // Log the exception with class name and more details
            Log::error('Exception occurred in SchemaService convertSchema method: ' . $e->getMessage(), [
                'class' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            // Return error response
            return null;
        }
    }
}
