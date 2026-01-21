<?php

namespace App\Providers;
use App\Models\Outlet;
use Illuminate\Support\Str;

class OutletService
{
    /**
     * Generate format token yang unik dan mudah dibaca
     */
    public function generateTokenFormat(): string
    {
        return 'TKN-' . strtoupper(Str::random(12));
    }


    public function syncToken(Outlet $outlet, ?string $manualToken = null): Outlet
    {
        $newToken = $manualToken ?? $this->generateTokenFormat();

        $outlet->update([
            'device_token' => $newToken
        ]);

        return $outlet;
    }
}
