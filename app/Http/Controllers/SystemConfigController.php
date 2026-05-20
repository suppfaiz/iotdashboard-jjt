<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SystemConfig;
use App\Models\User;

class SystemConfigController extends Controller
{
    public function edit()
    {
        $plnTariff = SystemConfig::where('key', 'pln_tariff')->first();
        $mqttHost = SystemConfig::where('key', 'mqtt_host')->first();
        $mqttPort = SystemConfig::where('key', 'mqtt_port')->first();
        $users = User::all();
        return view('settings.edit', compact('plnTariff', 'mqttHost', 'mqttPort', 'users'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'pln_tariff' => 'required|numeric|min:0',
            'mqtt_host' => 'required|string',
            'mqtt_port' => 'required|numeric|min:1|max:65535'
        ]);

        SystemConfig::updateOrCreate(
            ['key' => 'pln_tariff'],
            ['value' => $request->pln_tariff]
        );
        
        SystemConfig::updateOrCreate(
            ['key' => 'mqtt_host'],
            ['value' => $request->mqtt_host]
        );
        
        SystemConfig::updateOrCreate(
            ['key' => 'mqtt_port'],
            ['value' => $request->mqtt_port]
        );

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
