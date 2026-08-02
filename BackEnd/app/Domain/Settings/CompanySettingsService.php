<?php

namespace App\Domain\Settings;

use App\Models\Branch;
use App\Models\BusinessHour;
use App\Models\ContactChannel;
use App\Models\Setting;
use App\Models\SettingGroup;
use App\Models\SocialLink;
use App\Models\User;
use Illuminate\Support\Env;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final readonly class CompanySettingsService
{
    public function __construct(private CompanySettingsAuditLogger $auditLogger) {}

    /** @return array<string, mixed> */
    public function adminPayload(): array
    {
        return Cache::rememberForever($this->adminCacheKey(), function (): array {
            return [
                'groups' => SettingGroup::query()
                    ->where('is_active', true)
                    ->with('settings')
                    ->orderBy('sort_order')
                    ->get()
                    ->map(fn (SettingGroup $group): array => $this->serializeGroup($group))
                    ->all(),
                'branches' => $this->serializeBranches(false),
                'business_hours' => $this->serializeBusinessHours(false),
                'social_links' => $this->serializeSocialLinks(false),
                'contact_channels' => $this->serializeContactChannels(false),
            ];
        });
    }

    /** @return array<string, mixed> */
    public function publicPayload(): array
    {
        return Cache::rememberForever($this->publicCacheKey(), function (): array {
            $settings = [];
            SettingGroup::query()
                ->where('is_active', true)
                ->with(['settings' => fn ($query) => $query->where('is_public', true)->where('value_type', '!=', 'secret')->orderBy('sort_order')])
                ->orderBy('sort_order')
                ->get()
                ->each(function (SettingGroup $group) use (&$settings): void {
                    $settings[$group->key] = $group->settings
                        ->mapWithKeys(fn (Setting $setting): array => [$setting->key => $this->decode($setting)])
                        ->all();
                });

            return [
                'settings' => $settings,
                'branches' => $this->serializeBranches(true),
                'business_hours' => $this->serializeBusinessHours(true),
                'social_links' => $this->serializeSocialLinks(true),
                'contact_channels' => $this->serializeContactChannels(true),
            ];
        });
    }

    public function value(string $groupKey, string $settingKey, mixed $default = null): mixed
    {
        $setting = $this->find($groupKey, $settingKey);

        return $setting === null || $setting->value_type === 'secret'
            ? $default
            : $this->decode($setting);
    }

    public function secret(string $groupKey, string $settingKey): ?string
    {
        $setting = $this->find($groupKey, $settingKey);
        if ($setting === null || $setting->value_type !== 'secret' || $setting->value === null) {
            return null;
        }

        if (str_starts_with($setting->value, 'env:')) {
            $value = Env::get(substr($setting->value, 4));

            return is_scalar($value) ? (string) $value : null;
        }

        return str_starts_with($setting->value, 'enc:')
            ? Crypt::decryptString(substr($setting->value, 4))
            : null;
    }

    /**
     * @param  array<string, mixed>  $values
     * @return array<string, mixed>
     */
    public function updateGroup(SettingGroup $group, array $values, User $actor): array
    {
        $definition = config('company_settings.groups.'.$group->key.'.settings');
        abort_unless(is_array($definition), 404);
        $changedKeys = array_values(array_intersect(array_keys($values), array_keys($definition)));

        DB::transaction(function () use ($actor, $changedKeys, $group, $values): void {
            $settings = $group->settings()->whereIn('key', $changedKeys)->get()->keyBy('key');

            foreach ($changedKeys as $key) {
                $setting = $settings->get($key);
                if (! $setting instanceof Setting || $setting->is_locked) {
                    continue;
                }

                $value = $values[$key];
                if ($setting->value_type === 'secret') {
                    if ($value === null || $value === '') {
                        continue;
                    }
                    $value = $this->protectSecret((string) $value, $key);
                } else {
                    $value = $this->encode($value, $setting->value_type);
                }

                $setting->forceFill(['value' => $value, 'updated_by' => $actor->getKey()])->save();
            }
        });

        $this->invalidate();
        $this->auditLogger->record('settings.group.updated', $actor, $group->key, $changedKeys);
        $group->load('settings');

        return $this->serializeGroup($group);
    }

    public function invalidate(): void
    {
        Cache::forget($this->adminCacheKey());
        Cache::forget($this->publicCacheKey());
    }

    /** @return array<string, mixed> */
    private function serializeGroup(SettingGroup $group): array
    {
        return [
            'public_id' => $group->public_id,
            'key' => $group->key,
            'label' => $group->label,
            'description' => $group->description,
            'settings' => $group->settings->map(function (Setting $setting): array {
                $secret = $setting->value_type === 'secret';

                return [
                    'public_id' => $setting->public_id,
                    'key' => $setting->key,
                    'label' => $setting->label,
                    'description' => $setting->description,
                    'value' => $secret ? null : $this->decode($setting),
                    'value_type' => $setting->value_type,
                    'is_public' => $setting->is_public,
                    'is_locked' => $setting->is_locked,
                    'has_value' => $setting->value !== null && $setting->value !== '',
                ];
            })->all(),
        ];
    }

    /** @return list<array<string, mixed>> */
    private function serializeBranches(bool $publicOnly): array
    {
        return Branch::query()
            ->when($publicOnly, fn ($query) => $query->where('is_active', true))
            ->orderBy('sort_order')->orderBy('name')->get()
            ->map(static fn (Branch $branch): array => [
                'public_id' => $branch->public_id, 'name' => $branch->name, 'code' => $branch->code,
                'address' => $branch->address, 'province' => $branch->province, 'district' => $branch->district,
                'ward' => $branch->ward, 'postal_code' => $branch->postal_code, 'latitude' => $branch->latitude,
                'longitude' => $branch->longitude, 'phone' => $branch->phone, 'email' => $branch->email,
                'is_head_office' => $branch->is_head_office, 'is_active' => $branch->is_active, 'sort_order' => $branch->sort_order,
            ])->all();
    }

    /** @return list<array<string, mixed>> */
    private function serializeBusinessHours(bool $publicOnly): array
    {
        return BusinessHour::query()->with('branch')
            ->when($publicOnly, fn ($query) => $query->where('is_active', true))
            ->orderBy('sort_order')->orderBy('day_of_week')->get()
            ->map(static fn (BusinessHour $hour): array => [
                'public_id' => $hour->public_id, 'branch_id' => $hour->branch?->public_id,
                'day_of_week' => $hour->day_of_week, 'opens_at' => $hour->opens_at,
                'closes_at' => $hour->closes_at, 'is_closed' => $hour->is_closed,
                'note' => $hour->note, 'is_active' => $hour->is_active, 'sort_order' => $hour->sort_order,
            ])->all();
    }

    /** @return list<array<string, mixed>> */
    private function serializeSocialLinks(bool $publicOnly): array
    {
        return SocialLink::query()->when($publicOnly, fn ($query) => $query->where('is_active', true))
            ->orderBy('sort_order')->get()->map(static fn (SocialLink $link): array => [
                'public_id' => $link->public_id, 'platform' => $link->platform, 'label' => $link->label,
                'url' => $link->url, 'icon' => $link->icon, 'is_active' => $link->is_active, 'sort_order' => $link->sort_order,
            ])->all();
    }

    /** @return list<array<string, mixed>> */
    private function serializeContactChannels(bool $publicOnly): array
    {
        return ContactChannel::query()->when($publicOnly, fn ($query) => $query->where('is_active', true))
            ->orderBy('sort_order')->get()->map(static fn (ContactChannel $channel): array => [
                'public_id' => $channel->public_id, 'type' => $channel->type, 'label' => $channel->label,
                'value' => $channel->value, 'href' => $channel->href, 'availability_note' => $channel->availability_note,
                'is_primary' => $channel->is_primary, 'is_active' => $channel->is_active, 'sort_order' => $channel->sort_order,
            ])->all();
    }

    private function decode(Setting $setting): mixed
    {
        return match ($setting->value_type) {
            'boolean' => $setting->value === '1',
            'integer' => $setting->value === null ? null : (int) $setting->value,
            'decimal' => $setting->value === null ? null : (float) $setting->value,
            'json' => $setting->value === null ? null : json_decode($setting->value, true, 512, JSON_THROW_ON_ERROR),
            default => $setting->value,
        };
    }

    private function encode(mixed $value, string $type): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return match ($type) {
            'boolean' => $value ? '1' : '0',
            'json' => json_encode($value, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            default => (string) $value,
        };
    }

    private function protectSecret(string $value, string $key): string
    {
        if (str_starts_with($value, 'env:')) {
            if (preg_match('/^env:[A-Z][A-Z0-9_]*$/', $value) !== 1) {
                throw ValidationException::withMessages(['values.'.$key => ['The environment reference is invalid.']]);
            }

            return $value;
        }

        return 'enc:'.Crypt::encryptString($value);
    }

    private function find(string $groupKey, string $settingKey): ?Setting
    {
        return Setting::query()
            ->where('key', $settingKey)
            ->whereHas('group', fn ($query) => $query->where('key', $groupKey))
            ->first();
    }

    private function adminCacheKey(): string
    {
        return (string) config('company_settings.cache.admin', 'hongvan.company-settings.admin.v1');
    }

    private function publicCacheKey(): string
    {
        return (string) config('company_settings.cache.public', 'hongvan.company-settings.public.v1');
    }
}
