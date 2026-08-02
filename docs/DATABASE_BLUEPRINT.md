# DATABASE BLUEPRINT

## Nguyên tắc

- Mọi bảng bắt đầu bằng `hongvan_`.
- Tên bảng số nhiều, snake_case.
- Không dùng connection prefix.
- Mọi foreign key có index.
- Unique constraint phản ánh đúng nghiệp vụ.
- Bảng nội dung có `created_by`, `updated_by` khi cần.
- Dùng soft delete cho nội dung có khả năng khôi phục.
- DB lưu UTC.
- Tiền dùng `decimal(18,2)` hoặc integer minor unit; không dùng float.
- ID nội bộ ưu tiên `unsignedBigInteger`.
- Entity public có thêm `public_id` dạng ULID để không lộ sequence.
- JSON chỉ dùng cho cấu trúc linh hoạt có schema, không thay thế dữ liệu cần query/report.

## Nền tảng đã triển khai qua P11

Database MySQL dùng `utf8mb4` với collation mặc định `utf8mb4_0900_ai_ci`; connection-level prefix luôn để rỗng. Tên bảng vật lý được ghi đầy đủ trong migration, model và cấu hình framework.

Các bảng đã có migration:

```text
hongvan_migrations
hongvan_users
hongvan_password_reset_tokens
hongvan_sessions
hongvan_cache
hongvan_cache_locks
hongvan_jobs
hongvan_job_batches
hongvan_failed_jobs
hongvan_notifications
hongvan_personal_access_tokens
hongvan_roles
hongvan_permissions
hongvan_role_user
hongvan_permission_role
hongvan_user_permission_overrides
hongvan_languages
hongvan_setting_groups
hongvan_settings
hongvan_user_preferences
hongvan_branches
hongvan_business_hours
hongvan_social_links
hongvan_contact_channels
```

Quy ước định danh được chốt tại `ADR-009`: entity nghiệp vụ dùng unsigned `BIGINT` làm khóa nội bộ và `CHAR(26)` ULID unique làm `public_id` khi cần xuất hiện bên ngoài. Foreign key nội bộ tham chiếu `id`.

P13 đã hoàn thiện foundation settings bằng allowlist có kiểu dữ liệu cho 11 nhóm/28 khóa; secret dùng ciphertext hoặc tham chiếu `env:VARIABLE` và không xuất hiện trong payload Admin/public. P13 cũng triển khai `hongvan_branches`, `hongvan_business_hours`, `hongvan_social_links` và `hongvan_contact_channels`, có trạng thái, thứ tự, khóa ngoại, index và comment đầy đủ. P10 đã ánh xạ Sanctum vào `hongvan_personal_access_tokens`. P11 đã triển khai role, permission, hai pivot và user permission override; tất cả quan hệ dùng khóa nội bộ, còn API chỉ nhận/trả `public_id`.

P12 đã triển khai `hongvan_user_preferences` để lưu cấu hình riêng từng user theo cặp `namespace` + `key`. Giá trị JSON chỉ nhận contract có allowlist; hiện gồm theme Annular, locale `vi|en|zh` và danh sách favorite menu có thứ tự. Unique key `(user_id, namespace, key)` ngăn dữ liệu trùng và foreign key xóa cascade theo user.

## Core và Identity dự kiến

```text
hongvan_migrations
hongvan_users
hongvan_password_reset_tokens
hongvan_sessions
hongvan_personal_access_tokens
hongvan_roles
hongvan_permissions
hongvan_role_user
hongvan_permission_role
hongvan_user_permission_overrides
hongvan_user_preferences
hongvan_audit_logs
hongvan_notifications
hongvan_jobs
hongvan_job_batches
hongvan_failed_jobs
hongvan_cache
hongvan_cache_locks
```

## Settings và locale

```text
hongvan_setting_groups
hongvan_settings
hongvan_languages
hongvan_translation_keys
hongvan_translation_values
hongvan_branches
hongvan_business_hours
hongvan_social_links
hongvan_contact_channels
```

## Media

```text
hongvan_media_folders
hongvan_media
hongvan_media_variants
hongvan_media_tags
hongvan_media_tag_links
hongvan_media_usages
hongvan_media_operations
```

## Theme và Page Builder

```text
hongvan_themes
hongvan_theme_versions
hongvan_pages
hongvan_page_translations
hongvan_page_versions
hongvan_page_publish_schedules
hongvan_page_locks
hongvan_page_templates
hongvan_page_template_versions
hongvan_page_preview_sessions
hongvan_menus
hongvan_menu_items
hongvan_global_regions
hongvan_global_region_versions
```

`hongvan_page_versions.document_json` chứa document có schema version và phải được validate bằng registry. Version đã publish không được update tại chỗ.

## Products

```text
hongvan_product_categories
hongvan_product_category_translations
hongvan_brands
hongvan_brand_translations
hongvan_products
hongvan_product_translations
hongvan_product_media
hongvan_product_tags
hongvan_product_tag_links
hongvan_product_attribute_definitions
hongvan_product_attribute_values
hongvan_product_specifications
hongvan_product_related
```

Các trường giá cốt lõi:

```text
price_mode
price_amount
price_min
price_max
currency
price_unit
price_note
is_price_visible
```

`price_mode` thuộc allowlist:

```text
fixed
from
range
market
dealer
quantity
contact
```

## Crop Solutions

```text
hongvan_crop_categories
hongvan_crop_category_translations
hongvan_crops
hongvan_crop_translations
hongvan_crop_stages
hongvan_crop_stage_translations
hongvan_crop_solutions
hongvan_crop_solution_translations
hongvan_crop_solution_products
```

## Services

```text
hongvan_service_categories
hongvan_service_category_translations
hongvan_services
hongvan_service_translations
hongvan_service_media
```

## Transportation

```text
hongvan_vehicle_types
hongvan_vehicles
hongvan_vehicle_media
hongvan_transport_routes
hongvan_transport_service_areas
hongvan_transport_requests
hongvan_transport_request_status_histories
```

## Warehouses

```text
hongvan_warehouses
hongvan_warehouse_translations
hongvan_warehouse_media
hongvan_warehouse_facilities
hongvan_warehouse_services
hongvan_warehouse_requests
hongvan_warehouse_request_status_histories
```

## Leads và forms

```text
hongvan_leads
hongvan_lead_assignments
hongvan_lead_status_histories
hongvan_lead_notes
hongvan_contact_requests
hongvan_quote_requests
hongvan_quote_request_items
hongvan_form_definitions
hongvan_form_versions
hongvan_form_submissions
hongvan_form_submission_values
```

Transport và warehouse request có thể liên kết đến lead chung để reporting thống nhất.

## Content và showcase

```text
hongvan_post_categories
hongvan_post_category_translations
hongvan_posts
hongvan_post_translations
hongvan_post_tags
hongvan_post_tag_links
hongvan_galleries
hongvan_gallery_translations
hongvan_gallery_items
hongvan_partners
hongvan_partner_translations
hongvan_certifications
hongvan_certification_translations
hongvan_projects
hongvan_project_translations
hongvan_project_media
```

## SEO, redirect và analytics

```text
hongvan_seo_meta
hongvan_redirects
hongvan_sitemap_exclusions
hongvan_search_logs
hongvan_page_view_daily
hongvan_consent_records
```

## Quy tắc translation table

Mỗi bảng translation có:

```text
id
<entity>_id
locale
name/title
slug
summary
content
seo fields when appropriate
timestamps
```

Unique tối thiểu:

```text
UNIQUE(<entity>_id, locale)
UNIQUE(locale, slug) trong đúng namespace
```

## Kiểm tra bắt buộc

- Script CI scan mọi `Schema::create`, `Schema::table`, package migration và model `$table`.
- Không được merge migration tạo bảng không prefix.
- Mọi bảng phải có table comment và mọi cột phải có column comment mô tả rõ mục đích, kể cả bảng/cột framework.
- Test `DatabaseCommentTest` không được phát hiện table comment hoặc column comment rỗng.
- Test migration fresh trên database rỗng.
- Test rollback theo batch.
