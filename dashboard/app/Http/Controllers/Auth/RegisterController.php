<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use App\Models\Owner;
use App\Events\UserRegistered;
use App\Events\NewActivityEvent;
use App\Events\NotificationEvent;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use App\Providers\RouteServiceProvider;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;
use Throwable; // Pastikan menggunakan Throwable

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::DASHBOARD;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\Models\User
     */
    protected function create(array $data)
{
    // Memulai transaction
    DB::beginTransaction();

    try {
        // 1. Buat user baru
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => 'member'
        ]);

        $user->member()->create([
            'is_verify' => true,
            'user_id' => $user->id,
        ]);


        $admins = User::where('role', 'admin')->get();

        event(new NotificationEvent(
            recipients: $admins,
            title: '🆕 Pendaftaran Member Baru', // Judul notifikasi yang telah ditambahkan
            message: 'Seorang member baru bernama '.$user->name.' telah mendaftar!',
            url: route('admin.members.index'),
        ));

        DB::commit();

        // Mengembalikan instance user yang baru dibuat (sesuai yang diharapkan)
        return $user;

    } catch (Throwable $e) {
        DB::rollBack();

        // Cukup lempar kembali exception.
        // Traits RegistersUsers akan menangkapnya dan menangani redirect.
        throw $e;
    }
}
}
