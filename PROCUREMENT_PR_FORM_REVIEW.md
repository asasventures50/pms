# مراجعة Procurement Request (PR Form) — محدّثة حسب الكود الحالي

**التاريخ:** 2026-06-09  
**الحالة:** مُدمَج في `main` (commit `310f673` + متابعات `9c1d94a`, `6757d64`, `f713fcb`)

---

## ملخص التغييرات

### الهدف
تحويل نموذج **Procurement Request** من نموذج بسيط (بنود فقط) إلى نموذج PR كامل يشمل:

- **معلومات الرأس (Header):** مشروع، zone، category/subcategory، أنواع المشتريات، النطاق الجغرافي، نوع المورد، justification، delivery، currency، samples، scope of work
- **التأمين والضمان:** NDA (في القسم الداخلي)، primary/final insurance + حقول المتطلبات النصية، warranty
- **BOQ:** بنود مع `item_name`, `unit_price`, `total_price` + اختيار `currency_code`
- **Payment terms & Retentions:** جداول فرعية ديناميكية
- **Timeline & Approvals:** 8 أنشطة timeline + 4 أدوار موافقة
- **Supporting documents:** على مستوى الـ header (ملفات S3 أو روابط URL) بالإضافة لمستندات البنود القديمة (عرض فقط)
- **Print view:** أقسام كاملة + عرض العملة بجانب أسعار BOQ
- **Show view:** عرض الأقسام الجديدة عبر `ProcurementRequestFormDataResolver`

### Backend

| المكوّن | التفاصيل |
|---------|----------|
| **Migration** `2026_06_08_150000_expand_procurement_requests_for_pr_form.php` | أعمدة جديدة على `procurement_requests` و `procurement_request_items`، 4 جداول فرعية، إعادة `procurement_request_id` على المستندات، **ترحيل بيانات** من أول بند لكل PR |
| **Migration** `2026_06_08_160000_add_insurance_requirements_to_procurement_requests.php` | إضافة idempotent لـ `primary_insurance_requirements` و `final_insurance_requirements` إذا نُفّذت migration التوسيع سابقاً بدونهما |
| **Migration** `2026_06_09_140000_add_pr_commercial_terms_to_purchase_orders.php` | نسخ الشروط التجارية من PR إلى PO: `retentions` (JSON)، `show_retention`، `primary/final_insurance_*`، `show_insurance` |
| **Enums** (5 جديدة) | `ProcurementType`, `GeographicScope`, `ProcurementVendorType`, `ProcurementTimelineActivity`, `ProcurementApprovalRole` (+ `ProcurementRequestStatus` موجود مسبقاً) |
| **Models** (4 جديدة) | `ProcurementRequestPaymentTerm`, `ProcurementRequestRetention`, `ProcurementRequestTimelineEntry`, `ProcurementRequestApproval` |
| **Services** | `ProcurementRequestFormDataResolver`, `ProcurementRequestLegacyItemSync`, `ProcurementRequestPayloadResolver`, `ProcurementRequestRequestorResolver`, توسيع `ProcurementRequestPersistenceService` و `ProcurementRequestSupportingDocumentStorage` |
| **Requests** | 3 Concerns مستخدمة في Store/Update: `NormalizesProcurementCheckboxFields`, `PreparesHeaderSupportingDocuments`, `ValidatesProcurementRequestHeader` |
| **PO integration** | `PurchaseOrderProcurementRequestContext` يقرأ من header مع fallback للبنود؛ `ProcurementRequestLinesForPurchaseOrderPresenter` يُرجع `context`, `items`, و `commercial_terms` عبر `ProcurementRequestCommercialTermsForPurchaseOrder`؛ `LegacyItemSync` يحافظ على حقول البنود القديمة لتوافق RFQ/PO |

### Frontend (Blade)

تقسيم النموذج إلى partials: `_pr-information`, `_boq`, `_payment-terms`, `_retentions`, `_insurance`, `_justification-delivery`, `_supporting-documents`, `_internal-sections` (timeline + NDA + approvals), `_show-sections`, print `_sections` + `_items` (مع لاحقة العملة).

**حقول إلزامية في التحقق:** `project_id`, `category_id`, `geographic_scopes` (min:1), `delivery_location`, وبند BOQ واحد على الأقل (`description`).

---

## هل في خطورة على Production؟

### مستوى الخطورة: **منخفض–متوسط** (ليس breaking إذا نُفّذ migrate بنجاح)

| ✅ آمن نسبياً | ⚠️ انتبه |
|-------------|---------|
| Migrations تستخدم `hasTable` / `hasColumn` — قابلة لإعادة التشغيل جزئياً | **تعديل PR قديم** يتطلب حقولاً جديدة إلزامية (`geographic_scopes`، `project_id`، …) — الـ migration **لا** تملأ `procurement_types` ولا `geographic_scopes` |
| ترحيل البيانات: project/zone/category من أول بند + vendor_types من `scope_type` | مطابقة category/subcategory بالاسم — إذا الاسم في البند لا يطابق DB → `category_id` يبقى `null` |
| `ProcurementRequestLegacyItemSync` يحافظ على توافق RFQ/PO | Migration تمر على **كل** PR وتُدخل timeline + approvals — على DB كبيرة قد تأخذ دقائق |
| لا routes جديدة للـ PR ولا permissions جديدة للنموذج | مستندات الـ header ترفع على **S3** — لازم credentials شغالة |
| PO الجديد يأخذ commercial terms من PR عند الربط | migration PO (`2026_06_09_140000`) تضيف أعمدة على `purchase_orders` — لازم تُشغَّل مع باقي migrations |
| `down()` على migration التوسيع معقد — rollback يدوي غير موصى به | |

### ما **لن** ينكسر (إذا migrate نجح)

- قائمة PR (index) — eager load `project` + `items.required_delivery_date`
- PO المرتبط بـ PR — context من header + commercial terms snapshot
- مستندات البنود القديمة — تبقى مربوطة بـ `procurement_request_item_id`

### سيناريوهات مشاكل محتملة

1. **Migrate يفشل** إذا `project_id` موجود مسبقاً لكن أعمدة أخرى ناقصة (نادر — الـ guard على `project_id` فقط للبلوك كامل).
2. **Edit PR قديم** → validation error لأن `geographic_scopes` فارغ.
3. **S3 down** → رفع مستندات header يفشل بـ RuntimeException.
4. **إنشاء PO من PR** بدون تشغيل migration الشروط التجارية → أعمدة `retentions` / `show_insurance` ناقصة.

---

## ماذا تحتاج عند الرفع؟

### ✅ المطلوب

```bash
php artisan migrate
php artisan config:cache    # إذا production
php artisan view:cache      # إذا production
php artisan route:cache     # إذا production
```

### ❌ **لا** تحتاج

| الأمر | السبب |
|-------|-------|
| `migrate:fresh` | يمسح كل البيانات — **ممنوع** على production |
| `db:seed` | لا seeder جديد للـ permissions أو بيانات PR |
| `storage:link` | الملفات على S3 |
| `npm run build` | لا تغييرات Vite/JS assets أساسية (Blade + inline scripts فقط) |
| Queue restart | لا jobs جديدة |

### قبل الرفع (checklist سريع)

- [ ] نسخة احتياطية DB
- [ ] تشغيل migrate على staging أولاً (3 migrations ذات صلة: `150000`, `160000`, `140000` على PO)
- [ ] التأكد من S3 credentials على السيرفر
- [ ] فتح PR قديم + جديد وتجربة create/edit/print/show
- [ ] تجربة PO مربوط بـ PR (سطور + payment terms + retentions + insurance)
- [ ] تشغيل `ProcurementRequestCommercialTermsForPurchaseOrderTest`

---

## الملفات الرئيسية (في المستودع)

```
app/Enums/Procurement/ProcurementRequests/
    GeographicScope.php, ProcurementType.php, ProcurementVendorType.php
    ProcurementTimelineActivity.php, ProcurementApprovalRole.php

app/Http/Requests/Procurement/ProcurementRequests/Concerns/
    NormalizesProcurementCheckboxFields.php
    PreparesHeaderSupportingDocuments.php
    ValidatesProcurementRequestHeader.php

app/Models/Procurement/ProcurementRequests/
    ProcurementRequestPaymentTerm.php
    ProcurementRequestRetention.php
    ProcurementRequestTimelineEntry.php
    ProcurementRequestApproval.php

app/Services/Procurement/ProcurementRequests/
    ProcurementRequestFormDataResolver.php
    ProcurementRequestLegacyItemSync.php
    ProcurementRequestPersistenceService.php
    ProcurementRequestPayloadResolver.php
    ProcurementRequestRequestorResolver.php
    ProcurementRequestSupportingDocumentStorage.php

app/Services/Procurement/PurchaseOrders/
    PurchaseOrderProcurementRequestContext.php
    ProcurementRequestLinesForPurchaseOrderPresenter.php
    ProcurementRequestCommercialTermsForPurchaseOrder.php

app/Support/Procurement/ProcurementCheckboxGroup.php

database/migrations/
    2026_06_08_150000_expand_procurement_requests_for_pr_form.php
    2026_06_08_160000_add_insurance_requirements_to_procurement_requests.php
    2026_06_09_140000_add_pr_commercial_terms_to_purchase_orders.php

resources/views/procurement/procurement-requests/
    _pr-information, _boq, _boq-row, _payment-terms, _retentions, _insurance
    _justification-delivery, _supporting-documents, _internal-sections, _show-sections
    print/_sections, print/_items

tests/Unit/ProcurementRequestCommercialTermsForPurchaseOrderTest.php
```

**ملاحظة:** `_line-items.blade.php` و `_line-item-card.blade.php` ما زالتا موجودتين (legacy) لكن النموذج الحالي يستخدم `_boq`.

---

## Routes (بدون تغيير permissions)

| Method | URI | الاسم |
|--------|-----|-------|
| resource | `/procurement-requests` | `procurement-requests.*` |
| GET | `/procurement-requests/{id}/print` | `procurement-requests.print` |
| GET | `/procurement-requests/{id}/purchase-order-lines` | `procurement-requests.purchase-order-lines` |

---

## الخلاصة

| السؤال | الجواب |
|--------|--------|
| آمن للـ production؟ | **نعم** مع migrate + backup + اختبار staging |
| migrate فقط؟ | **نعم** (3 migrations ذات صلة) |
| fresh / seed؟ | **لا** |
| أكبر مخاطرة عملية؟ | تعديل PRs قديمة بدون `geographic_scopes` + وقت migrate على DB كبيرة |
| المستند يطابق الكود؟ | **نعم** — محدّث 2026-06-09 |
