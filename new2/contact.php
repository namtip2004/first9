<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pure Serenity Spa - Contact</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Thai:wght@300;400;500;600&family=Playfair+Display:wght@300;400;500;600;700&family=Crimson+Text:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <!-- Include Navigation -->
    <?php include("navbar.php"); ?>

    <!-- CONTACT PAGE -->
    <div id="contact" class="page-section active" style="padding-top: 120px; background: linear-gradient(135deg, var(--soft-cream), var(--warm-beige));">
        <div class="container">
            <div class="section-header fade-in">
                <!-- <div class="section-subtitle">Get In Touch</div> -->
                <h2 class="section-title font-display">Contact Our Spa</h2>
                <!-- <p class="section-description">
                    ติดต่อเราเพื่อสอบถามข้อมูลเพิ่มเติม หรือปรึกษาการรักษาที่เหมาะสมกับคุณ
                </p> -->
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
            <!-- <div class="row mt-5">
                <div class="col-lg-4 mb-4 fade-in" style="animation-delay: 0.3s;">
                    <div style="background: white; padding: 40px; text-align: center; border: 1px solid rgba(201, 169, 110, 0.2);">
                        <div style="width: 80px; height: 80px; background: linear-gradient(135deg, var(--luxury-gold), var(--deep-burgundy)); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; color: white; font-size: 2rem;">
                            <i class="fas fa-car"></i>
                        </div>
                        <h5 class="font-display mb-3">Valet Parking</h5>
                        <p style="color: var(--deep-brown);">บริการรับ-ส่งรถฟรี และที่จอดรถสำหรับลูกค้า VIP</p>
                    </div>
                </div> -->

                <!-- <div class="col-lg-4 mb-4 fade-in" style="animation-delay: 0.4s;">
                    <div style="background: white; padding: 40px; text-align: center; border: 1px solid rgba(201, 169, 110, 0.2);">
                        <div style="width: 80px; height: 80px; background: linear-gradient(135deg, var(--luxury-gold), var(--deep-burgundy)); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; color: white; font-size: 2rem;">
                            <i class="fas fa-gift"></i>
                        </div>
                        <h5 class="font-display mb-3">Gift Certificates</h5>
                        <p style="color: var(--deep-brown);">ซื้อของขวัญสปาสำหรับคนพิเศษ พร้อมบรรจุภัณฑ์หรูหรา</p>
                    </div>
                </div> -->

                <!-- <div class="col-lg-4 mb-4 fade-in" style="animation-delay: 0.5s;">
                    <div style="background: white; padding: 40px; text-align: center; border: 1px solid rgba(201, 169, 110, 0.2);">
                        <div style="width: 80px; height: 80px; background: linear-gradient(135deg, var(--luxury-gold), var(--deep-burgundy)); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; color: white; font-size: 2rem;">
                            <i class="fas fa-concierge-bell"></i>
                        </div>
                        <h5 class="font-display mb-3">Concierge Service</h5>
                        <p style="color: var(--deep-brown);">บริการคอนเซียร์จ 24/7 สำหรับข้อมูลและการจองทุกความต้องการ</p>
                    </div>
                </div> -->
            </div>
        </div>
    </div>

    <!-- Include Footer -->
    <?php include("footer.php"); ?>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="scripts.js"></script>
</body>
</html>