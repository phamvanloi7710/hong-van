# DEFINITION OF DONE

Một task chỉ DONE/VERIFIED khi:

1. Scope task đã được audit ở HEAD hiện tại.
2. Implementation đúng contract và không có stub/pseudo-code.
3. Test tối thiểu đã chạy và pass.
4. Formatter/linter/static analysis/build liên quan pass.
5. Angular change đã `build:laravel` và sync output.
6. Migration change đã kiểm tra prefix/comment/fresh/rollback.
7. State, execution log và tài liệu liên quan đã cập nhật.
8. Diff sạch, không secret và không thay đổi read-only source.
9. Commit đã push main và local HEAD trùng origin/main.
10. Báo cáo nêu đúng blocker/rủi ro còn lại.

Task production/UAT/external không DONE nếu thiếu môi trường hoặc owner approval thật.
