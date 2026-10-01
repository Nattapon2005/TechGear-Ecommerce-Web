<?php
include 'page/navbar.php';
?>

    <!--========================= Contact Hero ==========================-->
    <section class="contact-hero">
        <div class="container">
            <div class="contact-hero-content">
                <div class="contact-hero-badge">
                    <span class="badge-signal"></span>
                    GET IN TOUCH
                </div>
                <h1 class="contact-hero-title">
                    Let's <span class="gradient-text">Connect</span>
                </h1>
                <p class="contact-hero-desc">
                    Have a question, suggestion, or just want to say hello? 
                    We'd love to hear from you. Our team is ready to help.
                </p>
            </div>
        </div>

        <!-- Animated Background -->
        <div class="contact-hero-bg">
            <div class="circuit-lines"></div>
            <div class="pulse-ring ring-1"></div>
            <div class="pulse-ring ring-2"></div>
            <div class="pulse-ring ring-3"></div>
            <div class="scan-line-contact"></div>
        </div>
    </section>


    <!--========================= Contact Info Cards ==========================-->
    <section class="contact-info-section">
        <div class="container">
            <div class="contact-info-grid">
                <div class="contact-info-card">
                    <div class="info-card-icon">
                        <i class="bx bx-map"></i>
                        <div class="info-icon-ring"></div>
                    </div>
                    <h4>Visit Us</h4>
                    <p>123 Gaming District</p>
                    <p>Bangkok, Thailand 10110</p>
                </div>
                <div class="contact-info-card">
                    <div class="info-card-icon">
                        <i class="bx bx-phone"></i>
                        <div class="info-icon-ring"></div>
                    </div>
                    <h4>Call Us</h4>
                    <p>+66 (0) 2-123-4567</p>
                    <p>Mon-Fri: 9AM - 6PM</p>
                </div>
                <div class="contact-info-card">
                    <div class="info-card-icon">
                        <i class="bx bx-envelope"></i>
                        <div class="info-icon-ring"></div>
                    </div>
                    <h4>Email Us</h4>
                    <p>support@techgear.com</p>
                    <p>sales@techgear.com</p>
                </div>
                <div class="contact-info-card">
                    <div class="info-card-icon">
                        <i class="bx bx-time-five bx-clock"></i>
                        <div class="info-icon-ring"></div>
                    </div>
                    <h4>Business Hours</h4>
                    <p>Mon - Fri: 9:00 - 18:00</p>
                    <p>Sat - Sun: 10:00 - 16:00</p>
                </div>
            </div>
        </div>
    </section>


    <!--========================= Contact Form Section ==========================-->
    <section class="contact-form-section">
        <div class="container">
            <div class="contact-layout">

                <!-- Form Side -->
                <div class="contact-form-wrapper">
                    <div class="form-header">
                        <h2>Send us a <span class="gradient-text">Message</span></h2>
                        <p>Fill out the form below and we'll get back to you within 24 hours.</p>
                    </div>

                    <form class="contact-form" id="contactForm">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="contact-name">
                                    <i class="bx bx-user"></i> Full Name
                                </label>
                                <input type="text" id="contact-name" name="name" placeholder="John Doe" required>
                                <div class="input-focus-line"></div>
                            </div>
                            <div class="form-group">
                                <label for="contact-email">
                                    <i class="bx bx-envelope"></i> Email Address
                                </label>
                                <input type="email" id="contact-email" name="email" placeholder="john@example.com" required>
                                <div class="input-focus-line"></div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="contact-subject">
                                <i class="bx bx-edit"></i> Subject
                            </label>
                            <select id="contact-subject" name="subject" required>
                                <option value="" disabled selected>Select a subject</option>
                                <option value="general">General Inquiry</option>
                                <option value="order">Order Support</option>
                                <option value="product">Product Question</option>
                                <option value="return">Return / Refund</option>
                                <option value="partnership">Business Partnership</option>
                                <option value="other">Other</option>
                            </select>
                            <div class="input-focus-line"></div>
                        </div>

                        <div class="form-group">
                            <label for="contact-message">
                                <i class="bx bx-message-detail"></i> Message
                            </label>
                            <textarea id="contact-message" name="message" rows="5" placeholder="Tell us how we can help..." required></textarea>
                            <div class="input-focus-line"></div>
                        </div>

                        <button type="submit" class="btn-submit" id="submitBtn">
                            <span class="btn-text">Send Message</span>
                            <span class="btn-loading" style="display:none;">
                                <i class="bx bx-loader-alt bx-spin"></i> Sending...
                            </span>
                            <i class="bx bx-send btn-icon"></i>
                        </button>
                    </form>

                    <!-- Success Message -->
                    <div class="form-success" id="formSuccess" style="display:none;">
                        <div class="success-icon">
                            <i class="bx bx-check-circle"></i>
                        </div>
                        <h3>Message Sent!</h3>
                        <p>Thank you for reaching out. We'll get back to you within 24 hours.</p>
                        <button onclick="resetForm()" class="btn-new-message">Send Another Message</button>
                    </div>
                </div>


                <!-- Map / Social Side -->
                <div class="contact-sidebar">
                    <div class="contact-map-card">
                        <div class="map-placeholder">
                            <div class="map-pin">
                                <i class="bx bx-current-location"></i>
                                <div class="pin-pulse"></div>
                            </div>
                            <div class="map-grid-bg"></div>
                            <div class="map-label">Bangkok, Thailand</div>
                        </div>
                    </div>

                    <div class="contact-social-card">
                        <h4>Follow Us</h4>
                        <p>Stay connected for latest updates and deals</p>
                        <div class="social-links">
                            <a href="#" class="social-link" title="Facebook">
                                <i class="bx bxl-facebook"></i>
                            </a>
                            <a href="#" class="social-link" title="Twitter">
                                <i class="bx bxl-twitter"></i>
                            </a>
                            <a href="#" class="social-link" title="Instagram">
                                <i class="bx bxl-instagram"></i>
                            </a>
                            <a href="#" class="social-link" title="Discord">
                                <i class="bx bxl-discord-alt"></i>
                            </a>
                            <a href="#" class="social-link" title="YouTube">
                                <i class="bx bxl-youtube"></i>
                            </a>
                        </div>
                    </div>

                    <div class="contact-faq-card">
                        <h4><i class="bx bx-help-circle"></i> Quick FAQ</h4>
                        <div class="faq-item" onclick="toggleFaq(this)">
                            <div class="faq-question">
                                <span>How long does shipping take?</span>
                                <i class="bx bx-chevron-down"></i>
                            </div>
                            <div class="faq-answer">
                                <p>Standard shipping takes 3-5 business days. Express delivery is available within 24-48 hours.</p>
                            </div>
                        </div>
                        <div class="faq-item" onclick="toggleFaq(this)">
                            <div class="faq-question">
                                <span>What's your return policy?</span>
                                <i class="bx bx-chevron-down"></i>
                            </div>
                            <div class="faq-answer">
                                <p>We offer 30-day hassle-free returns. Items must be in original condition with packaging.</p>
                            </div>
                        </div>
                        <div class="faq-item" onclick="toggleFaq(this)">
                            <div class="faq-question">
                                <span>Do you ship internationally?</span>
                                <i class="bx bx-chevron-down"></i>
                            </div>
                            <div class="faq-answer">
                                <p>Yes! We ship to 25+ countries worldwide. International shipping takes 7-14 business days.</p>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>


<?php include 'page/footer.php'; ?>

<script>
// ===== Form Submission =====
document.getElementById('contactForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const btn = document.getElementById('submitBtn');
    const btnText = btn.querySelector('.btn-text');
    const btnLoading = btn.querySelector('.btn-loading');
    const btnIcon = btn.querySelector('.btn-icon');
    
    // Show loading
    btnText.style.display = 'none';
    btnIcon.style.display = 'none';
    btnLoading.style.display = 'inline-flex';
    btn.disabled = true;
    
    // Simulate submission
    setTimeout(() => {
        document.getElementById('contactForm').style.display = 'none';
        document.querySelector('.form-header').style.display = 'none';
        document.getElementById('formSuccess').style.display = 'flex';
    }, 2000);
});

// ===== Reset Form =====
function resetForm() {
    const form = document.getElementById('contactForm');
    const header = document.querySelector('.form-header');
    const success = document.getElementById('formSuccess');
    const btn = document.getElementById('submitBtn');
    const btnText = btn.querySelector('.btn-text');
    const btnLoading = btn.querySelector('.btn-loading');
    const btnIcon = btn.querySelector('.btn-icon');
    
    form.reset();
    form.style.display = 'block';
    header.style.display = 'block';
    success.style.display = 'none';
    btnText.style.display = 'inline';
    btnIcon.style.display = 'inline';
    btnLoading.style.display = 'none';
    btn.disabled = false;
}

// ===== FAQ Toggle =====
function toggleFaq(item) {
    const isActive = item.classList.contains('active');
    // Close all
    document.querySelectorAll('.faq-item').forEach(i => i.classList.remove('active'));
    // Open clicked if not already open
    if (!isActive) item.classList.add('active');
}

// ===== Scroll Animations =====
const contactObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.classList.add('animate-in');
        }
    });
}, { threshold: 0.1, rootMargin: '0px 0px -50px 0px' });

document.querySelectorAll('.contact-info-card, .contact-form-wrapper, .contact-sidebar, .faq-item').forEach(el => {
    contactObserver.observe(el);
});
</script>
