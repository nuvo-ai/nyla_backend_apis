<?php

namespace App\Providers;

use App\Models\Cart;
use App\Models\User;
use App\Policies\CartPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use App\Models\Sanctum\PersonalAccessToken;
use Laravel\Sanctum\Sanctum;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {

        Sanctum::usePersonalAccessTokenModel(PersonalAccessToken::class);
        //
        // Gate::policy(Cart::class, CartPolicy::class);
        // only the owner of the cart can view, update or delete it
        // Gate::define('view-cart', function (User $user, Cart $cart) {
        //     return $user->id === $cart->user_id;
        // });
        // Gate::define('update-cart', function (User $user, Cart $cart) {
        //     return $user->id === $cart->user_id;
        // });
        // Gate::define('delete-cart', function (User $user, Cart $cart) {
        //     return $user->id === $cart->user_id;
        // });
        // Gate::define('create-cart', function (User $user) {
        //     return $user->id !== null;
        // });
    }
}
