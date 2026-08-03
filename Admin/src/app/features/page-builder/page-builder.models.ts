export type JsonPrimitive = string | number | boolean | null;
export type JsonValue = JsonPrimitive | JsonObject | readonly JsonValue[];

export interface JsonObject {
  readonly [key: string]: JsonValue;
}

export type PageBuilderDevice = 'desktop' | 'tablet' | 'mobile';

export interface PageBuilderBlock {
  readonly id: string;
  readonly type: string;
  readonly version: number;
  readonly props: JsonObject;
  readonly style: Readonly<Record<PageBuilderDevice, JsonObject>>;
  readonly visibility: Readonly<Record<PageBuilderDevice, boolean>>;
  readonly bindings: JsonObject;
  readonly children: readonly PageBuilderBlock[];
}

export interface PageBuilderDocument {
  readonly schemaVersion: number;
  readonly themeVersionId: string | null;
  readonly pageSettings: {
    readonly container: 'default' | 'wide' | 'full';
    readonly background: 'surface' | 'muted' | 'brand';
    readonly hideHeader: boolean;
    readonly hideFooter: boolean;
  };
  readonly blocks: readonly PageBuilderBlock[];
}

export interface PageBuilderSchema {
  readonly type?: 'object' | 'array' | 'string' | 'number' | 'integer' | 'boolean';
  readonly properties?: Readonly<Record<string, PageBuilderSchema>>;
  readonly required?: readonly string[];
  readonly enum?: readonly JsonPrimitive[];
  readonly minLength?: number;
  readonly maxLength?: number;
  readonly minimum?: number;
  readonly maximum?: number;
  readonly min?: number;
  readonly max?: number;
  readonly additionalProperties?: boolean;
}

export interface PageBuilderBlockDefinition {
  readonly type: string;
  readonly version: number;
  readonly labels: Readonly<Record<'vi' | 'en' | 'zh', string>>;
  readonly category: string;
  readonly icon: string;
  readonly thumbnail: string | null;
  readonly schema: {
    readonly props: PageBuilderSchema;
    readonly style: PageBuilderSchema;
    readonly visibility: PageBuilderSchema;
    readonly bindings: PageBuilderSchema;
  };
  readonly defaults: Omit<PageBuilderBlock, 'id' | 'type' | 'version'>;
  readonly allowRoot: boolean;
  readonly allowedParents: readonly string[];
  readonly allowedChildren: readonly string[];
  readonly maxDepth: number;
  readonly minChildren: number;
  readonly maxChildren: number;
  readonly dataDependencies: readonly string[];
  readonly permissions: readonly string[];
  readonly cacheTags: readonly string[];
}

export interface PageBuilderRegistry {
  readonly document: {
    readonly schemaVersion: number;
    readonly limits: {
      readonly maxBytes: number;
      readonly maxDepth: number;
      readonly maxBlocks: number;
    };
    readonly blockFields: readonly string[];
    readonly pageSettings: {
      readonly container: readonly PageBuilderDocument['pageSettings']['container'][];
      readonly background: readonly PageBuilderDocument['pageSettings']['background'][];
    };
  };
  readonly blocks: readonly PageBuilderBlockDefinition[];
  readonly dataSources: readonly JsonObject[];
  readonly forms: readonly JsonObject[];
  readonly cache: JsonObject;
}

export interface PageTranslationRecord {
  readonly locale: 'vi' | 'en' | 'zh';
  readonly title: string;
  readonly navigation_label: string;
  readonly slug: string;
}

export interface PageVersionRecord {
  readonly public_id: string;
  readonly version_number: number;
  readonly status: 'draft' | 'published' | 'scheduled' | 'archived';
  readonly schema_version: number;
  readonly checksum: string;
  readonly document?: PageBuilderDocument;
  readonly published_at: string | null;
  readonly updated_at: string | null;
}

export interface PageRecord {
  readonly public_id: string;
  readonly code: string;
  readonly type: string;
  readonly status: string;
  readonly is_home: boolean;
  readonly translations: readonly PageTranslationRecord[];
  readonly draft: PageVersionRecord | null;
  readonly published: PageVersionRecord | null;
  readonly created_at: string | null;
  readonly updated_at: string | null;
}

export interface PagePreviewSession {
  readonly public_id: string;
  readonly token: string;
  readonly url: string;
  readonly expires_at: string;
  readonly ttl_seconds: number;
  readonly revision: number;
  readonly message_schema_version: number;
}

export interface PagePreviewMessage {
  readonly channel: 'hongvan.page-builder.preview';
  readonly schemaVersion: number;
  readonly type: 'preview.ready' | 'preview.block-selected';
  readonly token: string;
  readonly blockId?: string | null;
}

export interface PageBuilderDragPayload {
  readonly kind: 'palette' | 'block';
  readonly definitionType?: string;
  readonly blockId?: string;
}

export interface PageBuilderDropListData {
  readonly kind: 'palette' | 'document';
  readonly parentId: string | null;
  readonly itemIds: readonly string[];
}

export interface PageBuilderSchemaEntry {
  readonly key: string;
  readonly schema: PageBuilderSchema;
}

export function emptyPageBuilderDocument(schemaVersion: number): PageBuilderDocument {
  return {
    schemaVersion,
    themeVersionId: null,
    pageSettings: {
      container: 'default',
      background: 'surface',
      hideHeader: false,
      hideFooter: false,
    },
    blocks: [],
  };
}
