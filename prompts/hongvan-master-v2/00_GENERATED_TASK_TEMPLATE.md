# Gxxx — <Tên finding focused>

## Metadata

- Severity: Critical / High / Medium / Low
- Source audit round: <n>
- Depends on: <task IDs>
- Evidence HEAD: <sha>
- Scope: một finding độc lập

## Finding

- Hiện tượng:
- File/symbol/route/table:
- Root cause:
- Ảnh hưởng:
- Cách tái hiện:

## Công việc

1. Đọc đầy đủ hàm/class liên quan.
2. Sửa nhỏ nhất đúng contract.
3. Bổ sung regression test.
4. Chạy formatter/static analysis/build/E2E đúng phạm vi.
5. Cập nhật state/generated queue, commit, push và dừng.

## Không được làm

- Không sửa finding khác không liên quan.
- Không tắt security/test/budget.
- Không dùng pseudo-code hoặc stub.
- Không mark DONE nếu command chưa chạy.

## Definition of Done

- Finding không còn tái hiện.
- Regression test fail trước/fix sau hoặc evidence tương đương rõ ràng.
- Các gate liên quan pass.
- Không tạo regression module lân cận.
