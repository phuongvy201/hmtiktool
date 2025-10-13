<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'type',
        'group',
        'label',
        'description',
        'is_public',
    ];

    protected $casts = [
        'is_public' => 'boolean',
    ];

    /**
     * Get setting value with proper casting
     */
    public function getTypedValueAttribute()
    {
        if ($this->value === null) {
            return null;
        }

        switch ($this->type) {
            case 'boolean':
                return filter_var($this->value, FILTER_VALIDATE_BOOLEAN);
            case 'integer':
                return (int) $this->value;
            case 'json':
                return json_decode($this->value, true);
            case 'array':
                return explode(',', $this->value);
            default:
                return $this->value;
        }
    }

    /**
     * Set setting value with proper formatting
     */
    public function setTypedValueAttribute($value)
    {
        switch ($this->type) {
            case 'boolean':
                $this->value = $value ? '1' : '0';
                break;
            case 'integer':
                $this->value = (string) $value;
                break;
            case 'json':
                $this->value = json_encode($value);
                break;
            case 'array':
                $this->value = is_array($value) ? implode(',', $value) : $value;
                break;
            default:
                $this->value = (string) $value;
        }
    }

    /**
     * Get setting by key
     */
    public static function getValue(string $key, $default = null)
    {
        $setting = static::where('key', $key)->first();

        if (!$setting) {
            return $default;
        }

        return $setting->typed_value ?? $default;
    }

    /**
     * Set setting value
     */
    public static function setValue(string $key, $value, string $type = 'string'): bool
    {
        $setting = static::firstOrNew(['key' => $key]);
        $setting->type = $type;
        $setting->typed_value = $value;

        return $setting->save();
    }

    /**
     * Get settings by group
     */
    public static function getByGroup(string $group)
    {
        return static::where('group', $group)->get();
    }

    /**
     * Get all settings as key-value array
     */
    public static function getAllAsArray(): array
    {
        return static::all()->pluck('typed_value', 'key')->toArray();
    }

    /**
     * Check if setting exists
     */
    public static function has(string $key): bool
    {
        return static::where('key', $key)->exists();
    }

    /**
     * Delete setting by key
     */
    public static function deleteByKey(string $key): bool
    {
        return static::where('key', $key)->delete() > 0;
    }
}
