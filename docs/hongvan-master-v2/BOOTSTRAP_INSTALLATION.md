# BOOTSTRAP INSTALLATION

Installer chỉ copy file, không chạy Git. Vì vậy sau khi cài, ba thư mục Master Pack sẽ xuất hiện dưới dạng file mới.

T001 có trách nhiệm:

1. Xác minh đủ 240 task và queue/state hợp lệ.
2. Đối chiếu HEAD hiện tại.
3. Stage toàn bộ:

```text
prompts/hongvan-master-v2/
docs/hongvan-master-v2/
scripts/hongvan-master-v2/
```

4. Commit cùng kết quả reconcile T001.
5. Push main và xác nhận HEAD.

Không cần người dùng commit pack thủ công trước T001.
