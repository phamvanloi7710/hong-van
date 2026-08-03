import {
  CdkDrag,
  CdkDragDrop,
  CdkDragHandle,
  CdkDropList,
  CdkDropListGroup,
} from '@angular/cdk/drag-drop';
import { CommonModule } from '@angular/common';
import { HttpErrorResponse } from '@angular/common/http';
import {
  ChangeDetectionStrategy,
  Component,
  DestroyRef,
  ElementRef,
  HostListener,
  ViewChild,
  computed,
  inject,
  signal,
} from '@angular/core';
import { takeUntilDestroyed } from '@angular/core/rxjs-interop';
import { DomSanitizer, SafeResourceUrl } from '@angular/platform-browser';
import { FormsModule } from '@angular/forms';
import { MatButtonModule } from '@angular/material/button';
import { MatCardModule } from '@angular/material/card';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatIconModule } from '@angular/material/icon';
import { MatInputModule } from '@angular/material/input';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';
import { MatSelectModule } from '@angular/material/select';
import { MatSlideToggleModule } from '@angular/material/slide-toggle';
import { MatSnackBar, MatSnackBarModule } from '@angular/material/snack-bar';
import { MatTooltipModule } from '@angular/material/tooltip';
import {
  Observable,
  Subject,
  catchError,
  debounceTime,
  distinctUntilChanged,
  finalize,
  forkJoin,
  of,
  switchMap,
  tap,
} from 'rxjs';

import { AuthStore } from '../../core/auth/auth.store';
import { authErrorMessage } from '../../core/auth/auth-error';
import { I18nService } from '../../core/i18n/i18n.service';
import { PageBuilderDataService } from './page-builder-data.service';
import {
  DocumentMutationFailure,
  DocumentMutationResult,
  addBlock,
  blockBreadcrumbs,
  canPlaceBlock,
  deleteBlock,
  duplicateBlock,
  findBlock,
  flattenBlocks,
  moveBlock,
  updateBlockDeviceStyle,
  updateBlockProperty,
  updateBlockVisibility,
} from './page-builder-document';
import { PageBuilderHistory, fingerprint } from './page-builder-history';
import {
  PageBuilderTranslationKey,
  pageBuilderCategory,
  pageBuilderText,
} from './page-builder.i18n';
import {
  JsonPrimitive,
  JsonValue,
  PageBuilderBlock,
  PageBuilderBlockDefinition,
  PageBuilderDevice,
  PageBuilderDocument,
  PageBuilderDragPayload,
  PageBuilderDropListData,
  PageBuilderRegistry,
  PageBuilderSchema,
  PageBuilderSchemaEntry,
  PagePreviewMessage,
  PagePreviewSession,
  PageRecord,
  emptyPageBuilderDocument,
} from './page-builder.models';
import { PageBuilderPendingChanges } from './page-builder-route.guard';

interface AutosaveRequest {
  readonly pageId: string;
  readonly document: PageBuilderDocument;
}

type InspectorScope = 'props' | 'style';

@Component({
  selector: 'hv-page-builder-page',
  imports: [
    CommonModule,
    FormsModule,
    CdkDrag,
    CdkDragHandle,
    CdkDropList,
    CdkDropListGroup,
    MatButtonModule,
    MatCardModule,
    MatFormFieldModule,
    MatIconModule,
    MatInputModule,
    MatProgressSpinnerModule,
    MatSelectModule,
    MatSlideToggleModule,
    MatSnackBarModule,
    MatTooltipModule,
  ],
  templateUrl: './page-builder-page.html',
  styleUrl: './page-builder-page.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class PageBuilderPage implements PageBuilderPendingChanges {
  @ViewChild('previewFrame') private previewFrame?: ElementRef<HTMLIFrameElement>;

  private readonly data = inject(PageBuilderDataService);
  private readonly authStore = inject(AuthStore);
  private readonly i18n = inject(I18nService);
  private readonly snackBar = inject(MatSnackBar);
  private readonly destroyRef = inject(DestroyRef);
  private readonly sanitizer = inject(DomSanitizer);
  private readonly autosaveChanges = new Subject<AutosaveRequest>();
  private readonly previewChanges = new Subject<PageBuilderDocument>();
  private previewRefreshTimer: number | null = null;

  readonly devices: readonly PageBuilderDevice[] = ['desktop', 'tablet', 'mobile'];
  readonly history = new PageBuilderHistory(emptyPageBuilderDocument(1));
  readonly registry = signal<PageBuilderRegistry | null>(null);
  readonly pages = signal<readonly PageRecord[]>([]);
  readonly currentPage = signal<PageRecord | null>(null);
  readonly selectedBlockId = signal<string | null>(null);
  readonly activeDevice = signal<PageBuilderDevice>('desktop');
  readonly paletteSearch = signal('');
  readonly paletteCategory = signal('all');
  readonly loading = signal(true);
  readonly saving = signal(false);
  readonly loadError = signal<string | null>(null);
  readonly saveError = signal<string | null>(null);
  readonly operationError = signal<string | null>(null);
  readonly previewSession = signal<PagePreviewSession | null>(null);
  readonly previewUrl = signal<SafeResourceUrl | null>(null);
  readonly previewStatus = signal<'idle' | 'connecting' | 'loading' | 'ready' | 'updating' | 'error'>('idle');
  readonly previewError = signal<string | null>(null);

  readonly canEdit = computed(() => this.authStore.hasPermission('pages.update'));
  readonly canPublish = computed(() => this.authStore.hasPermission('pages.publish'));
  readonly definitions = computed(() => this.registry()?.blocks ?? []);
  readonly categories = computed(() =>
    [...new Set(this.definitions().map((definition) => definition.category))].sort(),
  );
  readonly filteredDefinitions = computed(() => {
    const query = this.paletteSearch().trim().toLocaleLowerCase(this.i18n.locale());
    const category = this.paletteCategory();

    return this.definitions().filter((definition) => {
      const label = this.blockLabel(definition).toLocaleLowerCase(this.i18n.locale());
      return (category === 'all' || definition.category === category)
        && (query === '' || label.includes(query) || definition.type.includes(query));
    });
  });
  readonly selectedBlock = computed(() => {
    const id = this.selectedBlockId();
    return id === null ? null : findBlock(this.history.current(), id);
  });
  readonly selectedDefinition = computed(() => {
    const block = this.selectedBlock();
    return block === null
      ? null
      : this.definitions().find((definition) => definition.type === block.type) ?? null;
  });
  readonly selectedBreadcrumbs = computed(() => {
    const id = this.selectedBlockId();
    return id === null ? [] : blockBreadcrumbs(this.history.current(), id);
  });
  readonly blockCount = computed(() => flattenBlocks(this.history.current()).length);

  readonly canEnter = (
    drag: CdkDrag<PageBuilderDragPayload>,
    drop: CdkDropList<PageBuilderDropListData>,
  ): boolean => {
    const registry = this.registry();
    if (registry === null || drop.data.kind !== 'document' || !this.canEdit()) return false;
    const payload = drag.data;
    if (payload.kind === 'palette' && payload.definitionType !== undefined) {
      return canPlaceBlock(this.history.current(), registry, payload.definitionType, drop.data.parentId);
    }
    if (payload.kind === 'block' && payload.blockId !== undefined) {
      const block = findBlock(this.history.current(), payload.blockId);
      if (block === null) return false;
      return moveBlock(
        this.history.current(),
        registry,
        block.id,
        drop.data.parentId,
        drop.data.itemIds.length,
      ).ok;
    }
    return false;
  };

  constructor() {
    this.autosaveChanges
      .pipe(
        debounceTime(1400),
        distinctUntilChanged(
          (previous, current) =>
            previous.pageId === current.pageId
            && fingerprint(previous.document) === fingerprint(current.document),
        ),
        switchMap((request) => this.persistDraft(request, false)),
        takeUntilDestroyed(this.destroyRef),
      )
      .subscribe();
    this.previewChanges
      .pipe(
        debounceTime(400),
        distinctUntilChanged((previous, current) => fingerprint(previous) === fingerprint(current)),
        switchMap((document) => this.pushPreview(document)),
        takeUntilDestroyed(this.destroyRef),
      )
      .subscribe();
    this.destroyRef.onDestroy(() => this.closePreviewSession());
    this.load();
  }

  text(
    key: PageBuilderTranslationKey,
    parameters: Readonly<Record<string, string | number>> = {},
  ): string {
    return pageBuilderText(this.i18n.locale(), key, parameters);
  }

  categoryLabel(category: string): string {
    return pageBuilderCategory(this.i18n.locale(), category);
  }

  blockLabel(block: PageBuilderBlock | PageBuilderBlockDefinition): string {
    const definition = 'labels' in block
      ? block
      : this.definitions().find((candidate) => candidate.type === block.type);
    return definition?.labels[this.i18n.locale()] ?? block.type;
  }

  pageTitle(page: PageRecord): string {
    return page.translations.find((translation) => translation.locale === this.i18n.locale())?.title
      ?? page.translations.find((translation) => translation.locale === 'vi')?.title
      ?? page.code;
  }

  currentPageTitle(): string {
    const page = this.currentPage();
    return page === null ? this.text('noPage') : this.pageTitle(page);
  }

  registryVersion(): string {
    return this.data.registryVersion() || '—';
  }

  load(): void {
    if (this.history.dirty() && !window.confirm(this.text('navigationConfirm'))) return;
    this.loading.set(true);
    this.loadError.set(null);
    forkJoin({ registry: this.data.registry(), pages: this.data.pages() })
      .pipe(
        finalize(() => this.loading.set(false)),
        takeUntilDestroyed(this.destroyRef),
      )
      .subscribe({
        next: ({ registry, pages }) => {
          this.registry.set(registry);
          this.pages.set(pages);
          const currentId = this.currentPage()?.public_id;
          this.selectPage(currentId ?? pages[0]?.public_id ?? null, true);
        },
        error: (error: unknown) =>
          this.loadError.set(authErrorMessage(error, this.text('loadError'))),
      });
  }

  selectPage(publicId: string | null, force = false): void {
    if (!force && this.history.dirty() && !window.confirm(this.text('navigationConfirm'))) return;
    const page = publicId === null
      ? null
      : this.pages().find((candidate) => candidate.public_id === publicId) ?? null;
    this.currentPage.set(page);
    this.selectedBlockId.set(null);
    this.saveError.set(null);
    const schemaVersion = this.registry()?.document.schemaVersion ?? 1;
    this.history.reset(page?.draft?.document ?? emptyPageBuilderDocument(schemaVersion));
    this.startPreview(page);
  }

  selectBlock(blockId: string): void {
    this.selectedBlockId.set(blockId);
    this.operationError.set(null);
    this.postPreviewMessage('preview.scroll-to-block', blockId);
  }

  setDevice(device: PageBuilderDevice): void {
    this.activeDevice.set(device);
  }

  retryPreview(): void {
    this.startPreview(this.currentPage());
  }

  refreshPreview(): void {
    const session = this.previewSession();
    if (session === null) {
      this.retryPreview();
      return;
    }
    this.data.refreshPreview(session)
      .pipe(takeUntilDestroyed(this.destroyRef))
      .subscribe({
        next: (refreshed) => {
          this.previewSession.set(refreshed);
          this.previewError.set(null);
          this.schedulePreviewRefresh(refreshed);
          this.postPreviewMessage('preview.refresh');
        },
        error: (error: unknown) => this.handlePreviewError(error, true),
      });
  }

  previewStatusText(): string {
    return this.text({
      idle: 'canvasHint',
      connecting: 'previewConnecting',
      loading: 'previewLoading',
      ready: 'previewReady',
      updating: 'previewUpdating',
      error: 'previewError',
    }[this.previewStatus()] as PageBuilderTranslationKey);
  }

  onPreviewFrameLoad(): void {
    if (this.previewSession() !== null) this.previewStatus.set('loading');
  }

  setPaletteSearch(value: string): void {
    this.paletteSearch.set(value);
  }

  setPaletteCategory(value: string): void {
    this.paletteCategory.set(value);
  }

  paletteDragData(definition: PageBuilderBlockDefinition): PageBuilderDragPayload {
    return { kind: 'palette', definitionType: definition.type };
  }

  blockDragData(block: PageBuilderBlock): PageBuilderDragPayload {
    return { kind: 'block', blockId: block.id };
  }

  blockIcon(block: PageBuilderBlock | PageBuilderBlockDefinition): string {
    const definition = 'icon' in block
      ? block
      : this.definitions().find((candidate) => candidate.type === block.type);
    const icon = definition?.icon ?? 'widgets';
    return icon.startsWith('fa-') ? 'widgets' : icon;
  }

  canHaveChildren(block: PageBuilderBlock): boolean {
    return (this.definitions().find((definition) => definition.type === block.type)
      ?.allowedChildren.length ?? 0) > 0;
  }

  addFromPalette(definition: PageBuilderBlockDefinition): void {
    const registry = this.registry();
    if (registry === null || !this.canEdit()) return;
    const selectedParent = this.selectedBlockId();
    const parentId = selectedParent !== null
      && canPlaceBlock(this.history.current(), registry, definition.type, selectedParent)
      ? selectedParent
      : null;
    this.applyMutation(
      addBlock(
        this.history.current(),
        registry,
        definition.type,
        parentId,
        parentId === null
          ? this.history.current().blocks.length
          : (findBlock(this.history.current(), parentId)?.children.length ?? 0),
      ),
    );
  }

  dropListData(
    parentId: string | null,
    blocks: readonly PageBuilderBlock[],
  ): PageBuilderDropListData {
    return { kind: 'document', parentId, itemIds: blocks.map((block) => block.id) };
  }

  paletteDropListData(): PageBuilderDropListData {
    return {
      kind: 'palette',
      parentId: null,
      itemIds: this.filteredDefinitions().map((definition) => definition.type),
    };
  }

  drop(
    event: CdkDragDrop<
      PageBuilderDropListData,
      PageBuilderDropListData,
      PageBuilderDragPayload
    >,
  ): void {
    const registry = this.registry();
    if (registry === null || event.container.data.kind !== 'document' || !this.canEdit()) return;
    const payload = event.item.data;
    const result = payload.kind === 'palette' && payload.definitionType !== undefined
      ? addBlock(
          this.history.current(),
          registry,
          payload.definitionType,
          event.container.data.parentId,
          event.currentIndex,
        )
      : payload.kind === 'block' && payload.blockId !== undefined
        ? moveBlock(
            this.history.current(),
            registry,
            payload.blockId,
            event.container.data.parentId,
            event.currentIndex,
          )
        : null;
    if (result !== null) this.applyMutation(result);
  }

  duplicateSelected(): void {
    const registry = this.registry();
    const blockId = this.selectedBlockId();
    if (registry === null || blockId === null || !this.canEdit()) return;
    this.applyMutation(duplicateBlock(this.history.current(), registry, blockId));
  }

  deleteSelected(): void {
    const registry = this.registry();
    const blockId = this.selectedBlockId();
    if (registry === null || blockId === null || !this.canEdit()) return;
    const result = deleteBlock(this.history.current(), registry, blockId);
    if (result.ok) this.selectedBlockId.set(null);
    this.applyMutation(result);
  }

  undo(): void {
    if (!this.canEdit() || !this.history.canUndo()) return;
    this.history.undo();
    this.ensureSelectionExists();
    this.queueAutosave();
  }

  redo(): void {
    if (!this.canEdit() || !this.history.canRedo()) return;
    this.history.redo();
    this.ensureSelectionExists();
    this.queueAutosave();
  }

  saveNow(): void {
    const page = this.currentPage();
    if (page === null || !this.canEdit() || !this.history.dirty()) return;
    this.persistDraft({ pageId: page.public_id, document: this.history.current() }, true)
      .pipe(takeUntilDestroyed(this.destroyRef))
      .subscribe();
  }

  publish(): void {
    if (!this.canPublish()) return;
    this.snackBar.open(this.text('publishPlanned'), undefined, { duration: 3500 });
  }

  propertyEntries(): readonly PageBuilderSchemaEntry[] {
    return schemaEntries(this.selectedDefinition()?.schema.props);
  }

  styleEntries(): readonly PageBuilderSchemaEntry[] {
    const style = this.selectedDefinition()?.schema.style.properties?.[this.activeDevice()];
    return schemaEntries(style);
  }

  propertyValue(scope: InspectorScope, key: string): JsonValue {
    const block = this.selectedBlock();
    if (block === null) return '';
    return scope === 'props'
      ? block.props[key] ?? ''
      : block.style[this.activeDevice()][key] ?? '';
  }

  primitiveValue(scope: InspectorScope, key: string): JsonPrimitive {
    const value = this.propertyValue(scope, key);
    return isJsonPrimitive(value) ? value : '';
  }

  isSupportedControl(schema: PageBuilderSchema): boolean {
    return schema.enum !== undefined
      || schema.type === 'string'
      || schema.type === 'number'
      || schema.type === 'integer'
      || schema.type === 'boolean';
  }

  isTextArea(entry: PageBuilderSchemaEntry): boolean {
    return (entry.schema.maxLength ?? 0) > 300
      || /content|description|caption|quote|message/i.test(entry.key);
  }

  updateTextValue(scope: InspectorScope, entry: PageBuilderSchemaEntry, event: Event): void {
    const target = event.target;
    if (!(target instanceof HTMLInputElement) && !(target instanceof HTMLTextAreaElement)) return;
    const value: JsonPrimitive = entry.schema.type === 'number' || entry.schema.type === 'integer'
      ? Number(target.value)
      : target.value;
    if (typeof value === 'number' && !Number.isFinite(value)) return;
    this.updateInspectorValue(scope, entry.key, value);
  }

  updateInspectorValue(scope: InspectorScope, key: string, value: JsonValue): void {
    const blockId = this.selectedBlockId();
    if (blockId === null || !this.canEdit()) return;
    const result = scope === 'props'
      ? updateBlockProperty(this.history.current(), blockId, key, value)
      : updateBlockDeviceStyle(
          this.history.current(),
          blockId,
          this.activeDevice(),
          key,
          value,
        );
    this.applyMutation(result);
  }

  setVisibility(device: PageBuilderDevice, visible: boolean): void {
    const blockId = this.selectedBlockId();
    if (blockId === null || !this.canEdit()) return;
    this.applyMutation(
      updateBlockVisibility(this.history.current(), blockId, device, visible),
    );
  }

  schemaRequired(scope: InspectorScope, key: string): boolean {
    const schema = scope === 'props'
      ? this.selectedDefinition()?.schema.props
      : this.selectedDefinition()?.schema.style.properties?.[this.activeDevice()];
    return schema?.required?.includes(key) ?? false;
  }

  failureText(reason: DocumentMutationFailure): string {
    return this.text(`failure.${reason}`);
  }

  canLeavePageBuilder(): boolean {
    return !this.history.dirty() || window.confirm(this.text('navigationConfirm'));
  }

  @HostListener('window:beforeunload', ['$event'])
  beforeUnload(event: BeforeUnloadEvent): void {
    if (!this.history.dirty()) return;
    event.preventDefault();
    event.returnValue = '';
  }

  @HostListener('window:keydown', ['$event'])
  keyboardShortcut(event: KeyboardEvent): void {
    if (!this.canEdit()) return;
    const command = event.ctrlKey || event.metaKey;
    if (command && event.key.toLocaleLowerCase() === 's') {
      event.preventDefault();
      this.saveNow();
    } else if (command && event.key.toLocaleLowerCase() === 'z' && !event.shiftKey) {
      event.preventDefault();
      this.undo();
    } else if (
      command
      && (event.key.toLocaleLowerCase() === 'y'
        || (event.shiftKey && event.key.toLocaleLowerCase() === 'z'))
    ) {
      event.preventDefault();
      this.redo();
    } else if (event.key === 'Delete' && !isEditableTarget(event.target)) {
      event.preventDefault();
      this.deleteSelected();
    }
  }

  @HostListener('window:message', ['$event'])
  previewMessage(event: MessageEvent<unknown>): void {
    const frame = this.previewFrame?.nativeElement;
    const session = this.previewSession();
    if (frame === undefined || session === null || event.origin !== window.location.origin || event.source !== frame.contentWindow) return;
    if (!isPreviewMessage(event.data, session)) return;
    if (event.data.type === 'preview.ready') {
      this.previewStatus.set('ready');
      this.previewError.set(null);
      const selected = this.selectedBlockId();
      if (selected !== null) this.postPreviewMessage('preview.scroll-to-block', selected);
      return;
    }
    const blockId = event.data.blockId;
    if (event.data.type === 'preview.block-selected' && typeof blockId === 'string' && findBlock(this.history.current(), blockId) !== null) {
      this.selectedBlockId.set(blockId);
      this.operationError.set(null);
    }
  }

  private applyMutation(result: DocumentMutationResult): void {
    if (!result.ok) {
      this.operationError.set(this.failureText(result.reason));
      return;
    }
    this.history.apply(result.document);
    this.selectedBlockId.set(result.blockId);
    this.operationError.set(null);
    this.queueAutosave();
  }

  private queueAutosave(): void {
    const page = this.currentPage();
    if (page === null || !this.canEdit()) return;
    const document = this.history.current();
    this.autosaveChanges.next({ pageId: page.public_id, document });
    this.previewChanges.next(document);
  }

  private startPreview(page: PageRecord | null): void {
    this.closePreviewSession();
    if (page === null) return;
    this.previewStatus.set('connecting');
    this.previewError.set(null);
    const pageId = page.public_id;
    this.data.createPreview(pageId, this.history.current(), this.i18n.locale())
      .pipe(takeUntilDestroyed(this.destroyRef))
      .subscribe({
        next: (session) => {
          if (this.currentPage()?.public_id !== pageId) {
            this.data.closePreview(session).subscribe();
            return;
          }
          if (!this.acceptPreviewUrl(session)) return;
          this.previewSession.set(session);
          this.previewStatus.set('loading');
          this.schedulePreviewRefresh(session);
        },
        error: (error: unknown) => this.handlePreviewError(error, false),
      });
  }

  private pushPreview(document: PageBuilderDocument): Observable<PagePreviewSession | null> {
    const session = this.previewSession();
    if (session === null) return of(null);
    this.previewStatus.set('updating');
    this.previewError.set(null);
    return this.data.updatePreview(session, document).pipe(
      tap((updated) => {
        this.previewSession.set(updated);
        this.schedulePreviewRefresh(updated);
        this.postPreviewMessage('preview.refresh');
      }),
      catchError((error: unknown) => {
        this.handlePreviewError(error, true);
        return of(null);
      }),
    );
  }

  private acceptPreviewUrl(session: PagePreviewSession): boolean {
    try {
      const url = new URL(session.url, window.location.origin);
      const valid = url.origin === window.location.origin
        && url.pathname === `/preview/page-builder/${session.token}`;
      if (!valid) throw new Error('Invalid preview URL');
      this.previewUrl.set(this.sanitizer.bypassSecurityTrustResourceUrl(url.toString()));
      return true;
    } catch {
      this.previewStatus.set('error');
      this.previewError.set(this.text('previewError'));
      this.previewSession.set(null);
      this.previewUrl.set(null);
      return false;
    }
  }

  private handlePreviewError(error: unknown, reconnectOnExpiry: boolean): void {
    if (reconnectOnExpiry && error instanceof HttpErrorResponse && [404, 410].includes(error.status)) {
      this.startPreview(this.currentPage());
      return;
    }
    this.previewStatus.set('error');
    this.previewError.set(previewErrorMessage(error, this.text('previewError'), this.text('previewValidationError')));
  }

  private schedulePreviewRefresh(session: PagePreviewSession): void {
    if (this.previewRefreshTimer !== null) window.clearTimeout(this.previewRefreshTimer);
    const delay = Math.max(15_000, Math.floor(session.ttl_seconds * 600));
    this.previewRefreshTimer = window.setTimeout(() => this.refreshPreview(), delay);
  }

  private closePreviewSession(): void {
    if (this.previewRefreshTimer !== null) {
      window.clearTimeout(this.previewRefreshTimer);
      this.previewRefreshTimer = null;
    }
    const session = this.previewSession();
    this.previewSession.set(null);
    this.previewUrl.set(null);
    this.previewStatus.set('idle');
    if (session !== null) this.data.closePreview(session).subscribe({ error: () => undefined });
  }

  private postPreviewMessage(type: 'preview.refresh' | 'preview.scroll-to-block', blockId?: string): void {
    const session = this.previewSession();
    const target = this.previewFrame?.nativeElement.contentWindow;
    if (session === null || target === null || target === undefined) return;
    target.postMessage({
      channel: 'hongvan.page-builder.preview',
      schemaVersion: session.message_schema_version,
      type,
      token: session.token,
      ...(blockId === undefined ? {} : { blockId }),
    }, window.location.origin);
  }

  private persistDraft(request: AutosaveRequest, notify: boolean): Observable<PageRecord | null> {
    this.saving.set(true);
    this.saveError.set(null);
    return this.data.saveDraft(request.pageId, request.document).pipe(
      tap((page) => {
        this.replacePage(page);
        this.history.markSaved(request.document);
        if (notify) {
          this.snackBar.open(this.text('savedNotice'), undefined, { duration: 2500 });
        }
      }),
      catchError((error: unknown) => {
        const fallback = error instanceof HttpErrorResponse && error.status === 409
          ? this.text('saveConflict')
          : this.text('saveError');
        this.saveError.set(authErrorMessage(error, fallback));
        return of(null);
      }),
      finalize(() => this.saving.set(false)),
    );
  }

  private replacePage(page: PageRecord): void {
    this.pages.update((pages) =>
      pages.map((candidate) => (candidate.public_id === page.public_id ? page : candidate)),
    );
    if (this.currentPage()?.public_id === page.public_id) this.currentPage.set(page);
  }

  private ensureSelectionExists(): void {
    const selected = this.selectedBlockId();
    if (selected !== null && findBlock(this.history.current(), selected) === null) {
      this.selectedBlockId.set(null);
    }
  }
}

function schemaEntries(schema: PageBuilderSchema | undefined): readonly PageBuilderSchemaEntry[] {
  return Object.entries(schema?.properties ?? {}).map(([key, value]) => ({ key, schema: value }));
}

function isJsonPrimitive(value: JsonValue): value is JsonPrimitive {
  return value === null || ['string', 'number', 'boolean'].includes(typeof value);
}

function isEditableTarget(target: EventTarget | null): boolean {
  return target instanceof HTMLInputElement
    || target instanceof HTMLTextAreaElement
    || target instanceof HTMLSelectElement
    || (target instanceof HTMLElement && target.isContentEditable);
}

function isPreviewMessage(value: unknown, session: PagePreviewSession): value is PagePreviewMessage {
  if (typeof value !== 'object' || value === null) return false;
  const message = value as Readonly<Record<string, unknown>>;
  return message['channel'] === 'hongvan.page-builder.preview'
    && message['schemaVersion'] === session.message_schema_version
    && message['token'] === session.token
    && (message['type'] === 'preview.ready' || message['type'] === 'preview.block-selected');
}

function previewErrorMessage(error: unknown, fallback: string, validationPrefix: string): string {
  if (!(error instanceof HttpErrorResponse)) return fallback;
  const payload = error.error;
  if (typeof payload !== 'object' || payload === null) return authErrorMessage(error, fallback);
  const errors = (payload as Readonly<Record<string, unknown>>)['errors'];
  if (typeof errors !== 'object' || errors === null) return authErrorMessage(error, fallback);
  const path = Object.keys(errors)[0];
  return path === undefined ? authErrorMessage(error, fallback) : `${validationPrefix}: ${path}`;
}
