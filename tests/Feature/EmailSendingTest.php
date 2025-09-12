<?php

namespace Tests\Feature;

use App\Mail\WelcomeMail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class EmailSendingTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_send_welcome_email()
    {
        Mail::fake();

        $user = \App\Models\User::factory()->create();

        Mail::to($user->email)->send(new WelcomeMail($user));

        Mail::assertSent(WelcomeMail::class, function ($mail) use ($user) {
            return $mail->hasTo($user->email);
        });
    }

    /** @test */
    public function can_send_notification_email()
    {
        Mail::fake();

        $user = \App\Models\User::factory()->create();

        $user->notify(new \App\Notifications\PriceAlertNotification);

        Mail::assertSent(\Illuminate\Notifications\Messages\MailMessage::class);
    }

    /** @test */
    public function email_has_correct_subject()
    {
        Mail::fake();

        $user = \App\Models\User::factory()->create();

        Mail::to($user->email)->send(new WelcomeMail($user));

        Mail::assertSent(WelcomeMail::class, function ($mail) {
            return $mail->subject === 'Welcome to Cobra';
        });
    }

    /** @test */
    public function email_contains_user_data()
    {
        Mail::fake();

        $user = \App\Models\User::factory()->create(['name' => 'John Doe']);

        Mail::to($user->email)->send(new WelcomeMail($user));

        Mail::assertSent(WelcomeMail::class, function ($mail) {
            return $mail->user->name === 'John Doe';
        });
    }

    /** @test */
    public function can_send_bulk_emails()
    {
        Mail::fake();

        $users = \App\Models\User::factory()->count(5)->create();

        foreach ($users as $user) {
            Mail::to($user->email)->send(new WelcomeMail($user));
        }

        Mail::assertSent(WelcomeMail::class, 5);
    }

    /** @test */
    public function email_queue_works()
    {
        Mail::fake();

        $user = \App\Models\User::factory()->create();

        Mail::to($user->email)->queue(new WelcomeMail($user));

        Mail::assertQueued(WelcomeMail::class);
    }
}
