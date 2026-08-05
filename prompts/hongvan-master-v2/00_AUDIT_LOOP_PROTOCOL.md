# AUDIT LOOP PROTOCOL

Áp dụng cho `T235 → T239` và generated tasks.

## Vòng đầu

Sau T234, chạy T235, T236, T237, T238, T239.

## Khi T239 tìm thấy gap

1. Tạo từng prompt độc lập theo `00_GENERATED_TASK_TEMPLATE.md`.
2. Ghi generated queue với trạng thái `PENDING`.
3. Đặt:

```json
{
  "generated_queue_status": "PENDING",
  "audit_recheck_required": true,
  "last_zero_gap_audit_at": null
}
```

4. Mark T239 `DONE` vì đã hoàn thành việc phát hiện/sinh prompt, nhưng **không mở T240**.
5. Commit, push và dừng.

## Sau khi chạy hết generated tasks

Generated task cuối phải:

1. Đặt generated queue thành `COMPLETED`.
2. Giữ `audit_recheck_required=true`.
3. Reset state của T235–T239 thành `PENDING` cho vòng audit mới, giữ lịch sử vòng trước trong execution log.
4. Tăng `audit_round` lên 1.
5. Dừng.

## Khi vòng audit mới tìm thấy 0 gap

T239 phải:

1. Đặt generated queue thành `EMPTY`.
2. Đặt `audit_recheck_required=false`.
3. Ghi `last_zero_gap_audit_at` và HEAD đã audit.
4. Mark T235–T239 DONE/VERIFIED cho vòng hiện tại.
5. Chỉ lúc này T240 mới được phép chạy.

## Giới hạn trung thực

- Không giới hạn cứng số vòng audit chỉ để sớm kết thúc.
- Nếu một finding không thể sửa do external blocker, generated task phải mark BLOCKED_EXTERNAL và T240 phải NO-GO.
- Không gộp findings không liên quan thành mega-prompt.
