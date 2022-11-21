<?php

namespace App\Traits;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Lang;

trait EmailSettings {
	public function setVerifyEmailTemplate($optCodeService) {
		// $notifiable = $user
		VerifyEmail::toMailUsing(function ($notifiable, $verificationUrl) use ($optCodeService) {
			$otp_code = $optCodeService->removeOldOTPAndGenerateANewOne($notifiable->getKey());

			return (new MailMessage)->subject(__('front/email.verify-email.subject'))->view(
				'email-template.email-verification',
				[
					'action' => $verificationUrl,
					'otp_code' => $otp_code,
					'locale' => $notifiable->language_code ?? config('app.locale'),
					'userId' => $notifiable->code,
					'userName' => $notifiable->name,
				]
			);
		});
	}
}