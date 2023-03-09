<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\User;
use App\Services\Termii;

class SendPhoneNumberVerificationCode implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $user;
    public $message;
    public $termiiService;

    /**
     * Create a new job instance.
     */
    public function __construct(User $user, string $message)
    {
        $this->user = $user;
        $this->message = $message;
        $this->termiiService = new Termii();
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->termiiService->sendSMS($this->user->phone_number, $this->message);
    }
}
