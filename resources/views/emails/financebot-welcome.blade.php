<!DOCTYPE html>

<html>

<head>
    <meta charset="UTF-8">
    <title>FinanceBot</title>
</head>

<body style="margin:0;padding:0;background:#f8fafc;font-family:Arial,sans-serif;">


    <table width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td align="center" style="padding:40px 20px;">

                <table width="600" cellpadding="0" cellspacing="0"
                    style="background:#ffffff;border-radius:20px;overflow:hidden;box-shadow:0 10px 30px rgba(0,0,0,.08);max-width:100%;">

                    <tr>
                        <td style="background:linear-gradient(135deg,#0284c7,#4f46e5);padding:40px;text-align:center;">

                            <h1 style="margin:0;color:#fff;font-size:28px;">
                                🎉 Selamat Datang di FinanceBot
                            </h1>

                            <p style="margin-top:10px;color:#dbeafe;">
                                Akun Anda berhasil dibuat
                            </p>

                        </td>
                    </tr>

                    <tr>
                        <td style="padding:40px;">

                            <p style="margin-top:0;">
                                Halo <strong>{{ $user->name }}</strong>,
                            </p>

                            <p style="color:#475569;line-height:1.8;">
                                Pembayaran Anda berhasil diproses dan akun
                                FinanceBot sudah siap digunakan.
                            </p>

                            <table width="100%"
                                style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;padding:20px;">

                                <tr>
                                    <td style="padding:8px 0;">
                                        <strong>Email</strong>
                                        <br>
                                        {{ $user->email }}
                                    </td>
                                </tr>

                                <tr>
                                    <td style="padding:8px 0;">
                                        <strong>Password</strong>
                                        <br>
                                        {{ $password }}
                                    </td>
                                </tr>

                            </table>

                            <div style="text-align:center;margin-top:35px;">

                                <a href="{{ url('/login') }}"
                                    style="display:inline-block;background:#0284c7;color:#fff;text-decoration:none;padding:14px 30px;border-radius:12px;font-weight:bold;">

                                    Login Sekarang

                                </a>

                            </div>

                            <div
                                style="margin-top:30px;padding:15px;background:#ecfeff;border-left:4px solid #06b6d4;border-radius:8px;">

                                🔐 Demi keamanan, segera ubah password setelah
                                berhasil login.

                            </div>

                        </td>
                    </tr>

                    <tr>
                        <td style="background:#f8fafc;padding:25px;text-align:center;font-size:12px;color:#64748b;">

                            © {{ date('Y') }} FinanceBot

                        </td>
                    </tr>

                </table>

            </td>
        </tr>
    </table>


</body>

</html>
