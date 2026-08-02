# AGENTS.md — BACKEND APP

- Domain logic đặt dưới `app/Domain/<Context>`.
- Không tạo God Service.
- DTO/Data object có type rõ.
- Action giải quyết một use case.
- Model không chứa controller logic.
- Event/listener chỉ dùng khi giúp tách side effect thật.
- Policy là nguồn authorization của resource.
- Không trả model trực tiếp từ API.
- Exception domain phải map sang response ổn định.
- Khi sửa method có sẵn, đọc và giữ đầy đủ method; không thay bằng đoạn rút gọn.
