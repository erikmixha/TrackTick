<?php

namespace Tests\Feature;

use App\Services\SchemaService;
use App\Services\TrackTickService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TrackTickControllerTest extends TestCase
{
    /**
     * Test schema conversion with valid data.
     *
     * @return void
     */
    public function testSchemaConversion()
    {
        // Mock the TrackTickService
        $schemaService = new SchemaService();

        // Sample provider ID and data
        $providerId = 1;
        $data = [
            "position" => "Software Engineer",
            "first_name" => "Johnnn",
            "last_name" => "Doeee",
            "email_address" => "john.doeee@example.com",
            "address_line_1" => "123 Main St",
            "city" => "Anytown",
            "country" => "US",
            "state" => "CA",
            "postal_code" => "12345",
            "phone" => "123-456-7890",
            "username" => "johndoeee",
            "password" => "Securepassword123!"
        ];

        // Convert the schema
        $convertedSchema = $schemaService->convertSchema($providerId, $data);

        // Assert that the converted schema is not null
        $this->assertNotNull($convertedSchema);

        // Assert that the converted schema contains expected keys
        $this->assertArrayHasKey('jobTitle', $convertedSchema);
        $this->assertArrayHasKey('firstName', $convertedSchema);
        $this->assertArrayHasKey('lastName', $convertedSchema);
        $this->assertArrayHasKey('email', $convertedSchema);
        $this->assertArrayHasKey('address', $convertedSchema);
        $this->assertArrayHasKey('primaryPhone', $convertedSchema);
        $this->assertArrayHasKey('username', $convertedSchema);
        $this->assertArrayHasKey('password', $convertedSchema);
    }
}
