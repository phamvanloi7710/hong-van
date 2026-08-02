# Scripts

Các script hiện có:

- `verify-prerequisites.ps1` và `verify-prerequisites.sh`: kiểm tra phiên bản PHP, Composer, Node.js, npm và Git; không cài đặt hoặc thay đổi môi trường.
- `verify-readonly-sources.ps1` và `verify-readonly-sources.sh`: đối chiếu dấu vân tay của `Template/`, `FrontEndTemplate/` và `SourceIntegrations/` với `.readonly-sources.sha256`.

Các script build/deploy sẽ chỉ được bổ sung tại prompt sở hữu tương ứng. Không script nào trong thư mục này được phép tự cài framework, package hoặc thay đổi database.
