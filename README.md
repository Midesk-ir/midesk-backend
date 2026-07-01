# Midesk Backend

بک‌اند API پلتفرم **Midesk** — سیستم یکپارچه مدیریت سازمان، پروژه، منابع انسانی، مالی و ارتباطات تیمی.

- **فریمورک:** Laravel 13
- **PHP:** 8.3+
- **مخزن:** [github.com/Midesk-ir/midesk-backend](https://github.com/Midesk-ir/midesk-backend)
- **مستندات فنی:** پوشه `midesk-docs` در ریپوی والد (فاز ۰ تا ۵)

---

## قابلیت‌ها

| فاز | ماژول‌ها |
|-----|---------|
| **۰** | احراز هویت (Sanctum / OTP / Google OAuth)، Multi-tenant، سازمان و تیم، پروژه و تسک، کانبان، داشبورد، اعلان‌ها |
| **۱** | اسپرینت، بک‌لاگ، ستون‌های کانبان + WIP، ورک‌لاگ، تقویم، تعطیلات، مرخصی |
| **۲** | پرسنل، حضور و غیاب، قرارداد، اسناد HR، مأموریت، استخدام، آنبوردینگ |
| **۳** | مالی (بودجه، هزینه، فاکتور، خرید، حقوق) + عملکرد (KPI، OKR، بازبینی) |
| **۴** | چت، جلسات (Jitsi / BBB / Google Meet)، AI، یادداشت |
| **۵** | مکاتبات، نظرسنجی، CRM، گزارش‌ساز، فایل پروژه، مهمان، اشتراک و پرداخت، سوپرادمین |

---

## پیش‌نیازها

- PHP 8.3+ با اکستنشن‌های `mbstring`, `pdo_mysql`, `redis`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`
- Composer 2
- MySQL 8+
- Redis (صف و کش)
- Node.js 20+ (اختیاری — فقط برای Vite)

---

## راه‌اندازی سریع (لوکال)

```bash
git clone git@github.com:Midesk-ir/midesk-backend.git
cd midesk-backend

composer install
cp .env.example .env
php artisan key:generate

# دیتابیس و Redis را در .env تنظیم کنید، سپس:
php artisan migrate:fresh --seed
php artisan serve
```

API در آدرس `http://localhost:8000/api/v1` در دسترس است.

---

## راه‌اندازی با Docker

```bash
docker compose up -d
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate:fresh --seed
```

| سرویس | آدرس |
|--------|------|
| API (nginx) | http://localhost:8080 |
| MySQL | `localhost:3307` (user/pass/db: `midesk` / `secret` / `midesk`) |
| Redis | `localhost:6380` |

برای محیط production از `docker/docker-compose.prod.yml` و اسکریپت‌های `docker/scripts/install.sh` و `update.sh` استفاده کنید.

---

## احراز هویت

API از **Laravel Sanctum** با Bearer token استفاده می‌کند.

```http
Authorization: Bearer {token}
X-Tenant-Id: {tenant_uuid}
Accept-Language: fa
```

### کاربران نمونه (بعد از seed)

| ایمیل | نقش | رمز |
|-------|-----|-----|
| `owner@midesk.test` | OWNER | `password` |
| `member@midesk.test` | MEMBER | `password` |
| `finance@midesk.test` | FINANCE | `password` |

### مسیرهای اصلی

| مسیر | توضیح |
|------|--------|
| `POST /api/v1/auth/register` | ثبت‌نام |
| `POST /api/v1/auth/login` | ورود |
| `GET /api/v1/core/bootstrap` | بوت‌استرپ اولیه (نیاز به auth + tenant) |
| `GET /api/v1/core/me` | پروفایل کاربر جاری |

---

## متغیرهای محیطی مهم

| متغیر | توضیح |
|-------|--------|
| `DB_*` | اتصال MySQL |
| `REDIS_*` | صف و کش |
| `OTP_PROVIDER` | `log` در توسعه؛ سرویس واقعی در production |
| `GOOGLE_CLIENT_*` | ورود با Google |
| `QUEUE_CONNECTION` | `redis` برای Jobها |
| `BROADCAST_CONNECTION` | `log` پیش‌فرض؛ Reverb برای real-time |

جزئیات بیشتر در `.env.example`.

---

## تست

```bash
php artisan test
```

۵۰ تست Feature پوشش احراز هویت، multi-tenant، HR، مالی، عملکرد، چت، مهمان و سوپرادمین را شامل می‌شود.

---

## ساختار پروژه

```
app/
  Http/Controllers/Api/V1/   # کنترلرهای API
  Models/                  # مدل‌ها + BelongsToTenant
  Policies/                # RBAC بر اساس نقش عضویت
  Services/                # منطق کسب‌وکار
routes/
  api.php                  # مسیرهای فاز ۰، ۴، ۵
  api_phase123.php         # مسیرهای فاز ۱، ۲، ۳
database/migrations/       # ۲۳ migration
tests/Feature/             # تست‌های Feature
docker/                    # پیکربندی production
```

---

## نقش‌های عضویت

`OWNER` · `ADMIN` · `MANAGER` · `HR_MANAGER` · `FINANCE` · `MEMBER`

دسترسی‌ها از طریق Laravel Policy و middleware `tenant` اعمال می‌شوند.

---

## لایسنس

MIT
