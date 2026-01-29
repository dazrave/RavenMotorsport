<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Raven Motorsport | Endurance Karting Team</title>
  <meta name="description" content="Raven Motorsport is an endurance karting team competing in the Daytona 24 Hours at Milton Keynes. Established in 2017, we race with strategy, consistency and camaraderie.">
  <meta name="keywords" content="Raven Motorsport, endurance karting, Daytona 24 Hours, Milton Keynes, racing team">
  <meta name="author" content="Raven Motorsport">
  <meta property="og:title" content="Raven Motorsport | Endurance Karting Team">
  <meta property="og:description" content="Competing in the Daytona 24 Hours since 2017. Mindset. Momentum. Mettle.">
  <meta property="og:image" content="https://res.cloudinary.com/dazrave/image/upload/e_grayscale/v1747483159/192805442_3960694670712677_4965092832512142571_n_h3fksh.jpg">
  <meta property="og:type" content="website">
  <meta property="og:url" content="https://ravenmotorsport.com">
  <link rel="canonical" href="https://ravenmotorsport.com">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script type="application/ld+json">
  {
    "@context": "https://schema.org",
    "@type": "SportsTeam",
    "name": "Raven Motorsport",
    "sport": "Endurance Karting",
    "description": "Endurance karting team competing in the Daytona 24 Hours at Milton Keynes since 2017",
    "url": "https://ravenmotorsport.com",
    "foundingDate": "2017",
    "slogan": "Mindset. Momentum. Mettle."
  }
  </script>
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      background-color: #000;
      color: #fff;
    }
    .hero {
      height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      text-align: center;
      flex-direction: column;
      position: relative;
    }
    .hero-logo {
      max-width: 300px;
      width: 100%;
      height: auto;
      margin-bottom: 1rem;
      opacity: 0;
      animation: fadeIn 2s ease forwards;
    }
    .lead {
      opacity: 0;
      animation: fadeIn 2s ease forwards;
      animation-delay: 1.5s;
    }
    @keyframes fadeIn {
      to {
        opacity: 1;
      }
    }
    .scroll-down {
      position: absolute;
      bottom: 30px;
      left: 50%;
      transform: translateX(-50%);
      font-size: 1.1rem;
      color: #fff;
      font-weight: bold;
      text-align: center;
      opacity: 0;
      animation: fadeIn 1s ease forwards, bounce 2s infinite;
      animation-delay: 4.2s, 0s;
    }
    .scroll-down .arrow {
      color: #8b241d;
      font-size: 1.5rem;
      display: block;
    }
    @keyframes bounce {
      0%, 100% {
        transform: translate(-50%, 0);
      }
      50% {
        transform: translate(-50%, 10px);
      }
    }
    .section {
      padding: 4rem 0;
    }
    .team-member {
      text-align: center;
    }
    .countdown {
      font-size: 2rem;
      font-weight: bold;
      color: #ffc107;
    }
    a.btn-outline-light {
      border-radius: 0;
      border-color: #8b241d;
      color: #fff;
    }
    a.btn-outline-light:hover {
      background-color: #8b241d;
      color: #fff;
    }
    h2 {
      color: #8b241d;
    }
    .fade-in-section {
      opacity: 0;
      transform: translateY(20px);
      transition: opacity 0.8s ease-out, transform 0.8s ease-out;
    }
    .fade-in-section.visible {
      opacity: 1;
      transform: none;
    }
    .fade-word {
      opacity: 0;
      display: inline-block;
      animation: fadeInWord 0.6s ease forwards;
    }
    @keyframes fadeInWord {
      to {
        opacity: 1;
      }
    }
  </style>
</head>
<body>
  <!-- Hero Section -->
  <section class="hero">
  <div class="container">
    <a href="/"><img src="https://res.cloudinary.com/dazrave/image/upload/v1602093800/Raven%20Motorsport/white-text.svg" alt="Raven Motorsport Logo" class="hero-logo"></a>
    <p class="lead">
      <span class="fade-word" style="animation-delay: 1.5s">Mindset.</span>
      <span class="fade-word" style="animation-delay: 2.5s">Momentum.</span>
      <span class="fade-word" style="animation-delay: 3.2s">Mettle.</span>
    </p>
    <div class="scroll-down">
      Scroll down
      <span class="arrow">↓</span>
    </div>
  </div>
</section>

  <!-- Track Image Section -->
<section class="fade-in-section text-center">
  <img src="https://res.cloudinary.com/dazrave/image/upload/e_grayscale/v1747484288/mk-cover_w0ajnu.jpg" alt="Daytona Milton Keynes" class="img-fluid" style="max-width: 1600px; width: 100%; filter: grayscale(100%);">
</section>

  <!-- About Section -->
<section class="section fade-in-section bg-dark text-light">
  <div class="container">
    <h2 class="text-center mb-4">About Us</h2>
    <p class="text-center">Founded in 2017, Raven Motorsport is a committed endurance karting team that races primarily at the Daytona 24 Hours in Milton Keynes. While we’ve yet to reach the podium, our team has welcomed a diverse range of talented drivers over the years. We focus on strategy, teamwork and consistency, proudly competing with determination and good spirit year after year.</p>
  </div>
</section>

<!-- Team Section -->
<section class="section fade-in-section bg-black text-light">
  <div class="container">
    <h2 class="text-center mb-4">2026 Team Lineup</h2>
    <div class="row g-4 justify-content-center">
      <div class="col-md-3 team-member">
        <h5>Tim Hockham</h5>
        <p>Race Manager <span style="color:#fff; opacity: 0.8">(6 races)</span></p>
      </div>
      <div class="col-md-3 team-member">
        <h5>Adrian Herrero Sanchez</h5>
        <p>Race Manager</p>
      </div>
      <div class="col-md-3 team-member">
        <h5>Darren Ravenscroft</h5>
        <p>Team Principal & Driver <span style="color:#fff; opacity: 0.8">(8 races)</span></p>
      </div>
      <div class="col-md-3 team-member">
        <h5>Andy Tait</h5>
        <p>Driver <span style="color:#fff; opacity: 0.8">(7 races)</span></p>
      </div>
      <div class="col-md-3 team-member">
        <h5>Matt Casey</h5>
        <p>Driver <span style="color:#fff; opacity: 0.8">(4 races)</span></p>
      </div>
      <div class="col-md-3 team-member">
        <h5>Dave Parker</h5>
        <p>Driver <span style="color:#fff; opacity: 0.8">(5 races)</span></p>
      </div>
      <div class="col-md-3 team-member">
        <h5>Tomek Zet</h5>
        <p>Driver <span style="color:#fff; opacity: 0.8">(2 races)</span></p>
      </div>
      <div class="col-md-3 team-member">
        <h5>Ryan Welch</h5>
        <p>Driver</p>
      </div>
      <div class="col-md-3 team-member">
        <h5>Luke Gore</h5>
        <p>Driver</p>
      </div>
      <div class="col-md-3 team-member">
        <h5>James Eaton</h5>
        <p>Driver</p>
      </div>
      <div class="col-md-3 team-member">
        <h5>James Addison</h5>
        <p>Driver <span style="color:#fff; opacity: 0.8">(12 races)</span></p>
      </div>
      <div class="col-md-3 team-member">
        <h5>Daniel Lane</h5>
        <p>Driver</p>
      </div>
    </div>
  </div>
</section>

<!-- Countdown Section -->
  <section class="section fade-in-section bg-dark text-light text-center">
    <div class="container">
      <h2 class="mb-3">Next Race: Daytona 24 Hours</h2>
      <div id="countdown" class="countdown"></div>
      <p class="mt-3">Milton Keynes | 23-24 May 2026 | 13:00 to 13:00</p>
    </div>
  </section>

  <!-- Links Section -->
  <section class="section fade-in-section bg-black text-light text-center">
    <div class="container">
      <h2 class="mb-4">Quick Links</h2>
      <a href="/info" class="btn btn-outline-light m-2">2026 Race Info</a>
      <a href="https://speedhive.mylaps.com/livetiming/52F470FC643E52C0-2147483649" class="btn btn-outline-light m-2" target="_blank" rel="noopener">Live Results</a>
      <a href="https://pitwall.ravenmotorsport.com/" class="btn btn-outline-light m-2" target="_blank" rel="noopener">Management</a>
    </div>
  </section>

  <script>
    const raceDate = new Date("May 23, 2026 13:00:00").getTime();
    const countdownEl = document.getElementById("countdown");

    const updateCountdown = () => {
      const now = new Date().getTime();
      const diff = raceDate - now;

      if (diff <= 0) {
        countdownEl.innerHTML = "Race in Progress!";
        return;
      }

      const days = Math.floor(diff / (1000 * 60 * 60 * 24));
      const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
      const mins = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
      const secs = Math.floor((diff % (1000 * 60)) / 1000);

      countdownEl.innerHTML = `${days}d ${hours}h ${mins}m ${secs}s`;
    };

    setInterval(updateCountdown, 1000);
    updateCountdown();

    const observer = new IntersectionObserver(entries => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.classList.add('visible');
          observer.unobserve(entry.target);
        }
      });
    }, { threshold: 0.1 });

    document.querySelectorAll('.fade-in-section').forEach(section => {
      observer.observe(section);
    });
  </script>
  <!-- Footer Image Section -->
<section class="text-center">
  <img src="https://res.cloudinary.com/dazrave/image/upload/e_grayscale/v1747483159/192805442_3960694670712677_4965092832512142571_n_h3fksh.jpg" alt="Raven Motorsport Footer" class="img-fluid" style="max-width: 1600px; width: 100%;">
</section>

  <!-- Footer Text -->
  <footer class="bg-black text-center py-3 small" style="color: #ccc;">
  <div class="container">
    &copy; Raven Motorsport 2026. All rights reserved. <a href="/" style="color: #ccc; text-decoration: none;">Home</a> | <a href="/info" style="color: #ccc; text-decoration: none;">Race Info</a>
  </div>
</footer>

</body>
</html>
