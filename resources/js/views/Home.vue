<script setup>
import { ref, onMounted, onUnmounted, inject } from 'vue'
import axios from 'axios'
import { getImagePath, huCategoryMap } from '../assets/js/CarData.js'

const featuredCars  = ref([])
const hoveredCar    = ref(null)
const heroLoaded    = ref(false)
const ctaReady      = ref(false)
const hero          = ref(null)
const featured      = ref(null)

const revealedSections = ref(new Set())
const counterValues    = ref({ years: 0, cars: 0, clients: 0 })
const counterTargets   = { years: 45, cars: 500, clients: 1200 }
let counterStarted     = false

const parallaxOffset  = inject('parallaxOffset', ref(0))
const setHeroVisible  = inject('setHeroVisible', () => {})

const formatPrice = (price) =>
  new Intl.NumberFormat('hu-HU', {
    style: 'currency',
    currency: 'HUF',
    minimumFractionDigits: 0,
  }).format(price)

const failedImages = ref(new Set())
const handleImageError = (id) => {
  failedImages.value = new Set([...failedImages.value, id])
}

const initReveal = () => {
  const revealObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        revealedSections.value.add(entry.target.dataset.reveal)
        if (entry.target.dataset.reveal === 'about' && !counterStarted) {
          counterStarted = true
          animateCounters()
        }
      }
    })
  }, {
    rootMargin: '0px 0px -15% 0px',
    threshold: 0.05
  })

  document.querySelectorAll('[data-reveal]').forEach(el => revealObserver.observe(el))

  const heroObserver = new IntersectionObserver(
    ([entry]) => { setHeroVisible(entry.isIntersecting) },
    {
      rootMargin: '-80px 0px 0px 0px',
      threshold: 0
    }
  )
  if (hero.value) heroObserver.observe(hero.value)
}

const animateCounters = () => {
  const duration = 1200
  const start    = performance.now()
  const tick = (now) => {
    const progress = Math.min((now - start) / duration, 1)
    const ease     = 1 - Math.pow(1 - progress, 3)
    counterValues.value.years   = Math.floor(ease * counterTargets.years)
    counterValues.value.cars    = Math.floor(ease * counterTargets.cars)
    counterValues.value.clients = Math.floor(ease * counterTargets.clients)
    if (progress < 1) {
      requestAnimationFrame(tick)
    } else {
      counterValues.value.years   = counterTargets.years
      counterValues.value.cars    = counterTargets.cars
      counterValues.value.clients = counterTargets.clients
    }
  }
  requestAnimationFrame(tick)
}

const fetchFeaturedCars = async () => {
  try {
    const response      = await axios.get('/api/cars?featured=true')
    featuredCars.value  = response.data.slice(0, 6)
  } catch (error) {
    console.error('Error fetching featured cars:', error)
  }
}

onMounted(() => {
  fetchFeaturedCars()
  setTimeout(() => { heroLoaded.value = true }, 50)
  setTimeout(() => { ctaReady.value = true }, 1000)
  initReveal()
})

onUnmounted(() => {
  setHeroVisible(false)
})
</script>

<template>
  <div class="home">

    <section class="hero" ref="hero" :class="{ loaded: heroLoaded }">
      <div
        class="hero-background"
        :style="{ transform: `translateY(${parallaxOffset * 0.5}px)` }"
      >
        <video autoplay loop muted playsinline class="hero-video">
          <source src="/public/media/Koenigsegg.mp4" type="video/mp4">
        </video>
      </div>
      <div class="hero-overlay" />

      <div class="orb orb-1" :style="{ transform: `translateY(${parallaxOffset * 0.2}px)` }" />
      <div class="orb orb-2" :style="{ transform: `translateY(${parallaxOffset * 0.35}px)` }" />

      <div class="hero-content">
        <div class="hero-eyebrow">
          <span class="eyebrow-line" />
          <span class="eyebrow-text">FŐOLDAL</span>
          <span class="eyebrow-line right" />
        </div>

        <h1 class="hero-title">
          <span class="hero-title-word"        style="--i:0">RAPID</span>
          <span class="hero-title-word italic" style="--i:1">MOTORS</span>
        </h1>

        <p class="hero-subtitle">
          <span
            class="subtitle-char"
            v-for="(ch, i) in 'klasszikus legendák és luxus autók'"
            :key="i"
            :style="`--d:${i * 10}ms`"
          >{{ ch === ' ' ? '\u00A0' : ch }}</span>
        </p>

        <router-link to="/cars" class="hero-cta" :class="{ ready: ctaReady }">
          <span class="cta-text">Fedezze fel kollekciónkat</span>
          <span class="cta-arrow">→</span>
          <span class="cta-shimmer" />
        </router-link>
      </div>

      <div class="scroll-indicator">
        <div class="scroll-line"><div class="scroll-dot" /></div>
        <span class="scroll-label">SCROLL</span>
      </div>

      <div class="hero-corner-tl" />
      <div class="hero-corner-br" />
    </section>

    <div class="marquee-strip">
      <div class="marquee-track">
        <span v-for="n in 10" :key="n" class="marquee-item">
          RAPID MOTORS <span class="marquee-dot">◆</span>
        </span>
      </div>
    </div>

    <section
      class="featured"
      ref="featured"
      data-reveal="featured"
      :class="{ revealed: revealedSections.has('featured') }"
    >
      <div class="section-header">
        <span class="section-label">KIEMELT MODELLEK</span>
        <h2 class="section-title">Különleges gyűjteményünk</h2>
        <div class="title-underline" />
      </div>

      <div class="cars-grid">
        <div
          v-for="(car, idx) in featuredCars"
          :key="car.id"
          class="car-card"
          :style="`--card-delay:${idx * 40}ms`"
          @mouseenter="hoveredCar = car.id"
          @mouseleave="hoveredCar = null"
        >
          <div class="car-card-image-wrapper">
            <img
              v-if="!failedImages.has(car.id)"
              :src="getImagePath(car)"
              :alt="car.manufacturer + ' ' + car.name"
              class="car-card-image"
              loading="lazy"
              @error="handleImageError(car.id)"
            />
            <div v-else class="car-card-img-fallback">
              <span class="img-fallback-icon">🚗</span>
              <span class="img-fallback-text">Kép nem elérhető</span>
            </div>
            <div class="car-card-overlay">
              <router-link :to="`/cars/${car.id}`" class="view-details">
                <span class="cta-text">Részletek megtekintése</span>
                <span class="cta-shimmer" />
              </router-link>
            </div>
          </div>
          <div class="car-card-content">
            <div class="car-card-category">
             {{ huCategoryMap.get(car.category).toUpperCase() }}
            </div>
            <h3 class="car-card-title">{{ car.manufacturer }} {{ car.name }}</h3>
            <div class="car-card-specs">
              <span>{{ car.year }}</span>
              <span class="spec-dot">•</span>
              <span>{{ car.horsepower }} LE</span>
              <span class="spec-dot">•</span>
              <span>{{ car.top_speed }} km/h</span>
            </div>
            <div class="car-card-footer">
              <div class="car-card-price">{{ formatPrice(car.price) }}</div>
              <router-link :to="`/cars/${car.id}`" class="card-arrow-icon">→</router-link>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section
      class="categories"
      data-reveal="categories"
      :class="{ revealed: revealedSections.has('categories') }"
    >
      <div class="category-card oldtimer-card">
        <div class="category-bg-zoom" />
        <div class="category-content">
          <div class="category-number">01</div>
          <h3 class="category-title">OLDTIMER AUTÓK</h3>
          <p class="category-description">Időtlen legendák, melyek az autóipar attyai</p>
          <router-link to="/cars?category=oldtimer" class="category-link">
            <span class="cta-text">Fedezd fel</span><span class="cat-arrow">→</span>
          </router-link>
        </div>
      </div>
      <div class="category-card luxurycar-card">
        <div class="category-bg-zoom" />
        <div class="category-content">
          <div class="category-number">02</div>
          <h3 class="category-title">LUXUS AUTÓK</h3>
          <p class="category-description">A luxus kiteljesedése az autók világában</p>
          <router-link to="/cars?category=luxury" class="category-link">
            <span class="cta-text">Fedezd fel</span><span class="cat-arrow">→</span>
          </router-link>
        </div>
      </div>
      <div class="category-card services-card">
        <div class="category-bg-zoom" />
        <div class="category-content">
          <div class="category-number">03</div>
          <h3 class="category-title">SZOLGÁLTATÁSOK</h3>
          <p class="category-description">Teljes körű szakértői támogatás és karbantartás a legjobb autókhoz</p>
          <router-link to="/services" class="category-link">
            <span class="cta-text">Fedezd fel</span><span class="cat-arrow">→</span>
          </router-link>
        </div>
      </div>
      <div class="category-card supercar-card">
        <div class="category-bg-zoom" />
        <div class="category-content">
          <div class="category-number">04</div>
          <h3 class="category-title">SZUPERAUTÓK</h3>
          <p class="category-description">A csúcstechnológia és a teljesítmény tökéletes harmóniája</p>
          <router-link to="/cars?category=supercar" class="category-link">
            <span class="cta-text">Fedezd fel</span><span class="cat-arrow">→</span>
          </router-link>
        </div>
      </div>
      <div class="category-card allcar-card">
        <div class="category-bg-zoom" />
        <div class="category-content">
          <div class="category-number">05</div>
          <h3 class="category-title">ÖSSZES AUTÓ</h3>
          <p class="category-description">Az összes oldalunkon listázott autó</p>
          <router-link to="/cars" class="category-link">
            <span class="cta-text">Fedezd fel</span><span class="cat-arrow">→</span>
          </router-link>
        </div>
      </div>
    </section>

    <section
      class="about"
      id="about"
      data-reveal="about"
      :class="{ revealed: revealedSections.has('about') }"
    >
      <div
        class="about-bg"
        :style="{ transform: `translateY(${parallaxOffset * 0.08}px) scale(1.15)` }"
      />
      <div class="about-overlay-left" />
      <div class="about-overlay-bottom" />

      <div class="about-inner">
        <div class="about-text-block">
          <span class="section-label">RAPID MOTORS</span>
          <h2 class="about-heading">
            Szenvedély<br><em>és szakértelem</em>
          </h2>
          <p class="about-description">
            A Rapid Motors 2026-ban indult útjára, azzal a céllal,
            hogy a klasszikus és luxus autók világában új mércét állítson fel.
            Minden egyes jármű gondosan válogatott kollekciónk része, ahol a minőség,
            az esztétika és az egyediség elsődleges szempont.
          </p>
          <div class="about-rule" />
        </div>

        <div class="about-stats">
          <div class="stat">
            <div class="stat-number">{{ counterValues.years }}<span class="stat-plus">+</span></div>
            <div class="stat-label">TAPASZTALT ÉRTÉKESÍTŐ</div>
            <div class="stat-bar-bg">
              <div class="stat-bar-fill" :style="`width:${(counterValues.years / counterTargets.years) * 100}%`" />
            </div>
          </div>
          <div class="stat">
            <div class="stat-number">{{ counterValues.cars }}<span class="stat-plus">+</span></div>
            <div class="stat-label">ÉRTÉKESÍTETT AUTÓ</div>
            <div class="stat-bar-bg">
              <div class="stat-bar-fill" :style="`width:${(counterValues.cars / counterTargets.cars) * 100}%`" />
            </div>
          </div>
          <div class="stat">
            <div class="stat-number">{{ counterValues.clients }}<span class="stat-plus">+</span></div>
            <div class="stat-label">ELÉGEDETT ÜGYFÉL</div>
            <div class="stat-bar-bg">
              <div class="stat-bar-fill" :style="`width:${(counterValues.clients / counterTargets.clients) * 100}%`" />
            </div>
          </div>
        </div>
      </div>
    </section>

    <section
      class="contact"
      id="contact"
      data-reveal="contact"
      :class="{ revealed: revealedSections.has('contact') }"
    >
      <div class="contact-bg-grid" aria-hidden="true" />
      <div class="contact-content">
        <span class="section-label">KAPCSOLAT</span>
        <h2 class="section-title">Lépjen kapcsolatba velünk</h2>
        <p class="contact-text">
          Készen áll, hogy megtalálja álmai autóját? Csapatunk rendelkezésére áll.
        </p>
        <div class="contact-info">
          <div class="contact-item">
            <div class="contact-icon">📞</div>
            <div class="contact-label">Telefon</div>
            <div class="contact-value">+36 1 234 5678</div>
          </div>
          <div class="contact-item">
            <div class="contact-icon">✉️</div>
            <div class="contact-label">E-mail</div>
            <div class="contact-value">info@rapidmotors.hu</div>
          </div>
          <div class="contact-item">
            <div class="contact-icon">📍</div>
            <div class="contact-label">Cím</div>
            <div class="contact-value">Budapest, V. kerület</div>
          </div>
        </div>
      </div>
    </section>

  </div>
</template>

<style>
@import '@/assets/css/home.css';
</style>
