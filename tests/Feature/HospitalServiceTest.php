<?php
namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Services\Hospital\HospitalService;
use App\Models\Hospital\Hospital;

class HospitalServiceTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_creates_a_hospital_with_departments_services_and_contact()
    {
        $service = new HospitalService();

        $data = [
            'hospital_name' => 'Test Hospital',
            'hospital_type' => 'public',
            'registration_number' => 'REG123',
            'hospital_phone' => '08012345678',
            'hospital_email' => 'test@hospital.com',
            'street_address' => '123 Main St',
            'city' => 'Lagos',
            'state' => 'Lagos',
            'country' => 'Nigeria',
            'departments' => ['Emergency', 'Pediatrics'],
            'services' => ['X-ray', 'Lab Test'],
            'primary_contact_name' => 'John Doe',
            'primary_contact_email' => 'contact@hospital.com',
            'primary_contact_phone' => '08098765432',
            'primary_contact_role' => 'Manager',
            'accept_terms' => true,
            'request_onsite_setup' => false,
            'status' => 'pending',
        ];

        $hospital = $service->createHospital($data);

        $this->assertInstanceOf(Hospital::class, $hospital);
        $this->assertEquals('Test Hospital', $hospital->name);
        $this->assertCount(2, $hospital->departments);
        $this->assertCount(2, $hospital->services);
        $this->assertCount(1, $hospital->contacts);
        $this->assertEquals('John Doe', $hospital->contacts->first()->name);
    }
}