<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Event Hub am Checkpoint Charlie – Berlin</title>
    <meta name="description" content="Premium event venue at the historic Checkpoint Charlie in Berlin. Corporate events, galas, product launches, and private celebrations.">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=playfair-display:400,500,700|dm-sans:300,400,500,600" rel="stylesheet" />

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif

    <style>
        :root {
            --cream:     #faf8f4;
            --white:     #ffffff;
            --ink:       #1c1814;
            --ink-mid:   #4a4540;
            --ink-soft:  #8a837a;
            --gold:      #b5913a;
            --gold-light:#d4aa58;
            --gold-pale: #f4ead8;
            --border:    #e8e3da;
            --shadow-sm: 0 1px 4px rgba(28,24,20,0.06);
            --shadow-md: 0 4px 20px rgba(28,24,20,0.08);
            --shadow-lg: 0 12px 48px rgba(28,24,20,0.1);
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        html { scroll-behavior: smooth; font-size: 16px; }

        body {
            background: var(--cream);
            color: var(--ink);
            font-family: 'DM Sans', system-ui, sans-serif;
            line-height: 1.6;
            overflow-x: hidden;
            -webkit-font-smoothing: antialiased;
        }

        .serif { font-family: 'Playfair Display', Georgia, serif; }

        /* ── Navigation ── */
        nav {
            position: fixed;
            top: 0; left: 0; right: 0;
            z-index: 900;
            padding: 1.25rem 3rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            transition: background 0.35s, box-shadow 0.35s, padding 0.35s;
        }
        nav.scrolled {
            background: rgba(250, 248, 244, 0.95);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            box-shadow: var(--shadow-sm);
            padding: 0.9rem 3rem;
            border-bottom: 1px solid var(--border);
        }
        .nav-logo {
            font-family: 'Playfair Display', serif;
            font-size: 1.1rem;
            color: var(--ink);
            text-decoration: none;
            letter-spacing: 0.01em;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .nav-logo-dot {
            width: 5px; height: 5px;
            border-radius: 50%;
            background: var(--gold);
        }
        .nav-links {
            display: flex;
            align-items: center;
            gap: 2.25rem;
            list-style: none;
        }
        .nav-links a {
            color: var(--ink-soft);
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 500;
            letter-spacing: 0.04em;
            transition: color 0.2s;
        }
        .nav-links a:hover { color: var(--ink); }
        .nav-cta {
            background: var(--ink);
            color: var(--white);
            padding: 0.55rem 1.4rem;
            font-size: 0.82rem;
            font-weight: 500;
            letter-spacing: 0.04em;
            text-decoration: none;
            border-radius: 2px;
            transition: background 0.2s, transform 0.2s;
        }
        .nav-cta:hover {
            background: var(--gold);
            transform: translateY(-1px);
        }

        /* ── Hero ── */
        .hero {
            min-height: 100vh;
            display: grid;
            grid-template-columns: 1fr 1fr;
            align-items: center;
            padding-top: 80px;
            max-width: 1300px;
            margin: 0 auto;
            padding-left: 3rem;
            padding-right: 3rem;
            gap: 4rem;
        }
        .hero-content {
            padding: 4rem 0;
        }
        .hero-tag {
            display: inline-flex;
            align-items: center;
            gap: 0.6rem;
            color: var(--gold);
            font-size: 0.72rem;
            font-weight: 600;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            margin-bottom: 1.75rem;
        }
        .hero-tag::before {
            content: '';
            width: 28px; height: 1px;
            background: var(--gold);
        }
        .hero-title {
            font-family: 'Playfair Display', serif;
            font-size: clamp(2.75rem, 5vw, 4.5rem);
            font-weight: 700;
            line-height: 1.1;
            letter-spacing: -0.02em;
            color: var(--ink);
            margin-bottom: 1.5rem;
        }
        .hero-title em {
            font-style: italic;
            color: var(--gold);
        }
        .hero-desc {
            font-size: 1.05rem;
            color: var(--ink-mid);
            line-height: 1.8;
            max-width: 460px;
            margin-bottom: 2.5rem;
            font-weight: 300;
        }
        .hero-actions {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }
        .btn-primary {
            background: var(--ink);
            color: var(--white);
            padding: 0.85rem 2rem;
            font-size: 0.85rem;
            font-weight: 500;
            letter-spacing: 0.05em;
            text-decoration: none;
            border-radius: 2px;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: background 0.2s, transform 0.2s, box-shadow 0.2s;
        }
        .btn-primary:hover {
            background: var(--gold);
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(181,145,58,0.25);
        }
        .btn-outline {
            background: transparent;
            color: var(--ink);
            padding: 0.85rem 2rem;
            font-size: 0.85rem;
            font-weight: 500;
            letter-spacing: 0.05em;
            text-decoration: none;
            border-radius: 2px;
            border: 1px solid var(--border);
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: border-color 0.2s, color 0.2s, transform 0.2s;
        }
        .btn-outline:hover {
            border-color: var(--ink);
            transform: translateY(-2px);
        }
        .hero-visual {
            position: relative;
            height: 580px;
        }
        .hero-img-block {
            position: absolute;
            inset: 0;
            background: var(--white);
            border-radius: 4px;
            box-shadow: var(--shadow-lg);
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .hero-img-block svg {
            width: 100%;
            height: 100%;
        }
        .hero-badge {
            position: absolute;
            bottom: -1.5rem;
            left: -2rem;
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: 4px;
            padding: 1.25rem 1.5rem;
            box-shadow: var(--shadow-md);
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        .hero-badge-num {
            font-family: 'Playfair Display', serif;
            font-size: 2rem;
            font-weight: 700;
            color: var(--gold);
            line-height: 1;
        }
        .hero-badge-text {
            font-size: 0.75rem;
            color: var(--ink-soft);
            font-weight: 500;
            line-height: 1.4;
        }
        .hero-badge-text strong {
            display: block;
            color: var(--ink);
            font-size: 0.85rem;
        }

        /* ── Stats ── */
        .stats-section {
            background: var(--ink);
            margin: 5rem 0 0;
        }
        .stats-inner {
            max-width: 1300px;
            margin: 0 auto;
            padding: 3.5rem 3rem;
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 2rem;
        }
        .stat-item {
            text-align: center;
            position: relative;
        }
        .stat-item:not(:last-child)::after {
            content: '';
            position: absolute;
            right: 0; top: 15%; height: 70%;
            width: 1px;
            background: rgba(255,255,255,0.08);
        }
        .stat-num {
            font-family: 'Playfair Display', serif;
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--gold-light);
            display: block;
            line-height: 1;
        }
        .stat-label {
            font-size: 0.75rem;
            color: rgba(255,255,255,0.45);
            letter-spacing: 0.1em;
            text-transform: uppercase;
            display: block;
            margin-top: 0.4rem;
        }

        /* ── Section base ── */
        section { position: relative; }
        .section-inner {
            max-width: 1300px;
            margin: 0 auto;
            padding: 6rem 3rem;
        }
        .section-tag {
            display: inline-flex;
            align-items: center;
            gap: 0.6rem;
            color: var(--gold);
            font-size: 0.7rem;
            font-weight: 600;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            margin-bottom: 1rem;
        }
        .section-tag::before {
            content: '';
            width: 20px; height: 1px;
            background: var(--gold);
        }
        .section-title {
            font-family: 'Playfair Display', serif;
            font-size: clamp(1.9rem, 4vw, 2.9rem);
            font-weight: 700;
            line-height: 1.15;
            color: var(--ink);
            margin-bottom: 1.25rem;
        }
        .section-title em {
            font-style: italic;
            color: var(--gold);
        }
        .divider {
            width: 40px; height: 2px;
            background: var(--gold);
            margin-bottom: 2rem;
            border-radius: 1px;
        }

        /* ── About ── */
        .about-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 5rem;
            align-items: center;
        }
        .about-visual {
            position: relative;
        }
        .about-frame {
            aspect-ratio: 4/5;
            background: var(--white);
            border-radius: 4px;
            box-shadow: var(--shadow-lg);
            overflow: hidden;
            position: relative;
        }
        .about-frame-accent {
            position: absolute;
            bottom: -1.25rem;
            right: -1.25rem;
            width: 160px; height: 160px;
            border: 1.5px solid var(--border);
            border-radius: 4px;
            z-index: -1;
        }
        .about-frame-accent-2 {
            position: absolute;
            top: -1.25rem;
            left: -1.25rem;
            width: 72px; height: 72px;
            border: 1.5px solid var(--gold-pale);
            border-radius: 4px;
            z-index: -1;
        }
        .about-year-badge {
            position: absolute;
            bottom: 1.75rem;
            right: -2rem;
            background: var(--gold);
            color: var(--white);
            padding: 1.25rem 1.5rem;
            border-radius: 3px;
            text-align: center;
            box-shadow: var(--shadow-md);
            z-index: 2;
        }
        .about-year-badge-num {
            font-family: 'Playfair Display', serif;
            font-size: 1.8rem;
            font-weight: 700;
            display: block;
            line-height: 1;
        }
        .about-year-badge-text {
            font-size: 0.65rem;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            display: block;
            margin-top: 0.2rem;
            opacity: 0.85;
        }
        .about-text p {
            color: var(--ink-mid);
            font-size: 0.97rem;
            line-height: 1.85;
            margin-bottom: 1.1rem;
        }
        .about-features {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.75rem;
            margin-top: 2rem;
        }
        .about-feat {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            padding: 0.75rem 1rem;
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: 4px;
            font-size: 0.82rem;
            color: var(--ink-mid);
            font-weight: 500;
        }
        .about-feat svg { color: var(--gold); flex-shrink: 0; }

        /* ── Spaces ── */
        .spaces-section { background: var(--white); }
        .spaces-header {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            margin-bottom: 3rem;
        }
        .spaces-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1.5rem;
        }
        .space-card {
            background: var(--cream);
            border: 1px solid var(--border);
            border-radius: 6px;
            padding: 2.25rem;
            position: relative;
            overflow: hidden;
            transition: box-shadow 0.3s, transform 0.3s, border-color 0.3s;
        }
        .space-card:hover {
            box-shadow: var(--shadow-lg);
            transform: translateY(-4px);
            border-color: rgba(181,145,58,0.3);
        }
        .space-card::after {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 3px;
            background: linear-gradient(90deg, var(--gold), var(--gold-light));
            border-radius: 6px 6px 0 0;
            opacity: 0;
            transition: opacity 0.3s;
        }
        .space-card:hover::after { opacity: 1; }
        .space-num {
            font-family: 'Playfair Display', serif;
            font-size: 3rem;
            font-weight: 700;
            color: var(--border);
            line-height: 1;
            margin-bottom: 1rem;
            letter-spacing: -0.05em;
        }
        .space-name {
            font-family: 'Playfair Display', serif;
            font-size: 1.25rem;
            color: var(--ink);
            margin-bottom: 0.75rem;
        }
        .space-desc {
            font-size: 0.88rem;
            color: var(--ink-soft);
            line-height: 1.75;
            margin-bottom: 1.5rem;
        }
        .space-capacity {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--gold);
            letter-spacing: 0.06em;
            background: var(--gold-pale);
            padding: 0.3rem 0.75rem;
            border-radius: 20px;
        }

        /* ── Features ── */
        .features-section { background: var(--cream); }
        .features-layout {
            display: grid;
            grid-template-columns: 1fr 1.4fr;
            gap: 5rem;
            align-items: start;
        }
        .features-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.25rem;
        }
        .feat-card {
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: 6px;
            padding: 1.5rem;
            transition: box-shadow 0.25s, transform 0.25s;
        }
        .feat-card:hover {
            box-shadow: var(--shadow-md);
            transform: translateY(-2px);
        }
        .feat-icon {
            width: 40px; height: 40px;
            background: var(--gold-pale);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--gold);
            margin-bottom: 1rem;
        }
        .feat-title {
            font-family: 'Playfair Display', serif;
            font-size: 1rem;
            color: var(--ink);
            margin-bottom: 0.35rem;
        }
        .feat-desc {
            font-size: 0.82rem;
            color: var(--ink-soft);
            line-height: 1.7;
        }

        /* ── Location ── */
        .location-section { background: var(--white); }
        .location-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 5rem;
            align-items: center;
        }
        .location-detail {
            display: flex;
            align-items: flex-start;
            gap: 0.9rem;
            padding: 1.1rem 0;
            border-bottom: 1px solid var(--border);
        }
        .location-detail:first-child { border-top: 1px solid var(--border); }
        .ld-icon {
            color: var(--gold);
            flex-shrink: 0;
            margin-top: 2px;
        }
        .ld-label {
            font-size: 0.68rem;
            font-weight: 600;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: var(--ink-soft);
            display: block;
            margin-bottom: 0.2rem;
        }
        .ld-value {
            font-size: 0.93rem;
            color: var(--ink);
        }
        .map-block {
            background: var(--cream);
            border: 1px solid var(--border);
            border-radius: 6px;
            aspect-ratio: 1;
            overflow: hidden;
            position: relative;
            box-shadow: var(--shadow-md);
        }

        /* ── CTA ── */
        .cta-section {
            background: var(--ink);
            position: relative;
            overflow: hidden;
        }
        .cta-section::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image:
                linear-gradient(rgba(255,255,255,0.025) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,0.025) 1px, transparent 1px);
            background-size: 72px 72px;
        }
        .cta-inner {
            position: relative;
            z-index: 2;
            max-width: 700px;
            margin: 0 auto;
            padding: 7rem 3rem;
            text-align: center;
        }
        .cta-title {
            font-family: 'Playfair Display', serif;
            font-size: clamp(2.25rem, 5vw, 3.5rem);
            font-weight: 700;
            color: var(--white);
            margin-bottom: 1rem;
            line-height: 1.15;
        }
        .cta-title em { color: var(--gold-light); font-style: italic; }
        .cta-sub {
            font-size: 1rem;
            color: rgba(255,255,255,0.55);
            margin-bottom: 2.5rem;
            font-weight: 300;
            line-height: 1.75;
        }
        .cta-actions {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }
        .btn-gold {
            background: var(--gold);
            color: var(--white);
            padding: 0.85rem 2.25rem;
            font-size: 0.85rem;
            font-weight: 500;
            letter-spacing: 0.05em;
            text-decoration: none;
            border-radius: 2px;
            transition: background 0.2s, transform 0.2s, box-shadow 0.2s;
        }
        .btn-gold:hover {
            background: var(--gold-light);
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(181,145,58,0.35);
        }
        .btn-white-outline {
            background: transparent;
            color: rgba(255,255,255,0.7);
            padding: 0.85rem 2.25rem;
            font-size: 0.85rem;
            font-weight: 500;
            letter-spacing: 0.05em;
            text-decoration: none;
            border-radius: 2px;
            border: 1px solid rgba(255,255,255,0.2);
            transition: border-color 0.2s, color 0.2s;
        }
        .btn-white-outline:hover {
            border-color: rgba(255,255,255,0.6);
            color: var(--white);
        }

        /* ── Footer ── */
        footer {
            background: var(--ink);
            border-top: 1px solid rgba(255,255,255,0.06);
            padding: 3.5rem 3rem 2rem;
        }
        .footer-inner {
            max-width: 1300px;
            margin: 0 auto;
        }
        .footer-top {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr;
            gap: 3rem;
            padding-bottom: 3rem;
            border-bottom: 1px solid rgba(255,255,255,0.06);
        }
        .footer-brand {
            font-family: 'Playfair Display', serif;
            font-size: 1.1rem;
            color: var(--white);
            margin-bottom: 0.6rem;
        }
        .footer-brand-desc {
            font-size: 0.83rem;
            color: rgba(255,255,255,0.4);
            line-height: 1.7;
            max-width: 280px;
        }
        .footer-col-title {
            font-size: 0.68rem;
            font-weight: 600;
            letter-spacing: 0.18em;
            text-transform: uppercase;
            color: var(--gold-light);
            margin-bottom: 1.1rem;
        }
        .footer-links {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 0.55rem;
        }
        .footer-links a {
            font-size: 0.84rem;
            color: rgba(255,255,255,0.45);
            text-decoration: none;
            transition: color 0.2s;
        }
        .footer-links a:hover { color: var(--white); }
        .footer-bottom {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-top: 1.75rem;
            flex-wrap: wrap;
            gap: 1rem;
        }
        .footer-copy {
            font-size: 0.78rem;
            color: rgba(255,255,255,0.3);
        }
        .footer-auth {
            display: flex;
            gap: 1.25rem;
        }
        .footer-auth a {
            font-size: 0.78rem;
            color: rgba(255,255,255,0.35);
            text-decoration: none;
            transition: color 0.2s;
        }
        .footer-auth a:hover { color: rgba(255,255,255,0.75); }

        /* ── Reveal animations ── */
        .reveal {
            opacity: 0;
            transform: translateY(20px);
            transition: opacity 0.6s ease, transform 0.6s ease;
        }
        .reveal.visible {
            opacity: 1;
            transform: translateY(0);
        }
        .reveal-delay-1 { transition-delay: 0.1s; }
        .reveal-delay-2 { transition-delay: 0.2s; }
        .reveal-delay-3 { transition-delay: 0.3s; }

        /* ── Responsive ── */
        @media (max-width: 1024px) {
            .hero { grid-template-columns: 1fr; padding-top: 120px; gap: 2rem; }
            .hero-visual { height: 420px; }
            .about-grid, .location-grid { grid-template-columns: 1fr; gap: 3rem; }
            .features-layout { grid-template-columns: 1fr; gap: 3rem; }
        }
        @media (max-width: 768px) {
            nav { padding: 1rem 1.5rem; }
            nav.scrolled { padding: 0.75rem 1.5rem; }
            .nav-links { display: none; }
            .hero { padding: 100px 1.5rem 3rem; }
            .section-inner { padding: 4rem 1.5rem; }
            .stats-inner { grid-template-columns: repeat(2, 1fr); padding: 3rem 1.5rem; }
            .stat-item:nth-child(2)::after { display: none; }
            .spaces-grid { grid-template-columns: 1fr; }
            .features-grid { grid-template-columns: 1fr; }
            .about-badge { right: 0; }
            .footer-top { grid-template-columns: 1fr; gap: 2rem; }
            .footer { padding: 3rem 1.5rem 1.5rem; }
            .cta-inner { padding: 5rem 1.5rem; }
            .spaces-header { flex-direction: column; align-items: flex-start; gap: 1rem; }
        }
    </style>
</head>
<body>

    <!-- Navigation -->
    <nav id="navbar">
        <a href="#" class="nav-logo">
            <span class="nav-logo-dot"></span>
            Event Hub · Checkpoint Charlie
        </a>
        <ul class="nav-links">
            <li><a href="#about">About</a></li>
            <li><a href="#spaces">Spaces</a></li>
            <li><a href="#features">Services</a></li>
            <li><a href="#location">Location</a></li>
        </ul>
        <a href="#contact" class="nav-cta">Book a Venue</a>
    </nav>

    <!-- Hero -->
    <section class="hero" id="hero">
        <div class="hero-content">
            <div class="hero-tag">Berlin's Historic Heart</div>
            <h1 class="hero-title">
                Where History<br>Meets <em>Modern Events</em>
            </h1>
            <p class="hero-desc">
                A premium event venue at the iconic Checkpoint Charlie — where Berlin's storied
                past becomes the backdrop for your most extraordinary moments.
            </p>
            <div class="hero-actions">
                <a href="#spaces" class="btn-primary">Explore Our Spaces</a>
                <a href="#contact" class="btn-outline">Request a Quote</a>
            </div>
        </div>

        <div class="hero-visual">
            <div class="hero-img-block">
                <svg viewBox="0 0 600 680" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <defs>
                        <linearGradient id="skyG" x1="0" y1="0" x2="0" y2="1">
                            <stop offset="0%" stop-color="#dfe8f5"/>
                            <stop offset="100%" stop-color="#f0e8d8"/>
                        </linearGradient>
                        <linearGradient id="bldG" x1="0" y1="0" x2="0" y2="1">
                            <stop offset="0%" stop-color="#d8d0c4"/>
                            <stop offset="100%" stop-color="#b8b0a4"/>
                        </linearGradient>
                        <linearGradient id="goldG" x1="0" y1="0" x2="1" y2="0">
                            <stop offset="0%" stop-color="#9a7a3a"/>
                            <stop offset="100%" stop-color="#c9a96e"/>
                        </linearGradient>
                    </defs>
                    <!-- Sky -->
                    <rect width="600" height="680" fill="url(#skyG)"/>
                    <!-- Subtle cloud shapes -->
                    <ellipse cx="120" cy="80" rx="90" ry="22" fill="rgba(255,255,255,0.5)"/>
                    <ellipse cx="460" cy="50" rx="70" ry="18" fill="rgba(255,255,255,0.4)"/>
                    <ellipse cx="320" cy="110" rx="110" ry="20" fill="rgba(255,255,255,0.35)"/>
                    <!-- Ground -->
                    <rect x="0" y="550" width="600" height="130" fill="#e8e0d0"/>
                    <!-- Road -->
                    <rect x="210" y="480" width="180" height="200" fill="#d0c8b8"/>
                    <!-- Road dashes -->
                    <rect x="297" y="510" width="6" height="28" fill="rgba(181,145,58,0.4)" rx="3"/>
                    <rect x="297" y="548" width="6" height="28" fill="rgba(181,145,58,0.4)" rx="3"/>
                    <rect x="297" y="586" width="6" height="28" fill="rgba(181,145,58,0.4)" rx="3"/>
                    <!-- Main building -->
                    <rect x="190" y="340" width="220" height="230" fill="url(#bldG)"/>
                    <!-- Building shadow -->
                    <rect x="190" y="340" width="220" height="10" fill="rgba(0,0,0,0.08)"/>
                    <!-- Windows -->
                    <rect x="214" y="362" width="38" height="32" fill="rgba(255,255,255,0.7)" rx="1"/>
                    <rect x="261" y="362" width="38" height="32" fill="rgba(181,145,58,0.25)" rx="1"/>
                    <rect x="308" y="362" width="38" height="32" fill="rgba(255,255,255,0.7)" rx="1"/>
                    <rect x="355" y="362" width="38" height="32" fill="rgba(255,255,255,0.5)" rx="1"/>
                    <rect x="214" y="406" width="38" height="32" fill="rgba(255,255,255,0.5)" rx="1"/>
                    <rect x="308" y="406" width="38" height="32" fill="rgba(181,145,58,0.2)" rx="1"/>
                    <rect x="355" y="406" width="38" height="32" fill="rgba(255,255,255,0.6)" rx="1"/>
                    <!-- Door -->
                    <rect x="272" y="465" width="56" height="105" fill="rgba(0,0,0,0.12)" rx="1"/>
                    <rect x="275" y="468" width="50" height="99" fill="#c4bdb0" rx="1"/>
                    <!-- Roof accent line -->
                    <rect x="186" y="332" width="228" height="10" fill="url(#goldG)" rx="1" opacity="0.85"/>
                    <!-- Columns -->
                    <rect x="220" y="342" width="8" height="228" fill="rgba(0,0,0,0.07)"/>
                    <rect x="270" y="342" width="8" height="228" fill="rgba(0,0,0,0.07)"/>
                    <rect x="372" y="342" width="8" height="228" fill="rgba(0,0,0,0.07)"/>
                    <!-- Barrier poles -->
                    <rect x="136" y="410" width="8" height="160" fill="#b8b0a0"/>
                    <rect x="127" y="405" width="26" height="8" fill="url(#goldG)" opacity="0.9" rx="1"/>
                    <rect x="136" y="412" width="74" height="5" fill="rgba(181,145,58,0.6)" rx="2.5"/>
                    <rect x="456" y="410" width="8" height="160" fill="#b8b0a0"/>
                    <rect x="447" y="405" width="26" height="8" fill="url(#goldG)" opacity="0.9" rx="1"/>
                    <rect x="390" y="412" width="74" height="5" fill="rgba(181,145,58,0.6)" rx="2.5"/>
                    <!-- Sign -->
                    <rect x="248" y="296" width="104" height="34" fill="white" stroke="#c9a96e" stroke-width="1" rx="2" opacity="0.95"/>
                    <text x="300" y="312" text-anchor="middle" font-size="9" fill="#b5913a" font-family="Georgia, serif" letter-spacing="1" font-weight="bold">CHECKPOINT</text>
                    <text x="300" y="323" text-anchor="middle" font-size="7.5" fill="#9a7a3a" font-family="Georgia, serif" letter-spacing="2">CHARLIE</text>
                    <!-- Flag pole -->
                    <line x1="300" y1="180" x2="300" y2="296" stroke="#b8b0a0" stroke-width="2.5"/>
                    <rect x="300" y="180" width="50" height="32" fill="rgba(181,145,58,0.45)" rx="1"/>
                    <!-- Ground shadow -->
                    <ellipse cx="300" cy="555" rx="130" ry="14" fill="rgba(0,0,0,0.05)"/>
                    <!-- Side buildings -->
                    <rect x="0" y="380" width="185" height="300" fill="#ccc4b4"/>
                    <rect x="415" y="360" width="185" height="300" fill="#ccc4b4"/>
                    <rect x="20" y="400" width="30" height="24" fill="rgba(255,255,255,0.5)" rx="1"/>
                    <rect x="60" y="400" width="30" height="24" fill="rgba(255,255,255,0.4)" rx="1"/>
                    <rect x="100" y="400" width="30" height="24" fill="rgba(255,255,255,0.5)" rx="1"/>
                    <rect x="430" y="380" width="30" height="24" fill="rgba(255,255,255,0.5)" rx="1"/>
                    <rect x="470" y="380" width="30" height="24" fill="rgba(255,255,255,0.4)" rx="1"/>
                    <rect x="510" y="380" width="30" height="24" fill="rgba(255,255,255,0.5)" rx="1"/>
                    <!-- Gold ground line -->
                    <line x1="0" y1="676" x2="600" y2="676" stroke="url(#goldG)" stroke-width="2" opacity="0.5"/>
                </svg>
            </div>
            <div class="hero-badge">
                <div class="hero-badge-num">★</div>
                <div class="hero-badge-text">
                    <strong>Since 2009</strong>
                    15 years of excellence
                </div>
            </div>
        </div>
    </section>

    <!-- Stats -->
    <div class="stats-section">
        <div class="stats-inner">
            <div class="stat-item reveal">
                <span class="stat-num" data-target="500" data-suffix="+">500+</span>
                <span class="stat-label">Events Hosted</span>
            </div>
            <div class="stat-item reveal reveal-delay-1">
                <span class="stat-num" data-target="3">3</span>
                <span class="stat-label">Unique Spaces</span>
            </div>
            <div class="stat-item reveal reveal-delay-2">
                <span class="stat-num" data-target="450">450</span>
                <span class="stat-label">Guest Capacity</span>
            </div>
            <div class="stat-item reveal reveal-delay-3">
                <span class="stat-num" data-target="15" data-suffix=" yr">15 yr</span>
                <span class="stat-label">Of Excellence</span>
            </div>
        </div>
    </div>

    <!-- About -->
    <section id="about">
        <div class="section-inner">
            <div class="about-grid">
                <div class="about-visual reveal">
                    <div class="about-frame-accent-2"></div>
                    <div class="about-frame">
                        <svg viewBox="0 0 420 500" fill="none" xmlns="http://www.w3.org/2000/svg" style="width:100%;height:100%;">
                            <defs>
                                <linearGradient id="skyG2" x1="0" y1="0" x2="0" y2="1">
                                    <stop offset="0%" stop-color="#dce6f5"/>
                                    <stop offset="100%" stop-color="#eee6d8"/>
                                </linearGradient>
                                <linearGradient id="goldG2" x1="0" y1="0" x2="1" y2="0">
                                    <stop offset="0%" stop-color="#9a7a3a"/>
                                    <stop offset="100%" stop-color="#c9a96e"/>
                                </linearGradient>
                            </defs>
                            <rect width="420" height="500" fill="url(#skyG2)"/>
                            <rect x="0" y="420" width="420" height="80" fill="#e8e0d0"/>
                            <rect x="150" y="350" width="120" height="150" fill="#d0c8b8"/>
                            <rect x="207" y="370" width="6" height="20" fill="rgba(181,145,58,0.45)" rx="3"/>
                            <rect x="207" y="400" width="6" height="20" fill="rgba(181,145,58,0.45)" rx="3"/>
                            <rect x="207" y="430" width="6" height="20" fill="rgba(181,145,58,0.45)" rx="3"/>
                            <rect x="140" y="260" width="140" height="160" fill="#ccc5b5"/>
                            <rect x="140" y="250" width="140" height="12" fill="url(#goldG2)" opacity="0.7"/>
                            <rect x="160" y="280" width="30" height="25" fill="rgba(255,255,255,0.7)" rx="1"/>
                            <rect x="200" y="280" width="30" height="25" fill="rgba(181,145,58,0.2)" rx="1"/>
                            <rect x="230" y="280" width="30" height="25" fill="rgba(255,255,255,0.65)" rx="1"/>
                            <rect x="160" y="315" width="30" height="25" fill="rgba(255,255,255,0.5)" rx="1"/>
                            <rect x="230" y="315" width="30" height="25" fill="rgba(255,255,255,0.5)" rx="1"/>
                            <rect x="194" y="360" width="32" height="60" fill="#b8b0a0"/>
                            <rect x="100" y="310" width="6" height="130" fill="#aaa098"/>
                            <rect x="93" y="305" width="20" height="6" fill="url(#goldG2)" opacity="0.8"/>
                            <rect x="100" y="311" width="60" height="4" fill="rgba(181,145,58,0.55)" rx="2"/>
                            <rect x="314" y="310" width="6" height="130" fill="#aaa098"/>
                            <rect x="307" y="305" width="20" height="6" fill="url(#goldG2)" opacity="0.8"/>
                            <rect x="260" y="311" width="60" height="4" fill="rgba(181,145,58,0.55)" rx="2"/>
                            <rect x="175" y="225" width="70" height="28" fill="white" stroke="#c9a96e" stroke-width="0.8" rx="2" opacity="0.95"/>
                            <text x="210" y="243" text-anchor="middle" font-size="8" fill="#b5913a" font-family="Georgia, serif" letter-spacing="0.5" font-weight="bold">CHECKPOINT</text>
                            <text x="210" y="252" text-anchor="middle" font-size="6" fill="#9a7a3a" font-family="Georgia, serif" letter-spacing="1">CHARLIE</text>
                            <line x1="210" y1="130" x2="210" y2="225" stroke="#aaa098" stroke-width="1.5"/>
                            <rect x="210" y="130" width="35" height="22" fill="rgba(181,145,58,0.4)" rx="1"/>
                            <line x1="0" y1="498" x2="420" y2="498" stroke="url(#goldG2)" stroke-width="1.5" opacity="0.5"/>
                        </svg>
                        <div class="about-year-badge">
                            <span class="about-year-badge-num">★</span>
                            <span class="about-year-badge-text">Since 2009</span>
                        </div>
                    </div>
                    <div class="about-frame-accent"></div>
                </div>

                <div class="about-text reveal reveal-delay-2">
                    <div class="section-tag">Our Story</div>
                    <h2 class="section-title">
                        At the Crossroads of<br><em>History & Elegance</em>
                    </h2>
                    <div class="divider"></div>
                    <p>
                        Event Hub am Checkpoint Charlie is Berlin's most distinctively located event venue,
                        situated steps from the legendary crossing point that once defined a divided world.
                        Today, this iconic address is home to unforgettable gatherings.
                    </p>
                    <p>
                        From intimate boardroom sessions to grand gala evenings, our spaces marry the
                        gravitas of Cold War history with the refined aesthetic of contemporary Berlin —
                        a backdrop unlike any other in the world.
                    </p>

                    <div class="about-features">
                        <div class="about-feat">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                            Fully Licensed & Insured
                        </div>
                        <div class="about-feat">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
                            24/7 Event Coordination
                        </div>
                        <div class="about-feat">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                            Up to 450 Guests
                        </div>
                        <div class="about-feat">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                            Award-Winning Service
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Spaces -->
    <section id="spaces" class="spaces-section">
        <div class="section-inner">
            <div class="spaces-header reveal">
                <div>
                    <div class="section-tag">Our Venues</div>
                    <h2 class="section-title">Three Distinct <em>Spaces</em></h2>
                </div>
                <a href="#contact" class="btn-outline">View All Spaces</a>
            </div>

            <div class="spaces-grid">
                <div class="space-card reveal">
                    <div class="space-num">01</div>
                    <h3 class="space-name">The Grand Ballroom</h3>
                    <p class="space-desc">
                        Our flagship space — a sweeping hall with soaring ceilings, crystal lighting,
                        and panoramic views of the historic Friedrichstraße. Perfect for galas,
                        award ceremonies, and wedding receptions.
                    </p>
                    <span class="space-capacity">Up to 450 guests</span>
                </div>

                <div class="space-card reveal reveal-delay-1">
                    <div class="space-num">02</div>
                    <h3 class="space-name">The Charlie Suite</h3>
                    <p class="space-desc">
                        An intimate mid-size venue infused with Cold War-era artworks and memorabilia.
                        Ideal for corporate dinners, product launches, and exclusive presentations
                        with a uniquely Berlin character.
                    </p>
                    <span class="space-capacity">Up to 180 guests</span>
                </div>

                <div class="space-card reveal reveal-delay-2">
                    <div class="space-num">03</div>
                    <h3 class="space-name">The Boardroom</h3>
                    <p class="space-desc">
                        An executive meeting room designed for focus and privacy. Full AV equipment,
                        high-speed connectivity, and catering on demand — all in an atmosphere
                        that inspires strategic thinking.
                    </p>
                    <span class="space-capacity">Up to 30 guests</span>
                </div>
            </div>
        </div>
    </section>

    <!-- Features -->
    <section id="features" class="features-section">
        <div class="section-inner">
            <div class="features-layout">
                <div class="reveal">
                    <div class="section-tag">What We Offer</div>
                    <h2 class="section-title">Every Detail,<br><em>Perfected</em></h2>
                    <div class="divider"></div>
                    <p style="color: var(--ink-mid); line-height: 1.85; font-size: 0.95rem;">
                        We believe extraordinary events begin long before the first guest arrives.
                        Our team works with you from concept to conclusion, ensuring every element
                        reflects your vision with precision and artistry.
                    </p>
                </div>

                <div class="features-grid">
                    <div class="feat-card reveal">
                        <div class="feat-icon">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                        </div>
                        <div class="feat-title">Bespoke Interiors</div>
                        <div class="feat-desc">Fully customisable décor packages tailored to your event theme and brand.</div>
                    </div>

                    <div class="feat-card reveal reveal-delay-1">
                        <div class="feat-icon">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><path d="M18 8h1a4 4 0 0 1 0 8h-1"/><path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z"/><line x1="6" y1="1" x2="6" y2="4"/><line x1="10" y1="1" x2="10" y2="4"/><line x1="14" y1="1" x2="14" y2="4"/></svg>
                        </div>
                        <div class="feat-title">In-House Catering</div>
                        <div class="feat-desc">From Berlin-style street food to fine dining menus by our executive chef.</div>
                    </div>

                    <div class="feat-card reveal reveal-delay-2">
                        <div class="feat-icon">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
                        </div>
                        <div class="feat-title">State-of-the-Art AV</div>
                        <div class="feat-desc">4K projection, spatial audio, LED walls, and hybrid streaming capabilities.</div>
                    </div>

                    <div class="feat-card reveal reveal-delay-3">
                        <div class="feat-icon">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><circle cx="12" cy="12" r="10"/><path d="M2 12h20M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                        </div>
                        <div class="feat-title">Central Location</div>
                        <div class="feat-desc">Steps from U-Bahn, S-Bahn, and international hotels across Berlin.</div>
                    </div>

                    <div class="feat-card reveal">
                        <div class="feat-icon">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                        </div>
                        <div class="feat-title">Full Security</div>
                        <div class="feat-desc">Discreet professional security and access management for any event size.</div>
                    </div>

                    <div class="feat-card reveal reveal-delay-1">
                        <div class="feat-icon">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
                        </div>
                        <div class="feat-title">Event Concierge</div>
                        <div class="feat-desc">Dedicated event manager from planning through to post-event wrap-up.</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Location -->
    <section id="location" class="location-section">
        <div class="section-inner">
            <div class="location-grid">
                <div class="reveal">
                    <div class="section-tag">Find Us</div>
                    <h2 class="section-title">At the Heart<br>of <em>Berlin</em></h2>
                    <div class="divider"></div>

                    <div class="location-detail">
                        <div class="ld-icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg></div>
                        <div>
                            <span class="ld-label">Address</span>
                            <span class="ld-value">Friedrichstraße 43–45, 10117 Berlin</span>
                        </div>
                    </div>

                    <div class="location-detail">
                        <div class="ld-icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg></div>
                        <div>
                            <span class="ld-label">Opening Hours</span>
                            <span class="ld-value">Mon–Fri 9:00–20:00 · Weekends by appointment</span>
                        </div>
                    </div>

                    <div class="location-detail">
                        <div class="ld-icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.61 3.38 2 2 0 0 1 3.6 1.2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L7.91 9a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg></div>
                        <div>
                            <span class="ld-label">Phone</span>
                            <span class="ld-value">+49 (30) 000 000 00</span>
                        </div>
                    </div>

                    <div class="location-detail">
                        <div class="ld-icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg></div>
                        <div>
                            <span class="ld-label">Email</span>
                            <span class="ld-value">events@eventhub-berlin.de</span>
                        </div>
                    </div>

                    <div class="location-detail">
                        <div class="ld-icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><rect x="1" y="3" width="15" height="13" rx="1"/><path d="M16 8h4l3 3v5h-7V8z"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg></div>
                        <div>
                            <span class="ld-label">Transport</span>
                            <span class="ld-value">U6 Kochstraße · S1, S2, S25 Anhalter Bahnhof</span>
                        </div>
                    </div>
                </div>

                <div class="map-block reveal reveal-delay-2">
                    <svg viewBox="0 0 400 400" fill="none" xmlns="http://www.w3.org/2000/svg" style="width:100%;height:100%;">
                        <rect width="400" height="400" fill="#f0ede6"/>
                        <!-- Streets -->
                        <rect x="0" y="185" width="400" height="30" fill="#ddd8cf"/>
                        <rect x="185" y="0" width="30" height="400" fill="#ddd8cf"/>
                        <rect x="0" y="108" width="400" height="14" fill="#e5e1da"/>
                        <rect x="0" y="278" width="400" height="14" fill="#e5e1da"/>
                        <rect x="108" y="0" width="14" height="400" fill="#e5e1da"/>
                        <rect x="278" y="0" width="14" height="400" fill="#e5e1da"/>
                        <!-- Blocks -->
                        <rect x="122" y="122" width="55" height="55" fill="#e2ddd6" rx="2"/>
                        <rect x="223" y="122" width="55" height="55" fill="#e2ddd6" rx="2"/>
                        <rect x="122" y="223" width="55" height="55" fill="#e2ddd6" rx="2"/>
                        <rect x="223" y="223" width="55" height="55" fill="#e2ddd6" rx="2"/>
                        <rect x="18" y="122" width="80" height="55" fill="#ddd8d0" rx="2"/>
                        <rect x="302" y="122" width="80" height="55" fill="#ddd8d0" rx="2"/>
                        <rect x="18" y="223" width="80" height="55" fill="#ddd8d0" rx="2"/>
                        <rect x="302" y="223" width="80" height="55" fill="#ddd8d0" rx="2"/>
                        <!-- Street label -->
                        <text x="200" y="198" text-anchor="middle" font-size="7" fill="#c4bdb0" font-family="system-ui" letter-spacing="2" font-weight="600">FRIEDRICHSTRASSE</text>
                        <!-- Glow under pin -->
                        <circle cx="200" cy="200" r="32" fill="rgba(181,145,58,0.1)"/>
                        <circle cx="200" cy="200" r="18" fill="rgba(181,145,58,0.15)"/>
                        <!-- Pin -->
                        <path d="M200 183 C200 183 189 195 189 202 C189 208.1 194 213 200 213 C206 213 211 208.1 211 202 C211 195 200 183 200 183Z" fill="#b5913a"/>
                        <circle cx="200" cy="202" r="4.5" fill="white"/>
                        <!-- Label bubble -->
                        <rect x="158" y="218" width="84" height="24" fill="white" rx="3" stroke="rgba(181,145,58,0.4)" stroke-width="1"/>
                        <text x="200" y="232" text-anchor="middle" font-size="7.5" fill="#b5913a" font-family="Georgia, serif" letter-spacing="0.5" font-weight="bold">EVENT HUB</text>
                        <!-- Compass -->
                        <text x="372" y="28" text-anchor="middle" font-size="11" fill="#c4bdb0" font-family="Georgia, serif" font-weight="bold">N</text>
                        <line x1="372" y1="33" x2="372" y2="46" stroke="#c4bdb0" stroke-width="1"/>
                    </svg>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA -->
    <section id="contact" class="cta-section">
        <div class="cta-inner reveal">
            <div class="section-tag" style="color: rgba(181,145,58,0.9); justify-content: center; margin-bottom: 1.5rem;">Make It Happen</div>
            <h2 class="cta-title">
                Ready to Create<br>Something <em>Unforgettable?</em>
            </h2>
            <p class="cta-sub">
                From intimate boardroom dinners to 450-person galas — our team is ready
                to bring your vision to life at Berlin's most iconic address.
            </p>
            <div class="cta-actions">
                @if (Route::has('login'))
                    @auth
                        <a href="{{ url('/manager') }}" class="btn-gold">Go to Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="btn-gold">Book a Viewing</a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="btn-white-outline">Create Account</a>
                        @endif
                    @endauth
                @else
                    <a href="mailto:events@eventhub-berlin.de" class="btn-gold">Send an Enquiry</a>
                    <a href="tel:+4930000000" class="btn-white-outline">Call Us Now</a>
                @endif
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="footer-inner">
            <div class="footer-top">
                <div>
                    <div class="footer-brand">Event Hub am Checkpoint Charlie</div>
                    <p class="footer-brand-desc">
                        Berlin's most historically resonant event venue — where the weight of history
                        becomes the foundation of extraordinary moments.
                    </p>
                </div>
                <div>
                    <div class="footer-col-title">Venue</div>
                    <ul class="footer-links">
                        <li><a href="#about">About Us</a></li>
                        <li><a href="#spaces">The Grand Ballroom</a></li>
                        <li><a href="#spaces">The Charlie Suite</a></li>
                        <li><a href="#spaces">The Boardroom</a></li>
                    </ul>
                </div>
                <div>
                    <div class="footer-col-title">Company</div>
                    <ul class="footer-links">
                        <li><a href="#features">Services</a></li>
                        <li><a href="#location">Location & Transport</a></li>
                        <li><a href="#contact">Book a Venue</a></li>
                        <li><a href="#contact">Contact</a></li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <div class="footer-copy">
                    &copy; {{ date('Y') }} Event Hub am Checkpoint Charlie · Berlin, Germany
                </div>
                <div class="footer-auth">
                    @if (Route::has('login'))
                        @auth
                            <a href="{{ url('/manager') }}">Dashboard</a>
                        @else
                            <a href="{{ route('login') }}">Staff Login</a>
                            @if (Route::has('register'))
                                <a href="{{ route('register') }}">Register</a>
                            @endif
                        @endauth
                    @endif
                    <a href="#">Privacy Policy</a>
                    <a href="#">Imprint</a>
                </div>
            </div>
        </div>
    </footer>

    <script>
        // Navbar scroll effect
        const navbar = document.getElementById('navbar');
        window.addEventListener('scroll', () => {
            navbar.classList.toggle('scrolled', window.scrollY > 60);
        }, { passive: true });

        // Scroll reveal
        const reveals = document.querySelectorAll('.reveal');
        const revealObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                    revealObserver.unobserve(entry.target);
                }
            });
        }, { threshold: 0.1, rootMargin: '0px 0px -30px 0px' });
        reveals.forEach(el => revealObserver.observe(el));

        // Smooth anchor scroll
        document.querySelectorAll('a[href^="#"]').forEach(a => {
            a.addEventListener('click', e => {
                const target = document.querySelector(a.getAttribute('href'));
                if (target) {
                    e.preventDefault();
                    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            });
        });

        // Counter animation
        function animateCounter(el, target, suffix) {
            let start = null;
            const duration = 1600;
            const step = (ts) => {
                if (!start) start = ts;
                const p = Math.min((ts - start) / duration, 1);
                const eased = 1 - Math.pow(1 - p, 3);
                el.textContent = Math.floor(eased * target) + suffix;
                if (p < 1) requestAnimationFrame(step);
            };
            requestAnimationFrame(step);
        }

        const statsObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    document.querySelectorAll('.stat-num').forEach(el => {
                        const target = parseInt(el.dataset.target);
                        const suffix = el.dataset.suffix || '';
                        if (target) animateCounter(el, target, suffix);
                    });
                    statsObserver.disconnect();
                }
            });
        }, { threshold: 0.4 });

        const statsSection = document.querySelector('.stats-section');
        if (statsSection) statsObserver.observe(statsSection);
    </script>
</body>
</html>
