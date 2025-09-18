<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pure Serenity Spa - สัมผัสความผ่อนคลายแท้จริง</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Thai:wght@300;400;500;600;700&family=Cormorant+Garamond:wght@300;400;500;600;700&family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-gold: #D4AF37;
            --dark-gold: #B8941F;
            --rose-gold: #E8B4A4;
            --cream: #FAF7F2;
            --pearl: #F5F2ED;
            --charcoal: #2C2C2C;
            --dark-gray: #4A4A4A;
            --light-gray: #F8F8F8;
            --sage: #B5C0A7;
            --accent-pink: #E6D7D3;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Noto Sans Thai', 'Montserrat', sans-serif;
            color: var(--charcoal);
            background-color: var(--cream);
            line-height: 1.7;
            overflow-x: hidden;
        }

        .font-serif {
            font-family: 'Cormorant Garamond', serif;
        }

        /* Elegant Navbar */
        .navbar {
            background: rgba(250, 247, 242, 0.95);
            backdrop-filter: blur(20px);
            box-shadow: 0 2px 40px rgba(212, 175, 55, 0.1);
            transition: all 0.4s ease;
            padding: 15px 0;
        }

        .navbar-brand {
            font-weight: 400;
            color: var(--primary-gold) !important;
            font-size: 2rem;
            letter-spacing: 2px;
            text-transform: uppercase;
        }

        .navbar-nav .nav-link {
            color: var(--charcoal) !important;
            font-weight: 400;
            margin: 0 20px;
            font-size: 0.95rem;
            letter-spacing: 1px;
            text-transform: uppercase;
            transition: all 0.4s ease;
            position: relative;
        }

        .navbar-nav .nav-link:hover,
        .navbar-nav .nav-link.active {
            color: var(--primary-gold) !important;
        }

        .navbar-nav .nav-link::after {
            content: '';
            position: absolute;
            bottom: -8px;
            left: 50%;
            transform: translateX(-50%);
            width: 0;
            height: 1px;
            background: var(--primary-gold);
            transition: width 0.4s ease;
        }

        .navbar-nav .nav-link:hover::after,
        .navbar-nav .nav-link.active::after {
            width: 70%;
        }

        /* Page Sections */
        .page-section {
            display: none;
            min-height: calc(100vh - 100px);
        }

        .page-section.active {
            display: block;
        }

        /* Luxury Hero Section */
        .hero-section {
            height: 100vh;
            background: linear-gradient(135deg, var(--cream) 0%, var(--pearl) 100%);
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
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="luxury-pattern" patternUnits="userSpaceOnUse" width="50" height="50"><path d="M25,10 L35,25 L25,40 L15,25 Z" fill="%23D4AF37" opacity="0.03"/></pattern></defs><rect width="100" height="100" fill="url(%23luxury-pattern)"/></svg>');
        }

        .hero-ornament {
            position: absolute;
            top: 50%;
            right: 10%;
            transform: translateY(-50%);
            font-size: 25rem;
            color: var(--primary-gold);
            opacity: 0.05;
            z-index: 1;
        }

        .hero-content {
            position: relative;
            z-index: 2;
        }

        .hero-title {
            font-size: 4.5rem;
            font-weight: 300;
            color: var(--charcoal);
            margin-bottom: 1.5rem;
            letter-spacing: 3px;
            line-height: 1.2;
        }

        .hero-subtitle {
            font-size: 1.4rem;
            color: var(--dark-gray);
            margin-bottom: 3rem;
            font-weight: 300;
            line-height: 1.8;
            max-width: 600px;
        }

        .hero-accent {
            color: var(--primary-gold);
            font-style: italic;
        }

        .btn-luxury {
            background: linear-gradient(135deg, var(--primary-gold), var(--dark-gold));
            color: white;
            border: none;
            padding: 18px 50px;
            border-radius: 0;
            font-weight: 400;
            text-decoration: none;
            display: inline-block;
            transition: all 0.4s ease;
            letter-spacing: 2px;
            text-transform: uppercase;
            font-size: 0.9rem;
            position: relative;
            overflow: hidden;
        }

        .btn-luxury::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.6s ease;
        }

        .btn-luxury:hover::before {
            left: 100%;
        }

        .btn-luxury:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 40px rgba(212, 175, 55, 0.3);
            color: white;
        }

        /* Elegant Service Cards */
        .service-card {
            background: white;
            border-radius: 0;
            padding: 50px 40px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.08);
            transition: all 0.4s ease;
            border-top: 3px solid var(--primary-gold);
            height: 100%;
            position: relative;
            overflow: hidden;
        }

        .service-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, var(--cream), white);
            opacity: 0;
            transition: opacity 0.4s ease;
        }

        .service-card:hover::before {
            opacity: 1;
        }

        .service-card:hover {
            transform: translateY(-15px);
            box-shadow: 0 30px 80px rgba(0, 0, 0, 0.15);
        }

        .service-card > * {
            position: relative;
            z-index: 2;
        }

        .service-icon {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, var(--primary-gold), var(--rose-gold));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 30px;
            font-size: 2.5rem;
            color: white;
            box-shadow: 0 10px 30px rgba(212, 175, 55, 0.3);
        }

        .service-card h4 {
            font-size: 1.5rem;
            color: var(--charcoal);
            margin-bottom: 20px;
            font-weight: 400;
        }

        .service-card p {
            color: var(--dark-gray);
            line-height: 1.8;
            font-size: 0.95rem;
        }

        /* Massage Cards */
        .massage-card {
            background: white;
            border-radius: 0;
            overflow: hidden;
            box-shadow: 0 15px 50px rgba(0, 0, 0, 0.08);
            transition: all 0.4s ease;
            margin-bottom: 40px;
            border-left: 4px solid var(--primary-gold);
        }

        .massage-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 25px 70px rgba(0, 0, 0, 0.15);
        }

        .massage-image {
            height: 250px;
            background: linear-gradient(135deg, var(--sage), var(--accent-pink));
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 4rem;
            color: white;
            position: relative;
            overflow: hidden;
        }

        .massage-image::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(45deg, rgba(212, 175, 55, 0.1), rgba(230, 215, 211, 0.1));
        }

        .massage-image i {
            position: relative;
            z-index: 2;
        }

        /* Forms */
        .form-control {
            border: 1px solid #E5E5E5;
            border-radius: 0;
            padding: 15px 20px;
            background: white;
            color: var(--charcoal);
            transition: all 0.3s ease;
            font-size: 0.95rem;
        }

        .form-control:focus {
            border-color: var(--primary-gold);
            box-shadow: 0 0 0 0.2rem rgba(212, 175, 55, 0.15);
            background: var(--light-gray);
        }

        .form-label {
            font-weight: 500;
            color: var(--charcoal);
            margin-bottom: 10px;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* Promotion Cards */
        .promotion-card {
            background: linear-gradient(135deg, var(--charcoal), var(--dark-gray));
            color: white;
            border-radius: 0;
            padding: 60px 40px;
            text-align: center;
            margin-bottom: 40px;
            position: relative;
            overflow: hidden;
        }

        .promotion-card::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(45deg, transparent, rgba(212, 175, 55, 0.1), transparent);
            transform: rotate(45deg);
            transition: all 0.6s ease;
        }

        .promotion-card:hover::before {
            top: -60%;
            right: -60%;
        }

        .promotion-badge {
            background: var(--primary-gold);
            padding: 8px 25px;
            border-radius: 0;
            font-size: 0.8rem;
            font-weight: 500;
            display: inline-block;
            margin-bottom: 20px;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        .promotion-card h3 {
            color: var(--primary-gold);
            margin-bottom: 15px;
            font-weight: 300;
        }

        /* Section Titles */
        .section-title {
            font-size: 3.5rem;
            color: var(--charcoal);
            text-align: center;
            margin-bottom: 4rem;
            position: relative;
            font-weight: 300;
            letter-spacing: 2px;
        }

        .section-subtitle {
            text-align: center;
            color: var(--dark-gray);
            font-size: 1.1rem;
            margin-bottom: 4rem;
            font-weight: 300;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }

        .section-title::after {
            content: '';
            position: absolute;
            bottom: -20px;
            left: 50%;
            transform: translateX(-50%);
            width: 120px;
            height: 1px;
            background: var(--primary-gold);
        }

        .section-title::before {
            content: '❋';
            position: absolute;
            bottom: -35px;
            left: 50%;
            transform: translateX(-50%);
            color: var(--primary-gold);
            font-size: 1.5rem;
        }

        /* About Section */
        .about-section {
            background: white;
            padding: 100px 0;
        }

        .stats-item {
            text-align: center;
            padding: 30px 20px;
        }

        .stats-number {
            font-size: 3rem;
            color: var(--primary-gold);
            font-weight: 300;
            margin-bottom: 10px;
        }

        .stats-label {
            color: var(--dark-gray);
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* Contact Info */
        .contact-info {
            background: white;
            border-radius: 0;
            padding: 50px;
            box-shadow: 0 15px 50px rgba(0, 0, 0, 0.08);
            margin-bottom: 40px;
            border-left: 4px solid var(--primary-gold);
        }

        .contact-item {
            display: flex;
            align-items: center;
            margin-bottom: 30px;
            padding: 15px 0;
        }

        .contact-icon {
            width: 60px;
            height: 60px;
            background: var(--primary-gold);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            margin-right: 20px;
            font-size: 1.3rem;
        }

        .contact-details {
            flex: 1;
        }

        .contact-details h6 {
            color: var(--charcoal);
            margin-bottom: 5px;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-size: 0.8rem;
        }

        .contact-details p {
            color: var(--dark-gray);
            margin: 0;
            font-size: 1.1rem;
        }

        /* Footer */
        .footer {
            background: var(--charcoal);
            color: var(--cream);
            padding: 80px 0 40px;
            margin-top: 100px;
        }

        .footer h5 {
            color: var(--primary-gold);
            margin-bottom: 30px;
            font-weight: 300;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .footer a {
            color: var(--cream);
            text-decoration: none;
            transition: color 0.3s ease;
            display: block;
            padding: 8px 0;
        }

        .footer a:hover {
            color: var(--primary-gold);
        }

        .footer-divider {
            height: 1px;
            background: var(--dark-gray);
            margin: 40px 0;
        }

        .footer-bottom {
            text-align: center;
            color: var(--dark-gray);
            font-size: 0.9rem;
        }

        /* Animations */
        .fade-in {
            opacity: 0;
            transform: translateY(50px);
            animation: fadeInUp 0.8s ease forwards;
        }

        @keyframes fadeInUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .slide-in-left {
            opacity: 0;
            transform: translateX(-50px);
            animation: slideInLeft 0.8s ease forwards;
        }

        @keyframes slideInLeft {
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        /* Loading */
        .loading {
            display: none;
            text-align: center;
            padding: 40px;
        }

        .spinner {
            width: 50px;
            height: 50px;
            border: 3px solid var(--cream);
            border-top: 3px solid var(--primary-gold);
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 20px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Responsive */
        @media (max-width: 768px) {
            .hero-title {
                font-size: 2.8rem;
            }
            
            .hero-subtitle {
                font-size: 1.1rem;
            }
            
            .section-title {
                font-size: 2.5rem;
            }
            
            .service-card,
            .contact-info {
                padding: 30px;
                margin-bottom: 30px;
            }

            .hero-ornament {
                font-size: 15rem;
            }
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: var(--cream);
        }

        ::-webkit-scrollbar-thumb {
            background: var(--primary-gold);
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: var(--dark-gold);
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg fixed-top">
        <div class="container">
            <a class="navbar-brand font-serif" href="#">
                <i class="fas fa-leaf me-2"></i>Pure Serenity
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" style="border: none;">
                <i class="fas fa-bars" style="color: var(--primary-gold);"></i>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="#" onclick="showPage('home')">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#" onclick="showPage('massage')">Massage</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#" onclick="showPage('booking')">Booking</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#" onclick="showPage('promotion')">Promotions</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#" onclick="showPage('contact')">Contact</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Home Page -->
    <div id="home" class="page-section active">
        <!-- Hero Section -->
        <section class="hero-section">
            <div class="hero-ornament">
                <i class="fas fa-spa"></i>
            </div>
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-lg-8">
                        <div class="hero-content">
                            <h1 class="hero-title font-serif fade-in">
                                Pure <span class="hero-accent">Serenity</span><br>
                                Luxury Spa
                            </h1>
                            <p class="hero-subtitle fade-in" style="animation-delay: 0.2s;">
                                สัมผัสความผ่อนคลายแท้จริง ด้วยศิลปะการนวดไทยโบราณ 
                                ในบรรยากาศที่เงียบสงบ เพื่อความสมดุลของจิตใจและร่างกาย
                            </p>
                            <a href="#" class="btn-luxury fade-in" style="animation-delay: 0.4s;" onclick="showPage('booking')">
                                <i class="fas fa-calendar-alt me-2"></i>Book Appointment
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Services Section -->
        <section class="py-5" style="padding: 120px 0 !important; background: white;">
            <div class="container">
                <h2 class="section-title font-serif fade-in">Our Services</h2>
                <p class="section-subtitle fade-in">บริการสปาระดับพรีเมียม ด้วยมือผู้เชี่ยวชาญและผลิตภัณฑ์คุณภาพสูง</p>
                
                <div class="row">
                    <div class="col-lg-4 mb-4 fade-in" style="animation-delay: 0.1s;">
                        <div class="service-card text-center">
                            <div class="service-icon">
                                <i class="fas fa-hands"></i>
                            </div>
                            <h4 class="font-serif">นวดไทยโบราณ</h4>
                            <p>การนวดแบบไทยโบราณที่ช่วยคลายความตึงเครียดของกล้ามเนื้อ เพิ่มความยืดหยุ่นของร่างกาย และกระตุ้นการไหลเวียนโลหิต</p>
                        </div>
                    </div>
                    <div class="col-lg-4 mb-4 fade-in" style="animation-delay: 0.2s;">
                        <div class="service-card text-center">
                            <div class="service-icon">
                                <i class="fas fa-tint"></i>
                            </div>
                            <h4 class="font-serif">นวดน้ำมันอโรม่า</h4>
                            <p>การนวดด้วยน้ำมันหอมระเหยธรรมชาติ ช่วยผ่อนคลายทั้งกายและใจ พร้อมบำรุงผิวให้นุ่มลื่น เรียบเนียน</p>
                        </div>
                    </div>
                    <div class="col-lg-4 mb-4 fade-in" style="animation-delay: 0.3s;">
                        <div class="service-card text-center">
                            <div class="service-icon">
                                <i class="fas fa-leaf"></i>
                            </div>
                            <h4 class="font-serif">สปาธรรมชาติ</h4>
                            <p>ผสมผสานสมุนไพรธรรมชาติและเทคนิคการดูแลแบบองค์รวม เพื่อความผ่อนคลายและสุขภาพที่ดีอย่างยั่งยืน</p>
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
                        <h2 class="font-serif mb-4" style="color: var(--charcoal); font-size: 2.5rem; font-weight: 300;">About Our Sanctuary</h2>
                        <p class="lead mb-4" style="color: var(--dark-gray); line-height: 1.8;">Pure Serenity Spa เป็นสปาที่มุ่งมั่นในการให้บริการนวดและผ่อนคลายแบบองค์รวม ด้วยประสบการณ์กว่า 10 ปี ในการดูแลลูกค้าด้วยใจ</p>
                        <p style="color: var(--dark-gray); line-height: 1.8;">เราเชื่อว่าการผ่อนคลายที่แท้จริงเกิดจากความสมดุลระหว่างร่างกายและจิตใจ ด้วยการผสมผสานศิลปะการนวดไทยโบราณกับเทคนิคสมัยใหม่</p>
                    </div>
                    <div class="col-lg-6 fade-in" style="animation-delay: 0.3s;">
                        <div class="row">
                            <div class="col-6">
                                <div class="stats-item">
                                    <div class="stats-number font-serif">10+</div>
                                    <div class="stats-label">Years Experience</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="stats-item">
                                    <div class="stats-number font-serif">1,000+</div>
                                    <div class="stats-label">Happy Clients</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="stats-item">
                                    <div class="stats-number font-serif">15+</div>
                                    <div class="stats-label">Treatments</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="stats-item">
                                    <div class="stats-number font-serif">98%</div>
                                    <div class="stats-label">Satisfaction</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <!-- Massage Page -->
    <div id="massage" class="page-section" style="padding-top: 120px;">
        <div class="container">
            <h2 class="section-title font-serif">Massage Treatments</h2>
            <p class="section-subtitle">เลือกการนวดที่เหมาะสมกับความต้องการของคุณ</p>
            
            <div class="row">
                <div class="col-lg-6 mb-4">
                    <div class="massage-card">
                        <div class="massage-image">
                            <i class="fas fa-hands"></i>
                        </div>
                        <div class="p-5">
                            <h4 class="font-serif mb-3" style="color: var(--charcoal);">นวดไทยโบราณ</h4>
                            <p class="mb-4" style="color: var(--dark-gray);">การนวดแบบดั้งเดิมของไทยที่ใช้การกดจุดสำคัญและการยืดกล้ามเนื้อ เพื่อความผ่อนคลายอย่างลึกซึ้ง</p>
                            <h6 style="color: var(--primary-gold); text-transform: uppercase; letter-spacing: 1px;">Benefits:</h6>
                            <ul style="color: var(--dark-gray); line-height: 1.8;">
                                <li>เพิ่มความยืดหยุ่นของร่างกาย</li>
                                <li>กระตุ้นการไหลเวียนของโลหิต</li>
                                <li>ลดความเครียดและความตึงเครียด</li>
                                <li>ปรับสมดุลพลังงานในร่างกาย</li>
                            </ul>
                            <div class="mt-4">
                                <span class="badge" style="background: var(--sage); color: white; padding: 8px 15px; margin-right: 10px;">60 minutes</span>
                                <span class="badge" style="background: var(--primary-gold); color: white; padding: 8px 15px;">฿800</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6 mb-4">
                    <div class="massage-card">
                        <div class="massage-image">
                            <i class="fas fa-tint"></i>
                        </div>
                        <div class="p-5">
                            <h4 class="font-serif mb-3" style="color: var(--charcoal);">นวดน้ำมันอโรม่า</h4>
                            <p class="mb-4" style="color: var(--dark-gray);">การนวดด้วยน้ำมันหอมระเหยจากธรรมชาติ เพื่อความผ่อนคลายสูงสุด</p>
                            <h6 style="color: var(--primary-gold); text-transform: uppercase; letter-spacing: 1px;">Benefits:</h6>
                            <ul style="color: var(--dark-gray); line-height: 1.8;">
                                <li>ผ่อนคลายกล้ามเนื้อได้อย่างลึกซึ้ง</li>
                                <li>บำรุงและชุมชื้นผิวหนัง</li>
                                <li>ลดความเครียดผ่านการหอมบำบัด</li>
                                <li>ปรับปรุงคุณภาพการนอนหลับ</li>
                            </ul>
                            <div class="mt-4">
                                <span class="badge" style="background: var(--sage); color: white; padding: 8px 15px; margin-right: 10px;">90 minutes</span>
                                <span class="badge" style="background: var(--primary-gold); color: white; padding: 8px 15px;">฿1,200</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6 mb-4">
                    <div class="massage-card">
                        <div class="massage-image">
                            <i class="fas fa-fire"></i>
                        </div>
                        <div class="p-5">
                            <h4 class="font-serif mb-3" style="color: var(--charcoal);">นวดหินร้อน</h4>
                            <p class="mb-4" style="color: var(--dark-gray);">การนวดด้วยหินภูเขาไฟร้อนช่วยคลายกล้ามเนื้อได้อย่างมีประสิทธิภาพ</p>
                            <h6 style="color: var(--primary-gold); text-transform: uppercase; letter-spacing: 1px;">Benefits:</h6>
                            <ul style="color: var(--dark-gray); line-height: 1.8;">
                                <li>คลายความตึงเครียดของกล้ามเนื้อได้ลึก</li>
                                <li>กระตุ้นการไหลเวียนโลหิต</li>
                                <li>ลดอาการปวดเมื่อย</li>
                                <li>เพิ่มความรู้สึกผ่อนคลาย</li>
                            </ul>
                            <div class="mt-4">
                                <span class="badge" style="background: var(--sage); color: white; padding: 8px 15px; margin-right: 10px;">75 minutes</span>
                                <span class="badge" style="background: var(--primary-gold); color: white; padding: 8px 15px;">฿1,000</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6 mb-4">
                    <div class="massage-card">
                        <div class="massage-image">
                            <i class="fas fa-leaf"></i>
                        </div>
                        <div class="p-5">
                            <h4 class="font-serif mb-3" style="color: var(--charcoal);">นวดสมุนไพรไทย</h4>
                            <p class="mb-4" style="color: var(--dark-gray);">การนวดด้วยถุงสมุนไพรไทยร้อนช่วยผ่อนคลายและบำบัดตามธรรมชาติ</p>
                            <h6 style="color: var(--primary-gold); text-transform: uppercase; letter-spacing: 1px;">Benefits:</h6>
                            <ul style="color: var(--dark-gray); line-height: 1.8;">
                                <li>ใช้สมุนไพรไทยธรรมชาติ 100%</li>
                                <li>ลดการอักเสบของกล้ามเนื้อ</li>
                                <li>ช่วยในการหายของการบาดเจ็บ</li>
                                <li>เสริมสร้างภูมิคุ้มกันร่างกาย</li>
                            </ul>
                            <div class="mt-4">
                                <span class="badge" style="background: var(--sage); color: white; padding: 8px 15px; margin-right: 10px;">90 minutes</span>
                                <span class="badge" style="background: var(--primary-gold); color: white; padding: 8px 15px;">฿1,500</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="text-center mt-5">
                <a href="#" class="btn-luxury" onclick="showPage('booking')">
                    <i class="fas fa-calendar-alt me-2"></i>Book Your Treatment
                </a>
            </div>
        </div>
    </div>

    <!-- Booking Page -->
    <div id="booking" class="page-section" style="padding-top: 120px;">
        <div class="container">
            <h2 class="section-title font-serif">Book Appointment</h2>
            <p class="section-subtitle">จองการนวดที่เหมาะสมกับคุณ และเพลิดเพลินไปกับประสบการณ์ผ่อนคลาย</p>
            
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="service-card">
                        <form id="bookingForm" onsubmit="submitBooking(event)">
                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <label for="customerName" class="form-label">Full Name *</label>
                                    <input type="text" class="form-control" id="customerName" required placeholder="ชื่อ-นามสกุล">
                                </div>
                                <div class="col-md-6 mb-4">
                                    <label for="customerPhone" class="form-label">Phone Number *</label>
                                    <input type="tel" class="form-control" id="customerPhone" required placeholder="เบอร์โทรศัพท์">
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <label for="customerEmail" class="form-label">Email Address</label>
                                <input type="email" class="form-control" id="customerEmail" placeholder="อีเมล (ไม่บังคับ)">
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <label for="bookingDate" class="form-label">Preferred Date *</label>
                                    <input type="date" class="form-control" id="bookingDate" required>
                                </div>
                                <div class="col-md-6 mb-4">
                                    <label for="bookingTime" class="form-label">Preferred Time *</label>
                                    <select class="form-control" id="bookingTime" required>
                                        <option value="">เลือกเวลา</option>
                                        <option value="09:00">09:00 AM</option>
                                        <option value="10:00">10:00 AM</option>
                                        <option value="11:00">11:00 AM</option>
                                        <option value="13:00">01:00 PM</option>
                                        <option value="14:00">02:00 PM</option>
                                        <option value="15:00">03:00 PM</option>
                                        <option value="16:00">04:00 PM</option>
                                        <option value="17:00">05:00 PM</option>
                                        <option value="18:00">06:00 PM</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <label for="massageType" class="form-label">Treatment Type *</label>
                                <select class="form-control" id="massageType" required onchange="updatePrice()">
                                    <option value="">เลือกประเภทการนวด</option>
                                    <option value="thai-800">นวดไทยโบราณ (60 นาที) - ฿800</option>
                                    <option value="aroma-1200">นวดน้ำมันอโรม่า (90 นาที) - ฿1,200</option>
                                    <option value="hotstone-1000">นวดหินร้อน (75 นาที) - ฿1,000</option>
                                    <option value="herbal-1500">นวดสมุนไพรไทย (90 นาที) - ฿1,500</option>
                                </select>
                            </div>

                            <div class="mb-4">
                                <label for="specialRequest" class="form-label">Special Requests</label>
                                <textarea class="form-control" id="specialRequest" rows="4" placeholder="ข้อมูลเพิ่มเติม หรือความต้องการพิเศษ (ไม่บังคับ)"></textarea>
                            </div>

                            <div id="priceDisplay" class="mb-4 p-4" style="background: var(--light-gray); border-left: 4px solid var(--primary-gold); display: none;">
                                <h5 class="font-serif" style="color: var(--charcoal);">Booking Summary</h5>
                                <div id="priceDetails"></div>
                            </div>

                            <div class="text-center">
                                <button type="submit" class="btn-luxury">
                                    <i class="fas fa-check me-2"></i>Confirm Booking
                                </button>
                            </div>
                        </form>

                        <div id="bookingSuccess" class="text-center" style="display: none; padding: 40px;">
                            <i class="fas fa-check-circle" style="font-size: 4rem; color: var(--primary-gold); margin-bottom: 20px;"></i>
                            <h3 class="font-serif" style="color: var(--charcoal); margin-bottom: 15px;">Booking Confirmed!</h3>
                            <p style="color: var(--dark-gray);">เราได้รับการจองของคุณแล้ว ทางเราจะติดต่อกลับเพื่อยืนยันนัดหมายในเร็วๆ นี้</p>
                        </div>

                        <div class="loading" id="loadingSpinner">
                            <div class="spinner"></div>
                            <p style="color: var(--dark-gray);">กำลังประมวลผล...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Promotion Page -->
    <div id="promotion" class="page-section" style="padding-top: 120px;">
        <div class="container">
            <h2 class="section-title font-serif">Special Promotions</h2>
            <p class="section-subtitle">โปรโมชั่นพิเศษและแพ็คเกจสุดคุ้มสำหรับคุณ</p>
            
            <div class="row">
                <div class="col-lg-4 mb-4">
                    <div class="promotion-card">
                        <div class="promotion-badge">Limited Time</div>
                        <h3 class="font-serif">First Visit Special</h3>
                        <p class="mb-4">สำหรับลูกค้าใหม่ รับส่วนลด 20% สำหรับการนวดครั้งแรก</p>
                        <div style="font-size: 2.5rem; color: var(--primary-gold); margin: 20px 0;">20% OFF</div>
                        <a href="#" class="btn btn-outline-light" onclick="showPage('booking')">Book Now</a>
                    </div>
                </div>

                <div class="col-lg-4 mb-4">
                    <div class="promotion-card">
                        <div class="promotion-badge">Best Value</div>
                        <h3 class="font-serif">Couple Package</h3>
                        <p class="mb-4">แพ็คเกจคู่รัก นวดสำหรับ 2 ท่าน พร้อมเครื่องดื่มต้อนรับ</p>
                        <div style="font-size: 2rem; color: var(--primary-gold); margin: 20px 0;">฿2,500</div>
                        <small style="text-decoration: line-through; opacity: 0.7;">ราคาปกติ ฿3,200</small><br>
                        <a href="#" class="btn btn-outline-light mt-2" onclick="showPage('booking')">Book Now</a>
                    </div>
                </div>

                <div class="col-lg-4 mb-4">
                    <div class="promotion-card">
                        <div class="promotion-badge">Premium</div>
                        <h3 class="font-serif">Monthly Membership</h3>
                        <p class="mb-4">สมาชิกรายเดือน นวด 4 ครั้ง พร้อมสิทธิประโยชน์พิเศษ</p>
                        <div style="font-size: 2rem; color: var(--primary-gold); margin: 20px 0;">฿2,800</div>
                        <small style="opacity: 0.8;">ประหยัด 30% ต่อเดือน</small><br>
                        <a href="#" class="btn btn-outline-light mt-2" onclick="showPage('contact')">Learn More</a>
                    </div>
                </div>
            </div>

            <div class="row mt-5">
                <div class="col-lg-6 mb-4">
                    <div style="background: white; padding: 40px; border-left: 4px solid var(--primary-gold);">
                        <h4 class="font-serif mb-3" style="color: var(--charcoal);">Birthday Special</h4>
                        <p style="color: var(--dark-gray); line-height: 1.8;">ฉลองวันเกิดของคุณด้วยการนวดพิเศษ รับส่วนลด 25% ในช่วงเดือนเกิด (ต้องแสดงบัตรประชาชน)</p>
                        <div class="mt-3">
                            <span style="color: var(--primary-gold); font-weight: 500;">Valid:</span>
                            <span style="color: var(--dark-gray);">ตลอดเดือนเกิด</span>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6 mb-4">
                    <div style="background: white; padding: 40px; border-left: 4px solid var(--primary-gold);">
                        <h4 class="font-serif mb-3" style="color: var(--charcoal);">Referral Rewards</h4>
                        <p style="color: var(--dark-gray); line-height: 1.8;">แนะนำเพื่อนมาใช้บริการ รับส่วนลด 15% สำหรับทั้งคุณและเพื่อน เมื่อเพื่อนจองครั้งแรก</p>
                        <div class="mt-3">
                            <span style="color: var(--primary-gold); font-weight: 500;">Reward:</span>
                            <span style="color: var(--dark-gray);">15% Off สำหรับทั้งคู่</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Contact Page -->
    <div id="contact" class="page-section" style="padding-top: 120px;">
        <div class="container">
            <h2 class="section-title font-serif">Contact Us</h2>
            <p class="section-subtitle">ติดต่อเราเพื่อสอบถามข้อมูลเพิ่มเติม หรือจองบริการ</p>
            
            <div class="row">
                <div class="col-lg-8 mb-5">
                    <div class="contact-info">
                        <h4 class="font-serif mb-4" style="color: var(--charcoal);">Get in Touch</h4>
                        
                        <div class="contact-item">
                            <div class="contact-icon">
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                            <div class="contact-details">
                                <h6>Location</h6>
                                <p>123 Sukhumvit Road, Watthana<br>Bangkok 10110, Thailand</p>
                            </div>
                        </div>

                        <div class="contact-item">
                            <div class="contact-icon">
                                <i class="fas fa-phone"></i>
                            </div>
                            <div class="contact-details">
                                <h6>Phone</h6>
                                <p>+66 2 123 4567<br>+66 9 8765 4321</p>
                            </div>
                        </div>

                        <div class="contact-item">
                            <div class="contact-icon">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <div class="contact-details">
                                <h6>Email</h6>
                                <p>info@pureserenityspa.com<br>booking@pureserenityspa.com</p>
                            </div>
                        </div>

                        <div class="contact-item">
                            <div class="contact-icon">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div class="contact-details">
                                <h6>Opening Hours</h6>
                                <p>Monday - Sunday: 9:00 AM - 9:00 PM<br>Public Holidays: 10:00 AM - 8:00 PM</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="contact-info">
                        <h4 class="font-serif mb-4" style="color: var(--charcoal);">Quick Contact</h4>
                        <form id="contactForm" onsubmit="submitContact(event)">
                            <div class="mb-3">
                                <label for="contactName" class="form-label">Name *</label>
                                <input type="text" class="form-control" id="contactName" required>
                            </div>
                            <div class="mb-3">
                                <label for="contactPhone" class="form-label">Phone *</label>
                                <input type="tel" class="form-control" id="contactPhone" required>
                            </div>
                            <div class="mb-3">
                                <label for="contactEmail" class="form-label">Email</label>
                                <input type="email" class="form-control" id="contactEmail">
                            </div>
                            <div class="mb-4">
                                <label for="contactMessage" class="form-label">Message *</label>
                                <textarea class="form-control" id="contactMessage" rows="4" required placeholder="สอบถามข้อมูل หรือข้อความ..."></textarea>
                            </div>
                            <button type="submit" class="btn-luxury w-100">
                                <i class="fas fa-paper-plane me-2"></i>Send Message
                            </button>
                        </form>

                        <div id="contactSuccess" class="text-center" style="display: none; padding: 40px 20px;">
                            <i class="fas fa-check-circle" style="font-size: 3rem; color: var(--primary-gold); margin-bottom: 15px;"></i>
                            <h5 class="font-serif" style="color: var(--charcoal);">Message Sent!</h5>
                            <p style="color: var(--dark-gray); font-size: 0.9rem;">เราจะติดต่อกลับโดยเร็วที่สุด</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 mb-4">
                    <h5 class="font-serif">Pure Serenity Spa</h5>
                    <p style="line-height: 1.8; color: var(--dark-gray);">
                        ประสบการณ์การผ่อนคลายที่เหนือระดับ ด้วยศิลปะการนวดไทยโบราณ 
                        ในสถานที่ที่เงียบสงบและผ่อนคลาย
                    </p>
                    <div class="mt-4">
                        <a href="#" style="color: var(--primary-gold); font-size: 1.5rem; margin-right: 15px;"><i class="fab fa-facebook"></i></a>
                        <a href="#" style="color: var(--primary-gold); font-size: 1.5rem; margin-right: 15px;"><i class="fab fa-instagram"></i></a>
                        <a href="#" style="color: var(--primary-gold); font-size: 1.5rem; margin-right: 15px;"><i class="fab fa-line"></i></a>
                    </div>
                </div>

                <div class="col-lg-2 mb-4">
                    <h5>Services</h5>
                    <a href="#" onclick="showPage('massage')">นวดไทยโบราณ</a>
                    <a href="#" onclick="showPage('massage')">นวดอโรม่า</a>
                    <a href="#" onclick="showPage('massage')">นวดหินร้อน</a>
                    <a href="#" onclick="showPage('massage')">นวดสมุนไพร</a>
                </div>

                <div class="col-lg-2 mb-4">
                    <h5>Quick Links</h5>
                    <a href="#" onclick="showPage('home')">Home</a>
                    <a href="#" onclick="showPage('massage')">Massage</a>
                    <a href="#" onclick="showPage('booking')">Booking</a>
                    <a href="#" onclick="showPage('promotion')">Promotions</a>
                    <a href="#" onclick="showPage('contact')">Contact</a>
                </div>

                <div class="col-lg-4 mb-4">
                    <h5>Contact Info</h5>
                    <div style="margin-bottom: 15px;">
                        <i class="fas fa-map-marker-alt" style="color: var(--primary-gold); margin-right: 10px;"></i>
                        123 Sukhumvit Road, Bangkok 10110
                    </div>
                    <div style="margin-bottom: 15px;">
                        <i class="fas fa-phone" style="color: var(--primary-gold); margin-right: 10px;"></i>
                        +66 2 123 4567
                    </div>
                    <div style="margin-bottom: 15px;">
                        <i class="fas fa-envelope" style="color: var(--primary-gold); margin-right: 10px;"></i>
                        info@pureserenityspa.com
                    </div>
                    <div>
                        <i class="fas fa-clock" style="color: var(--primary-gold); margin-right: 10px;"></i>
                        Mon-Sun: 9:00 AM - 9:00 PM
                    </div>
                </div>
            </div>

            <div class="footer-divider"></div>
            
            <div class="footer-bottom">
                <p>&copy; 2024 Pure Serenity Spa. All rights reserved. | Designed with luxury in mind.</p>
            </div>
        </div>
    </footer>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        // Page Navigation
        function showPage(pageId) {
            // Hide all pages
            const pages = document.querySelectorAll('.page-section');
            pages.forEach(page => page.classList.remove('active'));
            
            // Show selected page
            document.getElementById(pageId).classList.add('active');
            
            // Update navbar
            const navLinks = document.querySelectorAll('.nav-link');
            navLinks.forEach(link => link.classList.remove('active'));
            event.target.classList.add('active');
            
            // Scroll to top
            window.scrollTo(0, 0);
            
            // Close mobile menu if open
            const navbarCollapse = document.getElementById('navbarNav');
            if (navbarCollapse.classList.contains('show')) {
                const bsCollapse = new bootstrap.Collapse(navbarCollapse);
                bsCollapse.hide();
            }
        }

        // Price update for booking
        function updatePrice() {
            const select = document.getElementById('massageType');
            const priceDisplay = document.getElementById('priceDisplay');
            const priceDetails = document.getElementById('priceDetails');
            
            if (select.value) {
                const option = select.options[select.selectedIndex];
                const text = option.text;
                
                priceDisplay.style.display = 'block';
                priceDetails.innerHTML = `
                    <p style="margin: 0; color: var(--dark-gray);">
                        <strong>Treatment:</strong> ${text}
                    </p>
                `;
            } else {
                priceDisplay.style.display = 'none';
            }
        }

        // Booking form submission
        function submitBooking(event) {
            event.preventDefault();
            
            const form = document.getElementById('bookingForm');
            const loading = document.getElementById('loadingSpinner');
            const success = document.getElementById('bookingSuccess');
            
            // Show loading
            form.style.display = 'none';
            loading.style.display = 'block';
            
            // Simulate API call
            setTimeout(() => {
                loading.style.display = 'none';
                success.style.display = 'block';
                
                // Reset after 5 seconds
                setTimeout(() => {
                    success.style.display = 'none';
                    form.style.display = 'block';
                    form.reset();
                    document.getElementById('priceDisplay').style.display = 'none';
                }, 5000);
            }, 2000);
        }

        // Contact form submission
        function submitContact(event) {
            event.preventDefault();
            
            const form = document.getElementById('contactForm');
            const success = document.getElementById('contactSuccess');
            
            // Show success message
            form.style.display = 'none';
            success.style.display = 'block';
            
            // Reset after 3 seconds
            setTimeout(() => {
                success.style.display = 'none';
                form.style.display = 'block';
                form.reset();
            }, 3000);
        }

        // Set minimum date for booking (today)
        document.addEventListener('DOMContentLoaded', function() {
            const dateInput = document.getElementById('bookingDate');
            if (dateInput) {
                const today = new Date().toISOString().split('T')[0];
                dateInput.setAttribute('min', today);
            }

            // Initialize fade in animations
            const observerOptions = {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            };

            const observer = new IntersectionObserver(function(entries) {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.animationPlayState = 'running';
                    }