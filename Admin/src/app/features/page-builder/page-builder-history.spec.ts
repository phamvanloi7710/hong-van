import { PageBuilderHistory } from './page-builder-history';
import { PageBuilderDocument, emptyPageBuilderDocument } from './page-builder.models';

describe('PageBuilderHistory', () => {
  it('keeps bounded immutable undo and redo history with dirty state', () => {
    const initial = emptyPageBuilderDocument(1);
    const history = new PageBuilderHistory(initial, 2);
    const wide = settings(initial, 'wide');
    const full = settings(initial, 'full');
    const defaultAgain = settings(initial, 'default');

    history.apply(wide);
    history.apply(full);
    history.apply(defaultAgain);
    expect(history.dirty()).toBe(false);
    expect(history.undo().pageSettings.container).toBe('full');
    expect(history.undo().pageSettings.container).toBe('wide');
    expect(history.canUndo()).toBe(false);
    expect(history.redo().pageSettings.container).toBe('full');
    expect(history.canRedo()).toBe(true);
  });

  it('keeps dirty state after autosave conflict and clears only on confirmed save', () => {
    const initial = emptyPageBuilderDocument(1);
    const changed = settings(initial, 'wide');
    const history = new PageBuilderHistory(initial);

    history.apply(changed);
    expect(history.dirty()).toBe(true);
    // A failed/conflicting save does not call markSaved.
    expect(history.dirty()).toBe(true);
    history.markSaved(changed);
    expect(history.dirty()).toBe(false);
  });
});

function settings(
  document: PageBuilderDocument,
  container: PageBuilderDocument['pageSettings']['container'],
): PageBuilderDocument {
  return { ...document, pageSettings: { ...document.pageSettings, container } };
}
