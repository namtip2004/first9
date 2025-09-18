
        // Enhanced Page Navigation


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

        