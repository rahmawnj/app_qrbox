<?php

namespace App\Http\Controllers\Landing;

use App\Models\Owner;
use App\Models\Member;
use App\Models\Outlet;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use App\Events\NotificationEvent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use App\Events\MemberSubscribedToPartner;
use Illuminate\Pagination\LengthAwarePaginator;

class HomeController extends Controller
{
    private function haversineDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371; // Radius bumi dalam kilometer

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        $distance = $earthRadius * $c;

        return $distance;
    }

    public function index(Request $request)
    {
        // Hanya ambil brand yang statusnya aktif
        $brands = Owner::where('status', 1)->get();

        // Cek apakah permintaan adalah AJAX
        if ($request->ajax()) {
            $userLat = $request->input('lat');
            $userLon = $request->input('lon');
            $maxDistance = 50;

            if ($userLat && $userLon) {
                // Jika koordinat pengguna ada, cari outlet terdekat dengan status 1 dan owner status 1
                $outlets = Outlet::with('owner')
                    ->where('status', 1)
                    ->whereHas('owner', function ($query) {
                        $query->where('status', 1);
                    })
                    ->get()
                    ->filter(function ($outlet) use ($userLat, $userLon, $maxDistance) {
                        $outletCoords = json_decode($outlet->latlong, true); // Mendekode string JSON

                        if (!isset($outletCoords['lat']) || !isset($outletCoords['lon'])) {
                            return false; // Skip outlet tanpa data lokasi
                        }
                        $distance = $this->haversineDistance($userLat, $userLon, $outletCoords['lat'], $outletCoords['lon']);
                        return $distance <= $maxDistance;
                    })
                    ->take(9);
            } else {
                // Jika tidak ada koordinat, ambil 9 outlet aktif secara acak dari owner yang aktif
                $outlets = Outlet::with('owner')
                    ->where('status', 1)
                    ->whereHas('owner', function ($query) {
                        $query->where('status', 1);
                    })
                    ->inRandomOrder()
                    ->take(9)
                    ->get();
            }

            return view('landing.home_outlets', compact('outlets'));
        }

        // Jika bukan AJAX, kembalikan view utama (full page)
        return view('landing.home', compact('brands'));
    }

   // LandingHomeController.php
public function brand(Request $request, Owner $brand)
{
    // Jika brand tidak aktif, arahkan ke 404 atau halaman lain
    if ($brand->status != 1) {
        abort(404);
    }

    // Jika ini adalah permintaan AJAX untuk data outlet terdekat
    if ($request->ajax()) {
        $userLat = $request->input('lat');
        $userLon = $request->input('lon');
        $outletsWithDistance = [];

        if ($userLat && $userLon) {
            // Filter outlet dengan status 1 sebelum looping
            $activeOutlets = $brand->outlets->where('status', 1);

            foreach ($activeOutlets as $outlet) {
                if ($outlet->latlong) {
                    $outletLocation = json_decode($outlet->latlong);
                    $distance = $this->haversineDistance($userLat, $userLon, $outletLocation->lat, $outletLocation->lon);

                    $outletsWithDistance[] = [
                        'id' => $outlet->id,
                        'name' => $outlet->outlet_name,
                        'address' => $outlet->address,
                        'distance' => round($distance, 2),
                        'lat' => $outletLocation->lat,
                        'lon' => $outletLocation->lon,
                    ];
                }
            }
        }
        Log::info(''. $userLat .','. $userLon, $outletsWithDistance);

        return response()->json($outletsWithDistance);
    }

    // Jika ini adalah permintaan halaman biasa
    return view('landing.brand', compact('brand'));
}

    public function brands(Request $request)
    {
        // Jika ini adalah permintaan AJAX untuk data brand terdekat
        if ($request->ajax()) {
            $userLat = $request->input('lat');
            $userLon = $request->input('lon');
            $brandsWithOutlets = [];

            if ($userLat && $userLon) {
                // Eager load hanya outlet yang aktif (status = 1) dari brand yang aktif (status = 1)
                $brands = Owner::where('status', 1)->with(['outlets' => function ($query) {
                    $query->where('status', 1);
                }])->get();

                foreach ($brands as $brand) {
                    $closestOutlet = null;
                    $minDistance = PHP_FLOAT_MAX;

                    foreach ($brand->outlets as $outlet) {
                        if ($outlet->latlong) {
                            $outletLocation = json_decode($outlet->latlong);
                            $distance = $this->haversineDistance($userLat, $userLon, $outletLocation->lat, $outletLocation->lon);

                            if ($distance < $minDistance) {
                                $minDistance = $distance;
                                $closestOutlet = $outlet;
                            }
                        }
                    }

                    if ($closestOutlet) {
                        $brandsWithOutlets[] = [
                            'brand_id' => $brand->id,
                            'brand_name' => $brand->brand_name,
                            'closest_distance' => round($minDistance, 2),
                            'closest_outlet_code' => $closestOutlet->code,
                        ];
                    }
                }
            }

            return response()->json($brandsWithOutlets);
        }

        // Jika ini adalah permintaan halaman biasa, ambil hanya brand yang aktif
        $brands = Owner::where('status', 1)->with('outlets')->get();
        return view('landing.brands', compact('brands'));
    }

    private function geocodeAddress($address)
    {
        if (empty(trim($address))) {
            return null;
        }

        $encodedAddress = urlencode($address);
        $nominatimUrl = "https://nominatim.openstreetmap.org/search?q={$encodedAddress}&format=json&limit=1";
        $options = [
            'http' => [
                'method' => "GET",
                'header' => "User-Agent: LaundryApp/1.0 (your-email@example.com)\r\n" // Ganti dengan info aplikasi Anda
            ]
        ];
        $context = stream_context_create($options);

        // Gunakan @ untuk menyembunyikan warning jika file_get_contents gagal,
        // dan periksa hasilnya.
        $response = @file_get_contents($nominatimUrl, false, $context);

        if ($response === false) {
            Log::error('Nominatim Geocoding Failed: Could not fetch data for address: ' . $address);
            return null;
        }

        $data = json_decode($response);

        if ($data && is_array($data) && count($data) > 0) {
            $firstResult = $data[0];
            return [
                'latitude' => (float) $firstResult->lat,
                'longitude' => (float) $firstResult->lon,
            ];
        }
        Log::warning('Nominatim Geocoding: No results found for address: ' . $address);
        return null;
    }

    private function geocodeLocation($locationName)
    {
        $response = Http::withHeaders([
            'User-Agent' => 'LaundryApp/1.0 (contact@your-domain.com)',
        ])->get('https://nominatim.openstreetmap.org/search', [
            'q' => $locationName,
            'format' => 'json',
            'limit' => 1,
            'countrycodes' => 'id'
        ]);

        if ($response->successful() && !empty($response->json())) {
            $data = $response->json()[0];
            return [
                'lat' => (float) $data['lat'],
                'lon' => (float) $data['lon'],
            ];
        }

        return null;
    }

    public function outlets(Request $request)
    {
        // Tambahkan kondisi whereHas untuk memastikan owner juga aktif
        $query = Outlet::with('owner')->where('status', 1)->whereHas('owner', function ($q) {
            $q->where('status', 1);
        });

        $userLat = $request->input('lat');
        $userLon = $request->input('lon');
        $locationSearch = $request->input('location_search');
        $searchQuery = $request->input('search_query');
        $perPage = 10;
        $currentPage = $request->input('page', 1);

        // Geocoding: jika pengguna mencari dengan nama lokasi
        if ($locationSearch && !$userLat && !$userLon) {
            $coords = $this->geocodeLocation($locationSearch);
            if ($coords) {
                $userLat = $coords['lat'];
                $userLon = $coords['lon'];
            }
        }

        if ($userLat && $userLon) {
            $maxDistance = 50; // Radius 50 km
            $outletsCollection = Outlet::with('owner')
                ->where('status', 1)
                ->whereHas('owner', function ($query) {
                    $query->where('status', 1);
                })
                ->get()
                ->filter(function ($outlet) use ($userLat, $userLon, $maxDistance) {
                    $outletCoords = json_decode($outlet->latlong, true);
                    if (!isset($outletCoords['lat']) || !isset($outletCoords['lon'])) {
                        return false;
                    }
                    $distance = $this->haversineDistance($userLat, $userLon, $outletCoords['lat'], $outletCoords['lon']);
                    $outlet->distance = $distance; // Tambahkan jarak ke objek outlet
                    return $distance <= $maxDistance;
                })
                ->sortBy('distance');

            // Lakukan paginasi secara manual pada koleksi
            $outlets = new LengthAwarePaginator(
                $outletsCollection->forPage($currentPage, $perPage),
                $outletsCollection->count(),
                $perPage,
                $currentPage,
                ['path' => route('home.outlets')]
            );
        } elseif ($searchQuery) {
            $outlets = $query->where('outlet_name', 'like', '%' . $searchQuery . '%')
                             ->orWhereHas('owner', function ($q) use ($searchQuery) {
                                 $q->where('brand_name', 'like', '%' . $searchQuery . '%');
                             })
                             ->paginate($perPage);
        } else {
            // Default: ambil outlet secara acak jika tidak ada filter
            $outlets = $query->inRandomOrder()->paginate($perPage);
        }

        Log::info($outlets);

        if ($request->ajax()) {
            return response()->json([
                'html' => view('landing._outlets-outlet', compact('outlets'))->render(),
                'last_page' => $outlets->lastPage(),
                'current_page' => $outlets->currentPage(),
                'total' => $outlets->total(),
            ]);
        }

        // Jika bukan AJAX, kembalikan view utama
        return view('landing.outlets', compact('outlets'));
    }

    public function registerMemberForBrand(Request $request, Owner $brand)
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Anda harus login untuk mendaftar sebagai member.');
        }

        $user = Auth::user();

        DB::beginTransaction(); // Memulai transaksi database

        try {
            $member = $user->member;

            if ($member->owners()->where('owner_id', $brand->id)->exists()) {
                DB::rollBack();
                return redirect()->back()->with('error', 'Anda sudah terdaftar sebagai member untuk brand ini.');
            }

            $member->owners()->attach($brand->id);

            $cashierUsers = $brand->cashiers->map(fn($cashier) => $cashier->user);
            $ownerAndCashiers = $cashierUsers->push($brand->user);
            event(new NotificationEvent(
                recipients: $ownerAndCashiers,
                title: '📝 Pendaftaran Member Baru', // Tambahkan title dan emoji
                message: 'Member baru ' . $user->name . ' telah mendaftar. Mohon verifikasi pendaftaran mereka.',
                url: route('partner.members.unverified'),
            ));


            DB::commit();

            return redirect()->back()->with('success', 'Pendaftaran member untuk brand ' . $brand->brand_name . ' berhasil! Menunggu verifikasi admin.');
        } catch (\Exception $e) {
            DB::rollBack(); // Batalkan transaksi jika ada error
            Log::error('Pendaftaran member untuk brand gagal: ' . $e->getMessage(), [
                'user_id' => $user->id ?? 'N/A',
                'brand_id' => $brand->id,
                'exception_trace' => $e->getTraceAsString(),
            ]);
            return redirect()->back()->with('error', 'Pendaftaran member gagal: ' . $e->getMessage());
        }
    }
}
