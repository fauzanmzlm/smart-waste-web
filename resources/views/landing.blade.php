<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Waste - AI-Powered Waste Management</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        :root {
            --primary: #2ecc71;
            --primary-dark: #27ae60;
            --secondary: #3498db;
            --secondary-dark: #2980b9;
            --accent: #f39c12;
            --dark: #2c3e50;
            --light: #ecf0f1;
            --gray: #95a5a6;
            --success: #2ecc71;
            --warning: #f1c40f;
            --danger: #e74c3c;
            --white: #ffffff;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            color: var(--dark);
            line-height: 1.6;
            overflow-x: hidden;
        }

        h1,
        h2,
        h3,
        h4,
        h5,
        h6 {
            font-weight: 700;
            line-height: 1.2;
        }

        a {
            text-decoration: none;
            color: inherit;
        }

        img {
            max-width: 100%;
        }

        .container {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .section {
            padding: 100px 0;
        }

        .section-title {
            text-align: center;
            margin-bottom: 60px;
        }

        .section-title h2 {
            font-size: 2.5rem;
            margin-bottom: 20px;
            position: relative;
            display: inline-block;
        }

        .section-title h2:after {
            content: '';
            position: absolute;
            width: 60px;
            height: 3px;
            background-color: var(--primary);
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
        }

        .section-title p {
            font-size: 1.1rem;
            color: var(--gray);
            max-width: 700px;
            margin: 0 auto;
        }

        /* Navbar Styles */
        .navbar {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            z-index: 1000;
            background-color: transparent;
            padding: 20px 0;
            transition: all 0.3s ease;
        }

        .navbar.scrolled {
            background-color: var(--white);
            padding: 15px 0;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .navbar-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--white);
            display: flex;
            align-items: center;
        }

        .navbar.scrolled .logo {
            color: var(--dark);
        }

        .logo i {
            font-size: 1.8rem;
            margin-right: 10px;
            color: var(--primary);
        }

        .nav-links {
            display: flex;
            align-items: center;
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .nav-links li {
            margin-left: 30px;
        }

        .nav-links a {
            color: var(--white);
            font-weight: 500;
            position: relative;
            transition: all 0.3s ease;
            display: inline-block;
            padding: 6px 0;
        }

        .navbar.scrolled .nav-links a {
            color: var(--dark);
        }

        .nav-links a:after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            background-color: var(--primary);
            bottom: -5px;
            left: 0;
            transition: all 0.3s ease;
        }

        .nav-links a:hover:after {
            width: 100%;
        }

        .mobile-toggle {
            display: none;
            background: none;
            border: none;
            cursor: pointer;
            font-size: 1.5rem;
            color: var(--white);
        }

        .navbar.scrolled .mobile-toggle {
            color: var(--dark);
        }

        .btn {
            display: inline-block;
            padding: 12px 30px;
            border-radius: 50px;
            font-weight: 500;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background-color: var(--primary);
            color: var(--white);
        }

        .btn-primary:hover {
            background-color: var(--primary-dark);
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(46, 204, 113, 0.2);
        }

        .btn-secondary {
            background-color: var(--secondary);
            color: var(--white);
        }

        .btn-secondary:hover {
            background-color: var(--secondary-dark);
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(52, 152, 219, 0.2);
        }

        .btn-outline {
            background-color: transparent;
            border: 2px solid var(--white);
            color: var(--white);
        }

        .btn-outline:hover {
            background-color: var(--white);
            color: var(--primary);
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(255, 255, 255, 0.1);
        }

        /* Hero Section */
        .hero {
            height: 100vh;
            min-height: 700px;
            background: linear-gradient(135deg, rgba(46, 204, 113, 0.8), rgba(52, 152, 219, 0.8)), url('{{ asset("assets/images/tree.jpg") }}') center/cover no-repeat;
            display: flex;
            align-items: center;
            color: var(--white);
            position: relative;
        }

        .hero-content {
            max-width: 650px;
        }

        .hero-content h1 {
            font-size: 3.5rem;
            margin-bottom: 20px;
            line-height: 1.2;
        }

        .hero-content p {
            font-size: 1.1rem;
            margin-bottom: 30px;
            opacity: 0.9;
        }

        .hero-buttons {
            display: flex;
            gap: 15px;
        }

        .hero-image {
            position: absolute;
            right: 5%;
            bottom: 0;
            height: 80%;
            max-height: 600px;
            filter: drop-shadow(0 20px 30px rgba(0, 0, 0, 0.2));
        }

        /* Features Section */
        .features {
            background-color: var(--light);
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
        }

        .feature-card {
            background-color: var(--white);
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
        }

        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        }

        .feature-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
            font-size: 1.8rem;
        }

        .feature-1 .feature-icon {
            background-color: rgba(46, 204, 113, 0.1);
            color: var(--primary);
        }

        .feature-2 .feature-icon {
            background-color: rgba(52, 152, 219, 0.1);
            color: var(--secondary);
        }

        .feature-3 .feature-icon {
            background-color: rgba(243, 156, 18, 0.1);
            color: var(--accent);
        }

        .feature-4 .feature-icon {
            background-color: rgba(231, 76, 60, 0.1);
            color: var(--danger);
        }

        .feature-5 .feature-icon {
            background-color: rgba(155, 89, 182, 0.1);
            color: #9b59b6;
        }

        .feature-6 .feature-icon {
            background-color: rgba(241, 196, 15, 0.1);
            color: var(--warning);
        }

        .feature-card h3 {
            font-size: 1.3rem;
            margin-bottom: 15px;
        }

        .feature-card p {
            color: var(--gray);
            margin-bottom: 15px;
        }

        .feature-card a {
            color: var(--primary);
            font-weight: 500;
            display: inline-flex;
            align-items: center;
        }

        .feature-card a i {
            margin-left: 5px;
            transition: all 0.3s ease;
        }

        .feature-card a:hover i {
            transform: translateX(5px);
        }

        /* About App Section */
        .about-app {
            overflow: hidden;
        }

        .about-app-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 60px;
            align-items: center;
        }

        .about-app-content h2 {
            font-size: 2.2rem;
            margin-bottom: 25px;
        }

        .about-app-content p {
            margin-bottom: 25px;
            color: var(--gray);
        }

        .about-app-image {
            position: relative;
        }

        .app-screen {
            border-radius: 40px;
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.15);
        }

        .about-app-image .badge {
            position: absolute;
            background-color: var(--white);
            border-radius: 20px;
            padding: 15px 25px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center;
            font-size: 1rem;
        }

        .badge-1 {
            bottom: 60%;
            right: -40px;
        }

        .badge-2 {
            bottom: 40%;
            right: -60px;
        }

        .badge i {
            font-size: 1.5rem;
            margin-right: 10px;
        }

        .badge-1 i {
            color: var(--success);
        }

        .badge-2 i {
            color: var(--primary);
        }

        /* How it Works Section */
        .how-it-works {
            background-color: var(--light);
        }

        .steps {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
        }

        .step-card {
            background-color: var(--white);
            border-radius: 10px;
            padding: 30px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            position: relative;
        }

        .step-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        }

        .step-number {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: var(--primary);
            color: var(--white);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            margin: 0 auto 20px;
        }

        .step-card h3 {
            font-size: 1.3rem;
            margin-bottom: 15px;
        }

        .step-card p {
            color: var(--gray);
        }

        .step-card::after {
            content: '';
            position: absolute;
            width: 50px;
            height: 2px;
            background-color: var(--gray);
            top: 50%;
            right: -40px;
            display: none;
        }

        @media (min-width: 992px) {
            .step-card:not(:last-child)::after {
                display: block;
            }
        }

        /* Rewards Section */
        .rewards {
            overflow: hidden;
        }

        .rewards-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 60px;
            align-items: center;
        }

        .rewards-image {
            text-align: center;
        }

        .rewards-image img {
            max-height: 500px;
            border-radius: 20px;
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.15);
        }

        .rewards-content h2 {
            font-size: 2.2rem;
            margin-bottom: 25px;
        }

        .rewards-list {
            margin-bottom: 30px;
        }

        .reward-item {
            display: flex;
            align-items: flex-start;
            margin-bottom: 20px;
        }

        .reward-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            background-color: rgba(46, 204, 113, 0.1);
            color: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            margin-right: 15px;
            flex-shrink: 0;
        }

        .reward-info h4 {
            font-size: 1.1rem;
            margin-bottom: 5px;
        }

        .reward-info p {
            color: var(--gray);
            font-size: 0.9rem;
        }

        /* App Screens Section */
        .app-screens {
            background-color: var(--light);
            overflow: hidden;
        }

        .screens-slider {
            display: flex;
            justify-content: space-between;
            gap: 30px;
            overflow-x: auto;
            padding: 30px 0;
            scrollbar-width: none;
            -ms-overflow-style: none;
        }

        .screens-slider::-webkit-scrollbar {
            display: none;
        }

        .screen-item {
            flex: 0 0 auto;
            width: 250px;
            text-align: center;
            transition: all 0.3s ease;
        }

        .screen-item:hover {
            transform: translateY(-10px);
        }

        .screen-item img {
            border-radius: 40px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
            margin-bottom: 15px;
            height: 500px;
            object-fit: cover;
        }

        .screen-item h4 {
            font-size: 1.1rem;
            margin-bottom: 5px;
        }

        .screen-item p {
            color: var(--gray);
            font-size: 0.9rem;
        }

        /* Testimonials Section */
        .testimonials {
            overflow: hidden;
        }

        .testimonial-slider {
            display: flex;
            justify-content: space-between;
            gap: 30px;
            overflow-x: auto;
            padding: 30px 0;
            scrollbar-width: none;
            -ms-overflow-style: none;
        }

        .testimonial-slider::-webkit-scrollbar {
            display: none;
        }

        .testimonial-card {
            flex: 0 0 auto;
            width: 350px;
            background-color: var(--white);
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
        }

        .testimonial-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        }

        .testimonial-header {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }

        .testimonial-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            overflow: hidden;
            margin-right: 15px;
        }

        .testimonial-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .testimonial-author h4 {
            font-size: 1.1rem;
            margin-bottom: 5px;
        }

        .testimonial-author p {
            color: var(--gray);
            font-size: 0.9rem;
        }

        .testimonial-content {
            position: relative;
        }

        .testimonial-content::before {
            content: '\201C';
            font-size: 4rem;
            position: absolute;
            top: -20px;
            left: -10px;
            color: rgba(46, 204, 113, 0.1);
            font-family: Georgia, serif;
        }

        .testimonial-content p {
            color: var(--gray);
            line-height: 1.8;
        }

        /* FAQ Section */
        .faq {
            background-color: var(--light);
        }

        .faq-list {
            max-width: 800px;
            margin: 0 auto;
        }

        .faq-item {
            background-color: var(--white);
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            overflow: hidden;
        }

        .faq-header {
            padding: 20px 30px;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .faq-header h3 {
            font-size: 1.1rem;
            font-weight: 600;
        }

        .faq-header i {
            transition: all 0.3s ease;
        }

        .faq-item.active .faq-header i {
            transform: rotate(180deg);
        }

        .faq-content {
            padding: 0 30px;
            max-height: 0;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .faq-item.active .faq-content {
            padding: 0 30px 20px;
            max-height: 1000px;
        }

        .faq-content p {
            color: var(--gray);
        }

        /* CTA Section */
        .cta {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: var(--white);
            text-align: center;
            padding: 80px 0;
        }

        .cta h2 {
            font-size: 2.5rem;
            margin-bottom: 20px;
        }

        .cta p {
            max-width: 600px;
            margin: 0 auto 30px;
            opacity: 0.9;
        }

        .app-badges {
            display: flex;
            justify-content: center;
            gap: 20px;
        }

        .app-badge {
            background-color: var(--white);
            border-radius: 10px;
            padding: 12px 25px;
            display: flex;
            align-items: center;
            color: var(--dark);
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .app-badge:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }

        .app-badge i {
            font-size: 1.8rem;
            margin-right: 10px;
        }

        /* Footer */
        .footer {
            background-color: var(--dark);
            color: var(--white);
            padding: 80px 0 30px;
        }

        .footer-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 40px;
            margin-bottom: 40px;
        }

        .footer-logo {
            font-size: 1.5rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }

        .footer-logo i {
            font-size: 1.8rem;
            margin-right: 10px;
            color: var(--primary);
        }

        .footer-about p {
            margin-bottom: 20px;
            opacity: 0.8;
        }

        .social-links {
            display: flex;
            gap: 15px;
        }

        .social-link {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: rgba(255, 255, 255, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }

        .social-link:hover {
            background-color: var(--primary);
            transform: translateY(-3px);
        }

        .footer-links h4,
        .footer-contact h4 {
            font-size: 1.2rem;
            margin-bottom: 25px;
            position: relative;
        }

        .footer-links h4::after,
        .footer-contact h4::after {
            content: '';
            position: absolute;
            width: 30px;
            height: 2px;
            background-color: var(--primary);
            bottom: -10px;
            left: 0;
        }

        .footer-links ul {
            list-style: none;
        }

        .footer-links li {
            margin-bottom: 15px;
        }

        .footer-links a {
            opacity: 0.8;
            transition: all 0.3s ease;
        }

        .footer-links a:hover {
            opacity: 1;
            color: var(--primary);
        }

        .contact-info {
            margin-bottom: 15px;
            display: flex;
            align-items: flex-start;
        }

        .contact-info i {
            margin-right: 10px;
            color: var(--primary);
        }

        .footer-bottom {
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            padding-top: 30px;
            text-align: center;
            opacity: 0.7;
            font-size: 0.9rem;
        }

        /* Responsive */
        @media (max-width: 991px) {
            .section {
                padding: 80px 0;
            }

            .section-title h2 {
                font-size: 2rem;
            }

            .hero-content h1 {
                font-size: 2.5rem;
            }

            .hero-image {
                height: 60%;
            }

            .about-app-grid,
            .rewards-grid {
                grid-template-columns: 1fr;
                gap: 40px;
            }

            .about-app-image {
                order: -1;
            }

            .badge-1 {
                top: 10%;
                left: 0;
            }

            .badge-2 {
                bottom: 10%;
                right: 0;
            }
        }

        @media (max-width: 768px) {
            .navbar-container {
                position: relative;
            }

            .mobile-toggle {
                display: block;
            }

            .nav-links {
                position: absolute;
                top: 100%;
                left: 0;
                right: 0;
                background-color: var(--white);
                flex-direction: column;
                padding: 20px;
                box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
                border-radius: 0 0 10px 10px;
                display: none;
            }

            .nav-links.active {
                display: flex;
            }

            .nav-links li {
                margin: 0 0 15px;
            }

            .nav-links a {
                color: var(--dark);
            }

            .hero {
                text-align: center;
                padding-top: 150px;
            }

            .hero-content {
                margin: 0 auto;
            }

            .hero-buttons {
                justify-content: center;
            }

            .hero-image {
                display: none;
            }

            .app-badges {
                flex-direction: column;
                align-items: center;
            }
        }

        @media (max-width: 576px) {
            .section {
                padding: 60px 0;
            }

            .section-title h2 {
                font-size: 1.8rem;
            }

            .hero-content h1 {
                font-size: 2rem;
            }

            .feature-card,
            .step-card {
                padding: 20px;
            }

            .screen-item {
                width: 220px;
            }

            .testimonial-card {
                width: 300px;
            }
        }
    </style>
</head>

<body>
    <!-- Navbar -->
    <nav class="navbar">
        <div class="container navbar-container">
            <a href="#" class="logo">
                <i class="bi bi-recycle"></i>
                Smart Waste
            </a>

            <button class="mobile-toggle">
                <i class="bi bi-list"></i>
            </button>

            <ul class="nav-links">
                <li><a href="#features">Features</a></li>
                <li><a href="#about">About</a></li>
                <li><a href="#how-it-works">How It Works</a></li>
                <li><a href="#rewards">Rewards</a></li>
                <li><a href="#testimonials">Testimonials</a></li>
                <li><a href="#faq">FAQ</a></li>
                <li><a href="#" class="btn btn-primary" style="margin-left: 15px; padding: 8px 20px;">Download</a>
                </li>
            </ul>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero" id="home">
        <div class="container">
            <div class="hero-content">
                <h1>Revolutionizing Waste Management with AI</h1>
                <p>Smart Waste makes recycling simple with AI-powered waste classification, recycling center locator,
                    and rewards system to encourage sustainable living.</p>
                <div class="hero-buttons">
                    <a href="#cta" class="btn btn-primary">Download App</a>
                    <a href="#features" class="btn btn-outline">Learn More</a>
                </div>
            </div>

            <img src="{{ asset('assets/img/home.png') }}" alt="Smart Waste App" class="hero-image" />
        </div>
    </section>

    <!-- Features Section -->
    <section class="features section" id="features">
        <div class="container">
            <div class="section-title">
                <h2>App Features</h2>
                <p>Smart Waste offers a comprehensive solution for waste management with powerful features</p>
            </div>

            <div class="features-grid">
                <div class="feature-card feature-1">
                    <div class="feature-icon">
                        <i class="bi bi-camera"></i>
                    </div>
                    <h3>AI Waste Classification</h3>
                    <p>Take photos of waste items using your smartphone camera and our AI will instantly classify them
                        into the correct waste category.</p>
                    <a href="#">Learn more <i class="bi bi-arrow-right"></i></a>
                </div>

                <div class="feature-card feature-2">
                    <div class="feature-icon">
                        <i class="bi bi-geo-alt"></i>
                    </div>
                    <h3>Recycling Center Locator</h3>
                    <p>Find nearby recycling centers that accept your specific waste types and get directions to the
                        most convenient location.</p>
                    <a href="#">Learn more <i class="bi bi-arrow-right"></i></a>
                </div>

                <div class="feature-card feature-3">
                    <div class="feature-icon">
                        <i class="bi bi-clock-history"></i>
                    </div>
                    <h3>Recycling History</h3>
                    <p>Track your recycling activities over time and view detailed statistics about your environmental
                        impact.</p>
                    <a href="#">Learn more <i class="bi bi-arrow-right"></i></a>
                </div>

                <div class="feature-card feature-4">
                    <div class="feature-icon">
                        <i class="bi bi-trophy"></i>
                    </div>
                    <h3>Points & Rewards</h3>
                    <p>Earn points for recycling activities and redeem them for eco-friendly rewards from participating
                        centers.</p>
                    <a href="#">Learn more <i class="bi bi-arrow-right"></i></a>
                </div>

                <div class="feature-card feature-5">
                    <div class="feature-icon">
                        <i class="bi bi-book"></i>
                    </div>
                    <h3>Educational Content</h3>
                    <p>Learn about different waste types, proper disposal methods, and environmental impact through our
                        educational modules.</p>
                    <a href="#">Learn more <i class="bi bi-arrow-right"></i></a>
                </div>

                <div class="feature-card feature-6">
                    <div class="feature-icon">
                        <i class="bi bi-building-add"></i>
                    </div>
                    <h3>Center Management</h3>
                    <p>For recycling centers: manage your profile, accepted waste types, and create rewards for users.
                    </p>
                    <a href="#">Learn more <i class="bi bi-arrow-right"></i></a>
                </div>
            </div>
        </div>
    </section>

    <!-- About App Section -->
    <section class="about-app section" id="about">
        <div class="container">
            <div class="about-app-grid">
                <div class="about-app-content">
                    <h2>About Smart Waste</h2>
                    <p>Smart Waste is a comprehensive mobile application designed to revolutionize waste management and
                        promote sustainable living. By leveraging advanced AI technology and Convolutional Neural
                        Networks (CNNs), our app enables real-time classification of waste items.</p>
                    <p>Our mission is to increase recycling rates, reduce landfill reliance, and foster greater public
                        awareness about effective waste management practices. The app aligns with global Sustainable
                        Development Goals (SDGs), particularly SDG 12 (Responsible Consumption and Production) and SDG
                        13 (Climate Action).</p>
                    <a href="#cta" class="btn btn-primary">Download Now</a>
                </div>

                <div class="about-app-image">
                    <img src="{{ asset('assets/img/about-app.png') }}" alt="Smart Waste App Screen" class="app-screen"
                        style="width: 250px" />

                    <div class="badge badge-1">
                        <i class="bi bi-check-circle"></i>
                        <span>90% Classification Accuracy</span>
                    </div>

                    <div class="badge badge-2">
                        <i class="bi bi-award"></i>
                        <span>Eco-Friendly Innovation Award</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- How it Works Section -->
    <section class="how-it-works section" id="how-it-works">
        <div class="container">
            <div class="section-title">
                <h2>How It Works</h2>
                <p>Smart Waste makes recycling simple with a few easy steps</p>
            </div>

            <div class="steps">
                <div class="step-card">
                    <div class="step-number">1</div>
                    <h3>Capture Image</h3>
                    <p>Take a photo of your waste item using the app's built-in camera or upload an existing image from
                        your gallery.</p>
                </div>

                <div class="step-card">
                    <div class="step-number">2</div>
                    <h3>AI Classification</h3>
                    <p>Our advanced AI analyzes the image and classifies the waste item into the correct category with
                        detailed disposal instructions.</p>
                </div>

                <div class="step-card">
                    <div class="step-number">3</div>
                    <h3>Find Recycling Centers</h3>
                    <p>Locate nearby recycling centers that accept your specific waste type and get directions to the
                        most convenient location.</p>
                </div>

                <div class="step-card">
                    <div class="step-number">4</div>
                    <h3>Earn & Redeem Points</h3>
                    <p>Record your recycling activity to earn points, which can be redeemed for eco-friendly rewards
                        from participating centers.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Rewards Section -->
    <section class="rewards section" id="rewards">
        <div class="container">
            <div class="rewards-grid">
                <div class="rewards-image">
                    <img src="{{ asset('assets/img/reward-2.png') }}" alt="Smart Waste Rewards" />
                </div>

                <div class="rewards-content">
                    <h2>Points & Rewards System</h2>
                    <p>Our rewards system incentivizes sustainable behavior by offering points for recycling activities,
                        which can be redeemed for eco-friendly rewards.</p>

                    <div class="rewards-list" style="margin-top: 20px;">
                        <div class="reward-item">
                            <div class="reward-icon">
                                <i class="bi bi-recycle"></i>
                            </div>
                            <div class="reward-info">
                                <h4>Earn Points</h4>
                                <p>Collect points for every recycling activity. Different materials earn different point
                                    values based on environmental impact.</p>
                            </div>
                        </div>

                        <div class="reward-item">
                            <div class="reward-icon">
                                <i class="bi bi-graph-up"></i>
                            </div>
                            <div class="reward-info">
                                <h4>Track Progress</h4>
                                <p>Monitor your recycling history, set goals, and view your environmental impact
                                    statistics over time.</p>
                            </div>
                        </div>

                        <div class="reward-item">
                            <div class="reward-icon">
                                <i class="bi bi-gift"></i>
                            </div>
                            <div class="reward-info">
                                <h4>Redeem Rewards</h4>
                                <p>Exchange your points for eco-friendly products, discount coupons, services, and
                                    exclusive merchandise.</p>
                            </div>
                        </div>

                        <div class="reward-item">
                            <div class="reward-icon">
                                <i class="bi bi-stars"></i>
                            </div>
                            <div class="reward-info">
                                <h4>Bonus Opportunities</h4>
                                <p>Earn extra points for consecutive recycling days, special events, and challenges to
                                    boost your rewards.</p>
                            </div>
                        </div>
                    </div>

                    <a href="#" class="btn btn-primary">Start Earning Points</a>
                </div>
            </div>
        </div>
    </section>

    <!-- App Screens Section -->
    <section class="app-screens section" id="screens">
        <div class="container">
            <div class="section-title">
                <h2>App Screenshots</h2>
                <p>See how Smart Waste works in action</p>
            </div>

            <div class="screens-slider">
                <div class="screen-item">
                    <img src="{{ asset('assets/img/home.png') }}" alt="Home Screen" />
                    <h4>Home Dashboard</h4>
                    <p>Quick access to all features</p>
                </div>

                <div class="screen-item">
                    <img src="{{ asset('assets/img/waste-classification.png') }}" alt="Classification Screen" />
                    <h4>Waste Classification</h4>
                    <p>AI-powered identification</p>
                </div>

                <div class="screen-item">
                    <img src="{{ asset('assets/img/recycling-center.png') }}" alt="Recycling Centers Screen" />
                    <h4>Recycling Centers</h4>
                    <p>Find nearby drop-off locations</p>
                </div>

                <div class="screen-item">
                    <img src="{{ asset('assets/img/rewards.png') }}" alt="Rewards Screen" />
                    <h4>Points & Rewards</h4>
                    <p>Track and redeem rewards</p>
                </div>

                <div class="screen-item">
                    <img src="{{ asset('assets/img/learn.png') }}" alt="Educational Content Screen" />
                    <h4>Learn</h4>
                    <p>Educational content on waste types</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section class="testimonials section" id="testimonials">
        <div class="container">
            <div class="section-title">
                <h2>What Users Say</h2>
                <p>Hear from people who are already using Smart Waste</p>
            </div>

            <div class="testimonial-slider">
                <div class="testimonial-card">
                    <div class="testimonial-header">
                        <div class="testimonial-avatar">
                            <img src="https://img.freepik.com/premium-vector/man-profile_1083548-15963.jpg?semt=ais_hybrid&w=740"
                                alt="User Avatar" />
                        </div>
                        <div class="testimonial-author">
                            <h4>Zulhusni Amir</h4>
                            <p>Regular User</p>
                        </div>
                    </div>
                    <div class="testimonial-content">
                        <p>Smart Waste has completely changed how I recycle. The AI classification is amazingly
                            accurate, and I love earning points for my efforts. I've already redeemed several
                            eco-friendly rewards!</p>
                    </div>
                </div>

                <div class="testimonial-card">
                    <div class="testimonial-header">
                        <div class="testimonial-avatar">
                            <img src="https://img.freepik.com/premium-vector/man-profile_1083548-15963.jpg?semt=ais_hybrid&w=740"
                                alt="User Avatar" />
                            {{-- <img src="https://placehold.co/60x60" alt="User Avatar" /> --}}
                        </div>
                        <div class="testimonial-author">
                            <h4>Ammar Rafiq</h4>
                            <p>Recycling Enthusiast</p>
                        </div>
                    </div>
                    <div class="testimonial-content">
                        <p>As someone passionate about the environment, this app is a game-changer. It's helped me
                            discover recycling centers I didn't know existed and has educated me about proper disposal
                            of tricky items.</p>
                    </div>
                </div>

                <div class="testimonial-card">
                    <div class="testimonial-header">
                        <div class="testimonial-avatar">
                            <img src="https://img.freepik.com/premium-vector/man-profile_1083548-15963.jpg?semt=ais_hybrid&w=740"
                                alt="User Avatar" />
                        </div>
                        <div class="testimonial-author">
                            <h4>Encik Yunus</h4>
                            <p>Recycling Center Manager</p>
                        </div>
                    </div>
                    <div class="testimonial-content">
                        <p>From a center management perspective, this app has been invaluable. We've seen a 40% increase
                            in correctly sorted materials, and the rewards system has brought in many new recyclers to
                            our facility.</p>
                    </div>
                </div>

                <div class="testimonial-card">
                    <div class="testimonial-header">
                        <div class="testimonial-avatar">
                            <img src="https://img.freepik.com/premium-vector/man-profile_1083548-15963.jpg?semt=ais_hybrid&w=740"
                                alt="User Avatar" />
                        </div>
                        <div class="testimonial-author">
                            <h4>Encik Osman</h4>
                            <p>Environmental Educator</p>
                        </div>
                    </div>
                    <div class="testimonial-content">
                        <p>I recommend Smart Waste to all my students. The educational content is scientifically
                            accurate and easy to understand. The gamification aspect makes learning about waste
                            management fun and engaging.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section class="faq section" id="faq">
        <div class="container">
            <div class="section-title">
                <h2>Frequently Asked Questions</h2>
                <p>Find answers to common questions about Smart Waste</p>
            </div>

            <div class="faq-list">
                <div class="faq-item active">
                    <div class="faq-header">
                        <h3>How accurate is the waste classification?</h3>
                        <i class="bi bi-chevron-down"></i>
                    </div>
                    <div class="faq-content">
                        <p>Our AI classification system has an accuracy rate of over 90% for common waste items. The
                            system continuously improves through machine learning as more users contribute data. For
                            unusual or ambiguous items, the app may suggest multiple possible classifications or provide
                            general disposal guidelines.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-header">
                        <h3>How do I earn points in the app?</h3>
                        <i class="bi bi-chevron-down"></i>
                    </div>
                    <div class="faq-content">
                        <p>You earn points by recording your recycling activities in the app. Different materials earn
                            different point values based on their environmental impact. For example, recycling hazardous
                            materials or electronics might earn more points than standard plastics. You can also earn
                            bonus points for consecutive recycling days and special recycling events.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-header">
                        <h3>What kinds of rewards can I redeem with my points?</h3>
                        <i class="bi bi-chevron-down"></i>
                    </div>
                    <div class="faq-content">
                        <p>Rewards vary by location and participating recycling centers, but typically include
                            eco-friendly products, discount coupons for sustainable businesses, gift cards, free
                            services, donations to environmental causes, and exclusive merchandise. The app shows all
                            available rewards in your area and their point costs.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-header">
                        <h3>How does the recycling center locator work?</h3>
                        <i class="bi bi-chevron-down"></i>
                    </div>
                    <div class="faq-content">
                        <p>The recycling center locator uses your device's GPS to find centers near your current
                            location. You can filter results by waste types accepted, distance, operating hours, and
                            other criteria. For each center, you can view details such as accepted materials, contact
                            information, and get directions via your preferred navigation app.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-header">
                        <h3>I own a recycling center. How can I participate?</h3>
                        <i class="bi bi-chevron-down"></i>
                    </div>
                    <div class="faq-content">
                        <p>Recycling centers can register through the app to be listed in our directory. The Center
                            Management module allows you to create a profile, specify accepted waste types, set
                            operating hours, create and manage rewards, and process redemptions. Contact our support
                            team for partnership opportunities.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-header">
                        <h3>Is the app available for both Android and iOS?</h3>
                        <i class="bi bi-chevron-down"></i>
                    </div>
                    <div class="faq-content">
                        <p>Yes, Smart Waste is available for both Android and iOS devices. You can download it from the
                            Google Play Store or Apple App Store. The app is optimized for all screen sizes and device
                            types.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta section" id="cta">
        <div class="container">
            <h2>Download Smart Waste Today</h2>
            <p>Start your sustainable journey now. Classify waste, find recycling centers, earn rewards, and make a
                positive impact on the environment.</p>

            <div class="app-badges">
                <a href="#" class="app-badge">
                    <i class="bi bi-apple"></i>
                    <div>
                        <small>Download on the</small>
                        <strong>App Store</strong>
                    </div>
                </a>

                <a href="#" class="app-badge">
                    <i class="bi bi-google-play"></i>
                    <div>
                        <small>Get it on</small>
                        <strong>Google Play</strong>
                    </div>
                </a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-about">
                    <div class="footer-logo">
                        <i class="bi bi-recycle"></i>
                        Smart Waste
                    </div>
                    <p>Revolutionizing waste management through technology. Join us in creating a cleaner, more
                        sustainable future.</p>

                    <div class="social-links">
                        <a href="#" class="social-link"><i class="bi bi-facebook"></i></a>
                        <a href="#" class="social-link"><i class="bi bi-tiktok"></i></a>
                        <a href="#" class="social-link"><i class="bi bi-instagram"></i></a>
                        <a href="#" class="social-link"><i class="bi bi-twitter"></i></a>
                    </div>
                </div>

                <div class="footer-links">
                    <h4>Quick Links</h4>
                    <ul>
                        <li><a href="#">Home</a></li>
                        <li><a href="#features">Features</a></li>
                        <li><a href="#about">About</a></li>
                        <li><a href="#how-it-works">How It Works</a></li>
                        <li><a href="#rewards">Rewards</a></li>
                        <li><a href="#faq">FAQ</a></li>
                    </ul>
                </div>

                <div class="footer-links">
                    <h4>Resources</h4>
                    <ul>
                        <li><a href="#">Help Center</a></li>
                        <li><a href="#">Privacy Policy</a></li>
                        <li><a href="#">Terms of Service</a></li>
                        <li><a href="#">Recycling Centers</a></li>
                        <li><a href="#">Blog</a></li>
                    </ul>
                </div>

                <div class="footer-contact">
                    <h4>Contact Us</h4>
                    <div class="contact-info">
                        <i class="bi bi-geo-alt"></i>
                        <div>
                            <p>Jalan Tembila, 22200 Besut, Terengganu</p>
                        </div>
                    </div>

                    <div class="contact-info">
                        <i class="bi bi-envelope"></i>
                        <div>
                            <p>contact@smartwaste.app</p>
                        </div>
                    </div>

                    <div class="contact-info">
                        <i class="bi bi-telephone"></i>
                        <div>
                            <p>+60 179170000</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="footer-bottom">
                <p>&copy; 2025 Smart Waste. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- JavaScript -->
    <script>
        // Navbar scroll effect
        window.addEventListener('scroll', function() {
            const navbar = document.querySelector('.navbar');
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });

        // Mobile menu toggle
        const mobileToggle = document.querySelector('.mobile-toggle');
        const navLinks = document.querySelector('.nav-links');

        mobileToggle.addEventListener('click', function() {
            navLinks.classList.toggle('active');
        });

        // FAQ accordion
        const faqItems = document.querySelectorAll('.faq-item');

        faqItems.forEach(item => {
            const header = item.querySelector('.faq-header');

            header.addEventListener('click', () => {
                // Close all other items
                faqItems.forEach(otherItem => {
                    if (otherItem !== item && otherItem.classList.contains('active')) {
                        otherItem.classList.remove('active');
                    }
                });

                // Toggle current item
                item.classList.toggle('active');
            });
        });

        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                if (this.getAttribute('href') !== '#') {
                    e.preventDefault();

                    const targetId = this.getAttribute('href');
                    const targetElement = document.querySelector(targetId);

                    if (targetElement) {
                        window.scrollTo({
                            top: targetElement.offsetTop - 70,
                            behavior: 'smooth'
                        });

                        // Close mobile menu if open
                        navLinks.classList.remove('active');
                    }
                }
            });
        });
    </script>
</body>

</html>
