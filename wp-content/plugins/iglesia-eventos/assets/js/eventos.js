/**
 * Iglesia · Próximos Eventos - JavaScript Frontend
 * Cuenta atrás en vivo, slider accesible y ocultación dinámica en web estática.
 */
(function() {
  'use strict';

  function initCountdowns() {
    const countdowns = document.querySelectorAll('.ige-countdown-wrap[data-start]');
    if (!countdowns.length) return;

    countdowns.forEach(wrap => {
      const startStr = wrap.getAttribute('data-start');
      if (!startStr) return;

      const targetTime = new Date(startStr).getTime();
      if (isNaN(targetTime)) return;

      const daysEl = wrap.querySelector('.cd-days');
      const hoursEl = wrap.querySelector('.cd-hours');
      const minsEl = wrap.querySelector('.cd-minutes');
      const secsEl = wrap.querySelector('.cd-seconds');
      const liveMsg = wrap.querySelector('.ige-cd-live-msg');
      const grid = wrap.querySelector('.ige-countdown-grid');

      function update() {
        const now = new Date().getTime();
        const diff = targetTime - now;

        if (diff <= 0) {
          if (daysEl) daysEl.textContent = '00';
          if (hoursEl) hoursEl.textContent = '00';
          if (minsEl) minsEl.textContent = '00';
          if (secsEl) secsEl.textContent = '00';
          if (liveMsg) liveMsg.style.display = 'block';
          if (grid) grid.style.opacity = '0.5';
          return;
        }

        const days = Math.floor(diff / (1000 * 60 * 60 * 24));
        const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((diff % (1000 * 60)) / 1000);

        if (daysEl) daysEl.textContent = String(days).padStart(2, '0');
        if (hoursEl) hoursEl.textContent = String(hours).padStart(2, '0');
        if (minsEl) minsEl.textContent = String(minutes).padStart(2, '0');
        if (secsEl) secsEl.textContent = String(seconds).padStart(2, '0');
      }

      update();
      const timer = setInterval(update, 1000);
      wrap._igeTimer = timer;
    });
  }

  function initSliders() {
    const sliders = document.querySelectorAll('.ige-slider-container.ige-has-multiple');
    sliders.forEach(slider => {
      if (slider._igeSliderInit) return;
      slider._igeSliderInit = true;

      const slides = slider.querySelectorAll('.ige-slide');
      const dots = slider.querySelectorAll('.ige-dot');
      const prevBtn = slider.querySelector('.ige-slider-btn.prev');
      const nextBtn = slider.querySelector('.ige-slider-btn.next');
      let current = 0;
      let autoPlayTimer = null;

      function goTo(index) {
        if (index < 0) index = slides.length - 1;
        if (index >= slides.length) index = 0;
        current = index;

        slides.forEach((s, idx) => {
          s.classList.toggle('active', idx === current);
        });
        dots.forEach((d, idx) => {
          d.classList.toggle('active', idx === current);
        });
      }

      if (prevBtn) {
        prevBtn.addEventListener('click', (e) => {
          e.preventDefault();
          goTo(current - 1);
        });
      }

      if (nextBtn) {
        nextBtn.addEventListener('click', (e) => {
          e.preventDefault();
          goTo(current + 1);
        });
      }

      dots.forEach((dot, idx) => {
        dot.addEventListener('click', (e) => {
          e.preventDefault();
          goTo(idx);
        });
      });

      // Gestos táctiles
      let touchStartX = 0;
      let touchEndX = 0;
      slider.addEventListener('touchstart', (e) => {
        touchStartX = e.changedTouches[0].screenX;
      }, { passive: true });

      slider.addEventListener('touchend', (e) => {
        touchEndX = e.changedTouches[0].screenX;
        const diff = touchEndX - touchStartX;
        if (Math.abs(diff) > 40) {
          if (diff < 0) goTo(current + 1);
          else goTo(current - 1);
        }
      }, { passive: true });

      // Autoplay suave cada 6s si hay foco / hover
      function startAutoplay() {
        if (autoPlayTimer) clearInterval(autoPlayTimer);
        autoPlayTimer = setInterval(() => goTo(current + 1), 6000);
      }
      function stopAutoplay() {
        if (autoPlayTimer) clearInterval(autoPlayTimer);
      }

      slider.addEventListener('mouseenter', stopAutoplay);
      slider.addEventListener('mouseleave', startAutoplay);
      startAutoplay();
    });
  }

  // Filtrado de caducidad en el cliente (Crucial para Web Estática Simply Static)
  function filterPastEventsInClient() {
    const wrappers = document.querySelectorAll('.ige-events-wrapper');
    const now = new Date().getTime();

    wrappers.forEach(wrap => {
      const cards = wrap.querySelectorAll('[data-ige-end]');
      let visibleCount = 0;

      cards.forEach(card => {
        const endStr = card.getAttribute('data-ige-end');
        if (endStr) {
          const endTime = new Date(endStr).getTime();
          if (!isNaN(endTime) && endTime < now) {
            // El evento ha caducado
            card.style.display = 'none';
            return;
          }
        }
        visibleCount++;
      });

      if (visibleCount === 0) {
        const emptyMsg = wrap.getAttribute('data-empty-text') || 'Pronto anunciaremos nuevos eventos especiales.';
        wrap.innerHTML = '<div class="ige-empty-notice"><p>' + emptyMsg + '</p></div>';
      }
    });
  }

  // Modal Lightbox nativo y compatible con Elementor y Web Estática
  function initLightbox() {
    let modal = document.querySelector('.ige-lightbox-modal');
    if (!modal) {
      modal = document.createElement('div');
      modal.className = 'ige-lightbox-modal';
      modal.innerHTML = `
        <div class="ige-lightbox-modal-content">
          <button type="button" class="ige-lightbox-modal-close" aria-label="Cerrar">×</button>
          <img class="ige-lightbox-modal-img" src="" alt="" />
          <div class="ige-lightbox-modal-caption"></div>
        </div>
      `;
      document.body.appendChild(modal);

      const closeBtn = modal.querySelector('.ige-lightbox-modal-close');
      function closeModal() {
        modal.classList.remove('active');
        document.body.style.overflow = '';
      }
      closeBtn.addEventListener('click', closeModal);
      modal.addEventListener('click', (e) => {
        if (e.target === modal) closeModal();
      });
      document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && modal.classList.contains('active')) closeModal();
      });
    }

    const modalImg = modal.querySelector('.ige-lightbox-modal-img');
    const modalCaption = modal.querySelector('.ige-lightbox-modal-caption');

    const triggers = document.querySelectorAll('.ige-lightbox-trigger, .ige-lightbox-link');
    triggers.forEach(trig => {
      if (trig._igeLightboxInit) return;
      trig._igeLightboxInit = true;

      trig.addEventListener('click', function(e) {
        // Si Elementor Lightbox está activo y no es modo edición, delegar en Elementor
        if (window.elementorFrontend && window.elementorFrontend.utils && window.elementorFrontend.utils.lightbox && !window.elementorFrontend.isEditMode()) {
          return;
        }

        e.preventDefault();
        const fullUrl = trig.getAttribute('href');
        const title = trig.getAttribute('data-elementor-lightbox-title') || trig.getAttribute('title') || '';
        if (fullUrl) {
          modalImg.src = fullUrl;
          modalCaption.textContent = title;
          modal.classList.add('active');
          document.body.style.overflow = 'hidden';
        }
      });
    });
  }

  function initAll() {
    filterPastEventsInClient();
    initCountdowns();
    initSliders();
    initLightbox();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initAll);
  } else {
    initAll();
  }

  // Soporte para editor y vista previa de Elementor
  if (window.elementorFrontend && window.elementorFrontend.hooks) {
    window.elementorFrontend.hooks.addAction('frontend/element_ready/iglesia_eventos.default', initAll);
  }
})();
