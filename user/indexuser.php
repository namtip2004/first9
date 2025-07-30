<!DOCTYPE html>
<html lang="en">
<head>
<link href="assets/css/index.css" rel="stylesheet">
</head>
<body>
<?php include 'headerUser.php'; ?>
<main id="main" class="main">
<div class="carousel">
  <div class="slides">
    <!-- <div class="slide">
      <img src="https://via.placeholder.com/1600x900?text=Hero+Image+1" alt="Image 1" />
    </div> -->
    <div class="slide">
      <video autoplay muted playsinline>
        <source src="assets/video/vhome.mp4" type="video/mp4" />
        Your browser does not support HTML5 video.
      </video>
    </div>
    <!-- <div class="slide">
      <img src="https://via.placeholder.com/1600x900?text=Hero+Image+2" alt="Image 2" />
    </div> -->
  </div>
  <div class="buttons">
    <button onclick="prevSlide()">❮</button>
    <button onclick="nextSlide()">❯</button>
  </div>
</div>


</main>
<?php include 'footerUser.php'; ?>
<script>
  const slides = document.querySelector('.slides');
  const slideElements = document.querySelectorAll('.slide');
  const totalSlides = slideElements.length;
  let currentIndex = 0;
  let autoSlideTimer;

  function showSlide(index) {
    clearInterval(autoSlideTimer);
    removeVideoEndedListeners();

    currentIndex = index;
    const offset = -index * 100;
    slides.style.transform = `translateX(${offset}%)`;

    const currentSlide = slideElements[currentIndex];
    const video = currentSlide.querySelector("video");

    if (video) {
      video.currentTime = 0;
      video.play();
      video.addEventListener("ended", onVideoEnd);
    } else {
      autoSlideTimer = setInterval(() => {
        nextSlide();
      }, 5000);
    }
  }

  function removeVideoEndedListeners() {
    slideElements.forEach(slide => {
      const video = slide.querySelector("video");
      if (video) video.removeEventListener("ended", onVideoEnd);
    });
  }

  function onVideoEnd() {
    nextSlide();
  }

  function prevSlide() {
    const newIndex = (currentIndex > 0) ? currentIndex - 1 : totalSlides - 1;
    showSlide(newIndex);
  }

  function nextSlide() {
    const newIndex = (currentIndex + 1) % totalSlides;
    showSlide(newIndex);
  }

  // Start on load
  showSlide(0);
</script>
</body>
</html>