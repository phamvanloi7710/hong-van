import {
  JsonValue,
  PageBuilderBlock,
  PageBuilderBlockDefinition,
  PageBuilderDevice,
  PageBuilderDocument,
  PageBuilderRegistry,
} from './page-builder.models';

export type DocumentMutationFailure =
  | 'block-not-found'
  | 'definition-not-found'
  | 'duplicate-id'
  | 'invalid-parent'
  | 'invalid-depth'
  | 'parent-capacity'
  | 'parent-minimum'
  | 'inside-descendant'
  | 'document-limit';

export type DocumentMutationResult =
  | { readonly ok: true; readonly document: PageBuilderDocument; readonly blockId: string }
  | { readonly ok: false; readonly reason: DocumentMutationFailure };

export type BlockIdFactory = () => string;

interface BlockLocation {
  readonly block: PageBuilderBlock;
  readonly parentId: string | null;
  readonly index: number;
}

interface RemovedBlock {
  readonly document: PageBuilderDocument;
  readonly location: BlockLocation;
}

export function addBlock(
  document: PageBuilderDocument,
  registry: PageBuilderRegistry,
  definitionType: string,
  parentId: string | null,
  index: number,
  idFactory: BlockIdFactory = defaultBlockId,
): DocumentMutationResult {
  const definition = definitionFor(registry, definitionType);
  if (definition === undefined) return failure('definition-not-found');

  const usedIds = new Set(flattenBlocks(document).map((block) => block.id));
  const id = nextUniqueId(usedIds, idFactory);
  if (id === null) return failure('duplicate-id');

  const block: PageBuilderBlock = {
    id,
    type: definition.type,
    version: definition.version,
    props: structuredClone(definition.defaults.props),
    style: structuredClone(definition.defaults.style),
    visibility: structuredClone(definition.defaults.visibility),
    bindings: structuredClone(definition.defaults.bindings),
    children: structuredClone(definition.defaults.children),
  };

  return insertBlock(document, registry, block, parentId, index);
}

export function moveBlock(
  document: PageBuilderDocument,
  registry: PageBuilderRegistry,
  blockId: string,
  parentId: string | null,
  index: number,
): DocumentMutationResult {
  const source = findBlockLocation(document, blockId);
  if (source === null) return failure('block-not-found');
  if (parentId === blockId || descendants(source.block).some((block) => block.id === parentId)) {
    return failure('inside-descendant');
  }
  if (source.parentId !== parentId && !canRemoveFromParent(document, registry, source.parentId)) {
    return failure('parent-minimum');
  }

  const removed = removeBlock(document, blockId);
  if (removed === null) return failure('block-not-found');

  return insertBlock(removed.document, registry, removed.location.block, parentId, index);
}

export function duplicateBlock(
  document: PageBuilderDocument,
  registry: PageBuilderRegistry,
  blockId: string,
  idFactory: BlockIdFactory = defaultBlockId,
): DocumentMutationResult {
  const source = findBlockLocation(document, blockId);
  if (source === null) return failure('block-not-found');

  const usedIds = new Set(flattenBlocks(document).map((block) => block.id));
  const clone = cloneWithUniqueIds(source.block, usedIds, idFactory);
  if (clone === null) return failure('duplicate-id');

  return insertBlock(document, registry, clone, source.parentId, source.index + 1);
}

export function deleteBlock(
  document: PageBuilderDocument,
  registry: PageBuilderRegistry,
  blockId: string,
): DocumentMutationResult {
  const location = findBlockLocation(document, blockId);
  if (location === null) return failure('block-not-found');
  if (!canRemoveFromParent(document, registry, location.parentId)) return failure('parent-minimum');

  const removed = removeBlock(document, blockId);
  return removed === null
    ? failure('block-not-found')
    : { ok: true, document: removed.document, blockId };
}

export function updateBlockProperty(
  document: PageBuilderDocument,
  blockId: string,
  key: string,
  value: JsonValue,
): DocumentMutationResult {
  return updateBlock(document, blockId, (block) => ({
    ...block,
    props: { ...block.props, [key]: structuredClone(value) },
  }));
}

export function updateBlockDeviceStyle(
  document: PageBuilderDocument,
  blockId: string,
  device: PageBuilderDevice,
  key: string,
  value: JsonValue,
): DocumentMutationResult {
  return updateBlock(document, blockId, (block) => ({
    ...block,
    style: {
      ...block.style,
      [device]: { ...block.style[device], [key]: structuredClone(value) },
    },
  }));
}

export function updateBlockVisibility(
  document: PageBuilderDocument,
  blockId: string,
  device: PageBuilderDevice,
  visible: boolean,
): DocumentMutationResult {
  return updateBlock(document, blockId, (block) => ({
    ...block,
    visibility: { ...block.visibility, [device]: visible },
  }));
}

export function findBlock(document: PageBuilderDocument, blockId: string): PageBuilderBlock | null {
  return findBlockLocation(document, blockId)?.block ?? null;
}

export function blockBreadcrumbs(
  document: PageBuilderDocument,
  blockId: string,
): readonly PageBuilderBlock[] {
  const path: PageBuilderBlock[] = [];
  const visit = (blocks: readonly PageBuilderBlock[]): boolean => {
    for (const block of blocks) {
      path.push(block);
      if (block.id === blockId || visit(block.children)) return true;
      path.pop();
    }
    return false;
  };

  return visit(document.blocks) ? path : [];
}

export function flattenBlocks(document: PageBuilderDocument): readonly PageBuilderBlock[] {
  return document.blocks.flatMap((block) => [block, ...descendants(block)]);
}

export function canPlaceBlock(
  document: PageBuilderDocument,
  registry: PageBuilderRegistry,
  blockType: string,
  parentId: string | null,
  movingBlockId: string | null = null,
): boolean {
  const definition = definitionFor(registry, blockType);
  if (definition === undefined) return false;
  if (parentId === null) return definition.allowRoot;

  const parent = findBlock(document, parentId);
  if (parent === null || parent.id === movingBlockId) return false;
  const parentDefinition = definitionFor(registry, parent.type);
  if (parentDefinition === undefined) return false;

  const existingChildren = parent.children.filter((child) => child.id !== movingBlockId).length;
  return definition.allowedParents.includes(parent.type)
    && parentDefinition.allowedChildren.includes(blockType)
    && existingChildren < parentDefinition.maxChildren;
}

function insertBlock(
  document: PageBuilderDocument,
  registry: PageBuilderRegistry,
  block: PageBuilderBlock,
  parentId: string | null,
  index: number,
): DocumentMutationResult {
  if (flattenBlocks(document).some((item) => item.id === block.id)) return failure('duplicate-id');
  if (!canPlaceBlock(document, registry, block.type, parentId)) return failure('invalid-parent');
  if (flattenBlocks(document).length + descendants(block).length + 1 > registry.document.limits.maxBlocks) {
    return failure('document-limit');
  }

  const definition = definitionFor(registry, block.type);
  if (definition === undefined) return failure('definition-not-found');
  const targetDepth = parentId === null ? 0 : depthOf(document, parentId) + 1;
  const deepest = targetDepth + subtreeDepth(block);
  if (deepest > registry.document.limits.maxDepth || deepest > definition.maxDepth) {
    return failure('invalid-depth');
  }

  const updated = updateChildren(document, parentId, (children) => {
    const next = [...children];
    next.splice(clampIndex(index, next.length), 0, block);
    return next;
  });

  return updated === null
    ? failure('invalid-parent')
    : { ok: true, document: updated, blockId: block.id };
}

function updateBlock(
  document: PageBuilderDocument,
  blockId: string,
  updater: (block: PageBuilderBlock) => PageBuilderBlock,
): DocumentMutationResult {
  let found = false;
  const walk = (blocks: readonly PageBuilderBlock[]): readonly PageBuilderBlock[] =>
    blocks.map((block) => {
      if (block.id === blockId) {
        found = true;
        return updater(block);
      }
      const children = walk(block.children);
      return children === block.children ? block : { ...block, children };
    });
  const blocks = walk(document.blocks);

  return found
    ? { ok: true, document: { ...document, blocks }, blockId }
    : failure('block-not-found');
}

function updateChildren(
  document: PageBuilderDocument,
  parentId: string | null,
  updater: (children: readonly PageBuilderBlock[]) => readonly PageBuilderBlock[],
): PageBuilderDocument | null {
  if (parentId === null) return { ...document, blocks: updater(document.blocks) };

  let found = false;
  const walk = (blocks: readonly PageBuilderBlock[]): readonly PageBuilderBlock[] =>
    blocks.map((block) => {
      if (block.id === parentId) {
        found = true;
        return { ...block, children: updater(block.children) };
      }
      const children = walk(block.children);
      return children === block.children ? block : { ...block, children };
    });
  const blocks = walk(document.blocks);

  return found ? { ...document, blocks } : null;
}

function removeBlock(document: PageBuilderDocument, blockId: string): RemovedBlock | null {
  const location = findBlockLocation(document, blockId);
  if (location === null) return null;

  const updated = updateChildren(document, location.parentId, (children) =>
    children.filter((block) => block.id !== blockId),
  );

  return updated === null ? null : { document: updated, location };
}

function canRemoveFromParent(
  document: PageBuilderDocument,
  registry: PageBuilderRegistry,
  parentId: string | null,
): boolean {
  if (parentId === null) return true;
  const parent = findBlock(document, parentId);
  const definition = parent === null ? undefined : definitionFor(registry, parent.type);

  return parent !== null && definition !== undefined && parent.children.length - 1 >= definition.minChildren;
}

function findBlockLocation(
  document: PageBuilderDocument,
  blockId: string,
): BlockLocation | null {
  const visit = (blocks: readonly PageBuilderBlock[], parentId: string | null): BlockLocation | null => {
    for (let index = 0; index < blocks.length; index += 1) {
      const block = blocks[index];
      if (block.id === blockId) return { block, parentId, index };
      const nested = visit(block.children, block.id);
      if (nested !== null) return nested;
    }
    return null;
  };

  return visit(document.blocks, null);
}

function cloneWithUniqueIds(
  block: PageBuilderBlock,
  usedIds: Set<string>,
  idFactory: BlockIdFactory,
): PageBuilderBlock | null {
  const id = nextUniqueId(usedIds, idFactory);
  if (id === null) return null;
  usedIds.add(id);
  const children: PageBuilderBlock[] = [];
  for (const child of block.children) {
    const cloned = cloneWithUniqueIds(child, usedIds, idFactory);
    if (cloned === null) return null;
    children.push(cloned);
  }

  return { ...structuredClone(block), id, children };
}

function nextUniqueId(usedIds: Set<string>, idFactory: BlockIdFactory): string | null {
  for (let attempt = 0; attempt < 20; attempt += 1) {
    const candidate = idFactory();
    if (/^[A-Za-z0-9_-]{8,64}$/.test(candidate) && !usedIds.has(candidate)) return candidate;
  }
  return null;
}

function descendants(block: Pick<PageBuilderBlock, 'children'>): readonly PageBuilderBlock[] {
  return block.children.flatMap((child) => [child, ...descendants(child)]);
}

function depthOf(document: PageBuilderDocument, blockId: string): number {
  const path = blockBreadcrumbs(document, blockId);
  return Math.max(0, path.length - 1);
}

function subtreeDepth(block: PageBuilderBlock): number {
  return block.children.length === 0
    ? 0
    : 1 + Math.max(...block.children.map((child) => subtreeDepth(child)));
}

function definitionFor(
  registry: PageBuilderRegistry,
  type: string,
): PageBuilderBlockDefinition | undefined {
  return registry.blocks.find((definition) => definition.type === type);
}

function clampIndex(index: number, length: number): number {
  return Math.min(Math.max(0, index), length);
}

function failure(reason: DocumentMutationFailure): DocumentMutationResult {
  return { ok: false, reason };
}

function defaultBlockId(): string {
  return crypto.randomUUID();
}
