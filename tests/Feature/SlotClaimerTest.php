<?php

namespace Tests\Feature;

use App\Actions\SlotClaimer;
use App\Models\Booking;
use App\Models\Night;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SlotClaimerTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_the_first_claim_on_a_slot_wins(): void
    {
        $night = Night::factory()->slots(1)->create();
        [$first, $second] = Booking::factory()->count(2)->for($night)->create();

        // Both requests read the slot while it is still free ...
        $slotSeenByFirst = $night->slots[0]->fresh();
        $slotSeenBySecond = $night->slots[0]->fresh();

        // ... but the conditional UPDATE lets only one of them write.
        $claimer = new SlotClaimer;
        $this->assertTrue($claimer->claim($slotSeenByFirst, $first));
        $this->assertFalse($claimer->claim($slotSeenBySecond, $second));

        $this->assertSame($first->id, $night->slots[0]->fresh()->booking_id);
    }

    public function test_claim_first_free_skips_taken_slots(): void
    {
        $night = Night::factory()->slots(3)->create();
        [$a, $b] = Booking::factory()->count(2)->for($night)->create();
        $claimer = new SlotClaimer;

        $claimer->claim($night->slots[0], $a);

        $this->assertSame(2, $claimer->claimFirstFree($night, $b)?->position);
    }

    public function test_claim_first_free_returns_null_when_the_night_is_full(): void
    {
        $night = Night::factory()->slots(1)->create();
        [$a, $b] = Booking::factory()->count(2)->for($night)->create();
        $claimer = new SlotClaimer;

        $claimer->claimFirstFree($night, $a);

        $this->assertNull($claimer->claimFirstFree($night, $b));
    }
}
