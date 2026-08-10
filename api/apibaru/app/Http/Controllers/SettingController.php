<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class SettingController extends Controller
{
    public function form()
    {
        $appName = env('APP_NAME');
        $appIcon = env('APP_ICON');

        return view('dashboard.setting', compact('appName', 'appIcon'));
    }

    public function submit(Request $request)
    {
        $request->validate([
            'app_name' => 'required|string',
            'app_icon' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Accept image files
        ]);

        $appIcon = env('APP_ICON'); // Default value if no file is uploaded

        // Check if file is uploaded
        if ($request->hasFile('app_icon')) {
            // Store the uploaded file in storage
            $appIconPath = $request->file('app_icon')->store('icons', 'public');
            // Get the URL to the stored file
            $appIcon = asset('storage/' . $appIconPath);
        }

        $envData = [
            'APP_NAME' => $request->input('app_name'),
            'APP_ICON' => $appIcon, // Set the icon URL
        ];

        $this->setEnvironmentValue($envData);

        return redirect()->route('setting.form')->with('success', 'Settings updated successfully!');
    }

    private function setEnvironmentValue($data = array())
    {
        $envFile = app()->environmentFilePath();
        $str = file_get_contents($envFile);

        if (count($data) > 0) {
            foreach ($data as $key => $value) {
                $str .= "\n";
                $key = strtoupper($key);
                $value = $this->envValue($value);
                $key = $this->envKey($key);
                $key .= "=";

                if (strpos($str, $key) !== false) {
                    $str = preg_replace("/^$key.*$/m", "$key$value", $str);
                } else {
                    $str .= "\n$key$value";
                }
            }
        }

        file_put_contents($envFile, $str);
        Artisan::call('cache:clear');
    }

    private function envValue($value)
    {
        return '"' . $value . '"';
    }

    private function envKey($key)
    {
        return strtoupper($key);
    }
}
