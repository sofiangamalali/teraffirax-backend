<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Reset Password</title>
  </head>
  <body
    style="
      margin: 0;
      padding: 0;
      background-color: #f4f6f9;
      font-family: &quot;Segoe UI&quot;, Arial, sans-serif;
    "
  >
    <table
      width="100%"
      cellpadding="0"
      cellspacing="0"
      border="0"
      style="background-color: #f4f6f9; padding: 40px 0"
    >
      <tr>
        <td align="center">
          <!-- Card -->
          <table
            width="560"
            cellpadding="0"
            cellspacing="0"
            border="0"
            style="
              background-color: #ffffff;
              border-radius: 12px;
              overflow: hidden;
              box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
              max-width: 560px;
              width: 100%;
            "
          >
            <!-- Logo -->
            <tr>
              <td align="center" style="padding: 20px 40px 0px 40px">
                                <img
                  src="{{ asset('logo.png') }}"
                  alt="Logo"
                  style="
                    max-height: 170px;
                    max-width: 300px;
                    display: block;
                    margin: 0 auto;
                  "
                />
              </td>
            </tr>

            <!-- Title -->
            <tr>
              <td align="center" style="padding: 0 40px 10px 40px">
                <h1
                  style="
                    margin: 0;
                    font-size: 32px;
                    font-weight: 800;
                    color: #0b2f4e;
                    letter-spacing: -0.5px;
                  "
                >
                  {{ __('Reset Password') }}
                </h1>
              </td>
            </tr>

            <!-- Subtitle -->
            <tr>
              <td align="center" style="padding: 0 60px 32px 60px">
                <p
                  style="
                    margin: 0;
                    font-size: 17px;
                    color: #0b2f4e;
                    line-height: 1.6;
                    text-align: center;
                  "
                >
                  {{ __('Use the verification code below to securely access your account.') }}
                </p>
              </td>
            </tr>

            <!-- OTP Box -->
            <tr>
              <td align="center" style="padding: 0 40px 24px 40px">
                <table
                  cellpadding="0"
                  cellspacing="0"
                  border="0"
                  style="
                    border: 1.5px solid #e2e6ee;
                    border-radius: 10px;
                    width: 100%;
                  "
                >
                  <tr>
                    <td align="center" style="padding: 32px 20px">
                      <span
                        style="
                          font-size: 48px;
                          font-weight: 800;
                          color: #1a3a6b;
                          letter-spacing: 14px;
                          font-family: &quot;Segoe UI&quot;, Arial, sans-serif;
                        "
                        >{{ $otp }}</span
                      >
                    </td>
                  </tr>
                </table>
              </td>
            </tr>

            <!-- Expiry -->
            <tr>
              <td align="center" style="padding: 0 40px 36px 40px">
                <p
                  style="
                    margin: 0;
                    font-size: 11px;
                    font-weight: 600;
                    color: #8a94a6;
                    letter-spacing: 1.5px;
                    text-transform: uppercase;
                  "
                >
                  &#9201;&nbsp; {{ __('This code expires in 10 minutes') }}
                </p>
              </td>
            </tr>

            <!-- Security Note -->
            <tr>
              <td style="padding: 0 40px 40px 40px">
                <table
                  cellpadding="0"
                  cellspacing="0"
                  border="0"
                  width="100%"
                  style="background-color: #f4f6f9; border-radius: 8px"
                >
                  <tr>
                    <td style="padding: 20px 24px">
                      <table
                        cellpadding="0"
                        cellspacing="0"
                        border="0"
                        width="100%"
                      >
                        <tr>
                          <td valign="top" width="28" style="padding-top: 1px">
                            <svg
                              xmlns="http://www.w3.org/2000/svg"
                              width="20"
                              height="20"
                              viewBox="0 0 24 24"
                              fill="none"
                              stroke="#1a3a6b"
                              stroke-width="2"
                              stroke-linecap="round"
                              stroke-linejoin="round"
                            >
                              <path
                                d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"
                              />
                              <polyline points="9 12 11 14 15 10" />
                            </svg>
                          </td>
                          <td valign="top" style="padding-left: 10px">
                            <p
                              style="
                                margin: 0 0 6px 0;
                                font-size: 14px;
                                font-weight: 700;
                                color: #1a3a6b;
                              "
                            >
                              {{ __('Security Note') }}
                            </p>
                            <p
                              style="
                                margin: 0;
                                font-size: 13px;
                                color: #555e6e;
                                line-height: 1.6;
                              "
                            >
                              {{ __("If you didn't request this code, you can safely ignore this email.") }}
                            </p>
                          </td>
                        </tr>
                      </table>
                    </td>
                  </tr>
                </table>
              </td>
            </tr>
          </table>
          <!-- End Card -->
        </td>
      </tr>
    </table>
  </body>
</html>
