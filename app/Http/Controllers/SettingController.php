<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Services\SettingService;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    public function index()
    {
        $settings = Setting::all()->keyBy('key');
        return view('settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'school_name'    => 'nullable|string|max:255',
            'school_name_kh' => 'nullable|string|max:255',
            'school_tagline' => 'nullable|string|max:500',
            'school_address' => 'nullable|string|max:1000',
            'school_phone'   => 'nullable|string|max:50',
            'school_email'   => 'nullable|email|max:255',
            'school_website' => 'nullable|url|max:255',
            'school_facebook'=> 'nullable|string|max:255',
            'school_description' => 'nullable|string',
            'school_motto'   => 'nullable|string|max:500',
            'invoice_header' => 'nullable|string',
            'invoice_footer' => 'nullable|string',
            'logo'           => 'nullable|image|max:2048',
        ]);

        $data = $request->except(['_token', '_method', 'logo']);

        // Handle logo upload
        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('settings', 'public');
            $data['school_logo'] = $path;

            // Delete old logo
            $oldLogo = Setting::get('school_logo');
            if ($oldLogo && Storage::disk('public')->exists($oldLogo)) {
                Storage::disk('public')->delete($oldLogo);
            }
        }

        SettingService::bulkUpdate($data);

        AuditService::log('settings.updated', 'ការកំណត់សាលារៀនត្រូវបានកែប្រែ');

        return redirect()->route('settings.index')
            ->with('success', 'បានរក្សាទុកការកំណត់ដោយជោគជ័យ។');
    }
}
