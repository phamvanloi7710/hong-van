export type ThemeTokenValue = string | number;
export type ThemeTokenGroupName = 'colors' | 'fonts' | 'sizes' | 'spacing' | 'radii' | 'shadows' | 'containers' | 'buttons' | 'headings' | 'sections' | 'animation';
export type ThemeTokens = Record<ThemeTokenGroupName, Record<string, ThemeTokenValue>>;

export interface ThemeVersionRecord {
  readonly public_id: string;
  readonly version_number: number;
  readonly status: 'draft' | 'published' | 'discarded';
  readonly checksum: string;
  readonly tokens?: ThemeTokens;
  readonly published_at: string | null;
  readonly updated_at: string | null;
}

export interface PublicThemeRecord {
  readonly public_id: string;
  readonly key: string;
  readonly name: string;
  readonly description: string | null;
  readonly is_active: boolean;
  readonly draft: ThemeVersionRecord & { readonly tokens: ThemeTokens };
  readonly published: (ThemeVersionRecord & { readonly tokens: ThemeTokens }) | null;
  readonly versions: readonly ThemeVersionRecord[];
}

export interface ThemeTokenControlSpec {
  readonly group: ThemeTokenGroupName;
  readonly key: string;
  readonly kind: 'color' | 'number' | 'select';
  readonly min?: number;
  readonly max?: number;
  readonly step?: number;
  readonly options?: readonly string[];
}

export const THEME_TOKEN_CONTROLS: readonly ThemeTokenControlSpec[] = [
  ...['brand', 'brand_strong', 'brand_deep', 'brand_soft', 'accent', 'surface', 'surface_muted', 'surface_dark', 'text', 'text_muted', 'border', 'focus'].map((key) => ({ group: 'colors' as const, key, kind: 'color' as const })),
  { group: 'fonts', key: 'body', kind: 'select', options: ['system_sans', 'serif'] },
  { group: 'fonts', key: 'heading', kind: 'select', options: ['system_sans', 'serif'] },
  ...['base', 'small', 'large', 'h1_min', 'h1_max', 'h2_min', 'h2_max'].map((key) => ({ group: 'sizes' as const, key, kind: 'number' as const, min: 10, max: 120 })),
  ...['xs', 'sm', 'md', 'lg', 'xl', '2xl', '3xl'].map((key) => ({ group: 'spacing' as const, key, kind: 'number' as const, min: 0, max: 240 })),
  ...['small', 'medium', 'large', 'pill'].map((key) => ({ group: 'radii' as const, key, kind: 'number' as const, min: 0, max: 999 })),
  { group: 'shadows', key: 'preset', kind: 'select', options: ['none', 'soft', 'raised'] },
  ...['max', 'narrow', 'gutter_min', 'gutter_max'].map((key) => ({ group: 'containers' as const, key, kind: 'number' as const, min: 8, max: 1920 })),
  { group: 'buttons', key: 'min_height', kind: 'number', min: 0, max: 120 },
  { group: 'buttons', key: 'horizontal_padding', kind: 'number', min: 0, max: 120 },
  { group: 'buttons', key: 'radius', kind: 'select', options: ['small', 'medium', 'large', 'pill'] },
  { group: 'buttons', key: 'font_weight', kind: 'number', min: 300, max: 900, step: 100 },
  { group: 'headings', key: 'font_weight', kind: 'number', min: 300, max: 900, step: 100 },
  { group: 'headings', key: 'line_height', kind: 'number', min: 1, max: 2, step: 0.05 },
  { group: 'sections', key: 'gap', kind: 'number', min: 0, max: 240 },
  { group: 'animation', key: 'preset', kind: 'select', options: ['none', 'subtle', 'standard'] },
];
