<?php

/**
 * Vendor catalog: English + Arabic names. Slugs are derived in the seeder from English via Str::slug().
 *
 * @return list<array{name_en: string, name_ar: string, subcategories: list<array{name_en: string, name_ar: string}>}>
 */
return [
    [
        'name_en' => 'Furniture',
        'name_ar' => 'مفروشات',
        'subcategories' => [
            ['name_en' => 'Home Furniture', 'name_ar' => 'مفروشات منزلية'],
            ['name_en' => 'Office Furniture', 'name_ar' => 'مفروشات مكتبية'],
        ],
    ],
    [
        'name_en' => 'Surveying Equipment',
        'name_ar' => 'أجهزة مساحية',
        'subcategories' => [
            ['name_en' => 'Leveling Devices', 'name_ar' => 'أجهزة تسوية'],
        ],
    ],
    [
        'name_en' => 'Printing & Imaging Solutions',
        'name_ar' => 'حلول الطباعة والتصوير',
        'subcategories' => [
            ['name_en' => 'Laser Printers', 'name_ar' => 'طابعات ليزرية'],
            ['name_en' => 'Ink Printers', 'name_ar' => 'طابعات نفث الحبر'],
            ['name_en' => 'Plastic Card Printers', 'name_ar' => 'طابعات بطاقات بلاستيكية'],
        ],
    ],
    [
        'name_en' => 'Contracting & Construction',
        'name_ar' => 'مقاولات وإنشاءات',
        'subcategories' => [
            ['name_en' => 'Finishing Works', 'name_ar' => 'إكساء'],
            ['name_en' => 'General Contracting', 'name_ar' => 'تعهدات'],
            ['name_en' => 'Infrastructure Works', 'name_ar' => 'بنى تحتية'],
        ],
    ],
    [
        'name_en' => 'Interior Design & Decoration',
        'name_ar' => 'ديكور وتصميم داخلي',
        'subcategories' => [
            ['name_en' => 'Interior Design & Finishing', 'name_ar' => 'ديكور وإكساء داخلي'],
            ['name_en' => 'Decorative Materials Trading', 'name_ar' => 'تجارة مواد ديكور'],
            ['name_en' => 'PVC Doors', 'name_ar' => 'أبواب PVC'],
        ],
    ],
    [
        'name_en' => 'Renewable Energy',
        'name_ar' => 'طاقة بديلة',
        'subcategories' => [
            ['name_en' => 'Solar Energy Systems', 'name_ar' => 'طاقة شمسية'],
            ['name_en' => 'Solar Panels', 'name_ar' => 'ألواح طاقة شمسية'],
            ['name_en' => 'Batteries', 'name_ar' => 'بطاريات'],
            ['name_en' => 'Inverters', 'name_ar' => 'انفيرترات'],
            ['name_en' => 'Solar Water Heating', 'name_ar' => 'طاقة شمسية مائية'],
        ],
    ],
    [
        'name_en' => 'Heating Systems',
        'name_ar' => 'تدفئة',
        'subcategories' => [
            ['name_en' => 'Heating Equipment', 'name_ar' => 'لوازم تدفئة'],
            ['name_en' => 'Underfloor Heating', 'name_ar' => 'تدفئة أرضية'],
        ],
    ],
    [
        'name_en' => 'Hospitality',
        'name_ar' => 'فنادق',
        'subcategories' => [
            ['name_en' => 'Hotel Supplies', 'name_ar' => 'تجهيزات فندقية'],
            ['name_en' => 'Hotel Operations Services', 'name_ar' => 'خدمات تشغيل فندقي'],
        ],
    ],
    [
        'name_en' => 'Water Treatment',
        'name_ar' => 'معالجة مياه',
        'subcategories' => [
            ['name_en' => 'Water Filters', 'name_ar' => 'فلاتر مياه'],
            ['name_en' => 'Water Treatment Systems', 'name_ar' => 'أنظمة معالجة مياه'],
        ],
    ],
    [
        'name_en' => 'Plumbing, HVAC & Pools',
        'name_ar' => 'صحية وتكييف ومسابح',
        'subcategories' => [
            ['name_en' => 'Sanitary Equipment', 'name_ar' => 'تجهيزات صحية'],
            ['name_en' => 'Plumbing Works', 'name_ar' => 'تمديدات صحية'],
            ['name_en' => 'Air Conditioning', 'name_ar' => 'تكييف'],
            ['name_en' => 'Pools & Water Features', 'name_ar' => 'مسابح وبحيرات'],
        ],
    ],
    [
        'name_en' => 'Marketing & Advertising',
        'name_ar' => 'تسويق وإعلان',
        'subcategories' => [
            ['name_en' => 'Digital Marketing', 'name_ar' => 'تسويق رقمي'],
            ['name_en' => 'Printing Services', 'name_ar' => 'طباعة'],
            ['name_en' => 'Facade Branding', 'name_ar' => 'تلبيس واجهات'],
            ['name_en' => 'Advertising Production', 'name_ar' => 'تنفيذ إعلاني'],
        ],
    ],
    [
        'name_en' => 'Online Marketplace & Brokerage',
        'name_ar' => 'وسيط أونلاين',
        'subcategories' => [
            ['name_en' => 'Buying / Selling / Leasing', 'name_ar' => 'بيع وشراء وتأجير'],
            ['name_en' => 'Online Brokerage Services', 'name_ar' => 'خدمات وساطة أونلاين'],
        ],
    ],
    [
        'name_en' => 'IT Solutions',
        'name_ar' => 'حلول تكنولوجيا المعلومات',
        'subcategories' => [
            ['name_en' => 'Information Security', 'name_ar' => 'أمن المعلومات'],
            ['name_en' => 'Surveillance Systems', 'name_ar' => 'أنظمة مراقبة'],
            ['name_en' => 'Networking', 'name_ar' => 'الربط الشبكي'],
            ['name_en' => 'Mikrotik Solutions', 'name_ar' => 'حلول مايكروتيك'],
            ['name_en' => 'Servers & Infrastructure', 'name_ar' => 'مخدمات وبنية تحتية'],
            ['name_en' => 'Technical Support Services', 'name_ar' => 'خدمات دعم فني'],
        ],
    ],
    [
        'name_en' => 'Building Materials & Insulation',
        'name_ar' => 'مواد البناء والعزل',
        'subcategories' => [
            ['name_en' => 'Construction Chemicals', 'name_ar' => 'كيميائيات مواد بناء'],
            ['name_en' => 'Paints & Coatings', 'name_ar' => 'دهانات'],
            ['name_en' => 'Waterproofing', 'name_ar' => 'مواد عزل مائي'],
            ['name_en' => 'Thermal Insulation', 'name_ar' => 'مواد عزل حراري'],
            ['name_en' => 'Tile Adhesives', 'name_ar' => 'لواصق سيراميك'],
            ['name_en' => 'Silicones', 'name_ar' => 'سيليكونات'],
            ['name_en' => 'Aluminum', 'name_ar' => 'ألمنيوم'],
            ['name_en' => 'Fiberglass', 'name_ar' => 'فيبر غلاس'],
            ['name_en' => 'Precast Concrete', 'name_ar' => 'مقاطع خرسانية مسبقة الصب'],
            ['name_en' => 'Eco Blocks', 'name_ar' => 'بديل البلوك صديق البيئة'],
            ['name_en' => 'Cement & Concrete Chemicals', 'name_ar' => 'كيميائيات الخرسانة والإسمنت'],
            ['name_en' => 'Gypsum', 'name_ar' => 'جبصين'],
            ['name_en' => 'Calcium Carbonate', 'name_ar' => 'إنتاج كربونات الكالسيوم والاسبيداج'],
        ],
    ],
    [
        'name_en' => 'Raw Materials, Aggregates & Quarries',
        'name_ar' => 'مواد أولية ومقالع وكسارات',
        'subcategories' => [
            ['name_en' => 'Quarries & Crushers', 'name_ar' => 'مقالع وكسارات'],
            ['name_en' => 'Clay Sand', 'name_ar' => 'رمل طينة'],
            ['name_en' => 'White Sand', 'name_ar' => 'رمل أبيض'],
            ['name_en' => 'Powder Sand', 'name_ar' => 'رمل بودرة'],
            ['name_en' => 'Gravel', 'name_ar' => 'عدسية'],
        ],
    ],
    [
        'name_en' => 'Industrial & Agricultural Tools',
        'name_ar' => 'العدد الصناعية والزراعية',
        'subcategories' => [
            ['name_en' => 'Industrial Tools', 'name_ar' => 'عدد صناعية'],
            ['name_en' => 'Agricultural Tools', 'name_ar' => 'عدد زراعية'],
            ['name_en' => 'Workshop Equipment', 'name_ar' => 'تجهيزات ورش'],
        ],
    ],
    [
        'name_en' => 'Electrical Equipment',
        'name_ar' => 'أدوات وتجهيزات كهربائية',
        'subcategories' => [
            ['name_en' => 'Home Appliances', 'name_ar' => 'أجهزة منزلية'],
            ['name_en' => 'Electrical Switches', 'name_ar' => 'مفاتيح كهربائية'],
            ['name_en' => 'Exhaust Fans', 'name_ar' => 'ساحبات هواء'],
            ['name_en' => 'Water Coolers', 'name_ar' => 'مبردات ماء'],
            ['name_en' => 'Liquid Level Sensors', 'name_ar' => 'مقياس مستوى سوائل'],
        ],
    ],
    [
        'name_en' => 'Real Estate & Investment',
        'name_ar' => 'استثمار وعقارات',
        'subcategories' => [
            ['name_en' => 'Residential Investment', 'name_ar' => 'استثمارات سكنية'],
            ['name_en' => 'Real Estate Development', 'name_ar' => 'تطوير عقاري'],
            ['name_en' => 'Restaurant Fit-out', 'name_ar' => 'تعهدات وتجهيزات مطاعم'],
            ['name_en' => 'Gym Fit-out', 'name_ar' => 'تعهدات وتجهيز نوادي رياضية'],
            ['name_en' => 'Parking Solutions', 'name_ar' => 'حلول مواقف'],
        ],
    ],
    [
        'name_en' => 'Architecture & Design',
        'name_ar' => 'هندسة معمارية وتصميم',
        'subcategories' => [
            ['name_en' => 'Interior Design', 'name_ar' => 'تصميم داخلي'],
            ['name_en' => 'Shop Fit-out', 'name_ar' => 'تنفيذ محلات'],
            ['name_en' => 'Commercial Design', 'name_ar' => 'ديكور تجاري'],
            ['name_en' => 'Exhibition Booth Design & Build', 'name_ar' => 'تصميم وتنفيذ أجنحة معارض'],
        ],
    ],
    [
        'name_en' => 'Engineering Consultancy',
        'name_ar' => 'استشارات هندسية',
        'subcategories' => [
            ['name_en' => 'Structural Engineering Services', 'name_ar' => 'خدمات إنشائية احترافية'],
            ['name_en' => 'Architectural Consultancy', 'name_ar' => 'استشارات معمارية'],
            ['name_en' => 'MEP Consultancy', 'name_ar' => 'استشارات كهروميكانيك'],
        ],
    ],
    [
        'name_en' => 'Metal Industries',
        'name_ar' => 'صناعات معدنية',
        'subcategories' => [
            ['name_en' => 'Machinery Manufacturing', 'name_ar' => 'صناعة مكنات'],
            ['name_en' => 'Sponge Machinery', 'name_ar' => 'صناعة آلات سفنج'],
            ['name_en' => 'Metal Fabrication', 'name_ar' => 'تشكيل وتصنيع معدني'],
        ],
    ],
    [
        'name_en' => 'Logistics & Shipping',
        'name_ar' => 'شحن ولوجستيات',
        'subcategories' => [
            ['name_en' => 'International Shipping', 'name_ar' => 'شحن دولي'],
            ['name_en' => 'Customs Clearance', 'name_ar' => 'تخليص جمركي'],
            ['name_en' => 'Local Delivery Services', 'name_ar' => 'خدمات توصيل محلي'],
        ],
    ],
    [
        'name_en' => 'Banking & Financial Services',
        'name_ar' => 'بنوك وخدمات مالية',
        'subcategories' => [
            ['name_en' => 'Banks', 'name_ar' => 'بنوك'],
            ['name_en' => 'Money Transfer', 'name_ar' => 'شركة حوالات'],
            ['name_en' => 'Financial Services', 'name_ar' => 'خدمات مالية'],
        ],
    ],
    [
        'name_en' => 'Security & Safety Systems',
        'name_ar' => 'أنظمة الأمن والسلامة',
        'subcategories' => [
            ['name_en' => 'Fire Alarm Systems', 'name_ar' => 'أنظمة إنذار حريق'],
            ['name_en' => 'Fire Fighting Systems', 'name_ar' => 'أنظمة إطفاء'],
            ['name_en' => 'Access Control Systems', 'name_ar' => 'أنظمة دخول وخروج'],
            ['name_en' => 'CCTV Systems', 'name_ar' => 'أنظمة كاميرات مراقبة'],
            ['name_en' => 'Safety Equipment', 'name_ar' => 'معدات سلامة'],
        ],
    ],
    [
        'name_en' => 'Maintenance Services',
        'name_ar' => 'خدمات صيانة',
        'subcategories' => [
            ['name_en' => 'Electrical Maintenance', 'name_ar' => 'صيانة كهربائية'],
            ['name_en' => 'Mechanical Maintenance', 'name_ar' => 'صيانة ميكانيكية'],
            ['name_en' => 'HVAC Maintenance', 'name_ar' => 'صيانة تكييف'],
            ['name_en' => 'General Maintenance', 'name_ar' => 'صيانة عامة'],
            ['name_en' => 'Preventive Maintenance', 'name_ar' => 'صيانة وقائية'],
        ],
    ],
    [
        'name_en' => 'Facility Management',
        'name_ar' => 'إدارة مرافق',
        'subcategories' => [
            ['name_en' => 'Cleaning Services', 'name_ar' => 'خدمات نظافة'],
            ['name_en' => 'Building Management', 'name_ar' => 'إدارة مباني'],
            ['name_en' => 'Waste Management', 'name_ar' => 'إدارة نفايات'],
            ['name_en' => 'Pest Control', 'name_ar' => 'مكافحة حشرات'],
            ['name_en' => 'Facility Operations', 'name_ar' => 'تشغيل مرافق'],
        ],
    ],
];
