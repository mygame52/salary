<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SettingController extends Controller
{
    public function edit()
    {
        abort_unless(Auth::user()->isAdmin(), 403);
        $settings = Setting::allAsMap();
        return view('settings.edit', compact('settings'));
    }

    public function update(Request $request)
    {
        abort_unless(Auth::user()->isAdmin(), 403);

        $data = $request->validate([
            'agency_name' => ['required', 'string', 'max:255'],
            'agency_tax_id' => ['required', 'string', 'max:50'],
            'signer_name' => ['required', 'string', 'max:255'],
            'signer_position' => ['required', 'string', 'max:255'],
            'signer_title' => ['required', 'string', 'max:255'],
            'logo_file' => ['nullable', 'image', 'max:5120'],
            'signature_file' => ['nullable', 'image', 'max:5120'],
        ]);

        Setting::setValue('agency_name', $data['agency_name']);
        Setting::setValue('agency_tax_id', $data['agency_tax_id']);
        Setting::setValue('signer_name', $data['signer_name']);
        Setting::setValue('signer_position', $data['signer_position']);
        Setting::setValue('signer_title', $data['signer_title']);

        if ($request->hasFile('logo_file')) {
            $file = $request->file('logo_file');
            $filename = 'logo_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('images'), $filename);
            Setting::setValue('logo_path', '/images/' . $filename);
        }

        if ($request->hasFile('signature_file')) {
            $file = $request->file('signature_file');
            $filename = 'signature_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('images'), $filename);
            Setting::setValue('signature_image', '/images/' . $filename);
        }

        if ($request->boolean('clear_signature')) {
            Setting::setValue('signature_image', '');
        }

        return back()->with('success', 'บันทึกการตั้งค่าหัวสลิปและลายเซ็นเรียบร้อยแล้ว');
    }
}
