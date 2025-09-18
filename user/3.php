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

        /* Navbar */
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
            margin: 0 15px;
            font-size: 0.9rem;
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

        .auth-buttons {
            margin-left: 20px;
        }

        .btn-auth {
            background: transparent;
            color: var(--charcoal);
            border: 1px solid var(--primary-gold);
            padding: 8px 20px;
            border-radius: 25px;
            font-size: 0.85rem;
            margin: 0 5px;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }

        .btn-auth:hover {
            background: var(--primary-gold);
            color: white;
        }

        .btn-auth.signup {
            background: var(--primary-gold);
            color: white;
        }

        .user-menu {
            position: relative;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--primary-gold);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            cursor: pointer;
            margin-left: 20px;
        }

        .dropdown-menu-custom {
            position: absolute;
            top: 100%;
            right: 0;
            background: white;
            border: none;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            border-radius: 8px;
            padding: 10px 0;
            min-width: 200px;
            z-index: 1000;
            display: none;
        }

        .dropdown-menu-custom.show {
            display: block;
        }

        .dropdown-item-custom {
            padding: 12px 20px;
            color: var(--charcoal);
            text-decoration: none;
            display: block;
            transition: all 0.3s ease;
        }

        .dropdown-item-custom:hover {
            background: var(--light-gray);
            color: var(--primary-gold);
        }

        /* Page Sections */
        .page-section {
            display: none;
            min-height: calc(100vh - 100px);
        }

        .page-section.active {
            display: block;
        }

        /* Hero Section */
        .hero-section {
            height: 100vh;
            background: linear-gradient(135deg, var(--cream) 0%, var(--pearl) 100%);
            position: relative;
            display: flex;
            align-items: center;
            overflow: hidden;
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

        .btn-luxury:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 40px rgba(212, 175, 55, 0.3);
            color: white;
        }

        /* Service Cards */
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

        .service-card:hover {
            transform: translateY(-15px);
            box-shadow: 0 30px 80px rgba(0, 0, 0, 0.15);
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

        /* Owner & Team Section */
        .team-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            text-align: center;
            box-shadow: 0 15px 50px rgba(0,0,0,0.08);
            transition: all 0.4s ease;
            margin-bottom: 30px;
        }

        .team-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 25px 70px rgba(0,0,0,0.15);
        }

        .team-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-gold), var(--rose-gold));
            margin: 0 auto 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            color: white;
        }

        /* Hot Services */
        .hot-service {
            background: linear-gradient(135deg, var(--primary-gold), var(--dark-gold));
            color: white;
            border-radius: 15px;
            padding: 40px 30px;
            text-align: center;
            position: relative;
            overflow: hidden;
            margin-bottom: 30px;
        }

        .hot-badge {
            position: absolute;
            top: -10px;
            right: -10px;
            background: #ff4757;
            color: white;
            padding: 10px 20px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: bold;
            transform: rotate(15deg);
        }

        /* Enhanced Booking System */
        .booking-service-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 15px 50px rgba(0,0,0,0.08);
            transition: all 0.4s ease;
            margin-bottom: 30px;
            border: 2px solid transparent;
            cursor: pointer;
        }

        .booking-service-card:hover {
            transform: translateY(-5px);
            border-color: var(--primary-gold);
        }

        .booking-service-card.selected {
            border-color: var(--primary-gold);
            box-shadow: 0 0 0 3px rgba(212, 175, 55, 0.2);
        }

        .service-image {
            height: 200px;
            background: linear-gradient(135deg, var(--sage), var(--accent-pink));
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            color: white;
            position: relative;
        }

        .price-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            background: var(--primary-gold);
            color: white;
            padding: 8px 15px;
            border-radius: 20px;
            font-weight: bold;
        }

        .time-slots {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 20px;
        }

        .time-slot {
            padding: 8px 15px;
            background: var(--light-gray);
            border: 1px solid #ddd;
            border-radius: 20px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 0.9rem;
        }

        .time-slot:hover {
            background: var(--primary-gold);
            color: white;
        }

        .time-slot.selected {
            background: var(--primary-gold);
            color: white;
        }

        .time-slot.unavailable {
            background: #f8f8f8;
            color: #ccc;
            cursor: not-allowed;
        }

        /* Staff Selection */
        .staff-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            border: 2px solid transparent;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-bottom: 20px;
        }

        .staff-card:hover {
            border-color: var(--primary-gold);
        }

        .staff-card.selected {
            border-color: var(--primary-gold);
            background: var(--light-gray);
        }

        .staff-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-gold), var(--rose-gold));
            margin: 0 auto 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            color: white;
        }

        /* Calendar */
        .calendar {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 15px 50px rgba(0,0,0,0.08);
        }

        .calendar-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .calendar-nav {
            background: var(--primary-gold);
            color: white;
            border: none;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            cursor: pointer;
        }

        .calendar-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 10px;
        }

        .calendar-day {
            padding: 15px;
            text-align: center;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
            border: 1px solid #eee;
        }

        .calendar-day:hover {
            background: var(--light-gray);
        }

        .calendar-day.selected {
            background: var(--primary-gold);
            color: white;
        }

        .calendar-day.disabled {
            color: #ccc;
            cursor: not-allowed;
        }

        /* Payment */
        .payment-summary {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 15px 50px rgba(0,0,0,0.08);
            border-left: 4px solid var(--primary-gold);
            margin-bottom: 30px;
        }

        .payment-method {
            background: white;
            border: 2px solid #eee;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 15px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .payment-method:hover {
            border-color: var(--primary-gold);
        }

        .payment-method.selected {
            border-color: var(--primary-gold);
            background: var(--light-gray);
        }

        .slip-upload {
            border: 2px dashed var(--primary-gold);
            border-radius: 10px;
            padding: 40px;
            text-align: center;
            background: var(--light-gray);
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .slip-upload:hover {
            background: white;
        }

        /* Forms */
        .form-control {
            border: 1px solid #E5E5E5;
            border-radius: 8px;
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

        /* Map */
        .map-container {
            height: 400px;
            background: var(--light-gray);
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 30px;
        }

        /* Contact Info */
        .contact-info {
            background: white;
            border-radius: 15px;
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

        /* Modal */
        .modal-content {
            border: none;
            border-radius: 15px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.2);
        }

        .modal-header {
            border-bottom: 1px solid #eee;
            padding: 30px 30px 20px;
        }

        .modal-body {
            padding: 30px;
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

        /* Responsive */
        @media (max-width: 768px) {
            .hero-title {
                font-size: 2.8rem;
            }
            
            .section-title {
                font-size: 2.5rem;
            }
            
            .service-card,
            .contact-info {
                padding: 30px;
                margin-bottom: 30px;
            }

            .navbar-nav .nav-link {
                margin: 0 5px;
            }

            .auth-buttons {
                margin-left: 0;
                margin-top: 15px;
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
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg fixed-top">
        <div class="container">
            <a class="navbar-brand font-serif" href="#" onclick="showPage('home')">
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
                
                <!-- Authentication -->
                <div class="auth-buttons" id="authButtons">
                    <a href="#" class="btn-auth" onclick="showLoginModal()">Login</a>
                    <a href="#" class="btn-auth signup" onclick="showSignupModal()">Sign Up</a>
                </div>
    </div>

    <!-- Contact Page -->
    <div id="contact" class="page-section" style="padding-top: 120px;">
        <div class="container">
            <h2 class="section-title font-serif">Contact Us</h2>
            <p class="section-subtitle">ติดต่อเราเพื่อสอบถามข้อมูลเพิ่มเติม หรือจองบริการ</p>
            
            <div class="row">
                <!-- Map -->
                <div class="col-12 mb-5">
                    <div class="map-container">
                        <div class="text-center">
                            <i class="fas fa-map-marker-alt" style="font-size: 4rem; color: var(--primary-gold); margin-bottom: 20px;"></i>
                            <h4 class="font-serif" style="color: var(--charcoal);">Our Location</h4>
                            <p style="color: var(--dark-gray);">123 Sukhumvit Road, Watthana, Bangkok 10110</p>
                            <button class="btn-luxury" onclick="openMap()">
                                <i class="fas fa-directions me-2"></i>Get Directions
                            </button>
                        </div>
                    </div>
                </div>

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
                                <textarea class="form-control" id="contactMessage" rows="4" required placeholder="สอบถามข้อมูล หรือข้อความ..."></textarea>
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

    <!-- Profile Page -->
    <div id="profile" class="page-section" style="padding-top: 120px;">
        <div class="container">
            <h2 class="section-title font-serif">My Profile</h2>
            <p class="section-subtitle">จัดการข้อมูลส่วนตัวและการตั้งค่าบัญชี</p>
            
            <div class="row">
                <div class="col-lg-4 mb-4">
                    <div class="service-card text-center">
                        <div class="team-avatar mb-3">
                            <i class="fas fa-user"></i>
                        </div>
                        <h4 class="font-serif" id="profileName">John Doe</h4>
                        <p style="color: var(--primary-gold);" id="profileEmail">john@example.com</p>
                        <div class="mt-4">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Total Bookings:</span>
                                <strong id="totalBookings">12</strong>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Member Since:</span>
                                <strong id="memberSince">Jan 2023</strong>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span>Points:</span>
                                <strong style="color: var(--primary-gold);" id="loyaltyPoints">350</strong>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-8">
                    <div class="service-card">
                        <h4 class="font-serif mb-4" style="color: var(--charcoal);">Personal Information</h4>
                        
                        <form id="profileForm" onsubmit="updateProfile(event)">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="profileFirstName" class="form-label">First Name *</label>
                                    <input type="text" class="form-control" id="profileFirstName" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="profileLastName" class="form-label">Last Name *</label>
                                    <input type="text" class="form-control" id="profileLastName" required>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="profilePhone" class="form-label">Phone *</label>
                                    <input type="tel" class="form-control" id="profilePhone" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="profileEmailEdit" class="form-label">Email *</label>
                                    <input type="email" class="form-control" id="profileEmailEdit" required>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="profileAddress" class="form-label">Address</label>
                                <textarea class="form-control" id="profileAddress" rows="3"></textarea>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="profileBirthdate" class="form-label">Date of Birth</label>
                                    <input type="date" class="form-control" id="profileBirthdate">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="profileGender" class="form-label">Gender</label>
                                    <select class="form-control" id="profileGender">
                                        <option value="">Select Gender</option>
                                        <option value="male">Male</option>
                                        <option value="female">Female</option>
                                        <option value="other">Other</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <label for="profilePreferences" class="form-label">Treatment Preferences</label>
                                <textarea class="form-control" id="profilePreferences" rows="3" placeholder="แจ้งความต้องการพิเศษ หรือข้อควรระวัง..."></textarea>
                            </div>
                            
                            <button type="submit" class="btn-luxury">
                                <i class="fas fa-save me-2"></i>Update Profile
                            </button>
                        </form>
                    </div>

                    <!-- Change Password -->
                    <div class="service-card mt-4">
                        <h4 class="font-serif mb-4" style="color: var(--charcoal);">Change Password</h4>
                        
                        <form id="passwordForm" onsubmit="changePassword(event)">
                            <div class="mb-3">
                                <label for="currentPassword" class="form-label">Current Password *</label>
                                <input type="password" class="form-control" id="currentPassword" required>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="newPassword" class="form-label">New Password *</label>
                                    <input type="password" class="form-control" id="newPassword" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="confirmPassword" class="form-label">Confirm Password *</label>
                                    <input type="password" class="form-control" id="confirmPassword" required>
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-outline-secondary">
                                <i class="fas fa-key me-2"></i>Change Password
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- My Bookings Page -->
    <div id="bookings" class="page-section" style="padding-top: 120px;">
        <div class="container">
            <h2 class="section-title font-serif">My Bookings</h2>
            <p class="section-subtitle">ดูประวัติการจองและการนัดหมายของคุณ</p>
            
            <!-- Booking Filters -->
            <div class="row mb-4">
                <div class="col-lg-12">
                    <div class="service-card">
                        <div class="row align-items-center">
                            <div class="col-md-3">
                                <select class="form-control" id="bookingFilter" onchange="filterBookings()">
                                    <option value="all">All Bookings</option>
                                    <option value="upcoming">Upcoming</option>
                                    <option value="completed">Completed</option>
                                    <option value="cancelled">Cancelled</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <input type="text" class="form-control" id="bookingSearch" placeholder="Search by booking code or service..." onkeyup="searchBookings()">
                            </div>
                            <div class="col-md-3">
                                <button class="btn-luxury w-100" onclick="showPage('booking')">
                                    <i class="fas fa-plus me-2"></i>New Booking
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bookings List -->
            <div id="bookingsList">
                <!-- Sample Booking -->
                <div class="booking-item upcoming mb-4">
                    <div class="service-card">
                        <div class="row align-items-center">
                            <div class="col-lg-2">
                                <div class="text-center">
                                    <div style="width: 80px; height: 80px; background: linear-gradient(135deg, var(--primary-gold), var(--rose-gold)); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 10px;">
                                        <i class="fas fa-hands" style="color: white; font-size: 2rem;"></i>
                                    </div>
                                    <span class="badge" style="background: #28a745; color: white;">Confirmed</span>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <h5 class="font-serif" style="color: var(--charcoal); margin-bottom: 5px;">นวดไทยโบราณ</h5>
                                <p style="color: var(--dark-gray); margin-bottom: 5px;">60 minutes • ฿800</p>
                                <p style="color: var(--dark-gray); margin: 0; font-size: 0.9rem;">Therapist: คุณนิรมล</p>
                            </div>
                            <div class="col-lg-3">
                                <p style="color: var(--charcoal); margin-bottom: 5px;"><strong>Dec 25, 2024</strong></p>
                                <p style="color: var(--dark-gray); margin-bottom: 5px;">2:00 PM</p>
                                <p style="color: var(--primary-gold); margin: 0; font-size: 0.9rem;">PSPA-2024-001</p>
                            </div>
                            <div class="col-lg-3 text-end">
                                <button class="btn btn-outline-secondary btn-sm me-2" onclick="rescheduleBooking('PSPA-2024-001')">
                                    <i class="fas fa-calendar me-1"></i>Reschedule
                                </button>
                                <button class="btn btn-outline-danger btn-sm" onclick="cancelBooking('PSPA-2024-001')">
                                    <i class="fas fa-times me-1"></i>Cancel
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sample Completed Booking -->
                <div class="booking-item completed mb-4">
                    <div class="service-card" style="opacity: 0.8;">
                        <div class="row align-items-center">
                            <div class="col-lg-2">
                                <div class="text-center">
                                    <div style="width: 80px; height: 80px; background: linear-gradient(135deg, var(--sage), var(--accent-pink)); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 10px;">
                                        <i class="fas fa-tint" style="color: white; font-size: 2rem;"></i>
                                    </div>
                                    <span class="badge" style="background: #17a2b8; color: white;">Completed</span>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <h5 class="font-serif" style="color: var(--charcoal); margin-bottom: 5px;">นวดน้ำมันอโรม่า</h5>
                                <p style="color: var(--dark-gray); margin-bottom: 5px;">90 minutes • ฿1,200</p>
                                <p style="color: var(--dark-gray); margin: 0; font-size: 0.9rem;">Therapist: คุณสมใจ</p>
                            </div>
                            <div class="col-lg-3">
                                <p style="color: var(--charcoal); margin-bottom: 5px;"><strong>Dec 15, 2024</strong></p>
                                <p style="color: var(--dark-gray); margin-bottom: 5px;">3:00 PM</p>
                                <p style="color: var(--primary-gold); margin: 0; font-size: 0.9rem;">PSPA-2024-002</p>
                            </div>
                            <div class="col-lg-3 text-end">
                                <button class="btn btn-outline-primary btn-sm me-2" onclick="rateService('PSPA-2024-002')">
                                    <i class="fas fa-star me-1"></i>Rate
                                </button>
                                <button class="btn-luxury btn-sm" onclick="rebookService('aroma-1200')">
                                    <i class="fas fa-redo me-1"></i>Book Again
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div id="emptyBookings" style="display: none;">
                    <div class="text-center py-5">
                        <i class="fas fa-calendar-times" style="font-size: 4rem; color: var(--dark-gray); margin-bottom: 20px;"></i>
                        <h4 class="font-serif" style="color: var(--charcoal);">No Bookings Found</h4>
                        <p style="color: var(--dark-gray);">คุณยังไม่มีการจอง หรือลองค้นหาด้วยคำอื่น</p>
                        <button class="btn-luxury" onclick="showPage('booking')">
                            <i class="fas fa-plus me-2"></i>Make First Booking
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Login Modal -->
    <div class="modal fade" id="loginModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title font-serif">เข้าสู่ระบบ</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="loginForm" onsubmit="login(event)">
                        <div class="mb-3">
                            <label for="loginEmail" class="form-label">Email *</label>
                            <input type="email" class="form-control" id="loginEmail" required>
                        </div>
                        <div class="mb-4">
                            <label for="loginPassword" class="form-label">Password *</label>
                            <input type="password" class="form-control" id="loginPassword" required>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="rememberMe">
                                <label class="form-check-label" for="rememberMe">Remember me</label>
                            </div>
                            <a href="#" style="color: var(--primary-gold); text-decoration: none;">Forgot Password?</a>
                        </div>
                        <button type="submit" class="btn-luxury w-100">
                            <i class="fas fa-sign-in-alt me-2"></i>Login
                        </button>
                    </form>
                    <div class="text-center mt-3">
                        <p>Don't have an account? <a href="#" onclick="showSignupModal()" style="color: var(--primary-gold);">Sign up</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Signup Modal -->
    <div class="modal fade" id="signupModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title font-serif">สมัครสมาชิก</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="signupForm" onsubmit="signup(event)">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="signupFirstName" class="form-label">First Name *</label>
                                <input type="text" class="form-control" id="signupFirstName" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="signupLastName" class="form-label">Last Name *</label>
                                <input type="text" class="form-control" id="signupLastName" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="signupEmail" class="form-label">Email *</label>
                            <input type="email" class="form-control" id="signupEmail" required>
                        </div>
                        <div class="mb-3">
                            <label for="signupPhone" class="form-label">Phone *</label>
                            <input type="tel" class="form-control" id="signupPhone" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="signupPassword" class="form-label">Password *</label>
                                <input type="password" class="form-control" id="signupPassword" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="signupConfirmPassword" class="form-label">Confirm Password *</label>
                                <input type="password" class="form-control" id="signupConfirmPassword" required>
                            </div>
                        </div>
                        <div class="mb-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="agreeTerms" required>
                                <label class="form-check-label" for="agreeTerms">
                                    I agree to the <a href="#" style="color: var(--primary-gold);">Terms & Conditions</a>
                                </label>
                            </div>
                        </div>
                        <button type="submit" class="btn-luxury w-100">
                            <i class="fas fa-user-plus me-2"></i>Sign Up
                        </button>
                    </form>
                    <div class="text-center mt-3">
                        <p>Already have an account? <a href="#" onclick="showLoginModal()" style="color: var(--primary-gold);">Login</a></p>
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

            <div style="height: 1px; background: var(--dark-gray); margin: 40px 0;"></div>
            
            <div class="text-center" style="color: var(--dark-gray); font-size: 0.9rem;">
                <p>&copy; 2024 Pure Serenity Spa. All rights reserved. | Designed with luxury in mind.</p>
            </div>
        </div>
    </footer>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        // Global Variables
        let currentUser = null;
        let selectedService = null;
        let selectedDate = null;
        let selectedTime = null;
        let selectedStaff = null;
        let currentBookingStep = 1;
        let currentMonth = new Date().getMonth();
        let currentYear = new Date().getFullYear();

        // Initialize calendar styles
        const style = document.createElement('style');
        style.textContent = `
            .booking-steps {
                display: flex;
                align-items: center;
                max-width: 600px;
            }
            
            .step {
                display: flex;
                flex-direction: column;
                align-items: center;
                opacity: 0.5;
                transition: all 0.3s ease;
            }
            
            .step.active {
                opacity: 1;
            }
            
            .step-circle {
                width: 40px;
                height: 40px;
                border-radius: 50%;
                background: #ddd;
                display: flex;
                align-items: center;
                justify-content: center;
                color: white;
                font-weight: bold;
                margin-bottom: 10px;
                transition: all 0.3s ease;
            }
            
            .step.active .step-circle {
                background: var(--primary-gold);
            }
            
            .step-label {
                font-size: 0.8rem;
                text-align: center;
                color: var(--dark-gray);
            }
            
            .step-line {
                flex: 1;
                height: 2px;
                background: #ddd;
                margin: 0 10px;
            }
        `;
        document.head.appendChild(style);

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
            
            // Find and activate the correct nav link
            const targetLink = Array.from(navLinks).find(link => 
                link.getAttribute('onclick') && link.getAttribute('onclick').includes(pageId)
            );
            if (targetLink) {
                targetLink.classList.add('active');
            }
            
            // Scroll to top
            window.scrollTo(0, 0);
            
            // Close mobile menu if open
            const navbarCollapse = document.getElementById('navbarNav');
            if (navbarCollapse.classList.contains('show')) {
                const bsCollapse = new bootstrap.Collapse(navbarCollapse);
                bsCollapse.hide();
            }

            // Reset booking steps when navigating to booking page
            if (pageId === 'booking') {
                resetBookingSteps();
            }

            // Check authentication for protected pages
            if (['profile', 'bookings'].includes(pageId) && !currentUser) {
                showLoginModal();
                return;
            }
        }

        // Authentication Functions
        function showLoginModal() {
            const modal = new bootstrap.Modal(document.getElementById('loginModal'));
            modal.show();
        }

        function showSignupModal() {
            // Hide login modal if open
            const loginModal = bootstrap.Modal.getInstance(document.getElementById('loginModal'));
            if (loginModal) loginModal.hide();
            
            const modal = new bootstrap.Modal(document.getElementById('signupModal'));
            modal.show();
        }

        function login(event) {
            event.preventDefault();
            
            const email = document.getElementById('loginEmail').value;
            const password = document.getElementById('loginPassword').value;
            
            // Simulate login (replace with real authentication)
            currentUser = {
                id: 1,
                email: email,
                name: 'John Doe',
                phone: '+66 9 1234 5678',
                joinDate: 'Jan 2023',
                totalBookings: 12,
                loyaltyPoints: 350
            };
            
            updateAuthUI();
            
            // Hide modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('loginModal'));
            modal.hide();
            
            // Reset form
            document.getElementById('loginForm').reset();
            
            // Show success message
            alert('เข้าสู่ระบบสำเร็จ!');
        }

        function signup(event) {
            event.preventDefault();
            
            const firstName = document.getElementById('signupFirstName').value;
            const lastName = document.getElementById('signupLastName').value;
            const email = document.getElementById('signupEmail').value;
            const phone = document.getElementById('signupPhone').value;
            const password = document.getElementById('signupPassword').value;
            const confirmPassword = document.getElementById('signupConfirmPassword').value;
            
            if (password !== confirmPassword) {
                alert('รหัสผ่านไม่ตรงกัน');
                return;
            }
            
            // Simulate signup (replace with real registration)
            currentUser = {
                id: 1,
                email: email,
                name: `${firstName} ${lastName}`,
                phone: phone,
                joinDate: new Date().toLocaleDateString('en-US', { month: 'short', year: 'numeric' }),
                totalBookings: 0,
                loyaltyPoints: 0
            };
            
            updateAuthUI();
            
            // Hide modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('signupModal'));
            modal.hide();
            
            // Reset form
            document.getElementById('signupForm').reset();
            
            // Show success message
            alert('สมัครสมาชิกสำเร็จ!');
        }

        function logout() {
            currentUser = null;
            updateAuthUI();
            showPage('home');
            document.getElementById('userDropdown').classList.remove('show');
        }

        function updateAuthUI() {
            const authButtons = document.getElementById('authButtons');
            const userMenu = document.getElementById('userMenu');
            const viewBookingsBtn = document.getElementById('viewBookingsBtn');
            
            if (currentUser) {
                authButtons.classList.add('d-none');
                userMenu.classList.remove('d-none');
                if (viewBookingsBtn) viewBookingsBtn.style.display = 'inline-block';
                
                // Update user avatar with first letter of name
                const userAvatar = document.getElementById('userAvatar');
                userAvatar.innerHTML = `<span style="font-weight: bold;">${currentUser.name.charAt(0)}</span>`;
                
                // Update profile information if on profile page
                updateProfileInfo();
            } else {
                authButtons.classList.remove('d-none');
                userMenu.classList.add('d-none');
                if (viewBookingsBtn) viewBookingsBtn.style.display = 'none';
            }
        }

        function toggleUserDropdown() {
            const dropdown = document.getElementById('userDropdown');
            dropdown.classList.toggle('show');
        }

        // Close dropdown when clicking outside
        document.addEventListener('click', function(event) {
            const userMenu = document.getElementById('userMenu');
            const dropdown = document.getElementById('userDropdown');
            
            if (!userMenu.contains(event.target)) {
                dropdown.classList.remove('show');
            }
        });

        // Booking System Functions
        function resetBookingSteps() {
            currentBookingStep = 1;
            selectedService = null;
            selectedDate = null;
            selectedTime = null;
            selectedStaff = null;
            
            // Show only step 1
            for (let i = 1; i <= 4; i++) {
                const step = document.getElementById(`bookingStep${i}`);
                if (step) {
                    step.style.display = i === 1 ? 'block' : 'none';
                }
            }
            
            // Update step indicators
            updateStepIndicators();
            
            // Reset forms
            const bookingForm = document.getElementById('bookingForm');
            if (bookingForm) bookingForm.reset();
            
            // Hide success message
            const successDiv = document.getElementById('bookingSuccess');
            if (successDiv) successDiv.style.display = 'none';
        }

        function selectService(serviceId, serviceName, price, duration) {
            selectedService = { id: serviceId, name: serviceName, price: price, duration: duration };
            
            // Update UI
            const serviceCards = document.querySelectorAll('.booking-service-card');
            serviceCards.forEach(card => card.classList.remove('selected'));
            event.currentTarget.classList.add('selected');
            
            // Show next button
            document.getElementById('nextToStep2').style.display = 'inline-block';
        }

        function nextStep(step) {
            currentBookingStep = step;
            
            // Hide all steps
            for (let i = 1; i <= 4; i++) {
                const stepDiv = document.getElementById(`bookingStep${i}`);
                if (stepDiv) {
                    stepDiv.style.display = i === step ? 'block' : 'none';
                }
            }
            
            updateStepIndicators();
            
            // Initialize calendar when going to step 2
            if (step === 2) {
                initializeCalendar();
            }
            
            // Update summary when going to step 3 or 4
            if (step === 3) {
                updateBookingSummary();
            }
            
            if (step === 4) {
                updatePaymentSummary();
            }
        }

        function prevStep(step) {
            currentBookingStep = step;
            
            // Hide all steps
            for (let i = 1; i <= 4; i++) {
                const stepDiv = document.getElementById(`bookingStep${i}`);
                if (stepDiv) {
                    stepDiv.style.display = i === step ? 'block' : 'none';
                }
            }
            
            updateStepIndicators();
        }

        function updateStepIndicators() {
            for (let i = 1; i <= 4; i++) {
                const step = document.getElementById(`step${i}`);
                if (step) {
                    if (i <= currentBookingStep) {
                        step.classList.add('active');
                    } else {
                        step.classList.remove('active');
                    }
                }
            }
        }

        // Calendar Functions
        function initializeCalendar() {
            updateCalendarDisplay();
        }

        function updateCalendarDisplay() {
            const monthNames = [
                'January', 'February', 'March', 'April', 'May', 'June',
                'July', 'August', 'September', 'October', 'November', 'December'
            ];
            
            document.getElementById('currentMonth').textContent = `${monthNames[currentMonth]} ${currentYear}`;
            
            const calendarGrid = document.getElementById('calendarGrid');
            calendarGrid.innerHTML = '';
            
            // Add day headers
            const dayHeaders = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
            dayHeaders.forEach(day => {
                const dayHeader = document.createElement('div');
                dayHeader.textContent = day;
                dayHeader.style.padding = '10px';
                dayHeader.style.textAlign = 'center';
                dayHeader.style.fontWeight = 'bold';
                dayHeader.style.color = 'var(--primary-gold)';
                calendarGrid.appendChild(dayHeader);
            });
            
            const firstDay = new Date(currentYear, currentMonth, 1).getDay();
            const daysInMonth = new Date(currentYear, currentMonth + 1, 0).getDate();
            const today = new Date();
            
            // Add empty cells for days before the first day of the month
            for (let i = 0; i < firstDay; i++) {
                const emptyDay = document.createElement('div');
                calendarGrid.appendChild(emptyDay);
            }
            
            // Add days of the month
            for (let day = 1; day <= daysInMonth; day++) {
                const dayElement = document.createElement('div');
                dayElement.className = 'calendar-day';
                dayElement.textContent = day;
                
                const dayDate = new Date(currentYear, currentMonth, day);
                
                // Disable past dates
                if (dayDate < today.setHours(0, 0, 0, 0)) {
                    dayElement.classList.add('disabled');
                } else {
                    dayElement.onclick = () => selectDate(dayDate);
                }
                
                calendarGrid.appendChild(dayElement);
            }
        }

        function changeMonth(direction) {
            currentMonth += direction;
            
            if (currentMonth > 11) {
                currentMonth = 0;
                currentYear++;
            } else if (currentMonth < 0) {
                currentMonth = 11;
                currentYear--;
            }
            
            // Don't allow going to past months
            const today = new Date();
            if (currentYear < today.getFullYear() || 
                (currentYear === today.getFullYear() && currentMonth < today.getMonth())) {
                currentMonth = today.getMonth();
                currentYear = today.getFullYear();
            }
            
            // Limit to 3 months ahead
            const maxDate = new Date();
            maxDate.setMonth(maxDate.getMonth() + 3);
            if (currentYear > maxDate.getFullYear() || 
                (currentYear === maxDate.getFullYear() && currentMonth > maxDate.getMonth())) {
                currentMonth = maxDate.getMonth();
                currentYear = maxDate.getFullYear();
            }
            
            updateCalendarDisplay();
        }

        function selectDate(date) {
            selectedDate = date;
            
            // Update UI
            const calendarDays = document.querySelectorAll('.calendar-day');
            calendarDays.forEach(day => day.classList.remove('selected'));
            event.target.classList.add('selected');
            
            // Update time slots availability (simulate)
            updateTimeSlots();
            
            checkStep2Completion();
        }

        function updateTimeSlots() {
            const timeSlots = document.querySelectorAll('#timeSlots .time-slot');
            timeSlots.forEach(slot => {
                slot.classList.remove('unavailable', 'selected');
                // Simulate some random unavailable slots
                if (Math.random() < 0.3) {
                    slot.classList.add('unavailable');
                }
            });
        }

        function selectTime(time) {
            if (event.target.classList.contains('unavailable')) {
                return;
            }
            
            selectedTime = time;
            
            // Update UI
            const timeSlots = document.querySelectorAll('#timeSlots .time-slot');
            timeSlots.forEach(slot => slot.classList.remove('selected'));
            event.target.classList.add('selected');
            
            checkStep2Completion();
        }

        function selectStaff(staffId, staffName) {
            selectedStaff = { id: staffId, name: staffName };
            
            // Update UI
            const staffCards = document.querySelectorAll('.staff-card');
            staffCards.forEach(card => card.classList.remove('selected'));
            event.currentTarget.classList.add('selected');
            
            checkStep2Completion();
        }

        function checkStep2Completion() {
            const nextBtn = document.getElementById('nextToStep3');
            if (selectedDate && selectedTime && selectedStaff) {
                nextBtn.style.display = 'inline-block';
            } else {
                nextBtn.style.display = 'none';
            }
        }

        function updateBookingSummary() {
            if (!selectedService || !selectedDate || !selectedTime || !selectedStaff) return;
            
            const summary = document.getElementById('bookingSummary');
            const dateStr = selectedDate.toLocaleDateString('th-TH', { 
                weekday: 'long', 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric' 
            });
            
            summary.innerHTML = `
                <div class="mb-3">
                    <h6 style="color: var(--primary-gold);">บริการ</h6>
                    <p style="margin: 0;">${selectedService.name} (${selectedService.duration} นาที)</p>
                </div>
                <div class="mb-3">
                    <h6 style="color: var(--primary-gold);">วันที่และเวลา</h6>
                    <p style="margin: 0;">${dateStr}</p>
                    <p style="margin: 0;">${selectedTime}</p>
                </div>
                <div class="mb-3">
                    <h6 style="color: var(--primary-gold);">ผู้ให้บริการ</h6>
                    <p style="margin: 0;">${selectedStaff.name}</p>
                </div>
                <div style="border-top: 1px solid #eee; padding-top: 15px;">
                    <div class="d-flex justify-content-between">
                        <strong>ราคา:</strong>
                        <strong style="color: var(--primary-gold);">฿${selectedService.price.toLocaleString()}</strong>
                    </div>
                </div>
            `;
        }

        function updatePaymentSummary() {
            if (!selectedService) return;
            
            const summary = document.getElementById('paymentSummary');
            const totalAmount = document.getElementById('totalAmount');
            const promptpayAmount = document.getElementById('promptpayAmount');
            
            summary.innerHTML = `
                <div class="d-flex justify-content-between mb-2">
                    <span>${selectedService.name}</span>
                    <span>฿${selectedService.price.toLocaleString()}</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span>Service charge</span>
                    <span>฿0</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span>Tax (7%)</span>
                    <span>฿${Math.round(selectedService.price * 0.07).toLocaleString()}</span>
                </div>
            `;
            
            const total = selectedService.price + Math.round(selectedService.price * 0.07);
            totalAmount.textContent = `฿${total.toLocaleString()}`;
            if (promptpayAmount) {
                promptpayAmount.textContent = `฿${total.toLocaleString()}`;
            }
        }

        function selectPaymentMethod(method) {
            const paymentMethods = document.querySelectorAll('.payment-method');
            paymentMethods.forEach(pm => pm.classList.remove('selected'));
            event.currentTarget.classList.add('selected');
            
            // Show/hide payment details
            document.getElementById('bankDetails').style.display = method === 'bank' ? 'block' : 'none';
            document.getElementById('promptpayDetails').style.display = method === 'promptpay' ? 'block' : 'none';
        }

        function handleSlipUpload(input) {
            const file = input.files[0];
            if (!file) return;
            
            const previewId = input.id === 'slipFile' ? 'slipPreview' : 'promptpayPreview';
            const preview = document.getElementById(previewId) || 
                           document.querySelector(`#${input.id.replace('Slip', 'Details')} #slipPreview`) ||
                           (() => {
                               const div = document.createElement('div');
                               div.id = 'slipPreview';
                               div.style.marginTop = '15px';
                               input.parentNode.appendChild(div);
                               return div;
                           })();
            
            preview.style.display = 'block';
            preview.innerHTML = `
                <div class="d-flex align-items-center p-3" style="background: var(--light-gray); border-radius: 10px;">
                    <i class="fas fa-file-image" style="color: var(--primary-gold); font-size: 2rem; margin-right: 15px;"></i>
                    <div>
                        <h6 style="margin: 0; color: var(--charcoal);">${file.name}</h6>
                        <small style="color: var(--dark-gray);">Size: ${(file.size / 1024 / 1024).toFixed(2)} MB</small>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-danger ms-auto" onclick="removeSlip('${input.id}')">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `;
        }

        function removeSlip(inputId) {
            document.getElementById(inputId).value = '';
            const preview = document.getElementById('slipPreview');
            if (preview) preview.style.display = 'none';
        }

        function confirmBooking() {
            // Validate required fields
            const name = document.getElementById('customerName').value;
            const phone = document.getElementById('customerPhone').value;
            
            if (!name || !phone) {
                alert('กรุณากรอกข้อมูลให้ครบถ้วน');
                return;
            }
            
            // Check payment method selection
            const selectedPayment = document.querySelector('.payment-method.selected');
            if (!selectedPayment) {
                alert('กรุณาเลือกวิธีการชำระเงิน');
                return;
            }
            
            // Check slip upload for bank transfer and promptpay
            const paymentMethod = selectedPayment.getAttribute('onclick').match(/'([^']+)'/)[1];
            if ((paymentMethod === 'bank' || paymentMethod === 'promptpay')) {
                const slipInput = paymentMethod === 'bank' ? 
                    document.getElementById('slipFile') : 
                    document.getElementById('promptpaySlip');
                
                if (!slipInput.files[0]) {
                    alert('กรุณาอัพโหลดหลักฐานการชำระเงิน');
                    return;
                }
            }
            
            // Generate booking code
            const bookingCode = 'PSPA-' + new Date().getFullYear() + '-' + String(Math.floor(Math.random() * 1000)).padStart(3, '0');
            document.getElementById('bookingCode').textContent = bookingCode;
            
            // Hide step 4 and show success
            document.getElementById('bookingStep4').style.display = 'none';
            document.getElementById('bookingSuccess').style.display = 'block';
            
            // Update user's booking count if logged in
            if (currentUser) {
                currentUser.totalBookings++;
                currentUser.loyaltyPoints += Math.floor(selectedService.price / 10);
            }
        }

        // Profile Functions
        function updateProfileInfo() {
            if (!currentUser) return;
            
            // Update profile display
            const profileName = document.getElementById('profileName');
            const profileEmail = document.getElementById('profileEmail');
            const totalBookings = document.getElementById('totalBookings');
            const memberSince = document.getElementById('memberSince');
            const loyaltyPoints = document.getElementById('loyaltyPoints');
            
            if (profileName) profileName.textContent = currentUser.name;
            if (profileEmail) profileEmail.textContent = currentUser.email;
            if (totalBookings) totalBookings.textContent = currentUser.totalBookings;
            if (memberSince) memberSince.textContent = currentUser.joinDate;
            if (loyaltyPoints) loyaltyPoints.textContent = currentUser.loyaltyPoints;
            
            // Update profile form
            const nameParts = currentUser.name.split(' ');
            document.getElementById('profileFirstName').value = nameParts[0] || '';
            document.getElementById('profileLastName').value = nameParts.slice(1).join(' ') || '';
            document.getElementById('profilePhone').value = currentUser.phone || '';
            document.getElementById('profileEmailEdit').value = currentUser.email || '';
        }

        function updateProfile(event) {
            event.preventDefault();
            
            const firstName = document.getElementById('profileFirstName').value;
            const lastName = document.getElementById('profileLastName').value;
            const phone = document.getElementById('profilePhone').value;
            const email = document.getElementById('profileEmailEdit').value;
            
            // Update current user
            currentUser.name = `${firstName} ${lastName}`;
            currentUser.phone = phone;
            currentUser.email = email;
            
            updateAuthUI();
            alert('อัพเดทข้อมูลสำเร็จ!');
        }

        function changePassword(event) {
            event.preventDefault();
            
            const current = document.getElementById('currentPassword').value;
            const newPass = document.getElementById('newPassword').value;
            const confirm = document.getElementById('confirmPassword').value;
            
            if (newPass !== confirm) {
                alert('รหัสผ่านใหม่ไม่ตรงกัน');
                return;
            }
            
            // Simulate password change
            document.getElementById('passwordForm').reset();
            alert('เปลี่ยนรหัสผ่านสำเร็จ!');
        }

        // Booking Management Functions
        function filterBookings() {
            const filter = document.getElementById('bookingFilter').value;
            const bookings = document.querySelectorAll('.booking-item');
            
            bookings.forEach(booking => {
                if (filter === 'all' || booking.classList.contains(filter)) {
                    booking.style.display = 'block';
                } else {
                    booking.style.display = 'none';
                }
            });
        }

        function searchBookings() {
            const query = document.getElementById('bookingSearch').value.toLowerCase();
            const bookings = document.querySelectorAll('.booking-item');
            
            bookings.forEach(booking => {
                const text = booking.textContent.toLowerCase();
                if (text.includes(query)) {
                    booking.style.display = 'block';
                } else {
                    booking.style.display = 'none';
                }
            });
        }

        function rescheduleBooking(bookingId) {
            alert(`Reschedule booking ${bookingId} - This would open a reschedule modal`);
        }

        function cancelBooking(bookingId) {
            if (confirm('คุณแน่ใจหรือไม่ที่จะยกเลิกการจองนี้?')) {
                alert(`Cancelled booking ${bookingId}`);
                // In real implementation, update booking status
            }
        }

        function rateService(bookingId) {
            alert(`Rate service for booking ${bookingId} - This would open a rating modal`);
        }

        function rebookService(serviceId) {
            // Pre-select the service and go to booking page
            showPage('booking');
            // In real implementation, pre-fill the service selection
        }

        // Contact Functions
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

        function openMap() {
            // Open Google Maps with the spa location
            const address = '123 Sukhumvit Road, Watthana, Bangkok 10110, Thailand';
            const mapsUrl = `https://maps.google.com/?q=${encodeURIComponent(address)}`;
            window.open(mapsUrl, '_blank');
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            updateAuthUI();
            
            // Initialize calendar if on booking page
            if (document.getElementById('booking').classList.contains('active')) {
                initializeCalendar();
            }
        });
    </script>
</body>
</html>>

                <!-- User Menu -->
                <div class="user-menu d-none" id="userMenu">
                    <div class="user-avatar" onclick="toggleUserDropdown()" id="userAvatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="dropdown-menu-custom" id="userDropdown">
                        <a href="#" class="dropdown-item-custom" onclick="showPage('profile')">
                            <i class="fas fa-user me-2"></i>Profile
                        </a>
                        <a href="#" class="dropdown-item-custom" onclick="showPage('bookings')">
                            <i class="fas fa-calendar me-2"></i>My Bookings
                        </a>
                        <div style="border-top: 1px solid #eee; margin: 10px 0;"></div>
                        <a href="#" class="dropdown-item-custom" onclick="logout()">
                            <i class="fas fa-sign-out-alt me-2"></i>Logout
                        </a>
                    </div>
                </div>
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

        <!-- Owner & Team Section -->
        <section class="py-5" style="padding: 120px 0 !important; background: white;">
            <div class="container">
                <h2 class="section-title font-serif fade-in">Meet Our Team</h2>
                <p class="section-subtitle fade-in">ทีมผู้เชี่ยวชาญด้านการนวดและสปา ด้วยประสบการณ์กว่า 10 ปี</p>
                
                <div class="row">
                    <div class="col-lg-4 mb-4 fade-in">
                        <div class="team-card">
                            <div class="team-avatar">
                                <i class="fas fa-crown"></i>
                            </div>
                            <h4 class="font-serif mb-2" style="color: var(--charcoal);">คุณนิรมล สุขสวัสดิ์</h4>
                            <p style="color: var(--primary-gold); font-weight: 500; margin-bottom: 15px;">Owner & Master Therapist</p>
                            <p style="color: var(--dark-gray); line-height: 1.8;">
                                ผู้ก่อตั้งและเจ้าของสปา ด้วยประสบการณ์กว่า 15 ปี ในวงการสปาและการนวดไทยโบราณ 
                                ได้รับการรับรองจากสมาคมนวดไทยแห่งประเทศไทย
                            </p>
                        </div>
                    </div>

                    <div class="col-lg-4 mb-4 fade-in" style="animation-delay: 0.2s;">
                        <div class="team-card">
                            <div class="team-avatar">
                                <i class="fas fa-hands"></i>
                            </div>
                            <h4 class="font-serif mb-2" style="color: var(--charcoal);">คุณสมใจ บำรุงดี</h4>
                            <p style="color: var(--primary-gold); font-weight: 500; margin-bottom: 15px;">Senior Therapist</p>
                            <p style="color: var(--dark-gray); line-height: 1.8;">
                                ผู้เชี่ยวชาญด้านการนวดอโรม่าและหินร้อน มีประสบการณ์ 12 ปี 
                                เชี่ยวชาญในการผสมผสานเทคนิคดั้งเดิมกับสมัยใหม่
                            </p>
                        </div>
                    </div>

                    <div class="col-lg-4 mb-4 fade-in" style="animation-delay: 0.4s;">
                        <div class="team-card">
                            <div class="team-avatar">
                                <i class="fas fa-leaf"></i>
                            </div>
                            <h4 class="font-serif mb-2" style="color: var(--charcoal);">คุณวันเพ็ญ ใจดี</h4>
                            <p style="color: var(--primary-gold); font-weight: 500; margin-bottom: 15px;">Herbal Specialist</p>
                            <p style="color: var(--dark-gray); line-height: 1.8;">
                                ผู้เชี่ยวชาญด้านสมุนไพรไทยและการนวดสมุนไพร มีความรู้ลึกซึ้งเกี่ยวกับสรรพคุณ
                                ของสมุนไพรไทยต่างๆ ประสบการณ์ 10 ปี
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Hot Services Section -->
        <section class="py-5" style="padding: 80px 0 !important; background: var(--light-gray);">
            <div class="container">
                <h2 class="section-title font-serif fade-in">Hot Services</h2>
                <p class="section-subtitle fade-in">บริการยอดนิยมที่ลูกค้าเลือกมากที่สุด</p>
                
                <div class="row">
                    <div class="col-lg-4 mb-4 fade-in">
                        <div class="hot-service">
                            <div class="hot-badge">🔥 HOT</div>
                            <div style="font-size: 4rem; margin-bottom: 20px;">
                                <i class="fas fa-hands"></i>
                            </div>
                            <h4 class="font-serif mb-3">นวดไทยโบราณ Premium</h4>
                            <p class="mb-4">การนวดแบบไทยโบราณระดับพรีเมียม ด้วยเทคนิคพิเศษและสมุนไพรคัดสรร</p>
                            <div class="mb-3">
                                <span style="font-size: 1.5rem; font-weight: bold;">฿1,200</span>
                                <small style="text-decoration: line-through; margin-left: 10px;">฿1,500</small>
                            </div>
                            <a href="#" class="btn btn-outline-light" onclick="showPage('booking')">จองเลย</a>
                        </div>
                    </div>

                    <div class="col-lg-4 mb-4 fade-in" style="animation-delay: 0.2s;">
                        <div class="hot-service">
                            <div class="hot-badge">⭐ NEW</div>
                            <div style="font-size: 4rem; margin-bottom: 20px;">
                                <i class="fas fa-fire"></i>
                            </div>
                            <h4 class="font-serif mb-3">Signature Hot Stone</h4>
                            <p class="mb-4">นวดหินร้อนสูตรเฉพาะของเรา ผสมผสานเทคนิคตะวันออกและตะวันตก</p>
                            <div class="mb-3">
                                <span style="font-size: 1.5rem; font-weight: bold;">฿1,800</span>
                            </div>
                            <a href="#" class="btn btn-outline-light" onclick="showPage('booking')">จองเลย</a>
                        </div>
                    </div>

                    <div class="col-lg-4 mb-4 fade-in" style="animation-delay: 0.4s;">
                        <div class="hot-service">
                            <div class="hot-badge">💎 VIP</div>
                            <div style="font-size: 4rem; margin-bottom: 20px;">
                                <i class="fas fa-gem"></i>
                            </div>
                            <h4 class="font-serif mb-3">Royal Treatment</h4>
                            <p class="mb-4">แพ็คเกจพิเศษ 3 ชั่วโมง รวมนวด สครับ ทรีทเมนต์ พร้อมอาหารว่างและเครื่องดื่ม</p>
                            <div class="mb-3">
                                <span style="font-size: 1.5rem; font-weight: bold;">฿3,500</span>
                            </div>
                            <a href="#" class="btn btn-outline-light" onclick="showPage('booking')">จองเลย</a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Quick Promotions -->
        <section class="py-5" style="padding: 80px 0 !important; background: white;">
            <div class="container">
                <h2 class="section-title font-serif fade-in">Special Offers</h2>
                
                <div class="row">
                    <div class="col-lg-6 mb-4 fade-in">
                        <div style="background: linear-gradient(135deg, var(--primary-gold), var(--dark-gold)); color: white; border-radius: 15px; padding: 40px; text-align: center;">
                            <h3 class="font-serif mb-3">First Visit 30% OFF</h3>
                            <p class="mb-4">สำหรับลูกค้าใหม่ รับส่วนลดพิเศษ 30% ทุกบริการ</p>
                            <a href="#" class="btn btn-outline-light" onclick="showPage('booking')">จองเลย</a>
                        </div>
                    </div>
                    <div class="col-lg-6 mb-4 fade-in" style="animation-delay: 0.2s;">
                        <div style="background: linear-gradient(135deg, var(--sage), var(--accent-pink)); color: white; border-radius: 15px; padding: 40px; text-align: center;">
                            <h3 class="font-serif mb-3">Birthday Special</h3>
                            <p class="mb-4">ฉลองวันเกิดด้วยส่วนลด 25% ตลอดเดือนเกิด</p>
                            <a href="#" class="btn btn-outline-light" onclick="showPage('promotion')">ดูรายละเอียด</a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Services Section -->
        <section class="py-5" style="padding: 120px 0 !important; background: var(--light-gray);">
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
    </div>

    <!-- Massage Page -->
    <div id="massage" class="page-section" style="padding-top: 120px;">
        <div class="container">
            <h2 class="section-title font-serif">Massage Treatments</h2>
            <p class="section-subtitle">เลือกการนวดที่เหมาะสมกับความต้องการของคุณ</p>
            
            <div class="row">
                <div class="col-lg-6 mb-4">
                    <div class="booking-service-card">
                        <div class="service-image">
                            <div class="price-badge">฿800</div>
                            <i class="fas fa-hands"></i>
                        </div>
                        <div class="p-4">
                            <h4 class="font-serif mb-3" style="color: var(--charcoal);">นวดไทยโบราณ</h4>
                            <p class="mb-4" style="color: var(--dark-gray);">การนวดแบบดั้งเดิมของไทยที่ใช้การกดจุดสำคัญและการยืดกล้ามเนื้อ เพื่อความผ่อนคลายอย่างลึกซึ้ง</p>
                            
                            <div class="mb-3">
                                <h6 style="color: var(--primary-gold); text-transform: uppercase; letter-spacing: 1px;">Benefits:</h6>
                                <ul style="color: var(--dark-gray); line-height: 1.8; font-size: 0.9rem;">
                                    <li>เพิ่มความยืดหยุ่นของร่างกาย</li>
                                    <li>กระตุ้นการไหลเวียนของโลหิต</li>
                                    <li>ลดความเครียดและความตึงเครียด</li>
                                    <li>ปรับสมดุลพลังงานในร่างกาย</li>
                                </ul>
                            </div>
                            
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="badge" style="background: var(--sage); color: white; padding: 8px 15px;">60 minutes</span>
                                <a href="#" class="btn-luxury" style="padding: 10px 25px; font-size: 0.8rem;" onclick="showPage('booking')">จองเลย</a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6 mb-4">
                    <div class="booking-service-card">
                        <div class="service-image">
                            <div class="price-badge">฿1,200</div>
                            <i class="fas fa-tint"></i>
                        </div>
                        <div class="p-4">
                            <h4 class="font-serif mb-3" style="color: var(--charcoal);">นวดน้ำมันอโรม่า</h4>
                            <p class="mb-4" style="color: var(--dark-gray);">การนวดด้วยน้ำมันหอมระเหยจากธรรมชาติ เพื่อความผ่อนคลายสูงสุด</p>
                            
                            <div class="mb-3">
                                <h6 style="color: var(--primary-gold); text-transform: uppercase; letter-spacing: 1px;">Benefits:</h6>
                                <ul style="color: var(--dark-gray); line-height: 1.8; font-size: 0.9rem;">
                                    <li>ผ่อนคลายกล้ามเนื้อได้อย่างลึกซึ้ง</li>
                                    <li>บำรุงและชุมชื้นผิวหนัง</li>
                                    <li>ลดความเครียดผ่านการหอมบำบัด</li>
                                    <li>ปรับปรุงคุณภาพการนอนหลับ</li>
                                </ul>
                            </div>
                            
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="badge" style="background: var(--sage); color: white; padding: 8px 15px;">90 minutes</span>
                                <a href="#" class="btn-luxury" style="padding: 10px 25px; font-size: 0.8rem;" onclick="showPage('booking')">จองเลย</a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6 mb-4">
                    <div class="booking-service-card">
                        <div class="service-image">
                            <div class="price-badge">฿1,000</div>
                            <i class="fas fa-fire"></i>
                        </div>
                        <div class="p-4">
                            <h4 class="font-serif mb-3" style="color: var(--charcoal);">นวดหินร้อน</h4>
                            <p class="mb-4" style="color: var(--dark-gray);">การนวดด้วยหินภูเขาไฟร้อนช่วยคลายกล้ามเนื้อได้อย่างมีประสิทธิภาพ</p>
                            
                            <div class="mb-3">
                                <h6 style="color: var(--primary-gold); text-transform: uppercase; letter-spacing: 1px;">Benefits:</h6>
                                <ul style="color: var(--dark-gray); line-height: 1.8; font-size: 0.9rem;">
                                    <li>คลายความตึงเครียดของกล้ามเนื้อได้ลึก</li>
                                    <li>กระตุ้นการไหลเวียนโลหิต</li>
                                    <li>ลดอาการปวดเมื่อย</li>
                                    <li>เพิ่มความรู้สึกผ่อนคลาย</li>
                                </ul>
                            </div>
                            
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="badge" style="background: var(--sage); color: white; padding: 8px 15px;">75 minutes</span>
                                <a href="#" class="btn-luxury" style="padding: 10px 25px; font-size: 0.8rem;" onclick="showPage('booking')">จองเลย</a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6 mb-4">
                    <div class="booking-service-card">
                        <div class="service-image">
                            <div class="price-badge">฿1,500</div>
                            <i class="fas fa-leaf"></i>
                        </div>
                        <div class="p-4">
                            <h4 class="font-serif mb-3" style="color: var(--charcoal);">นวดสมุนไพรไทย</h4>
                            <p class="mb-4" style="color: var(--dark-gray);">การนวดด้วยถุงสมุนไพรไทยร้อนช่วยผ่อนคลายและบำบัดตามธรรมชาติ</p>
                            
                            <div class="mb-3">
                                <h6 style="color: var(--primary-gold); text-transform: uppercase; letter-spacing: 1px;">Benefits:</h6>
                                <ul style="color: var(--dark-gray); line-height: 1.8; font-size: 0.9rem;">
                                    <li>ใช้สมุนไพรไทยธรรมชาติ 100%</li>
                                    <li>ลดการอักเสบของกล้ามเนื้อ</li>
                                    <li>ช่วยในการหายของการบาดเจ็บ</li>
                                    <li>เสริมสร้างภูมิคุ้มกันร่างกาย</li>
                                </ul>
                            </div>
                            
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="badge" style="background: var(--sage); color: white; padding: 8px 15px;">90 minutes</span>
                                <a href="#" class="btn-luxury" style="padding: 10px 25px; font-size: 0.8rem;" onclick="showPage('booking')">จองเลย</a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Additional Services -->
                <div class="col-lg-6 mb-4">
                    <div class="booking-service-card">
                        <div class="service-image">
                            <div class="price-badge">฿1,800</div>
                            <i class="fas fa-gem"></i>
                        </div>
                        <div class="p-4">
                            <h4 class="font-serif mb-3" style="color: var(--charcoal);">Signature Hot Stone</h4>
                            <p class="mb-4" style="color: var(--dark-gray);">นวดหินร้อนสูตรเฉพาะของเรา ผสมผสานเทคนิคตะวันออกและตะวันตก</p>
                            
                            <div class="mb-3">
                                <h6 style="color: var(--primary-gold); text-transform: uppercase; letter-spacing: 1px;">Benefits:</h6>
                                <ul style="color: var(--dark-gray); line-height: 1.8; font-size: 0.9rem;">
                                    <li>เทคนิคพิเศษเฉพาะของร้าน</li>
                                    <li>ใช้หินภูเขาไฟนำเข้าจากญี่ปุ่น</li>
                                    <li>ผสมผสานกับน้ำมันอโรม่า</li>
                                    <li>ประสบการณ์ผ่อนคลายระดับพรีเมียม</li>
                                </ul>
                            </div>
                            
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="badge" style="background: var(--primary-gold); color: white; padding: 8px 15px;">120 minutes</span>
                                <a href="#" class="btn-luxury" style="padding: 10px 25px; font-size: 0.8rem;" onclick="showPage('booking')">จองเลย</a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6 mb-4">
                    <div class="booking-service-card">
                        <div class="service-image">
                            <div class="price-badge">฿3,500</div>
                            <i class="fas fa-crown"></i>
                        </div>
                        <div class="p-4">
                            <h4 class="font-serif mb-3" style="color: var(--charcoal);">Royal Treatment</h4>
                            <p class="mb-4" style="color: var(--dark-gray);">แพ็คเกจพิเศษ 3 ชั่วโมง รวมนวด สครับ ทรีทเมนต์ พร้อมอาหารว่างและเครื่องดื่ม</p>
                            
                            <div class="mb-3">
                                <h6 style="color: var(--primary-gold); text-transform: uppercase; letter-spacing: 1px;">Includes:</h6>
                                <ul style="color: var(--dark-gray); line-height: 1.8; font-size: 0.9rem;">
                                    <li>นวดผ่อนคลายแบบเต็มตัว</li>
                                    <li>สครับผิวด้วยเกลือทะเล</li>
                                    <li>ทรีทเมนต์หน้าด้วยมาส์กธรรมชาติ</li>
                                    <li>อาหารว่างสุขภาพและเครื่องดื่ม</li>
                                </ul>
                            </div>
                            
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="badge" style="background: var(--primary-gold); color: white; padding: 8px 15px;">180 minutes</span>
                                <a href="#" class="btn-luxury" style="padding: 10px 25px; font-size: 0.8rem;" onclick="showPage('booking')">จองเลย</a>
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

    <!-- Enhanced Booking Page -->
    <div id="booking" class="page-section" style="padding-top: 120px;">
        <div class="container">
            <h2 class="section-title font-serif">Book Your Appointment</h2>
            <p class="section-subtitle">เลือกบริการ วันที่ เวลา และผู้ให้บริการที่คุณต้องการ</p>
            
            <!-- Step Indicator -->
            <div class="row mb-5">
                <div class="col-12">
                    <div class="d-flex justify-content-center">
                        <div class="booking-steps d-flex align-items-center">
                            <div class="step active" id="step1">
                                <div class="step-circle">1</div>
                                <div class="step-label">เลือกบริการ</div>
                            </div>
                            <div class="step-line"></div>
                            <div class="step" id="step2">
                                <div class="step-circle">2</div>
                                <div class="step-label">เลือกวันเวลา</div>
                            </div>
                            <div class="step-line"></div>
                            <div class="step" id="step3">
                                <div class="step-circle">3</div>
                                <div class="step-label">ข้อมูลส่วนตัว</div>
                            </div>
                            <div class="step-line"></div>
                            <div class="step" id="step4">
                                <div class="step-circle">4</div>
                                <div class="step-label">ชำระเงิน</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Step 1: Service Selection -->
            <div id="bookingStep1" class="booking-step">
                <h3 class="font-serif mb-4" style="color: var(--charcoal); text-align: center;">เลือกบริการที่ต้องการ</h3>
                
                <div class="row">
                    <div class="col-lg-4 mb-4">
                        <div class="booking-service-card" onclick="selectService('royal-3500', 'Royal Treatment', 3500, 180)">
                            <div class="service-image">
                                <div class="price-badge">฿3,500</div>
                                <i class="fas fa-crown"></i>
                            </div>
                            <div class="p-4">
                                <h5 class="font-serif">Royal Treatment</h5>
                                <p style="font-size: 0.9rem; color: var(--dark-gray);">แพ็คเกจพิเศษครบครัน 3 ชั่วโมง</p>
                                <div class="time-slots">
                                    <span class="time-slot">180 นาที</span>
                                    <span class="time-slot">฿3,500</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="text-center mt-4">
                    <button class="btn-luxury" onclick="nextStep(2)" id="nextToStep2" style="display: none;">
                        <i class="fas fa-arrow-right me-2"></i>ต่อไป: เลือกวันเวลา
                    </button>
                </div>
            </div>

            <!-- Step 2: Date & Time Selection with Staff -->
            <div id="bookingStep2" class="booking-step" style="display: none;">
                <h3 class="font-serif mb-4" style="color: var(--charcoal); text-align: center;">เลือกวันที่ เวลา และผู้ให้บริการ</h3>
                
                <div class="row">
                    <!-- Calendar -->
                    <div class="col-lg-6 mb-4">
                        <div class="calendar">
                            <div class="calendar-header">
                                <button class="calendar-nav" onclick="changeMonth(-1)">
                                    <i class="fas fa-chevron-left"></i>
                                </button>
                                <h4 class="font-serif" id="currentMonth" style="color: var(--charcoal);"></h4>
                                <button class="calendar-nav" onclick="changeMonth(1)">
                                    <i class="fas fa-chevron-right"></i>
                                </button>
                            </div>
                            
                            <div class="calendar-grid" id="calendarGrid">
                                <!-- Calendar days will be generated by JavaScript -->
                            </div>
                        </div>
                    </div>

                    <!-- Time Slots & Staff -->
                    <div class="col-lg-6 mb-4">
                        <!-- Time Slots -->
                        <div class="service-card mb-4">
                            <h5 class="font-serif mb-3" style="color: var(--charcoal);">เลือกเวลา</h5>
                            <div class="time-slots" id="timeSlots">
                                <div class="time-slot" onclick="selectTime('09:00')">09:00</div>
                                <div class="time-slot" onclick="selectTime('10:00')">10:00</div>
                                <div class="time-slot" onclick="selectTime('11:00')">11:00</div>
                                <div class="time-slot" onclick="selectTime('13:00')">13:00</div>
                                <div class="time-slot" onclick="selectTime('14:00')">14:00</div>
                                <div class="time-slot" onclick="selectTime('15:00')">15:00</div>
                                <div class="time-slot" onclick="selectTime('16:00')">16:00</div>
                                <div class="time-slot" onclick="selectTime('17:00')">17:00</div>
                                <div class="time-slot" onclick="selectTime('18:00')">18:00</div>
                            </div>
                        </div>

                        <!-- Staff Selection -->
                        <div class="service-card">
                            <h5 class="font-serif mb-3" style="color: var(--charcoal);">เลือกผู้ให้บริการ</h5>
                            <div class="row">
                                <div class="col-6 mb-3">
                                    <div class="staff-card" onclick="selectStaff('niramol', 'คุณนิรมล')">
                                        <div class="staff-avatar">
                                            <i class="fas fa-crown"></i>
                                        </div>
                                        <h6 style="color: var(--charcoal); margin-bottom: 5px;">คุณนิรมล</h6>
                                        <small style="color: var(--primary-gold);">Master Therapist</small>
                                        <div class="mt-2">
                                            <i class="fas fa-star" style="color: gold;"></i>
                                            <i class="fas fa-star" style="color: gold;"></i>
                                            <i class="fas fa-star" style="color: gold;"></i>
                                            <i class="fas fa-star" style="color: gold;"></i>
                                            <i class="fas fa-star" style="color: gold;"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6 mb-3">
                                    <div class="staff-card" onclick="selectStaff('somjai', 'คุณสมใจ')">
                                        <div class="staff-avatar">
                                            <i class="fas fa-hands"></i>
                                        </div>
                                        <h6 style="color: var(--charcoal); margin-bottom: 5px;">คุณสมใจ</h6>
                                        <small style="color: var(--primary-gold);">Senior Therapist</small>
                                        <div class="mt-2">
                                            <i class="fas fa-star" style="color: gold;"></i>
                                            <i class="fas fa-star" style="color: gold;"></i>
                                            <i class="fas fa-star" style="color: gold;"></i>
                                            <i class="fas fa-star" style="color: gold;"></i>
                                            <i class="fas fa-star" style="color: #ddd;"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6 mb-3">
                                    <div class="staff-card" onclick="selectStaff('wanpen', 'คุณวันเพ็ญ')">
                                        <div class="staff-avatar">
                                            <i class="fas fa-leaf"></i>
                                        </div>
                                        <h6 style="color: var(--charcoal); margin-bottom: 5px;">คุณวันเพ็ญ</h6>
                                        <small style="color: var(--primary-gold);">Herbal Specialist</small>
                                        <div class="mt-2">
                                            <i class="fas fa-star" style="color: gold;"></i>
                                            <i class="fas fa-star" style="color: gold;"></i>
                                            <i class="fas fa-star" style="color: gold;"></i>
                                            <i class="fas fa-star" style="color: gold;"></i>
                                            <i class="fas fa-star" style="color: gold;"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6 mb-3">
                                    <div class="staff-card" onclick="selectStaff('any', 'ไม่ระบุ')">
                                        <div class="staff-avatar">
                                            <i class="fas fa-question"></i>
                                        </div>
                                        <h6 style="color: var(--charcoal); margin-bottom: 5px;">ไม่ระบุ</h6>
                                        <small style="color: var(--dark-gray);">ให้ระบบเลือกให้</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="text-center mt-4">
                    <button class="btn btn-outline-secondary me-3" onclick="prevStep(1)">
                        <i class="fas fa-arrow-left me-2"></i>ย้อนกลับ
                    </button>
                    <button class="btn-luxury" onclick="nextStep(3)" id="nextToStep3" style="display: none;">
                        <i class="fas fa-arrow-right me-2"></i>ต่อไป: กรอกข้อมูล
                    </button>
                </div>
            </div>

            <!-- Step 3: Personal Information -->
            <div id="bookingStep3" class="booking-step" style="display: none;">
                <h3 class="font-serif mb-4" style="color: var(--charcoal); text-align: center;">กรอกข้อมูลส่วนตัว</h3>
                
                <div class="row justify-content-center">
                    <div class="col-lg-8">
                        <div class="service-card">
                            <form id="bookingForm">
                                <div class="row">
                                    <div class="col-md-6 mb-4">
                                        <label for="customerName" class="form-label">ชื่อ-นามสกุล *</label>
                                        <input type="text" class="form-control" id="customerName" required placeholder="กรอกชื่อ-นามสกุล">
                                    </div>
                                    <div class="col-md-6 mb-4">
                                        <label for="customerPhone" class="form-label">เบอร์โทรศัพท์ *</label>
                                        <input type="tel" class="form-control" id="customerPhone" required placeholder="กรอกเบอร์โทรศัพท์">
                                    </div>
                                </div>
                                
                                <div class="mb-4">
                                    <label for="customerEmail" class="form-label">อีเมล</label>
                                    <input type="email" class="form-control" id="customerEmail" placeholder="กรอกอีเมล (ไม่บังคับ)">
                                </div>
                                
                                <div class="mb-4">
                                    <label for="specialRequest" class="form-label">ความต้องการพิเศษ</label>
                                    <textarea class="form-control" id="specialRequest" rows="4" placeholder="ข้อมูลเพิ่มเติม หรือความต้องการพิเศษ (ไม่บังคับ)"></textarea>
                                </div>

                                <!-- Booking Summary -->
                                <div class="payment-summary">
                                    <h5 class="font-serif mb-3" style="color: var(--charcoal);">สรุปการจอง</h5>
                                    <div id="bookingSummary">
                                        <!-- Summary will be populated by JavaScript -->
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="text-center mt-4">
                    <button class="btn btn-outline-secondary me-3" onclick="prevStep(2)">
                        <i class="fas fa-arrow-left me-2"></i>ย้อนกลับ
                    </button>
                    <button class="btn-luxury" onclick="nextStep(4)">
                        <i class="fas fa-arrow-right me-2"></i>ต่อไป: ชำระเงิน
                    </button>
                </div>
            </div>

            <!-- Step 4: Payment -->
            <div id="bookingStep4" class="booking-step" style="display: none;">
                <h3 class="font-serif mb-4" style="color: var(--charcoal); text-align: center;">ชำระเงิน</h3>
                
                <div class="row">
                    <div class="col-lg-8 mb-4">
                        <!-- Payment Methods -->
                        <div class="service-card mb-4">
                            <h5 class="font-serif mb-3" style="color: var(--charcoal);">เลือกช่องทางการชำระเงิน</h5>
                            
                            <div class="payment-method" onclick="selectPaymentMethod('bank')">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-university" style="font-size: 2rem; color: var(--primary-gold); margin-right: 20px;"></i>
                                    <div>
                                        <h6 style="color: var(--charcoal); margin-bottom: 5px;">โอนเงินผ่านธนาคาร</h6>
                                        <p style="color: var(--dark-gray); margin: 0; font-size: 0.9rem;">โอนเงินและอัพโหลดสลิป</p>
                                    </div>
                                </div>
                            </div>

                            <div class="payment-method" onclick="selectPaymentMethod('promptpay')">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-qrcode" style="font-size: 2rem; color: var(--primary-gold); margin-right: 20px;"></i>
                                    <div>
                                        <h6 style="color: var(--charcoal); margin-bottom: 5px;">PromptPay</h6>
                                        <p style="color: var(--dark-gray); margin: 0; font-size: 0.9rem;">สแกน QR Code เพื่อชำระเงิน</p>
                                    </div>
                                </div>
                            </div>

                            <div class="payment-method" onclick="selectPaymentMethod('cash')">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-money-bill-wave" style="font-size: 2rem; color: var(--primary-gold); margin-right: 20px;"></i>
                                    <div>
                                        <h6 style="color: var(--charcoal); margin-bottom: 5px;">ชำระเงินสดหน้าร้าน</h6>
                                        <p style="color: var(--dark-gray); margin: 0; font-size: 0.9rem;">ชำระเงินสดเมื่อมาใช้บริการ</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Bank Transfer Details -->
                        <div id="bankDetails" class="service-card" style="display: none;">
                            <h5 class="font-serif mb-3" style="color: var(--charcoal);">ข้อมูลการโอนเงิน</h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="p-3" style="background: var(--light-gray); border-radius: 10px;">
                                        <h6 style="color: var(--primary-gold);">ธนาคารกสิกรไทย</h6>
                                        <p style="margin: 0;"><strong>เลขที่บัญชี:</strong> 123-4-56789-0</p>
                                        <p style="margin: 0;"><strong>ชื่อบัญชี:</strong> Pure Serenity Spa</p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="p-3" style="background: var(--light-gray); border-radius: 10px;">
                                        <h6 style="color: var(--primary-gold);">ธนาคารไทยพาณิชย์</h6>
                                        <p style="margin: 0;"><strong>เลขที่บัญชี:</strong> 987-6-54321-0</p>
                                        <p style="margin: 0;"><strong>ชื่อบัญชี:</strong> Pure Serenity Spa</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mt-4">
                                <label class="form-label">อัพโหลดสลิปการโอนเงิน *</label>
                                <div class="slip-upload" onclick="document.getElementById('slipFile').click()">
                                    <i class="fas fa-cloud-upload-alt" style="font-size: 3rem; color: var(--primary-gold); margin-bottom: 15px;"></i>
                                    <p style="color: var(--dark-gray); margin: 0;">คลิกเพื่อเลือกไฟล์สลิป</p>
                                    <small style="color: var(--dark-gray);">รองรับไฟล์ JPG, PNG, PDF</small>
                                </div>
                                <input type="file" id="slipFile" accept="image/*,.pdf" style="display: none;" onchange="handleSlipUpload(this)">
                                <div id="slipPreview" style="display: none; margin-top: 15px;"></div>
                            </div>
                        </div>

                        <!-- PromptPay QR -->
                        <div id="promptpayDetails" class="service-card text-center" style="display: none;">
                            <h5 class="font-serif mb-3" style="color: var(--charcoal);">สแกน QR Code เพื่อชำระเงิน</h5>
                            <div class="mb-4">
                                <div style="width: 200px; height: 200px; background: var(--light-gray); margin: 0 auto; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-qrcode" style="font-size: 4rem; color: var(--primary-gold);"></i>
                                </div>
                                <p class="mt-3" style="color: var(--dark-gray);">PromptPay: 098-765-4321</p>
                                <p style="color: var(--primary-gold); font-weight: bold; font-size: 1.2rem;" id="promptpayAmount">฿0</p>
                            </div>
                            
                            <div class="text-start">
                                <label class="form-label">อัพโหลดหลักฐานการชำระเงิน *</label>
                                <div class="slip-upload" onclick="document.getElementById('promptpaySlip').click()">
                                    <i class="fas fa-cloud-upload-alt" style="font-size: 3rem; color: var(--primary-gold); margin-bottom: 15px;"></i>
                                    <p style="color: var(--dark-gray); margin: 0;">คลิกเพื่อเลือกไฟล์หลักฐาน</p>
                                </div>
                                <input type="file" id="promptpaySlip" accept="image/*" style="display: none;" onchange="handleSlipUpload(this)">
                            </div>
                        </div>
                    </div>

                    <!-- Payment Summary -->
                    <div class="col-lg-4">
                        <div class="payment-summary">
                            <h5 class="font-serif mb-3" style="color: var(--charcoal);">สรุปการชำระเงิน</h5>
                            <div id="paymentSummary">
                                <!-- Payment summary will be populated by JavaScript -->
                            </div>
                            
                            <div style="border-top: 2px solid var(--primary-gold); padding-top: 20px; margin-top: 20px;">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h5 style="color: var(--charcoal); margin: 0;">ยอดชำระทั้งหมด</h5>
                                    <h4 style="color: var(--primary-gold); margin: 0;" id="totalAmount">฿0</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="text-center mt-4">
                    <button class="btn btn-outline-secondary me-3" onclick="prevStep(3)">
                        <i class="fas fa-arrow-left me-2"></i>ย้อนกลับ
                    </button>
                    <button class="btn-luxury" onclick="confirmBooking()" id="confirmBookingBtn">
                        <i class="fas fa-check me-2"></i>ยืนยันการจอง
                    </button>
                </div>
            </div>

            <!-- Booking Success -->
            <div id="bookingSuccess" class="text-center" style="display: none; padding: 60px 20px;">
                <i class="fas fa-check-circle" style="font-size: 5rem; color: var(--primary-gold); margin-bottom: 30px;"></i>
                <h3 class="font-serif" style="color: var(--charcoal); margin-bottom: 20px;">การจองสำเร็จ!</h3>
                <p style="color: var(--dark-gray); font-size: 1.1rem; margin-bottom: 30px;">
                    เราได้รับการจองของคุณแล้ว ทางเราจะติดต่อกลับเพื่อยืนยันนัดหมายในเร็วๆ นี้
                </p>
                <div class="service-card" style="max-width: 500px; margin: 0 auto;">
                    <h5 class="font-serif mb-3">รหัสการจอง</h5>
                    <div style="background: var(--light-gray); padding: 20px; border-radius: 10px; margin-bottom: 20px;">
                        <h4 style="color: var(--primary-gold); margin: 0; letter-spacing: 2px;" id="bookingCode">PSPA-2024-001</h4>
                    </div>
                    <p style="color: var(--dark-gray); font-size: 0.9rem;">
                        กรุณาเก็บรหัสนี้ไว้สำหรับอ้างอิงการจอง
                    </p>
                </div>
                <div class="mt-4">
                    <button class="btn-luxury me-3" onclick="showPage('home')">
                        <i class="fas fa-home me-2"></i>กลับหน้าหลัก
                    </button>
                    <button class="btn btn-outline-secondary" onclick="showPage('bookings')" id="viewBookingsBtn" style="display: none;">
                        <i class="fas fa-calendar me-2"></i>ดูการจองของฉัน
                    </button>
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
                    <div style="background: linear-gradient(135deg, var(--charcoal), var(--dark-gray)); color: white; border-radius: 15px; padding: 60px 40px; text-align: center; position: relative; overflow: hidden;">
                        <div style="position: absolute; top: -10px; right: -10px; background: #ff4757; color: white; padding: 10px 20px; border-radius: 20px; font-size: 0.8rem; font-weight: bold; transform: rotate(15deg);">Limited Time</div>
                        <h3 class="font-serif">First Visit Special</h3>
                        <p class="mb-4">สำหรับลูกค้าใหม่ รับส่วนลด 30% สำหรับการนวดครั้งแรก</p>
                        <div style="font-size: 2.5rem; color: var(--primary-gold); margin: 20px 0;">30% OFF</div>
                        <a href="#" class="btn btn-outline-light" onclick="showPage('booking')">Book Now</a>
                    </div>
                </div>

                <div class="col-lg-4 mb-4">
                    <div style="background: linear-gradient(135deg, var(--charcoal), var(--dark-gray)); color: white; border-radius: 15px; padding: 60px 40px; text-align: center; position: relative; overflow: hidden;">
                        <div style="position: absolute; top: -10px; right: -10px; background: var(--primary-gold); color: white; padding: 10px 20px; border-radius: 20px; font-size: 0.8rem; font-weight: bold; transform: rotate(15deg);">Best Value</div>
                        <h3 class="font-serif">Couple Package</h3>
                        <p class="mb-4">แพ็คเกจคู่รัก นวดสำหรับ 2 ท่าน พร้อมเครื่องดื่มต้อนรับ</p>
                        <div style="font-size: 2rem; color: var(--primary-gold); margin: 20px 0;">฿2,500</div>
                        <small style="text-decoration: line-through; opacity: 0.7;">ราคาปกติ ฿3,200</small><br>
                        <a href="#" class="btn btn-outline-light mt-2" onclick="showPage('booking')">Book Now</a>
                    </div>
                </div>

                <div class="col-lg-4 mb-4">
                    <div style="background: linear-gradient(135deg, var(--charcoal), var(--dark-gray)); color: white; border-radius: 15px; padding: 60px 40px; text-align: center; position: relative; overflow: hidden;">
                        <div style="position: absolute; top: -10px; right: -10px; background: #8e44ad; color: white; padding: 10px 20px; border-radius: 20px; font-size: 0.8rem; font-weight: bold; transform: rotate(15deg);">Premium</div>
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
                    <div style="background: white; padding: 40px; border-left: 4px solid var(--primary-gold); border-radius: 15px; box-shadow: 0 15px 50px rgba(0,0,0,0.08);">
                        <h4 class="font-serif mb-3" style="color: var(--charcoal);">Birthday Special</h4>
                        <p style="color: var(--dark-gray); line-height: 1.8;">ฉลองวันเกิดของคุณด้วยการนวดพิเศษ รับส่วนลด 25% ในช่วงเดือนเกิด (ต้องแสดงบัตรประชาชน)</p>
                        <div class="mt-3">
                            <span style="color: var(--primary-gold); font-weight: 500;">Valid:</span>
                            <span style="color: var(--dark-gray);">ตลอดเดือนเกิด</span>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6 mb-4">
                    <div style="background: white; padding: 40px; border-left: 4px solid var(--primary-gold); border-radius: 15px; box-shadow: 0 15px 50px rgba(0,0,0,0.08);">
                        <h4 class="font-serif mb-3" style="color: var(--charcoal);">Referral Rewards</h4>
                        <p style="color: var(--dark-gray); line-height: 1.8;">แนะนำเพื่อนมาใช้บริการ รับส่วนลด 15% สำหรับทั้งคุณและเพื่อน เมื่อเพื่อนจองครั้งแรก</p>
                        <div class="mt-3">
                            <span style="color: var(--primary-gold); font-weight: 500;">Reward:</span>
                            <span style="color: var(--dark-gray);">15% Off สำหรับทั้งคู่</span>
                        </div>
                    </div>
                </div>
            </div>
        </divcard" onclick="selectService('thai-800', 'นวดไทยโบราณ', 800, 60)">
                            <div class="service-image">
                                <div class="price-badge">฿800</div>
                                <i class="fas fa-hands"></i>
                            </div>
                            <div class="p-4">
                                <h5 class="font-serif">นวดไทยโบราณ</h5>
                                <p style="font-size: 0.9rem; color: var(--dark-gray);">การนวดแบบดั้งเดิมของไทย</p>
                                <div class="time-slots">
                                    <span class="time-slot">60 นาที</span>
                                    <span class="time-slot">฿800</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4 mb-4">
                        <div class="booking-service-card" onclick="selectService('aroma-1200', 'นวดน้ำมันอโรม่า', 1200, 90)">
                            <div class="service-image">
                                <div class="price-badge">฿1,200</div>
                                <i class="fas fa-tint"></i>
                            </div>
                            <div class="p-4">
                                <h5 class="font-serif">นวดน้ำมันอโรม่า</h5>
                                <p style="font-size: 0.9rem; color: var(--dark-gray);">การนวดด้วยน้ำมันหอมระเหย</p>
                                <div class="time-slots">
                                    <span class="time-slot">90 นาที</span>
                                    <span class="time-slot">฿1,200</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4 mb-4">
                        <div class="booking-service-card" onclick="selectService('hotstone-1000', 'นวดหินร้อน', 1000, 75)">
                            <div class="service-image">
                                <div class="price-badge">฿1,000</div>
                                <i class="fas fa-fire"></i>
                            </div>
                            <div class="p-4">
                                <h5 class="font-serif">นวดหินร้อน</h5>
                                <p style="font-size: 0.9rem; color: var(--dark-gray);">การนวดด้วยหินภูเขาไฟร้อน</p>
                                <div class="time-slots">
                                    <span class="time-slot">75 นาที</span>
                                    <span class="time-slot">฿1,000</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4 mb-4">
                        <div class="booking-service-card" onclick="selectService('herbal-1500', 'นวดสมุนไพรไทย', 1500, 90)">
                            <div class="service-image">
                                <div class="price-badge">฿1,500</div>
                                <i class="fas fa-leaf"></i>
                            </div>
                            <div class="p-4">
                                <h5 class="font-serif">นวดสมุนไพรไทย</h5>
                                <p style="font-size: 0.9rem; color: var(--dark-gray);">การนวดด้วยถุงสมุนไพรไทย</p>
                                <div class="time-slots">
                                    <span class="time-slot">90 นาที</span>
                                    <span class="time-slot">฿1,500</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4 mb-4">
                        <div class="booking-service-card" onclick="selectService('signature-1800', 'Signature Hot Stone', 1800, 120)">
                            <div class="service-image">
                                <div class="price-badge">฿1,800</div>
                                <i class="fas fa-gem"></i>
                            </div>
                            <div class="p-4">
                                <h5 class="font-serif">Signature Hot Stone</h5>
                                <p style="font-size: 0.9rem; color: var(--dark-gray);">นวดหินร้อนสูตรเฉพาะของเรา</p>
                                <div class="time-slots">
                                    <span class="time-slot">120 นาที</span>
                                    <span class="time-slot">฿1,800</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4 mb-4">
                        <div class="booking-service-