<?php

namespace Tests\Feature;

use App\Http\Middleware\EnsureSubscriptionIsActive;
use App\Models\Abonnement;
use App\Models\Boutique;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class SubscriptionMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_with_expired_subscription_is_blocked(): void
    {
        $boutique = Boutique::factory()->create();

        $user = User::factory()->create([
            'role' => 'employe',
            'subscription_status' => Abonnement::STATUT_EXPIRE,
            'subscription_expires_at' => now()->subDay(),
            'boutique_id' => $boutique->id,
        ]);

        Abonnement::factory()->create([
            'user_id' => $user->id,
            'type_abonnement' => Abonnement::TYPE_MENSUEL,
            'date_debut' => Carbon::now()->subMonth(),
            'date_expiration' => Carbon::now()->subDay(),
            'statut' => Abonnement::STATUT_EXPIRE,
        ]);

        $this->be($user->fresh());

        $middleware = new EnsureSubscriptionIsActive();

        $request = Request::create('/test', 'GET');
        $request->setUserResolver(fn () => $user->fresh());

        try {
            $middleware->handle($request, fn () => response('ok'));
            $this->fail('Le middleware aurait dû bloquer l’accès.');
        } catch (HttpException $exception) {
            $this->assertEquals(402, $exception->getStatusCode());
        }
    }

    public function test_user_with_active_subscription_can_access(): void
    {
        $boutique = Boutique::factory()->create();

        $user = User::factory()->create([
            'role' => 'employe',
            'subscription_status' => Abonnement::STATUT_ACTIF,
            'subscription_expires_at' => now()->addMonth(),
            'boutique_id' => $boutique->id,
        ]);

        Abonnement::factory()->create([
            'user_id' => $user->id,
            'type_abonnement' => Abonnement::TYPE_MENSUEL,
            'date_debut' => Carbon::now()->subDay(),
            'date_expiration' => Carbon::now()->addMonth(),
            'statut' => Abonnement::STATUT_ACTIF,
        ]);

        $this->be($user->fresh());

        $middleware = new EnsureSubscriptionIsActive();

        $request = Request::create('/test', 'GET');
        $request->setUserResolver(fn () => $user->fresh());

        $response = $middleware->handle($request, fn () => response('ok'));

        $this->assertEquals(200, $response->status());
    }
}
