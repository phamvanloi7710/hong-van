# TESTING STRATEGY

## Backend

### Unit

- Price display resolver.
- Page document validation.
- Block registry.
- Slug/redirect logic.
- Permission resolver.
- SEO schema builder.
- Lead deduplication.
- Theme token validation.

### Feature/API

- Auth + CSRF.
- Permission matrix.
- CRUD từng domain.
- Public forms.
- Page draft/publish/rollback.
- Media upload authorization.
- Search/filter/sort allowlist.
- Validation response contract.
- Sitemap and redirects.

### Architecture

- Controllers không chứa business logic lớn.
- Models đúng prefix.
- Không có bảng không prefix.
- Domain dependency boundaries.
- Không có direct use của source template.

## Angular

- Data-access services.
- Guards/interceptors.
- Permission directive.
- Theme preference.
- Page builder reducer/state operations.
- Property editor schema mapping.
- Autosave.
- Media picker.
- Error/loading/empty states.

## E2E

- Login/logout.
- Role denied/allowed.
- Create product and public view.
- Create page via builder, preview, publish.
- Rollback page.
- Submit quote.
- Submit transport request.
- Media upload/select.
- User theme persistence.
- SEO metadata render.

## Visual regression

- Public page components ở desktop/tablet/mobile.
- Admin layout.
- Page Builder preview.
- Media manager after source clone.

Snapshot chỉ hỗ trợ, không thay thế assertion nghiệp vụ.
