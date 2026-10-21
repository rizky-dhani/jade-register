<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f6f7f5">
    <title>@yield('title') — JADE 2026</title>
    <style>
        :root { color-scheme: light; font-family: Arial, Helvetica, sans-serif; color: #202923; background: #f6f7f5; }
        * { box-sizing: border-box; }
        body { min-height: 100vh; margin: 0; display: grid; place-items: center; padding: 24px; }
        main { width: min(100%, 520px); padding: 48px 40px; text-align: center; background: #fff; border: 1px solid #e4e9e5; border-radius: 18px; box-shadow: 0 18px 55px rgb(26 54 38 / 8%); }
        .mark { display: inline-grid; place-items: center; width: 54px; height: 54px; margin-bottom: 24px; border-radius: 16px; background: #e8f0e9; color: #315a3d; font-size: 24px; font-weight: 700; }
        .eyebrow { margin: 0 0 12px; color: #52705a; font-size: 12px; font-weight: 700; letter-spacing: .16em; text-transform: uppercase; }
        h1 { margin: 0; font-size: clamp(28px, 7vw, 38px); letter-spacing: -.04em; line-height: 1.15; }
        .message { margin: 18px auto 0; max-width: 360px; color: #647168; font-size: 16px; line-height: 1.7; }
        .rule { width: 42px; height: 2px; margin: 30px auto 20px; background: #d6e1d7; }
        footer { color: #879189; font-size: 12px; letter-spacing: .08em; }
        @media (max-width: 480px) { main { padding: 38px 24px; } }
    </style>
</head>
<body>
    <main aria-labelledby="page-title">
        <div class="mark" aria-hidden="true">J</div>
        <p class="eyebrow">JADE 2026</p>
        @yield('content')
        <div class="rule" aria-hidden="true"></div>
        <footer>JAKARTA DENTAL EXHIBITIONS</footer>
    </main>
</body>
</html>
