<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\BaggageItem;
use App\Models\Flight;
use App\Models\Passenger;
use App\Models\ScreeningGate;
use App\Models\ScreeningLog;
use App\Services\PassengerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class OperationalPagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_authenticated_users_can_view_baggage_items(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('baggage-items.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('baggage-items/index')
                ->has('baggageItems')
                ->has('passengers')
                ->has('flights'));
    }

    public function test_authenticated_users_can_view_screening_logs(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('screening-logs.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('screening-logs/index')
                ->has('screeningLogs')
                ->where('screeningLogs.data', [])
                ->has('baggageItems')
                ->has('screeningGates'));
    }

    public function test_authenticated_users_can_manage_reference_records_needed_for_screening(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get(route('passengers.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('passengers/index'));
        $this->get(route('flights.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('flights/index'));
        $this->get(route('screening-gates.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('screening-gates/index'));
    }

    public function test_baggage_items_use_requests_services_and_models_for_create_and_update(): void
    {
        $this->actingAs(User::factory()->create());
        $passenger = Passenger::create([
            'passenger_passport' => 'P-123',
            'passenger_name' => 'Test Passenger',
        ]);
        $flight = Flight::create(['flight_code' => 'AP123']);

        $this->post(route('baggage-items.store'), [
            'passenger_id' => $passenger->id,
            'flight_id' => $flight->id,
            'bag_tag_number' => ' bag-123 ',
            'bag_weight_kg' => 32.01,
        ])->assertRedirect(route('baggage-items.index'));

        $baggage = BaggageItem::where('bag_tag_number', 'BAG-123')->firstOrFail();
        $this->assertTrue($baggage->heavy_cargo);
        $this->assertFalse(Schema::hasColumn('baggage_items', 'heavy_cargo'));

        $this->put(route('baggage-items.update', $baggage), [
            'passenger_id' => $passenger->id,
            'flight_id' => $flight->id,
            'bag_tag_number' => 'bag-456',
            'bag_weight_kg' => 32,
        ])->assertRedirect(route('baggage-items.index'));

        $this->assertDatabaseHas('baggage_items', [
            'id' => $baggage->id,
            'bag_tag_number' => 'BAG-456',
        ]);
        $this->assertFalse($baggage->fresh()->heavy_cargo);
    }

    public function test_delete_actions_remove_records_and_their_dependents(): void
    {
        $this->actingAs(User::factory()->create());

        [$passenger, $flight, $gate, $baggage, $log] = $this->createOperationalRecords('LOG');
        $this->delete(route('screening-logs.destroy', $log))
            ->assertRedirect(route('screening-logs.index'));
        $this->assertDatabaseMissing('screening_logs', ['id' => $log->id]);

        [$passenger, $flight, $gate, $baggage, $log] = $this->createOperationalRecords('BAG');
        $this->delete(route('baggage-items.destroy', $baggage))
            ->assertRedirect(route('baggage-items.index'));
        $this->assertDatabaseMissing('baggage_items', ['id' => $baggage->id]);
        $this->assertDatabaseMissing('screening_logs', ['id' => $log->id]);
        $this->assertDatabaseHas('passengers', ['id' => $passenger->id]);
        $this->assertDatabaseHas('flights', ['id' => $flight->id]);

        [$passenger, $flight, $gate, $baggage, $log] = $this->createOperationalRecords('PASSENGER');
        $this->delete(route('passengers.destroy', $passenger))
            ->assertRedirect(route('passengers.index'));
        $this->assertDatabaseMissing('baggage_items', ['id' => $baggage->id]);
        $this->assertDatabaseMissing('screening_logs', ['id' => $log->id]);

        [$passenger, $flight, $gate, $baggage, $log] = $this->createOperationalRecords('FLIGHT');
        $this->delete(route('flights.destroy', $flight))
            ->assertRedirect(route('flights.index'));
        $this->assertDatabaseMissing('baggage_items', ['id' => $baggage->id]);
        $this->assertDatabaseMissing('screening_logs', ['id' => $log->id]);

        [$passenger, $flight, $gate, $baggage, $log] = $this->createOperationalRecords('GATE');
        $this->delete(route('screening-gates.destroy', $gate))
            ->assertRedirect(route('screening-gates.index'));
        $this->assertDatabaseMissing('screening_logs', ['id' => $log->id]);
        $this->assertDatabaseHas('baggage_items', ['id' => $baggage->id]);
    }

    private function createOperationalRecords(string $suffix): array
    {
        $passenger = Passenger::create([
            'passenger_passport' => 'P-'.$suffix,
            'passenger_name' => 'Delete Passenger',
        ]);
        $flight = Flight::create(['flight_code' => 'AP-'.$suffix]);
        $gate = ScreeningGate::create(['gate_code' => 'GATE-'.$suffix]);
        $baggage = BaggageItem::create([
            'passenger_id' => $passenger->id,
            'flight_id' => $flight->id,
            'bag_tag_number' => 'BAG-'.$suffix,
            'bag_weight_kg' => 10,
        ]);
        $log = ScreeningLog::create([
            'baggage_item_id' => $baggage->id,
            'screening_gate_id' => $gate->id,
            'screener_officer' => 'Officer',
            'screening_result' => 'Passed',
            'clearance_status' => 'Pending',
        ]);

        return [$passenger, $flight, $gate, $baggage, $log];
    }

    public function test_baggage_tags_are_normalized_before_duplicate_validation(): void
    {
        $this->actingAs(User::factory()->create());
        $passenger = Passenger::create([
            'passenger_passport' => 'P-789',
            'passenger_name' => 'Tag Passenger',
        ]);
        $flight = Flight::create(['flight_code' => 'AP789']);
        BaggageItem::create([
            'passenger_id' => $passenger->id,
            'flight_id' => $flight->id,
            'bag_tag_number' => 'BAG-LOWER',
            'bag_weight_kg' => 12,
        ]);

        $this->post(route('baggage-items.store'), [
            'passenger_id' => $passenger->id,
            'flight_id' => $flight->id,
            'bag_tag_number' => ' bag-lower ',
            'bag_weight_kg' => 12,
        ])->assertSessionHasErrors('bag_tag_number');

        $this->post(route('baggage-items.store'), [
            'passenger_id' => $passenger->id,
            'flight_id' => $flight->id,
            'bag_tag_number' => ['BAG-ARRAY'],
            'bag_weight_kg' => 12,
        ])->assertSessionHasErrors('bag_tag_number');
    }

    public function test_passenger_service_stores_passport_numbers_in_uppercase(): void
    {
        $passenger = app(PassengerService::class)->create([
            'passenger_passport' => ' p-lower ',
            'passenger_name' => 'Existing Passenger',
        ]);

        $this->assertSame('P-LOWER', $passenger->passenger_passport);
    }

    public function test_screening_requests_and_service_enforce_failed_clearance_rules(): void
    {
        $this->actingAs(User::factory()->create());
        $passenger = Passenger::create([
            'passenger_passport' => 'P-456',
            'passenger_name' => 'Screening Passenger',
        ]);
        $flight = Flight::create(['flight_code' => 'AP456']);
        $baggage = BaggageItem::create([
            'passenger_id' => $passenger->id,
            'flight_id' => $flight->id,
            'bag_tag_number' => 'BAG-789',
            'bag_weight_kg' => 12,
        ]);
        $gate = ScreeningGate::create(['gate_code' => 'GATE-1']);
        $data = [
            'baggage_item_id' => $baggage->id,
            'screening_gate_id' => $gate->id,
            'screener_officer' => ' Officer A ',
            'screening_result' => 'Failed',
            'clearance_status' => 'Cleared',
        ];

        $this->post(route('screening-logs.store'), $data)
            ->assertSessionHasErrors('flagged_reason');

        $this->post(route('screening-logs.store'), [
            ...$data,
            'flagged_reason' => 'Prohibited item found',
        ])->assertRedirect(route('screening-logs.index'));

        $log = ScreeningLog::firstOrFail();
        $this->assertSame('Officer A', $log->screener_officer);
        $this->assertSame('Held', $log->clearance_status);

        $this->put(route('screening-logs.update', $log), [
            ...$data,
            'flagged_reason' => '',
        ])->assertSessionHasErrors('flagged_reason');

        $this->put(route('screening-logs.update', $log), [
            ...$data,
            'screening_result' => 'Passed',
            'flagged_reason' => '',
            'clearance_status' => 'Pending',
        ])->assertRedirect(route('screening-logs.index'));

        $this->assertDatabaseHas('screening_logs', [
            'id' => $log->id,
            'screening_result' => 'Passed',
            'flagged_reason' => null,
            'clearance_status' => 'Pending',
        ]);
    }
}
