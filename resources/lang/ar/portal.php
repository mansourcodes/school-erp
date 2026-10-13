<?php

// New student/teacher portal (/beta SPA + /api/v1): Arabic only, see code-standards.md.
return [

    'auth' => [
        'failed' => 'الرقم الشخصي أو كلمة المرور غير صحيحة',
        'locked' => 'تم إيقاف تسجيل الدخول مؤقتًا بسبب كثرة المحاولات. حاول لاحقًا.',
        'throttled' => 'طلبات كثيرة. انتظر قليلًا ثم حاول مرة أخرى.',
        'bot_check_failed' => 'تعذّر التحقق من أنك لست برنامجًا آليًا. أعد المحاولة.',
    ],

    'mode' => [
        'maintenance' => 'البوابة تحت الصيانة. نعمل على تحسينها وستعود قريبًا.',
        'closed' => 'البوابة مغلقة حاليًا. للاستفسار يرجى مراجعة قسم إدارة المركز.',
    ],

    'errors' => [
        'unauthenticated' => 'يجب تسجيل الدخول أولًا',
        'session_expired' => 'انتهت الجلسة. أعد تحميل الصفحة وحاول مرة أخرى.',
        'forbidden' => 'ليس لديك صلاحية للوصول إلى هذه الصفحة',
        'not_found' => 'المطلوب غير موجود',
        'too_many_requests' => 'طلبات كثيرة. انتظر قليلًا ثم حاول مرة أخرى.',
        'server' => 'حدث خطأ غير متوقع. حاول مرة أخرى لاحقًا.',
    ],

    // Payment keys come from the Backpack settings payment_types / payment_sources.
    'payment_types' => [
        'FULL' => 'رسوم كاملة',
        'PART' => 'دفعة جزئية',
        'OFFER' => 'عرض خاص',
    ],

    'payment_sources' => [
        'CASH' => 'نقدًا',
        'BENEFIT_PAY' => 'بنفت باي',
    ],

    'attributes' => [
        'role' => 'نوع الحساب',
        'cpr' => 'الرقم الشخصي',
        'password' => 'كلمة المرور',
    ],

];
