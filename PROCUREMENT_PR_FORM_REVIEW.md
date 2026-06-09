# مراجعة Procurement Request (PR Form) — محدّثة حسب الكود الحالي

**التاريخ:** 2026-06-09  
**الحالة:** PR form كامل — شركات متعددة، Maintenance، Compliance + Level، Timeline مع Final delivery

---

## ملخص التغييرات

### الهدف
نموذج **Procurement Request** كامل يشمل:

- **الشركة (Company):** ASAS Ventures / Qassioun Journey / Activation — هيدر وفوتر الطباعة واللوغو (`PrCompany`)
- **Requester information:** Name, Date, Department (تلقائي)
- **PR information:** project, zone, category/subcategory, procurement type, **Local + International** (checkboxين — تحديد الاثنين = Both في العرض), vendor type
- **BOQ:** item, description, quantity, unit price, total + `currency_code` + samples
- **Justification & delivery:** justification, lead time (من PO issuance), flexible delivery, delivery location
- **Scope of work:** **إلزامي**
- **Supporting documents:** header (S3 أو URL)
- **أقسام داخلية (internal)** — النموذج + show فقط، **لا تظهر في الطباعة:**
  - Payment terms
  - Retention by year
  - Maintenance: after-sale service, warranty years, coverage
  - Procurement timeline: 8 أنشطة + **Final delivery date** (نفس `delivery_lead_time_days` — للعرض فقط، بدون عمود DB)
  - Compliance: verification, prequalification (+ **Level A/B/C/D** إذا Yes), NDA, conflict of interest, commitment + general terms (`RfqTerms::defaults()`)
  - Approvals (4 أدوار)
- **Print view:** للمورد/خارجي فقط (بدون internal)
- **Show view:** كل الأقسام عبر `ProcurementRequestFormDataResolver`

### Backend

| المكوّن | التفاصيل |
|---------|----------|
| `2026_06_08_150000_expand_procurement_requests_for_pr_form.php` | توسيع PR + 4 جداول فرعية |
| `2026_06_08_160000_add_insurance_requirements_to_procurement_requests.php` | insurance legacy للـ PO |
| `2026_06_09_140000_add_pr_commercial_terms_to_purchase_orders.php` | snapshot على PO |
| `2026_06_09_160000_add_pr_company_and_compliance_fields.php` | `company_key`, compliance booleans, `after_sale_service_applicable` |
| `2026_06_09_170000_add_compliance_prequalification_level_to_procurement_requests.php` | `compliance_prequalification_level` (a/b/c/d) |
| **Enum** `PrCompany` | 3 شركات — `app/Enums/Procurement/PrCompany.php` |
| **Enum** `CompliancePrequalificationLevel` | `a`, `b`, `c`, `d` |
| **Enum** `GeographicScope` | `local`, `international` — العرض "Both" عند اختيار الاثنين (ليس checkbox ثالث) |
| **Enums** (PR) | `ProcurementType`, `ProcurementVendorType`, `ProcurementTimelineActivity`, `ProcurementApprovalRole` |

### Frontend (Blade)

```
_document-header          ← company + logo
_pr-information           ← Local / International (checkboxين)
_boq, _justification-delivery, _supporting-documents
_payment-terms, _retentions, _maintenance     (internal)
_internal-sections        ← timeline + final delivery row, compliance + level, approvals
_show-sections
print/…                   ← بدون أقسام internal
```

**حقول إلزامية:** `company_key`, `project_id`, `category_id`, `geographic_scopes` (min:1), `delivery_location`, `scope_of_work`, BOQ ≥1 (`description`).

**شرط إضافي:** `compliance_prequalification_level` إلزامي عند `compliance_prequalification_required = Yes`.

**اللوغو:** `public/images/pr/companies/{company_key}.png`

---

## هل في خطورة على Production؟

| ✅ آمن | ⚠️ انتبه |
|--------|---------|
| Migrations additive فقط | PR قديم بدون `geographic_scopes` → validation error |
| `company_key` → ASAS للقديم | `scope_of_work` إلزامي — backfill من أول بند إن وُجد |
| insurance columns باقية | لوغو Qassioun/Activation placeholder |

---

## ماذا تحتاج عند الرفع؟

```bash
php artisan migrate
php artisan config:cache    # production
php artisan view:cache      # production
```

### Checklist

- [ ] backup DB
- [ ] migrate (يشمل `160000` + `170000`)
- [ ] create / edit / show / print
- [ ] Local + International معاً → يعرض "Both"
- [ ] prequalification Yes → Level مطلوب
- [ ] Final delivery في timeline = lead time days
- [ ] الطباعة بدون internal sections
- [ ] `php artisan test --filter=ProcurementRequest`

---

## الملفات الرئيسية

```
app/Enums/Procurement/PrCompany.php
app/Enums/Procurement/ProcurementRequests/
    GeographicScope.php, CompliancePrequalificationLevel.php, …

database/migrations/
    …160000_add_pr_company_and_compliance_fields.php
    …170000_add_compliance_prequalification_level_to_procurement_requests.php

resources/views/procurement/procurement-requests/
    _maintenance (بدل _insurance), _internal-sections, partials/_yes-no-radio
```

---

## الخلاصة

| السؤال | الجواب |
|--------|--------|
| Both كيف؟ | checkbox Local + International معاً — ليس خيار ثالث |
| Final delivery؟ | صف عرض في timeline = `delivery_lead_time_days` |
| Prequalification level؟ | A/B/C/D enum — إلزامي إذا Yes |
| Insurance على PR؟ | لا — Maintenance (internal) |
| الطباعة internal؟ | لا |
| المستند يطابق الكود؟ | **نعم** — 2026-06-09 |
