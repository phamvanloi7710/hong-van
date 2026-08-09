# Read-only Source Fingerprints — T004

Snapshot: 2026-08-09, base HEAD `62418a8448ccf112dc8b24c493f2a3e73351b70f`.

| Boundary | Files | Bytes | SHA-256 aggregate fingerprint |
| --- | ---: | ---: | --- |
| `Template/` | 271 | 568,065 | `cc31a2a5c1a276be1c235ba632c230a1667eb78282838c30dfe97157269c39be` |
| `FrontEndTemplate/` | 558 | 45,852,960 | `4f19254a2a039bb9edfb0821cb483cff40ac9df1457e00255ebb124b2f2b25dc` |
| `SourceIntegrations/` | 14,078 | 189,978,353 | `674ba23764bd4b428ef066aa3d31f796c05bb615519f274594ad7f15f46fc9dc` |

Fingerprint được tạo từ danh sách file sắp xếp ordinal, Git object hash của từng file và SHA-256 của manifest chuẩn hóa. PowerShell và Git Bash sinh cùng kết quả.

Root cause của baseline cũ: `FrontEndTemplate/` đã được owner cập nhật sau lần đóng dấu trước, trong khi `.readonly-sources.sha256` vẫn giữ hash cũ. T004 chỉ cập nhật hash tổng hợp sau khi đối chiếu; không sửa hoặc commit tài sản tham chiếu.

Guard hiện có:

- `scripts/verify-readonly-sources.ps1`
- `scripts/verify-readonly-sources.sh`

Hai script fail với exit code khác 0 khi source thiếu, chưa có baseline hoặc fingerprint thay đổi; `-PrintBaseline`/`--print-baseline` chỉ in fingerprint để review thủ công.
