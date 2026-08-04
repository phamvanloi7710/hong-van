import {
  addBlock,
  deleteBlock,
  duplicateBlock,
  findBlock,
  findPlacementParent,
  flattenBlocks,
  moveBlock,
  updateBlockDeviceStyle,
  updateBlockVisibility,
} from './page-builder-document';
import {
  PageBuilderBlock,
  PageBuilderBlockDefinition,
  PageBuilderDocument,
  PageBuilderRegistry,
  emptyPageBuilderDocument,
} from './page-builder.models';

describe('Page Builder immutable document operations', () => {
  it('creates a valid block id when randomUUID is unavailable on an HTTP custom domain', () => {
    const originalCrypto = globalThis.crypto;
    vi.stubGlobal('crypto', {
      getRandomValues: (bytes: Uint8Array) => {
        bytes.set(Array.from({ length: 16 }, (_, index) => index));
        return bytes;
      },
    });

    try {
      const added = addBlock(emptyPageBuilderDocument(1), registryFixture(), 'layout.section', null, 0);
      expect(added.ok).toBe(true);
      if (!added.ok) return;
      expect(added.blockId).toBe('00010203-0405-4607-8809-0a0b0c0d0e0f');
    } finally {
      vi.stubGlobal('crypto', originalCrypto);
    }
  });

  it('adds nested blocks without mutating input and rejects invalid parent relationships', () => {
    const registry = registryFixture();
    const initial = emptyPageBuilderDocument(1);
    const invalid = addBlock(initial, registry, 'content.text', null, 0, ids('text-root-0001'));
    expect(invalid).toEqual({ ok: false, reason: 'invalid-parent' });

    const section = addBlock(initial, registry, 'layout.section', null, 0, ids('section-0001'));
    expect(section.ok).toBe(true);
    if (!section.ok) return;
    expect(initial.blocks).toHaveLength(0);

    const container = addBlock(
      section.document,
      registry,
      'layout.container',
      section.blockId,
      0,
      ids('container-0001'),
    );
    expect(container.ok).toBe(true);
    if (!container.ok) return;

    const text = addBlock(
      container.document,
      registry,
      'content.text',
      container.blockId,
      0,
      ids('content-text-0001'),
    );
    expect(text.ok).toBe(true);
    if (!text.ok) return;
    expect(findBlock(text.document, text.blockId)?.type).toBe('content.text');

    const cycle = moveBlock(text.document, registry, section.blockId, container.blockId, 0);
    expect(cycle).toEqual({ ok: false, reason: 'inside-descendant' });
  });

  it('finds the nearest compatible container for palette clicks', () => {
    const { registry, document, containerId, firstTextId } = nestedDocument();

    expect(findPlacementParent(document, registry, 'content.text', firstTextId)).toBe(containerId);
    expect(findPlacementParent(document, registry, 'content.text', null)).toBe(containerId);
    expect(findPlacementParent(document, registry, 'layout.section', firstTextId)).toBeNull();
  });

  it('reorders nested blocks and enforces parent minimum children', () => {
    const { registry, document, containerId, firstTextId, secondTextId } = nestedDocument();
    const reordered = moveBlock(document, registry, secondTextId, containerId, 0);
    expect(reordered.ok).toBe(true);
    if (!reordered.ok) return;
    expect(findBlock(reordered.document, containerId)?.children.map((block) => block.id)).toEqual([
      secondTextId,
      firstTextId,
    ]);

    const protectedRegistry: PageBuilderRegistry = {
      ...registry,
      blocks: registry.blocks.map((definition) =>
        definition.type === 'layout.container' ? { ...definition, minChildren: 2 } : definition,
      ),
    };
    expect(deleteBlock(document, protectedRegistry, firstTextId)).toEqual({
      ok: false,
      reason: 'parent-minimum',
    });
  });

  it('duplicates every nested id and prevents collisions from a broken id factory', () => {
    const { registry, document, containerId } = nestedDocument();
    const duplicateIds = ids('container-copy-01', 'text-copy-0001', 'text-copy-0002');
    const duplicated = duplicateBlock(document, registry, containerId, duplicateIds);
    expect(duplicated.ok).toBe(true);
    if (!duplicated.ok) return;

    const allIds = flattenBlocks(duplicated.document).map((block) => block.id);
    expect(new Set(allIds).size).toBe(allIds.length);
    expect(allIds).toContain('container-copy-01');
    expect(allIds).toContain('text-copy-0001');
    expect(document.blocks[0].children).toHaveLength(1);

    const collision = duplicateBlock(document, registry, containerId, () => containerId);
    expect(collision).toEqual({ ok: false, reason: 'duplicate-id' });
  });

  it('updates responsive style and visibility immutably', () => {
    const { document, firstTextId } = nestedDocument();
    const styled = updateBlockDeviceStyle(document, firstTextId, 'mobile', 'spacing', 'sm');
    expect(styled.ok).toBe(true);
    if (!styled.ok) return;
    const hidden = updateBlockVisibility(styled.document, firstTextId, 'mobile', false);
    expect(hidden.ok).toBe(true);
    if (!hidden.ok) return;
    expect(findBlock(hidden.document, firstTextId)?.style.mobile['spacing']).toBe('sm');
    expect(findBlock(hidden.document, firstTextId)?.visibility.mobile).toBe(false);
    expect(findBlock(document, firstTextId)?.visibility.mobile).toBe(true);
  });
});

function nestedDocument(): {
  readonly registry: PageBuilderRegistry;
  readonly document: PageBuilderDocument;
  readonly containerId: string;
  readonly firstTextId: string;
  readonly secondTextId: string;
} {
  const registry = registryFixture();
  const section = addBlock(
    emptyPageBuilderDocument(1),
    registry,
    'layout.section',
    null,
    0,
    ids('section-0001'),
  );
  if (!section.ok) throw new Error(section.reason);
  const container = addBlock(
    section.document,
    registry,
    'layout.container',
    section.blockId,
    0,
    ids('container-0001'),
  );
  if (!container.ok) throw new Error(container.reason);
  const first = addBlock(
    container.document,
    registry,
    'content.text',
    container.blockId,
    0,
    ids('content-text-0001'),
  );
  if (!first.ok) throw new Error(first.reason);
  const second = addBlock(
    first.document,
    registry,
    'content.text',
    container.blockId,
    1,
    ids('content-text-0002'),
  );
  if (!second.ok) throw new Error(second.reason);

  return {
    registry,
    document: second.document,
    containerId: container.blockId,
    firstTextId: first.blockId,
    secondTextId: second.blockId,
  };
}

function registryFixture(): PageBuilderRegistry {
  return {
    document: {
      schemaVersion: 1,
      limits: { maxBytes: 524288, maxDepth: 12, maxBlocks: 300 },
      blockFields: ['id', 'type', 'version', 'props', 'style', 'visibility', 'bindings', 'children'],
      pageSettings: { container: ['default', 'wide', 'full'], background: ['surface', 'muted', 'brand'] },
    },
    blocks: [
      definition('layout.section', true, [], ['layout.container'], 0, 12),
      definition('layout.container', false, ['layout.section'], ['content.text'], 0, 12),
      definition('content.text', false, ['layout.container'], [], 0, 12),
    ],
    dataSources: [],
    forms: [],
    cache: {},
  };
}

function definition(
  type: string,
  allowRoot: boolean,
  allowedParents: readonly string[],
  allowedChildren: readonly string[],
  minChildren: number,
  maxDepth: number,
): PageBuilderBlockDefinition {
  return {
    type,
    version: 1,
    labels: { vi: type, en: type, zh: type },
    category: type.startsWith('layout') ? 'layout' : 'content',
    icon: 'widgets',
    thumbnail: null,
    schema: {
      props: { type: 'object', properties: {} },
      style: { type: 'object', properties: {} },
      visibility: { type: 'object', properties: {} },
      bindings: { type: 'object', properties: {} },
    },
    defaults: blockDefaults(),
    allowRoot,
    allowedParents,
    allowedChildren,
    maxDepth,
    minChildren,
    maxChildren: 20,
    dataDependencies: [],
    permissions: [],
    cacheTags: ['page-builder'],
  };
}

function blockDefaults(): Omit<PageBuilderBlock, 'id' | 'type' | 'version'> {
  return {
    props: {},
    style: { desktop: {}, tablet: {}, mobile: {} },
    visibility: { desktop: true, tablet: true, mobile: true },
    bindings: {},
    children: [],
  };
}

function ids(...values: readonly string[]): () => string {
  let index = 0;
  return () => values[Math.min(index++, values.length - 1)];
}
