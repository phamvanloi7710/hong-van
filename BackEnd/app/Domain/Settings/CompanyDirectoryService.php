<?php

namespace App\Domain\Settings;

use App\Models\Branch;
use App\Models\BusinessHour;
use App\Models\ContactChannel;
use App\Models\SocialLink;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

final readonly class CompanyDirectoryService
{
    public function __construct(private CompanySettingsService $settings, private CompanySettingsAuditLogger $auditLogger) {}

    /** @param array<string, mixed> $data */
    public function saveBranch(?Branch $branch, array $data, User $actor): Branch
    {
        $branch ??= new Branch;
        $branch->fill($data)->forceFill([$branch->exists ? 'updated_by' : 'created_by' => $actor->getKey(), 'updated_by' => $actor->getKey()])->save();
        $this->changed('branch.saved', $actor, $branch, array_keys($data));

        return $branch->refresh();
    }

    /** @param array<string, mixed> $data */
    public function saveSocialLink(?SocialLink $link, array $data, User $actor): SocialLink
    {
        $link ??= new SocialLink;
        $link->fill($data)->forceFill([$link->exists ? 'updated_by' : 'created_by' => $actor->getKey(), 'updated_by' => $actor->getKey()])->save();
        $this->changed('social_link.saved', $actor, $link, array_keys($data));

        return $link->refresh();
    }

    /** @param array<string, mixed> $data */
    public function saveContactChannel(?ContactChannel $channel, array $data, User $actor): ContactChannel
    {
        $channel ??= new ContactChannel;
        $channel->fill($data)->forceFill([$channel->exists ? 'updated_by' : 'created_by' => $actor->getKey(), 'updated_by' => $actor->getKey()])->save();
        $this->changed('contact_channel.saved', $actor, $channel, array_keys($data));

        return $channel->refresh();
    }

    /** @param list<array<string, mixed>> $hours */
    public function replaceBusinessHours(?Branch $branch, array $hours, User $actor): void
    {
        $scopeKey = $branch ? 'branch:'.$branch->getKey() : 'global';

        DB::transaction(function () use ($actor, $branch, $hours, $scopeKey): void {
            BusinessHour::query()->where('scope_key', $scopeKey)->delete();
            foreach ($hours as $index => $hour) {
                BusinessHour::query()->create([
                    ...$hour,
                    'branch_id' => $branch?->getKey(),
                    'scope_key' => $scopeKey,
                    'sort_order' => $index,
                    'created_by' => $actor->getKey(),
                    'updated_by' => $actor->getKey(),
                ]);
            }
        });

        $this->settings->invalidate();
        $this->auditLogger->record('business_hours.replaced', $actor, $branch === null ? 'global' : $branch->public_id, ['hours']);
    }

    public function delete(Model $model, User $actor): void
    {
        $subject = $model instanceof Branch ? 'branch' : ($model instanceof SocialLink ? 'social_link' : 'contact_channel');
        $publicId = (string) $model->getAttribute('public_id');
        $model->delete();
        $this->settings->invalidate();
        $this->auditLogger->record($subject.'.deleted', $actor, $publicId);
    }

    /** @param list<string> $keys */
    private function changed(string $event, User $actor, Model $model, array $keys): void
    {
        $this->settings->invalidate();
        $this->auditLogger->record($event, $actor, (string) $model->getAttribute('public_id'), $keys);
    }
}
