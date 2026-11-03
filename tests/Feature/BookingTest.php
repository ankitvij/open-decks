<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Mail\BookingReceived;
use App\Models\Booking;
use App\Models\Night;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class BookingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
    }

    public function test_dj_gets_the_earliest_free_slot(): void
    {
        $night = Night::factory()->slots(3)->create();

        $this->post(route('bookings.store', $night), $this->dj('Mira'))
            ->assertRedirect(route('nights.show', $night))
            ->assertSessionHas('status');

        $booking = Booking::sole();
        $this->assertSame(BookingStatus::Confirmed, $booking->status);
        $this->assertSame(1, $booking->slot->position);
        Mail::assertSent(BookingReceived::class, fn (BookingReceived $mail) => $mail->hasTo('mira@example.com'));
    }

    public function test_dj_can_pick_a_specific_slot(): void
    {
        $night = Night::factory()->slots(3)->create();
        $third = $night->slots[2];

        $this->post(route('bookings.store', $night), $this->dj('Mira') + ['slot_id' => $third->id]);

        $this->assertSame($third->id, Booking::sole()->slot->id);
    }

    public function test_a_taken_slot_cannot_be_booked_twice(): void
    {
        $night = Night::factory()->slots(3)->create();
        $first = $night->slots[0];

        $this->post(route('bookings.store', $night), $this->dj('Mira') + ['slot_id' => $first->id]);
        $this->post(route('bookings.store', $night), $this->dj('Kai') + ['slot_id' => $first->id])
            ->assertSessionHasErrors('booking');

        $this->assertSame(1, Booking::count(), 'The losing booking is rolled back.');
        $this->assertSame('Mira', $first->fresh()->booking->dj_name);
    }

    public function test_dj_lands_on_the_waitlist_when_the_night_is_full(): void
    {
        $night = Night::factory()->slots(1)->create();

        $this->post(route('bookings.store', $night), $this->dj('Mira'));
        $this->post(route('bookings.store', $night), $this->dj('Kai'));

        $kai = Booking::where('dj_name', 'Kai')->sole();
        $this->assertSame(BookingStatus::Waitlisted, $kai->status);
        $this->assertNull($kai->slot);
        $this->assertSame([$kai->id], $night->waitlist()->pluck('id')->all());
    }

    public function test_the_same_email_cannot_book_twice_for_one_night(): void
    {
        $night = Night::factory()->slots(3)->create();

        $this->post(route('bookings.store', $night), $this->dj('Mira'));
        $this->post(route('bookings.store', $night), ['dj_name' => 'Mira again', 'email' => 'MIRA@example.com'])
            ->assertSessionHasErrors('booking');

        $this->assertSame(1, Booking::count());
    }

    public function test_slots_of_another_night_are_rejected(): void
    {
        $night = Night::factory()->create();
        $otherNight = Night::factory()->create();

        $this->post(route('bookings.store', $night), $this->dj('Mira') + ['slot_id' => $otherNight->slots[0]->id])
            ->assertSessionHasErrors('slot_id');

        $this->assertSame(0, Booking::count());
    }

    public function test_past_nights_cannot_be_booked(): void
    {
        $night = Night::factory()->past()->create();

        $this->post(route('bookings.store', $night), $this->dj('Mira'))
            ->assertSessionHasErrors('booking');

        $this->assertSame(0, Booking::count());
    }

    public function test_unpublished_nights_are_hidden(): void
    {
        $night = Night::factory()->unpublished()->create();

        $this->get(route('nights.show', $night))->assertNotFound();
        $this->post(route('bookings.store', $night), $this->dj('Mira'))->assertNotFound();
    }

    public function test_input_is_validated(): void
    {
        $night = Night::factory()->create();

        $this->post(route('bookings.store', $night), ['dj_name' => '', 'email' => 'not-an-email', 'mix_url' => 'http://insecure.example'])
            ->assertSessionHasErrors(['dj_name', 'email', 'mix_url']);
    }

    public function test_the_public_pages_show_the_lineup(): void
    {
        $night = Night::factory()->slots(2)->create(['title' => 'Vinyl Night']);
        $this->post(route('bookings.store', $night), $this->dj('Mira'));

        $this->get(route('nights.index'))->assertOk()->assertSee('Vinyl Night')->assertSee('1 slot free');
        $this->get(route('nights.show', $night))->assertOk()->assertSee('Mira')->assertDontSee('mira@example.com');
    }

    /** @return array{dj_name: string, email: string, genre: string} */
    private function dj(string $name): array
    {
        return ['dj_name' => $name, 'email' => strtolower($name).'@example.com', 'genre' => 'House'];
    }
}
