
/**
 * Pure Serenity Spa - JavaScript Functionality
 * Handles multi-page navigation, form submissions, and animations
 */

// Booking Price Update
function updateBookingPrice() {
    const select = document.getElementById('treatmentType');
    const summary = document.getElementById('bookingSummary');
    const details = document.getElementById('summaryDetails');
    
    if (select && select.value) {
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
                Includes a welcome refreshment and premium skincare amenities.
            </div>
        `;
    } else {
        summary.style.display = 'none';
    }
}

// Booking Form Submission
function submitBooking(event) {
    event.preventDefault();
    
    const form = document.getElementById('bookingForm');
    const loading = document.getElementById('loadingSpinner');
    const success = document.getElementById('bookingSuccess');
    
    // Validate required fields
    const name = document.getElementById('customerName').value.trim();
    const phone = document.getElementById('customerPhone').value.trim();
    const date = document.getElementById('bookingDate').value;
    const time = document.getElementById('bookingTime').value;
    const treatment = document.getElementById('treatmentType').value;
    
    if (!name || !phone || !date || !time || !treatment) {
        alert('Please complete all required fields.');
        return;
    }
    
    // Phone number validation (9-10 digits for Thai numbers)
    const phoneRegex = /^[0-9]{9,10}$/;
    if (!phoneRegex.test(phone)) {
        alert('Please enter a valid phone number (9-10 digits).');
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

// Contact Form Submission
function submitContact(event) {
    event.preventDefault();
    
    const form = document.getElementById('contactForm');
    const success = document.getElementById('contactSuccess');
    
    // Validate required fields
    const name = document.getElementById('contactName').value.trim();
    const phone = document.getElementById('contactPhone').value.trim();
    const message = document.getElementById('contactMessage').value.trim();
    
    if (!name || !phone || !message) {
        alert('Please complete all required fields.');
        return;
    }
    
    // Phone number validation (9-10 digits for Thai numbers)
    const phoneRegex = /^[0-9]{9,10}$/;
    if (!phoneRegex.test(phone)) {
        alert('Please enter a valid phone number (9-10 digits).');
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

// Initialization
document.addEventListener('DOMContentLoaded', function() {
    // Load navbar and footer
    // fetch('navbar.php')
    //     .then(response => response.text())
    //     .then(data => {
    //         document.getElementById('navbar-placeholder').innerHTML = data;
            
    //         // Highlight active nav link based on current page
    //         const currentPath = window.location.pathname.split('/').pop() || 'index.php';
    //         document.querySelectorAll('.navbar-nav .nav-link').forEach(link => {
    //             if (link.getAttribute('href') === currentPath) {
    //                 link.classList.add('active');
    //             }
    //         });
    //     })
    //     .catch(error => console.error('Error loading navbar:', error));
    
    // fetch('footer.php')
    //     .then(response => response.text())
    //     .then(data => {
    //         document.getElementById('footer-placeholder').innerHTML = data;
    //     })
    //     .catch(error => console.error('Error loading footer:', error));

    // Set date constraints for booking
    const dateInput = document.getElementById('bookingDate');
    if (dateInput) {
        const today = new Date().toISOString().split('T')[0];
        dateInput.setAttribute('min', today);
        
        const maxDate = new Date();
        maxDate.setMonth(maxDate.getMonth() + 3);
        dateInput.setAttribute('max', maxDate.toISOString().split('T')[0]);
    }

    // Scroll animations
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.animationPlayState = 'running';
                entry.target.style.opacity = '1';
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);

    document.querySelectorAll('.fade-in, .slide-in-left').forEach(el => {
        observer.observe(el);
    });

    // Add event listener for treatment selection
    const treatmentSelect = document.getElementById('treatmentType');
    if (treatmentSelect) {
        treatmentSelect.addEventListener('change', updateBookingPrice);
    }
});


