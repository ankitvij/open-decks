<?php

namespace Tests\Feature;

use App\Actions\RequestSlot;
use App\Enums\BookingStatus;
use App\Mail\PromotedFromWaitlist;
use App\Models\Booking;
use App\Models\Night;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class CancellationTest extends TestCase
{
    use RefreshDatabase;

    private Night $night;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();

        // One slot, one confirmed DJ, two DJs waiting in order.
        $this->night = Night::factory()->slots(1)->create();
        foreach (['Mira', 'Kai', 'Lou'] as $name) {
            app(RequestSlot::class)->handle($this->night, ['dj_name' => $name, 'email' => strtolower($name).'@example.com']);
            $this->travel(1)->minutes(); // makes the waitlist order explicit
        }
    }

    public function test_the_signed_link_shows_a_confirmation_page(): void
    {
        $mira = $this->booking('Mira');

        $this->get($mira->cancelUrl())->assertOk()->assertSee('Cancel your booking?');
    }

    public function test_cancelling_frees_the_slot_for_the_first_dj_on_the_waitlist(): void
    {
        $mira = $this->booking('Mira');

        $this->post($mira->cancelActionUrl())->assertRedirect(route('nights.show', $this->night));

        $this->assertSame(BookingStatus::Cancelled, $mira->fresh()->status);
        $this->assertNotNull($mira->fresh()->cancelled_at);

        $kai = $this->booking('Kai');
        $this->assertSame(BookingStatus::Confirmed, $kai->status);
        $this->assertSame($this->night->slots[0]->id, $kai->slot->id);
        $this->assertSame(BookingStatus::Waitlisted, $this->booking('Lou')->status);

        Mail::assertSent(PromotedFromWaitlist::class, fn ($mail) => $mail->hasTo('kai@example.com'));
    }

    public function test_cancelling_a_waitlist_spot_does_not_promote_anyone(): void
    {
        $this->post($this->booking('Kai')->cancelActionUrl());

        $this->assertSame(BookingStatus::Confirmed, $this->booking('Mira')->status);
        $this->assertSame(BookingStatus::Waitlisted, $this->booking('Lou')->status);
        Mail::assertNotSent(PromotedFromWaitlist::class);
    }

    public function test_cancelling_twice_is_harmless(): void
    {
        $mira = $this->booking('Mira');

        $this->post($mira->cancelActionUrl());
        $this->post($mira->cancelActionUrl());

        $this->assertSame(BookingStatus::Confirmed, $this->booking('Kai')->status);
        $this->assertSame(BookingStatus::Waitlisted, $this->booking('Lou')->status);
        Mail::assertSent(PromotedFromWaitlist::class, 1);
    }

    public function test_unsigned_or_tampered_links_are_rejected(): void
    {
        $mira = $this->booking('Mira');
        $kai = $this->booking('Kai');

        $this->post(route('bookings.cancel.store', $mira))->assertForbidden();

        // Re-using Kai's signature for Mira's booking must fail.
        $tampered = str_replace("/bookings/{$kai->id}/", "/bookings/{$mira->id}/", $kai->cancelActionUrl());
        $this->post($tampered)->assertForbidden();

        $this->assertSame(BookingStatus::Confirmed, $mira->fresh()->status);
    }

    public function test_links_expire_when_the_night_starts(): void
    {
        $url = $this->booking('Mira')->cancelActionUrl();

        $this->travelTo($this->night->starts_at->copy()->addMinute());

        $this->post($url)->assertForbidden();
    }

    public function test_a_dj_can_rebook_after_cancelling(): void
    {
        $this->post($this->booking('Mira')->cancelActionUrl());

        app(RequestSlot::class)->handle($this->night, ['dj_name' => 'Mira', 'email' => 'mira@example.com']);

        $this->assertSame(2, Booking::where('email', 'mira@example.com')->count());
    }

    private function booking(string $name): Booking
    {
        return Booking::where('dj_name', $name)->latest('id')->firstOrFail();
    }
}
