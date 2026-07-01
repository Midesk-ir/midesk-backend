# Midesk API — OpenAPI

مستندات قرارداد API پلتفرم Midesk به فرمت **OpenAPI 3.1**.

## فایل‌ها

| فایل | توضیح |
|------|--------|
| [`openapi.json`](openapi.json) | مشخصات کامل API (مسیرها، request/response schema، خطاها) |

## مشاهده تعاملی (لوکال)

با اجرای سرور Laravel:

```bash
php artisan serve
```

مستندات تعاملی در آدرس زیر در دسترس است:

- **UI:** http://localhost:8000/docs/api
- **JSON زنده:** http://localhost:8000/docs/api.json

در محیط `local` بدون محدودیت باز می‌شود.

## به‌روزرسانی مستندات

پس از تغییر route، controller، FormRequest یا Resource:

```bash
composer docs:openapi
# یا
php artisan scramble:export
```

فایل `docs/openapi.json` بازتولید می‌شود.

## احراز هویت در Try It

1. `POST /v1/auth/login` با ایمیل/رمز seed (مثلاً `owner@midesk.test` / `password`)
2. `access_token` را در Authorize به‌صورت Bearer قرار دهید
3. برای endpointهای tenant، هدر `X-Tenant-Id` را از پاسخ bootstrap یا دیتابیس `tenants` بگیرید

## ابزارهای سازگار

- [Stoplight Elements](https://stoplight.io/open-source/elements) (پیش‌فرض Scramble)
- [Swagger Editor](https://editor.swagger.io/) — import `openapi.json`
- [Postman](https://www.postman.com/) — Import → OpenAPI 3
- [Insomnia](https://insomnia.rest/) — Import OpenAPI

## پوشش

حدود **۲۶۰+** operation از تمام فازهای ۰ تا ۵ (Auth، Projects، HR، Finance، Chat، CRM، Super Admin و …) با schemaهای استخراج‌شده از FormRequest و API Resource.
