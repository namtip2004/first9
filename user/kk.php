<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pure Serenity Spa - สัมผัสความผ่อนคลายแท้จริง</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Thai:wght@300;400;500;600;700&family=Playfair+Display:wght@400;500;600&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #8B7355;
            --secondary-color: #D4C4A8;
            --accent-color: #A8956F;
            --light-beige: #F5F1EB;
            --cream: #FEFCF7;
            --dark-brown: #6B5B47;
            --sage-green: #9CAF88;
            --soft-white: #FDFDFB;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Noto Sans Thai', sans-serif;
            color: var(--dark-brown);
            background-color: var(--cream);
            line-height: 1.6;
        }

        .font-playfair {
            font-family: 'Playfair Display', serif;
        }

        /* Navbar Styles */
        .navbar {
            background: rgba(253, 253, 251, 0.95);
            backdrop-filter: blur(10px);
            box-shadow: 0 2px 20px rgba(107, 91, 71, 0.1);
            transition: all 0.3s ease;
        }

        .navbar-brand {
            font-weight: 600;
            color: var(--primary-color) !important;
            font-size: 1.5rem;
        }

        .navbar-nav .nav-link {
            color: var(--dark-brown) !important;
            font-weight: 500;
            margin: 0 10px;
            transition: all 0.3s ease;
            position: relative;
        }

        .navbar-nav .nav-link:hover,
        .navbar-nav .nav-link.active {
            color: var(--primary-color) !important;
        }

        .navbar-nav .nav-link::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 50%;
            transform: translateX(-50%);
            width: 0;
            height: 2px;
            background: var(--primary-color);
            transition: width 0.3s ease;
        }

        .navbar-nav .nav-link:hover::after,
        .navbar-nav .nav-link.active::after {
            width: 80%;
        }

        /* Page Sections */
        .page-section {
            display: none;
            padding: 100px 0 50px;
            min-height: calc(100vh - 160px);
        }

        .page-section.active {
            display: block;
        }

        /* Hero Section */
        .hero-section {
            background: linear-gradient(135deg, var(--light-beige) 0%, var(--cream) 100%);
            padding: 120px 0;
            position: relative;
            overflow: hidden;
        }

        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="leaves" patternUnits="userSpaceOnUse" width="20" height="20"><circle cx="10" cy="10" r="1" fill="%23A8956F" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23leaves)"/></svg>');
        }

        .hero-content {
            position: relative;
            z-index: 2;
        }

        .hero-title {
            font-size: 3.5rem;
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 1rem;
        }

        .hero-subtitle {
            font-size: 1.3rem;
            color: var(--dark-brown);
            margin-bottom: 2rem;
            opacity: 0.9;
        }

        .btn-spa {
            background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
            color: white;
            border: none;
            padding: 15px 40px;
            border-radius: 50px;
            font-weight: 500;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
            box-shadow: 0 5px 20px rgba(139, 115, 85, 0.3);
        }

        .btn-spa:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 30px rgba(139, 115, 85, 0.4);
            color: white;
        }

        /* Service Cards */
        .service-card {
            background: var(--soft-white);
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 10px 40px rgba(107, 91, 71, 0.1);
            transition: all 0.3s ease;
            border: 1px solid rgba(212, 196, 168, 0.3);
            height: 100%;
        }

        .service-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 60px rgba(107, 91, 71, 0.15);
        }

        .service-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, var(--secondary-color), var(--light-beige));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 2rem;
            color: var(--primary-color);
        }

        /* Massage Info Cards */
        .massage-card {
            background: var(--soft-white);
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 8px 30px rgba(107, 91, 71, 0.1);
            transition: all 0.3s ease;
            margin-bottom: 30px;
        }

        .massage-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 45px rgba(107, 91, 71, 0.15);
        }

        .massage-image {
            height: 200px;
            background: linear-gradient(135deg, var(--secondary-color), var(--light-beige));
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            color: var(--primary-color);
        }

        /* Form Styles */
        .form-control {
            border: 2px solid var(--secondary-color);
            border-radius: 10px;
            padding: 12px 15px;
            background: var(--soft-white);
            color: var(--dark-brown);
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(139, 115, 85, 0.25);
            background: var(--cream);
        }

        .form-label {
            font-weight: 500;
            color: var(--dark-brown);
            margin-bottom: 8px;
        }

        /* Promotion Cards */
        .promotion-card {
            background: linear-gradient(135deg, var(--sage-green), var(--secondary-color));
            color: white;
            border-radius: 20px;
            padding: 30px;
            text-align: center;
            margin-bottom: 30px;
            position: relative;
            overflow: hidden;
        }

        .promotion-card::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            transition: all 0.3s ease;
        }

        .promotion-card:hover::before {
            transform: scale(1.5);
        }

        .promotion-badge {
            background: rgba(255, 255, 255, 0.2);
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            display: inline-block;
            margin-bottom: 15px;
        }

        /* Footer */
        .footer {
            background: var(--primary-color);
            color: var(--cream);
            padding: 50px 0 30px;
            margin-top: 80px;
        }

        .footer h5 {
            color: var(--secondary-color);
            margin-bottom: 20px;
        }

        .footer a {
            color: var(--cream);
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .footer a:hover {
            color: var(--secondary-color);
        }

        /* Animation Classes */
        .fade-in {
            opacity: 0;
            transform: translateY(30px);
            animation: fadeInUp 0.6s ease forwards;
        }

        @keyframes fadeInUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .section-title {
            font-size: 2.5rem;
            color: var(--primary-color);
            text-align: center;
            margin-bottom: 3rem;
            position: relative;
        }

        .section-title::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 3px;
            background: linear-gradient(to right, var(--secondary-color), var(--accent-color));
            border-radius: 3px;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .hero-title {
                font-size: 2.5rem;
            }
            
            .hero-subtitle {
                font-size: 1.1rem;
            }
            
            .section-title {
                font-size: 2rem;
            }
            
            .service-card {
                margin-bottom: 30px;
            }
        }

        /* Contact Info Styles */
        .contact-info {
            background: var(--soft-white);
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 8px 30px rgba(107, 91, 71, 0.1);
            margin-bottom: 30px;
        }

        .contact-item {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }

        .contact-icon {
            width: 50px;
            height: 50px;
            background: var(--secondary-color);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary-color);
            margin-right: 15px;
            font-size: 1.2rem;
        }

        /* Loading Animation */
        .loading {
            display: none;
            text-align: center;
            padding: 20px;
        }

        .spinner {
            width: 40px;
            height: 40px;
            border: 4px solid var(--secondary-color);
            border-top: 4px solid var(--primary-color);
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg fixed-top">
        <div class="container">
            <a class="navbar-brand font-playfair" href="#">
                <i class="fas fa-leaf me-2"></i>Pure Serenity Spa
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="#" onclick="showPage('home')">หน้าแรก</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#" onclick="showPage('massage')">การนวด</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#" onclick="showPage('booking')">จองบริการ</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#" onclick="showPage('promotion')">โปรโมชั่น</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#" onclick="showPage('contact')">ติดต่อเรา</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Home Page -->
    <div id="home" class="page-section active">
        <!-- Hero Section -->
        <section class="hero-section">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-lg-6">
                        <div class="hero-content fade-in">
                            <h1 class="hero-title font-playfair">Pure Serenity Spa</h1>
                            <p class="hero-subtitle">สัมผัสความผ่อนคลายแท้จริง ด้วยศิลปะการนวดไทยโบราณ ในบรรยากาศที่เงียบสงบ เพื่อความสมดุลของจิตใจและร่างกาย</p>
                            <a href="#" class="btn-spa" onclick="showPage('booking')">
                                <i class="fas fa-calendar-alt me-2"></i>จองบริการ
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-6 text-center">
                        <div class="hero-image fade-in" style="animation-delay: 0.3s;">
                            <div style="font-size: 15rem; color: var(--secondary-color); opacity: 0.6;">
                                <i class="fas fa-spa"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Services Section -->
        <section class="py-5">
            <div class="container">
                <h2 class="section-title font-playfair fade-in">บริการของเรา</h2>
                <div class="row">
                    <div class="col-md-4 fade-in" style="animation-delay: 0.1s;">
                        <div class="service-card text-center">
                            <div class="service-icon">
                                <i class="fas fa-hands"></i>
                            </div>
                            <h4 class="font-playfair mb-3">นวดไทยโบราณ</h4>
                            <p>การนวดแบบไทยโบราณที่ช่วยคลายความตึงเครียดของกล้ามเนื้อ เพิ่มความยืดหยุ่นของร่างกาย และกระตุ้นการไฟคลาเวียน</p>
                        </div>
                    </div>
                    <div class="col-md-4 fade-in" style="animation-delay: 0.2s;">
                        <div class="service-card text-center">
                            <div class="service-icon">
                                <i class="fas fa-tint"></i>
                            </div>
                            <h4 class="font-playfair mb-3">นวดน้ำมันอโรม่า</h4>
                            <p>การนวดด้วยน้ำมันหอมระเหยธรรมชาติ ช่วยผ่อนคลายทั้งกายและใจ พร้อมบำรุงผิวให้นุ่มลื่น เรียบเนียน</p>
                        </div>
                    </div>
                    <div class="col-md-4 fade-in" style="animation-delay: 0.3s;">
                        <div class="service-card text-center">
                            <div class="service-icon">
                                <i class="fas fa-leaf"></i>
                            </div>
                            <h4 class="font-playfair mb-3">สปาธรรมชาติ</h4>
                            <p>ผสมผสานสมุนไพรธรรมชาติและเทคนิคการดูแลแบบองค์รวม เพื่อความผ่อนคลายสุขภาพที่ดีอย่างยั่งยืน</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- About Section -->
        <section class="py-5" style="background: var(--light-beige);">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-lg-6 fade-in">
                        <h2 class="font-playfair mb-4" style="color: var(--primary-color);">เกี่ยวกับเรา</h2>
                        <p class="lead mb-4">Pure Serenity Spa เป็นสปาที่มุ่งมั่นในการให้บริการนวดและผ่อนคลายแบบองค์รวม ด้วยประสบการณ์กว่า 10 ปี</p>
                        <div class="row">
                            <div class="col-6">
                                <div class="text-center mb-3">
                                    <h3 class="font-playfair" style="color: var(--primary-color);">10+</h3>
                                    <p>ปีประสบการณ์</p>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="text-center mb-3">
                                    <h3 class="font-playfair" style="color: var(--primary-color);">1000+</h3>
                                    <p>ลูกค้าที่พึงพอใจ</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6 text-center fade-in" style="animation-delay: 0.3s;">
                        <div style="font-size: 12rem; color: var(--accent-color); opacity: 0.7;">
                            <i class="fas fa-spa"></i>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <!-- Massage Page -->
    <div id="massage" class="page-section">
        <div class="container">
            <h2 class="section-title font-playfair">บริการการนวด</h2>
            
            <div class="row">
                <div class="col-lg-6 mb-4">
                    <div class="massage-card">
                        <div class="massage-image">
                            <i class="fas fa-hands"></i>
                        </div>
                        <div class="p-4">
                            <h4 class="font-playfair mb-3">นวดไทยโบราณ</h4>
                            <p class="mb-3">การนวดแบบดั้งเดิมของไทยที่ใช้การกดจุดสำคัญและการยืดกล้ามเนื้อ</p>
                            <h5 class="text-primary">ประโยชน์:</h5>
                            <ul>
                                <li>เพิ่มความยืดหยุ่นของร่างกาย</li>
                                <li>กระตุ้นการไหลเวียนของโลหิต</li>
                                <li>ลดความเครียดและความตึงเครียด</li>
                                <li>ปรับสมดุลพลังงานในร่างกาย</li>
                            </ul>
                            <div class="mt-3">
                                <span class="badge bg-secondary me-2">60 นาที</span>
                                <span class="badge bg-primary">฿800</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6 mb-4">
                    <div class="massage-card">
                        <div class="massage-image">
                            <i class="fas fa-tint"></i>
                        </div>
                        <div class="p-4">
                            <h4 class="font-playfair mb-3">นวดน้ำมันอโรม่า</h4>
                            <p class="mb-3">การนวดด้วยน้ำมันหอมระเหยจากธรรมชาติ เพื่อความผ่อนคลายสูงสุด</p>
                            <h5 class="text-primary">ประโยชน์:</h5>
                            <ul>
                                <li>ผ่อนคลายกล้ามเนื้อได้อย่างลึกซึ้ง</li>
                                <li>บำรุงและชุมชื้นผิวหนัง</li>
                                <li>ลดความเครียดผ่านการหอมบำบัด</li>
                                <li>ปรับปรุงคุณภาพการนอนหลับ</li>
                            </ul>
                            <div class="mt-3">
                                <span class="badge bg-secondary me-2">90 นาที</span>
                                <span class="badge bg-primary">฿1,200</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6 mb-4">
                    <div class="massage-card">
                        <div class="massage-image">
                            <i class="fas fa-fire"></i>
                        </div>
                        <div class="p-4">
                            <h4 class="font-playfair mb-3">นวดหินร้อน</h4>
                            <p class="mb-3">การนวดด้วยหินภูเขาไฟร้อนช่วยคลายกล้ามเนื้อได้อย่างมีประสิทธิภาพ</p>
                            <h5 class="text-primary">ประโยชน์:</h5>
                            <ul>
                                <li>คลายความตึงเครียดของกล้ามเนื้อได้ลึก</li>
                                <li>กระตุ้นการไหลเวียนโลหิต</li>
                                <li>ลดอาการปวดเมื่อย</li>
                                <li>เพิ่มความรู้สึกผ่อนคลาย</li>
                            </ul>
                            <div class="mt-3">
                                <span class="badge bg-secondary me-2">75 นาที</span>
                                <span class="badge bg-primary">฿1,000</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6 mb-4">
                    <div class="massage-card">
                        <div class="massage-image">
                            <i class="fas fa-leaf"></i>
                        </div>
                        <div class="p-4">
                            <h4 class="font-playfair mb-3">นวดสมุนไพรไทย</h4>
                            <p class="mb-3">การนวดด้วยถุงสมุนไพรไทยร้อนช่วยผ่อนคลายและบำบัดตามธรรมชาติ</p>
                            <h5 class="text-primary">ประโยชน์:</h5>
                            <ul>
                                <li>ใช้สมุนไพรไทยธรรมชาติ 100%</li>
                                <li>ลดการอักเสบของกล้ามเนื้อ</li>
                                <li>ช่วยในการหายของการบาดเจ็บ</li>
                                <li>เสริมสร้างภูมิคุ้มกันร่างกาย</li>
                            </ul>
                            <div class="mt-3">
                                <span class="badge bg-secondary me-2">90 นาที</span>
                                <span class="badge bg-primary">฿1,500</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Booking Page -->
    <div id="booking" class="page-section">
        <div class="container">
            <h2 class="section-title font-playfair">จองบริการ</h2>
            
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="service-card">
                        <form id="bookingForm" onsubmit="submitBooking(event)">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="customerName" class="form-label">ชื่อ-นามสกุล *</label>
                                    <input type="text" class="form-control" id="customerName" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="customerPhone" class="form-label">เบอร์โทรศัพท์ *</label>
                                    <input type="tel" class="form-control" id="customerPhone" required>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="customerEmail" class="form-label">อีเมล</label>
                                <input type="email" class="form-control" id="customerEmail">
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="bookingDate" class="form-label">วันที่ต้องการ *</label>
                                    <input type="date" class="form-control" id="bookingDate" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="bookingTime" class="form-label">เวลา *</label>
                                    <select class="form-control" id="bookingTime" required>
                                        <option value="">เลือกเวลา</option>
                                        <option value="09:00">09:00</option>
                                        <option value="10:00">10:00</option>
                                        <option value="11:00">11:00</option>
                                        <option value="13:00">13:00</option>
                                        <option value="14:00">14:00</option>
                                        <option value="15:00">15:00</option>
                                        <option value="16:00">16:00</option>
                                        <option value="17:00">17:00</option>
                                        <option value="18:00">18:00</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="massageType" class="form-label">ประเภทการนวด *</label>
                                <select class="form-control" id="massageType" required onchange="updatePrice()">