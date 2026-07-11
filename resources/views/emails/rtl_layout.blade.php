<!doctype html>
<html lang="ar" dir="rtl">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>@yield('title', 'Teraffirax')</title>
</head>

<body style="
      margin: 0;
      padding: 0;
      background-color: #f4f6f9;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      direction: rtl;
      text-align: right;
    ">
  <table width="100%" cellpadding="0" cellspacing="0" border="0"
    style="background-color: #f4f6f9; padding: 40px 0; direction: rtl;">
    <tr>
      <td align="center">
        <!-- Card -->
        <table width="560" cellpadding="0" cellspacing="0" border="0" style="
              background-color: #ffffff;
              border-radius: 12px;
              overflow: hidden;
              box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
              max-width: 560px;
              width: 100%;
              border: 1px solid #e2e6ee;
            ">
          <!-- Logo Header -->
          <tr>
            <td align="center" style="padding: 30px 40px 10px 40px; background-color: #ffffff;">
              <img src="{{ asset('logo.png') }}" alt="Teraffirax" style="
                    max-height: 80px;
                    max-width: 220px;
                    display: block;
                    margin: 0 auto;
                  " />
            </td>
          </tr>

          <!-- Content Area -->
          <tr>
            <td
              style="padding: 20px 40px 40px 40px; font-size: 16px; line-height: 1.8; color: #334155; text-align: right;">
              @yield('content')
            </td>
          </tr>

          <!-- Divider -->
          <tr>
            <td style="padding: 0 40px;">
              <div style="border-top: 1px solid #e2e6ee;"></div>
            </td>
          </tr>

          <!-- Footer -->
          <tr>
            <td align="center" style="padding: 30px 40px; font-size: 13px; color: #64748b; line-height: 1.6;">
              <p style="margin: 0 0 10px 0;">
                وصلك هذا البريد الإلكتروني كشريك في منصة Teraffirax.
              </p>
              <p style="margin: 0;">
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</body>

</html>