# MASTER ROADMAP

Luồng mặc định là tuần tự để chủ dự án chỉ cần nói `tiếp`. Task đã đúng được VERIFIED; task thiếu/sai được sửa và DONE.

## Phase A: Quản trị, baseline và bộ nhớ dự án

- Phạm vi: `T001` → `T012`
- Số task: **12**

## Phase B: Nền tảng Laravel, Angular, Database và API

- Phạm vi: `T013` → `T030`
- Số task: **18**

## Phase C: Authentication, RBAC, Preferences, Settings, Localization và Audit

- Phạm vi: `T031` → `T050`
- Số task: **20**

## Phase D: Admin shell và Media Manager

- Phạm vi: `T051` → `T066`
- Số task: **16**

## Phase E: Public Blade foundation và Theme Studio

- Phạm vi: `T067` → `T080`
- Số task: **14**

## Phase F: Page Builder foundation, blocks, editor và preview

- Phạm vi: `T081` → `T106`
- Số task: **26**

## Phase G: Ổn định P28 và P29

- Phạm vi: `T107` → `T126`
- Số task: **20**

## Phase H: Menus, Global Regions và Public Routing

- Phạm vi: `T127` → `T142`
- Số task: **16**

## Phase I: Product Catalog hoàn chỉnh

- Phạm vi: `T143` → `T156`
- Số task: **14**

## Phase J: Crop Solutions, Services, Transportation và Warehouses

- Phạm vi: `T157` → `T184`
- Số task: **28**

## Phase K: Leads, News, Showcase và Search

- Phạm vi: `T185` → `T208`
- Số task: **24**

## Phase L: SEO, Analytics và Dashboard

- Phạm vi: `T209` → `T222`
- Số task: **14**

## Phase M: Seeders, QA, CI, Deployment, Operations, Security, UAT và Handover

- Phạm vi: `T223` → `T234`
- Số task: **12**

## Phase N: Rà soát lặp, sinh prompt còn thiếu và Release Gate

- Phạm vi: `T235` → `T240`
- Số task: **6**

## Vòng audit lặp

T235–T239 audit toàn source. Nếu có gap, T239 sinh generated prompts và queue. Sau khi chạy hết, audit lại. T240 chỉ mở khi vòng gần nhất có 0 gap.
