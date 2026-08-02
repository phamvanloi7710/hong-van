import { ChangeDetectionStrategy, Component, inject, signal } from '@angular/core';
import { FormControl, FormRecord, ReactiveFormsModule } from '@angular/forms';
import { MatButtonModule } from '@angular/material/button';
import { MatCardModule } from '@angular/material/card';
import { MatCheckboxModule } from '@angular/material/checkbox';
import { MatDialog, MatDialogModule } from '@angular/material/dialog';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatIconModule } from '@angular/material/icon';
import { MatInputModule } from '@angular/material/input';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';
import { MatSelectModule } from '@angular/material/select';
import { MatSlideToggleModule } from '@angular/material/slide-toggle';
import { MatSnackBar } from '@angular/material/snack-bar';
import { MatTabsModule } from '@angular/material/tabs';
import { filter, finalize, Observable, switchMap } from 'rxjs';

import { authErrorMessage } from '../../core/auth/auth-error';
import { AuthStore } from '../../core/auth/auth.store';
import { I18nService } from '../../core/i18n/i18n.service';
import { TranslationPipe } from '../../core/i18n/translation.pipe';
import { DirectoryDialogData, DirectoryFormDialog, DirectoryPayload } from './directory-form-dialog';
import { SettingsDataService } from './settings-data.service';
import { SETTINGS_TRANSLATIONS } from './settings.i18n';
import {
  Branch,
  BranchPayload,
  BusinessHour,
  CompanySettingGroup,
  CompanySettingsPayload,
  ContactChannel,
  ContactChannelPayload,
  SettingValue,
  SocialLink,
  SocialLinkPayload,
} from './settings.models';

@Component({
  selector: 'hv-settings-page',
  imports: [MatButtonModule, MatCardModule, MatCheckboxModule, MatDialogModule, MatFormFieldModule, MatIconModule, MatInputModule, MatProgressSpinnerModule, MatSelectModule, MatSlideToggleModule, MatTabsModule, ReactiveFormsModule, TranslationPipe],
  templateUrl: './settings-page.html',
  styleUrl: './settings-page.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class SettingsPage {
  private readonly data = inject(SettingsDataService);
  private readonly dialog = inject(MatDialog);
  private readonly snackBar = inject(MatSnackBar);
  private readonly i18n = inject(I18nService);
  readonly authStore = inject(AuthStore);

  readonly payload = signal<CompanySettingsPayload | null>(null);
  readonly loading = signal(true);
  readonly error = signal<string | null>(null);
  readonly hourScope = signal<string>('global');
  readonly dayKeys = ['sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat'] as const;
  readonly hours = signal<readonly BusinessHour[]>(this.defaultHours());
  readonly settingForms = new Map<string, FormRecord<FormControl<SettingValue>>>();

  constructor() {
    this.reload();
  }

  text(key: string, parameters: Readonly<Record<string, string>> = {}): string {
    const source = SETTINGS_TRANSLATIONS[this.i18n.locale()][key] ?? key;
    return Object.entries(parameters).reduce((value, [name, replacement]) => value.replaceAll(`{${name}}`, replacement), source);
  }

  reload(): void {
    this.loading.set(true);
    this.error.set(null);
    this.data.load().pipe(finalize(() => this.loading.set(false))).subscribe({
      next: (payload) => this.setPayload(payload),
      error: (error: unknown) => this.error.set(authErrorMessage(error, this.text('loadError'))),
    });
  }

  formFor(group: CompanySettingGroup): FormRecord<FormControl<SettingValue>> {
    const existing = this.settingForms.get(group.key);
    if (existing) return existing;
    const controls: Record<string, FormControl<SettingValue>> = {};
    for (const setting of group.settings) controls[setting.key] = new FormControl<SettingValue>(setting.value);
    const form = new FormRecord<FormControl<SettingValue>>(controls);
    this.settingForms.set(group.key, form);
    return form;
  }

  saveGroup(group: CompanySettingGroup): void {
    this.run(this.data.updateGroup(group.key, this.formFor(group).getRawValue()), this.text('saved'), false);
  }

  selectHourScope(scope: string): void {
    this.hourScope.set(scope);
    this.syncHours();
  }

  updateHour(day: number, key: 'closes_at' | 'is_active' | 'is_closed' | 'note' | 'opens_at', value: string | boolean): void {
    this.hours.update((hours) => hours.map((hour) => hour.day_of_week === day ? { ...hour, [key]: value === '' ? null : value } : hour));
  }

  saveHours(): void {
    const branchId = this.hourScope() === 'global' ? null : this.hourScope();
    this.run(this.data.replaceBusinessHours(branchId, this.hours()), this.text('saved'));
  }

  openBranch(branch: Branch | null = null): void {
    this.openDialog('branch', branch).pipe(switchMap((payload) => this.data.saveBranch(branch?.public_id ?? null, payload as BranchPayload))).subscribe({ next: (result) => this.complete(result), error: (error: unknown) => this.fail(error) });
  }

  openSocial(link: SocialLink | null = null): void {
    this.openDialog('social', link).pipe(switchMap((payload) => this.data.saveSocialLink(link?.public_id ?? null, payload as SocialLinkPayload))).subscribe({ next: (result) => this.complete(result), error: (error: unknown) => this.fail(error) });
  }

  openContact(channel: ContactChannel | null = null): void {
    this.openDialog('contact', channel).pipe(switchMap((payload) => this.data.saveContactChannel(channel?.public_id ?? null, payload as ContactChannelPayload))).subscribe({ next: (result) => this.complete(result), error: (error: unknown) => this.fail(error) });
  }

  deleteBranch(branch: Branch): void {
    if (confirm(this.text('confirmDelete', { name: branch.name }))) this.run(this.data.deleteBranch(branch.public_id), this.text('deleted'));
  }

  deleteSocial(link: SocialLink): void {
    if (confirm(this.text('confirmDelete', { name: link.label }))) this.run(this.data.deleteSocialLink(link.public_id), this.text('deleted'));
  }

  deleteContact(channel: ContactChannel): void {
    if (confirm(this.text('confirmDelete', { name: channel.label }))) this.run(this.data.deleteContactChannel(channel.public_id), this.text('deleted'));
  }

  private openDialog(kind: DirectoryDialogData['kind'], record: DirectoryDialogData['record']): Observable<DirectoryPayload> {
    return this.dialog.open<DirectoryFormDialog, DirectoryDialogData, DirectoryPayload>(DirectoryFormDialog, { data: { kind, record }, width: '760px' }).afterClosed().pipe(filter((payload): payload is DirectoryPayload => payload !== undefined));
  }

  private run(request: Observable<CompanySettingGroup | CompanySettingsPayload>, message: string, replacesPayload = true): void {
    request.subscribe({
      next: (result) => {
        if (replacesPayload && 'groups' in result) this.setPayload(result);
        else this.reload();
        this.snackBar.open(message, this.i18n.t('common.close'), { duration: 3000 });
      },
      error: (error: unknown) => this.fail(error),
    });
  }

  private complete(payload: CompanySettingsPayload): void {
    this.setPayload(payload);
    this.snackBar.open(this.text('saved'), this.i18n.t('common.close'), { duration: 3000 });
  }

  private fail(error: unknown): void {
    this.snackBar.open(authErrorMessage(error, this.text('operationError')), this.i18n.t('common.close'), { duration: 5000 });
  }

  private setPayload(payload: CompanySettingsPayload): void {
    this.payload.set(payload);
    this.settingForms.clear();
    this.syncHours();
  }

  private syncHours(): void {
    const payload = this.payload();
    if (!payload) return;
    const branchId = this.hourScope() === 'global' ? null : this.hourScope();
    const stored = payload.business_hours.filter((hour) => hour.branch_id === branchId);
    this.hours.set(this.defaultHours().map((fallback) => stored.find((hour) => hour.day_of_week === fallback.day_of_week) ?? fallback));
  }

  private defaultHours(): readonly BusinessHour[] {
    return this.dayKeys.map((_, day) => ({ branch_id: null, day_of_week: day, opens_at: '08:00', closes_at: '17:00', is_closed: day === 0, note: null, is_active: true }));
  }
}
