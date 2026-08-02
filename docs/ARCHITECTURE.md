# KIẾN TRÚC TỔNG THỂ

```text
┌──────────────────────────────────────────────────────────────┐
│                         PUBLIC USER                          │
└──────────────────────────────┬───────────────────────────────┘
                               │ HTTPS
                               ▼
┌──────────────────────────────────────────────────────────────┐
│                    LARAVEL 13 / BLADE                        │
│  Public routes · SEO · Page renderer · Forms · Search        │
└───────────────┬───────────────────────────┬──────────────────┘
                │                           │
                │ Eloquent                  │ Queue / Cache
                ▼                           ▼
┌──────────────────────────┐     ┌─────────────────────────────┐
│      MySQL 8.4 LTS       │     │            Redis            │
│ all tables hongvan_*     │     │ cache · queue · preview     │
└──────────────────────────┘     └─────────────────────────────┘

┌──────────────────────────────────────────────────────────────┐
│                     ADMIN USER / /admin                      │
└──────────────────────────────┬───────────────────────────────┘
                               │ Angular SPA
                               ▼
┌──────────────────────────────────────────────────────────────┐
│                    ANGULAR 22 ADMIN                          │
│ template · RBAC · media · page builder · reports             │
└──────────────────────────────┬───────────────────────────────┘
                               │ same-origin cookie + CSRF
                               ▼
┌──────────────────────────────────────────────────────────────┐
│              /api/admin/v1 — LARAVEL API                     │
│ auth · policies · resources · actions · validation            │
└──────────────────────────────────────────────────────────────┘
```

## Monorepo

- `BackEnd/`: Laravel, public Blade và API.
- `Admin/`: Angular admin.
- `Template/`: source template admin chỉ đọc.
- `FrontEndTemplate/`: source template public chỉ đọc.
- `SourceIntegrations/`: source tham chiếu bên ngoài.
- `prompts/`: prompt tuần tự.
- `docs/`: quyết định, kiến trúc, quy tắc và tiến độ.
- `docker/`: hạ tầng phát triển/deploy.
- `scripts/`: build, verify và kiểm tra prefix.

## Bounded contexts Laravel

- Identity & Access.
- Settings.
- Media.
- Page Builder.
- Products.
- Crop Solutions.
- Services.
- Transportation.
- Warehouses.
- Leads.
- Content.
- Showcase.
- SEO.
- Analytics.
- Audit.
- Shared.

## Page Builder

Server giữ:

- Component registry.
- Schema.
- Validation.
- Blade renderer.
- Published versions.
- Preview rendering.

Angular giữ:

- Palette.
- Tree/canvas interaction.
- Property editor.
- Undo/redo.
- Responsive mode.
- Autosave orchestration.

Canvas chính dùng iframe Blade preview. Nhờ đó markup và CSS trong editor là đúng với website public, không phải bản mô phỏng Angular dễ lệch style.

## Luồng publish page

```text
Edit document
→ validate schema
→ autosave draft
→ create immutable version
→ preview
→ publish now/schedule
→ point page.published_version_id
→ invalidate cache
→ rebuild sitemap if needed
→ audit event
```

## Luồng lead

```text
Public form
→ validate + anti-spam + rate limit
→ create lead
→ notify assigned team
→ status history
→ internal notes
→ done/spam/archive
```
