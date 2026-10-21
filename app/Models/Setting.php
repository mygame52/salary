<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    public static function getValue(string $key, ?string $default = null): ?string
    {
        return static::where('key', $key)->value('value') ?? $default;
    }

    public static function setValue(string $key, ?string $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
    }

    public static function allAsMap(): array
    {
        $defaults = [
            'agency_name' => 'สำนักงานส่งเสริมการเรียนรู้ประจำจังหวัดนครศรีธรรมราช',
            'agency_tax_id' => '0994000550537',
            'logo_path' => '/images/logo.png',
            'signer_name' => 'นางดารารัตน์ เพ็ชรทวี',
            'signer_position' => 'ตำแหน่ง นักวิชาการเงินและบัญชี',
            'signer_title' => 'เจ้าหน้าที่การเงิน',
            'signature_image' => '',
        ];

        $stored = static::pluck('value', 'key')->toArray();

        return array_merge($defaults, array_filter($stored, fn ($v) => $v !== null));
    }
}
