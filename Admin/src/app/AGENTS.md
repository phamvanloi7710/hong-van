# AGENTS.md — ANGULAR APP

- `core/`: singleton infrastructure.
- `shared/`: reusable, không chứa business của một feature.
- `features/`: bounded UI feature.
- Không import feature này xuyên sâu vào feature khác; chia sẻ contract qua core/shared khi thật sự chung.
- Signals cho state cục bộ; tránh state global không cần.
- Error/loading/empty/permission-denied state là bắt buộc.
- Subscription phải cleanup an toàn.
- Không hardcode API URL hoặc company contact.
