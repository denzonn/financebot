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
                        <td style="background:linear-gradient(135deg,#f59e0b,#f97316);padding:40px;text-align:center;">

                            <h1 style="margin:0;color:#fff;font-size:28px;">
                                Email Sudah Terdaftar
                            </h1>

                            <p style="margin-top:10px;color:#ffedd5;">
                                Kami membutuhkan bantuan admin untuk aktivasi
                            </p>

                        </td>
                    </tr>

                    <tr>
                        <td style="padding:40px;">

                            <p>
                                Halo <strong>{{ $name }}</strong>,
                            </p>

                            <p style="color:#475569;line-height:1.8;">

                                Kami menerima pembayaran FinanceBot menggunakan
                                email berikut:

                            </p>

                            <div style="padding:18px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;">

                                <strong>{{ $email }}</strong>

                            </div>

                            <p style="margin-top:25px;color:#475569;line-height:1.8;">

                                Namun email tersebut sudah terdaftar pada sistem
                                FinanceBot.

                                Silakan hubungi admin agar akun dapat
                                diaktifkan kembali.

                            </p>

                            <div style="text-align:center;margin-top:35px;">

                                <a href="{{ $waUrl }}"
                                    style="display:inline-block;background:#25D366;color:white;text-decoration:none;padding:14px 30px;border-radius:12px;font-weight:bold;">

                                    Hubungi Admin via WhatsApp

                                </a>

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
