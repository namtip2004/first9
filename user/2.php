<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pure Serenity Spa - Luxury Wellness Sanctuary</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Thai:wght@300;400;500;600&family=Playfair+Display:wght@300;400;500;600;700&family=Crimson+Text:wght@300;400;600&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --luxury-gold: #C9A96E;
            --deep-burgundy: #8B4B5C;
            --soft-cream: #F8F5F0;
            --pearl-white: #FEFCF9;
            --charcoal: #2A2A2A;
            --soft-pink: #E8D5D3;
            --sage-green: #A8B5A0;
            --warm-beige: #E6DDD4;
            --deep-brown: #4A3F35;
            --rose-gold: #E8B4CB;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Noto Sans Thai', 'Crimson Text', serif;
            color: var(--charcoal);
            background: var(--pearl-white);
            line-height: 1.7;
            overflow-x: hidden;
        }

        .font-display {
            font-family: 'Playfair Display', serif;
        }

        /* Elegant Navigation */
        .navbar {
            background: rgba(254, 252, 249, 0.98);
            backdrop-filter: blur(20px);
            box-shadow: 0 1px 30px rgba(201, 169, 110, 0.15);
            transition: all 0.4s ease;
            padding: 20px 0;
            border-bottom: 1px solid rgba(201, 169, 110, 0.1);
        }

        .navbar-brand {
            font-size: 1.8rem;
            font-weight: 400;
            color: var(--luxury-gold) !important;
            letter-spacing: 3px;
            text-transform: uppercase;
        }

        .navbar-nav .nav-link {
            color: var(--deep-brown) !important;
            font-weight: 400;
            margin: 0 25px;
            font-size: 0.9rem;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            transition: all 0.4s ease;
            position: relative;
            padding: 10px 0 !important;
        }

        .navbar-nav .nav-link:hover,
        .navbar-nav .nav-link.active {
            color: var(--luxury-gold) !important;
        }

        .navbar-nav .nav-link::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 0;
            height: 2px;
            background: linear-gradient(90deg, transparent, var(--luxury-gold), transparent);
            transition: width 0.4s ease;
        }

        .navbar-nav .nav-link:hover::after,
        .navbar-nav .nav-link.active::after {
            width: 100%;
        }

        /* Page Management */
        .page-section {
            display: none;
            min-height: 100vh;
        }

        .page-section.active {
            display: block;
        }

        /* Spectacular Hero Section */
        .hero-section {
            height: 100vh;
            background: linear-gradient(135deg, var(--soft-cream) 0%, var(--warm-beige) 50%, var(--pearl-white) 100%);
            position: relative;
            display: flex;
            align-items: center;
            overflow: hidden;
        }

        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="damask" patternUnits="userSpaceOnUse" width="40" height="40"><path d="M20,5 C25,10 30,15 25,20 C30,25 25,30 20,35 C15,30 10,25 15,20 C10,15 15,10 20,5 Z" fill="%23C9A96E" opacity="0.08"/></pattern></defs><rect width="100" height="100" fill="url(%23damask)"/></svg>');
        }

        .hero-floating-elements {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            pointer-events: none;
        }

        .floating-ornament {
            position: absolute;
            color: var(--luxury-gold);
            opacity: 0.1;
            animation: float 6s ease-in-out infinite;
        }

        .floating-ornament:nth-child(1) {
            top: 10%;
            right: 15%;
            font-size: 8rem;
            animation-delay: 0s;
        }

        .floating-ornament:nth-child(2) {
            bottom: 20%;
            left: 10%;
            font-size: 6rem;
            animation-delay: 2s;
        }

        .floating-ornament:nth-child(3) {
            top: 50%;
            right: 5%;
            font-size: 4rem;
            animation-delay: 4s;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(5deg); }
        }

        .hero-content {
            position: relative;
            z-index: 10;
        }

        .hero-subtitle-small {
            font-size: 0.9rem;
            color: var(--luxury-gold);
            text-transform: uppercase;
            letter-spacing: 3px;
            margin-bottom: 15px;
            font-weight: 300;
        }

        .hero-title {
            font-size: 5rem;
            font-weight: 300;
            color: var(--charcoal);
            margin-bottom: 20px;
            letter-spacing: 2px;
            line-height: 1.1;
        }

        .hero-title-accent {
            color: var(--deep-burgundy);
            font-style: italic;
            font-weight: 400;
        }

        .hero-description {
            font-size: 1.3rem;
            color: var(--deep-brown);
            margin-bottom: 40px;
            font-weight: 300;
            line-height: 1.8;
            max-width: 500px;
        }

        .btn-luxury {
            background: linear-gradient(135deg, var(--deep-burgundy), var(--luxury-gold));
            color: white;
            border: none;
            padding: 18px 45px;
            font-size: 0.9rem;
            font-weight: 400;
            text-decoration: none;
            display: inline-block;
            transition: all 0.5s ease;
            letter-spacing: 2px;
            text-transform: uppercase;
            position: relative;
            overflow: hidden;
            box-shadow: 0 8px 30px rgba(139, 75, 92, 0.3);
        }

        .btn-luxury::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            transition: left 0.7s ease;
        }

        .btn-luxury:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 40px rgba(139, 75, 92, 0.4);
            color: white;
        }

        .btn-luxury:hover::before {
            left: 100%;
        }

        /* Luxury Service Cards */
        .services-section {
            padding: 120px 0;
            background: white;
            position: relative;
        }

        .services-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 1px;
            background: linear-gradient(90deg, transparent, var(--luxury-gold), transparent);
        }

        .section-header {
            text-align: center;
            margin-bottom: 80px;
        }

        .section-subtitle {
            font-size: 0.9rem;
            color: var(--luxury-gold);
            text-transform: uppercase;
            letter-spacing: 3px;
            margin-bottom: 15px;
            font-weight: 400;
        }

        .section-title {
            font-size: 3.5rem;
            color: var(--charcoal);
            font-weight: 300;
            margin-bottom: 25px;
            letter-spacing: 1px;
        }

        .section-description {
            font-size: 1.1rem;
            color: var(--deep-brown);
            max-width: 600px;
            margin: 0 auto;
            line-height: 1.8;
        }

        .service-card {
            background: var(--pearl-white);
            padding: 60px 40px;
            text-align: center;
            transition: all 0.5s ease;
            border: 1px solid rgba(201, 169, 110, 0.1);
            position: relative;
            overflow: hidden;
            height: 100%;
        }

        .service-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, var(--soft-cream), white);
            opacity: 0;
            transition: opacity 0.5s ease;
        }

        .service-card:hover::before {
            opacity: 1;
        }

        .service-card:hover {
            transform: translateY(-15px);
            box-shadow: 0 25px 60px rgba(139, 75, 92, 0.15);
            border-color: var(--luxury-gold);
        }

        .service-card > * {
            position: relative;
            z-index: 2;
        }

        .service-icon {
            width: 120px;
            height: 120px;
            background: linear-gradient(135deg, var(--luxury-gold), var(--deep-burgundy));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 35px;
            font-size: 3rem;
            color: white;
            box-shadow: 0 15px 40px rgba(201, 169, 110, 0.3);
            transition: all 0.5s ease;
        }

        .service-card:hover .service-icon {
            transform: scale(1.1);
            box-shadow: 0 20px 50px rgba(139, 75, 92, 0.4);
        }

        .service-card h3 {
            font-size: 1.5rem;
            color: var(--charcoal);
            margin-bottom: 20px;
            font-weight: 400;
        }

        .service-card p {
            color: var(--deep-brown);
            line-height: 1.8;
            font-size: 1rem;
        }

        /* About Section */
        .about-section {
            padding: 120px 0;
            background: linear-gradient(135deg, var(--soft-cream) 0%, var(--warm-beige) 100%);
            position: relative;
        }

        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 40px;
            margin-top: 60px;
        }

        .stat-item {
            text-align: center;
            padding: 40px 20px;
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(201, 169, 110, 0.2);
            transition: all 0.4s ease;
        }

        .stat-item:hover {
            transform: translateY(-10px);
            background: rgba(255, 255, 255, 0.9);
            box-shadow: 0 20px 40px rgba(139, 75, 92, 0.1);
        }

        .stat-number {
            font-size: 3.5rem;
            color: var(--luxury-gold);
            font-weight: 300;
            margin-bottom: 10px;
            display: block;
        }

        .stat-label {
            color: var(--deep-brown);
            text-transform: uppercase;
            letter-spacing: 2px;
            font-size: 0.9rem;
        }

        /* Massage Treatment Cards */
        .treatment-card {
            background: white;
            overflow: hidden;
            margin-bottom: 40px;
            transition: all 0.5s ease;
            border: 1px solid rgba(201, 169, 110, 0.1);
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.05);
        }

        .treatment-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 25px 60px rgba(139, 75, 92, 0.15);
        }

        .treatment-image {
            height: 280px;
            background: linear-gradient(135deg, var(--sage-green), var(--soft-pink));
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 4rem;
            color: white;
            position: relative;
            overflow: hidden;
        }

        .treatment-image::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(45deg, rgba(139, 75, 92, 0.3), rgba(201, 169, 110, 0.3));
        }

        .treatment-image i {
            position: relative;
            z-index: 2;
            transition: transform 0.5s ease;
        }

        .treatment-card:hover .treatment-image i {
            transform: scale(1.1);
        }

        .treatment-content {
            padding: 50px;
        }

        .treatment-title {
            font-size: 1.8rem;
            color: var(--charcoal);
            margin-bottom: 15px;
            font-weight: 400;
        }

        .treatment-description {
            color: var(--deep-brown);
            line-height: 1.8;
            margin-bottom: 25px;
            font-size: 1.05rem;
        }

        .benefits-title {
            color: var(--luxury-gold);
            font-size: 1rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 15px;
        }

        .benefits-list {
            list-style: none;
            color: var(--deep-brown);
            line-height: 1.8;
        }

        .benefits-list li {
            margin-bottom: 8px;
            position: relative;
            padding-left: 20px;
        }

        .benefits-list li::before {
            content: '✦';
            position: absolute;
            left: 0;
            color: var(--luxury-gold);
        }

        .price-info {
            margin-top: 30px;
            padding-top: 25px;
            border-top: 1px solid rgba(201, 169, 110, 0.2);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .duration-badge {
            background: var(--soft-cream);
            color: var(--deep-brown);
            padding: 8px 20px;
            border: 1px solid var(--luxury-gold);
            font-size: 0.9rem;
            font-weight: 500;
        }

        .price-badge {
            background: linear-gradient(135deg, var(--luxury-gold), var(--deep-burgundy));
            color: white;
            padding: 10px 25px;
            font-size: 1.1rem;
            font-weight: 500;
        }

        /* Elegant Forms */
        .booking-form-container {
            background: white;
            padding: 60px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(201, 169, 110, 0.1);
        }

        .form-group {
            margin-bottom: 30px;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            color: var(--deep-brown);
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-size: 0.85rem;
        }

        .form-control {
            width: 100%;
            padding: 15px 20px;
            border: 2px solid rgba(201, 169, 110, 0.2);
            background: var(--pearl-white);
            color: var(--charcoal);
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--luxury-gold);
            background: white;
            box-shadow: 0 0 0 3px rgba(201, 169, 110, 0.1);
        }

        /* Promotion Cards */
        .promotion-card {
            background: linear-gradient(135deg, var(--charcoal), var(--deep-brown));
            color: white;
            padding: 60px 40px;
            text-align: center;
            position: relative;
            overflow: hidden;
            transition: all 0.5s ease;
            border: 1px solid rgba(201, 169, 110, 0.3);
        }

        .promotion-card::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(201, 169, 110, 0.1) 0%, transparent 70%);
            transition: all 0.8s ease;
        }

        .promotion-card:hover::before {
            top: -60%;
            right: -60%;
            transform: rotate(45deg);
        }

        .promotion-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 25px 50px rgba(139, 75, 92, 0.3);
        }

        .promotion-badge {
            background: var(--luxury-gold);
            color: var(--charcoal);
            padding: 8px 25px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            display: inline-block;
            margin-bottom: 25px;
        }

        .promotion-title {
            font-size: 2rem;
            margin-bottom: 20px;
            font-weight: 300;
            color: var(--luxury-gold);
        }

        .promotion-description {
            line-height: 1.8;
            margin-bottom: 25px;
            opacity: 0.9;
        }

        .promotion-price {
            font-size: 2.5rem;
            color: var(--luxury-gold);
            font-weight: 300;
            margin: 20px 0;
        }

        /* Contact Section */
        .contact-info-card {
            background: white;
            padding: 50px;
            box-shadow: 0 15px 50px rgba(0, 0, 0, 0.08);
            border: 1px solid rgba(201, 169, 110, 0.1);
            margin-bottom: 30px;
        }

        .contact-item {
            display: flex;
            align-items: center;
            margin-bottom: 35px;
            padding: 20px 0;
            border-bottom: 1px solid rgba(201, 169, 110, 0.1);
        }

        .contact-item:last-child {
            border-bottom: none;
            margin-bottom: 0;
        }

        .contact-icon {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, var(--luxury-gold), var(--deep-burgundy));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            margin-right: 25px;
            font-size: 1.5rem;
            flex-shrink: 0;
        }

        .contact-details h6 {
            color: var(--luxury-gold);
            margin-bottom: 8px;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-size: 0.85rem;
        }

        .contact-details p {
            color: var(--deep-brown);
            margin: 0;
            font-size: 1.1rem;
            line-height: 1.6;
        }

        /* Elegant Footer */
        .footer {
            background: var(--charcoal);
            color: var(--soft-cream);
            padding: 100px 0 50px;
            margin-top: 120px;
            position: relative;
        }

        .footer::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 1px;
            background: linear-gradient(90deg, transparent, var(--luxury-gold), transparent);
        }

        .footer h5 {
            color: var(--luxury-gold);
            margin-bottom: 30px;
            font-weight: 300;
            font-size: 1.3rem;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .footer p {
            line-height: 1.8;
            color: rgba(248, 245, 240, 0.8);
        }

        .footer a {
            color: rgba(248, 245, 240, 0.8);
            text-decoration: none;
            transition: color 0.3s ease;
            display: block;
            padding: 8px 0;
            border-bottom: 1px solid transparent;
        }

        .footer a:hover {
            color: var(--luxury-gold);
            border-bottom-color: var(--luxury-gold);
        }

        .social-links a {
            display: inline-block;
            width: 50px;
            height: 50px;
            background: rgba(201, 169, 110, 0.1);
            border: 1px solid rgba(201, 169, 110, 0.3);
            border-radius: 50%;
            text-align: center;
            line-height: 48px;
            margin-right: 15px;
            transition: all 0.3s ease;
        }

        .social-links a:hover {
            background: var(--luxury-gold);
            color: var(--charcoal);
            transform: translateY(-3px);
        }

        /* Animations */
        .fade-in {
            opacity: 0;
            transform: translateY(50px);
            animation: fadeInUp 1s ease forwards;
        }

        @keyframes fadeInUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .slide-in-left {
            opacity: 0;
            transform: translateX(-80px);
            animation: slideInLeft 1s ease forwards;
        }

        @keyframes slideInLeft {
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        /* Loading States */
        .loading-spinner {
            display: none;
            text-align: center;
            padding: 60px;
        }

        .spinner {
            width: 60px;
            height: 60px;
            border: 3px solid rgba(201, 169, 110, 0.2);
            border-top: 3px solid var(--luxury-gold);
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 25px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Success Messages */
        .success-message {
            display: none;
            text-align: center;
            padding: 60px 40px;
            background: linear-gradient(135deg, var(--soft-cream), white);
            border: 2px solid var(--luxury-gold);
        }

        .success-icon {
            font-size: 4rem;
            color: var(--luxury-gold);
            margin-bottom: 25px;
        }

        .success-title {
            font-size: 1.8rem;
            color: var(--charcoal);
            margin-bottom: 15px;
            font-weight: 400;
        }

        .success-text {
            color: var(--deep-brown);
            line-height: 1.8;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .hero-title {
                font-size: 3rem;
            }
            
            .section-title {
                font-size: 2.5rem;
            }
            
            .service-card,
            .booking-form-container,
            .contact-info-card {
                padding: 40px 30px;
            }

            .treatment-content {
                padding: 30px;
            }

            .navbar-nav .nav-link {
                margin: 0 10px;
            }

            .floating-ornament {
                display: none;
            }
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: var(--soft-cream);
        }

        ::-webkit-scrollbar-thumb {
            background: linear-gradient(var(--luxury-gold), var(--deep-burgundy));
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(var(--deep-burgundy), var(--luxury-gold));
        }
    </style>
</head>
<body>
    <!-- Luxury Navigation -->
    <nav class="navbar navbar-expand-lg fixed-top">
        <div class="container">
            <a class="navbar-brand font-display" href="#">
                <i class="fas fa-gem me-2"></i>Pure Serenity
            </a>
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <i class="fas fa-bars" style="color: var(--luxury-gold);"></i>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="#" onclick="showPage('home')">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#" onclick="showPage('treatments')">Treatments</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#" onclick="showPage('booking')">Reservations</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#" onclick="showPage('offers')">Exclusive Offers</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#" onclick="showPage('contact')">Contact</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- HOME PAGE -->
    <div id="home" class="page-section active">
        <!-- Hero Section -->
        <section class="hero-section">
            <div class="hero-floating-elements">
                <div class="floating-ornament"><i class="fas fa-leaf"></i></div>
                <div class="floating-ornament"><i class="fas fa-spa"></i></div>
                <div class="floating-ornament"><i class="fas fa-lotus"></i></div>
            </div>
            
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-lg-7">
                        <div class="hero-content">
                            <div class="hero-subtitle-small fade-in">Luxury Wellness Sanctuary</div>
                            <h1 class="hero-title font-display fade-in" style="animation-delay: 0.2s;">
                                Pure <span class="hero-title-accent">Serenity</span><br>
                                Spa Experience
                            </h1>
                            <p class="hero-description fade-in" style="animation-delay: 0.4s;">
                                สัมผัสประสบการณ์สปาระดับพรีเมียม ด้วยศิลปะการนวดไทยโบราณ 
                                ในสถานที่ที่เงียบสงบและหรูหรา เพื่อการผ่อนคลายอย่างแท้จริง
                            </p>
                            <a href="#" class="btn-luxury fade-in" style="animation-delay: 0.6s;" onclick="showPage('booking')">
                                <i class="fas fa-calendar-check me-2"></i>Reserve Your Experience
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Services Section -->
        <section class="services-section">
            <div class="container">
                <div class="section-header fade-in">
                    <div class="section-subtitle">Our Signature Services</div>
                    <h2 class="section-title font-display">Luxury Wellness Treatments</h2>
                    <p class="section-description">
                        บริการสปาระดับพรีเมียม ด้วยเทคนิคการนวดโบราณผสมผสานกับนวัตกรรมสมัยใหม่ 
                        เพื่อประสบการณ์การผ่อนคลายที่เหนือระดับ
                    </p>
                </div>
                
                <div class="row g-4">
                    <div class="col-lg-4 col-md-6 fade-in" style="animation-delay: 0.1s;">
                        <div class="service-card">
                            <div class="service-icon">
                                <i class="fas fa-hands-helping"></i>
                            </div>
                            <h3 class="font-display">Royal Thai Massage</h3>
                            <p>การนวดไทยโบราณแบบราชการ ด้วยเทคนิควิชาการระดับสูง ช่วยปรับสมดุลร่างกายและจิตใจ เพื่อความผ่อนคลายอย่างลึกซึ้ง</p>
                        </div>
                    </div>
                    
                    <div class="col-lg-4 col-md-6 fade-in" style="animation-delay: 0.2s;">
                        <div class="service-card">
                            <div class="service-icon">
                                <i class="fas fa-seedling"></i>
                            </div>
                            <h3 class="font-display">Aromatherapy Bliss</h3>
                            <p>การนวดด้วยน้ำมันหอมระเหยธรรมชาติจากทั่วโลก ผสมผสานกับเทคนิคพิเศษ เพื่อการบำรุงผิวและผ่อนคลายทั้งกายและใจ</p>
                        </div>
                    </div>
                    
                    <div class="col-lg-4 col-md-6 fade-in" style="animation-delay: 0.3s;">
                        <div class="service-card">
                            <div class="service-icon">
                                <i class="fas fa-gem"></i>
                            </div>
                            <h3 class="font-display">Luxury Holistic Spa</h3>
                            <p>การดูแลแบบองค์รวมด้วยสมุนไพรธรรมชาติและเทคนิคสมัยใหม่ เพื่อความงามและสุขภาพที่ดีอย่างยั่งยืน</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- About Section -->
        <section class="about-section">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-lg-6 slide-in-left">
                        <div class="section-subtitle">About Our Sanctuary</div>
                        <h2 class="section-title font-display">A Legacy of Luxury & Wellness</h2>
                        <p class="section-description" style="text-align: left; margin: 0;">
                            Pure Serenity Spa ได้รับการยอมรับในฐานะสปาระดับพรีเมียม ที่ให้บริการการนวดและการดูแลสุขภาพแบบองค์รวม 
                            ด้วยประสบการณ์กว่า 15 ปี และทีมผู้เชี่ยวชาญระดับโลก
                        </p>
                        <p style="color: var(--deep-brown); line-height: 1.8; margin-top: 20px;">
                            เราเป็นผู้นำในการผสมผสานศิลปะการนวดไทยโบราณกับเทคนิคสปาสมัยใหม่ 
                            เพื่อมอบประสบการณ์การผ่อนคลายที่ไม่เหมือนใคร
                        </p>
                    </div>
                    
                    <div class="col-lg-6 fade-in" style="animation-delay: 0.3s;">
                        <div class="stats-container">
                            <div class="stat-item">
                                <span class="stat-number font-display">15+</span>
                                <div class="stat-label">Years Excellence</div>
                            </div>
                            <div class="stat-item">
                                <span class="stat-number font-display">5,000+</span>
                                <div class="stat-label">Happy Clients</div>
                            </div>
                            <div class="stat-item">
                                <span class="stat-number font-display">25+</span>
                                <div class="stat-label">Luxury Treatments</div>
                            </div>
                            <div class="stat-item">
                                <span class="stat-number font-display">99%</span>
                                <div class="stat-label">Satisfaction Rate</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <!-- TREATMENTS PAGE -->
    <div id="treatments" class="page-section" style="padding-top: 120px; background: white;">
        <div class="container">
            <div class="section-header fade-in">
                <div class="section-subtitle">Premium Treatments</div>
                <h2 class="section-title font-display">Signature Massage Experiences</h2>
                <p class="section-description">
                    เลือกการนวดที่เหมาะสมกับความต้องการของคุณ จากคอลเลกชั่นการรักษาระดับพรีเมียม
                </p>
            </div>
            
            <div class="row">
                <!-- Royal Thai Massage -->
                <div class="col-lg-6 mb-5 fade-in" style="animation-delay: 0.1s;">
                    <div class="treatment-card">
                        <div class="treatment-image">
                            <i class="fas fa-hands-helping"></i>
                        </div>
                        <div class="treatment-content">
                            <h3 class="treatment-title font-display">Royal Thai Massage</h3>
                            <p class="treatment-description">
                                การนวดไทยโบราณแบบราชการ ที่ผสมผสานการกดจุดสำคัญ การยืดกล้ามเนื้อ 
                                และการปรับสมดุลพลังงานในร่างกาย เพื่อความผ่อนคลายอย่างลึกซึ้ง
                            </p>
                            <div class="benefits-title">Treatment Benefits:</div>
                            <ul class="benefits-list">
                                <li>เพิ่มความยืดหยุ่นและความแข็งแรงของร่างกาย</li>
                                <li>กระตุ้นการไหลเวียนโลหิตและน้ำเหลือง</li>
                                <li>ลดความเครียดและความตึงเครียดอย่างมีประสิทธิภาพ</li>
                                <li>ปรับสมดุลพลังงานและจักระในร่างกาย</li>
                            </ul>
                            <div class="price-info">
                                <span class="duration-badge">90 Minutes</span>
                                <span class="price-badge">฿1,200</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Aromatherapy Massage -->
                <div class="col-lg-6 mb-5 fade-in" style="animation-delay: 0.2s;">
                    <div class="treatment-card">
                        <div class="treatment-image">
                            <i class="fas fa-seedling"></i>
                        </div>
                        <div class="treatment-content">
                            <h3 class="treatment-title font-display">Signature Aromatherapy</h3>
                            <p class="treatment-description">
                                การนวดด้วยน้ำมันหอมระเหยธรรมชาติจากทั่วโลก ที่คัดสรรมาเป็นพิเศษ 
                                ผสมผสานกับเทคนิคการนวดแบบสวีเดน เพื่อประสบการณ์ที่หรูหราและผ่อนคลาย
                            </p>
                            <div class="benefits-title">Treatment Benefits:</div>
                            <ul class="benefits-list">
                                <li>ผ่อนคลายกล้ามเนื้อได้อย่างลึกซึ้งและนุ่มนวล</li>
                                <li>บำรุงและฟื้นฟูผิวให้นุ่มลื่นเรียบเนียน</li>
                                <li>ลดความเครียดผ่านประสาทสัมผัสกลิ่น</li>
                                <li>ปรับปรุงคุณภาพการนอนและความรู้สึกโดยรวม</li>
                            </ul>
                            <div class="price-info">
                                <span class="duration-badge">120 Minutes</span>
                                <span class="price-badge">฿1,800</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Hot Stone Massage -->
                <div class="col-lg-6 mb-5 fade-in" style="animation-delay: 0.3s;">
                    <div class="treatment-card">
                        <div class="treatment-image">
                            <i class="fas fa-fire"></i>
                        </div>
                        <div class="treatment-content">
                            <h3 class="treatment-title font-display">Volcanic Hot Stone Therapy</h3>
                            <p class="treatment-description">
                                การนวดด้วยหินภูเขาไฟที่อุ่นจากธรรมชาติ ช่วยคลายความตึงเครียดของกล้ามเนื้อได้อย่างมีประสิทธิภาพ 
                                พร้อมกับการนวดด้วยเทคนิคพิเศษ
                            </p>
                            <div class="benefits-title">Treatment Benefits:</div>
                            <ul class="benefits-list">
                                <li>คลายความตึงเครียดของกล้ามเนื้อได้อย่างลึก</li>
                                <li>กระตุ้นการไหลเวียนโลหิตและระบบน้ำเหลือง</li>
                                <li>ลดอาการปวดเมื่อยและอักเสบ</li>
                                <li>เพิ่มความรู้สึกผ่อนคลายและสงบ</li>
                            </ul>
                            <div class="price-info">
                                <span class="duration-badge">105 Minutes</span>
                                <span class="price-badge">฿1,500</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Herbal Compress -->
                <div class="col-lg-6 mb-5 fade-in" style="animation-delay: 0.4s;">
                    <div class="treatment-card">
                        <div class="treatment-image">
                            <i class="fas fa-leaf"></i>
                        </div>
                        <div class="treatment-content">
                            <h3 class="treatment-title font-display">Royal Herbal Compress</h3>
                            <p class="treatment-description">
                                การนวดด้วยลูกประคบสมุนไพรไทยโบราณ ที่ผสมผสานสมุนไพรคุณภาพสูงกว่า 12 ชนิด 
                                เพื่อการบำบัดและฟื้นฟูตามหลักการแพทย์แผนไทย
                            </p>
                            <div class="benefits-title">Treatment Benefits:</div>
                            <ul class="benefits-list">
                                <li>ใช้สมุนไพรไทยธรรมชาติ 100% คุณภาพพรีเมียม</li>
                                <li>ลดการอักเสบและบวมของกล้ามเนื้อ</li>
                                <li>ช่วยในการฟื้นฟูและการหายของการบาดเจ็บ</li>
                                <li>เสริมสร้างภูมิคุ้มกันและสุขภาพโดยรวม</li>
                            </ul>
                            <div class="price-info">
                                <span class="duration-badge">120 Minutes</span>
                                <span class="price-badge">฿2,200</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="text-center mt-5 fade-in" style="animation-delay: 0.5s;">
                <a href="#" class="btn-luxury" onclick="showPage('booking')">
                    <i class="fas fa-calendar-check me-2"></i>Book Your Treatment Now
                </a>
            </div>
        </div>
    </div>

    <!-- BOOKING PAGE -->
    <div id="booking" class="page-section" style="padding-top: 120px; background: linear-gradient(135deg, var(--soft-cream), var(--warm-beige));">
        <div class="container">
            <div class="section-header fade-in">
                <div class="section-subtitle">Make a Reservation</div>
                <h2 class="section-title font-display">Book Your Luxury Experience</h2>
                <p class="section-description">
                    จองประสบการณ์สปาระดับพรีเมียม และเริ่มต้นการเดินทางสู่ความผ่อนคลาย
                </p>
            </div>
            
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="booking-form-container fade-in" style="animation-delay: 0.2s;">
                        <form id="bookingForm" onsubmit="submitBooking(event)">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="customerName" class="form-label">Full Name *</label>
                                        <input type="text" class="form-control" id="customerName" required placeholder="ชื่อ-นามสกุล">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="customerPhone" class="form-label">Phone Number *</label>
                                        <input type="tel" class="form-control" id="customerPhone" required placeholder="เบอร์โทรศัพท์">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="customerEmail" class="form-label">Email Address</label>
                                <input type="email" class="form-control" id="customerEmail" placeholder="อีเมล (ไม่บังคับ)">
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="bookingDate" class="form-label">Preferred Date *</label>
                                        <input type="date" class="form-control" id="bookingDate" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="bookingTime" class="form-label">Preferred Time *</label>
                                        <select class="form-control" id="bookingTime" required>
                                            <option value="">เลือกเวลา</option>
                                            <option value="09:00">09:00 AM</option>
                                            <option value="10:30">10:30 AM</option>
                                            <option value="12:00">12:00 PM</option>
                                            <option value="13:30">01:30 PM</option>
                                            <option value="15:00">03:00 PM</option>
                                            <option value="16:30">04:30 PM</option>
                                            <option value="18:00">06:00 PM</option>
                                            <option value="19:30">07:30 PM</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="treatmentType" class="form-label">Treatment Selection *</label>
                                <select class="form-control" id="treatmentType" required onchange="updateBookingPrice()">
                                    <option value="">เลือกการรักษา</option>
                                    <option value="royal-thai-1200">Royal Thai Massage (90 min) - ฿1,200</option>
                                    <option value="aromatherapy-1800">Signature Aromatherapy (120 min) - ฿1,800</option>
                                    <option value="hot-stone-1500">Volcanic Hot Stone Therapy (105 min) - ฿1,500</option>
                                    <option value="herbal-2200">Royal Herbal Compress (120 min) - ฿2,200</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="specialRequests" class="form-label">Special Requests</label>
                                <textarea class="form-control" id="specialRequests" rows="4" placeholder="ข้อมูลเพิ่มเติม ความต้องการพิเศษ หรือปัญหาสุขภาพที่ควรทราบ"></textarea>
                            </div>

                            <div id="bookingSummary" class="booking-summary" style="display: none; background: var(--pearl-white); padding: 30px; border: 2px solid var(--luxury-gold); margin: 30px 0;">
                                <h5 class="font-display" style="color: var(--charcoal); margin-bottom: 20px;">Booking Summary</h5>
                                <div id="summaryDetails"></div>
                            </div>

                            <div class="text-center" style="margin-top: 40px;">
                                <button type="submit" class="btn-luxury">
                                    <i class="fas fa-check-circle me-2"></i>Confirm Reservation
                                </button>
                            </div>
                        </form>

                        <!-- Success Message -->
                        <div id="bookingSuccess" class="success-message">
                            <div class="success-icon">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <h3 class="success-title font-display">Reservation Confirmed!</h3>
                            <p class="success-text">
                                ขอบคุณสำหรับการจอง เราได้รับข้อมูลของคุณแล้ว<br>
                                ทีมงานจะติดต่อกลับเพื่อยืนยันนัดหมายภายใน 2 ชั่วโมง
                            </p>
                        </div>

                        <!-- Loading -->
                        <div id="loadingSpinner" class="loading-spinner">
                            <div class="spinner"></div>
                            <p style="color: var(--deep-brown);">กำลังประมวลผลการจอง...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- OFFERS PAGE -->
    <div id="offers" class="page-section" style="padding-top: 120px; background: white;">
        <div class="container">
            <div class="section-header fade-in">
                <div class="section-subtitle">Exclusive Offers</div>
                <h2 class="section-title font-display">Luxury Spa Packages</h2>
                <p class="section-description">
                    แพ็คเกจสปาพิเศษและโปรโมชั่นสุดคุ้ม เพื่อประสบการณ์ความหรูหราที่ไม่ลืม
                </p>
            </div>
            
            <div class="row">
                <div class="col-lg-4 mb-4 fade-in" style="animation-delay: 0.1s;">
                    <div class="promotion-card">
                        <div class="promotion-badge">New Client Special</div>
                        <h3 class="promotion-title font-display">First Visit Luxury</h3>
                        <p class="promotion-description">
                            สำหรับลูกค้าใหม่ รับส่วนลด 25% สำหรับการรักษาครั้งแรก 
                            พร้อมเครื่องดื่มต้อนรับและของที่ระลึกพิเศษ
                        </p>
                        <div class="promotion-price font-display">25% OFF</div>
                        <a href="#" class="btn btn-outline-light" onclick="showPage('booking')">
                            Book Now
                        </a>
                    </div>
                </div>

                <div class="col-lg-4 mb-4 fade-in" style="animation-delay: 0.2s;">
                    <div class="promotion-card">
                        <div class="promotion-badge">Most Popular</div>
                        <h3 class="promotion-title font-display">Couple's Retreat</h3>
                        <p class="promotion-description">
                            แพ็คเกจคู่รัก การนวดพร้อมกันสำหรับ 2 ท่าน 
                            ในห้องส่วนตัวพร้อมบรรยากาศโรแมนติก และเครื่องดื่มแชมเปญ
                        </p>
                        <div class="promotion-price font-display">฿3,800</div>
                        <small style="text-decoration: line-through; opacity: 0.7;">ราคาปกติ ฿4,800</small>
                        <br><a href="#" class="btn btn-outline-light mt-3" onclick="showPage('booking')">
                            Reserve Now
                        </a>
                    </div>
                </div>

                <div class="col-lg-4 mb-4 fade-in" style="animation-delay: 0.3s;">
                    <div class="promotion-card">
                        <div class="promotion-badge">Premium Membership</div>
                        <h3 class="promotion-title font-display">Royal Membership</h3>
                        <p class="promotion-description">
                            สมาชิกพิเศษรายปี การนวด 12 ครั้ง พร้อมสิทธิประโยชน์มากมาย 
                            รวมถึงส่วนลดพิเศษและบริการ VIP
                        </p>
                        <div class="promotion-price font-display">฿18,000</div>
                        <small style="opacity: 0.8;">ประหยัดกว่า 40% ต่อปี</small>
                        <br><a href="#" class="btn btn-outline-light mt-3" onclick="showPage('contact')">
                            Learn More
                        </a>
                    </div>
                </div>
            </div>

            <!-- Additional Offers -->
            <div class="row mt-5">
                <div class="col-lg-6 mb-4 fade-in" style="animation-delay: 0.4s;">
                    <div style="background: var(--pearl-white); padding: 50px; border: 1px solid rgba(201, 169, 110, 0.2);">
                        <h4 class="font-display mb-3" style="color: var(--charcoal);">Birthday Celebration</h4>
                        <p style="color: var(--deep-brown); line-height: 1.8; margin-bottom: 25px;">
                            ฉลองวันเกิดของคุณกับเรา รับส่วนลด 30% ในช่วงเดือนเกิด 
                            พร้อมเค้กวันเกิดและของขวัญพิเศษ (ต้องแสดงบัตรประชาชน)
                        </p>
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <span style="color: var(--luxury-gold); font-weight: 500;">Discount:</span>
                                <span style="color: var(--deep-brown);"> 30% Off</span>
                            </div>
                            <div>
                                <span style="color: var(--luxury-gold); font-weight: 500;">Valid:</span>
                                <span style="color: var(--deep-brown);"> Birth Month</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6 mb-4 fade-in" style="animation-delay: 0.5s;">
                    <div style="background: var(--pearl-white); padding: 50px; border: 1px solid rgba(201, 169, 110, 0.2);">
                        <h4 class="font-display mb-3" style="color: var(--charcoal);">Referral Rewards</h4>
                        <p style="color: var(--deep-brown); line-height: 1.8; margin-bottom: 25px;">
                            แนะนำเพื่อนมาใช้บริการ รับส่วนลด 20% สำหรับทั้งคุณและเพื่อน 
                            เมื่อเพื่อนจองและใช้บริการครั้งแรก
                        </p>
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <span style="color: var(--luxury-gold); font-weight: 500;">Reward:</span>
                                <span style="color: var(--deep-brown);"> 20% Off Each</span>
                            </div>
                            <div>
                                <span style="color: var(--luxury-gold); font-weight: 500;">Limit:</span>
                                <span style="color: var(--deep-brown);"> Unlimited</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- CONTACT PAGE -->
    <div id="contact" class="page-section" style="padding-top: 120px; background: linear-gradient(135deg, var(--soft-cream), var(--warm-beige));">
        <div class="container">
            <div class="section-header fade-in">
                <div class="section-subtitle">Get In Touch</div>
                <h2 class="section-title font-display">Contact Our Spa</h2>
                <p class="section-description">
                    ติดต่อเราเพื่อสอบถามข้อมูลเพิ่มเติม หรือปรึกษาการรักษาที่เหมาะสมกับคุณ
                </p>
            </div>
            
            <div class="row">
                <div class="col-lg-8 mb-5 fade-in" style="animation-delay: 0.1s;">
                    <div class="contact-info-card">
                        <h4 class="font-display mb-4" style="color: var(--charcoal);">Visit Our Sanctuary</h4>
                        
                        <div class="contact-item">
                            <div class="contact-icon">
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                            <div class="contact-details">
                                <h6>Spa Location</h6>
                                <p>123 Luxury Avenue, Sukhumvit Road<br>Watthana District, Bangkok 10110<br>Thailand</p>
                            </div>
                        </div>

                        <div class="contact-item">
                            <div class="contact-icon">
                                <i class="fas fa-phone"></i>
                            </div>
                            <div class="contact-details">
                                <h6>Phone & WhatsApp</h6>
                                <p>+66 2 555 0123<br>+66 98 765 4321 (WhatsApp)</p>
                            </div>
                        </div>

                        <div class="contact-item">
                            <div class="contact-icon">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <div class="contact-details">
                                <h6>Email Address</h6>
                                <p>reservations@pureserenityspa.com<br>info@pureserenityspa.com</p>
                            </div>
                        </div>

                        <div class="contact-item">
                            <div class="contact-icon">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div class="contact-details">
                                <h6>Operating Hours</h6>
                                <p>Monday - Sunday: 9:00 AM - 10:00 PM<br>Public Holidays: 10:00 AM - 9:00 PM<br>Last appointment: 8:30 PM</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 fade-in" style="animation-delay: 0.2s;">
                    <div class="contact-info-card">
                        <h4 class="font-display mb-4" style="color: var(--charcoal);">Quick Inquiry</h4>
                        <form id="contactForm" onsubmit="submitContact(event)">
                            <div class="form-group">
                                <label for="contactName" class="form-label">Your Name *</label>
                                <input type="text" class="form-control" id="contactName" required placeholder="ชื่อของคุณ">
                            </div>
                            
                            <div class="form-group">
                                <label for="contactPhone" class="form-label">Phone Number *</label>
                                <input type="tel" class="form-control" id="contactPhone" required placeholder="เบอร์โทรศัพท์">
                            </div>
                            
                            <div class="form-group">
                                <label for="contactEmail" class="form-label">Email Address</label>
                                <input type="email" class="form-control" id="contactEmail" placeholder="อีเมล (ไม่บังคับ)">
                            </div>
                            
                            <div class="form-group">
                                <label for="inquiryType" class="form-label">Inquiry Type</label>
                                <select class="form-control" id="inquiryType">
                                    <option value="">เลือกประเภทการสอบถาม</option>
                                    <option value="booking">Booking Inquiry</option>
                                    <option value="treatments">Treatment Information</option>
                                    <option value="membership">Membership</option>
                                    <option value="gift">Gift Certificates</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="contactMessage" class="form-label">Your Message *</label>
                                <textarea class="form-control" id="contactMessage" rows="4" required placeholder="ข้อความ คำถาม หรือข้อมูลเพิ่มเติม..."></textarea>
                            </div>
                            
                            <button type="submit" class="btn-luxury w-100">
                                <i class="fas fa-paper-plane me-2"></i>Send Message
                            </button>
                        </form>

                        <!-- Contact Success Message -->
                        <div id="contactSuccess" class="success-message">
                            <div class="success-icon">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <h5 class="success-title font-display">Message Sent!</h5>
                            <p class="success-text">
                                ขอบคุณสำหรับข้อความ<br>
                                เราจะตอบกลับภายใน 4 ชั่วโมง
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Additional Information -->
            <div class="row mt-5">
                <div class="col-lg-4 mb-4 fade-in" style="animation-delay: 0.3s;">
                    <div style="background: white; padding: 40px; text-align: center; border: 1px solid rgba(201, 169, 110, 0.2);">
                        <div style="width: 80px; height: 80px; background: linear-gradient(135deg, var(--luxury-gold), var(--deep-burgundy)); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; color: white; font-size: 2rem;">
                            <i class="fas fa-car"></i>
                        </div>
                        <h5 class="font-display mb-3">Valet Parking</h5>
                        <p style="color: var(--deep-brown);">บริการรับ-ส่งรถฟรี และที่จอดรถสำหรับลูกค้า VIP</p>
                    </div>
                </div>

                <div class="col-lg-4 mb-4 fade-in" style="animation-delay: 0.4s;">
                    <div style="background: white; padding: 40px; text-align: center; border: 1px solid rgba(201, 169, 110, 0.2);">
                        <div style="width: 80px; height: 80px; background: linear-gradient(135deg, var(--luxury-gold), var(--deep-burgundy)); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; color: white; font-size: 2rem;">
                            <i class="fas fa-gift"></i>
                        </div>
                        <h5 class="font-display mb-3">Gift Certificates</h5>
                        <p style="color: var(--deep-brown);">ซื้อของขวัญสปาสำหรับคนพิเศษ พร้อมบรรจุภัณฑ์หรูหรา</p>
                    </div>
                </div>

                <div class="col-lg-4 mb-4 fade-in" style="animation-delay: 0.5s;">
                    <div style="background: white; padding: 40px; text-align: center; border: 1px solid rgba(201, 169, 110, 0.2);">
                        <div style="width: 80px; height: 80px; background: linear-gradient(135deg, var(--luxury-gold), var(--deep-burgundy)); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; color: white; font-size: 2rem;">
                            <i class="fas fa-concierge-bell"></i>
                        </div>
                        <h5 class="font-display mb-3">Concierge Service</h5>
                        <p style="color: var(--deep-brown);">บริการคอนเซียร์จ 24/7 สำหรับข้อมูลและการจองทุกความต้องการ</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Luxury Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 mb-5">
                    <h5 class="font-display">Pure Serenity Spa</h5>
                    <p style="line-height: 1.8; margin-bottom: 30px;">
                        ประสบการณ์สปาระดับพรีเมียม ที่ผสมผสานความหรูหราของตะวันออกและตะวันตก 
                        เพื่อการผ่อนคลายและความงามที่เหนือระดับ ในสถานที่ที่เงียบสงบและส่วนตัว
                    </p>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-line"></i></a>
                        <a href="#"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>

                <div class="col-lg-2 mb-5">
                    <h5>Treatments</h5>
                    <a href="#" onclick="showPage('treatments')">Royal Thai Massage</a>
                    <a href="#" onclick="showPage('treatments')">Signature Aromatherapy</a>
                    <a href="#" onclick="showPage('treatments')">Hot Stone Therapy</a>
                    <a href="#" onclick="showPage('treatments')">Herbal Compress</a>
                    <a href="#" onclick="showPage('treatments')">Face Treatments</a>
                    <a href="#" onclick="showPage('treatments')">Body Scrubs</a>
                </div>

                <div class="col-lg-2 mb-5">
                    <h5>Services</h5>
                    <a href="#" onclick="showPage('offers')">Spa Packages</a>
                    <a href="#" onclick="showPage('offers')">Couple Treatments</a>
                    <a href="#" onclick="showPage('offers')">Gift Certificates</a>
                    <a href="#" onclick="showPage('offers')">Membership</a>
                    <a href="#" onclick="showPage('contact')">Private Events</a>
                    <a href="#" onclick="showPage('contact')">Corporate Wellness</a>
                </div>

                <div class="col-lg-4 mb-5">
                    <h5>Contact Information</h5>
                    <div style="margin-bottom: 20px;">
                        <i class="fas fa-map-marker-alt" style="color: var(--luxury-gold); margin-right: 15px; width: 20px;"></i>
                        <span>123 Luxury Avenue, Sukhumvit Road<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Bangkok 10110, Thailand</span>
                    </div>
                    <div style="margin-bottom: 20px;">
                        <i class="fas fa-phone" style="color: var(--luxury-gold); margin-right: 15px; width: 20px;"></i>
                        <span>+66 2 555 0123</span>
                    </div>
                    <div style="margin-bottom: 20px;">
                        <i class="fab fa-whatsapp" style="color: var(--luxury-gold); margin-right: 15px; width: 20px;"></i>
                        <span>+66 98 765 4321</span>
                    </div>
                    <div style="margin-bottom: 20px;">
                        <i class="fas fa-envelope" style="color: var(--luxury-gold); margin-right: 15px; width: 20px;"></i>
                        <span>info@pureserenityspa.com</span>
                    </div>
                    <div>
                        <i class="fas fa-clock" style="color: var(--luxury-gold); margin-right: 15px; width: 20px;"></i>
                        <span>Daily: 9:00 AM - 10:00 PM</span>
                    </div>
                </div>
            </div>

            <div style="height: 1px; background: linear-gradient(90deg, transparent, var(--luxury-gold), transparent); margin: 60px 0 40px;"></div>
            
            <div class="text-center">
                <p style="margin: 0; color: rgba(248, 245, 240, 0.6); font-size: 0.9rem;">
                    &copy; 2024 Pure Serenity Spa. All rights reserved. | 
                    <span style="color: var(--luxury-gold);">Luxury Wellness Redefined</span>
                </p>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        // Enhanced Page Navigation
        function showPage(pageId) {
            // Hide all pages with fade effect
            const pages = document.querySelectorAll('.page-section');
            pages.forEach(page => {
                page.classList.remove('active');
                page.style.opacity = '0';
            });
            
            // Show selected page with fade in
            setTimeout(() => {
                document.getElementById(pageId).classList.add('active');
                document.getElementById(pageId).style.opacity = '1';
            }, 150);
            
            // Update navbar
            const navLinks = document.querySelectorAll('.nav-link');
            navLinks.forEach(link => link.classList.remove('active'));
            
            // Find and activate the correct nav link
            const targetLink = document.querySelector(`[onclick="showPage('${pageId}')"]`);
            if (targetLink) {
                targetLink.classList.add('active');
            }
            
            // Smooth scroll to top
            window.scrollTo({ top: 0, behavior: 'smooth' });
            
            // Close mobile menu if open
            const navbarCollapse = document.getElementById('navbarNav');
            if (navbarCollapse && navbarCollapse.classList.contains('show')) {
                const bsCollapse = new bootstrap.Collapse(navbarCollapse);
                bsCollapse.hide();
            }
        }

        // Enhanced booking price update
        function updateBookingPrice() {
            const select = document.getElementById('treatmentType');
            const summary = document.getElementById('bookingSummary');
            const details = document.getElementById('summaryDetails');
            
            if (select.value) {
                const option = select.options[select.selectedIndex];
                const text = option.text;
                const [treatment, duration, price] = text.split(' - ');
                
                summary.style.display = 'block';
                details.innerHTML = `
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; padding-bottom: 15px; border-bottom: 1px solid rgba(201, 169, 110, 0.2);">
                        <div>
                            <div style="font-weight: 500; color: var(--charcoal);">${treatment}</div>
                            <div style="color: var(--deep-brown); font-size: 0.9rem;">${duration}</div>
                        </div>
                        <div style="font-size: 1.2rem; font-weight: 500; color: var(--luxury-gold);">${price}</div>
                    </div>
                    <div style="text-align: center; color: var(--deep-brown); font-size: 0.9rem;">
                        <i class="fas fa-info-circle me-2"></i>
                        รวมเครื่องดื่มต้อนรับ และผลิตภัณฑ์บำรุงผิวระดับพรีเมียม
                    </div>
                `;
            } else {
                summary.style.display = 'none';
            }
        }

        // Enhanced booking form submission
        function submitBooking(event) {
            event.preventDefault();
            
            const form = document.getElementById('bookingForm');
            const loading = document.getElementById('loadingSpinner');
            const success = document.getElementById('bookingSuccess');
            
            // Validate required fields
            const name = document.getElementById('customerName').value;
            const phone = document.getElementById('customerPhone').value;
            const date = document.getElementById('bookingDate').value;
            const time = document.getElementById('bookingTime').value;
            const treatment = document.getElementById('treatmentType').value;
            
            if (!name || !phone || !date || !time || !treatment) {
                alert('กรุณากรอกข้อมูลที่จำเป็นให้ครบถ้วน');
                return;
            }
            
            // Show loading
            form.style.display = 'none';
            loading.style.display = 'block';
            
            // Simulate API call
            setTimeout(() => {
                loading.style.display = 'none';
                success.style.display = 'block';
                
                // Reset after 8 seconds
                setTimeout(() => {
                    success.style.display = 'none';
                    form.style.display = 'block';
                    form.reset();
                    document.getElementById('bookingSummary').style.display = 'none';
                }, 8000);
            }, 2500);
        }

        // Enhanced contact form submission
        function submitContact(event) {
            event.preventDefault();
            
            const form = document.getElementById('contactForm');
            const success = document.getElementById('contactSuccess');
            
            // Validate required fields
            const name = document.getElementById('contactName').value;
            const phone = document.getElementById('contactPhone').value;
            const message = document.getElementById('contactMessage').value;
            
            if (!name || !phone || !message) {
                alert('กรุณากรอกข้อมูลที่จำเป็นให้ครบถ้วน');
                return;
            }
            
            // Show success message
            form.style.display = 'none';
            success.style.display = 'block';
            
            // Reset after 5 seconds
            setTimeout(() => {
                success.style.display = 'none';
                form.style.display = 'block';
                form.reset();
            }, 5000);
        }

        // Enhanced initialization
        document.addEventListener('DOMContentLoaded', function() {
            // Set minimum date for booking (today)
            const dateInput = document.getElementById('bookingDate');
            if (dateInput) {
                const today = new Date().toISOString().split('T')[0];
                dateInput.setAttribute('min', today);
                
                // Set max date to 3 months from now
                const maxDate = new Date();
                maxDate.setMonth(maxDate.getMonth() + 3);
                dateInput.setAttribute('max', maxDate.toISOString().split('T')[0]);
            }

            // Enhanced scroll animations
            const observerOptions = {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            };

            const observer = new IntersectionObserver(function(entries) {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.animationPlayState = 'running';
                        entry.target.style.opacity = '1';
                    }
                });
            }, observerOptions);

            // Observe all animated elements
            document.querySelectorAll('.fade-in, .slide-in-left').forEach(el => {
                el.style.animationPlayState = 'paused';
                observer.observe(el);
            });

            // Enhanced navbar scroll effect
            window.addEventListener('scroll', function() {
                const navbar = document.querySelector('.navbar');
                if (window.scrollY > 50) {
                    navbar.style.background = 'rgba(254, 252, 249, 0.98)';
                    navbar.style.boxShadow = '0 2px 40px rgba(201, 169, 110, 0.2)';
                } else {
                    navbar.style.background = 'rgba(254, 252, 249, 0.95)';
                    navbar.style.boxShadow = '0 1px 30px rgba(201, 169, 110, 0.15)';
                }
            });

            // Initialize page transitions
            document.querySelectorAll('.page-section').forEach(page => {
                page.style.transition = 'opacity 0.3s ease-in-out';
            });

            // Add luxury cursor effects for interactive elements
            document.querySelectorAll('.btn-luxury, .service-card, .treatment-card').forEach(el => {
                el.style.cursor = 'pointer';
            });
        });

        // Enhanced mobile responsiveness
        window.addEventListener('resize', function() {
            // Adjust floating ornaments for mobile
            const ornaments = document.querySelectorAll('.floating-ornament');
            if (window.innerWidth <= 768) {
                ornaments.forEach(ornament => {
                    ornament.style.display = 'none';
                });
            } else {
                ornaments.forEach(ornament => {
                    ornament.style.display = 'block';
                });
            }
        });

        // Trigger resize event on load
        window.dispatchEvent(new Event('resize'));
    </script>
</body>
</html>