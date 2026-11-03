<?php

namespace Tests\Feature;

use App\Actions\RequestSlot;
use App\Mail\BookingReceived;
use App\Mail\PromotedFromWaitlist;
use App\Models\Night;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class MailTest extends TestCase
{
    use RefreshDatabase;

    public function test_confirmation_mail_contains_the_slot_and_a_working_cancel_link(): void
    {
        Mail::fake();
        $night = Night::factory()->slots(1)->create(['title' => 'Vinyl Night']);
        $booking = app(RequestSlot::class)->handle($night, ['dj_name' => 'Mira', 'email' => 'mira@example.com']);

        $mail = new BookingReceived($booking);

        $mail->assertHasSubject("You're playing at Vinyl Night");
        $mail->assertSeeInHtml('Mira');
        $mail->assertSeeInHtml($night->starts_at->format('H:i'));
        $mail->assertSeeInHtml('signature=', false);
    }

    public function test_waitlist_mail_explains_what_happens_next(): void
    {
        Mail::fake();
        $night = Night::factory()->slots(1)->create(['title' => 'Vinyl Night']);
        app(RequestSlot::class)->handle($night, ['dj_name' => 'Mira', 'email' => 'mira@example.com']);
        $kai = app(RequestSlot::class)->handle($night, ['dj_name' => 'Kai', 'email' => 'kai@example.com']);

        $mail = new BookingReceived($kai);

        $mail->assertHasSubject("You're on the waitlist for Vinyl Night");
        $mail->assertSeeInText('on the waitlist');
    }

    public function test_promotion_mail_names_the_new_slot(): void
    {
        Mail::fake();
        $night = Night::factory()->slots(1)->create(['title' => 'Vinyl Night']);
        $booking = app(RequestSlot::class)->handle($night, ['dj_name' => 'Kai', 'email' => 'kai@example.com']);

        $mail = new PromotedFromWaitlist($booking);

        $mail->assertHasSubject("A slot opened up: you're playing at Vinyl Night");
        $mail->assertSeeInText($night->starts_at->format('H:i'));
    }
}
