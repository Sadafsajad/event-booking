<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>EventBook — Simple Event Booking Manager</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <style>
        :root {
            --bg: #0b1020;
            --card: #10172a;
            --soft: #0f172a;
            --text: #e5e7eb;
            --muted: #94a3b8;
            --brand: #7c3aed;
            /* violet */
            --brand-2: #22d3ee;
            /* cyan */
            --ring: rgba(124, 58, 237, .35);
            --white: #fff;
        }

        * {
            box-sizing: border-box
        }

        html,
        body {
            height: 100%
        }

        body {
            margin: 0;
            font-family: ui-sans-serif, system-ui, -apple-system, "Segoe UI", Inter, Roboto, Arial;
            color: var(--text);
            background:
                radial-gradient(1200px 600px at -15% -10%, rgba(34, 211, 238, .15), transparent 60%),
                radial-gradient(900px 500px at 110% -10%, rgba(124, 58, 237, .18), transparent 60%),
                linear-gradient(180deg, #0b1329, #0b1020 30%);
            background-attachment: fixed;
        }

        /* ----- helpers ----- */
        .container {
            max-width: 1180px;
            margin: 0 auto;
            padding: 24px
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            border: none;
            border-radius: 12px;
            padding: 12px 16px;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
            transition: transform .08s ease, box-shadow .15s ease, opacity .15s ease;
            box-shadow: 0 10px 18px rgba(2, 6, 23, .35);
            backdrop-filter: saturate(130%) blur(6px);
        }

        .btn:active {
            transform: translateY(1px)
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--brand), #4f46e5 55%, var(--brand-2));
            color: var(--white);
            box-shadow: 0 12px 24px rgba(124, 58, 237, .35);
        }

        .btn-ghost {
            background: rgba(255, 255, 255, .06);
            color: var(--text);
            border: 1px solid rgba(148, 163, 184, .2);
        }

        .chip {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-weight: 700;
            font-size: 12px;
            letter-spacing: .04em;
            padding: 6px 10px;
            border-radius: 999px;
            color: #a78bfa;
            background: rgba(124, 58, 237, .15);
            border: 1px solid rgba(124, 58, 237, .35);
        }

        .card {
            background: linear-gradient(180deg, rgba(255, 255, 255, .03), rgba(255, 255, 255, .02));
            border: 1px solid rgba(148, 163, 184, .18);
            border-radius: 18px;
            padding: 22px;
            box-shadow: 0 18px 40px rgba(2, 6, 23, .45);
        }

        .muted {
            color: var(--muted)
        }

        .split {
            display: grid;
            grid-template-columns: 1.15fr .85fr;
            gap: 24px;
            align-items: stretch
        }

        @media (max-width:1000px) {
            .split {
                grid-template-columns: 1fr
            }
        }

        /* ----- header ----- */
        .nav {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 6px 0
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 800;
            text-decoration: none;
            color: var(--text)
        }

        .logo {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            background: conic-gradient(from 200deg at 50% 50%, var(--brand), #6ee7b7 40%, var(--brand-2) 70%, var(--brand));
            box-shadow: 0 6px 18px var(--ring), inset 0 0 24px rgba(255, 255, 255, .12);
        }

        .nav a {
            color: var(--text);
            text-decoration: none;
            font-weight: 700;
            opacity: .9
        }

        .gap-10 {
            display: flex;
            align-items: center;
            gap: 10px
        }

        /* ----- hero ----- */
        .hero {
            padding: 28px
        }

        .hero h1 {
            font-size: 48px;
            line-height: 1.05;
            margin: 16px 0 10px;
            letter-spacing: -.02em;
            background: linear-gradient(180deg, #fff, #dbeafe 70%);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            text-shadow: 0 10px 30px rgba(59, 130, 246, .15);
        }

        .hero p {
            margin: 0;
            color: var(--muted);
            max-width: 58ch
        }

        /* ----- feature list ----- */
        .features {
            display: grid;
            grid-template-columns: 1fr;
            gap: 12px;
            margin-top: 6px
        }

        .feature {
            display: flex;
            gap: 12px;
            align-items: flex-start
        }

        .tick {
            width: 22px;
            height: 22px;
            border-radius: 7px;
            flex: none;
            display: grid;
            place-items: center;
            background: linear-gradient(135deg, #10b981, #22d3ee);
            box-shadow: 0 8px 18px rgba(34, 211, 238, .3);
        }

        .tick svg {
            fill: #05121f
        }

        .illus {
            min-height: 260px;
            border-radius: 16px;
            position: relative;
            overflow: hidden;
            background:
                radial-gradient(700px 180px at 60% 0%, rgba(124, 58, 237, .25), transparent 60%),
                radial-gradient(600px 220px at -5% 100%, rgba(34, 211, 238, .25), transparent 60%),
                linear-gradient(160deg, rgba(255, 255, 255, .06), rgba(255, 255, 255, .02));
            border: 1px solid rgba(148, 163, 184, .18);
            box-shadow: 0 18px 40px rgba(2, 6, 23, .45);
        }

        .bubble {
            position: absolute;
            border-radius: 50%;
            filter: blur(6px);
            opacity: .7;
            mix-blend-mode: screen;
            animation: float 12s ease-in-out infinite;
        }

        .bubble.one {
            width: 220px;
            height: 220px;
            left: -40px;
            bottom: -40px;
            background: #22d3ee33
        }

        .bubble.two {
            width: 260px;
            height: 260px;
            right: -60px;
            top: -60px;
            background: #7c3aed33;
            animation-delay: 2s
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(0)
            }

            50% {
                transform: translateY(-14px)
            }
        }

        /* footer */
        footer {
            opacity: .7;
            padding: 20px 0;
            text-align: center;
            color: var(--muted);
            font-size: 13px
        }
    </style>
</head>

<body>
    <div class="container">

        <!-- Header -->
        <div class="nav">
            <a class="brand" href="{{ url('/') }}">
                <span class="logo"></span>
                <span>EventBook</span>
            </a>

            <div class="gap-10">
                @auth
                    <a href="{{ route('dashboard') }}" class="btn btn-ghost" style="padding:10px 14px">Dashboard</a>
                    <form method="POST" action="{{ route('logout') }}" style="margin:0">
                        @csrf
                        <button class="btn btn-primary" style="padding:10px 14px">Log out</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="btn btn-ghost" style="padding:10px 14px">Log in</a>
                    <a href="{{ route('register') }}" class="btn btn-primary" style="padding:10px 14px">Register</a>
                @endauth
            </div>
        </div>

        <!-- Content -->
        <div class="split" style="margin-top:18px">
            <div class="card hero">
                <span class="chip">Event Booking Manager</span>
                <h1>Plan events, book seats,<br>and track occupancy in real-time.</h1>
                <p>Browse upcoming events, reserve tickets without overbooking, and view admin insights like top events
                    and power users — all in one lightweight dashboard.</p>

                <div class="gap-10" style="margin-top:18px;flex-wrap:wrap">
                    <a href="{{ route('events.index') }}" class="btn btn-primary">Browse Events</a>

                    @auth
                        @if(auth()->user()->is_admin)
                            <a class="btn btn-ghost" href="{{ route('admin.events.index') }}">Admin: Manage Events</a>
                            <a class="btn btn-ghost" href="{{ route('admin.reports.dashboard') }}">View Reports</a>
                        @else
                            <a class="btn btn-ghost" href="{{ route('events.index') }}">My Bookings</a>
                        @endif
                    @else
                        <a class="btn btn-ghost" href="{{ route('login') }}">Log in to Book</a>
                    @endauth
                </div>
            </div>

            <div class="card">
                <h3 style="margin:0 0 10px">What’s inside</h3>
                <div class="features">
                    <div class="feature">
                        <span class="tick">
                            <svg width="13" height="13" viewBox="0 0 16 16">
                                <path d="M6.2 11.3 3 8.1l1.4-1.4 1.8 1.8L11.6 3l1.4 1.4-6.8 6.9z" />
                            </svg>
                        </span>
                        <div><b>Email/Password</b> + Google/GitHub login</div>
                    </div>
                    <div class="feature">
                        <span class="tick"><svg width="13" height="13" viewBox="0 0 16 16">
                                <path d="M6.2 11.3 3 8.1l1.4-1.4 1.8 1.8L11.6 3l1.4 1.4-6.8 6.9z" />
                            </svg></span>
                        <div>Event creation (title, venue, capacity, date)</div>
                    </div>
                    <div class="feature">
                        <span class="tick"><svg width="13" height="13" viewBox="0 0 16 16">
                                <path d="M6.2 11.3 3 8.1l1.4-1.4 1.8 1.8L11.6 3l1.4 1.4-6.8 6.9z" />
                            </svg></span>
                        <div>Booking with DB transactions (no overbooking)</div>
                    </div>
                    <div class="feature">
                        <span class="tick"><svg width="13" height="13" viewBox="0 0 16 16">
                                <path d="M6.2 11.3 3 8.1l1.4-1.4 1.8 1.8L11.6 3l1.4 1.4-6.8 6.9z" />
                            </svg></span>
                        <div>Search, filter, & pagination</div>
                    </div>
                    <div class="feature">
                        <span class="tick"><svg width="13" height="13" viewBox="0 0 16 16">
                                <path d="M6.2 11.3 3 8.1l1.4-1.4 1.8 1.8L11.6 3l1.4 1.4-6.8 6.9z" />
                            </svg></span>
                        <div>Admin reports: top events, power users, occupancy%</div>
                    </div>
                </div>

                <div class="illus" style="margin-top:18px">
                    <div class="bubble one"></div>
                    <div class="bubble two"></div>
                </div>
            </div>
        </div>

        <footer>© {{ date('Y') }} EventBook • Built with Laravel</footer>
    </div>
</body>

</html>