@extends('emails.rtl_layout')

@section('title')
    @if ($variant === 'A')
        فلوسك جاهزة يا {{ $creator_first_name }}
    @elseif ($variant === 'B')
        في عمولة جديدة اتسجلتلك يا {{ $creator_first_name }}
    @else
        يوم كويس يا {{ $creator_first_name }} - عمولة جديدة وفلوس بقت جاهزة
    @endif
@endsection

@section('content')
    <p style="margin: 0 0 20px 0; font-size: 18px; font-weight: bold; color: #0b2f4e;">
        يا {{ $creator_first_name }}،
    </p>

    @if ($variant === 'A')
        <p style="margin: 0 0 15px 0;">
            خبر كويس - <strong style="color: #059669; font-size: 17px;">{{ number_format($matured_sum, 2) }} ج.م</strong> بقوا
            دلوقتي في رصيدك المتاح للسحب.
        </p>
        <div style="background-color: #f8fafc; border-radius: 8px; padding: 15px; margin: 20px 0; border: 1px solid #e2e6ee;">
            <p style="margin: 0 0 8px 0; font-size: 15px;">
                رصيدك المتاح دلوقتي: <strong>{{ number_format($current_available, 2) }} ج.م</strong>
            </p>
            <p style="margin: 0; font-size: 15px; color: #64748b;">
                رصيدك المعلق: <strong>{{ number_format($current_pending, 2) }} ج.م</strong>
            </p>
        </div>
        <p style="margin: 0 0 20px 0;">
            القناة بتاعتك شغالة وبتجيب نتيجة. تقدر تسحب رصيدك دلوقتي أو تسيبه يتراكم أكتر.
        </p>
        <div align="center" style="margin-top: 25px;">
            <a href="{{ config('notifications.frontend_url', 'https://traffirax.com/dashboard') }}"
                style="display: inline-block; padding: 12px 30px; background-color: #0b2f4e; color: #ffffff; text-decoration: none; border-radius: 6px; font-weight: bold; font-size: 16px;">
                اسحب رصيدك
            </a>
        </div>
    @elseif ($variant === 'B')
        <p style="margin: 0 0 15px 0;">
            عمولة جديدة بقيمة <strong style="color: #0f172a; font-size: 17px;">{{ number_format($new_commissions_sum, 2) }}
                ج.م</strong> اتسجلت من مبيعات قناتك.
        </p>
        <p style="margin: 0 0 15px 0;">
            هي دلوقتي في مرحلة الاستحقاق، وهتبقى متاحة للسحب خلال الفترة المحددة.
        </p>
        <div style="background-color: #f8fafc; border-radius: 8px; padding: 15px; margin: 20px 0; border: 1px solid #e2e6ee;">
            <p style="margin: 0 0 8px 0; font-size: 15px;">
                رصيدك المعلق: <strong>{{ number_format($current_pending, 2) }} ج.م</strong>
            </p>
            <p style="margin: 0; font-size: 15px; color: #64748b;">
                رصيدك المتاح دلوقتي: <strong>{{ number_format($current_available, 2) }} ج.م</strong>
            </p>
        </div>
        <p style="margin: 0;">
            استمر، القناة بتاعتك شغالة.
        </p>
    @else
        <p style="margin: 0 0 15px 0; font-weight: 600;">
            يوم نشيط للقناة بتاعتك:
        </p>
        <ul style="margin: 0 0 20px 0; padding-right: 20px; list-style-type: none;">
            <li style="margin-bottom: 10px; font-size: 15px;">
                💰 <strong style="color: #059669;">{{ number_format($matured_sum, 2) }} ج.م</strong> بقوا متاحين للسحب دلوقتي
            </li>
            <li style="margin-bottom: 10px; font-size: 15px;">
                📈 <strong style="color: #0b2f4e;">{{ number_format($new_commissions_sum, 2) }} ج.م</strong> عمولة جديدة اتسجلت
                (لسه في الاستحقاق)
            </li>
        </ul>
        <div style="background-color: #f8fafc; border-radius: 8px; padding: 15px; margin: 20px 0; border: 1px solid #e2e6ee;">
            <p style="margin: 0 0 8px 0; font-size: 15px;">
                رصيدك المتاح: <strong>{{ number_format($current_available, 2) }} ج.م</strong>
            </p>
            <p style="margin: 0; font-size: 15px; color: #64748b;">
                رصيدك المعلق: <strong>{{ number_format($current_pending, 2) }} ج.م</strong>
            </p>
        </div>
        <div align="center" style="margin-top: 25px;">
            <a href="{{ config('notifications.frontend_url', 'https://traffirax.com/dashboard') }}"
                style="display: inline-block; padding: 12px 30px; background-color: #0b2f4e; color: #ffffff; text-decoration: none; border-radius: 6px; font-weight: bold; font-size: 16px;">
                اسحب رصيدك
            </a>
        </div>
    @endif
@endsection