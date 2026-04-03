# BÁO CÁO THỰC HÀNH — BAGISTO HEADLESS COMMERCE

---

## Câu 1: So sánh Payload giữa REST API và GraphQL

**REST API** trả về toàn bộ dữ liệu của một resource, bất kể client cần bao nhiêu trường. Ví dụ, khi gọi `GET /api/products`, server trả về object đầy đủ gồm hàng chục trường: `id`, `name`, `price`, `sku`, `weight`, `meta_title`, `meta_description`, `images`, `inventory`, `categories`... dù frontend chỉ cần `name` và `price`. Đây gọi là **over-fetching** — lãng phí băng thông.

**GraphQL** cho phép client khai báo chính xác trường cần lấy. Trong bài làm, Query 2 chỉ yêu cầu 5 trường (`id`, `name`, `price`, `description`, `url_key`), payload trả về nhỏ hơn đáng kể — ước tính **giảm 60–80% dung lượng** so với REST endpoint tương đương.

> **Kết luận:** GraphQL tối ưu payload theo nhu cầu thực tế, còn REST API gây dư thừa dữ liệu không cần thiết.

---

## Câu 2: Query hay Mutation để thay đổi giá sản phẩm?

Để thay đổi giá sản phẩm, cần sử dụng **Mutation**.

**Lý do:** Trong GraphQL, hai loại hành động được phân biệt rõ ràng theo mục đích:

| Loại | Mục đích | Tác động |
|------|----------|----------|
| `Query` | **Đọc** dữ liệu | Không thay đổi trạng thái server |
| `Mutation` | **Ghi / Thay đổi** dữ liệu | Cập nhật, tạo mới, xóa dữ liệu |

Việc thay đổi giá là hành động **ghi dữ liệu** vào database, do đó bắt buộc phải dùng `Mutation`. Ví dụ minh họa:

```graphql
mutation UpdateProductPrice($id: ID!, $price: Float!) {
  updateProduct(id: $id, input: { price: $price }) {
    id
    name
    price   # Giá sau khi cập nhật
  }
}
```

Dùng `Query` cho hành động ghi là **sai về ngữ nghĩa** và vi phạm nguyên tắc thiết kế GraphQL — Query phải là **idempotent** (gọi bao nhiêu lần kết quả không đổi), còn Mutation thì không.

---

*Họ tên: Bùi Minh Đức — MSSV: 23810310110*
