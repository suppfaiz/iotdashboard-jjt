<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SystemConfig;
use App\Models\User;

class SystemConfigController extends Controller
{
    public function edit()
    {
        $configs = SystemConfig::pluck('value', 'key')->all();
        $users = User::all();
        return view('settings.edit', compact('configs', 'users'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'pln_tariff' => 'required|numeric|min:0',
            'pln_tariff_wbp' => 'required|numeric|min:0',
            'pln_tariff_lwbp' => 'required|numeric|min:0',
            'wbp_start' => 'required|string|regex:/^\d{2}:\d{2}$/',
            'wbp_end' => 'required|string|regex:/^\d{2}:\d{2}$/',
            'mqtt_host' => 'required|string',
            'mqtt_port' => 'required|numeric|min:1|max:65535',
            'mqtt_user' => 'nullable|string',
            'mqtt_password' => 'nullable|string',
            'mqtt_use_tls' => 'nullable|in:0,1',
            'telegram_bot_token' => 'nullable|string',
            'telegram_chat_id' => 'nullable|string',
            'alert_voltage_min' => 'required|numeric|min:0',
            'alert_voltage_max' => 'required|numeric|min:0',
            'alert_power_max' => 'required|numeric|min:0',
        ]);

        $keys = [
            'pln_tariff',
            'pln_tariff_wbp',
            'pln_tariff_lwbp',
            'wbp_start',
            'wbp_end',
            'mqtt_host',
            'mqtt_port',
            'mqtt_user',
            'mqtt_password',
            'mqtt_use_tls',
            'telegram_bot_token',
            'telegram_chat_id',
            'alert_voltage_min',
            'alert_voltage_max',
            'alert_power_max',
        ];

        foreach ($keys as $key) {
            $value = $request->input($key) ?? '';
            if ($key === 'mqtt_use_tls') {
                $value = $request->has('mqtt_use_tls') ? '1' : '0';
            }
            SystemConfig::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }

        return redirect()->route('settings.edit', ['tab' => 'config'])->with('success', 'System configurations updated successfully!');
    }

    public function storeUser(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:admin,user',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'role' => $request->role,
        ]);

        return redirect()->route('settings.edit', ['tab' => 'users'])->with('success', 'User created successfully!');
    }

    public function destroyUser(User $user)
    {
        if (auth()->id() === $user->id) {
            return redirect()->route('settings.edit', ['tab' => 'users'])->with('error', 'You cannot delete your own account!');
        }

        $user->delete();

        return redirect()->route('settings.edit', ['tab' => 'users'])->with('success', 'User deleted successfully!');
    }
}
