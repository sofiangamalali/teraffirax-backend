@extends('emails.rtl_layout')

@section('title')
    تسجيل مستخدم جديد
@endsection

@section('content')
    <p style="margin: 0 0 20px 0; font-size: 18px; font-weight: bold; color: #0b2f4e;">
        مرحباً مدير النظام،
    </p>

    <p style="margin: 0 0 15px 0;">
        لقد قام مستخدم جديد بتسجيل حساب على المنصة. إليك تفاصيل الحساب:
    </p>

    <table cellpadding="5" cellspacing="0" border="0"
        style="width: 100%; border-collapse: collapse; margin-bottom: 25px; text-align: right;">
        <tr style="border-bottom: 1px solid #e2e6ee;">
            <td style="padding: 8px; font-weight: bold; width: 120px;">الاسم:</td>
            <td style="padding: 8px;">{{ $creator->name }}</td>
        </tr>
        <tr style="border-bottom: 1px solid #e2e6ee;">
            <td style="padding: 8px; font-weight: bold;">البريد الإلكتروني:</td>
            <td style="padding: 8px;">{{ $creator->email }}</td>
        </tr>
        @if($creator->phone)
            <tr style="border-bottom: 1px solid #e2e6ee;">
                <td style="padding: 8px; font-weight: bold;">رقم الهاتف:</td>
                <td style="padding: 8px;">{{ $creator->phone }}</td>
            </tr>
        @endif
        @if($creator->country)
            <tr style="border-bottom: 1px solid #e2e6ee;">
                <td style="padding: 8px; font-weight: bold;">الدولة:</td>
                <td style="padding: 8px;">{{ $creator->country->name }}</td>
            </tr>
        @endif
        <tr>
            <td style="padding: 8px; font-weight: bold;">تاريخ التسجيل:</td>
            <td style="padding: 8px;">{{ $creator->created_at->format('Y-m-d H:i') }}</td>
        </tr>
    </table>

    <div align="center">
        <a href="{{ config('notifications.admin_frontend_url', 'https://traffirax.com/admin') }}"
            style="display: inline-block; padding: 12px 30px; background-color: #0b2f4e; color: #ffffff; text-decoration: none; border-radius: 6px; font-weight: bold; font-size: 16px;">
            عرض الحساب في لوحة التحكم
        </a>
    </div>
@endsection