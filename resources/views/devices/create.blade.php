@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto bg-slate-800 rounded-xl shadow-lg overflow-hidden border border-slate-700">
    <div class="px-6 py-8">
        <h2 class="text-2xl font-bold text-white mb-6">Register New Device</h2>
        
        <form action="{{ route('devices.store') }}" method="POST" class="space-y-6">
            @csrf
            
            <div>
                <label for="name" class="block text-sm font-medium text-slate-300">Device Name</label>
                <input type="text" name="name" id="name" required class="mt-1 block w-full rounded-md bg-slate-900 border-slate-700 text-white shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm p-2.5">
            </div>

            <div>
                <label for="group_id" class="block text-sm font-medium text-slate-300">Operational Area / Group</label>
                <select name="group_id" id="group_id" required class="mt-1 block w-full rounded-md bg-slate-900 border-slate-700 text-white shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm p-2.5">
                    @foreach($groups as $group)
                        <option value="{{ $group->id }}">{{ $group->name }}</option>
                    @endforeach
                </select>
                @if($groups->isEmpty())
                    <p class="text-xs text-red-400 mt-2">No groups found. Please create a group in the database first.</p>
                @endif
            </div>

            <div class="border-t border-slate-700 pt-6">
                <h3 class="text-lg font-medium text-white mb-4">Target WiFi Credentials</h3>
                
                <div class="space-y-4">
                    <div>
                        <label for="wifi_ssid" class="block text-sm font-medium text-slate-300">WiFi SSID</label>
                        <input type="text" name="wifi_ssid" id="wifi_ssid" required class="mt-1 block w-full rounded-md bg-slate-900 border-slate-700 text-white shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm p-2.5">
                    </div>

                    <div>
                        <label for="wifi_password" class="block text-sm font-medium text-slate-300">WiFi Password</label>
                        <input type="password" name="wifi_password" id="wifi_password" required class="mt-1 block w-full rounded-md bg-slate-900 border-slate-700 text-white shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm p-2.5">
                    </div>
                </div>
            </div>

            <div class="pt-4 flex justify-end">
                <button type="submit" class="inline-flex justify-center rounded-md border border-transparent bg-blue-600 py-2 px-4 text-sm font-medium text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:ring-offset-slate-900 transition-colors">
                    Register and Generate Code
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
