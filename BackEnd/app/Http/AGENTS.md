# AGENTS.md — HTTP LAYER

- Controller chỉ parse orchestration, gọi Action/Service và trả Resource/Response.
- Validation trong Form Request.
- Authorization trước business action.
- Không query tùy ý từ request.
- Admin API v1 không trả Eloquent model thô.
- Public form endpoint phải rate limit/anti-spam/idempotency khi phù hợp.
- Error contract theo `docs/API_CONVENTIONS.md`.
