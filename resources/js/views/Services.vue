<script setup>
import { ref, onMounted, onUnmounted, inject } from 'vue'

const hero = ref(null)
const heroLoaded = ref(false)
const ctaReady = ref(false)
const revealedSections = ref(new Set())
const selectedService = ref(null)
const activeAccordion = ref(null)

const parallaxOffset = inject('parallaxOffset', ref(0))
const setHeroVisible = inject('setHeroVisible', () => {})

const services = [
  {
    id: 1,
    icon: '🔧',
    title: 'Teljes körű karbantartás',
    subtitle: 'Szakértői autóápolás',
    description: 'Professzionális karbantartási szolgáltatásokat kínálunk minden prémium és klasszikus járműhöz.',
    features: [
      'Rendszeres szervizelés és diagnosztika',
      'Eredeti alkatrészek beszerzése',
      'Motor és futómű felújítás',
      'Klíma és elektronika szerviz'
    ],
    price: 'Ár: 250.000 Ft',
    image: 'images/services1.jpg'
  },
  {
    id: 2,
    icon: '🫧',
    title: 'Prémium autómosás',
    subtitle: 'Részletes tisztítás',
    description: 'Luxus külső-belső tisztítás a legmagasabb minőségi standardok szerint.',
    features: [
      'Kézi autómosás prémium termékekkel',
      'Belső teljes tisztítás és ápolás',
      'Wax kezelés és polírozás',
      'Motor- és kerékjárati tisztítás'
    ],
    price: 'Ár: 100.000 Ft',
    image: 'images/services2.jpg'
  },
  {
    id: 3,
    icon: '⚒️',
    title: 'Részleges restaurálás',
    subtitle: 'Szakértői helyreállítás',
    description: 'Klasszikus és veterán autók professzionális restaurálása.',
    features: [
      'Fényezés és karosszéria munka',
      'Krómozás és nikkelezés',
      'Belső kárpitozás helyreállítás',
      'Eredeti állapot visszaállítása'
    ],
    price: 'Egyedi árajánlat',
    image: 'images/services3.jpg'
  },
  {
    id: 4,
    icon: '🛡️',
    title: 'Védőbevonat',
    subtitle: 'Kerámiabevonat és PPF',
    description: 'Tartós védelem a fényezett felületeknek és a karosszériának.',
    features: [
      'Kerámia bevonat felvitele',
      'PPF fóliázás (Paint Protection Film)',
      'Üveg hidrofób bevonat',
      '5-10 év garancia a bevonatokra'
    ],
    price: 'Ár: 150.000 Ft',
    image: 'images/services4.jpg'
  },
  {
    id: 5,
    icon: '🛒',
    title: 'Autókereskedés',
    subtitle: 'Vásárlás és értékesítés',
    description: 'Segítünk megtalálni az álmai autóját vagy eladni a meglévőt.',
    features: [
      'Személyre szabott autókeresés',
      'Piaci értékelés és tanácsadás',
      'Bizományosi értékesítés',
      'Teljes adminisztráció intézése'
    ],
    price: 'Egyedi megállapodás',
    image: 'images/services5.jpg'
  },
  {
    id: 6,
    icon: '📦',
    title: 'Tárolás',
    subtitle: 'Biztonságos őrzés',
    description: 'Klímakontrollált tároló helyiségek luxus járművek számára.',
    features: [
      'Klímakontrollált szárított környezet',
      '24/7 kamerarendszer és biztonsági őrzés',
      'Időszakos indítás és mozgatás',
      'Karbantartási csomag lehetőség'
    ],
    price: 'Ár: 50.000 Ft/hó',
    image: 'images/services6.jpg'
  }
]

const processSteps = [
  {
    number: '01',
    title: 'Konzultáció',
    description: 'Részletes igényfelmérés és személyes konzultáció szakértőinkkel.'
  },
  {
    number: '02',
    title: 'Árajánlat',
    description: 'Átlátható, részletes árajánlat a szolgáltatás minden eleméről.'
  },
  {
    number: '03',
    title: 'Kivitelezés',
    description: 'Professzionális munka a legmagasabb minőségi standardok szerint.'
  },
  {
    number: '04',
    title: 'Átadás',
    description: 'Részletes átadás, dokumentáció és garanciális feltételek ismertetése.'
  }
]

const faqs = [
  {
    question: 'Milyen márkájú autókat szolgálnak ki?',
    answer: 'Minden prémium és klasszikus autómárkával dolgozunk, beleértve a Ferrari, Porsche, Lamborghini, Mercedes-Benz, Bentley, Rolls-Royce és veterán autók széles spektrumát.'
  },
  {
    question: 'Mennyi idő egy teljes karbantartás?',
    answer: 'A munka komplexitásától függően 1-3 napot vesz igénybe. Sürgős esetekben expressz szolgáltatást is tudunk biztosítani egyeztetéssel.'
  },
  {
    question: 'Használnak eredeti alkatrészeket?',
    answer: 'Kizárólag eredeti vagy OEM minőségű alkatrészeket használunk. Veterán autóknál segítünk az eredeti alkatrészek beszerzésében világszerte.'
  },
  {
    question: 'Van garanciájuk a munkára?',
    answer: 'Igen, minden szolgáltatásunkra 12 hónapos garanciát vállalunk. A speciális bevonatok akár 5-10 év garanciával is rendelkeznek.'
  },
  {
    question: 'Foglalást hogy lehet kérni?',
    answer: 'Telefonon, e-mailben vagy személyesen is egyeztethetünk időpontot. Törekszünk rá, hogy 48 órán belül minden megkeresésre válaszoljunk.'
  }
]

const scrollToSection = (sectionId) => {
  const element = document.getElementById(sectionId)
  if (element) {
    element.scrollIntoView({ behavior: 'smooth', block: 'start' })
  }
}

const openServiceModal = (service) => {
  selectedService.value = service
  document.body.style.overflow = 'hidden'
}

const closeServiceModal = () => {
  selectedService.value = null
  document.body.style.overflow = ''
}

const toggleAccordion = (index) => {
  activeAccordion.value = activeAccordion.value === index ? null : index
}

const initReveal = () => {
  const revealObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        revealedSections.value.add(entry.target.dataset.reveal)
      }
    })
  }, {
    rootMargin: '0px 0px -15% 0px',
    threshold: 0.05
  })

  document.querySelectorAll('[data-reveal]').forEach(el => revealObserver.observe(el))

  const heroObserver = new IntersectionObserver(
    ([entry]) => { setHeroVisible(entry.isIntersecting) },
    { rootMargin: '-80px 0px 0px 0px', threshold: 0 }
  )
  if (hero.value) heroObserver.observe(hero.value)
}

onMounted(() => {
  setTimeout(() => { heroLoaded.value = true }, 50)
  setTimeout(() => { ctaReady.value = true }, 1000)
  initReveal()
})

onUnmounted(() => {
  document.body.style.overflow = ''
  setHeroVisible(false)
})
</script>

<template>
  <div class="services-page">

    <section class="services-hero" ref="hero" :class="{ loaded: heroLoaded }">
      <div
        class="hero-bg"
        :style="{ transform: `translateY(${parallaxOffset * 0.3}px)` }"
      />
      <div class="hero-overlay" />

      <div class="hero-orb orb-1" :style="{ transform: `translateY(${parallaxOffset * 0.15}px)` }" />
      <div class="hero-orb orb-2" :style="{ transform: `translateY(${parallaxOffset * 0.25}px)` }" />

      <div class="hero-content-wrapper">
        <div class="hero-badge">
          <span class="badge-line" />
          <span class="badge-text">SZOLGÁLTATÁSOK</span>
          <span class="badge-line right" />
        </div>

        <h1 class="hero-main-title">
          <span class="title-line" style="--i:0">Prémium</span>
          <span class="title-line italic" style="--i:1">autóápolás</span>
        </h1>

        <p class="hero-description">
          <span
            class="subtitle-char"
            v-for="(ch, i) in 'Szakértői szolgáltatások luxus és klasszikus járművek számára'"
            :key="i"
            :style="`--d:${i * 10}ms`"
          >{{ ch === ' ' ? '\u00A0' : ch }}</span>
        </p>

        <a
          href="#services-list"
          class="hero-cta-btn"
          :class="{ ready: ctaReady }"
          @click.prevent="scrollToSection('services-list')"
        >
          <span class="btn-text">Fedezze fel szolgáltatásainkat</span>
          <span class="btn-arrow">↓</span>
          <span class="cta-shimmer" />
        </a>
      </div>

      <div class="hero-scroll-hint">
        <div class="scroll-line"><div class="scroll-dot" /></div>
        <span class="scroll-label">SCROLL</span>
      </div>
    </section>

    <section
      class="services-grid-section"
      id="services-list"
      data-reveal="services"
      :class="{ revealed: revealedSections.has('services') }"
    >
      <div class="section-intro">
        <span class="section-label">SZOLGÁLTATÁSOK</span>
        <h2 class="section-title">Amit kínálunk</h2>
      </div>

      <div class="services-grid">
        <div
          v-for="(service, idx) in services"
          :key="service.id"
          class="service-card"
          :style="`--delay: ${idx * 40}ms`"
          @click="openServiceModal(service)"
        >
          <div class="service-image-wrapper">
            <img :src="service.image" :alt="service.title" class="service-image" loading="lazy" />
            <div class="service-overlay">
              <div class="service-icon">{{ service.icon }}</div>
            </div>
          </div>

          <div class="service-content">
            <div class="service-subtitle">{{ service.subtitle }}</div>
            <h3 class="service-title">{{ service.title }}</h3>
            <p class="service-description">{{ service.description }}</p>

            <div class="service-footer">
              <div class="service-price">{{ service.price }}</div>
              <div class="service-arrow">→</div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section
      class="process-section"
      data-reveal="process"
      :class="{ revealed: revealedSections.has('process') }"
    >
      <div class="process-bg-grid" />

      <div class="process-content">
        <div class="section-intro centered">
          <span class="section-label">FOLYAMAT</span>
          <h2 class="section-title">Hogyan dolgozunk</h2>
        </div>

        <div class="process-steps">
          <div
            v-for="(step, idx) in processSteps"
            :key="idx"
            class="process-step"
            :style="`--step-delay: ${idx * 50}ms`"
          >
            <div class="step-number">{{ step.number }}</div>
            <div class="step-content">
              <h3 class="step-title">{{ step.title }}</h3>
              <p class="step-description">{{ step.description }}</p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section
      class="faq-section"
      data-reveal="faq"
      :class="{ revealed: revealedSections.has('faq') }"
    >
      <div class="section-intro centered">
        <span class="section-label">GYAKORI KÉRDÉSEK</span>
        <h2 class="section-title">Tudjon meg többet</h2>
      </div>

      <div class="faq-list">
        <div
          v-for="(faq, idx) in faqs"
          :key="idx"
          class="faq-item"
          :class="{ active: activeAccordion === idx }"
          :style="`--faq-delay: ${idx * 30}ms`"
        >
          <button class="faq-question" @click="toggleAccordion(idx)">
            <span>{{ faq.question }}</span>
            <span class="faq-toggle">{{ activeAccordion === idx ? '−' : '+' }}</span>
          </button>
          <div class="faq-answer-wrapper">
            <div class="faq-answer">{{ faq.answer }}</div>
          </div>
        </div>
      </div>
    </section>

    <section
      class="cta-section"
      data-reveal="cta"
      :class="{ revealed: revealedSections.has('cta') }"
    >
      <div class="cta-bg-pattern" />
      <div class="cta-content">
        <h2 class="cta-title">Készen áll a következő lépésre?</h2>
        <p class="cta-text-service">
          Vegye fel velünk a kapcsolatot, és kezdjük el együtt gondoskodni autójáról
        </p>
        <div class="cta-buttons">
          <div class="btn-wrap" data-tooltip="A funkció hamarosan elérhető lesz.">
            <button class="cta-btn primary" disabled>
              <span>Hívjon minket</span>
            </button>
          </div>

          <router-link to="/" class="cta-btn secondary">
            <span>Vissza a főoldalra</span>
            <span class="btn-icon">→</span>
          </router-link>
        </div>
      </div>
    </section>

    <Transition name="modal">
      <div
        v-if="selectedService"
        class="service-modal-overlay"
        @click="closeServiceModal"
      >
        <div class="service-modal" @click.stop>
          <button class="modal-close" @click="closeServiceModal">×</button>

          <div class="modal-image">
            <img :src="selectedService.image" :alt="selectedService.title" />
            <div class="modal-icon">{{ selectedService.icon }}</div>
          </div>

          <div class="modal-content">
            <div class="modal-subtitle">{{ selectedService.subtitle }}</div>
            <h3 class="modal-title">{{ selectedService.title }}</h3>
            <p class="modal-description">{{ selectedService.description }}</p>

            <div class="modal-features">
              <h4 class="features-title">Szolgáltatás részletei:</h4>
              <ul class="features-list">
                <li v-for="(feature, idx) in selectedService.features" :key="idx">
                  <span class="feature-bullet">◆</span>
                  <span>{{ feature }}</span>
                </li>
              </ul>
            </div>

            <div class="modal-footer">
              <div class="modal-price">{{ selectedService.price }}</div>

              <div class="btn-wrap" data-tooltip="Az időpontfoglalás hamarosan elérhető lesz.">
                <button class="modal-cta" disabled>
                  Időpontfoglalás
                  <span class="cta-arrow">→</span>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </Transition>

  </div>
</template>

<style>
@import '@/assets/css/services.css';
</style>
