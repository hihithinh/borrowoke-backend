<?php

namespace App\Traits;

use App\Constant\SessionKey;
use App\Enums\ECustomerType;
use App\Enums\EStatus;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Lang;

trait RedirectCustomer {
	public function getRedirectRoute($user, $defaultRedirectRoute = null) {
		$fromRoute = request('fromRoute', 'home');
		$redirectRoute = $defaultRedirectRoute;
		if (empty($user)) {
			return $redirectRoute ?? route('home', [], false);
		}
		switch ($user->customer_type) {
			case ECustomerType::SELLER:
				if ($user->status !== EStatus::ACTIVE) {
					$redirectRoute = route('home.seller', [], false);
					break;
				}
				if (in_array($fromRoute, ['post.detail'])) {
					// Do nothing
				} elseif (in_array($fromRoute, ['home', 'home.seller', 'home.buyer', 'home.advertiser', 'post.list']) || !session(SessionKey::IS_SELLER_ROUTE)) {
					$redirectRoute = route('seller', [], false);
				}
				break;
			case ECustomerType::BUYER:
				if ($user->status !== EStatus::ACTIVE) {
					$redirectRoute = route('home.buyer', [], false);
					break;
				}
				if (in_array($fromRoute, ['post.detail'])) {
					// Do nothing
				} elseif (in_array($fromRoute, ['home', 'home.seller', 'home.buyer', 'home.advertiser'])
					|| (!session(SessionKey::IS_BUYER_ROUTE) && !in_array($fromRoute, ['buyer', 'post.detail']))) {
					$redirectRoute = route('buyer', [], false);
				}
				break;
			case ECustomerType::ADVERTISER:
				if ($user->status !== EStatus::ACTIVE) {
					$redirectRoute = route('home.advertiser', [], false);
					break;
				}
				if (in_array($fromRoute, ['home', 'home.seller', 'home.buyer', 'home.advertiser'])
					|| (!session(SessionKey::IS_ADVERTISER_ROUTE) && !in_array($fromRoute, ['buyer', 'post.detail']))) {
					$redirectRoute = route('ads.list', [], false);
				}
				break;
		}
		return $redirectRoute;
	}

	public function getSimpleRedirectRoute() {
		$redirectRoute = route('home', [], false);
		if (!auth()->check()) {
			return $redirectRoute;
		}
		switch (auth()->user()->customer_type) {
			case ECustomerType::SELLER:
				if (empty(auth()->user()->post)) {
					$redirectRoute = route('post.create.form', [], false);
				} else {
					$redirectRoute = route('home.seller', [], false);
				}
				break;
			case ECustomerType::BUYER:
				$redirectRoute = route('home.buyer', [], false);
				break;
			case ECustomerType::ADVERTISER:
				$redirectRoute = route('home.advertiser', [], false);
				break;
		}
		return $redirectRoute;
	}
}