<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PDO;
use PDOException;

class CustomerServiceController extends Controller
{
    public function index(Request $request)
    {
        // Ganti dengan API Key Mistral-mu
        $apiKey = 'Oh3qid6ZYAGmHntC143OYOLx5vJyu96Z';

        // === BAGIAN YANG DIPERBARUI: Mengambil data dari SQL Database dengan hierarki ===

        // Konfigurasi Database - Disesuaikan dengan file superapp_smartdevice.sql
        $dbHost = 'localhost';
        $dbName = 'superapp_smartdevice';
        $dbUser = 'root';
        $dbPass = '';

        $productData = "";

        try {
            $pdo = new PDO("mysql:host=$dbHost;dbname=$dbName", $dbUser, $dbPass);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Mengambil semua data dari tabel yang relevan
            $owners_stmt = $pdo->query("SELECT * FROM owners");
            $owners = $owners_stmt->fetchAll(PDO::FETCH_ASSOC);

            $outlets_stmt = $pdo->query("SELECT * FROM outlets");
            $outlets = $outlets_stmt->fetchAll(PDO::FETCH_ASSOC);

            $services_stmt = $pdo->query("SELECT * FROM services");
            $services = $services_stmt->fetchAll(PDO::FETCH_ASSOC);

            $addons_stmt = $pdo->query("SELECT * FROM addons");
            $addons = $addons_stmt->fetchAll(PDO::FETCH_ASSOC);

            // Membuat string data produk dengan hierarki
            $productData .= "# Informasi Bisnis: SaaS Laundry\n\n";

            foreach ($owners as $owner) {
                $productData .= "## Brand (Owner): {$owner['brand_name']}\n";
                $productData .= "- Brand ini memiliki outlet-outlet berikut:\n\n";

                // Filter outlets untuk owner saat ini
                $ownerOutlets = array_filter($outlets, function($outlet) use ($owner) {
                    return $outlet['owner_id'] == $owner['id'];
                });

                foreach ($ownerOutlets as $outlet) {
                    $productData .= "### Outlet: {$outlet['outlet_name']}\n";
                    $productData .= "- Outlet Address: {$outlet['address']}\n";
                    $productData .= "- Outlet Lat Long: {$outlet['latlong']}\n";
                    $productData .= "- Outlet ini menawarkan layanan-layanan berikut:\n\n";

                    // Filter services untuk outlet saat ini
                    $outletServices = array_filter($services, function($service) use ($outlet) {
                        return $service['outlet_id'] == $outlet['id'];
                    });

                    foreach ($outletServices as $service) {
                        $productData .= "#### Layanan: {$service['name']}\n";
                        $productData .= "- Harga Member: {$service['member_price']}\n";
                        $productData .= "- Harga Non Member: {$service['non_member_price']}\n";
                        $productData .= "- Harga per Unit: {$service['unit']}\n";

                        // Filter addons untuk service saat ini
                        $serviceAddons = array_filter($addons, function($addon) use ($service) {
                            return $addon['service_id'] == $service['id'];
                        });

                        if (!empty($serviceAddons)) {
                            $productData .= "- Layanan ini memiliki add-on:\n\n";
                            foreach ($serviceAddons as $addon) {
                                $productData .= "##### Add-on: {$addon['name']}\n";
                                $productData .= "- Deskripsi: {$addon['description']}\n";
                                $productData .= "- Kategori: {$addon['category']}\n";
                                $productData .= "- Harga: {$addon['price']}\n\n";
                            }
                        }
                    }
                }
            }

        } catch (PDOException $e) {
            return response()->json(['error' => 'Koneksi database gagal: ' . $e->getMessage()], 500);
        }

        // Variabel untuk pertanyaan yang akan dikirim oleh pelanggan
        // Mengambil pertanyaan dari input 'question' di request
        $userQuestion = $request->input('question', 'yang service pricenya paling worthit untuk dijadikan member dan lokasi saya di padalarang membership ke brand mana ya
    .');

        // Membuat prompt dengan instruksi yang jelas dan menyertakan data produk.
        $fullPrompt = "
        Kamu adalah asisten customer service AI untuk saas Laundry.
        Tugasmu adalah menjawab pertanyaan pelanggan HANYA berdasarkan informasi produk yang telah diberikan di bawah ini.
        Jika pertanyaan pelanggan tidak relevan dengan produk yang tersedia, jawab dengan sopan bahwa kamu hanya bisa membantu terkait produk kami.
        Jangan mengarang jawaban atau memberikan informasi yang tidak ada.

        {$productData}

        Pertanyaan Pelanggan: {$userQuestion}
        ";

        $curl = curl_init();

        // Data yang akan dikirim dalam format JSON
        $data = json_encode([
            'model' => 'mistral-tiny', // Atau model lain seperti 'mistral-small', 'mistral-medium'
            'messages' => [
                [
                    'role' => 'user',
                    'content' => $fullPrompt
                ]
            ],
            'temperature' => 0.7 // Nilai antara 0-1, semakin tinggi semakin kreatif
        ]);

        curl_setopt_array($curl, [
            CURLOPT_URL => 'https://api.mistral.ai/v1/chat/completions',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Accept: application/json',
                'Authorization: Bearer ' . $apiKey
            ],
        ]);

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            return response()->json(['error' => 'cURL Error #:' . $err], 500);
        }

        $responseData = json_decode($response, true);

        // Periksa apakah ada respons dari AI
        if (isset($responseData['choices'][0]['message']['content'])) {
            $aiResponse = $responseData['choices'][0]['message']['content'];
            return response()->json(['response' => $aiResponse]);
        } else {
            return response()->json(['error' => 'Respons tidak valid dari API.'], 500);
        }
    }
}
