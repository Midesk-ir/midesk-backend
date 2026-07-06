# Midesk Site API Reference

English API catalog for **midesk-site** (Nuxt). Implemented by **midesk-backend** (Laravel 13).

## Base URL

```
http://localhost:8000/api/v1
```

Set in the frontend:

```env
NUXT_PUBLIC_API_BASE=http://localhost:8000/api/v1
```

## Authentication & Headers

| Header | Required | Description |
|--------|----------|-------------|
| `Authorization` | Tenant routes | `Bearer {sanctum_token}` |
| `X-Tenant-Id` | Tenant routes | Organization UUID |
| `Accept-Language` | Optional | `fa` or `en` |

### Auth flow

1. `POST /auth/login` or `POST /auth/verify-otp` → `{ token, user, tenant }`
2. `GET /core/bootstrap` → `{ user, tenant, permissions[] }`
3. Attach `Authorization` + `X-Tenant-Id` on tenant-scoped requests.

Super-admin panel: `POST /super-admin/auth/login` → `{ token }`.

## Response conventions

- Most endpoints return `{ data: ... }`.
- User: `{ id, name, email, phone, avatar_url, role }`.
- Bootstrap includes `permissions: string[]` for UI gating.
- Errors: `{ error: { code, message, details? } }`.

## Endpoints (175 total)

Paths below omit the `/api/v1` prefix for brevity.

### Authentication

| Method | Path |
|--------|------|
| `POST` | `/auth` |
| `POST` | `/auth/login` |

### AI Assistant

| Method | Path |
|--------|------|
| `GET` | `/ai/requests` |
| `GET` | `/ai/requests/{id}` |
| `POST` | `/ai` |
| `POST` | `/ai/requests/{id}` |

### Real-time Broadcasting

| Method | Path |
|--------|------|
| `POST` | `/broadcasting` |

### Calendar

| Method | Path |
|--------|------|
| `GET` | `/calendar` |
| `GET` | `/calendar/events` |
| `POST` | `/calendar/events` |

### Chat

| Method | Path |
|--------|------|
| `GET` | `/chat/channels` |
| `GET` | `/chat/channels/{id}` |
| `GET` | `/chat/channels/{id}/messages` |
| `POST` | `/chat/channels` |
| `POST` | `/chat/channels/{id}` |
| `POST` | `/chat/channels/{id}/messages` |
| `POST` | `/chat/messages/{id}` |

### Core / Session

| Method | Path |
|--------|------|
| `GET` | `/core` |
| `PATCH` | `/core` |

### Correspondence

| Method | Path |
|--------|------|
| `GET` | `/correspondences` |
| `GET` | `/correspondences/{id}` |
| `POST` | `/correspondences` |
| `POST` | `/correspondences/{id}` |
| `POST` | `/correspondences/{id}/routing/{routingId}` |

### CRM

| Method | Path |
|--------|------|
| `GET` | `/crm` |
| `GET` | `/crm/activities` |
| `GET` | `/crm/contacts` |
| `GET` | `/crm/contacts/{id}` |
| `GET` | `/crm/opportunities` |
| `PATCH` | `/crm/contacts/{id}` |
| `PATCH` | `/crm/opportunities/{id}` |
| `POST` | `/crm/activities` |
| `POST` | `/crm/contacts` |
| `POST` | `/crm/opportunities` |

### Dashboard

| Method | Path |
|--------|------|
| `GET` | `/dashboard` |

### Finance

| Method | Path |
|--------|------|
| `GET` | `/finance` |
| `GET` | `/finance/budgets` |
| `GET` | `/finance/cost-centers` |
| `GET` | `/finance/expenses` |
| `GET` | `/finance/invoices` |
| `GET` | `/finance/payroll` |
| `GET` | `/finance/purchase-orders` |
| `GET` | `/finance/purchase-requests` |
| `GET` | `/finance/vendors` |
| `PATCH` | `/finance/vendors` |
| `POST` | `/finance/budgets` |
| `POST` | `/finance/expenses` |
| `POST` | `/finance/expenses/{id}` |
| `POST` | `/finance/invoices` |
| `POST` | `/finance/invoices/{id}` |
| `POST` | `/finance/payroll` |
| `POST` | `/finance/payroll/{id}` |
| `POST` | `/finance/purchase-requests` |
| `POST` | `/finance/purchase-requests/{id}` |
| `POST` | `/finance/vendors` |

### Guest Access

| Method | Path |
|--------|------|
| `DELETE` | `/guests` |
| `GET` | `/guests` |
| `POST` | `/guests` |

### Human Resources

| Method | Path |
|--------|------|
| `DELETE` | `/hr/personnel` |
| `GET` | `/hr` |
| `GET` | `/hr/attendance` |
| `GET` | `/hr/candidates` |
| `GET` | `/hr/candidates/{id}` |
| `GET` | `/hr/contracts` |
| `GET` | `/hr/documents` |
| `GET` | `/hr/documents/{id}` |
| `GET` | `/hr/missions` |
| `GET` | `/hr/onboarding` |
| `GET` | `/hr/personnel` |
| `GET` | `/hr/requisitions` |
| `PATCH` | `/hr` |
| `PATCH` | `/hr/candidates/{id}` |
| `PATCH` | `/hr/contracts/{id}` |
| `PATCH` | `/hr/interviews` |
| `PATCH` | `/hr/missions/{id}` |
| `PATCH` | `/hr/onboarding/progress/{id}` |
| `PATCH` | `/hr/onboarding/templates` |
| `PATCH` | `/hr/personnel` |
| `POST` | `/hr/attendance` |
| `POST` | `/hr/candidates/{id}` |
| `POST` | `/hr/contracts` |
| `POST` | `/hr/documents` |
| `POST` | `/hr/interviews` |
| `POST` | `/hr/missions` |
| `POST` | `/hr/personnel` |
| `POST` | `/hr/requisitions` |

### Leave Management

| Method | Path |
|--------|------|
| `GET` | `/leave` |
| `GET` | `/leave/policies` |
| `GET` | `/leave/requests` |
| `PATCH` | `/leave/policies` |
| `PATCH` | `/leave/requests/{id}` |
| `POST` | `/leave/policies` |
| `POST` | `/leave/requests` |

### Meetings

| Method | Path |
|--------|------|
| `GET` | `/meetings` |
| `GET` | `/meetings/{id}` |
| `POST` | `/meetings` |
| `POST` | `/meetings/{id}` |

### Members

| Method | Path |
|--------|------|
| `GET` | `/members` |

### My Finance

| Method | Path |
|--------|------|
| `GET` | `/my-finance` |
| `GET` | `/my-finance/payslips` |

### Notes

| Method | Path |
|--------|------|
| `DELETE` | `/notes/{id}` |
| `GET` | `/notes` |
| `PATCH` | `/notes/{id}` |
| `POST` | `/notes` |
| `POST` | `/notes/{id}` |

### Notification Preferences

| Method | Path |
|--------|------|
| `GET` | `/notification-preferences` |
| `PATCH` | `/notification-preferences` |

### Notifications

| Method | Path |
|--------|------|
| `GET` | `/notifications` |
| `PATCH` | `/notifications/{id}` |
| `POST` | `/notifications` |

### Organization

| Method | Path |
|--------|------|
| `GET` | `/orgs` |
| `GET` | `/orgs/current` |
| `PATCH` | `/orgs` |
| `POST` | `/orgs` |
| `POST` | `/orgs/current/members` |

### Performance & OKR

| Method | Path |
|--------|------|
| `GET` | `/performance` |
| `GET` | `/performance/indicators` |
| `GET` | `/performance/objectives` |
| `GET` | `/performance/reviews` |

### Projects & Kanban

| Method | Path |
|--------|------|
| `DELETE` | `/projects/{id}/columns` |
| `DELETE` | `/projects/{id}/files/{fileId}` |
| `GET` | `/projects` |
| `GET` | `/projects/{id}` |
| `GET` | `/projects/{id}/columns` |
| `GET` | `/projects/{id}/files` |
| `GET` | `/projects/{id}/report` |
| `GET` | `/projects/{id}/sprints` |
| `PATCH` | `/projects/{id}/columns` |
| `PATCH` | `/projects/{id}/sprints/{sprintId}` |
| `POST` | `/projects` |
| `POST` | `/projects/{id}` |
| `POST` | `/projects/{id}/columns` |
| `POST` | `/projects/{id}/files` |
| `POST` | `/projects/{id}/files/{fileId}` |
| `POST` | `/projects/{id}/sprints` |

### Custom Reports

| Method | Path |
|--------|------|
| `GET` | `/reports` |
| `GET` | `/reports/{id}` |
| `POST` | `/reports` |

### Custom Roles

| Method | Path |
|--------|------|
| `DELETE` | `/roles/custom` |
| `GET` | `/roles` |
| `GET` | `/roles/custom` |
| `PATCH` | `/roles/custom` |
| `POST` | `/roles/custom` |

### Sprints & Agile

| Method | Path |
|--------|------|
| `GET` | `/sprints/{id}/report` |

### Subscription & Billing

| Method | Path |
|--------|------|
| `GET` | `/subscription` |
| `POST` | `/subscription` |

### Super Admin

| Method | Path |
|--------|------|
| `DELETE` | `/super-admin/plans` |
| `GET` | `/super-admin` |
| `GET` | `/super-admin/consultations` |
| `GET` | `/super-admin/financial` |
| `GET` | `/super-admin/plans` |
| `GET` | `/super-admin/settings` |
| `GET` | `/super-admin/tenants` |
| `PATCH` | `/super-admin/consultations` |
| `PATCH` | `/super-admin/plans` |
| `PATCH` | `/super-admin/settings` |
| `POST` | `/super-admin/auth` |
| `POST` | `/super-admin/plans` |
| `POST` | `/super-admin/tenants/{id}` |

### Surveys

| Method | Path |
|--------|------|
| `GET` | `/surveys` |
| `GET` | `/surveys/{id}` |
| `PATCH` | `/surveys/{id}` |
| `POST` | `/surveys` |
| `POST` | `/surveys/{id}` |

### Tasks

| Method | Path |
|--------|------|
| `GET` | `/tasks` |
| `GET` | `/tasks/labels` |
| `GET` | `/tasks/{id}` |
| `PATCH` | `/tasks` |
| `PATCH` | `/tasks/{id}` |
| `POST` | `/tasks/{id}` |

### Teams

| Method | Path |
|--------|------|
| `GET` | `/teams` |

### Worklogs

| Method | Path |
|--------|------|
| `DELETE` | `/worklogs` |
| `GET` | `/worklogs` |
| `PATCH` | `/worklogs` |
| `POST` | `/worklogs` |

## Site compatibility layer

Backend file `routes/api_site_compat.php` implements Nuxt-specific aliases:

| Method | Path | Purpose |
|--------|------|---------|
| GET | `/members` | List tenant users (site user shape) |
| GET | `/tasks/labels/all` | Alias for `/labels` |
| GET | `/worklogs/team` | Manager team worklogs |
| GET | `/leave/requests/pending` | Pending approvals |
| PATCH | `/leave/requests/{id}/approve` | Approve leave (alias) |
| PATCH | `/leave/requests/{id}/reject` | Reject leave (alias) |
| POST | `/hr/attendance/break-start` | Start break |
| POST | `/hr/attendance/break-end` | End break |
| PATCH | `/hr/missions/{id}/approve` | Approve mission |
| PATCH | `/hr/missions/{id}/reject` | Reject mission |
| GET | `/hr/dashboard` | HR summary widgets |
| GET | `/finance/dashboard` | Finance summary widgets |
| GET | `/crm/dashboard` | CRM funnel & pipeline |
| GET | `/performance/dashboard` | OKR / review summary |
| PATCH | `/projects/{project}/sprints/{sprint}/activate` | Activate sprint |
| PATCH | `/projects/{project}/sprints/{sprint}/close` | Close sprint |
| PATCH | `/projects/{project}/columns/reorder` | Reorder kanban columns |
| DELETE | `/projects/{project}/files/{file}` | Delete project file |
| POST | `/projects/{project}/files/{file}/new-version` | Upload new file version |
| POST | `/guests` | Invite guest (alias) |
| PATCH | `/crm/opportunities/{id}/stage` | Update CRM stage |
| POST | `/correspondences/{id}/routing/{routingId}/complete` | Complete routing |
| PATCH | `/notification-preferences/{eventType}` | Update one preference |
| POST | `/subscription/verify` | Checkout verification |
| GET | `/chat/channels/unread` | `{ count: number }` |
| POST | `/super-admin/auth/login` | Super-admin login alias |
| GET | `/super-admin/financial` | SaaS revenue stats |
| GET/PATCH | `/super-admin/settings` | Platform settings |
| GET/POST/PATCH/DELETE | `/roles/custom` | Custom RBAC roles |
| GET | `/roles/permissions` | All permission strings |
| POST | `/broadcasting/auth` | Echo auth stub |
| POST | `/ai/requests/{id}/complete` | Complete AI job |

## OpenAPI

With the backend running: `http://localhost:8000/docs/api` (Scramble / OpenAPI 3.1).
