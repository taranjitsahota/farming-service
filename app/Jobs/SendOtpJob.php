<?php 

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\Otp\SendOtp;
use Exception;
use Illuminate\Support\Facades\Log;

class SendOtpJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $contact;
    protected $otp;
    protected $contactType;

    public function __construct($contact, $otp, $contactType)
    {
        $this->contact = $contact;
        $this->otp = $otp;
        $this->contactType = $contactType;
    }

    public $tries = 3; // Retry 3 times if failed
    public $timeout = 60; // Job timeout 60 seconds

    public function handle()
    {
        try{
        if ($this->contactType === 'phone') {
            log::info('phone job start');
            SendOtp::sendOtpPhone($this->contact, $this->otp);
        } else {
            SendOtp::sendOtpMail($this->contact, $this->otp);
            Log::info('email job started');
        }
    }catch(Exception $e){
        Log::error('OTP Job Failed: ' . $e->getMessage());
            throw $e;
    }
    }
}
