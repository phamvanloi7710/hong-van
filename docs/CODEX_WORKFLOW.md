# CODEX WORKFLOW

## Một prompt — một checkpoint

Không chạy nhiều prompt một lúc. Mỗi prompt phải:

1. Đọc rules.
2. Kiểm tra working tree.
3. Lập kế hoạch ngắn.
4. Sửa đúng phạm vi.
5. Test.
6. Update state.
7. Báo cáo.
8. Dừng.

## Tìm kiếm có mục tiêu

Ưu tiên:

```bash
rg "symbol"
rg "hongvan_"
git diff --name-only
git diff -- path/to/file
```

Không dùng lệnh dump toàn repository hoặc mở hàng trăm file.

## State files

### `docs/CODEX_STATE.md`

Chứa trạng thái hiện tại, prompt gần nhất, test, blocker, next prompt.

### `docs/TASK_LEDGER.md`

Checklist dài hạn theo prompt.

### `docs/DECISIONS.md`

ADR ngắn cho quyết định có ảnh hưởng lâu dài.

## Báo cáo chuẩn

```text
Status:
Scope completed:
Files changed:
Database/API changes:
Commands run:
Tests/build:
Risks:
Deferred:
Next prompt:
```

## Xử lý lỗi

- Không che lỗi bằng cách tắt test.
- Không thay đổi dependency major để vượt lỗi.
- Thu nhỏ phạm vi, tìm root cause.
- Nếu phụ thuộc source chưa có, đánh dấu DEFERRED và ghi rõ dữ liệu còn thiếu.
- Nếu test fail do lỗi có trước, chứng minh bằng baseline và không sửa ngoài scope nếu không cần.

## Git

- `git status` trước và sau.
- Không chạm thay đổi người dùng không liên quan.
- Commit nhỏ sau khi pass.
- Không push tự động.
