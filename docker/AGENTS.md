# AGENTS.md — DOCKER/DEPLOYMENT

- Không hardcode secret.
- Image pin version/digest hợp lý.
- Container không chạy root nếu không cần.
- Healthcheck.
- Persistent volume rõ.
- Queue/scheduler tách process.
- Production không expose MySQL/Redis ra public.
- Public root là `BackEnd/public`.
