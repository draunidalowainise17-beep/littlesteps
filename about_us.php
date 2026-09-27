<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us | Little Steps</title>
    <link rel="stylesheet" href="assets/css/about.css">
    <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css" />
</head>

<body>

    <?php include 'header.php'; ?>

    <section id="about-us-section" class="about-wrapper">
        <h1 class="page-title">About Us</h1>
        
        <div class="about-flex-container">
            <div class="about-visuals" data-aos="fade-right" data-aos-offset="300" data-aos-easing="ease-in-sine">
                <img src="assets/media/about-img-1.png" alt="Happy Children">
            </div>
            
            <div class="about-text-content" data-aos="fade-right" data-aos-offset="300" data-aos-easing="ease-in-sine">
                <img src="assets/media/logo.png" alt="Daycare logo graphic">
                <h3>Reliable, Affordable, Flexible, Secure, Budget-Friendly</h3>
                <p>
                    At Little Steps Daycare, we partner with leading and trusted daycare providers to offer dependable, high-quality childcare solutions tailored to your needs. Whether you're seeking flexible schedules, secure environments, or budget-friendly options, Little Steps is here to support working parents with care you can trust. 💕
                </p>
            </div>
        </div>

        <div class="contact-info-grid">
            <div class="contact-card">
                <i class="fas fa-phone"></i>
                <h3>Phone Number</h3>
                <a href="tel:1234567890">123-456-7890</a><br>
                <a href="tel:1112223333">111-222-3333</a>
            </div>
            <div class="contact-card">
                <i class="fas fa-envelope"></i>
                <h3>Email Address</h3>
                <a href="mailto:littlesteps@gmail.com">littlesteps@gmail.com</a><br>
                <a href="mailto:littlesteps@yahoo.com">littlesteps@yahoo.com</a>
            </div>
            <div class="contact-card">
                <i class="fas fa-map-marker-alt"></i>
                <h3>Office Address</h3>
                <address>
                    12345 Main Street, Lot 3<br>
                    Votua Drive,<br>
                    Fiji
                </address>
            </div>
        </div>
    </section>

    <script src="https://unpkg.com/aos@next/dist/aos.js"></script>
    <script>
        AOS.init({
            duration: 1000,
            once: true 
        });
    </script>

</body>
</html>