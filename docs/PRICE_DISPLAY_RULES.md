# QUY TẮC HIỂN THỊ GIÁ SẢN PHẨM

## Modes

| Code | Hiển thị |
|---|---|
| `fixed` | Một giá xác định |
| `from` | Giá từ |
| `range` | Khoảng giá |
| `market` | Giá theo thị trường |
| `dealer` | Liên hệ giá đại lý |
| `quantity` | Giá theo số lượng |
| `contact` | Liên hệ báo giá |

## Quy tắc bắt buộc

1. `price_amount` null hoặc <= 0 không được hiển thị `0đ`.
2. `fixed` cần amount hợp lệ; nếu không, fallback `contact`.
3. `from` cần min hợp lệ.
4. `range` cần min/max hợp lệ và min <= max.
5. `market`, `dealer`, `quantity`, `contact` không bắt buộc numeric price.
6. Có thể hiển thị `price_unit` như bao 50kg, kg, tấn, chai.
7. `price_note` dùng cho “giá tham khảo/có thể thay đổi”, không thay validation.
8. CTA luôn có: liên hệ báo giá, gọi điện, Zalo theo settings.
9. Không có Buy Now/Add to Cart/Checkout.
10. Product structured data không phát `Offer.price=0`.

## Test matrix tối thiểu

- fixed valid.
- fixed null.
- fixed zero.
- from valid/null.
- range valid/inverted.
- contact.
- market.
- dealer.
- quantity.
- hidden price.
- locale/currency/unit.
