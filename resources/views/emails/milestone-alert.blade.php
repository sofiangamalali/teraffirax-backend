@extends('emails.rtl_layout')

@section('title')
    🎉 مبروك يا {{ $creator_first_name }} - وصلت للحد الأدنى للسحب ({{ number_format($minimum_withdraw_amount, 2) }} ج.م)
@endsection

@section('content')
    <p style="margin: 0 0 20px 0; font-size: 18px; font-weight: bold; color: #0b2f4e;">
        يا {{ $creator_first_name }}،
    </p>

    <p style="margin: 0 0 15px 0; font-size: 17px; line-height: 1.6;">
        لحظة نفتخر بيها معاك - رصيدك المتاح للسحب وصل لـ <strong
            style="color: #059669;">{{ number_format($current_available, 2) }} ج.م</strong>، يعني عديت أول <strong
            style="color: #0b2f4e;">{{ number_format($minimum_withdraw_amount, 2) }} ج.م</strong> ليك وبقى عندك أول مبلغ
        تقدر تسحبه فعليًا من القناة بتاعتك.
    </p>

    <p style="margin: 0 0 20px 0;">
        ده أول دليل ملموس إن الشغل اللي بتعمله بيجيب نتيجة فعلية. كتير من الكريتورز بيوصلوا للحظة دي ويحسوا إنها نقطة تحول.
    </p>

    <div align="center" style="margin: 30px 0;">
        <a href="{{ config('notifications.frontend_url', 'https://traffirax.com/dashboard') }}"
            style="display: inline-block; padding: 12px 30px; background-color: #059669; color: #ffffff; text-decoration: none; border-radius: 6px; font-weight: bold; font-size: 16px; box-shadow: 0 4px 10px rgba(5, 150, 105, 0.2);">
            اسحب رصيدك دلوقتي
        </a>
    </div>

    <p style="margin: 20px 0 0 0; font-weight: bold; color: #0b2f4e;">
        كمل، ده لسه البداية.
    </p>
@endsection