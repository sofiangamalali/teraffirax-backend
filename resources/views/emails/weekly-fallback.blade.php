@extends('emails.rtl_layout')

@section('title')
    مشتاقين لنشاطك يا {{ $creator_first_name }}
@endsection

@section('content')
    <p style="margin: 0 0 20px 0; font-size: 18px; font-weight: bold; color: #0b2f4e;">
        يا {{ $creator_first_name }}،
    </p>

    <p style="margin: 0 0 15px 0;">
        لاحظنا إن مفيش نشاط جديد على قناتك من أسبوع.
    </p>

    <p style="margin: 0 0 15px 0;">
        القناة لسه شغالة وبتدور على أفضل العروض عشانك. أي وقت تحب ترجع تنشر أو تتابع أداء قناتك، إحنا هنا.
    </p>

    <p style="margin: 0 0 25px 0;">
        لو محتاج مساعدة أو عندك سؤال، رد على الإيميل ده وهنكلمك.
    </p>

    <div align="center">
        <a href="{{ config('notifications.frontend_url', 'https://www.traffirax.com/dashboard') }}" 
           style="display: inline-block; padding: 12px 30px; background-color: #0b2f4e; color: #ffffff; text-decoration: none; border-radius: 6px; font-weight: bold; font-size: 16px;">
            شاهد العروض الحالية
        </a>
    </div>
@endsection