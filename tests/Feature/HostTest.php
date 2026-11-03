<?php

namespace Tests\Feature;

use App\Actions\CreateNight;
use App\Actions\RequestSlot;
use App\Enums\BookingStatus;
use App\Mail\PromotedFromWaitlist;
use App\Models\Booking;
use App\Models\Night;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class HostTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get(route('host.nights.index'))->assertRedirect(route('login'));
        $this->post(route('host.nights.store'))->assertRedirect(route('login'));
    }

    public function test_a_host_can_log_in_and_out(): void
    {
        $host = User::factory()->create(['email' => 'host@example.com']);

        $this->post(route('login'), ['email' => 'host@example.com', 'password' => 'wrong'])
            ->assertSessionHasErrors('email');
        $this->assertGuest();

        $this->post(route('login'), ['email' => 'host@example.com', 'password' => 'password'])
            ->assertRedirect(route('host.nights.index'));
        $this->assertAuthenticatedAs($host);

        $this->post(route('logout'))->assertRedirect(route('nights.index'));
        $this->assertGuest();
    }

    public function test_creating_a_night_generates_back_to_back_slots(): void
    {
        $host = User::factory()->create();
        $startsAt = now()->addWeek()->setTime(20, 0);

        $this->actingAs($host)->post(route('host.nights.store'), [
            'title' => 'Wednesday Open Decks',
            'venue' => 'Sensorium, Berlin',
            'starts_at' => $startsAt->format('Y-m-d\TH:i'),
            'slot_minutes' => 45,
            'slot_count' => 4,
        ])->assertRedirect();

        $night = Night::sole();
        $this->assertSame($host->id, $night->host_id);
        $this->assertSame(
            ['20:00', '20:45', '21:30', '22:15'],
            $night->slots->map(fn ($slot) => $slot->starts_at->format('H:i'))->all(),
        );
        $this->assertSame('23:00', $night->endsAt()->format('H:i'));
    }

    public function test_a_freshly_created_night_is_open_for_bookings(): void
    {
        Mail::fake();
        $night = app(CreateNight::class)->handle(User::factory()->create(), [
            'title' => 'Fresh Night',
            'venue' => 'Sensorium, Berlin',
            'starts_at' => now()->addDay()->toDateTimeString(),
            'slot_minutes' => 30,
            'slot_count' => 2,
        ]);

        $booking = app(RequestSlot::class)->handle($night, ['dj_name' => 'Mira', 'email' => 'mira@example.com']);

        $this->assertSame(BookingStatus::Confirmed, $booking->status);
    }

    public function test_night_input_is_validated(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('host.nights.store'), [
                'title' => '',
                'starts_at' => now()->subDay()->toDateTimeString(),
                'slot_minutes' => 5,
                'slot_count' => 50,
            ])
            ->assertSessionHasErrors(['title', 'venue', 'starts_at', 'slot_minutes', 'slot_count']);

        $this->assertSame(0, Night::count());
    }

    public function test_hosts_only_see_their_own_nights(): void
    {
        $night = Night::factory()->create(['title' => 'Mine']);
        $other = Night::factory()->create(['title' => 'Someone else']);

        $this->actingAs($night->host)
            ->get(route('host.nights.index'))
            ->assertSee('Mine')
            ->assertDontSee('Someone else');

        $this->actingAs($night->host)->get(route('host.nights.show', $other))->assertForbidden();
    }

    public function test_the_host_sees_contact_details_and_the_waitlist(): void
    {
        Mail::fake();
        $night = Night::factory()->slots(1)->create();
        app(RequestSlot::class)->handle($night, ['dj_name' => 'Mira', 'email' => 'mira@example.com']);
        app(RequestSlot::class)->handle($night, ['dj_name' => 'Kai', 'email' => 'kai@example.com']);

        $this->actingAs($night->host)
            ->get(route('host.nights.show', $night))
            ->assertOk()
            ->assertSeeInOrder(['Mira', 'mira@example.com', 'Waitlist', 'Kai', 'kai@example.com']);
    }

    public function test_removing_a_dj_moves_the_waitlist_up(): void
    {
        Mail::fake();
        $night = Night::factory()->slots(1)->create();
        $mira = app(RequestSlot::class)->handle($night, ['dj_name' => 'Mira', 'email' => 'mira@example.com']);
        $kai = app(RequestSlot::class)->handle($night, ['dj_name' => 'Kai', 'email' => 'kai@example.com']);

        $this->actingAs($night->host)
            ->delete(route('host.nights.bookings.destroy', [$night, $mira]))
            ->assertRedirect(route('host.nights.show', $night))
            ->assertSessionHas('status', 'Removed Mira. Kai moved up from the waitlist and was notified.');

        $this->assertSame(BookingStatus::Cancelled, $mira->fresh()->status);
        $this->assertSame(BookingStatus::Confirmed, $kai->fresh()->status);
        Mail::assertSent(PromotedFromWaitlist::class);
    }

    public function test_hosts_cannot_remove_bookings_from_other_hosts_nights(): void
    {
        Mail::fake();
        $night = Night::factory()->create();
        $intruder = User::factory()->create();
        $booking = app(RequestSlot::class)->handle($night, ['dj_name' => 'Mira', 'email' => 'mira@example.com']);

        $this->actingAs($intruder)
            ->delete(route('host.nights.bookings.destroy', [$night, $booking]))
            ->assertForbidden();

        $this->assertSame(BookingStatus::Confirmed, $booking->fresh()->status);
    }

    public function test_a_booking_must_belong_to_the_night_in_the_url(): void
    {
        $night = Night::factory()->create();
        $foreign = Booking::factory()->create();

        $this->actingAs($night->host)
            ->delete(route('host.nights.bookings.destroy', [$night, $foreign]))
            ->assertNotFound();
    }
}
