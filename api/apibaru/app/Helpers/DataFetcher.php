<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Owner;
use App\Models\Outlet;
use App\Models\Addon;
use App\Models\Device;
use App\Models\Transaction;
use InvalidArgumentException;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class DataFetcher
{
    protected $rootEntity;
    protected $currentUser;
    protected $isAdminConfig = false;

    public function __construct()
    {
        // 1. Deteksi Guard yang sedang aktif
        if (Auth::guard('admin_config')->check()) {
            $this->currentUser = Auth::guard('admin_config')->user();
            $this->isAdminConfig = true;
        } else {
            $this->currentUser = Auth::guard('web')->user();
            $this->isAdminConfig = false;
        }

        $this->rootEntity = $this->resolveRootEntity();
    }

    public static function forCurrentUser(): static
    {
        return new static();
    }

    /**
     * Memecahkan entitas root (Owner untuk owner, Outlet untuk kasir).
     * Jika admin_config, rootEntity bisa null karena admin menguasai semua data.
     */
    protected function resolveRootEntity()
    {
        if (!$this->currentUser) {
            return null;
        }

        // Jika ini adalah admin dari config, dia tidak terikat ke outlet/owner tertentu
        if ($this->isAdminConfig) {
            return null;
        }

        // Logic untuk guard 'web' (Owner & Cashier)
        if ($this->currentUser->role === 'owner') {
            return $this->currentUser->owner;
        } elseif ($this->currentUser->role === 'cashier') {
            $cashier = $this->currentUser->cashier;
            return $cashier ? $cashier->outlet : null;
        }

        return null;
    }

    /**
     * Magic method untuk handle ->devices, ->transactions, dll.
     */
    public function __get(string $name): Builder
    {
        // 1. LOGIKA UNTUK ADMIN CONFIG (BISA LIHAT SEMUA)
        if ($this->isAdminConfig) {
            $modelClass = $this->getModelClassFromRelationName($name);
            if ($modelClass) {
                return $modelClass::query();
            }
        }

        // 2. LOGIKA UNTUK OUTLET/CASHIER (Data terbatas pada ID Outlet)
        if ($this->rootEntity instanceof Outlet && $name === 'outlets') {
            return Outlet::query()->where('id', $this->rootEntity->id);
        }

        // 3. LOGIKA RELASI DINAMIS (Untuk Owner/Outlet)
        if ($this->rootEntity && method_exists($this->rootEntity, $name)) {
            $relation = $this->rootEntity->$name();

            if (
                $relation instanceof \Illuminate\Database\Eloquent\Relations\HasMany ||
                $relation instanceof \Illuminate\Database\Eloquent\Relations\HasManyThrough
            ) {
                return $relation->getQuery();
            }
        }

        // 4. FALLBACK: Jika tidak ada akses, kembalikan query kosong agar tidak error
        $fallbackModel = $this->getModelClassFromRelationName($name) ?? User::class;
        return $fallbackModel::query()->whereRaw('1 = 0');
    }

    protected function getModelClassFromRelationName(string $relationName): ?string
    {
        $customMapping = [
            'devices'      => Device::class,
            'transactions' => Transaction::class,
            'outlets'      => Outlet::class,
            'addons'       => Addon::class,
            'owners'       => Owner::class,
            'users'        => User::class,
        ];

        if (isset($customMapping[$relationName])) {
            return $customMapping[$relationName];
        }

        $singularName = Str::singular($relationName);
        $modelClass = "App\\Models\\" . Str::studly($singularName);

        if (class_exists($modelClass)) {
            return $modelClass;
        }

        return null;
    }

    public function getBrand(): ?Owner
    {
        if ($this->rootEntity instanceof Owner) {
            return $this->rootEntity;
        } elseif ($this->rootEntity instanceof Outlet) {
            return $this->rootEntity->owner;
        }
        return null;
    }

    public function getOutlet(): ?Outlet
    {
        return ($this->rootEntity instanceof Outlet) ? $this->rootEntity : null;
    }

    /**
     * Update sistem izin (can) untuk mendukung admin_config
     */
    public function can(string $ability): bool
    {
        // 1. Admin Config punya akses ke SEMUA hal (Super Admin)
        if ($this->isAdminConfig) {
            return true;
        }

        // 2. Cek apakah user biasa login
        if (!$this->currentUser) {
            return false;
        }

        // 3. Cek apakah ability terdaftar
        if (!isset($this->permissions[$ability])) {
            return false;
        }

        $allowedRoles = $this->permissions[$ability];

        // 4. Cek role user (web guard)
        return in_array($this->currentUser->role, $allowedRoles);
    }

    // Daftar permissions tetap sama seperti sebelumnya...
   protected array $permissions = [
        // Device
        'partner.device_list'                 => ['owner', 'cashier'],
        'partner.device.store'                => ['owner'],
        'partner.device.update'               => ['owner'],
        'partner.device.destroy'              => ['owner'],
        'partner.device.update_status'        => ['owner'],
        'partner.device.service_types.update' => ['owner'],

        // Outlet
        'partner.outlets.list'                 => ['owner'],
        'partner.outlets.detail'               => ['owner'],
        'partner.outlets.services.update'     => ['owner'],
        'partner.outlets.update'              => ['owner'],
        'partner.outlets.patch_update'        => ['owner'],
        'partner.outlets.destroy'             => ['owner'],
        'partner.outlets.update-status'       => ['owner'],

        // Addons
        'partner.addons.index'                => ['owner', 'cashier'],
        'partner.addons.create'               => ['owner'],
        'partner.addons.store'                => ['owner'],
        'partner.addons.edit'                 => ['owner'],
        'partner.addons.update'               => ['owner'],
        'partner.addons.destroy'              => ['owner'],

        // Cashier Payment
        'partner.cashier.payment.create'      => ['owner', 'cashier'],
        // 'partner.cashier.payment.store'       => ['owner', 'cashier'],

        // Service Order
        'partner.service-order.list'          => ['owner', 'cashier'],
        // 'partner.service-order.detail'        => ['owner', 'cashier'],
        // 'partner.service-orders.activate-device' => ['owner', 'cashier'],

        // // Dashboard
        // 'partner.dashboard'                   => ['owner', 'cashier'],

        // // Member
        // 'partner.members.verified'            => ['owner', 'cashier'],
        // 'partner.members.unverified'          => ['owner', 'cashier'],
        'partner.members.verify'              => ['owner', 'cashier'],
        'partner.members.subscription.destroy' => ['owner', 'cashier'],

        'partner.bypass.logs'                 => ['owner', 'cashier'],

        // // Bypass Log

        // // Transaction
        'partner.transactions.index'          => ['owner', 'cashier'],
        'partner.manual.transactions'         => ['owner', 'cashier'],
        'partner.qris.transactions'           => ['owner', 'cashier'],
        'partner.member.transactions'         => ['owner', 'cashier'],


        // // Cashiers
        // 'partner.cashiers.index'              => ['owner', 'cashier'],
        // 'partner.cashiers.create'             => ['owner', 'cashier'],
        // 'partner.cashiers.store'              => ['owner', 'cashier'],
        // 'partner.cashiers.edit'               => ['owner', 'cashier'],
        // 'partner.cashiers.update'             => ['owner', 'cashier'],
        // 'partner.cashiers.destroy'            => ['owner', 'cashier'],

        // // Receipt Config
        // 'partner.receipt.config.edit'         => ['owner', 'cashier'],
        // 'partner.receipt.config.update'       => ['owner', 'cashier'],


        // // Topup
        // 'partner.topup'                       => ['owner', 'cashier'],
        // 'partner.topup.histories'             => ['owner', 'cashier'],
        // 'partner.topup.store'                 => ['owner', 'cashier'],

        'withdrawal.request'                       => ['owner'],
        'withdrawal.histories'                      => ['owner'],


        // // Member Payment
        // 'partner.member.payment.create'       => ['owner', 'cashier'],
        // 'partner.member.payment.store'        => ['owner', 'cashier'],

    ];

}
