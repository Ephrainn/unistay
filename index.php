<?php
require_once 'includes/auth.php';
if (isLoggedIn()) { header('Location: home.php'); exit; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UniStay – Student Housing Made Simple</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/landing2.css">
</head>
<body>

<!-- NAV -->
<nav class="nav" id="nav">
    <div class="nav-inner">
        <a href="index.php" class="nav-logo">
            <div class="logo-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5">
                    <path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
                    <polyline points="9 22 9 12 15 12 15 22"/>
                </svg>
            </div>
            UniStay
        </a>
        <div class="nav-links">
            <a href="#features">Features</a>
            <a href="#how">How it works</a>
            <a href="login.php">Sign In</a>
            <a href="register.php" class="nav-cta">Get Started →</a>
        </div>
        <button class="burger" id="burger" aria-label="Menu">
            <span></span><span></span><span></span>
        </button>
    </div>
    <div class="mobile-nav" id="mobileNav">
        <a href="#features">Features</a>
        <a href="#how">How it works</a>
        <a href="login.php">Sign In</a>
        <a href="register.php" class="nav-cta" style="text-align:center;">Get Started →</a>
    </div>
</nav>

<!-- HERO -->
<section class="hero">
    <div class="hero-inner">
        <div class="hero-left">
            <div class="hero-pill">
                <span class="pill-dot"></span>
                50,000+ students housed
            </div>
            <h1>Student housing,<br><span class="grad">done right.</span></h1>
            <p>Find verified hostels near your campus. Compare prices, check distances, and book in minutes — no agents, no hidden fees.</p>
            <div class="hero-actions">
                <a href="register.php" class="btn-hero-primary">Find Your Space</a>
                <a href="login.php" class="btn-hero-ghost">Sign In</a>
            </div>
            <div class="hero-trust">
                <div class="trust-item">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#22c55e" stroke-width="2.5"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                    Verified listings
                </div>
                <div class="trust-item">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#22c55e" stroke-width="2.5"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                    No booking fees
                </div>
                <div class="trust-item">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#22c55e" stroke-width="2.5"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                    Cancel anytime
                </div>
            </div>
        </div>
        <div class="hero-right">
            <!-- Floating UI mockup -->
            <div class="mockup">
                <div class="mockup-card">
                    <div class="mc-img">
                        <div class="mc-badge">✓ Verified</div>
                        <div class="mc-tag">2 rooms left</div>
                    </div>
                    <div class="mc-body">
                        <div class="mc-row">
                            <div>
                                <div class="mc-name">Tesano Palace Hostel</div>
                                <div class="mc-loc">
                                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
                                    1.2 km · GCTU Tesano
                                </div>
                            </div>
                            <div class="mc-price">GH₵750<span>/sem</span></div>
                        </div>
                        <div class="mc-amenities">
                            <span>WiFi</span><span>Laundry</span><span>Security</span>
                        </div>
                        <div class="mc-footer">
                            <div class="mc-stars">★★★★★ <span>4.9</span></div>
                            <div class="mc-btn">Book Now</div>
                        </div>
                    </div>
                </div>
                <!-- Floating stat cards -->
                <div class="float-card float-1">
                    <div class="fc-icon green">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                    </div>
                    <div>
                        <div class="fc-val">Confirmed!</div>
                        <div class="fc-sub">Booking #00142</div>
                    </div>
                </div>
                <div class="float-card float-2">
                    <div class="fc-icon blue">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                    </div>
                    <div>
                        <div class="fc-val">128 students</div>
                        <div class="fc-sub">searching now</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- STATS -->
<div class="stats-bar">
    <div class="stat"><strong>50,000+</strong><span>Students Housed</span></div>
    <div class="stat-sep"></div>
    <div class="stat"><strong>5,000+</strong><span>Verified Hostels</span></div>
    <div class="stat-sep"></div>
    <div class="stat"><strong>200+</strong><span>Universities</span></div>
    <div class="stat-sep"></div>
    <div class="stat"><strong>4.9 ★</strong><span>Avg Rating</span></div>
</div>

<!-- HOW IT WORKS -->
<section class="section" id="how">
    <div class="section-inner">
        <div class="section-label">How it works</div>
        <h2 class="section-title">Booked in 3 steps</h2>
        <div class="steps">
            <div class="step">
                <div class="step-num">01</div>
                <h3>Search & Filter</h3>
                <p>Enter your university. Filter by price, distance, room type, and amenities to find your match.</p>
            </div>
            <div class="step-arrow">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
            </div>
            <div class="step">
                <div class="step-num">02</div>
                <h3>Verify & Book</h3>
                <p>Upload your student ID, pick your dates, and submit your booking request in minutes.</p>
            </div>
            <div class="step-arrow">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
            </div>
            <div class="step">
                <div class="step-num">03</div>
                <h3>Move In</h3>
                <p>Get instant confirmation, manage payments, and track everything from your dashboard.</p>
            </div>
        </div>
    </div>
</section>

<!-- FEATURES -->
<section class="section features-section" id="features">
    <div class="section-inner">
        <div class="section-label">Why UniStay</div>
        <h2 class="section-title">Everything you need</h2>
        <div class="features-grid">
            <div class="feature-card">
                <div class="feat-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                </div>
                <h3>Verified Properties</h3>
                <p>Every listing is physically inspected for safety, cleanliness, and student suitability.</p>
            </div>
            <div class="feature-card">
                <div class="feat-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                </div>
                <h3>Smart Search</h3>
                <p>Filter by rent, walking distance, room type, and amenities. Find exactly what fits.</p>
            </div>
            <div class="feature-card">
                <div class="feat-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
                </div>
                <h3>Instant Booking</h3>
                <p>Real-time availability. Secure your room the moment you find it.</p>
            </div>
            <div class="feature-card">
                <div class="feat-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
                </div>
                <h3>Easy Payments</h3>
                <p>Pay rent, track deposits, and download invoices — all in one dashboard.</p>
            </div>
            <div class="feature-card">
                <div class="feat-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/></svg>
                </div>
                <h3>Student Verified</h3>
                <p>Every user is verified with a university email for a safe community.</p>
            </div>
            <div class="feature-card">
                <div class="feat-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/></svg>
                </div>
                <h3>24/7 Support</h3>
                <p>Our student support team is available around the clock for any issue.</p>
            </div>
        </div>
    </div>
</section>

<!-- TESTIMONIALS -->
<section class="section testi-section">
    <div class="section-inner">
        <div class="section-label">Student reviews</div>
        <h2 class="section-title">Loved by students</h2>
        <div class="testi-grid">
            <div class="testi-card">
                <div class="testi-stars">★★★★★</div>
                <p>"Found my room in under 10 minutes. The verification process gave me confidence the place was legit. Moved in the same week."</p>
                <div class="testi-author">
                    <div class="testi-avatar">AR</div>
                    <div>
                        <strong>Alex Rivera</strong>
                        <span>Computer Science · Stanford</span>
                    </div>
                </div>
            </div>
            <div class="testi-card">
                <div class="testi-stars">★★★★★</div>
                <p>"The payment dashboard is a game changer. I can see exactly what I've paid, what's due, and download receipts instantly."</p>
                <div class="testi-author">
                    <div class="testi-avatar">JM</div>
                    <div>
                        <strong>Jamie Morgan</strong>
                        <span>Business · MIT</span>
                    </div>
                </div>
            </div>
            <div class="testi-card">
                <div class="testi-stars">★★★★★</div>
                <p>"As an international student I was worried about safe housing. UniStay's verified listings made the whole process stress-free."</p>
                <div class="testi-author">
                    <div class="testi-avatar">PK</div>
                    <div>
                        <strong>Priya Kumar</strong>
                        <span>Medicine · UCL</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA BANNER -->
<section class="cta-section">
    <div class="cta-inner">
        <h2>Ready to find your space?</h2>
        <p>Join 50,000+ students who found their home through UniStay.</p>
        <a href="register.php" class="btn-hero-primary" style="margin-top:8px;">Create Free Account →</a>
        <div class="cta-note">No credit card required · Takes 2 minutes</div>
    </div>
</section>

<!-- FOOTER -->
<footer class="footer">
    <div class="footer-inner">
        <div class="footer-brand">
            <div class="nav-logo" style="margin-bottom:12px;">
                <div class="logo-icon">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                </div>
                UniStay
            </div>
            <p>Simplifying student living through technology and verified proximity data.</p>
        </div>
        <div class="footer-col">
            <h4>Product</h4>
            <a href="register.php">Find Hostels</a>
            <a href="login.php">Sign In</a>
            <a href="register.php">Create Account</a>
        </div>
        <div class="footer-col">
            <h4>Support</h4>
            <a href="#">Safety Guide</a>
            <a href="#">Payment Plans</a>
            <a href="#">Contact Us</a>
            <a href="#">Privacy Policy</a>
        </div>
    </div>
    <div class="footer-copy">© <?= date('Y') ?> UniStay. All rights reserved.</div>
</footer>

<!-- Mobile CTA -->
<div class="mobile-cta">
    <a href="register.php">Find My Space →</a>
</div>

<script>
const nav = document.getElementById('nav');
window.addEventListener('scroll', () => nav.classList.toggle('scrolled', scrollY > 40));
document.getElementById('burger').addEventListener('click', () => {
    document.getElementById('mobileNav').classList.toggle('open');
});
</script>
</body>
</html>
