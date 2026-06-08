# مراجعة قبل Commit — توسيع نموذج Procurement Request (PR Form)

**التاريخ:** 2026-06-08  
**النطاق:** ~20 ملف معدّل + ~30 ملف جديد (غير متتبّع)

---

## ملخص التغييرات

### الهدف
تحويل نموذج **Procurement Request** من نموذج بسيط (بنود فقط) إلى نموذج PR كامل يشمل:

- **معلومات الرأس (Header):** مشروع، zone، category/subcategory، أنواع المشتريات، النطاق الجغرافي، نوع المورد، justification، delivery، currency، samples، scope of work
- **التأمين والضمان:** NDA، primary/final insurance، warranty
- **BOQ:** بنود مع `item_name`, `unit_price`, `total_price`
- **Payment terms & Retentions:** جداول فرعية ديناميكية
- **Timeline & Approvals:** 8 أنشطة timeline + 4 أدوار موافقة
- **Supporting documents:** على مستوى الـ header (بالإضافة للبنود)
- **Print view:** تحديث كامل للطباعة (sections, signatures, styles)
- **Show view:** عرض الأقسام الجديدة

### Backend

| المكوّن | التغيير |
|---------|---------|
| **Migration** `2026_06_08_150000_expand_procurement_requests_for_pr_form.php` | أعمدة جديدة على `procurement_requests` و `procurement_request_items`، 4 جداول فرعية، إعادة `procurement_request_id` على المستندات، **ترحيل بيانات** من أول بند لكل PR |
| **Enums** (5) | `ProcurementType`, `GeographicScope`, `ProcurementVendorType`, `ProcurementTimelineActivity`, `ProcurementApprovalRole` |
| **Models** (4 جديدة) | `PaymentTerm`, `Retention`, `TimelineEntry`, `Approval` |
| **Services** | `ProcurementRequestFormDataResolver`, `ProcurementRequestLegacyItemSync`, توسيع `PersistenceService` (+420 سطر) |
| **Requests** | Concerns للـ validation/normalization؛ حقول إلزامية جديدة (`project_id`, `category_id`, `geographic_scopes`, `delivery_location`, …) |
| **PO integration** | `PurchaseOrderProcurementRequestContext` و `ProcurementRequestLinesForPurchaseOrderPresenter` يقرأون من header مع fallback للبنود + `LegacyItemSync` يحافظ على حقول البنود القديمة |

### Frontend (Blade)

تقسيم النموذج إلى partials: `_pr-information`, `_boq`, `_payment-terms`, `_retentions`, `_insurance`, `_justification-delivery`, `_supporting-documents`, `_internal-sections`, `_show-sections`, print `_sections`.

**إحصائيات diff:** ~1,463 إضافة / ~837 حذف (بدون الملفات الجديدة غير المتتبّعة).

---

## هل في خطورة على Production؟

### مستوى الخطورة: **منخفض–متوسط** (ليس breaking إذا نُفّذ migrate بنجاح)

| ✅ آمن نسبياً | ⚠️ انتبه |
|-------------|---------|
| Migration تستخدم `hasTable` / `hasColumn` — قابلة لإعادة التشغيل جزئياً | **تعديل PR قديم** يتطلب حقولاً جديدة إلزامية (`geographic_scopes`، `project_id`، …) — الـ migration **لا** تملأ `procurement_types` ولا `geographic_scopes` |
| ترحيل البيانات: project/zone/category من أول بند + vendor_types من `scope_type` | مطابقة category/subcategory بالاسم — إذا الاسم في البند لا يطابق DB → `category_id` يبقى `null` |
| `ProcurementRequestLegacyItemSync` يحافظ على توافق RFQ/PO | Migration تمر على **كل** PR وتُدخل timeline + approvals — على DB كبيرة قد تأخذ دقائق (بدون downtime logic) |
| لا routes جديدة ولا permissions جديدة | مستندات الـ header ترفع على **S3** — لازم credentials شغالة |
| لا حذف جداول ولا `migrate:fresh` | `down()` على migration معقد — rollback يدوي غير موصى به |

### ما **لن** ينكسر (إذا migrate نجح)

- قائمة PR (index) — تغييرات بسيطة ( eager load `project`)
- PO المرتبط بـ PR — تم تحديث الـ context ليقرأ من header
- مستندات البنود القديمة — تبقى مربوطة بـ `procurement_request_item_id`

### سيناريوهات مشاكل محتملة

1. **Migrate يفشل** إذا `project_id` موجود مسبقاً لكن أعمدة أخرى ناقصة (نادر — الـ guard على `project_id` فقط للبلوك كامل).
2. **Edit PR قديم** → validation error لأن `geographic_scopes` فارغ.
3. **S3 down** → رفع مستندات header يفشل بـ RuntimeException.

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
- [ ] تشغيل migrate على staging أولاً
- [ ] التأكد من S3 credentials على السيرفر
- [ ] فتح PR قديم + جديد وتجربة create/edit/print/show
- [ ] تجربة PO مربوط بـ PR

---

## الملفات الجديدة (untracked) — لازم تدخل الـ commit

```
app/Enums/Procurement/ProcurementRequests/*          (5 enums)
app/Http/Requests/.../Concerns/*                     (3 traits)
app/Models/.../ProcurementRequest{Approval,PaymentTerm,Retention,TimelineEntry}.php
app/Services/.../ProcurementRequestFormDataResolver.php
app/Services/.../ProcurementRequestLegacyItemSync.php
app/Support/Procurement/ProcurementCheckboxGroup.php
database/migrations/2026_06_08_150000_expand_procurement_requests_for_pr_form.php
resources/views/procurement/procurement-requests/_*.blade.php  (partials جديدة)
resources/views/procurement/procurement-requests/print/_sections.blade.php
```

**لا تدخل commit:** `storage/framework/views/*` (compiled views — أضفها لـ `.gitignore` إذا لم تكن).

---

## الخلاصة

| السؤال | الجواب |
|--------|--------|
| آمن للـ production؟ | **نعم** مع migrate + backup + اختبار staging |
| migrate فقط؟ | **نعم** |
| fresh / seed؟ | **لا** |
| أكبر مخاطرة عملية؟ | تعديل PRs قديمة بدون `geographic_scopes` + وقت migrate على DB كبيرة |
