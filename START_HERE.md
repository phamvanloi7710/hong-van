# BẮT ĐẦU TỪ ĐÂY — HỒNG VÂN V2

> Template website public phải đặt tại `FrontEndTemplate/`. Không dùng tên thư mục cũ của bộ V1.

## 1. Giải nén

Giải nén project vào một thư mục riêng, ví dụ:

```text
D:\www\HongVan
```

File này phải tồn tại ngay tại root:

```text
D:\www\HongVan\AGENTS.md
```

## 2. Bổ sung source tham chiếu

```text
Template/                              Template Angular admin
FrontEndTemplate/                      Template website public
SourceIntegrations/StayHubMedia/       Source tham chiếu Media Manager
```

Ba thư mục trên là read-only. Không chép template vào `Admin/` hoặc Blade source đích bằng tay.

Không cần chép `node_modules`, `dist`, cache, `.git`, secret hoặc dữ liệu production.

## 3. Khởi tạo Git

```powershell
cd D:\www\HongVan
git init
git branch -M main
git add .
git commit -m "chore: initialize Hong Van prompt kit v2"
```

Source template mặc định bị ignore để bảo vệ tài sản có giấy phép.

## 4. Mở root bằng Codex

Codex phải nhìn thấy:

```text
AGENTS.md
prompts/
docs/
BackEnd/
Admin/
Template/
FrontEndTemplate/
```

## 5. Chạy prompt đầu tiên

```text
prompts/00_PROJECT_BASELINE_AND_REPOSITORY_AUDIT.md
```

Không yêu cầu Codex chạy tự động P00–P56. Mỗi prompt phải dừng ở checkpoint riêng.

## 6. Sau mỗi prompt

```powershell
git status --short --branch
git diff --check
git diff --stat
Get-Content .\docs\CODEX_STATE.md
Get-Content .\docs\TASK_LEDGER.md
```

Chỉ chạy prompt kế khi bước trước `DONE`, hoặc `DEFERRED` hợp lệ với P17/P19.

## 7. Hai prompt có thể tạm hoãn

- P17 nếu thiếu source tại `SourceIntegrations/StayHubMedia/`.
- P19 nếu thiếu source tại `FrontEndTemplate/`.

Phải quay lại hoàn tất trước UAT/production hoặc có acceptance chính thức.

## 8. Tài liệu cần đọc

```text
docs/IMPLEMENTATION_GUIDE_FROM_SCRATCH.md   Hướng dẫn từ đầu đến production
prompts/PROMPT_INDEX.md                     Danh sách 57 prompt và checkpoint
prompts/FULL_PROMPT_SEQUENCE.md             Toàn bộ nội dung prompt
prompts/RUN_PROMPT_TEMPLATE.md               Mẫu giao từng prompt cho Codex
```

## 9. Đích build

```text
Angular source:      Admin/
Angular build:       BackEnd/public/admin/browser/
Public Blade views:  BackEnd/resources/views/
```

Không chỉnh thủ công file trong output build.
