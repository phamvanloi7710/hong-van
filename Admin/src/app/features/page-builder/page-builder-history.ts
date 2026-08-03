import { WritableSignal, computed, signal } from '@angular/core';

import { PageBuilderDocument } from './page-builder.models';

export class PageBuilderHistory {
  private readonly pastState = signal<readonly PageBuilderDocument[]>([]);
  private readonly currentState: WritableSignal<PageBuilderDocument>;
  private readonly futureState = signal<readonly PageBuilderDocument[]>([]);
  private readonly savedFingerprintState: WritableSignal<string>;

  readonly current;
  readonly canUndo = computed(() => this.pastState().length > 0);
  readonly canRedo = computed(() => this.futureState().length > 0);
  readonly dirty = computed(
    () => fingerprint(this.currentState()) !== this.savedFingerprintState(),
  );

  constructor(initial: PageBuilderDocument, private readonly limit = 50) {
    this.currentState = signal(structuredClone(initial));
    this.savedFingerprintState = signal(fingerprint(initial));
    this.current = this.currentState.asReadonly();
  }

  reset(document: PageBuilderDocument): void {
    const snapshot = structuredClone(document);
    this.pastState.set([]);
    this.currentState.set(snapshot);
    this.futureState.set([]);
    this.savedFingerprintState.set(fingerprint(snapshot));
  }

  apply(document: PageBuilderDocument): void {
    if (fingerprint(document) === fingerprint(this.currentState())) return;
    const past = [...this.pastState(), this.currentState()];
    this.pastState.set(past.slice(Math.max(0, past.length - this.limit)));
    this.currentState.set(structuredClone(document));
    this.futureState.set([]);
  }

  undo(): PageBuilderDocument {
    const past = this.pastState();
    if (past.length === 0) return this.currentState();
    const previous = past[past.length - 1];
    this.pastState.set(past.slice(0, -1));
    this.futureState.set([this.currentState(), ...this.futureState()].slice(0, this.limit));
    this.currentState.set(previous);
    return previous;
  }

  redo(): PageBuilderDocument {
    const future = this.futureState();
    if (future.length === 0) return this.currentState();
    const next = future[0];
    const past = [...this.pastState(), this.currentState()];
    this.pastState.set(past.slice(Math.max(0, past.length - this.limit)));
    this.futureState.set(future.slice(1));
    this.currentState.set(next);
    return next;
  }

  markSaved(document: PageBuilderDocument): void {
    this.savedFingerprintState.set(fingerprint(document));
  }
}

export function fingerprint(document: PageBuilderDocument): string {
  return JSON.stringify(document);
}
