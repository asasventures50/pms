# PMS — Project Spec (Procurement Management System)

> ملف مرجعي مختصر للنقاش مع AI أو لصياغة الـ CV.  
> ليس توثيق تقني كامل — يصف **ماذا يفعل النظام** و**كيف منظّم** بدون تفاصيل كل ملف.

---

## 1) One-liner

نظام ويب لإدارة دورة المشتريات (Procurement) من طلب الشراء حتى الفاتورة، مع إدارة الموردين، الصلاحيات، والتتبع — مبني بـ **Laravel 12 + Blade/Alpine/Tailwind**.

**English CV line:**  
Built a full-stack Procurement Management System (Laravel) covering the end-to-end purchase lifecycle: PR → RFQ → Vendor Quotations → Selection → PO → Invoice, with RBAC, public vendor portals, Excel import/export, and activity auditing.

---

## 2) Tech stack

| Layer | Choice |
|--------|--------|
| Backend | PHP 8.2+, Laravel 12 |
| Auth | Laravel Breeze (session) |
| UI | Blade, Alpine.js, Tailwind, Vite |
| i18n | `lang/en` + `lang/ar` (واجهات عامة تدعم locale) |
| Files | Local + AWS S3 (`league/flysystem-aws-s3-v3`) |
| Excel | Maatwebsite Excel (export/import categories & vendors) |
| Domain | Enums + Service classes (Persistence, Code generators, Flow builders) |
| Access | Custom Roles/Permissions middleware (`permission:...`) |
| DB | Migrations (~94) — Eloquent models تحت `app/Models/Procurement/...` |

---

## 3) Core business flow (القلب)

المسار الرسمي لدورة الشراء مرتبط بـ **Procurement Request (PR)**:

```
PR → RFQ → Quotations → Selection → PO → Invoice
```

معرّف في `FlowStageKey`: `pr | rfq | quotations | selection | po | invoice`.

| Stage | Module | ماذا يعني |
|--------|--------|-----------|
| PR | Procurement Requests | طلب شراء داخلي: بنود، موافقات، مستندات، timeline، شروط دفع/احتفاظ |
| RFQ | RFQs | طلب عروض أسعار مرتبط ببنود PR + شروط عامة + موردين |
| Quotations | Vendor Quotations | جمع عروض الموردين (داخلي + رابط عام بالـ token) |
| Selection | Comparison | مقارنة العروض واختيار |
| PO | Purchase Orders | أمر شراء من السياق التجاري للـ PR/المورد |
| Invoice | Invoices | فواتير مرتبطة بـ PO / سياق المشروع والمنطقة |

**مسارات موازية / مساعدة:**
- **Schedule of Works (SOW):** جداول أعمال قابلة للربط/المapping مع بنود PR
- **Quick Receipts:** إيصالات سريعة بحدود يومية وحالات توقيع/اعتماد
- **Public forms:** تسجيل موردين + تقديم عرض سعر عبر invite link (بدون login)

---

## 4) Feature map (الموديولات)

### A) Access & ops
- **Users / Roles / Permissions** — CRUD + middleware per action
- **Activity Logs** — سجل نشاط + تقرير insights
- **Dashboard + Landing**

### B) Master data
- **Categories / Subcategories** — كتالوج، ربط بموردين، نقل subcategory، import/export Excel، quick-store
- **Projects / Zones** — مشاريع ومناطق + أكواد + quick-store
- **Countries / Cities (Geo)** — مواقع للموردين والتغطية
- **Vendors** — ملف مورد غني (حالة، نوع شركة، طرق دفع، تغطية، لغة، RFQ method، كتيبات، مواقع، business types، فئات) + import/export + search-for-select

### C) Procurement documents
- **Procurement Requests** — statuses: `draft | submitted | received | closed | cancelled`؛ موافقات بأدوار؛ مستندات داعمة؛ طباعة؛ flow view
- **RFQs** — بنود من PR، شروط عامة (locales)، snapshot مورد، مقارنة عروض، **Vendor Quotation Invites** (رابط عام + locale + status)
- **Vendor Quotations** — بنود، مستندات، توقيع، compliance
- **Purchase Orders** — بنود، حالة دفع، طباعة، snapshot مورد، خطوط من PR
- **Invoices** — بناء أسطر، عملة، ربط مشروع/منطقة، Excel export
- **Schedule of Works** — scope، شروط، طباعة، mapping لـ PR sections/items
- **Quick Receipts** — إنشاء/توقيع، حد يومي، مرفقات، حالات status

### D) Public (unauthenticated)
- Vendor self-registration (`/vendor-registration`) — throttled
- Public quotation form عبر token (`/vendor-quotation/{token}`) — locale middleware + throttle

---

## 5) Architecture style (كيف الشغل منظم)

نمط شائع في المشروع:

```
Controller → FormRequest → Service (Persistence / Query / Generator) → Model/Enum
Views: resources/views/procurement/<module>/
```

- **Services** تحت `app/Services/Procurement/{Module}/`  
  أمثلة: `*PersistenceService`, `*CodeGenerator`, `*PayloadResolver`, Flow builders, Excel processors
- **Enums** تحت `app/Enums/Procurement/...` لحالات ومستويات الأعمال
- **Exports** تحت `app/Exports/Procurement/`
- **صلاحيات دقيقة** على مستوى الـ route (`permission:rfqs.create` إلخ) + أحياناً `view-own`
- **طباعة** عبر routes `*/print` و labels services
- **لا SPA** — server-rendered Blade مع Alpine للتفاعل

عند طلب تعديل من AI: حدّد الموديول + الـ stage في الـ flow + هل التغيير UI / validation / persistence / permission.

---

## 6) Domain glossary (مصطلحات سريعة)

| Term | Meaning |
|------|---------|
| PR | Procurement Request — طلب شراء داخلي |
| RFQ | Request for Quotation — طلب عرض سعر للموردين |
| VQ / Quotation | عرض سعر من مورد |
| Invite | رابط عام لملء عرض السعر بدون حساب |
| PO | Purchase Order — أمر شراء |
| SOW | Schedule of Work — جدول أعمال |
| Quick Receipt | إيصال استلام سريع (مسار مختصر) |
| Zone | منطقة داخل مشروع |
| Buyer / PR Company | شركة المشتري ضمن المستند |
| Flow | شريط مراحل PR→Invoice |

---

## 7) Notable product capabilities (نقاط قوة)

1. دورة مشتريات كاملة مربوطة بـ PR وليس شاشات معزولة  
2. بوابة عامة للموردين (تسجيل + تقديم عرض)  
3. RBAC مخصص على مستوى الأكشن  
4. مقارنة عروض أسعار ودعوات RFQ بروابط  
5. Excel import/export للكتالوج والموردين  
6. ثنائية لغة (EN/AR) للنماذج العامة  
7. Activity audit trail  
8. طباعة مستندات (PR/PO/SOW/…)  
9. تخزين ملفات محلي أو S3  
10. حدود عمل (مثال: daily limit لـ Quick Receipts)

---

## 8) CV bullets (جاهزة للنسخ)

- Designed and implemented an end-to-end **Procurement Management System** in Laravel covering PR, RFQ, vendor quotations, PO, and invoicing.  
- Built **role-based access control** with fine-grained permissions across procurement modules.  
- Delivered **public vendor portals** for self-registration and token-based quotation submission (EN/AR).  
- Implemented domain services for persistence, document coding, quotation comparison, and procurement flow tracking.  
- Added **Excel import/export**, print-ready documents, file storage (S3-ready), and activity logging for auditability.

**عربي مختصر للـ CV:**  
تطوير نظام إدارة مشتريات متكامل (Laravel) يغطي دورة الشراء من طلب الشراء حتى الفاتورة، مع صلاحيات، بوابات عامة للموردين، استيراد/تصدير Excel، وتتبع نشاط.

---

## 9) كيف تناقش الـ AI عن المشروع

الصق هذا الملف (أو القسم 3+4) ثم اطلب مثلاً:

- «عدّل صلاحية إنشاء PO ليشمل view-own»  
- «أضف حالة جديدة لـ Quick Receipt»  
- «اربط Invite بالـ RFQ وأرسل locale عربي»  
- «اشرح لي flow الـ PR من الكود بدون ما تقرأ كل الملفات»

**مفاتيح مسارات مفيدة:**
- Routes: `routes/web.php`
- Controllers: `app/Http/Controllers/Procurement/...`
- Services: `app/Services/Procurement/...`
- Models: `app/Models/Procurement/...`
- Enums: `app/Enums/Procurement/...`
- Views: `resources/views/procurement/...`

---

## 10) Out of scope / غير موجود حالياً (مهم للصدق)

- README ما زال قالب Laravel الافتراضي (هذا الملف هو المرجع الحقيقي)
- لا Policies منفصلة تحت `app/Policies` — الاعتماد على middleware صلاحيات
- ليس React/Vue SPA
- ليس ERP مالي كامل (محاسبة عامة / مخزون متقدم) — تركيزه **Procurement ops**

---

*آخر تحديث للهيكل: مبني على مسح عالي المستوى للكود (Controllers / Services / Enums / Routes / Views).*
